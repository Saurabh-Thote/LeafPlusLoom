<?php
/**
 * Leaf+Loom Admin - Edit Blog
 * Update blog information
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

// Get blog ID
$blog_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$blog_id) {
    header("Location: admin-blogs.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_blog'])) {
    try {
        // Generate slug from title
        $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\s-]/', '', $_POST['title'])));
        
        // Prepare SQL
        $sql = "UPDATE blogs SET
            title = :title,
            slug = :slug,
            featured_image = :featured_image,
            excerpt = :excerpt,
            content = :content,
            author_name = :author_name,
            category = :category,
            tags = :tags,
            reading_time = :reading_time,
            is_featured = :is_featured,
            status = :status";
        
        // Add published_at only if status is being set to published
        if ($_POST['status'] === 'published' && !$blog['published_at']) {
            $sql .= ", published_at = NOW()";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute([
            'title' => $_POST['title'],
            'slug' => $slug,
            'featured_image' => $_POST['featured_image'],
            'excerpt' => $_POST['excerpt'],
            'content' => $_POST['content'],
            'author_name' => $_POST['author_name'],
            'category' => $_POST['category'],
            'tags' => $_POST['tags'] ?? '',
            'reading_time' => $_POST['reading_time'],
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'status' => $_POST['status'],
            'id' => $blog_id
        ]);
        
        $success_message = "Blog updated successfully!";
        
        // Redirect after 1 second
        header("refresh:1;url=admin-view-blog.php?id=" . $blog_id);
        
    } catch(PDOException $e) {
        $error_message = "Error updating blog: " . $e->getMessage();
    }
}

// Fetch blog details
try {
    $stmt = $conn->prepare("SELECT * FROM blogs WHERE id = :id");
    $stmt->execute(['id' => $blog_id]);
    $blog = $stmt->fetch();
    
    if (!$blog) {
        header("Location: admin-blogs.php?error=Blog not found");
        exit;
    }
    
} catch(PDOException $e) {
    $error_message = "Error fetching blog: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog - <?php echo htmlspecialchars($blog['title']); ?> - Leaf+Loom Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-primary-green: #4A7C59;
            --color-primary-green-dark: #3A6047;
            --color-secondary-green: #7FB069;
        }
    </style>
</head>
<body class="bg-gray-50 font-[system-ui]">

    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="admin-blogs.php" class="text-gray-600 hover:text-primary-green transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Edit Blog Post</h1>
                        <p class="text-xs text-gray-500">Update blog information</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="admin-view-blog.php?id=<?php echo $blog['id']; ?>" 
                       class="text-gray-600 hover:text-primary-green transition-colors">View Blog</a>
                    <a href="dashboard.php" class="text-gray-600 hover:text-primary-green">Dashboard</a>
                    <a href="?logout=true" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8">
        
        <?php if (isset($success_message)): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-green-700 font-medium"><?php echo htmlspecialchars($success_message); ?> Redirecting...</p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
            <p class="text-red-700 font-medium"><?php echo htmlspecialchars($error_message); ?></p>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column - Current Blog Preview -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Current Blog</h3>
                    
                    <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden mb-4">
                        <img src="../<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($blog['title']); ?>"
                             class="w-full h-full object-cover"
                             onerror="this.src='../images/placeholder.jpg'">
                    </div>
                    
                    <h4 class="font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($blog['title']); ?></h4>
                    <p class="text-sm text-gray-600 mb-4 line-clamp-3"><?php echo htmlspecialchars($blog['excerpt']); ?></p>
                    
                    <div class="space-y-2 pt-3 border-t border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Category:</span>
                            <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($blog['category']); ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Views:</span>
                            <span class="font-semibold text-gray-800"><?php echo number_format($blog['views_count']); ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Status:</span>
                            <?php if ($blog['status'] === 'published'): ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Published
                                </span>
                            <?php elseif ($blog['status'] === 'draft'): ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Draft
                                </span>
                            <?php else: ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                    Archived
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Created:</span>
                            <span class="font-semibold text-gray-800"><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Edit Form -->
            <div class="lg:col-span-2">
                <form method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                    
                    <input type="hidden" name="update_blog" value="1">
                    
                    <!-- Basic Information -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">Basic Information</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Blog Title *</label>
                                <input type="text" name="title" required
                                       value="<?php echo htmlspecialchars($blog['title']); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Featured Image URL *</label>
                                <input type="text" name="featured_image" required
                                       value="<?php echo htmlspecialchars($blog['featured_image']); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Enter the full path to the featured image</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Excerpt / Short Description *</label>
                                <textarea name="excerpt" rows="3" required
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent"><?php echo htmlspecialchars($blog['excerpt']); ?></textarea>
                                <p class="text-xs text-gray-500 mt-1">Brief description that appears in blog listings</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Blog Content -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">Blog Content</h2>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Full Content *</label>
                            <textarea name="content" rows="12" required
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent"><?php echo htmlspecialchars($blog['content']); ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">Main blog content - supports HTML formatting</p>
                        </div>
                    </div>
                    
                    <!-- Author & Category -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">Author & Category</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Author Name *</label>
                                <input type="text" name="author_name" required
                                       value="<?php echo htmlspecialchars($blog['author_name']); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                                <input type="text" name="category" required
                                       value="<?php echo htmlspecialchars($blog['category']); ?>"
                                       placeholder="e.g., Sustainability, Hair Care"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tags & Reading Time -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">Tags & Reading Time</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tags</label>
                                <input type="text" name="tags"
                                       value="<?php echo htmlspecialchars($blog['tags']); ?>"
                                       placeholder="tag1, tag2, tag3"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Separate tags with commas</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Reading Time (minutes) *</label>
                                <input type="number" name="reading_time" required
                                       value="<?php echo $blog['reading_time']; ?>"
                                       min="1" max="60"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status & Featured -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">Publication Settings</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Blog Status *</label>
                                <select name="status" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                    <option value="draft" <?php echo $blog['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo $blog['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="archived" <?php echo $blog['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_featured" value="1" 
                                           <?php echo $blog['is_featured'] ? 'checked' : ''; ?>
                                           class="w-5 h-5 text-primary-green border-gray-300 rounded focus:ring-primary-green">
                                    <span class="text-sm font-medium text-gray-700">Featured Blog Post</span>
                                </label>
                            </div>
                        </div>
                        
                        <?php if ($blog['published_at']): ?>
                        <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-3 rounded">
                            <p class="text-sm text-blue-700">
                                <span class="font-semibold">Published on:</span> 
                                <?php echo date('F j, Y \a\t g:i A', strtotime($blog['published_at'])); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-4 pt-6 border-t border-gray-200">
                        <a href="admin-blogs.php" 
                           class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-colors text-center">
                            Cancel
                        </a>
                        <button type="submit"
                                class="flex-1 bg-primary-green hover:bg-primary-green-dark text-white font-semibold py-3 px-6 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Blog Post
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    </main>

</body>
</html>
