<?php
/**
 * Leaf+Loom Admin - Add New Blog Post
 * Handles blog post creation and database insertion
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Include database connection
require_once '../config.php';

// Get admin info
$admin_name = $_SESSION['admin_full_name'];
$admin_email = $_SESSION['admin_email'];

// Initialize variables
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize and validate input
        $title = trim($_POST['title']);
        $slug = trim($_POST['slug']);
        $content = $_POST['content'];
        $excerpt = trim($_POST['excerpt']);
        $featured_image = trim($_POST['featured_image']);
        $author_name = trim($_POST['author_name']);
        $author_image = isset($_POST['author_image']) ? trim($_POST['author_image']) : 'images/author/default-author.jpg';
        $category = trim($_POST['category']);
        $tags = isset($_POST['tags']) ? trim($_POST['tags']) : null;
        $reading_time = (int)$_POST['reading_time'];
        $status = $_POST['status'];
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        
        // Auto-generate slug if empty
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        }
        
        // Set published_at based on status
        $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;
        
        // Validate required fields
        if (empty($title) || empty($content) || empty($excerpt) || empty($featured_image) || empty($author_name) || empty($category)) {
            throw new Exception("All required fields must be filled out.");
        }
        
        // Check if slug already exists
        $stmt = $conn->prepare("SELECT id FROM blogs WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        if ($stmt->fetch()) {
            // Append timestamp to make slug unique
            $slug = $slug . '-' . time();
        }
        
        // Handle featured image upload if file was uploaded
        if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../images/blog/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['featured_image_file']['name'], PATHINFO_EXTENSION);
            $new_filename = $slug . '-' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            // Validate image
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array(strtolower($file_extension), $allowed_extensions)) {
                throw new Exception("Invalid image format. Allowed: JPG, PNG, WEBP, GIF");
            }
            
            // Check file size (max 5MB)
            if ($_FILES['featured_image_file']['size'] > 5 * 1024 * 1024) {
                throw new Exception("Image file is too large. Maximum size is 5MB.");
            }
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['featured_image_file']['tmp_name'], $upload_path)) {
                $featured_image = 'images/blog/' . $new_filename;
            } else {
                throw new Exception("Failed to upload image. Please try again.");
            }
        }
        
        // Insert blog into database
        $sql = "INSERT INTO blogs (
            title, 
            slug, 
            featured_image, 
            excerpt, 
            content, 
            author_name, 
            author_image, 
            category, 
            tags, 
            reading_time, 
            views_count, 
            is_featured, 
            status, 
            published_at, 
            created_at, 
            updated_at
        ) VALUES (
            :title, 
            :slug, 
            :featured_image, 
            :excerpt, 
            :content, 
            :author_name, 
            :author_image, 
            :category, 
            :tags, 
            :reading_time, 
            0, 
            :is_featured, 
            :status, 
            :published_at, 
            NOW(), 
            NOW()
        )";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'title' => $title,
            'slug' => $slug,
            'featured_image' => $featured_image,
            'excerpt' => $excerpt,
            'content' => $content,
            'author_name' => $author_name,
            'author_image' => $author_image,
            'category' => $category,
            'tags' => $tags,
            'reading_time' => $reading_time,
            'is_featured' => $is_featured,
            'status' => $status,
            'published_at' => $published_at
        ]);
        
        $blog_id = $conn->lastInsertId();
        
        // Redirect to blog list with success message
        $redirect_status = ($status === 'published') ? 'published' : 'saved';
        header("Location: admin-blogs.php?success=$redirect_status&id=$blog_id");
        exit;
        
    } catch(PDOException $e) {
        $error_message = "Database Error: " . $e->getMessage();
    } catch(Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Set page title
$page_title = "Add New Blog";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Leaf+Loom Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-primary-green: #4A7C59;
            --color-primary-green-dark: #3A6047;
            --color-secondary-green: #7FB069;
            --color-bamboo-beige: #D4C5A0;
        }
        
        .editor-btn {
            @apply p-2 hover:bg-gray-200 rounded transition-colors;
        }
        
        #editor:empty:before {
            content: attr(placeholder);
            color: #9CA3AF;
            pointer-events: none;
        }
        
        #editor h1 { @apply text-3xl font-bold mb-4; }
        #editor h2 { @apply text-2xl font-bold mb-3; }
        #editor h3 { @apply text-xl font-bold mb-2; }
        #editor h4 { @apply text-lg font-bold mb-2; }
        #editor p { @apply mb-4; }
        #editor ul { @apply list-disc ml-6 mb-4; }
        #editor ol { @apply list-decimal ml-6 mb-4; }
        #editor a { @apply text-primary-green underline; }
        #editor blockquote { @apply border-l-4 border-gray-300 pl-4 italic my-4; }
        
        .prose img {
            @apply max-w-full h-auto rounded-lg my-4;
        }
    </style>
</head>
<body class="bg-gray-50 font-[system-ui]">

    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="bg-primary-green rounded-lg p-2">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Leaf+Loom Admin</h1>
                        <p class="text-xs text-gray-500">Add New Blog Post</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($admin_name); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($admin_email); ?></p>
                    </div>
                    <a href="admin-blogs.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Blogs
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-white min-h-screen shadow-sm border-r border-gray-200">
            <nav class="p-4 space-y-2">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>
                <a href="admin-products-list.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Products
                </a>
                <a href="admin-blogs.php" class="flex items-center gap-3 px-4 py-3 bg-primary-green text-white rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5-1.253"></path>
                    </svg>
                    Blogs
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">

            <?php if ($error_message): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-red-700 font-medium"><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Create New Blog Post</h2>
                <p class="text-gray-600">Fill in the details below to publish a new blog article</p>
            </div>

            <!-- Blog Form -->
            <form action="admin-add-blog.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left Column - Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Blog Title -->
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Blog Title *</label>
                            <input type="text" id="blogTitle" name="title" required 
                                   placeholder="Enter an engaging blog title..." 
                                   oninput="updateSEOAnalysis(); generateSlug();"
                                   class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">
                                <span id="titleCount">0</span>/60 characters (Optimal: 50-60)
                            </p>
                        </div>

                        <!-- Permalink/Slug -->
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Permalink (Auto-generated)</label>
                            <div class="flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-lg border border-gray-200">
                                <span class="text-gray-500 text-sm">https://leafplusloom.com/blog/</span>
                                <span id="slugPreview" class="text-primary-green font-mono text-sm">your-blog-slug</span>
                            </div>
                            <input type="hidden" name="slug" id="slugInput" value="">
                        </div>

                        <!-- Rich Text Editor -->
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Blog Content *</label>
                            
                            <!-- Editor Toolbar -->
                            <div class="border border-gray-300 rounded-t-lg bg-gray-50 p-2 flex flex-wrap gap-1">
                                <!-- Text Formatting -->
                                <div class="flex gap-1 border-r border-gray-300 pr-2">
                                    <button type="button" onclick="formatText('bold')" title="Bold (Ctrl+B)" class="editor-btn">
                                        <strong>B</strong>
                                    </button>
                                    <button type="button" onclick="formatText('italic')" title="Italic (Ctrl+I)" class="editor-btn">
                                        <em>I</em>
                                    </button>
                                    <button type="button" onclick="formatText('underline')" title="Underline (Ctrl+U)" class="editor-btn">
                                        <u>U</u>
                                    </button>
                                </div>

                                <!-- Headings -->
                                <div class="flex gap-1 border-r border-gray-300 pr-2">
                                    <select onchange="formatText('formatBlock', this.value); this.selectedIndex=0;" 
                                            class="text-xs border border-gray-300 rounded px-2 py-1 bg-white">
                                        <option value="">Paragraph</option>
                                        <option value="h1">Heading 1</option>
                                        <option value="h2">Heading 2</option>
                                        <option value="h3">Heading 3</option>
                                        <option value="h4">Heading 4</option>
                                        <option value="p">Paragraph</option>
                                    </select>
                                </div>

                                <!-- Lists -->
                                <div class="flex gap-1 border-r border-gray-300 pr-2">
                                    <button type="button" onclick="formatText('insertUnorderedList')" title="Bullet List" class="editor-btn">
                                        • List
                                    </button>
                                    <button type="button" onclick="formatText('insertOrderedList')" title="Numbered List" class="editor-btn">
                                        1. List
                                    </button>
                                </div>

                                <!-- Alignment -->
                                <div class="flex gap-1 border-r border-gray-300 pr-2">
                                    <button type="button" onclick="formatText('justifyLeft')" title="Align Left" class="editor-btn">⬅</button>
                                    <button type="button" onclick="formatText('justifyCenter')" title="Align Center" class="editor-btn">⬌</button>
                                    <button type="button" onclick="formatText('justifyRight')" title="Align Right" class="editor-btn">➡</button>
                                </div>

                                <!-- Links & Media -->
                                <div class="flex gap-1 border-r border-gray-300 pr-2">
                                    <button type="button" onclick="insertLink()" title="Insert Link" class="editor-btn">🔗 Link</button>
                                    <button type="button" onclick="document.getElementById('contentImageUpload').click()" title="Insert Image" class="editor-btn">🖼 Image</button>
                                    <input type="file" id="contentImageUpload" accept="image/" class="hidden" onchange="insertImage(event)">
                                </div>

                                <!-- Undo/Redo -->
                                <div class="flex gap-1">
                                    <button type="button" onclick="formatText('undo')" title="Undo" class="editor-btn">↶</button>
                                    <button type="button" onclick="formatText('redo')" title="Redo" class="editor-btn">↷</button>
                                </div>
                            </div>

                            <!-- Editor Content Area -->
                            <div id="editor" contenteditable="true" 
                                 oninput="syncEditorContent(); updateSEOAnalysis();"
                                 class="border border-t-0 border-gray-300 rounded-b-lg p-4 min-h-[400px] max-h-[500px] overflow-y-auto bg-white prose prose-sm max-w-none focus:outline-none focus:ring-2 focus:ring-primary-green"
                                 placeholder="Start writing your blog content here...">
                            </div>
                            <textarea name="content" id="contentHidden" required class="hidden"></textarea>

                            <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                                <span>Word count: <strong id="wordCount">0</strong> words</span>
                                <span>Character count: <strong id="charCount">0</strong> characters</span>
                            </div>
                        </div>

                        <!-- Excerpt -->
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Excerpt / Meta Description *</label>
                            <textarea name="excerpt" id="excerpt" rows="3" required oninput="updateSEOAnalysis()"
                                      placeholder="Write a compelling summary (150-160 characters optimal for SEO)"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent"></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                <span id="excerptCount">0</span>/160 characters
                            </p>
                        </div>

                    </div>

                    <!-- Right Column - Settings & SEO -->
                    <div class="lg:col-span-1 space-y-6">

                        <!-- SEO Analysis Panel -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <h3 class="font-bold text-gray-800">SEO Analysis</h3>
                            </div>

                            <!-- SEO Score -->
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-semibold text-gray-700">SEO Score</span>
                                    <span id="seoScore" class="text-2xl font-bold text-gray-400">0%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div id="seoProgress" class="h-full bg-gray-400 transition-all duration-500" style="width: 0%"></div>
                                </div>
                                <p id="seoStatus" class="text-xs text-gray-500 mt-2">Start writing to see SEO analysis</p>
                            </div>

                            <!-- SEO Checklist -->
                            <div class="space-y-2">
                                <div id="seoTitle" class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-400">○</span>
                                    <span class="text-gray-600">Title length (50-60 chars)</span>
                                </div>
                                <div id="seoExcerpt" class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-400">○</span>
                                    <span class="text-gray-600">Meta description (150-160)</span>
                                </div>
                                <div id="seoKeyword" class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-400">○</span>
                                    <span class="text-gray-600">Focus keyword present</span>
                                </div>
                                <div id="seoContent" class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-400">○</span>
                                    <span class="text-gray-600">Content length (300+ words)</span>
                                </div>
                                <div id="seoHeadings" class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-400">○</span>
                                    <span class="text-gray-600">Use of headings (H2, H3)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Focus Keyword -->
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Focus Keyword</label>
                            <input type="text" name="focus_keyword" id="focusKeyword" oninput="updateSEOAnalysis()"
                                   placeholder="e.g., eco-friendly products"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Main keyword you want to rank for</p>
                        </div>

                        <!-- Featured Image -->
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Featured Image *</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary-green transition-colors cursor-pointer"
                                 onclick="document.getElementById('featuredImageInput').click()">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-sm text-gray-600">Click to upload image</p>
                                <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP (Max 5MB)</p>
                            </div>
                            <input type="file" id="featuredImageInput" name="featured_image_file" accept="image/" class="hidden">
                            <input type="text" name="featured_image" id="featuredImage" required 
                                   placeholder="images/blog/your-image.jpg"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent mt-2">
                            <p class="text-xs text-gray-500 mt-1">Or enter image URL manually</p>
                        </div>

                        <!-- Author -->
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Author Name *</label>
                            <input type="text" name="author_name" required value="<?php echo htmlspecialchars($admin_name); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                        </div>

                        <!-- Category -->
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                            <select name="category" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                <option value="">Select Category</option>
                                <option value="Sustainability">Sustainability</option>
                                <option value="Hair Care">Hair Care</option>
                                <option value="Lifestyle">Lifestyle</option>
                                <option value="Product Care">Product Care</option>
                                <option value="DIY & Crafts">DIY & Crafts</option>
                                <option value="Product Reviews">Product Reviews</option>
                                <option value="Eco Tips">Eco Tips</option>
                            </select>
                        </div>

                        <!-- Tags -->
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tags</label>
                            <input type="text" name="tags" placeholder="bamboo, eco-friendly, sustainable"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Separate with commas</p>
                        </div>

                        <!-- Reading Time -->
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Reading Time *</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="reading_time" id="readingTime" value="5" required min="1" max="60"
                                       class="w-20 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                <span class="text-sm text-gray-600">minutes</span>
                                <button type="button" onclick="calculateReadingTime()" 
                                        class="text-xs text-primary-green hover:text-primary-green-dark ml-auto">
                                    Auto Calculate
                                </button>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                            <select name="status" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>

                        <!-- Featured Checkbox -->
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_featured" value="1" id="isFeatured"
                                       class="w-5 h-5 text-primary-green border-gray-300 rounded focus:ring-primary-green">
                                <label for="isFeatured" class="text-sm font-medium text-gray-700 cursor-pointer">
                                    Mark as Featured Post
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-6">
                    <a href="admin-blogs.php"
                       class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-colors text-center">
                        Cancel
                    </a>
                    <button type="button" onclick="saveDraft()"
                            class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                        Save Draft
                    </button>
                    <button type="submit"
                            class="flex-1 bg-primary-green hover:bg-primary-green-dark text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                        Publish Blog
                    </button>
                </div>
            </form>

        </main>
    </div>

    <script>
        // Rich Text Editor Functions
        function formatText(command, value = null) {
            document.execCommand(command, false, value);
            document.getElementById('editor').focus();
        }

        function insertLink() {
            const url = prompt('Enter URL:');
            if (url) {
                formatText('createLink', url);
            }
        }

        function insertImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    formatText('insertImage', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        }

        // Sync editor content to hidden textarea
        function syncEditorContent() {
            const editor = document.getElementById('editor');
            const hidden = document.getElementById('contentHidden');
            hidden.value = editor.innerHTML;

            // Update word and character count
            const text = editor.innerText || editor.textContent;
            const words = text.trim().split(/\s+/).filter(word => word.length > 0);
            document.getElementById('wordCount').textContent = words.length;
            document.getElementById('charCount').textContent = text.length;
        }

        // Generate slug from title
        function generateSlug() {
            const title = document.getElementById('blogTitle').value;
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();

            document.getElementById('slugPreview').textContent = slug || 'your-blog-slug';
            document.getElementById('slugInput').value = slug;

            // Update title character count
            document.getElementById('titleCount').textContent = title.length;
        }

        // Update excerpt character count
        document.getElementById('excerpt').addEventListener('input', function() {
            document.getElementById('excerptCount').textContent = this.value.length;
        });

        // Calculate reading time based on word count
        function calculateReadingTime() {
            const wordCount = parseInt(document.getElementById('wordCount').textContent);
            const readingTime = Math.ceil(wordCount / 200); // Average 200 words per minute
            document.getElementById('readingTime').value = readingTime || 1;
        }

        // SEO Analysis Function
        function updateSEOAnalysis() {
            const title = document.getElementById('blogTitle').value;
            const excerpt = document.getElementById('excerpt').value;
            const keyword = document.getElementById('focusKeyword').value.toLowerCase();
            const editor = document.getElementById('editor');
            const content = editor.innerText || editor.textContent;
            const wordCount = content.trim().split(/\s+/).filter(word => word.length > 0).length;

            let score = 0;

            // Check 1: Title length (50-60 characters)
            const titleCheck = document.getElementById('seoTitle');
            if (title.length >= 50 && title.length <= 60) {
                titleCheck.innerHTML = '<span class="text-green-500">✓</span> <span class="text-gray-700">Title length optimal</span>';
                score += 20;
            } else if (title.length > 0) {
                titleCheck.innerHTML = '<span class="text-yellow-500">!</span> <span class="text-gray-600">Title should be 50-60 chars</span>';
                score += 10;
            } else {
                titleCheck.innerHTML = '<span class="text-gray-400">○</span> <span class="text-gray-600">Title length (50-60 chars)</span>';
            }

            // Check 2: Meta description (150-160 characters)
            const excerptCheck = document.getElementById('seoExcerpt');
            if (excerpt.length >= 150 && excerpt.length <= 160) {
                excerptCheck.innerHTML = '<span class="text-green-500">✓</span> <span class="text-gray-700">Meta description optimal</span>';
                score += 20;
            } else if (excerpt.length > 0) {
                excerptCheck.innerHTML = '<span class="text-yellow-500">!</span> <span class="text-gray-600">Description should be 150-160 chars</span>';
                score += 10;
            } else {
                excerptCheck.innerHTML = '<span class="text-gray-400">○</span> <span class="text-gray-600">Meta description (150-160)</span>';
            }

            // Check 3: Focus keyword present
            const keywordCheck = document.getElementById('seoKeyword');
            if (keyword && (title.toLowerCase().includes(keyword) || content.toLowerCase().includes(keyword))) {
                keywordCheck.innerHTML = '<span class="text-green-500">✓</span> <span class="text-gray-700">Focus keyword found</span>';
                score += 20;
            } else if (keyword) {
                keywordCheck.innerHTML = '<span class="text-red-500">✗</span> <span class="text-gray-600">Keyword not found in content</span>';
            } else {
                keywordCheck.innerHTML = '<span class="text-gray-400">○</span> <span class="text-gray-600">Focus keyword present</span>';
            }

            // Check 4: Content length (300+ words)
            const contentCheck = document.getElementById('seoContent');
            if (wordCount >= 300) {
                contentCheck.innerHTML = '<span class="text-green-500">✓</span> <span class="text-gray-700">Content length good (' + wordCount + ' words)</span>';
                score += 20;
            } else if (wordCount > 0) {
                contentCheck.innerHTML = '<span class="text-yellow-500">!</span> <span class="text-gray-600">Need ' + (300 - wordCount) + ' more words</span>';
                score += Math.round((wordCount / 300) * 20);
            } else {
                contentCheck.innerHTML = '<span class="text-gray-400">○</span> <span class="text-gray-600">Content length (300+ words)</span>';
            }

            // Check 5: Use of headings
            const headingsCheck = document.getElementById('seoHeadings');
            const hasHeadings = editor.querySelector('h1, h2, h3, h4');
            if (hasHeadings) {
                headingsCheck.innerHTML = '<span class="text-green-500">✓</span> <span class="text-gray-700">Headings present</span>';
                score += 20;
            } else if (content.length > 100) {
                headingsCheck.innerHTML = '<span class="text-yellow-500">!</span> <span class="text-gray-600">Add headings for better structure</span>';
            } else {
                headingsCheck.innerHTML = '<span class="text-gray-400">○</span> <span class="text-gray-600">Use of headings (H2, H3)</span>';
            }

            // Update SEO Score
            const seoScoreEl = document.getElementById('seoScore');
            const seoProgress = document.getElementById('seoProgress');
            const seoStatus = document.getElementById('seoStatus');

            seoScoreEl.textContent = score + '%';
            seoProgress.style.width = score + '%';

            // Color coding
            if (score >= 80) {
                seoProgress.className = 'h-full bg-green-500 transition-all duration-500';
                seoScoreEl.className = 'text-2xl font-bold text-green-600';
                seoStatus.textContent = 'Excellent! Your content is SEO optimized.';
                seoStatus.className = 'text-xs text-green-600 mt-2';
            } else if (score >= 60) {
                seoProgress.className = 'h-full bg-yellow-500 transition-all duration-500';
                seoScoreEl.className = 'text-2xl font-bold text-yellow-600';
                seoStatus.textContent = 'Good! A few improvements can boost SEO.';
                seoStatus.className = 'text-xs text-yellow-600 mt-2';
            } else if (score > 0) {
                seoProgress.className = 'h-full bg-orange-500 transition-all duration-500';
                seoScoreEl.className = 'text-2xl font-bold text-orange-600';
                seoStatus.textContent = 'Needs work. Follow the checklist above.';
                seoStatus.className = 'text-xs text-orange-600 mt-2';
            } else {
                seoProgress.className = 'h-full bg-gray-400 transition-all duration-500';
                seoScoreEl.className = 'text-2xl font-bold text-gray-400';
                seoStatus.textContent = 'Start writing to see SEO analysis';
                seoStatus.className = 'text-xs text-gray-500 mt-2';
            }
        }

        // Save as draft
        function saveDraft() {
            document.querySelector('select[name="status"]').value = 'draft';
            document.querySelector('form').submit();
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'b':
                        e.preventDefault();
                        formatText('bold');
                        break;
                    case 'i':
                        e.preventDefault();
                        formatText('italic');
                        break;
                    case 'u':
                        e.preventDefault();
                        formatText('underline');
                        break;
                }
            }
        });
    </script>

</body>
</html>
