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

    <link rel="canonical" href="https://leafplusloom.com/products.html">

    <!-- Product Schema Markup -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "ItemList",
      "itemListElement": [
        {
          "@type": "Product",
          "position": 1,
          "name": "Round Handle Brush",
          "description": "Ergonomic wooden brush with natural bristles",
          "offers": {
            "@type": "Offer",
            "price": "299",
            "priceCurrency": "INR",
            "availability": "https://schema.org/InStock"
          }
        }
      ]
    }
    </script>
</head>

<body class="font-[system-ui] text-gray-800 overflow-x-hidden">

    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="bg-gradient-to-br from-primary-green to-secondary-green text-white py-16 md:py-20 text-center">
        <div class="container mx-auto px-6">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">Our Products</h1>
            <p class="text-lg md:text-xl">Discover our handcrafted wooden and bamboo collection</p>
        </div>
    </section>

    <!-- Filter & Sort Section -->
    <section class="bg-gray-100 py-4">
        <div class="container mx-auto px-6">
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-8 justify-center items-center">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <label for="category" class="font-semibold text-gray-700">Category:</label>
                    <select id="category" onchange="filterProducts()"
                        class="px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:border-primary-green transition-colors w-full sm:w-auto">
                        <option value="all">All Products</option>
                        <option value="brushes">Brushes</option>
                        <option value="combs">Combs</option>
                        <option value="stationery">Stationery</option>
                        <option value="accessories">Accessories</option>
                    </select>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <label for="sort" class="font-semibold text-gray-700">Sort By:</label>
                    <select id="sort" onchange="sortProducts()"
                        class="px-4 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:border-primary-green transition-colors w-full sm:w-auto">
                        <option value="featured">Featured</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="newest">Newest First</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="py-16 md:py-20">
        <div class="container mx-auto px-6">
            <div id="productGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8">

                <!-- Product 1 -->
                <article
                    class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group"
                    itemscope itemtype="https://schema.org/Product">
                    <div class="relative h-72 bg-gray-100 overflow-hidden">
                        <img src="/images/round-handle-wooden-brush.jpg" alt="Round Handle Wooden Brush"
                            itemprop="image"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <span
                            class="absolute top-4 right-4 bg-primary-green text-white text-xs font-semibold px-3 py-1 rounded">New</span>
                        <div
                            class="absolute inset-0 bg-primary-green/90 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <a href="product-details.html?id=round-brush"
                                class="bg-white text-primary-green font-semibold px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors">Quick
                                View</a>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 itemprop="name" class="text-lg font-semibold mb-2">Round Handle Brush</h3>
                        <p class="text-sm text-gray-600 mb-3 leading-relaxed" itemprop="description">Ergonomic wooden
                            brush with natural bristles for smooth hair</p>
                        <div class="text-sm text-amber-500 mb-3">
                            ⭐⭐⭐⭐⭐ <span class="text-gray-500">(24 reviews)</span>
                        </div>
                        <div class="flex justify-between items-center mt-4" itemprop="offers" itemscope
                            itemtype="https://schema.org/Offer">
                            <span class="text-2xl font-bold text-primary-green" itemprop="price"
                                content="299">₹299</span>
                            <meta itemprop="priceCurrency" content="INR">
                            <link itemprop="availability" href="https://schema.org/InStock">
                            <button onclick="addToCart('Round Handle Brush', 299, 1)"
                                class="bg-secondary-green hover:bg-primary-green text-white font-semibold px-5 py-2 rounded-lg text-sm hover:scale-105 transition-transform">Add
                                to Cart</button>
                        </div>
                    </div>
                </article>

                <!-- Product 2 -->
                <article
                    class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group"
                    itemscope itemtype="https://schema.org/Product">
                    <div class="relative h-72 bg-gray-100 overflow-hidden">
                        <img src="images/products/kids-brush.jpg" alt="Kids Wooden Brush" itemprop="image"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <span
                            class="absolute top-4 right-4 bg-primary-green text-white text-xs font-semibold px-3 py-1 rounded">Popular</span>
                        <div
                            class="absolute inset-0 bg-primary-green/90 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <a href="product-details.html?id=kids-brush"
                                class="bg-white text-primary-green font-semibold px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors">Quick
                                View</a>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 itemprop="name" class="text-lg font-semibold mb-2">Kids Brush</h3>
                        <p class="text-sm text-gray-600 mb-3 leading-relaxed" itemprop="description">Gentle bamboo brush
                            perfect for children's delicate hair</p>
                        <div class="text-sm text-amber-500 mb-3">
                            ⭐⭐⭐⭐⭐ <span class="text-gray-500">(18 reviews)</span>
                        </div>
                        <div class="flex justify-between items-center mt-4" itemprop="offers" itemscope
                            itemtype="https://schema.org/Offer">
                            <span class="text-2xl font-bold text-primary-green" itemprop="price"
                                content="249">₹249</span>
                            <meta itemprop="priceCurrency" content="INR">
                            <link itemprop="availability" href="https://schema.org/InStock">
                            <button onclick="addToCart('Kids Brush', 249, 1)"
                                class="bg-secondary-green hover:bg-primary-green text-white font-semibold px-5 py-2 rounded-lg text-sm hover:scale-105 transition-transform">Add
                                to Cart</button>
                        </div>
                    </div>
                </article>

                <!-- Product 3 -->
                <article
                    class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group"
                    itemscope itemtype="https://schema.org/Product">
                    <div class="relative h-72 bg-gray-100 overflow-hidden">
                        <img src="/images/premium-wooden-comb.png" alt="Premium Wooden Comb" itemprop="image"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div
                            class="absolute inset-0 bg-primary-green/90 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <a href="product-details.html?id=premium-comb"
                                class="bg-white text-primary-green font-semibold px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors">Quick
                                View</a>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 itemprop="name" class="text-lg font-semibold mb-2">Premium Comb</h3>
                        <p class="text-sm text-gray-600 mb-3 leading-relaxed" itemprop="description">Wide-tooth wooden
                            comb for all hair types</p>
                        <div class="text-sm text-amber-500 mb-3">
                            ⭐⭐⭐⭐⭐ <span class="text-gray-500">(32 reviews)</span>
                        </div>
                        <div class="flex justify-between items-center mt-4" itemprop="offers" itemscope
                            itemtype="https://schema.org/Offer">
                            <span class="text-2xl font-bold text-primary-green" itemprop="price"
                                content="399">₹399</span>
                            <meta itemprop="priceCurrency" content="INR">
                            <link itemprop="availability" href="https://schema.org/InStock">
                            <button onclick="addToCart('Premium Comb', 399, 1)"
                                class="bg-secondary-green hover:bg-primary-green text-white font-semibold px-5 py-2 rounded-lg text-sm hover:scale-105 transition-transform">Add
                                to Cart</button>
                        </div>
                    </div>
                </article>

                <!-- Product 4 -->
                <article
                    class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group"
                    itemscope itemtype="https://schema.org/Product">
                    <div class="relative h-72 bg-gray-100 overflow-hidden">
                        <img src="images/products/wooden-pen.jpg" alt="Wooden Pen" itemprop="image"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <span
                            class="absolute top-4 right-4 bg-primary-green text-white text-xs font-semibold px-3 py-1 rounded">Eco</span>
                        <div
                            class="absolute inset-0 bg-primary-green/90 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <a href="product-details.html?id=wooden-pen"
                                class="bg-white text-primary-green font-semibold px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors">Quick
                                View</a>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 itemprop="name" class="text-lg font-semibold mb-2">Wooden Pen</h3>
                        <p class="text-sm text-gray-600 mb-3 leading-relaxed" itemprop="description">Elegant bamboo pen
                            with smooth ink flow</p>
                        <div class="text-sm text-amber-500 mb-3">
                            ⭐⭐⭐⭐☆ <span class="text-gray-500">(15 reviews)</span>
                        </div>
                        <div class="flex justify-between items-center mt-4" itemprop="offers" itemscope
                            itemtype="https://schema.org/Offer">
                            <span class="text-2xl font-bold text-primary-green" itemprop="price"
                                content="199">₹199</span>
                            <meta itemprop="priceCurrency" content="INR">
                            <link itemprop="availability" href="https://schema.org/InStock">
                            <button onclick="addToCart('Wooden Pen', 199, 1)"
                                class="bg-secondary-green hover:bg-primary-green text-white font-semibold px-5 py-2 rounded-lg text-sm hover:scale-105 transition-transform">Add
                                to Cart</button>
                        </div>
                    </div>
                </article>

                <!-- Product 5 -->
                <article
                    class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group"
                    itemscope itemtype="https://schema.org/Product">
                    <div class="relative h-72 bg-gray-100 overflow-hidden">
                        <img src="images/products/bamboo-toothbrush.jpg" alt="Bamboo Toothbrush" itemprop="image"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div
                            class="absolute inset-0 bg-primary-green/90 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <a href="product-details.html?id=bamboo-toothbrush"
                                class="bg-white text-primary-green font-semibold px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors">Quick
                                View</a>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 itemprop="name" class="text-lg font-semibold mb-2">Bamboo Toothbrush</h3>
                        <p class="text-sm text-gray-600 mb-3 leading-relaxed" itemprop="description">100% biodegradable
                            toothbrush with soft bristles</p>
                        <div class="text-sm text-amber-500 mb-3">
                            ⭐⭐⭐⭐⭐ <span class="text-gray-500">(41 reviews)</span>
                        </div>
                        <div class="flex justify-between items-center mt-4" itemprop="offers" itemscope
                            itemtype="https://schema.org/Offer">
                            <span class="text-2xl font-bold text-primary-green" itemprop="price"
                                content="149">₹149</span>
                            <meta itemprop="priceCurrency" content="INR">
                            <link itemprop="availability" href="https://schema.org/InStock">
                            <button onclick="addToCart('Bamboo Toothbrush', 149, 1)"
                                class="bg-secondary-green hover:bg-primary-green text-white font-semibold px-5 py-2 rounded-lg text-sm hover:scale-105 transition-transform">Add
                                to Cart</button>
                        </div>
                    </div>
                </article>

                <!-- Product 6 -->
                <article
                    class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group"
                    itemscope itemtype="https://schema.org/Product">
                    <div class="relative h-72 bg-gray-100 overflow-hidden">
                        <img src="images/products/wooden-spoon-set.jpg" alt="Wooden Spoon Set" itemprop="image"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div
                            class="absolute inset-0 bg-primary-green/90 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <a href="product-details.html?id=wooden-spoon-set"
                                class="bg-white text-primary-green font-semibold px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors">Quick
                                View</a>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 itemprop="name" class="text-lg font-semibold mb-2">Wooden Spoon Set</h3>
                        <p class="text-sm text-gray-600 mb-3 leading-relaxed" itemprop="description">Set of 5
                            handcrafted wooden cooking spoons</p>
                        <div class="text-sm text-amber-500 mb-3">
                            ⭐⭐⭐⭐⭐ <span class="text-gray-500">(27 reviews)</span>
                        </div>
                        <div class="flex justify-between items-center mt-4" itemprop="offers" itemscope
                            itemtype="https://schema.org/Offer">
                            <span class="text-2xl font-bold text-primary-green" itemprop="price"
                                content="549">₹549</span>
                            <meta itemprop="priceCurrency" content="INR">
                            <link itemprop="availability" href="https://schema.org/InStock">
                            <button onclick="addToCart('Wooden Spoon Set', 549, 1)"
                                class="bg-secondary-green hover:bg-primary-green text-white font-semibold px-5 py-2 rounded-lg text-sm hover:scale-105 transition-transform">Add
                                to Cart</button>
                        </div>
                    </div>
                </article>

                <!-- Product 7 -->
                <article
                    class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group"
                    itemscope itemtype="https://schema.org/Product">
                    <div class="relative h-72 bg-gray-100 overflow-hidden">
                        <img src="images/products/bamboo-keychain.jpg" alt="Bamboo Keychain" itemprop="image"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div
                            class="absolute inset-0 bg-primary-green/90 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <a href="product-details.html?id=bamboo-keychain"
                                class="bg-white text-primary-green font-semibold px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors">Quick
                                View</a>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 itemprop="name" class="text-lg font-semibold mb-2">Bamboo Keychain</h3>
                        <p class="text-sm text-gray-600 mb-3 leading-relaxed" itemprop="description">Customizable bamboo
                            keychain with engraving option</p>
                        <div class="text-sm text-amber-500 mb-3">
                            ⭐⭐⭐⭐☆ <span class="text-gray-500">(12 reviews)</span>
                        </div>
                        <div class="flex justify-between items-center mt-4" itemprop="offers" itemscope
                            itemtype="https://schema.org/Offer">
                            <span class="text-2xl font-bold text-primary-green" itemprop="price" content="99">₹99</span>
                            <meta itemprop="priceCurrency" content="INR">
                            <link itemprop="availability" href="https://schema.org/InStock">
                            <button onclick="addToCart('Bamboo Keychain', 99, 1)"
                                class="bg-secondary-green hover:bg-primary-green text-white font-semibold px-5 py-2 rounded-lg text-sm hover:scale-105 transition-transform">Add
                                to Cart</button>
                        </div>
                    </div>
                </article>

                <!-- Product 8 -->
                <article
                    class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group"
                    itemscope itemtype="https://schema.org/Product">
                    <div class="relative h-72 bg-gray-100 overflow-hidden">
                        <img src="images/products/wooden-phone-stand.jpg" alt="Wooden Phone Stand" itemprop="image"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <span
                            class="absolute top-4 right-4 bg-primary-green text-white text-xs font-semibold px-3 py-1 rounded">New</span>
                        <div
                            class="absolute inset-0 bg-primary-green/90 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <a href="product-details.html?id=wooden-phone-stand"
                                class="bg-white text-primary-green font-semibold px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors">Quick
                                View</a>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 itemprop="name" class="text-lg font-semibold mb-2">Wooden Phone Stand</h3>
                        <p class="text-sm text-gray-600 mb-3 leading-relaxed" itemprop="description">Adjustable wooden
                            stand for smartphones and tablets</p>
                        <div class="text-sm text-amber-500 mb-3">
                            ⭐⭐⭐⭐⭐ <span class="text-gray-500">(19 reviews)</span>
                        </div>
                        <div class="flex justify-between items-center mt-4" itemprop="offers" itemscope
                            itemtype="https://schema.org/Offer">
                            <span class="text-2xl font-bold text-primary-green" itemprop="price"
                                content="449">₹449</span>
                            <meta itemprop="priceCurrency" content="INR">
                            <link itemprop="availability" href="https://schema.org/InStock">
                            <button onclick="addToCart('Wooden Phone Stand', 449, 1)"
                                class="bg-secondary-green hover:bg-primary-green text-white font-semibold px-5 py-2 rounded-lg text-sm hover:scale-105 transition-transform">Add
                                to Cart</button>
                        </div>
                    </div>
                </article>

            </div>
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
    <?php include 'includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="js/cart.js"></script>
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
            // Your cart.js logic here
        }

        // Filter products
        function filterProducts() {
            const category = document.getElementById('category').value;
            console.log('Filtering by category:', category);
            // Add your filter logic here
        }

        // Sort products
        function sortProducts() {
            const sortBy = document.getElementById('sort').value;
            console.log('Sorting by:', sortBy);
            // Add your sort logic here
        }

        // Newsletter subscription
        function subscribeNewsletter(event) {
            event.preventDefault();
            const email = event.target.querySelector('input[type="email"]').value;
            console.log('Subscribed:', email);
            alert('Thank you for subscribing!');
            event.target.reset();
        }
    </script>
</body>

</html>