<?php
/**
 * Leaf+Loom Admin - Blogs Management
 * Complete CRUD operations for blog posts
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

// Handle Delete Blog
if (isset($_POST['delete_blog'])) {
    $blog_id = (int)$_POST['blog_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM blogs WHERE id = :id");
        $stmt->execute(['id' => $blog_id]);
        
        header("Location: admin-blogs.php?deleted=1");
        exit;
        
    } catch(PDOException $e) {
        header("Location: admin-blogs.php?error=" . urlencode("Error deleting blog: " . $e->getMessage()));
        exit;
    }
}

// Fetch all blogs
try {
    $stmt = $conn->query("SELECT * FROM blogs ORDER BY created_at DESC");
    $blogs = $stmt->fetchAll();
    
    // Calculate statistics
    $total_blogs = count($blogs);
    $published_blogs = count(array_filter($blogs, fn($b) => $b['status'] === 'published'));
    $draft_blogs = count(array_filter($blogs, fn($b) => $b['status'] === 'draft'));
    $total_views = array_sum(array_column($blogs, 'views_count'));
    
} catch(PDOException $e) {
    $error_message = "Error fetching blogs: " . $e->getMessage();
    $blogs = [];
}

// Helper function to format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Helper function to truncate text
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blogs Management - Leaf+Loom Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-primary-green: #4A7C59;
            --color-primary-green-dark: #3A6047;
            --color-secondary-green: #7FB069;
        }
        
        .modal-backdrop {
            animation: fadeIn 0.2s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            animation: slideUp 0.3s ease-out;
        }
        
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Leaf+Loom Admin</h1>
                        <p class="text-xs text-gray-500">Blogs Management</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($admin_name); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($admin_email); ?></p>
                    </div>
                    <a href="dashboard.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 p-2 rounded-lg transition-colors" title="Dashboard">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </a>
                    <a href="?logout=true" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Logout
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Blogs
                </a>
                <a href="orders.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Orders
                </a>
                <a href="customers.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Customers
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            
            <!-- Success/Error Messages -->
            <?php if (isset($_GET['deleted'])): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                <p class="text-green-700 font-medium">✓ Blog deleted successfully!</p>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <p class="text-red-700 font-medium"><?php echo htmlspecialchars($_GET['error']); ?></p>
            </div>
            <?php endif; ?>

            <!-- Page Header with Add Button -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Blogs Management</h2>
                    <p class="text-gray-600">Create and manage your blog content</p>
                </div>
                <button onclick="openAddBlogModal()" class="bg-primary-green hover:bg-primary-green-dark text-white font-semibold px-6 py-3 rounded-lg transition-colors flex items-center gap-2 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add New Blog
                </button>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-blue-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_blogs; ?></h3>
                    <p class="text-gray-500 text-sm">Total Blogs</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-green-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $published_blogs; ?></h3>
                    <p class="text-gray-500 text-sm">Published</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-amber-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $draft_blogs; ?></h3>
                    <p class="text-gray-500 text-sm">Drafts</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-purple-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($total_views); ?></h3>
                    <p class="text-gray-500 text-sm">Total Views</p>
                </div>
            </div>

            <!-- Blogs Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">All Blog Posts</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Blog Post</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Author</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Views</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($blogs)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                            </svg>
                                            <p class="text-lg font-medium mb-2">No blogs found</p>
                                            <p class="text-sm text-gray-400">Click "Add New Blog" to create your first blog post</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($blogs as $blog): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-16 w-24 bg-gray-200 rounded overflow-hidden">
                                                <img src="../<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                                     class="h-full w-full object-cover"
                                                     onerror="this.src='../images/placeholder.jpg'">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo truncateText(htmlspecialchars($blog['title']), 50); ?>
                                                    <?php if ($blog['is_featured']): ?>
                                                        <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Featured</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1"><?php echo formatDate($blog['created_at']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($blog['category']); ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-primary-green text-white flex items-center justify-center text-xs font-semibold">
                                                <?php echo strtoupper(substr($blog['author_name'], 0, 2)); ?>
                                            </div>
                                            <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($blog['author_name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            <?php echo number_format($blog['views_count']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
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
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- View Button -->
                                            <button onclick="viewBlog(<?php echo $blog['id']; ?>)" 
                                                    class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition-colors" 
                                                    title="View Blog">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                            
                                            <!-- Edit Button -->
                                            <button onclick="editBlog(<?php echo $blog['id']; ?>)" 
                                                    class="bg-amber-100 hover:bg-amber-200 text-amber-700 p-2 rounded-lg transition-colors" 
                                                    title="Edit Blog">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            
                                            <!-- Delete Button -->
                                            <button onclick="confirmDeleteBlog(
                                                <?php echo $blog['id']; ?>, 
                                                '<?php echo htmlspecialchars($blog['title'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($blog['featured_image'], ENT_QUOTES); ?>'
                                            )" 
                                                    class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition-colors" 
                                                    title="Delete Blog">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Add Blog Modal -->
    <div id="addBlogModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 modal-backdrop">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full modal-content max-h-[90vh] overflow-y-auto">
                <div class="bg-gradient-to-r from-primary-green to-secondary-green p-6 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-white">Add New Blog Post</h3>
                        <button onclick="closeAddBlogModal()" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <form action="admin-add-blog.php" method="POST" class="p-6 space-y-4">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Blog Title *</label>
                        <input type="text" name="title" required
                               placeholder="Enter blog title"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Featured Image URL *</label>
                        <input type="text" name="featured_image" required
                               placeholder="images/blog/your-image.jpg"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Excerpt / Short Description *</label>
                        <textarea name="excerpt" rows="2" required
                                  placeholder="Brief description of the blog post"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Blog Content *</label>
                        <textarea name="content" rows="8" required
                                  placeholder="Write your blog content here..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Author Name *</label>
                            <input type="text" name="author_name" required
                                   value="<?php echo htmlspecialchars($admin_name); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                            <input type="text" name="category" required
                                   placeholder="e.g., Sustainability, Hair Care"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tags</label>
                            <input type="text" name="tags"
                                   placeholder="tag1, tag2, tag3"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Reading Time (minutes) *</label>
                            <input type="number" name="reading_time" value="5" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                            <select name="status" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        
                        <div class="flex items-center pt-7">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1"
                                       class="w-5 h-5 text-primary-green border-gray-300 rounded focus:ring-primary-green">
                                <span class="text-sm font-medium text-gray-700">Featured Blog Post</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeAddBlogModal()"
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 bg-primary-green hover:bg-primary-green-dark text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                            Create Blog Post
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteBlogModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 modal-backdrop">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full modal-content">
                
                <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 rounded-t-2xl">
                    <div class="flex items-center gap-4">
                        <div class="bg-white bg-opacity-20 rounded-full p-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white">Delete Blog?</h3>
                            <p class="text-red-100 text-sm">This action cannot be undone</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="mb-6">
                        <p class="text-gray-700 mb-4">Are you sure you want to delete this blog post?</p>
                        
                        <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-red-500">
                            <div class="flex items-center gap-3">
                                <img id="deleteBlogImage" src="" alt="Blog" class="w-16 h-16 object-cover rounded-lg">
                                <div>
                                    <p class="font-semibold text-gray-800" id="deleteBlogTitle"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded mb-6">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-amber-800">Warning!</p>
                                <p class="text-sm text-amber-700">This will permanently delete the blog post and all associated data.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" onclick="closeDeleteBlogModal()"
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-all hover:scale-105">
                            Cancel
                        </button>
                        <button type="button" onclick="confirmDeleteBlogFinal()"
                                class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-3 px-6 rounded-lg transition-all hover:scale-105 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Form (Hidden) -->
    <form id="deleteBlogForm" method="POST" style="display:none;">
        <input type="hidden" name="delete_blog" value="1">
        <input type="hidden" id="delete_blog_id" name="blog_id">
    </form>

    <!-- Toast Notification -->
    <div id="toastNotification" class="hidden fixed top-4 right-4 z-50 animate-slide-in">
        <div id="toastContent" class="bg-white rounded-lg shadow-2xl border-l-4 p-4 max-w-sm">
            <!-- Content will be injected by JavaScript -->
        </div>
    </div>

    <script>
        // Store blog info for deletion
        let deleteBlogData = {
            id: null,
            title: '',
            image: ''
        };

        // Open Add Blog Modal
        function openAddBlogModal() {
            document.getElementById('addBlogModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Close Add Blog Modal
        function closeAddBlogModal() {
            document.getElementById('addBlogModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // View Blog
        function viewBlog(blogId) {
            window.location.href = `admin-view-blog.php?id=${blogId}`;
        }

        // Edit Blog
        function editBlog(blogId) {
            window.location.href = `admin-edit-blog.php?id=${blogId}`;
        }

        // Open Delete Blog Modal
        function confirmDeleteBlog(blogId, blogTitle, blogImage) {
            deleteBlogData = {
                id: blogId,
                title: blogTitle,
                image: blogImage
            };
            
            document.getElementById('deleteBlogTitle').textContent = blogTitle;
            document.getElementById('deleteBlogImage').src = '../' + blogImage;
            
            document.getElementById('deleteBlogModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Close Delete Blog Modal
        function closeDeleteBlogModal() {
            document.getElementById('deleteBlogModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            
            deleteBlogData = {
                id: null,
                title: '',
                image: ''
            };
        }

        // Confirm Delete and Submit Form
        function confirmDeleteBlogFinal() {
            if (deleteBlogData.id) {
                document.getElementById('delete_blog_id').value = deleteBlogData.id;
                closeDeleteBlogModal();
                showToast('Deleting blog...', 'loading');
                
                setTimeout(() => {
                    document.getElementById('deleteBlogForm').submit();
                }, 500);
            }
        }

        // Toast Notification System
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toastNotification');
            const content = document.getElementById('toastContent');
            
            let icon = '';
            let borderColor = '';
            
            switch(type) {
                case 'success':
                    icon = `<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>`;
                    borderColor = 'border-green-500';
                    break;
                case 'error':
                    icon = `<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>`;
                    borderColor = 'border-red-500';
                    break;
                case 'loading':
                    icon = `<svg class="w-6 h-6 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>`;
                    borderColor = 'border-blue-500';
                    break;
            }
            
            content.className = `bg-white rounded-lg shadow-2xl border-l-4 ${borderColor} p-4 max-w-sm`;
            content.innerHTML = `
                <div class="flex items-center gap-3">
                    ${icon}
                    <p class="text-gray-800 font-medium">${message}</p>
                </div>
            `;
            
            toast.classList.remove('hidden');
            
            if (type !== 'loading') {
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 3000);
            }
        }

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddBlogModal();
                closeDeleteBlogModal();
            }
        });

        // Close modals on backdrop click
        document.getElementById('addBlogModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddBlogModal();
            }
        });

        document.getElementById('deleteBlogModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteBlogModal();
            }
        });

        // Show success/error messages on page load
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.has('deleted')) {
                showToast('Blog deleted successfully!', 'success');
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (urlParams.has('error')) {
                showToast(urlParams.get('error'), 'error');
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>

</body>
</html>
