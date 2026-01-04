<?php
/**
 * Leaf+Loom Admin - View Blog Details
 * Display complete blog information
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

// Helper function to format date
function formatDate($date) {
    return date('F j, Y \a\t g:i A', strtotime($date));
}

// Helper function to format tags
function formatTags($tags) {
    if (!$tags) return [];
    return array_map('trim', explode(',', $tags));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Blog - <?php echo htmlspecialchars($blog['title']); ?> - Leaf+Loom Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-primary-green: #4A7C59;
            --color-primary-green-dark: #3A6047;
            --color-secondary-green: #7FB069;
        }
        
        .blog-content {
            line-height: 1.8;
        }
        
        .blog-content p {
            margin-bottom: 1rem;
        }
        
        .blog-content h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 1.5rem 0 1rem;
        }
        
        .blog-content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 1.25rem 0 0.75rem;
        }
        
        .blog-content ul, .blog-content ol {
            margin: 1rem 0;
            padding-left: 2rem;
        }
        
        .blog-content li {
            margin: 0.5rem 0;
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
                        <h1 class="text-xl font-bold text-gray-800">Blog Details</h1>
                        <p class="text-xs text-gray-500">Viewing blog post information</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="admin-edit-blog.php?id=<?php echo $blog['id']; ?>" 
                       class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Blog
                    </a>
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
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column - Blog Meta Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24 space-y-6">
                    
                    <!-- Featured Image -->
                    <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden">
                        <img src="../<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($blog['title']); ?>"
                             class="w-full h-full object-cover"
                             onerror="this.src='../images/placeholder.jpg'">
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                            <span class="text-sm text-gray-600">Blog ID:</span>
                            <span class="text-sm font-semibold text-gray-800">#<?php echo $blog['id']; ?></span>
                        </div>
                        
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                            <span class="text-sm text-gray-600">Status:</span>
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
                        
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                            <span class="text-sm text-gray-600">Category:</span>
                            <span class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($blog['category']); ?></span>
                        </div>
                        
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                            <span class="text-sm text-gray-600">Views:</span>
                            <span class="text-sm font-semibold text-gray-800 flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <?php echo number_format($blog['views_count']); ?>
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                            <span class="text-sm text-gray-600">Reading Time:</span>
                            <span class="text-sm font-semibold text-gray-800 flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <?php echo $blog['reading_time']; ?> min
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                            <span class="text-sm text-gray-600">Created:</span>
                            <span class="text-sm font-semibold text-gray-800"><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span>
                        </div>
                        
                        <?php if ($blog['published_at']): ?>
                        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                            <span class="text-sm text-gray-600">Published:</span>
                            <span class="text-sm font-semibold text-gray-800"><?php echo date('M d, Y', strtotime($blog['published_at'])); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Last Updated:</span>
                            <span class="text-sm font-semibold text-gray-800"><?php echo date('M d, Y', strtotime($blog['updated_at'])); ?></span>
                        </div>
                    </div>
                    
                    <!-- Author Info -->
                    <div class="pt-6 border-t border-gray-200">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Author</p>
                        <div class="flex items-center gap-3">
                            <div class="h-12 w-12 rounded-full bg-primary-green text-white flex items-center justify-center text-lg font-bold">
                                <?php echo strtoupper(substr($blog['author_name'], 0, 2)); ?>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($blog['author_name']); ?></p>
                                <p class="text-xs text-gray-500">Content Author</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Featured Badge -->
                    <?php if ($blog['is_featured']): ?>
                    <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-lg p-4 text-center">
                        <svg class="w-8 h-8 text-white mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <p class="text-white font-semibold text-sm">Featured Blog</p>
                    </div>
                    <?php endif; ?>
                    
                </div>
            </div>
            
            <!-- Right Column - Blog Content -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Blog Title & Excerpt -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <h1 class="text-4xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($blog['title']); ?></h1>
                    <p class="text-xl text-gray-600 leading-relaxed"><?php echo htmlspecialchars($blog['excerpt']); ?></p>
                </div>
                
                <!-- Blog Content -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b border-gray-200">Blog Content</h2>
                    <div class="blog-content text-gray-700">
                        <?php echo nl2br($blog['content']); ?>
                    </div>
                </div>
                
                <!-- Tags -->
                <?php if ($blog['tags']): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b border-gray-200">Tags</h2>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach (formatTags($blog['tags']) as $tag): ?>
                        <span class="inline-flex items-center px-4 py-2 rounded-full bg-primary-green/10 text-primary-green text-sm font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <?php echo htmlspecialchars($tag); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Blog Metadata -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b border-gray-200">Blog Metadata</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Slug (URL)</p>
                            <p class="font-semibold text-gray-800 bg-gray-50 rounded px-3 py-2 text-sm font-mono">
                                <?php echo htmlspecialchars($blog['slug']); ?>
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Full URL</p>
                            <p class="font-semibold text-primary-green bg-gray-50 rounded px-3 py-2 text-sm break-all">
                                /blog/<?php echo htmlspecialchars($blog['slug']); ?>
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Created At</p>
                            <p class="font-semibold text-gray-800 bg-gray-50 rounded px-3 py-2 text-sm">
                                <?php echo formatDate($blog['created_at']); ?>
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Last Updated</p>
                            <p class="font-semibold text-gray-800 bg-gray-50 rounded px-3 py-2 text-sm">
                                <?php echo formatDate($blog['updated_at']); ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex gap-4">
                        <a href="admin-edit-blog.php?id=<?php echo $blog['id']; ?>" 
                           class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors text-center flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Blog
                        </a>
                        
                        <a href="admin-blogs.php" 
                           class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-colors text-center flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to List
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
        
    </main>

</body>
</html>
