<?php
/**
 * Leaf+Loom Header Navigation
 * Dynamic URL generation with auto-active link detection
 */

// ============================================
// BASE URL CONFIGURATION
// ============================================
// Auto-detect the protocol and host
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// Define base URL (adjust the path if needed)
$base_url = $protocol . '://' . $host . '/LeafplusLoom/';

// Fallback to manual URL if needed
// $base_url = 'http://localhost/LeafplusLoom/';
// For production: $base_url = 'https://leafplusloom.com/';

// ============================================
// IMAGE PATH HELPER
// ============================================
function getImagePath($imageName) {
    global $base_url;
    return $base_url . 'images/' . ltrim($imageName, '/');
}

// ============================================
// CURRENT PAGE DETECTION
// ============================================
function getCurrentPage() {
    $script_name = basename($_SERVER['SCRIPT_NAME']);
    return $script_name;
}

function isActivePage($page) {
    $current = getCurrentPage();
    
    // Handle special cases
    if ($page === 'index.php' && $current === 'index.php') {
        return true;
    }
    
    // Check if current page matches
    if ($current === $page) {
        return true;
    }
    
    // Check if in a directory (e.g., products/index.php)
    if (strpos($page, '/') !== false) {
        $parts = explode('/', $page);
        $dir = $parts[0];
        
        // Check if current file is in that directory
        if (strpos($_SERVER['SCRIPT_NAME'], $dir) !== false) {
            return true;
        }
    }
    
    return false;
}

// ============================================
// NAVIGATION MENU STRUCTURE
// ============================================
$nav_menu = [
    [
        'url' => 'index.php',
        'label' => 'Home',
        'icon' => '🏠'
    ],
    [
        'url' => 'about.php',
        'label' => 'About',
        'icon' => 'ℹ️'
    ],
    [
        'url' => 'services.php',
        'label' => 'Services',
        'icon' => '⚙️'
    ],
    [
        'url' => 'products/index.php',
        'label' => 'Products',
        'icon' => '🛍️'
    ],
    [
        'url' => 'blogs/index.php',
        'label' => 'Blog',
        'icon' => '📝'
    ],
    [
        'url' => 'contact.php',
        'label' => 'Contact',
        'icon' => '📧'
    ]
];

// Get active page for styling
$active_page = getCurrentPage();
?>

<!-- Header Navigation -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <nav class="container mx-auto px-6 py-4">
        <div class="flex justify-between items-center">

            <!-- Logo Section -->
            <div class="flex items-center gap-2">
                <a href="<?php echo $base_url; ?>index.php"
                    class="flex items-center gap-2 hover:opacity-80 transition-opacity" title="Go to Home">
                    <img src="<?php echo getImagePath('logo.png'); ?>" alt="Leaf+Loom Logo" class="h-14"
                        onerror="this.src='<?php echo $base_url; ?>images/placeholder-logo.png'">
                </a>
            </div>

            <!-- Desktop Navigation Menu -->
            <ul class="hidden lg:flex gap-8 items-center">
                <?php foreach ($nav_menu as $menu_item): ?>
                <?php 
                    $is_active = isActivePage($menu_item['url']);
                    $active_class = $is_active ? 'active text-primary-green border-b-2 border-primary-green pb-1' : '';
                    ?>
                <li>
                    <a href="<?php echo $base_url . $menu_item['url']; ?>"
                        class="nav-link <?php echo $active_class; ?> font-medium hover:text-primary-green transition-colors duration-300"
                        title="Navigate to <?php echo $menu_item['label']; ?>">
                        <?php echo $menu_item['label']; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

            <!-- Right Side: Cart & Mobile Menu Toggle -->
            <div class="flex items-center gap-4">
                <!-- Shopping Cart Button -->
                <button onclick="openCart()"
                    class="relative bg-gray-100 hover:bg-gray-200 p-2.5 rounded-lg transition-all duration-300 group"
                    title="View Shopping Cart" aria-label="Shopping Cart">
                    <span class="text-xl group-hover:scale-110 transition-transform">🛒</span>
                    <span id="cart-count"
                        class="absolute -top-2 -right-2 bg-primary-green text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">
                        0
                    </span>
                </button>

                <!-- Mobile Menu Toggle Button -->
                <button onclick="toggleMenu()"
                    class="lg:hidden text-2xl text-primary-green hover:text-primary-green-dark transition-colors"
                    id="mobileMenuToggle" aria-label="Toggle Mobile Menu" title="Open Mobile Menu">
                    <span id="menuIcon">☰</span>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobileMenu" class="hidden lg:hidden mt-4 pb-4 bg-gray-50 rounded-lg p-4 -mx-4 px-6 animate-slideDown"
            style="display: none;">
            <ul class="flex flex-col gap-2">
                <?php foreach ($nav_menu as $menu_item): ?>
                <?php 
                    $is_active = isActivePage($menu_item['url']);
                    $active_class = $is_active ? 'border-l-4 border-primary-green text-primary-green bg-green-50' : 'hover:border-l-4 hover:border-primary-green';
                    ?>
                <li>
                    <a href="<?php echo $base_url . $menu_item['url']; ?>"
                        class="block py-3 font-medium pl-4 transition-all duration-300 <?php echo $active_class; ?>"
                        title="Navigate to <?php echo $menu_item['label']; ?>">
                        <span class="inline-block mr-2">
                            <?php echo $menu_item['icon']; ?>
                        </span>
                        <?php echo $menu_item['label']; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
