<?php
/**
 * Leaf+Loom Admin - Products Management
 * Complete CRUD operations with dynamic functionality
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
$admin_name = $_SESSION['admin_full_name'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? 'admin@leafplusloom.com';

// Handle Delete Product
if (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute(['id' => $product_id]);
        
        header("Location: admin-products-list.php?deleted=1");
        exit;
        
    } catch(PDOException $e) {
        header("Location: admin-products-list.php?error=" . urlencode("Error deleting product: " . $e->getMessage()));
        exit;
    }
}

// Fetch all products with error handling
try {
    $stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Error fetching products: " . $e->getMessage();
    $products = [];
}

// Calculate statistics
$total_products = count($products);
$in_stock_products = count(array_filter($products, fn($p) => $p['stock_status'] === 'in-stock'));
$low_stock_products = count(array_filter($products, fn($p) => $p['stock_quantity'] < 10 && $p['stock_quantity'] > 0));
$out_of_stock_products = count(array_filter($products, fn($p) => $p['stock_status'] === 'out-of-stock'));

// Calculate total inventory value
$total_value = array_sum(array_map(fn($p) => $p['price'] * $p['stock_quantity'], $products));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Leaf+Loom Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-primary-green: #4A7C59;
            --color-primary-green-dark: #3A6047;
            --color-secondary-green: #7FB069;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .modal-backdrop {
            animation: fadeIn 0.2s ease-out;
        }
        
        .modal-content {
            animation: slideUp 0.3s ease-out;
        }
        
        .toast-notification {
            animation: slideIn 0.3s ease-out;
        }
        
        .action-btn {
            @apply p-2 rounded-lg transition-all duration-200 transform hover:scale-110;
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Leaf+Loom Admin</h1>
                        <p class="text-xs text-gray-500">Products Management</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($admin_name); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($admin_email); ?></p>
                    </div>
                    <a href="../index.php" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 p-2 rounded-lg transition-colors" title="View Website">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
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
                <a href="admin-products-list.php" class="flex items-center gap-3 px-4 py-3 bg-primary-green text-white rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Products
                </a>
                <a href="admin-blogs.php" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
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
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded animate-slideIn">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-green-700 font-medium">✓ Product deleted successfully!</p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <p class="text-red-700 font-medium"><?php echo htmlspecialchars($_GET['error']); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['added'])): ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded animate-slideIn">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-blue-700 font-medium">✓ Product added successfully!</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Page Header with Add Button -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Products Management</h2>
                    <p class="text-gray-600">Manage your product catalog - Total Inventory Value: ₹<?php echo number_format($total_value, 2); ?></p>
                </div>
                <button onclick="openAddProductModal()" class="bg-primary-green hover:bg-primary-green-dark text-white font-semibold px-6 py-3 rounded-lg transition-all transform hover:scale-105 flex items-center gap-2 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add New Product
                </button>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-blue-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800"><?php echo $total_products; ?></h3>
                    <p class="text-gray-500 text-sm">Total Products</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-green-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800"><?php echo $in_stock_products; ?></h3>
                    <p class="text-gray-500 text-sm">In Stock</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-amber-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800"><?php echo $low_stock_products; ?></h3>
                    <p class="text-gray-500 text-sm">Low Stock Alert</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-red-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800"><?php echo $out_of_stock_products; ?></h3>
                    <p class="text-gray-500 text-sm">Out of Stock</p>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">All Products (<?php echo $total_products; ?>)</h3>
                    <div class="flex gap-2">
                        <button onclick="exportProducts()" class="text-sm bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg transition-colors">
                            📥 Export CSV
                        </button>
                        <input type="text" id="searchProducts" placeholder="Search products..." 
                               class="text-sm px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green"
                               onkeyup="searchTable()">
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full" id="productsTable">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                            </svg>
                                            <p class="text-lg font-medium mb-2">No products found</p>
                                            <p class="text-sm text-gray-400">Click "Add New Product" to create your first product</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-14 w-14 bg-gray-200 rounded-lg overflow-hidden">
                                                <img src="../<?php echo htmlspecialchars($product['main_image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['title']); ?>"
                                                     class="h-full w-full object-cover"
                                                     onerror="this.src='../images/placeholder.jpg'">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['title']); ?></div>
                                                <div class="text-xs text-gray-500">SKU: <?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
                                            <?php echo htmlspecialchars($product['category'] ?? 'Uncategorized'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <div class="flex flex-col">
                                            <span class="text-primary-green font-bold">₹<?php echo number_format($product['price'], 2); ?></span>
                                            <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                                <span class="text-xs text-gray-400 line-through">₹<?php echo number_format($product['original_price'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php 
                                        $stockClass = $product['stock_quantity'] < 10 ? 'text-amber-600 font-semibold' : 'text-gray-600';
                                        ?>
                                        <span class="<?php echo $stockClass; ?>">
                                            <?php echo $product['stock_quantity']; ?> units
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($product['stock_status'] === 'in-stock'): ?>
                                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                ● In Stock
                                            </span>
                                        <?php elseif ($product['stock_status'] === 'out-of-stock'): ?>
                                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                ● Out of Stock
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                ● Pre-order
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- View Button -->
                                            <button onclick="viewProduct(<?php echo $product['id']; ?>)" 
                                                    class="action-btn bg-blue-100 hover:bg-blue-200 text-blue-700" 
                                                    title="View Product Details">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                            
                                            <!-- Edit Button -->
                                            <button onclick="editProduct(<?php echo $product['id']; ?>)" 
                                                    class="action-btn bg-amber-100 hover:bg-amber-200 text-amber-700" 
                                                    title="Edit Product">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            
                                            <!-- Delete Button -->
                                            <button onclick="confirmDelete(
                                                <?php echo $product['id']; ?>, 
                                                '<?php echo htmlspecialchars($product['title'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($product['main_image'], ENT_QUOTES); ?>'
                                            )" 
                                                    class="action-btn bg-red-100 hover:bg-red-200 text-red-700" 
                                                    title="Delete Product">
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

    <!-- Add Product Modal -->
    <div id="addProductModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 modal-backdrop">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full modal-content max-h-[90vh] overflow-y-auto">
                <div class="bg-gradient-to-r from-primary-green to-secondary-green p-6 rounded-t-2xl sticky top-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-white">Add New Product</h3>
                            <p class="text-green-100 text-sm mt-1">Fill in the product details below</p>
                        </div>
                        <button onclick="closeAddProductModal()" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <form action="admin-add-product.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                    
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Basic Information</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Product Title *</label>
                                <input type="text" name="title" required
                                       placeholder="e.g., Round Handle Wooden Brush"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">SKU *</label>
                                <input type="text" name="sku" required
                                       placeholder="e.g., LLWB-001"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Short Description *</label>
                            <input type="text" name="short_description" required
                                   placeholder="Brief product description for listings"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Full Description *</label>
                            <textarea name="description" rows="4" required
                                      placeholder="Detailed product description..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent"></textarea>
                        </div>
                    </div>
                    
                    <!-- Product Details -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Product Details</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                                <select name="category" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                    <option value="">Select Category</option>
                                    <option value="Hair Care">Hair Care</option>
                                    <option value="Personal Care">Personal Care</option>
                                    <option value="Stationery">Stationery</option>
                                    <option value="Kitchen">Kitchen</option>
                                    <option value="Home Decor">Home Decor</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Main Image Path *</label>
                                <input type="text" name="main_image" required
                                       placeholder="images/products/product-image.jpg"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pricing & Inventory -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Pricing & Inventory</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Price (₹) *</label>
                                <input type="number" name="price" step="0.01" required min="0"
                                       placeholder="299.00"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Original Price (₹)</label>
                                <input type="number" name="original_price" step="0.01" min="0"
                                       placeholder="499.00"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Stock Quantity *</label>
                                <input type="number" name="stock_quantity" required min="0"
                                       placeholder="100"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Stock Status *</label>
                                <select name="stock_status" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                    <option value="in-stock">In Stock</option>
                                    <option value="out-of-stock">Out of Stock</option>
                                    <option value="pre-order">Pre-order</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Product Status *</label>
                                <select name="status" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Options -->
                    <div class="flex flex-wrap gap-4 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" class="w-5 h-5 text-primary-green border-gray-300 rounded focus:ring-primary-green">
                            <span class="text-sm font-medium text-gray-700">⭐ Featured Product</span>
                        </label>
                        
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_new_arrival" value="1" class="w-5 h-5 text-primary-green border-gray-300 rounded focus:ring-primary-green">
                            <span class="text-sm font-medium text-gray-700">🆕 New Arrival</span>
                        </label>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeAddProductModal()"
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 bg-primary-green hover:bg-primary-green-dark text-white font-semibold py-3 px-6 rounded-lg transition-all transform hover:scale-105">
                            ✓ Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enhanced Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 modal-backdrop">
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
                            <h3 class="text-2xl font-bold text-white">Delete Product?</h3>
                            <p class="text-red-100 text-sm">This action cannot be undone</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="mb-6">
                        <p class="text-gray-700 mb-4">Are you sure you want to delete this product?</p>
                        
                        <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-red-500">
                            <div class="flex items-center gap-3">
                                <img id="deleteProductImage" src="" alt="Product" class="w-16 h-16 object-cover rounded-lg">
                                <div>
                                    <p class="font-semibold text-gray-800" id="deleteProductName"></p>
                                    <p class="text-sm text-gray-500">Product ID: <span id="deleteProductId"></span></p>
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
                                <p class="text-sm text-amber-700">All product data, images, and reviews will be permanently deleted.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" onclick="closeDeleteModal()"
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-all hover:scale-105">
                            Cancel
                        </button>
                        <button type="button" onclick="executeDelete()"
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

    <!-- Hidden Delete Form -->
    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="delete_product" value="1">
        <input type="hidden" id="delete_product_id" name="product_id">
    </form>

    <!-- Toast Notification -->
    <div id="toast" class="hidden fixed top-4 right-4 z-50 toast-notification">
        <div id="toastContent" class="bg-white rounded-lg shadow-2xl border-l-4 p-4 max-w-sm">
            <!-- Content will be injected by JavaScript -->
        </div>
    </div>

    <script>
        // Store product info for deletion
        let deleteProductData = {
            id: null,
            name: '',
            image: ''
        };

        // Open Add Product Modal
        function openAddProductModal() {
            document.getElementById('addProductModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Close Add Product Modal
        function closeAddProductModal() {
            document.getElementById('addProductModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // View Product
        function viewProduct(productId) {
            window.location.href = `admin-view-product.php?id=${productId}`;
        }

        // Edit Product
        function editProduct(productId) {
            window.location.href = `admin-edit-product.php?id=${productId}`;
        }

        // Confirm Delete - Open Modal
        function confirmDelete(productId, productName, productImage) {
            deleteProductData = {
                id: productId,
                name: productName,
                image: productImage
            };
            
            document.getElementById('deleteProductId').textContent = productId;
            document.getElementById('deleteProductName').textContent = productName;
            document.getElementById('deleteProductImage').src = '../' + productImage;
            
            document.getElementById('deleteModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Close Delete Modal
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            
            deleteProductData = {
                id: null,
                name: '',
                image: ''
            };
        }

        // Execute Delete
        function executeDelete() {
            if (deleteProductData.id) {
                document.getElementById('delete_product_id').value = deleteProductData.id;
                closeDeleteModal();
                showToast('Deleting product...', 'loading');
                
                setTimeout(() => {
                    document.getElementById('deleteForm').submit();
                }, 500);
            }
        }

        // Search Table
        function searchTable() {
            const input = document.getElementById('searchProducts');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('productsTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell) {
                        const textValue = cell.textContent || cell.innerText;
                        if (textValue.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                row.style.display = found ? '' : 'none';
            }
        }

        // Export Products to CSV
        function exportProducts() {
            showToast('Exporting products to CSV...', 'loading');
            
            setTimeout(() => {
                showToast('Export feature coming soon!', 'info');
            }, 1000);
        }

        // Toast Notification System
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const content = document.getElementById('toastContent');
            
            let icon = '';
            let borderColor = '';
            
            switch(type) {
                case 'success':
                    icon = '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                    borderColor = 'border-green-500';
                    break;
                case 'error':
                    icon = '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                    borderColor = 'border-red-500';
                    break;
                case 'loading':
                    icon = '<svg class="w-6 h-6 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                    borderColor = 'border-blue-500';
                    break;
                case 'info':
                    icon = '<svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
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
                closeAddProductModal();
                closeDeleteModal();
            }
        });

        // Close modals on backdrop click
        document.getElementById('addProductModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddProductModal();
            }
        });

        document.getElementById('deleteModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Show success/error messages on page load
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.has('deleted')) {
                showToast('Product deleted successfully!', 'success');
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (urlParams.has('added')) {
                showToast('Product added successfully!', 'success');
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
