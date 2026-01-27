<?php
// Include database configuration
require_once('../config.php');

// Get product slug or ID from URL
$product_identifier = isset($_GET['product']) ? $_GET['product'] : '';

if (empty($product_identifier)) {
    header("Location: index.php");
    exit();
}

try {
    // Fetch product details - try by slug first, then by ID
    $sql = "SELECT * FROM products WHERE (slug = :identifier OR id = :identifier) AND status = 'active' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':identifier', $product_identifier, PDO::PARAM_STR);
    $stmt->execute();
    $product = $stmt->fetch();

    // If product not found, redirect to products page
    if (!$product) {
        header("Location: index.php");
        exit();
    }

    // Fetch product benefits
    $benefits_sql = "SELECT * FROM product_benefits WHERE product_id = :product_id ORDER BY display_order ASC";
    $benefits_stmt = $conn->prepare($benefits_sql);
    $benefits_stmt->bindValue(':product_id', $product['id'], PDO::PARAM_INT);
    $benefits_stmt->execute();
    $benefits = $benefits_stmt->fetchAll();

    // Fetch product gallery images
    $gallery_sql = "SELECT * FROM product_gallery WHERE product_id = :product_id ORDER BY display_order ASC";
    $gallery_stmt = $conn->prepare($gallery_sql);
    $gallery_stmt->bindValue(':product_id', $product['id'], PDO::PARAM_INT);
    $gallery_stmt->execute();
    $gallery_images = $gallery_stmt->fetchAll();

    // Fetch key features
    $features_sql = "SELECT * FROM product_features WHERE product_id = :product_id AND feature_type = 'key-features' ORDER BY display_order ASC";
    $features_stmt = $conn->prepare($features_sql);
    $features_stmt->bindValue(':product_id', $product['id'], PDO::PARAM_INT);
    $features_stmt->execute();
    $key_features = $features_stmt->fetchAll();

    // Fetch how to use instructions
    $usage_sql = "SELECT * FROM product_features WHERE product_id = :product_id AND feature_type = 'how-to-use' ORDER BY display_order ASC";
    $usage_stmt = $conn->prepare($usage_sql);
    $usage_stmt->bindValue(':product_id', $product['id'], PDO::PARAM_INT);
    $usage_stmt->execute();
    $usage_instructions = $usage_stmt->fetchAll();

    // Fetch care instructions
    $care_sql = "SELECT * FROM product_features WHERE product_id = :product_id AND feature_type = 'care-instructions' ORDER BY display_order ASC";
    $care_stmt = $conn->prepare($care_sql);
    $care_stmt->bindValue(':product_id', $product['id'], PDO::PARAM_INT);
    $care_stmt->execute();
    $care_instructions = $care_stmt->fetchAll();

    // Fetch specifications
    $specs_sql = "SELECT * FROM product_specifications WHERE product_id = :product_id ORDER BY display_order ASC";
    $specs_stmt = $conn->prepare($specs_sql);
    $specs_stmt->bindValue(':product_id', $product['id'], PDO::PARAM_INT);
    $specs_stmt->execute();
    $specifications = $specs_stmt->fetchAll();

    // Fetch reviews (approved only)
    $reviews_sql = "SELECT * FROM product_reviews WHERE product_id = :product_id AND is_approved = 1 ORDER BY created_at DESC LIMIT 5";
    $reviews_stmt = $conn->prepare($reviews_sql);
    $reviews_stmt->bindValue(':product_id', $product['id'], PDO::PARAM_INT);
    $reviews_stmt->execute();
    $reviews = $reviews_stmt->fetchAll();

    // Fetch related products (same category, different product)
    $related_sql = "SELECT * FROM products WHERE category = :category AND id != :product_id AND status = 'active' LIMIT 4";
    $related_stmt = $conn->prepare($related_sql);
    $related_stmt->bindValue(':category', $product['category'], PDO::PARAM_STR);
    $related_stmt->bindValue(':product_id', $product['id'], PDO::PARAM_INT);
    $related_stmt->execute();
    $related_products = $related_stmt->fetchAll();

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Helper function to display star ratings
function displayStarsHTML($rating) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = 5 - $fullStars - $halfStar;
    
    $html = '';
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<span>⭐</span>';
    }
    if ($halfStar) {
        $html .= '<span>⭐</span>';
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<span>☆</span>';
    }
    return $html;
}

