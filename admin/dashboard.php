<?php
/**
 * Leaf+Loom Admin Dashboard
 * Main admin control panel after successful login
 */

// Set page title
$page_title = "Dashboard";

// Include header with sidebar
include 'includes/admin-header.php';
include 'includes/admin-sidebar.php';

// Fetch dashboard statistics
try {
    // Count total products
    $stmt = $conn->query("SELECT COUNT(*) as total FROM products");
    $total_products = $stmt->fetch()['total'];
    
    // Count total blogs
    $stmt = $conn->query("SELECT COUNT(*) as total FROM blogs WHERE status = 'published'");
    $total_blogs = $stmt->fetch()['total'];
    
    // Count total orders (if orders table exists)
    $total_orders = 0;
    try {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM orders");
        $total_orders = $stmt->fetch()['total'];
    } catch(PDOException $e) {
        // Orders table doesn't exist yet
    }
    
    // Count total customers (if customers table exists)
    $total_customers = 0;
    try {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM customers");
        $total_customers = $stmt->fetch()['total'];
    } catch(PDOException $e) {
        // Customers table doesn't exist yet
    }
    
    // Get recent products
    $stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
    $recent_products = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $error_message = "Error fetching data: " . $e->getMessage();
}
?>

<!-- Main Content Starts Here -->
<main class="flex-1 p-8">
    
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-primary-green to-secondary-green rounded-xl p-8 text-white mb-8">
        <h2 class="text-3xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars(explode(' ', $admin_name)[0]); ?>! 👋</h2>
        <p class="text-green-100">Here's what's happening with your store today.</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Total Products -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-blue-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <span class="text-green-600 text-sm font-semibold">+12%</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1"><?php echo $total_products; ?></h3>
            <p class="text-gray-500 text-sm">Total Products</p>
        </div>
        
        <!-- Total Blogs -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-purple-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332-.477-4.5-1.253"></path>
                    </svg>
                </div>
                <span class="text-green-600 text-sm font-semibold">+8%</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1"><?php echo $total_blogs; ?></h3>
            <p class="text-gray-500 text-sm">Published Blogs</p>
        </div>
        
        <!-- Total Orders -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-green-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <span class="text-green-600 text-sm font-semibold">+23%</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1"><?php echo $total_orders; ?></h3>
            <p class="text-gray-500 text-sm">Total Orders</p>
        </div>
        
        <!-- Revenue -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-amber-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-green-600 text-sm font-semibold">+15%</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">₹0</h3>
            <p class="text-gray-500 text-sm">Total Revenue</p>
        </div>
    </div>
    
    <!-- Recent Products Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Recent Products</h3>
            <a href="admin-products-list.php" class="text-primary-green hover:text-primary-green-dark text-sm font-medium">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (!empty($recent_products)): ?>
                        <?php foreach ($recent_products as $product): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img src="../<?php echo htmlspecialchars($product['main_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['title']); ?>"
                                         class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded object-cover"
                                         onerror="this.src='../images/products/default.jpg'">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['title']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($product['category']); ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">₹<?php echo number_format($product['price'], 2); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo $product['stock_quantity']; ?></td>
                            <td class="px-6 py-4">
                                <?php if ($product['stock_status'] === 'in-stock'): ?>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">In Stock</span>
                                <?php else: ?>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No products found. <a href="admin-add-product.php" class="text-primary-green hover:underline">Add your first product</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php include 'includes/admin-footer.php'; ?>
