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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Leaf+Loom Admin</h1>
                        <p class="text-xs text-gray-500">Blogs Management</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-800">
                            <?php echo htmlspecialchars($admin_name); ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            <?php echo htmlspecialchars($admin_email); ?>
                        </p>
                    </div>
                    <a href="dashboard.php"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 p-2 rounded-lg transition-colors"
                        title="Dashboard">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                    </a>
                    <a href="?logout=true"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
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
                <a href="dashboard.php"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    Dashboard
                </a>
                <a href="admin-products-list.php"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Products
                </a>
                <a href="admin-blogs.php"
                    class="flex items-center gap-3 px-4 py-3 bg-primary-green text-white rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                    Blogs
                </a>
                <a href="orders.php"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Orders
                </a>
                <a href="customers.php"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
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
                <p class="text-red-700 font-medium">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Page Header with Add Button -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Blogs Management</h2>
                    <p class="text-gray-600">Create and manage your blog content</p>
                </div>
                <button onclick="openAddBlogModal()"
                    class="bg-primary-green hover:bg-primary-green-dark text-white font-semibold px-6 py-3 rounded-lg transition-colors flex items-center gap-2 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800">
                        <?php echo $total_blogs; ?>
                    </h3>
                    <p class="text-gray-500 text-sm">Total Blogs</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-green-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800">
                        <?php echo $published_blogs; ?>
                    </h3>
                    <p class="text-gray-500 text-sm">Published</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-amber-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800">
                        <?php echo $draft_blogs; ?>
                    </h3>
                    <p class="text-gray-500 text-sm">Drafts</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-purple-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800">
                        <?php echo number_format($total_views); ?>
                    </h3>
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
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Blog Post
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Category
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Author
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Views</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($blogs)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                            </path>
                                        </svg>
                                        <p class="text-lg font-medium mb-2">No blogs found</p>
                                        <p class="text-sm text-gray-400">Click "Add New Blog" to create your first blog
                                            post</p>
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
                                                <span
                                                    class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Featured</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <?php echo formatDate($blog['created_at']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($blog['category']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center">
                                        <div
                                            class="h-8 w-8 rounded-full bg-primary-green text-white flex items-center justify-center text-xs font-semibold">
                                            <?php echo strtoupper(substr($blog['author_name'], 0, 2)); ?>
                                        </div>
                                        <span class="ml-2 text-gray-700">
                                            <?php echo htmlspecialchars($blog['author_name']); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                        <?php echo number_format($blog['views_count']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($blog['status'] === 'published'): ?>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Published
                                    </span>
                                    <?php elseif ($blog['status'] === 'draft'): ?>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Draft
                                    </span>
                                    <?php else: ?>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </button>

                                        <!-- Edit Button -->
                                        <button onclick="editBlog(<?php echo $blog['id']; ?>)"
                                            class="bg-amber-100 hover:bg-amber-200 text-amber-700 p-2 rounded-lg transition-colors"
                                            title="Edit Blog">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
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

    <!-- Add Blog Modal with Rich Text Editor -->
    <div id="addBlogModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 modal-backdrop overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="bg-white rounded-2xl shadow-2xl max-w-6xl w-full modal-content my-8">

                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-primary-green to-secondary-green p-6 rounded-t-2xl sticky top-0 z-10">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-white">Add New Blog Post</h3>
                            <p class="text-green-100 text-sm mt-1">Create SEO-optimized content for your blog</p>
                        </div>
                        <button onclick="closeAddBlogModal()" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <form action="admin-add-blog.php" method="POST" enctype="multipart/form-data" class="p-6">

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        <!-- Left Column - Main Content -->
                        <div class="lg:col-span-2 space-y-6">

                            <!-- Blog Title -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Blog Title *</label>
                                <input type="text" id="blogTitle" name="title" required
                                    placeholder="Enter an engaging blog title..." oninput="updateSEOAnalysis()"
                                    class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">
                                    <span id="titleCount">0</span>/60 characters (Optimal: 50-60)
                                </p>
                            </div>

                            <!-- Permalink/Slug -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Permalink
                                    (Auto-generated)</label>
                                <div
                                    class="flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-lg border border-gray-200">
                                    <span class="text-gray-500 text-sm">https://leafplusloom.com/blog/</span>
                                    <span id="slugPreview"
                                        class="text-primary-green font-mono text-sm">your-blog-slug</span>
                                </div>
                                <input type="hidden" name="slug" id="slugInput" value="">
                            </div>

                            <!-- Rich Text Editor for Blog Content -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Blog Content *</label>

                                <!-- Editor Toolbar -->
                                <div class="border border-gray-300 rounded-t-lg bg-gray-50 p-2 flex flex-wrap gap-1">

                                    <!-- Text Formatting -->
                                    <div class="flex gap-1 border-r border-gray-300 pr-2">
                                        <button type="button" onclick="formatText('bold')" title="Bold (Ctrl+B)"
                                            class="editor-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z">
                                                </path>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="formatText('italic')" title="Italic (Ctrl+I)"
                                            class="editor-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 4h4l-4 16h-4"></path>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="formatText('underline')"
                                            title="Underline (Ctrl+U)" class="editor-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 19v-7m0 0V5m0 7c0 2.21-1.79 4-4 4s-4-1.79-4-4m8 0c0 2.21 1.79 4 4 4s4-1.79 4-4M5 19h14">
                                                </path>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="formatText('strikethrough')"
                                            title="Strikethrough" class="editor-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 12h18M8 5h8M9 19h6"></path>
                                            </svg>
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
                                        <button type="button" onclick="formatText('insertUnorderedList')"
                                            title="Bullet List" class="editor-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 6h16M4 12h16M4 18h16"></path>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="formatText('insertOrderedList')"
                                            title="Numbered List" class="editor-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5h12M9 12h12M9 19h12M3 7l1-1v8"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Alignment -->
                                    <div class="flex gap-1 border-r border-gray-300 pr-2">
                                        <button type="button" onclick="formatText('justifyLeft')" title="Align Left"
                                            class="editor-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 6h18M3 12h12M3 18h18"></path>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="formatText('justifyCenter')" title="Align Center"
                                            class="editor-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 6h18M6 12h12M3 18h18"></path>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="formatText('justifyRight')" title="Align Right"
                                            class="editor-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 6h18M9 12h12M3 18h18"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Links & Media -->
                                    <div class="flex gap-1 border-r border-gray-300 pr-2">
                                        <button type="button" onclick="insertLink()" title="Insert Link"
                                            class="editor-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                                </path>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="document.getElementById('imageUpload').click()"
                                            title="Insert Image" class="editor-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </button>
                                        <input type="file" id="imageUpload" accept="image/" class="hidden"
                                            onchange="insertImage(event)">
                                    </div>

                                    <!-- Text Color -->
                                    <div class="flex gap-1 border-r border-gray-300 pr-2">
                                        <input type="color" id="textColor"
                                            onchange="formatText('foreColor', this.value)" title="Text Color"
                                            class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                        <input type="color" id="bgColor"
                                            onchange="formatText('hiliteColor', this.value)" title="Background Color"
                                            class="w-8 h-8 border border-gray-300 rounded cursor-pointer">
                                    </div>

                                    <!-- Undo/Redo -->
                                    <div class="flex gap-1">
                                        <button type="button" onclick="formatText('undo')" title="Undo"
                                            class="editor-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="formatText('redo')" title="Redo"
                                            class="editor-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 10h-10a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Editor Content Area -->
                                <div id="editor" contenteditable="true"
                                    oninput="syncEditorContent(); updateSEOAnalysis();"
                                    class="border border-t-0 border-gray-300 rounded-b-lg p-4 min-h-[400px] max-h-[500px] overflow-y-auto bg-white prose prose-sm max-w-none focus:outline-none focus:ring-2 focus:ring-primary-green"
                                    placeholder="Start writing your blog content here">
                                </div>
                                <textarea name="content" id="contentHidden" required class="hidden"></textarea>

                                <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                                    <span>Word count: <strong id="wordCount">0</strong> words</span>
                                    <span>Character count: <strong id="charCount">0</strong> characters</span>
                                </div>
                            </div>

                            <!-- Excerpt -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Excerpt / Meta Description
                                    *</label>
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
                            <div
                                class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
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
                                        <div id="seoProgress" class="h-full bg-gray-400 transition-all duration-500"
                                            style="width: 0%"></div>
                                    </div>
                                    <p id="seoStatus" class="text-xs text-gray-500 mt-2">Start writing to see SEO
                                        analysis</p>
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
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <span class="flex items-center gap-2">
                                        Focus Keyword
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </span>
                                </label>
                                <input type="text" name="focus_keyword" id="focusKeyword" oninput="updateSEOAnalysis()"
                                    placeholder="e.g., eco-friendly products"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Main keyword you want to rank for</p>
                            </div>

                            <!-- Featured Image -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Featured Image *</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary-green transition-colors cursor-pointer"
                                    onclick="document.getElementById('featuredImageInput').click()">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <p class="text-sm text-gray-600">Click to upload image</p>
                                    <p class="text-xs text-gray-400 mt-1">or enter URL below</p>
                                </div>
                                <input type="file" id="featuredImageInput" accept="image/" class="hidden">
                                <input type="text" name="featured_image" id="featuredImage" required
                                    placeholder="images/blog/your-image.jpg"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent mt-2">
                            </div>

                            <!-- Author -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Author Name *</label>
                                <input type="text" name="author_name" required
                                    value="<?php echo htmlspecialchars($admin_name); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                                <select name="category" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                    <option value="">Select Category</option>
                                    <option value="Sustainability">Sustainability</option>
                                    <option value="Hair Care">Hair Care</option>
                                    <option value="Lifestyle">Lifestyle</option>
                                    <option value="DIY & Crafts">DIY & Crafts</option>
                                    <option value="Product Reviews">Product Reviews</option>
                                    <option value="Eco Tips">Eco Tips</option>
                                </select>
                            </div>

                            <!-- Tags -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tags</label>
                                <input type="text" name="tags" placeholder="bamboo, eco-friendly, sustainable"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Separate with commas</p>
                            </div>

                            <!-- Reading Time -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Reading Time *</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="reading_time" id="readingTime" value="5" required min="1"
                                        max="60"
                                        class="w-20 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                    <span class="text-sm text-gray-600">minutes</span>
                                    <button type="button" onclick="calculateReadingTime()"
                                        class="text-xs text-primary-green hover:text-primary-green-dark ml-auto">
                                        Auto Calculate
                                    </button>
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                                <select name="status" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>

                            <!-- Featured Checkbox -->
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_featured" value="1" id="isFeatured"
                                    class="w-5 h-5 text-primary-green border-gray-300 rounded focus:ring-primary-green">
                                <label for="isFeatured" class="text-sm font-medium text-gray-700 cursor-pointer">
                                    Mark as Featured Post
                                </label>
                            </div>

                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-6 mt-6 border-t border-gray-200">
                        <button type="button" onclick="closeAddBlogModal()"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-colors">
                            Cancel
                        </button>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
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
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-amber-800">Warning!</p>
                                <p class="text-sm text-amber-700">This will permanently delete the blog post and all
                                    associated data.</p>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
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
                reader.onload = function (e) {
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
        document.getElementById('blogTitle')?.addEventListener('input', function () {
            const title = this.value;
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();

            document.getElementById('slugPreview').textContent = slug || 'your-blog-slug';
            document.getElementById('slugInput').value = slug;

            // Update title character count
            document.getElementById('titleCount').textContent = title.length;
        });

        // Update excerpt character count
        document.getElementById('excerpt')?.addEventListener('input', function () {
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
            let checks = 0;
            let passed = 0;

            // Check 1: Title length (50-60 characters)
            checks++;
            const titleCheck = document.getElementById('seoTitle');
            if (title.length >= 50 && title.length <= 60) {
                titleCheck.innerHTML = '<span class="text-green-500">✓</span> <span class="text-gray-700">Title length optimal</span>';
                passed++;
                score += 20;
            } else if (title.length > 0) {
                titleCheck.innerHTML = '<span class="text-yellow-500">!</span> <span class="text-gray-600">Title should be 50-60 chars</span>';
                score += 10;
            } else {
                titleCheck.innerHTML = '<span class="text-gray-400">○</span> <span class="text-gray-600">Title length (50-60 chars)</span>';
            }

            // Check 2: Meta description (150-160 characters)
            checks++;
            const excerptCheck = document.getElementById('seoExcerpt');
            if (excerpt.length >= 150 && excerpt.length <= 160) {
                excerptCheck.innerHTML = '<span class="text-green-500">✓</span> <span class="text-gray-700">Meta description optimal</span>';
                passed++;
                score += 20;
            } else if (excerpt.length > 0) {
                excerptCheck.innerHTML = '<span class="text-yellow-500">!</span> <span class="text-gray-600">Description should be 150-160 chars</span>';
                score += 10;
            } else {
                excerptCheck.innerHTML = '<span class="text-gray-400">○</span> <span class="text-gray-600">Meta description (150-160)</span>';
            }

            // Check 3: Focus keyword present
            checks++;
            const keywordCheck = document.getElementById('seoKeyword');
            if (keyword && (title.toLowerCase().includes(keyword) || content.toLowerCase().includes(keyword))) {
                keywordCheck.innerHTML = '<span class="text-green-500">✓</span> <span class="text-gray-700">Focus keyword found</span>';
                passed++;
                score += 20;
            } else if (keyword) {
                keywordCheck.innerHTML = '<span class="text-red-500">✗</span> <span class="text-gray-600">Keyword not found in content</span>';
            } else {
                keywordCheck.innerHTML = '<span class="text-gray-400">○</span> <span class="text-gray-600">Focus keyword present</span>';
            }

            // Check 4: Content length (300+ words)
            checks++;
            const contentCheck = document.getElementById('seoContent');
            if (wordCount >= 300) {
                contentCheck.innerHTML = '<span class="text-green-500">✓</span> <span class="text-gray-700">Content length good (' + wordCount + ' words)</span>';
                passed++;
                score += 20;
            } else if (wordCount > 0) {
                contentCheck.innerHTML = '<span class="text-yellow-500">!</span> <span class="text-gray-600">Need ' + (300 - wordCount) + ' more words</span>';
                score += Math.round((wordCount / 300) * 20);
            } else {
                contentCheck.innerHTML = '<span class="text-gray-400">○</span> <span class="text-gray-600">Content length (300+ words)</span>';
            }

            // Check 5: Use of headings
            checks++;
            const headingsCheck = document.getElementById('seoHeadings');
            const hasHeadings = editor.querySelector('h1, h2, h3, h4');
            if (hasHeadings) {
                headingsCheck.innerHTML = '<span class="text-green-500">✓</span> <span class="text-gray-700">Headings present</span>';
                passed++;
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
        document.addEventListener('keydown', function (e) {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
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

            switch (type) {
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
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAddBlogModal();
                closeDeleteBlogModal();
            }
        });

        // Close modals on backdrop click
        document.getElementById('addBlogModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeAddBlogModal();
            }
        });

        document.getElementById('deleteBlogModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeDeleteBlogModal();
            }
        });

        // Show success/error messages on page load
        window.addEventListener('DOMContentLoaded', function () {
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