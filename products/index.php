<?php
/**
 * Leaf+Loom Products Page - Dynamic Product Listing
 * Displays all products from database with filter and sort
 */

// Include database connection
require_once '../config.php';

// Get filter and sort parameters
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'featured';

// Build SQL query based on filters
$sql = "SELECT * FROM products WHERE status = 'active'";

// Apply category filter
if ($category_filter !== 'all') {
    $sql .= " AND category LIKE :category";
}

// Apply sorting
switch ($sort_by) {
    case 'price-low':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price-high':
        $sql .= " ORDER BY price DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY created_at DESC";
        break;
    case 'featured':
    default:
        $sql .= " ORDER BY is_featured DESC, created_at DESC";
        break;
}

try {
    $stmt = $conn->prepare($sql);
    
    // Bind category parameter if filtering
    if ($category_filter !== 'all') {
        $stmt->bindValue(':category', '%' . $category_filter . '%', PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    // Get unique categories for filter dropdown
    $categories_sql = "SELECT DISTINCT category FROM products WHERE status = 'active' ORDER BY category";
    $categories_stmt = $conn->query($categories_sql);
    $categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch(PDOException $e) {
    $error_message = "Error fetching products: " . $e->getMessage();
    $products = [];
    $categories = [];
}

// Helper function to display star ratings
function displayStars($rating) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = 5 - $fullStars - $halfStar;
    
    $stars = str_repeat('⭐', $fullStars);
    $stars .= $halfStar ? '⭐' : '';
    $stars .= str_repeat('☆', $emptyStars);
    
    return $stars;
}

// Helper function to format price
function formatPrice($price, $originalPrice = null) {
    $html = '<span class="text-2xl font-bold text-primary-green">₹' . number_format($price, 0) . '</span>';
    
    if ($originalPrice && $originalPrice > $price) {
        $discount = round((($originalPrice - $price) / $originalPrice) * 100);
        $html .= ' <span class="text-gray-500 line-through text-sm">₹' . number_format($originalPrice, 0) . '</span>';
        $html .= ' <span class="text-red-600 text-sm font-semibold">(' . $discount . '% OFF)</span>';
    }
    
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Shop premium wooden and bamboo products at Leaf+ Loom. Browse our collection of eco-friendly brushes, combs, pens, and more sustainable alternatives.">
    <meta name="keywords" content="buy wooden brush, bamboo comb, wooden pen, eco-friendly products online">

    <title>Products - Leaf+ Loom | Wooden & Bamboo Products Online</title>

    <!-- Tailwind CSS v4 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Custom Theme Configuration -->
    <style type="text/tailwindcss">
        @theme {
            --color-primary-green: #4A7C59;
            --color-primary-green-dark: #3A6047;
            --color-secondary-green: #7FB069;
            --color-bamboo-beige: #D4C5A0;
            --color-wood-brown: #8B6F47;
            --color-leaf-accent: #9BC184;
            --color-earth-tone: #C9B79C;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        .nav-link {
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #4A7C59;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
    </style>

    <link rel="canonical" href="https://leafplusloom.com/products/">
</head>

<body class="font-[system-ui] text-gray-800 overflow-x-hidden">

    <!-- Include Header -->
    <?php include '../includes/header.php'; ?>

    <!-- Page Header -->
    <section class="bg-gradient-to-br from-primary-green to-secondary-green text-white py-16 md:py-20 text-center">
        <div class="container mx-auto px-6">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">Our Products</h1>
            <p class="text-lg md:text-xl">Discover our handcrafted wooden and bamboo collection</p>
            <p class="text-sm mt-2 text-green-100">Showing <?php echo count($products); ?> products</p>
        </div>
    </section>

    <!-- Filter & Sort Section -->
    <section class="bg-gray-100 py-4 sticky top-0 z-40 shadow-sm">
        <div class="container mx-auto px-6">
            <form method="GET" class="flex flex-col sm:flex-row gap-4 sm:gap-8 justify-center items-center">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <label for="category" class="font-semibold text-gray-700">Category:</label>
                    <select id="category" name="category" onchange="this.form.submit()"
                        class="px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:border-primary-green transition-colors w-full sm:w-auto">
                        <option value="all" <?php echo $category_filter === 'all' ? 'selected' : ''; ?>>All Products</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                <?php echo $category_filter === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <label for="sort" class="font-semibold text-gray-700">Sort By:</label>
                    <select id="sort" name="sort" onchange="this.form.submit()"
                        class="px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:border-primary-green transition-colors w-full sm:w-auto">
                        <option value="featured" <?php echo $sort_by === 'featured' ? 'selected' : ''; ?>>Featured</option>
                        <option value="price-low" <?php echo $sort_by === 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price-high" <?php echo $sort_by === 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    </select>
                </div>
            </form>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="py-16 md:py-20">
        <div class="container mx-auto px-6">
            
            <?php if (isset($error_message)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded">
                    <p class="text-red-700"><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (empty($products)): ?>
                <div class="text-center py-16">
                    <div class="text-6xl mb-4">📦</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">No Products Found</h3>
                    <p class="text-gray-600 mb-6">Try changing your filters or check back later for new products.</p>
                    <a href="index.php" class="bg-primary-green hover:bg-primary-green-dark text-white font-semibold px-6 py-3 rounded-lg transition-colors">
                        View All Products
                    </a>
                </div>
            <?php else: ?>
                
                <div id="productGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8">
                    
                    <?php foreach ($products as $product): ?>
                        <article
                            class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group"
                            itemscope itemtype="https://schema.org/Product">
                            
                            <!-- Product Image -->
                            <div class="relative h-72 bg-gray-100 overflow-hidden">
                                <img src="<?php echo htmlspecialchars($product['main_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['title']); ?>"
                                     itemprop="image"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                     onerror="this.src='../images/placeholder.jpg'">
                                
                                <!-- Badges -->
                                <?php if ($product['is_new_arrival']): ?>
                                    <span class="absolute top-4 right-4 bg-primary-green text-white text-xs font-semibold px-3 py-1 rounded">New</span>
                                <?php elseif ($product['is_featured']): ?>
                                    <span class="absolute top-4 right-4 bg-amber-500 text-white text-xs font-semibold px-3 py-1 rounded">Featured</span>
                                <?php elseif ($product['discount_percentage'] > 0): ?>
                                    <span class="absolute top-4 right-4 bg-red-500 text-white text-xs font-semibold px-3 py-1 rounded">
                                        <?php echo $product['discount_percentage']; ?>% OFF
                                    </span>
                                <?php endif; ?>
                                
                                <!-- Stock Badge -->
                                <?php if ($product['stock_status'] !== 'in-stock'): ?>
                                    <span class="absolute top-4 left-4 bg-gray-800 text-white text-xs font-semibold px-3 py-1 rounded">
                                        Out of Stock
                                    </span>
                                <?php endif; ?>
                                
                                <!-- Quick View Overlay -->
                                <div class="absolute inset-0 bg-primary-green/90 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                    <a href="../product/<?php echo htmlspecialchars($product['slug']); ?>.php"
                                        class="bg-white text-primary-green font-semibold px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                        Quick View
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Product Info -->
                            <div class="p-6">
                                <h3 itemprop="name" class="text-lg font-semibold mb-2 line-clamp-2">
                                    <?php echo htmlspecialchars($product['title']); ?>
                                </h3>
                                
                                <p class="text-sm text-gray-600 mb-3 leading-relaxed line-clamp-2" itemprop="description">
                                    <?php echo htmlspecialchars($product['short_description']); ?>
                                </p>
                                
                                <!-- Rating -->
                                <?php if ($product['total_reviews'] > 0): ?>
                                    <div class="text-sm text-amber-500 mb-3">
                                        <?php echo displayStars($product['average_rating']); ?>
                                        <span class="text-gray-500">(<?php echo $product['total_reviews']; ?> reviews)</span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Price & Add to Cart -->
                                <div class="flex justify-between items-center mt-4" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                                    <div>
                                        <?php echo formatPrice($product['price'], $product['original_price']); ?>
                                    </div>
                                    <meta itemprop="priceCurrency" content="INR">
                                    <meta itemprop="price" content="<?php echo $product['price']; ?>">
                                    <link itemprop="availability" href="https://schema.org/<?php echo $product['stock_status'] === 'in-stock' ? 'InStock' : 'OutOfStock'; ?>">
                                    
                                    <?php if ($product['stock_status'] === 'in-stock'): ?>
                                        <button onclick="addToCart('<?php echo htmlspecialchars($product['title'], ENT_QUOTES); ?>', <?php echo $product['price']; ?>, 1)"
                                            class="bg-secondary-green hover:bg-primary-green text-white font-semibold px-5 py-2 rounded-lg text-sm hover:scale-105 transition-transform">
                                            Add to Cart
                                        </button>
                                    <?php else: ?>
                                        <button disabled
                                            class="bg-gray-300 text-gray-600 font-semibold px-5 py-2 rounded-lg text-sm cursor-not-allowed">
                                            Out of Stock
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="bg-gradient-to-br from-primary-green to-secondary-green text-white py-16 text-center">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Stay Updated</h2>
            <p class="text-lg mb-8 max-w-2xl mx-auto">Subscribe to get special offers, new product launches, and
                eco-friendly living tips</p>
            <form class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto" onsubmit="subscribeNewsletter(event)">
                <input type="email" placeholder="Enter your email" required
                    class="flex-1 px-6 py-3 rounded-lg border-none focus:outline-none focus:ring-2 focus:ring-white text-gray-800">
                <button type="submit"
                    class="bg-white text-primary-green font-semibold px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors">Subscribe</button>
            </form>
        </div>
    </section>

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../js/cart.js"></script>
    <script>
        // Mobile menu toggle
        function toggleMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Cart functionality
        function openCart() {
            alert('Cart functionality - integrate with your cart.js');
        }

        // Add to cart
        function addToCart(productName, price, quantity) {
            console.log(`Added to cart: ${productName}, Price: ₹${price}, Qty: ${quantity}`);
            
            // Show success notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in';
            notification.innerHTML = `✓ ${productName} added to cart!`;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
            
            // Your cart.js logic here
            updateCartCount();
        }

        // Update cart count
        function updateCartCount() {
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                const currentCount = parseInt(cartCount.textContent);
                cartCount.textContent = currentCount + 1;
            }
        }

        // Newsletter subscription
        function subscribeNewsletter(event) {
            event.preventDefault();
            const email = event.target.querySelector('input[type="email"]').value;
            console.log('Subscribed:', email);
            alert('Thank you for subscribing!');
            event.target.reset();
        }
        
        // Add fade-in animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fade-in {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-in {
                animation: fade-in 0.3s ease-out;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>