// Calculate discount percentage if not already set
$discount_percent = $product['discount_percentage'];
if ($product['original_price'] && $product['original_price'] > $product['price'] && !$discount_percent) {
    $discount_percent = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
}

// Prepare meta description
$meta_description = $product['short_description'] ? $product['short_description'] : substr(strip_tags($product['description']), 0, 160);
$meta_keywords = $product['tags'] ? $product['tags'] : $product['category'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
    
    <title><?php echo htmlspecialchars($product['title']); ?> - Leaf+ Loom</title>
    
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
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

        .thumbnail-button {
            transition: all 0.3s ease;
        }

        .thumbnail-button.active {
            border-color: #4A7C59;
            box-shadow: 0 4px 6px -1px rgba(74, 124, 89, 0.2);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-down {
            animation: slideDown 0.3s ease-out;
        }
    </style>
    
    <link rel="canonical" href="https://leafplusloom.com/products/<?php echo htmlspecialchars($product['slug']); ?>">
    
    <!-- Product Schema for SEO -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "<?php echo htmlspecialchars($product['title']); ?>",
      "image": "https://leafplusloom.com<?php echo htmlspecialchars($product['main_image']); ?>",
      "description": "<?php echo htmlspecialchars($product['short_description']); ?>",
      "brand": {
        "@type": "Brand",
        "name": "Leaf+ Loom"
      },
      "offers": {
        "@type": "Offer",
        "url": "https://leafplusloom.com/products/<?php echo htmlspecialchars($product['slug']); ?>",
        "priceCurrency": "INR",
        "price": "<?php echo $product['price']; ?>",
        "availability": "<?php echo ($product['stock_status'] == 'in-stock') ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'; ?>",
        "itemCondition": "https://schema.org/NewCondition"
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "<?php echo $product['average_rating']; ?>",
        "reviewCount": "<?php echo $product['total_reviews']; ?>"
      }
    }
    </script>
</head>
<body class="font-[system-ui] text-gray-800 bg-gray-50">
    
    <!-- Header -->
    <?php include('../includes/header.php'); ?>

    <!-- Breadcrumb -->
    <nav class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-6 py-3">
            <ol class="flex items-center gap-2 text-sm text-gray-600">
                <li><a href="../index.php" class="hover:text-primary-green transition-colors">Home</a></li>
                <span>›</span>
                <li><a href="index.php" class="hover:text-primary-green transition-colors">Products</a></li>
                <span>›</span>
                <li class="text-gray-800 font-medium"><?php echo htmlspecialchars($product['title']); ?></li>
            </ol>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8 md:py-12 max-w-7xl">
        
        <!-- Product Section -->
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 bg-white rounded-2xl shadow-lg p-6 md:p-10">
            
            <!-- Left: Product Images -->
            <div class="space-y-4">
                <!-- Main Image with Badge -->
                <div class="relative bg-gradient-to-br from-bamboo-beige/20 to-earth-tone/10 rounded-2xl overflow-hidden aspect-square flex items-center justify-center border border-gray-100">
                    <?php if ($product['is_new_arrival']): ?>
                    <span class="absolute top-4 right-4 bg-gradient-to-r from-primary-green to-secondary-green text-white text-xs font-bold tracking-wide px-4 py-2 rounded-full shadow-lg z-10">
                        NEW ARRIVAL
                    </span>
                    <?php elseif ($discount_percent > 0): ?>
                    <span class="absolute top-4 right-4 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-bold tracking-wide px-4 py-2 rounded-full shadow-lg z-10">
                        <?php echo $discount_percent; ?>% OFF
                    </span>
                    <?php endif; ?>
                    <img id="mainImage" src="..<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="w-full h-full object-contain p-8 md:p-12 transition-opacity duration-300">
                </div>
                
                <!-- Thumbnail Gallery -->
                <?php if (count($gallery_images) > 0): ?>
                <div class="grid grid-cols-4 gap-3">
                    <!-- Main image as first thumbnail -->
                    <button onclick="changeImage('..<?php echo htmlspecialchars($product['main_image']); ?>', 0)" class="thumbnail-button active border-2 rounded-xl overflow-hidden aspect-square bg-white hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-green focus:ring-offset-2 transition-all" data-index="0">
                        <img src="..<?php echo htmlspecialchars($product['main_image']); ?>" alt="Main View" class="w-full h-full object-contain p-2">
                    </button>
                    
                    <!-- Additional gallery images -->
                    <?php foreach (array_slice($gallery_images, 0, 3) as $index => $image): ?>
                    <button onclick="changeImage('..<?php echo htmlspecialchars($image['image_url']); ?>', <?php echo ($index + 1); ?>)" class="thumbnail-button border-2 border-gray-200 hover:border-primary-green rounded-xl overflow-hidden aspect-square bg-white hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-green focus:ring-offset-2 transition-all" data-index="<?php echo ($index + 1); ?>">
                        <img src="..<?php echo htmlspecialchars($image['image_url']); ?>" alt="<?php echo htmlspecialchars($image['image_alt']); ?>" class="w-full h-full object-contain p-2">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Product Information -->
            <div class="flex flex-col space-y-6">
                <!-- Product Title & Description -->
                <div>
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-4 leading-tight">
                        <?php echo htmlspecialchars($product['title']); ?>
                    </h1>
                    <p class="text-lg text-gray-600 leading-relaxed mb-5">
                        <?php echo htmlspecialchars($product['short_description']); ?>
                    </p>
                    
                    <!-- Rating & Reviews -->
                    <div class="flex items-center gap-4 mb-5">
                        <div class="flex items-center gap-1 text-amber-500 text-xl">
                            <?php echo displayStarsHTML($product['average_rating']); ?>
                        </div>
                        <span class="text-gray-700 font-semibold"><?php echo number_format($product['average_rating'], 1); ?></span>
                        <span class="text-gray-500">|</span>
                        <a href="#reviews" class="text-primary-green hover:underline font-medium"><?php echo $product['total_reviews']; ?> Reviews</a>
                    </div>
                    
                    <!-- Price -->
                    <div class="flex items-baseline gap-3 mb-6">
                        <p class="text-5xl font-extrabold text-primary-green">₹<?php echo number_format($product['price'], 0); ?></p>
                        <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                        <span class="text-gray-500 line-through text-xl">₹<?php echo number_format($product['original_price'], 0); ?></span>
                        <span class="bg-red-100 text-red-700 text-sm font-bold px-3 py-1 rounded-full"><?php echo $discount_percent; ?>% OFF</span>
                        <?php endif; ?>
                    </div>

                    <!-- Key Benefits Highlight -->
                    <?php if (count($benefits) > 0): ?>
                    <div class="bg-gradient-to-br from-green-50 to-bamboo-beige/10 border-l-4 border-primary-green p-6 rounded-xl mb-6 shadow-sm">
                        <h3 class="font-bold text-gray-900 mb-3 text-lg">✨ Key Benefits</h3>
                        <ul class="space-y-2.5 text-sm md:text-base">
                            <?php foreach (array_slice($benefits, 0, 6) as $benefit): ?>
                            <li class="flex items-start gap-3">
                                <span class="text-primary-green flex-shrink-0 mt-0.5 font-bold"><?php echo $benefit['benefit_icon'] ? htmlspecialchars($benefit['benefit_icon']) : '✓'; ?></span>
                                <span class="text-gray-700"><?php echo htmlspecialchars($benefit['benefit_text']); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quantity & Actions -->
                <div class="space-y-5 border-t border-gray-200 pt-6">
                    <!-- Quantity Selector -->
                    <div class="flex items-center gap-5">
                        <label class="font-bold text-gray-900 text-lg">Quantity:</label>
                        <div class="flex items-center border-2 border-gray-300 rounded-xl overflow-hidden shadow-sm hover:border-primary-green transition-colors">
                            <button onclick="decrementQty()" class="px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-xl font-bold text-gray-700">
                                −
                            </button>
                            <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" class="w-20 text-center border-x-2 border-gray-300 py-3 focus:outline-none font-bold text-lg" readonly>
                            <button onclick="incrementQty()" class="px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-xl font-bold text-gray-700">
                                +
                            </button>
                        </div>
                        <div class="text-sm text-gray-600">
                            <?php if ($product['stock_status'] == 'in-stock'): ?>
                            <span class="font-semibold text-primary-green">In Stock</span> - Ready to ship
                            <?php else: ?>
                            <span class="font-semibold text-red-600">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <button onclick="addToCartProduct()" class="w-full bg-gradient-to-r from-primary-green to-primary-green-dark hover:from-primary-green-dark hover:to-primary-green text-white font-bold py-4 px-6 rounded-xl transition-all hover:shadow-xl transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3 text-lg" <?php echo ($product['stock_status'] != 'in-stock') ? 'disabled' : ''; ?>>
                            <span class="text-xl">🛒</span>
                            <span>ADD TO CART - ₹<span id="totalPrice"><?php echo number_format($product['price'], 0); ?></span></span>
                        </button>
                        
                        <button onclick="buyNow()" class="w-full border-2 border-primary-green text-primary-green hover:bg-primary-green hover:text-white font-bold py-4 px-6 rounded-xl transition-all transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2" <?php echo ($product['stock_status'] != 'in-stock') ? 'disabled' : ''; ?>>
                            <span>Buy Now with Google Pay</span>
                            <span class="text-xl">💳</span>
                        </button>
                    </div>

                    <!-- Trust Badges -->
                    <div class="grid grid-cols-3 gap-4 pt-6 border-t border-gray-200">
                        <div class="text-center p-3 bg-gray-50 rounded-lg hover:bg-bamboo-beige/20 transition-colors">
                            <div class="text-3xl mb-2">🌿</div>
                            <div class="text-xs font-bold text-gray-800">100% Natural</div>
                            <div class="text-xs text-gray-600 mt-1">Eco-Friendly</div>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg hover:bg-bamboo-beige/20 transition-colors">
                            <div class="text-3xl mb-2">🇮🇳</div>
                            <div class="text-xs font-bold text-gray-800">Made in India</div>
                            <div class="text-xs text-gray-600 mt-1">Quality Assured</div>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg hover:bg-bamboo-beige/20 transition-colors">
                            <div class="text-3xl mb-2">📦</div>
                            <div class="text-xs font-bold text-gray-800">Free Shipping</div>
                            <div class="text-xs text-gray-600 mt-1">Pan India</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Details Accordion Sections -->
        <div class="mt-12 space-y-4">
            
            <!-- Product Description -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow">
                <button onclick="toggleSection('description')" class="w-full flex justify-between items-center p-6 hover:bg-gray-50 transition-colors">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <span class="text-2xl">📝</span>
                        PRODUCT DESCRIPTION
                    </h2>
                    <span id="icon-description" class="text-3xl text-primary-green font-bold transition-transform duration-300">−</span>
                </button>
                <div id="content-description" class="px-6 pb-6 text-gray-700 leading-relaxed animate-slide-down">
                    <div class="prose max-w-none">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                </div>
            </div>

            <!-- Product Features -->
            <?php if (count($key_features) > 0): ?>
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow">
                <button onclick="toggleSection('features')" class="w-full flex justify-between items-center p-6 hover:bg-gray-50 transition-colors">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <span class="text-2xl">⭐</span>
                        KEY FEATURES
                    </h2>
                    <span id="icon-features" class="text-3xl text-primary-green font-bold transition-transform duration-300">+</span>
                </button>
                <div id="content-features" class="hidden px-6 pb-6 text-gray-700 leading-relaxed">
                    <div class="grid md:grid-cols-2 gap-6">
                        <?php foreach ($key_features as $feature): ?>
                        <div class="flex items-start gap-4 p-4 bg-green-50 rounded-lg">
                            <span class="text-3xl flex-shrink-0"><?php echo $feature['feature_icon'] ? htmlspecialchars($feature['feature_icon']) : '✨'; ?></span>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($feature['feature_title']); ?></h3>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($feature['feature_content']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- How to Use -->
            <?php if (count($usage_instructions) > 0): ?>
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow">
                <button onclick="toggleSection('usage')" class="w-full flex justify-between items-center p-6 hover:bg-gray-50 transition-colors">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <span class="text-2xl">💡</span>
                        HOW TO USE
                    </h2>
                    <span id="icon-usage" class="text-3xl text-primary-green font-bold transition-transform duration-300">+</span>
                </button>
                <div id="content-usage" class="hidden px-6 pb-6">
                    <div class="space-y-4">
                        <?php foreach ($usage_instructions as $index => $instruction): ?>
                        <div class="flex items-start gap-4 p-4 border-l-4 border-wood-brown bg-bamboo-beige/10 rounded-r-lg">
                            <span class="bg-wood-brown text-white w-8 h-8 rounded-full flex items-center justify-center font-bold flex-shrink-0"><?php echo ($index + 1); ?></span>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($instruction['feature_title']); ?></h4>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($instruction['feature_content']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Care Instructions -->
            <?php if (count($care_instructions) > 0): ?>
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow">
                <button onclick="toggleSection('care')" class="w-full flex justify-between items-center p-6 hover:bg-gray-50 transition-colors">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <span class="text-2xl">🧼</span>
                        CARE INSTRUCTIONS
                    </h2>
                    <span id="icon-care" class="text-3xl text-primary-green font-bold transition-transform duration-300">+</span>
                </button>
                <div id="content-care" class="hidden px-6 pb-6">
                    <div class="space-y-4">
                        <?php foreach ($care_instructions as $care): ?>
                        <div class="bg-green-50 p-5 rounded-lg border-l-4 border-secondary-green">
                            <h4 class="font-bold text-gray-900 mb-2 flex items-center gap-2">
                                <span class="text-green-600"><?php echo $care['feature_icon'] ? htmlspecialchars($care['feature_icon']) : '✓'; ?></span> 
                                <?php echo htmlspecialchars($care['feature_title']); ?>
                            </h4>
                            <p class="text-sm text-gray-700"><?php echo htmlspecialchars($care['feature_content']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Specifications -->
            <?php if (count($specifications) > 0): ?>
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow">
                <button onclick="toggleSection('specs')" class="w-full flex justify-between items-center p-6 hover:bg-gray-50 transition-colors">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <span class="text-2xl">📏</span>
                        SPECIFICATIONS
                    </h2>
                    <span id="icon-specs" class="text-3xl text-primary-green font-bold transition-transform duration-300">+</span>
                </button>
                <div id="content-specs" class="hidden px-6 pb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($specifications as $spec): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 font-semibold text-gray-900 w-1/3"><?php echo htmlspecialchars($spec['spec_name']); ?></td>
                                    <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($spec['spec_value']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Customer Reviews -->
            <?php if (count($reviews) > 0): ?>
            <div id="reviews" class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow">
                <button onclick="toggleSection('reviews')" class="w-full flex justify-between items-center p-6 hover:bg-gray-50 transition-colors">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <span class="text-2xl">⭐</span>
                        CUSTOMER REVIEWS (<?php echo $product['total_reviews']; ?>)
                    </h2>
                    <span id="icon-reviews" class="text-3xl text-primary-green font-bold transition-transform duration-300">+</span>
                </button>
                <div id="content-reviews" class="hidden px-6 pb-6">
                    <!-- Rating Summary -->
                    <div class="bg-gradient-to-r from-amber-50 to-yellow-50 rounded-xl p-6 mb-6 border border-amber-200">
                        <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                            <div class="text-center md:border-r md:border-amber-300 md:pr-8">
                                <div class="text-5xl font-extrabold text-primary-green mb-2"><?php echo number_format($product['average_rating'], 1); ?></div>
                                <div class="text-amber-500 text-2xl mb-2">
                                    <?php echo displayStarsHTML($product['average_rating']); ?>
                                </div>
                                <p class="text-sm text-gray-600">Based on <?php echo $product['total_reviews']; ?> reviews</p>
                            </div>
                        </div>
                    </div>

                    <!-- Individual Reviews -->
                    <div class="space-y-6">
                        <?php foreach ($reviews as $index => $review): ?>
                        <div class="<?php echo ($index < count($reviews) - 1) ? 'border-b border-gray-200 ' : ''; ?>pb-6">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <div class="font-bold text-gray-900 text-lg"><?php echo htmlspecialchars($review['customer_name']); ?></div>
                                    <div class="text-amber-500 text-sm mt-1">
                                        <?php echo displayStarsHTML($review['rating']); ?>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></div>
                            </div>
                            <?php if ($review['review_title']): ?>
                            <h5 class="font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($review['review_title']); ?></h5>
                            <?php endif; ?>
                            <p class="text-gray-700 leading-relaxed mb-3">
                                <?php echo htmlspecialchars($review['review_text']); ?>
                            </p>
                            <?php if ($review['is_verified_purchase']): ?>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full">Verified Purchase</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Write Review CTA -->
                    <div class="mt-8 text-center">
                        <button class="bg-wood-brown hover:bg-wood-brown/90 text-white font-semibold py-3 px-8 rounded-lg transition-all">
                            Write a Review
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Related Products -->
        <?php if (count($related_products) > 0): ?>
        <section class="mt-16">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8 text-center">You May Also Like</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($related_products as $related): ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow border border-gray-100">
                    <div class="aspect-square bg-bamboo-beige/20 p-6">
                        <img src="..<?php echo htmlspecialchars($related['main_image']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="w-full h-full object-contain">
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-gray-900 mb-2 line-clamp-2"><?php echo htmlspecialchars($related['title']); ?></h3>
                        <div class="flex items-center justify-between">
                            <span class="text-xl font-bold text-primary-green">₹<?php echo number_format($related['price'], 0); ?></span>
                            <a href="product-details.php?product=<?php echo htmlspecialchars($related['slug']); ?>" class="bg-primary-green text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-primary-green-dark transition-colors">
                                View
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <?php include('../includes/footer.php'); ?>

    <script src="../js/cart.js"></script>
    <script>
        let qty = 1;
        const basePrice = <?php echo $product['price']; ?>;
        const maxQty = <?php echo $product['stock_quantity']; ?>;

        function toggleMenu() {
            document.getElementById('mobileMenu')?.classList.toggle('hidden');
        }

        function changeImage(src, index) {
            const mainImg = document.getElementById('mainImage');
            mainImg.style.opacity = '0';
            
            setTimeout(() => {
                mainImg.src = src;
                mainImg.style.opacity = '1';
            }, 150);
            
            document.querySelectorAll('.thumbnail-button').forEach(btn => {
                btn.classList.remove('active', 'border-primary-green');
                btn.classList.add('border-gray-200');
            });
            
            event.currentTarget.classList.remove('border-gray-200');
            event.currentTarget.classList.add('active', 'border-primary-green');
        }

        function incrementQty() {
            if (qty < maxQty) {
                qty++;
                document.getElementById('quantity').value = qty;
                updatePrice();
            }
        }

        function decrementQty() {
            if (qty > 1) {
                qty--;
                document.getElementById('quantity').value = qty;
                updatePrice();
            }
        }

        function updatePrice() {
            const total = basePrice * qty;
            document.getElementById('totalPrice').textContent = total.toFixed(0);
        }

        function addToCartProduct() {
            const quantity = parseInt(document.getElementById('quantity').value);
            const productName = "<?php echo addslashes($product['title']); ?>";
            
            // Call your cart.js function
            if (typeof addToCart === 'function') {
                addToCart(productName, basePrice, quantity);
            }
            
            // Show success message
            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="text-xl">✓</span> <span>Added to Cart!</span>';
            btn.classList.add('bg-green-600');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('bg-green-600');
            }, 2000);
        }

        function buyNow() {
            addToCartProduct();
            setTimeout(() => {
                window.location.href = '../checkout.php';
            }, 500);
        }

        function toggleSection(sectionId) {
            const content = document.getElementById('content-' + sectionId);
            const icon = document.getElementById('icon-' + sectionId);
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                content.classList.add('animate-slide-down');
                icon.textContent = '−';
                icon.style.transform = 'rotate(0deg)';
            } else {
                content.classList.add('hidden');
                content.classList.remove('animate-slide-down');
                icon.textContent = '+';
                icon.style.transform = 'rotate(180deg)';
            }
        }
    </script>
</body>
</html>
