<!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <a href="index.html" class="flex items-center gap-2">
                        <img src="./images/logo.png" alt="Leaf+ Loom Logo" class="h-14">
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <ul class="hidden lg:flex gap-8 items-center">
                    <li><a href="index.php" class="nav-link active font-medium hover:text-primary-green transition-colors">Home</a></li>
                    <li><a href="about.php" class="nav-link font-medium hover:text-primary-green transition-colors">About</a></li>
                    <li><a href="services.php" class="nav-link font-medium hover:text-primary-green transition-colors">Services</a></li>
                    <li><a href="products/index.php" class="nav-link font-medium hover:text-primary-green transition-colors">Products</a></li>
                    <li><a href="blogs/index.php" class="nav-link font-medium hover:text-primary-green transition-colors">Blog</a></li>
                    <li><a href="contact.php" class="nav-link font-medium hover:text-primary-green transition-colors">Contact</a></li>
                </ul>
                
                <!-- Cart & Mobile Menu -->
                <div class="flex items-center gap-4">
                    <button onclick="openCart()" class="relative bg-gray-100 hover:bg-gray-200 p-2 rounded-lg transition-colors">
                        <span class="text-xl">🛒</span>
                        <span id="cart-count" class="absolute -top-1 -right-1 bg-primary-green text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">0</span>
                    </button>
                    <button onclick="toggleMenu()" class="lg:hidden text-2xl text-primary-green">☰</button>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <div id="mobileMenu" class="hidden lg:hidden mt-4 pb-4">
                <ul class="flex flex-col gap-3">
                    <li><a href="index.html" class="block py-2 font-medium text-primary-green border-l-4 border-primary-green pl-4">Home</a></li>
                    <li><a href="about.php" class="block py-2 font-medium hover:text-primary-green hover:border-l-4 hover:border-primary-green pl-4 transition-all">About</a></li>
                    <li><a href="services.php" class="block py-2 font-medium hover:text-primary-green hover:border-l-4 hover:border-primary-green pl-4 transition-all">Services</a></li>
                    <li><a href="products.html" class="block py-2 font-medium hover:text-primary-green hover:border-l-4 hover:border-primary-green pl-4 transition-all">Products</a></li>
                    <li><a href="/blogs/index.php" class="block py-2 font-medium hover:text-primary-green hover:border-l-4 hover:border-primary-green pl-4 transition-all">Blog</a></li>
                    <li><a href="contact.php" class="block py-2 font-medium hover:text-primary-green hover:border-l-4 hover:border-primary-green pl-4 transition-all">Contact</a></li>
                </ul>
            </div>
        </nav>
    </header>