</header>

<!-- Mobile Menu Animation & Toggle Script -->
<script>
    // Mobile Menu Toggle Function
    function toggleMenu() {
        const mobileMenu = document.getElementById('mobileMenu');
        const menuIcon = document.getElementById('menuIcon');

        if (mobileMenu.style.display === 'none' || mobileMenu.classList.contains('hidden')) {
            // Open menu
            mobileMenu.style.display = 'block';
            mobileMenu.classList.remove('hidden');
            menuIcon.textContent = '✕'; // Change to X icon
        } else {
            // Close menu
            mobileMenu.style.display = 'none';
            mobileMenu.classList.add('hidden');
            menuIcon.textContent = '☰'; // Change back to hamburger
        }
    }

    // Close mobile menu when a link is clicked (on mobile)
    document.querySelectorAll('#mobileMenu a').forEach(link => {
        link.addEventListener('click', function () {
            const mobileMenu = document.getElementById('mobileMenu');
            const menuIcon = document.getElementById('menuIcon');

            // Only close if on mobile
            if (window.innerWidth < 1024) {
                mobileMenu.style.display = 'none';
                mobileMenu.classList.add('hidden');
                menuIcon.textContent = '☰';
            }
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', function (event) {
        const header = document.querySelector('header');
        const menuToggle = document.getElementById('mobileMenuToggle');
        const mobileMenu = document.getElementById('mobileMenu');

        if (!header.contains(event.target) && mobileMenu.style.display === 'block') {
            mobileMenu.style.display = 'none';
            mobileMenu.classList.add('hidden');
            document.getElementById('menuIcon').textContent = '☰';
        }
    });

    // Handle window resize to close mobile menu on large screens
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.style.display = 'none';
            mobileMenu.classList.add('hidden');
            document.getElementById('menuIcon').textContent = '☰';
        }
    });

    // Placeholder shopping cart function
    function openCart() {
        console.log('Opening shopping cart...');
        // Add your cart implementation here
        alert('Shopping cart functionality coming soon!');
    }
</script>

<!-- CSS for animations and hover effects -->
<style>
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

    .animate-slideDown {
        animation: slideDown 0.3s ease-out;
    }

    /* Navigation link smooth transition */
    .nav-link {
        position: relative;
        padding-bottom: 0.25rem;
        transition: color 0.3s ease, border-color 0.3s ease;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background-color: #4A7C59;
        transition: width 0.3s ease;
    }

    .nav-link:hover::after {
        width: 100%;
    }

    .nav-link.active::after {
        width: 100%;
    }

    /* Mobile menu smooth animation */
    #mobileMenu {
        transition: all 0.3s ease;
    }

    /* Cart button hover effect */
    button[title="View Shopping Cart"]:hover {
        transform: scale(1.05);
    }
</style>