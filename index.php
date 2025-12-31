<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Leaf+ Loom offers premium eco-friendly wooden and bamboo products including brushes, combs, and stationery. Shop sustainable, handcrafted items for a greener lifestyle.">
    <meta name="keywords" content="wooden products, bamboo products, eco-friendly, sustainable, wooden brush, bamboo comb, wooden pen">
    <meta name="author" content="Leaf+ Loom">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Leaf+ Loom - Eco-Friendly Wooden & Bamboo Products">
    <meta property="og:description" content="Premium handcrafted wooden and bamboo products for sustainable living">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://leafplusloom.com">
    
    <title>Leaf+ Loom - Premium Wooden & Bamboo Products | Sustainable Living</title>
    
    <!-- Tailwind CSS v4 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Custom Theme Configuration -->
    <style type="text/tailwindcss">
        @theme {
            /* Custom Color Palette */
            --color-primary-green: #4A7C59;
            --color-primary-green-dark: #3A6047;
            --color-secondary-green: #7FB069;
            --color-bamboo-beige: #D4C5A0;
            --color-wood-brown: #8B6F47;
            --color-leaf-accent: #9BC184;
            --color-earth-tone: #C9B79C;
            
            /* Additional Utilities */
            --font-display: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Custom animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom nav link underline effect */
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
    
    <link rel="canonical" href="https://leafplusloom.com">
</head>
<body class="font-[system-ui] text-gray-800 overflow-x-hidden">
    
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-primary-green/90 to-leaf-accent/90 text-white py-24 md:py-32 text-center min-h-[500px] flex items-center justify-center">
        <!-- Background Image (add via inline style or replace with your image) -->
        <div class="absolute inset-0 bg-[url('../images/hero/hero-bg.jpg')] bg-cover bg-center -z-10"></div>
        
        <div class="container mx-auto px-6 relative z-10 animate-fadeInUp">
            <h1 class="text-3xl md:text-5xl font-bold mb-6 drop-shadow-lg">Sustainable Living Starts Here</h1>
            <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto">Discover premium handcrafted wooden and bamboo products for an eco-friendly lifestyle</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="products.html" class="inline-block bg-primary-green hover:bg-primary-green-dark text-white font-semibold px-7 py-3 rounded-lg shadow-lg hover:-translate-y-1 hover:shadow-xl transition-all">Shop Now</a>
                <a href="about.html" class="inline-block bg-transparent border-2 border-white text-white hover:bg-white hover:text-primary-green font-semibold px-7 py-3 rounded-lg transition-all">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-16 md:py-24">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold text-primary-green text-center mb-4">Our Featured Products</h2>
            <p class="text-center text-gray-600 text-lg mb-12">Handcrafted with love, designed for sustainability</p>
            
            <!-- Product Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mt-8">
                
                <!-- Product Card 1 -->
                <article class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                    <div class="relative h-72 bg-gray-100 overflow-hidden">
                        <img src="images/products/round-brush.jpg" alt="Round Handle Wooden Brush" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <span class="absolute top-4 right-4 bg-primary-green text-white text-xs font-semibold px-3 py-1 rounded">New</span>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-2">Round Handle Brush</h3>
                        <p class="text-sm text-gray-600 mb-4 leading-relaxed">Ergonomic wooden brush with natural bristles</p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-2xl font-bold text-primary-green">₹299</span>
                            <button onclick="addToCart('Round Handle Brush', 299, 1)" class="bg-secondary-green hover:bg-primary-green text-white font-semibold px-5 py-2 rounded-lg text-sm hover:scale-105 transition-transform">Add to Cart</button>
                        </div>
                    </div>
                </article>

                <!-- Product Card 2 -->
                <article class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                    <div class="relative h-72 bg-gray-100 overflow-hidden">
                        <img src="images/products/kids-brush.jpg" alt="Kids Wooden Brush" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <span class="absolute top-4 right-4 bg-primary-green text-white text-xs font-semibold px-3 py-1 rounded">Popular</span>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-2">Kids Brush</h3>
                        <p class="text-sm text-gray-600 mb-4 leading-relaxed">Gentle bamboo brush perfect for children</p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-2xl font-bold text-primary-green">₹249</span>
                            <button onclick="addToCart('Kids Brush', 249, 1)" class="bg-secondary-green hover:bg-primary-green text-white font-semibold px-5 py-2 rounded-lg text-sm hover:scale-105 transition-transform">Add to Cart</button>
                        </div>
                    </div>
                </article>

                <!-- Product Card 3 -->
                <article class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                    <div class="relative h-72 bg-gray-100 overflow-hidden">
                        <img src="images/products/premium-comb.jpg" alt="Premium Wooden Comb" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-2">Premium Comb</h3>
                        <p class="text-sm text-gray-600 mb-4 leading-relaxed">Wide-tooth wooden comb for all hair types</p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-2xl font-bold text-primary-green">₹399</span>
                            <button onclick="addToCart('Premium Comb', 399, 1)" class="bg-secondary-green hover:bg-primary-green text-white font-semibold px-5 py-2 rounded-lg text-sm hover:scale-105 transition-transform">Add to Cart</button>
                        </div>
                    </div>
                </article>

                <!-- Product Card 4 -->
                <article class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                    <div class="relative h-72 bg-gray-100 overflow-hidden">
                        <img src="images/products/wooden-pen.jpg" alt="Wooden Pen" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <span class="absolute top-4 right-4 bg-primary-green text-white text-xs font-semibold px-3 py-1 rounded">Eco</span>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-2">Wooden Pen</h3>
                        <p class="text-sm text-gray-600 mb-4 leading-relaxed">Elegant bamboo pen with smooth ink flow</p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-2xl font-bold text-primary-green">₹199</span>
                            <button onclick="addToCart('Wooden Pen', 199, 1)" class="bg-secondary-green hover:bg-primary-green text-white font-semibold px-5 py-2 rounded-lg text-sm hover:scale-105 transition-transform">Add to Cart</button>
                        </div>
                    </div>
                </article>
                
            </div>
            
            <div class="text-center mt-12">
                <a href="products.html" class="inline-block bg-primary-green hover:bg-primary-green-dark text-white font-semibold px-7 py-3 rounded-lg shadow-md hover:-translate-y-1 hover:shadow-lg transition-all">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="bg-gray-50 py-16 md:py-24">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold text-primary-green text-center mb-12">Why Choose Leaf+ Loom?</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-xl text-center shadow-sm hover:-translate-y-2 transition-transform duration-300">
                    <div class="text-5xl mb-4">🌱</div>
                    <h3 class="text-lg font-semibold text-primary-green mb-3">100% Eco-Friendly</h3>
                    <p class="text-gray-600">Made from sustainable wood and bamboo materials</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-xl text-center shadow-sm hover:-translate-y-2 transition-transform duration-300">
                    <div class="text-5xl mb-4">✋</div>
                    <h3 class="text-lg font-semibold text-primary-green mb-3">Handcrafted Quality</h3>
                    <p class="text-gray-600">Each product is carefully crafted by skilled artisans</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-xl text-center shadow-sm hover:-translate-y-2 transition-transform duration-300">
                    <div class="text-5xl mb-4">♻️</div>
                    <h3 class="text-lg font-semibold text-primary-green mb-3">Zero Plastic</h3>
                    <p class="text-gray-600">Completely plastic-free packaging and products</p>
                </div>
                
                <!-- Feature 4 -->
                <div class="bg-white p-8 rounded-xl text-center shadow-sm hover:-translate-y-2 transition-transform duration-300">
                    <div class="text-5xl mb-4">💚</div>
                    <h3 class="text-lg font-semibold text-primary-green mb-3">Earth Friendly</h3>
                    <p class="text-gray-600">Biodegradable products that don't harm the planet</p>
                </div>
                
            </div>
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
        
        // Cart functionality placeholder
        function openCart() {
            alert('Cart functionality - integrate with your cart.js');
        }
        
        // Add to cart placeholder (implement in cart.js)
        function addToCart(productName, price, quantity) {
            console.log(`Added to cart: ${productName}, Price: ₹${price}, Qty: ${quantity}`);
            // Your cart logic here
        }
    </script>
</body>
</html>
