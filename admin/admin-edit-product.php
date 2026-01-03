<?php
/**
 * Leaf+Loom Admin - Edit Product
 * Update product information
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    try {
        $stmt = $conn->prepare("
            UPDATE products SET
                title = :title,
                slug = :slug,
                description = :description,
                short_description = :short_description,
                main_image = :main_image,
                price = :price,
                original_price = :original_price,
                discount_percentage = :discount_percentage,
                stock_quantity = :stock_quantity,
                stock_status = :stock_status,
                sku = :sku,
                category = :category,
                tags = :tags,
                is_featured = :is_featured,
                is_new_arrival = :is_new_arrival,
                status = :status
            WHERE id = :id
        ");
        
        // Generate slug from title
        $slug = strtolower(str_replace(' ', '-', $_POST['title']));
        
        // Calculate discount percentage
        $discount = 0;
        if ($_POST['original_price'] && $_POST['original_price'] > $_POST['price']) {
            $discount = round((($_POST['original_price'] - $_POST['price']) / $_POST['original_price']) * 100);
        }
        
        $stmt->execute([
            'title' => $_POST['title'],
            'slug' => $slug,
            'description' => $_POST['description'],
            'short_description' => $_POST['short_description'],
            'main_image' => $_POST['main_image'],
            'price' => $_POST['price'],
            'original_price' => $_POST['original_price'] ?: null,
            'discount_percentage' => $discount,
            'stock_quantity' => $_POST['stock_quantity'],
            'stock_status' => $_POST['stock_status'],
            'sku' => $_POST['sku'],
            'category' => $_POST['category'],
            'tags' => $_POST['tags'] ?? '',
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_new_arrival' => isset($_POST['is_new_arrival']) ? 1 : 0,
            'status' => $_POST['status'],
            'id' => $product_id
        ]);
        
        $success_message = "Product updated successfully!";
        
    } catch(PDOException $e) {
        $error_message = "Error updating product: " . $e->getMessage();
    }
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
    
} catch(PDOException $e) {
    $error_message = "Error fetching product: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - <?php echo htmlspecialchars($product['title']); ?> - Leaf+Loom Admin</title>
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
                        <h1 class="text-xl font-bold text-gray-800">Edit Product</h1>
                        <p class="text-xs text-gray-500">Update product information</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="admin-view-product.php?id=<?php echo $product['id']; ?>" 
                       class="text-gray-600 hover:text-primary-green transition-colors">View Product</a>
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
            <p class="text-green-700 font-medium"><?php echo htmlspecialchars($success_message); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
            <p class="text-red-700 font-medium"><?php echo htmlspecialchars($error_message); ?></p>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column - Current Product Preview -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Current Product</h3>
                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-4">
                        <img src="<?php echo htmlspecialchars($product['main_image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['title']); ?>"
                             class="w-full h-full object-cover"
                             onerror="this.src='../images/placeholder.jpg'">
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($product['title']); ?></h4>
                    <p class="text-sm text-gray-600 mb-3"><?php echo htmlspecialchars($product['short_description']); ?></p>
                    <div class="space-y-2 pt-3 border-t border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Price:</span>
                            <span class="font-semibold text-primary-green">₹<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Stock:</span>
                            <span class="font-semibold"><?php echo $product['stock_quantity']; ?> units</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Status:</span>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $product['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst($product['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Edit Form -->
            <div class="lg:col-span-2">
                <form method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                    
                    <input type="hidden" name="update_product" value="1">
                    
                    <!-- Basic Information -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">Basic Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Product Title *</label>
                                <input type="text" name="title" required
                                       value="<?php echo htmlspecialchars($product['title']); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">SKU *</label>
                                <input type="text" name="sku" required
                                       value="<?php echo htmlspecialchars($product['sku']); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Short Description *</label>
                            <input type="text" name="short_description" required
                                   value="<?php echo htmlspecialchars($product['short_description']); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Full Description *</label>
                            <textarea name="description" rows="6" required
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                                <input type="text" name="category" required
                                       value="<?php echo htmlspecialchars($product['category']); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tags</label>
                                <input type="text" name="tags"
                                       value="<?php echo htmlspecialchars($product['tags']); ?>"
                                       placeholder="comma, separated, tags"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Images -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">Images</h2>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Main Image URL *</label>
                            <input type="text" name="main_image" required
                                   value="<?php echo htmlspecialchars($product['main_image']); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Enter the full URL or path to the main product image</p>
                        </div>
                    </div>
                    
                    <!-- Pricing & Stock -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">Pricing & Stock</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Price (₹) *</label>
                                <input type="number" name="price" step="0.01" required
                                       value="<?php echo $product['price']; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Original Price (₹)</label>
                                <input type="number" name="original_price" step="0.01"
                                       value="<?php echo $product['original_price']; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Leave empty if no discount</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Stock Quantity *</label>
                                <input type="number" name="stock_quantity" required
                                       value="<?php echo $product['stock_quantity']; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Stock Status *</label>
                                <select name="stock_status" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                    <option value="in-stock" <?php echo $product['stock_status'] === 'in-stock' ? 'selected' : ''; ?>>In Stock</option>
                                    <option value="out-of-stock" <?php echo $product['stock_status'] === 'out-of-stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                    <option value="pre-order" <?php echo $product['stock_status'] === 'pre-order' ? 'selected' : ''; ?>>Pre-Order</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Product Status *</label>
                                <select name="status" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent">
                                    <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="draft" <?php echo $product['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Attributes -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">Product Attributes</h2>
                        
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" 
                                       <?php echo $product['is_featured'] ? 'checked' : ''; ?>
                                       class="w-5 h-5 text-primary-green border-gray-300 rounded focus:ring-primary-green">
                                <span class="text-sm font-medium text-gray-700">Featured Product</span>
                            </label>
                            
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_new_arrival" value="1" 
                                       <?php echo $product['is_new_arrival'] ? 'checked' : ''; ?>
                                       class="w-5 h-5 text-primary-green border-gray-300 rounded focus:ring-primary-green">
                                <span class="text-sm font-medium text-gray-700">New Arrival</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-4 pt-6 border-t border-gray-200">
                        <a href="admin-products-list.php" 
                           class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition-colors text-center">
                            Cancel
                        </a>
                        <button type="submit"
                                class="flex-1 bg-primary-green hover:bg-primary-green-dark text-white font-semibold py-3 px-6 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    </main>

</body>
</html>
