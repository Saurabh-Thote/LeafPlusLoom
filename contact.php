<?php
/**
 * Contact Page - Leaf+Loom
 * Handles contact form submissions and saves to database
 */

// Include database configuration
require_once 'config.php';

// Initialize variables
$success_message = '';
$error_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Sanitize and validate input
    $full_name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_no = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Name is required";
    } elseif (strlen($full_name) > 30) {
        $errors[] = "Name must be less than 30 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } elseif (strlen($email) > 40) {
        $errors[] = "Email must be less than 40 characters";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // Convert phone to integer (remove non-numeric characters)
    $phone_no = preg_replace('/[^0-9]/', '', $phone_no);
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO contacts (full_name, email, phone_no, subject, message, date) 
                    VALUES (:full_name, :email, :phone_no, :subject, :message, NOW())";
            
            $stmt = $conn->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':phone_no', $phone_no, PDO::PARAM_INT);
            $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
            $stmt->bindParam(':message', $message, PDO::PARAM_STR);
            
            // Execute the statement
            if ($stmt->execute()) {
                $success_message = "Thank you for contacting us! We will get back to you soon.";
                
                // Clear form fields after successful submission
                $full_name = $email = $phone_no = $subject = $message = '';
            } else {
                $error_message = "Something went wrong. Please try again.";
            }
            
        } catch (PDOException $e) {
            error_log("Contact Form Error: " . $e->getMessage());
            $error_message = "Failed to send message. Please try again later.";
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contact Leaf+ Loom for inquiries about our wooden and bamboo products, custom orders, bulk purchases, or any questions about sustainable living.">
    <meta name="keywords" content="contact leaf loom, customer support, custom orders, bulk inquiry">
    
    <title>Contact Us - Leaf+ Loom | Get in Touch</title>
    
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
    
    <link rel="canonical" href="https://leafplusloom.com/contact.php">
</head>
<body class="font-[system-ui] text-gray-800 overflow-x-hidden">
    
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <a href="index.php" class="flex items-center gap-2">
                        <img src="/images/leaf+loom logo 1.png" alt="Leaf+ Loom Logo" class="h-14">
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <ul class="hidden lg:flex gap-8 items-center">
                    <li><a href="index.php" class="nav-link font-medium hover:text-primary-green transition-colors">Home</a></li>
                    <li><a href="about.php" class="nav-link font-medium hover:text-primary-green transition-colors">About</a></li>
                    <li><a href="services.php" class="nav-link font-medium hover:text-primary-green transition-colors">Services</a></li>
                    <li><a href="products/index.php" class="nav-link font-medium hover:text-primary-green transition-colors">Products</a></li>
                    <li><a href="blogs/index.php" class="nav-link font-medium hover:text-primary-green transition-colors">Blog</a></li>
                    <li><a href="contact.php" class="nav-link active font-medium hover:text-primary-green transition-colors">Contact</a></li>
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
                    <li><a href="index.php" class="block py-2 font-medium hover:text-primary-green hover:border-l-4 hover:border-primary-green pl-4 transition-all">Home</a></li>
                    <li><a href="about.php" class="block py-2 font-medium hover:text-primary-green hover:border-l-4 hover:border-primary-green pl-4 transition-all">About</a></li>
                    <li><a href="services.php" class="block py-2 font-medium hover:text-primary-green hover:border-l-4 hover:border-primary-green pl-4 transition-all">Services</a></li>
                    <li><a href="products/index.php" class="block py-2 font-medium hover:text-primary-green hover:border-l-4 hover:border-primary-green pl-4 transition-all">Products</a></li>
                    <li><a href="blogs/index.php" class="block py-2 font-medium hover:text-primary-green hover:border-l-4 hover:border-primary-green pl-4 transition-all">Blog</a></li>
                    <li><a href="contact.php" class="block py-2 font-medium text-primary-green border-l-4 border-primary-green pl-4">Contact</a></li>
                </ul>
            </div>
        </nav>
    </header>


    <!-- Page Header -->
    <section class="bg-gradient-to-br from-primary-green to-secondary-green text-white py-16 md:py-20 text-center">
        <div class="container mx-auto px-6">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">Contact Us</h1>
            <p class="text-lg md:text-xl">We'd love to hear from you. Get in touch with our team</p>
        </div>
    </section>


    <!-- Contact Section -->
    <section class="py-16 md:py-20">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-12">
                
                <!-- Contact Info (2 columns on large screens) -->
                <div class="lg:col-span-2">
                    <h2 class="text-3xl font-bold text-primary-green mb-4">Get in Touch</h2>
                    <p class="text-gray-600 mb-8">Have questions about our products or services? We're here to help!</p>
                    
                    <div class="space-y-8">
                        <!-- Address -->
                        <div class="flex gap-4 items-start">
                            <div class="text-4xl text-primary-green flex-shrink-0">📍</div>
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Visit Us</h3>
                                <p class="text-gray-600 leading-relaxed">At Pahela, Po. Pahela,<br>Bhandara, Maharashtra 441924<br>India</p>
                            </div>
                        </div>


                        <!-- Phone -->
                        <div class="flex gap-4 items-start">
                            <div class="text-4xl text-primary-green flex-shrink-0">📞</div>
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Call Us</h3>
                                <p class="text-gray-600 leading-relaxed">+91 8446159421<br>Mon-Sat: 9:00 AM - 6:00 PM IST</p>
                            </div>
                        </div>


                        <!-- Email -->
                        <div class="flex gap-4 items-start">
                            <div class="text-4xl text-primary-green flex-shrink-0">✉️</div>
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Email Us</h3>
                                <p class="text-gray-600 leading-relaxed">info@leafplusloom.com<br>support@leafplusloom.com</p>
                            </div>
                        </div>


                        <!-- Social Media -->
                        <div class="flex gap-4 items-start">
                            <div class="text-4xl text-primary-green flex-shrink-0">🌐</div>
                            <div>
                                <h3 class="text-lg font-semibold mb-3">Follow Us</h3>
                                <div class="flex flex-col gap-2">
                                    <a href="#" aria-label="Facebook" class="text-primary-green hover:text-primary-green-dark font-medium transition-colors">Facebook</a>
                                    <a href="#" aria-label="Instagram" class="text-primary-green hover:text-primary-green-dark font-medium transition-colors">Instagram</a>
                                    <a href="#" aria-label="Twitter" class="text-primary-green hover:text-primary-green-dark font-medium transition-colors">Twitter</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Contact Form (3 columns on large screens) -->
                <div class="lg:col-span-3 bg-gray-50 p-8 rounded-xl">
                    <h2 class="text-3xl font-bold text-primary-green mb-6">Send us a Message</h2>
                    
                    <!-- Success Message -->
                    <?php if (!empty($success_message)): ?>
                        <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-800 rounded-lg">
                            <p class="font-semibold">✅ Success!</p>
                            <p><?php echo htmlspecialchars($success_message); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Error Message -->
                    <?php if (!empty($error_message)): ?>
                        <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-lg">
                            <p class="font-semibold">❌ Error!</p>
                            <p><?php echo $error_message; ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="contact.php" class="space-y-5">
                        
                        <!-- Full Name -->
                        <div>
                            <label for="name" class="block mb-2 font-semibold text-gray-700">Full Name *</label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                required 
                                maxlength="30"
                                value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                                placeholder="Enter your name"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:border-primary-green transition-colors">
                        </div>


                        <!-- Email -->
                        <div>
                            <label for="email" class="block mb-2 font-semibold text-gray-700">Email Address *</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                maxlength="40"
                                value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                placeholder="Enter your email"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:border-primary-green transition-colors">
                        </div>


                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block mb-2 font-semibold text-gray-700">Phone Number</label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                value="<?php echo htmlspecialchars($phone_no ?? ''); ?>"
                                placeholder="Enter your phone number"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:border-primary-green transition-colors">
                        </div>


                        <!-- Subject -->
                        <div>
                            <label for="subject" class="block mb-2 font-semibold text-gray-700">Subject *</label>
                            <select 
                                id="subject" 
                                name="subject" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:border-primary-green transition-colors">
                                <option value="">Select a subject</option>
                                <option value="product-inquiry" <?php echo (isset($subject) && $subject === 'product-inquiry') ? 'selected' : ''; ?>>Product Inquiry</option>
                                <option value="custom-order" <?php echo (isset($subject) && $subject === 'custom-order') ? 'selected' : ''; ?>>Custom Order</option>
                                <option value="bulk-order" <?php echo (isset($subject) && $subject === 'bulk-order') ? 'selected' : ''; ?>>Bulk/Wholesale Order</option>
                                <option value="support" <?php echo (isset($subject) && $subject === 'support') ? 'selected' : ''; ?>>Customer Support</option>
                                <option value="partnership" <?php echo (isset($subject) && $subject === 'partnership') ? 'selected' : ''; ?>>Partnership Opportunity</option>
                                <option value="other" <?php echo (isset($subject) && $subject === 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>


                        <!-- Message -->
                        <div>
                            <label for="message" class="block mb-2 font-semibold text-gray-700">Message *</label>
                            <textarea 
                                id="message" 
                                name="message" 
                                rows="6" 
                                required 
                                placeholder="Write your message here..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:border-primary-green transition-colors resize-none"><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        </div>


                        <!-- Checkbox -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" required id="agree" class="mt-1 w-4 h-4 text-primary-green border-gray-300 rounded focus:ring-primary-green">
                            <label for="agree" class="text-sm text-gray-700">
                                I agree to the <a href="#" class="text-primary-green hover:underline">Privacy Policy</a> and <a href="#" class="text-primary-green hover:underline">Terms of Service</a>
                            </label>
                        </div>


                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            class="w-full bg-primary-green hover:bg-primary-green-dark text-white font-semibold px-8 py-3 rounded-lg shadow-md hover:-translate-y-1 hover:shadow-lg transition-all">
                            Send Message
                        </button>
                    </form>
                </div>


            </div>
        </div>
    </section>


    <!-- Map Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold text-primary-green text-center mb-8">Find Us on the Map</h2>
            <div class="rounded-xl overflow-hidden shadow-lg">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3720.717711690874!2d79.07163167503536!3d21.16362948051972!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bd4c0fce97c402f%3A0x733bbcf1102e7eb5!2sDigambar%20Jain%20Mandir%2C%20Sadar%20Bazar!5e0!3m2!1sen!2sin!4v1766475612011!5m2!1sen!2sin" 
                    width="100%" 
                    height="400" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy"
                    title="Leaf+ Loom Location"
                    class="w-full">
                </iframe>
            </div>
        </div>
    </section>


    <!-- FAQ Section -->
    <section class="py-16 md:py-20">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold text-primary-green text-center mb-12">Frequently Asked Questions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="bg-gray-50 p-6 rounded-lg border-l-4 border-primary-green">
                    <h3 class="text-lg font-semibold text-primary-green mb-3">What are your shipping times?</h3>
                    <p class="text-gray-600">We typically ship orders within 1-2 business days. Delivery takes 5-7 business days within India.</p>
                </div>


                <div class="bg-gray-50 p-6 rounded-lg border-l-4 border-primary-green">
                    <h3 class="text-lg font-semibold text-primary-green mb-3">Do you offer international shipping?</h3>
                    <p class="text-gray-600">Yes, we ship to select countries. Please contact us for international shipping rates and timelines.</p>
                </div>


                <div class="bg-gray-50 p-6 rounded-lg border-l-4 border-primary-green">
                    <h3 class="text-lg font-semibold text-primary-green mb-3">What is your return policy?</h3>
                    <p class="text-gray-600">We offer a 30-day return policy for unused products in original packaging. Contact our support team to initiate a return.</p>
                </div>


                <div class="bg-gray-50 p-6 rounded-lg border-l-4 border-primary-green">
                    <h3 class="text-lg font-semibold text-primary-green mb-3">Can I customize products?</h3>
                    <p class="text-gray-600">Absolutely! We offer customization for bulk orders. Contact us to discuss your requirements.</p>
                </div>


            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12 md:py-16">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                <div>
                    <img src="/images/leaf+loom logo 2.jpg" alt="Leaf+ Loom Logo" class="h-14 mb-4">
                    <p class="text-gray-400">Crafting sustainable wooden and bamboo products for a greener tomorrow.</p>
                </div>
                <div>
                    <h4 class="text-secondary-green font-semibold mb-4 text-lg">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="products/index.php" class="text-gray-400 hover:text-white transition-colors">Products</a></li>
                        <li><a href="blogs/index.php" class="text-gray-400 hover:text-white transition-colors">Blog</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-secondary-green font-semibold mb-4 text-lg">Customer Service</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Shipping Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Return Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Terms & Conditions</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-secondary-green font-semibold mb-4 text-lg">Connect With Us</h4>
                    <div class="flex gap-4 text-2xl">
                        <a href="#" aria-label="Facebook" class="hover:text-secondary-green transition-colors">📘</a>
                        <a href="#" aria-label="Instagram" class="hover:text-secondary-green transition-colors">📷</a>
                        <a href="#" aria-label="Twitter" class="hover:text-secondary-green transition-colors">🐦</a>
                        <a href="#" aria-label="Pinterest" class="hover:text-secondary-green transition-colors">📌</a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-6 text-center text-gray-400">
                <p>&copy; 2025 Leaf+ Loom. All rights reserved.</p>
            </div>
        </div>
    </footer>


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
        
        // Auto-hide success/error messages after 5 seconds
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const messages = document.querySelectorAll('.bg-green-100, .bg-red-100');
                messages.forEach(function(msg) {
                    msg.style.transition = 'opacity 0.5s';
                    msg.style.opacity = '0';
                    setTimeout(function() {
                        msg.remove();
                    }, 500);
                });
            }, 5000);
        });
    </script>
</body>
</html>
