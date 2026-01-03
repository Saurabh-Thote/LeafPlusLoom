<?php
/**
 * Leaf+Loom Admin - View Product Details
 * Display complete product information
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

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header("Location: admin-products-list.php");
    exit;
}

// Fetch product details
try {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header("Location: admin-products-list.php?error=Product not found");
        exit;
    }
    
    // Fetch product benefits
    $stmt = $conn->prepare("SELECT * FROM product_benefits WHERE product_id = :id ORDER BY display_order");
    $stmt->execute(['id' => $product_id]);
    $benefits = $stmt->fetchAll();
    
    // Fetch product specifications
    $stmt = $conn->prepare("SELECT * FROM product_specifications WHERE product_id = :id ORDER BY display_order");
    $stmt->execute(['id' => $product_id]);
    $specifications = $stmt->fetchAll();
    
    // Fetch product features
    $stmt = $conn->prepare("SELECT * FROM product_features WHERE product_id = :id ORDER BY display_order");
    $stmt->execute(['id' => $product_id]);
    $features = $stmt->fetchAll();
    
    // Fetch product gallery
    $stmt = $conn->prepare("SELECT * FROM product_gallery WHERE product_id = :id ORDER BY display_order");
    $stmt->execute(['id' => $product_id]);
    $gallery = $stmt->fetchAll();
    
    // Fetch product reviews
    $stmt = $conn->prepare("SELECT * FROM product_reviews WHERE product_id = :id ORDER BY created_at DESC LIMIT 5");
    $stmt->execute(['id' => $product_id]);
    $reviews = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $error_message = "Error fetching product: " . $e->getMessage();
}

// Calculate discount percentage
$discount_percentage = 0;
if ($product['original_price'] && $product['original_price'] > $product['price']) {
    $discount_percentage = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Product - <?php echo htmlspecialchars($product['title']); ?> - Leaf+Loom Admin</title>
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
                    <a href="admin-products-list.php" class="text-gray-600 hover:text-primary-green transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Product Details</h1>
                        <p class="text-xs text-gray-500">Viewing product information</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="admin-edit-product.php?id=<?php echo $product['id']; ?>" 
                       class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Product
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
            
            <!-- Left Column - Product Image & Gallery -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-4">
                        <img src="<?php echo htmlspecialchars($product['main_image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['title']); ?>"
                             class="w-full h-full object-cover"
                             onerror="this.src='../images/placeholder.jpg'">
                    </div>
                    
                    <!-- Gallery Images -->
                    <?php if (!empty($gallery)): ?>
                    <div class="grid grid-cols-4 gap-2">
                        <?php foreach ($gallery as $image): ?>
                        <div class="aspect-square bg-gray-100 rounded overflow-hidden">
                            <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($image['image_alt']); ?>"
                                 class="w-full h-full object-cover"
                                 onerror="this.src='../images/placeholder.jpg'">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Quick Stats -->
                    <div class="mt-6 pt-6 border-t border-gray-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Product ID:</span>
                            <span class="text-sm font-semibold text-gray-800">#<?php echo $product['id']; ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">SKU:</span>
                            <span class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($product['sku']); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Status:</span>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $product['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($product['status']); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Created:</span>
                            <span class="text-sm font-semibold text-gray-800"><?php echo date('M d, Y', strtotime($product['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Product Details -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Product Info Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($product['title']); ?></h1>
                            <p class="text-gray-600"><?php echo htmlspecialchars($product['short_description']); ?></p>
                        </div>
                        <div class="flex gap-2">
                            <?php if ($product['is_featured']): ?>
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Featured</span>
                            <?php endif; ?>
                            <?php if ($product['is_new_arrival']): ?>
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">New</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="flex items-baseline gap-4 mb-4">
                        <span class="text-4xl font-bold text-primary-green">₹<?php echo number_format($product['price'], 2); ?></span>
                        <?php if ($product['original_price'] && $discount_percentage > 0): ?>
                            <span class="text-xl text-gray-500 line-through">₹<?php echo number_format($product['original_price'], 2); ?></span>
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                <?php echo $discount_percentage; ?>% OFF
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Category</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($product['category']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Stock Quantity</p>
                            <p class="font-semibold <?php echo $product['stock_quantity'] < 10 ? 'text-amber-600' : 'text-gray-800'; ?>">
                                <?php echo $product['stock_quantity']; ?> units
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Stock Status</p>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $product['stock_status'] === 'in-stock' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $product['stock_status'] === 'in-stock' ? 'In Stock' : 'Out of Stock'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($product['average_rating'] > 0): ?>
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-200 mt-4">
                        <div class="text-amber-500 text-lg">
                            <?php 
                            $fullStars = floor($product['average_rating']);
                            $halfStar = ($product['average_rating'] - $fullStars) >= 0.5 ? 1 : 0;
                            $emptyStars = 5 - $fullStars - $halfStar;
                            echo str_repeat('⭐', $fullStars);
                            echo $halfStar ? '⭐' : '';
                            echo str_repeat('☆', $emptyStars);
                            ?>
                        </div>
                        <span class="text-sm text-gray-600">
                            <?php echo number_format($product['average_rating'], 1); ?> 
                            (<?php echo $product['total_reviews']; ?> reviews)
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Description Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Product Description</h2>
                    <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <!-- Benefits Card -->
                <?php if (!empty($benefits)): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Key Benefits</h2>
                    <div class="space-y-3">
                        <?php foreach ($benefits as $benefit): ?>
                        <div class="flex items-start gap-3">
                            <span class="text-primary-green text-xl flex-shrink-0"><?php echo htmlspecialchars($benefit['benefit_icon']); ?></span>
                            <p class="text-gray-700"><?php echo htmlspecialchars($benefit['benefit_text']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Specifications Card -->
                <?php if (!empty($specifications)): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Specifications</h2>
                    <table class="w-full">
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($specifications as $spec): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 pr-8 font-semibold text-gray-800"><?php echo htmlspecialchars($spec['spec_name']); ?></td>
                                <td class="py-3 text-gray-700"><?php echo htmlspecialchars($spec['spec_value']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <!-- Features Card -->
                <?php if (!empty($features)): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Features</h2>
                    <div class="space-y-4">
                        <?php foreach ($features as $feature): ?>
                        <div class="border-l-4 border-primary-green pl-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-2xl"><?php echo htmlspecialchars($feature['feature_icon']); ?></span>
                                <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($feature['feature_title']); ?></h3>
                            </div>
                            <p class="text-gray-700 text-sm"><?php echo htmlspecialchars($feature['feature_content']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Reviews Card -->
                <?php if (!empty($reviews)): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Reviews (<?php echo count($reviews); ?>)</h2>
                    <div class="space-y-4">
                        <?php foreach ($reviews as $review): ?>
                        <div class="border-b border-gray-200 pb-4 last:border-0">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($review['customer_name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></p>
                                </div>
                                <div class="text-amber-500">
                                    <?php echo str_repeat('⭐', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                </div>
                            </div>
                            <p class="font-semibold text-sm text-gray-800 mb-1"><?php echo htmlspecialchars($review['review_title']); ?></p>
                            <p class="text-gray-700 text-sm"><?php echo htmlspecialchars($review['review_text']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        
    </main>

</body>
</html>
