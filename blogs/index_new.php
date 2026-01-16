<?php
// Database connection
include '../includes/db_connect.php';

// Pagination settings
$posts_per_page = 9;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $posts_per_page;

// Filter by category
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Build SQL query
if ($category_filter === 'all') {
    $sql = "SELECT * FROM blogs WHERE status = 'published' ORDER BY published_date DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $posts_per_page, $offset);
} else {
    $sql = "SELECT * FROM blogs WHERE status = 'published' AND category = ? ORDER BY published_date DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $category_filter, $posts_per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$blog_posts = $result->fetch_all(MYSQLI_ASSOC);

// Get total posts for pagination
if ($category_filter === 'all') {
    $count_sql = "SELECT COUNT(*) as total FROM blogs WHERE status = 'published'";
    $count_result = $conn->query($count_sql);
} else {
    $count_sql = "SELECT COUNT(*) as total FROM blogs WHERE status = 'published' AND category = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("s", $category_filter);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
}

$total_posts = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $posts_per_page);

// Get featured post (most recent)
$featured_sql = "SELECT * FROM blogs WHERE status = 'published' AND is_featured = 1 ORDER BY published_date DESC LIMIT 1";
$featured_result = $conn->query($featured_sql);
$featured_post = $featured_result->fetch_assoc();

// If no featured post, get the most recent one
if (!$featured_post) {
    $featured_sql = "SELECT * FROM blogs WHERE status = 'published' ORDER BY published_date DESC LIMIT 1";
    $featured_result = $conn->query($featured_sql);
    $featured_post = $featured_result->fetch_assoc();
}

// Function to calculate reading time
function calculateReadingTime($content) {
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // Average reading speed: 200 words per minute
    return $reading_time;
}

// Function to format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Read the Leaf+ Loom blog for eco-friendly living tips, sustainable lifestyle guides, zero waste ideas, and stories about wooden and bamboo products.">
    <meta name="keywords" content="sustainability blog, eco-friendly living, zero waste tips, bamboo products guide, wooden products care, green lifestyle">
    <meta name="author" content="Leaf+ Loom">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Blog - Leaf+ Loom | Sustainable Living & Eco Tips">
    <meta property="og:description" content="Expert tips and insights on sustainable living, eco-friendly products, and green lifestyle choices">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://leafplusloom.infinityfreeapp.com/blogs/index.php">
    
    <title>Blog - Leaf+ Loom | Sustainable Living & Eco Tips</title>
    
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
    
    <link rel="canonical" href="https://leafplusloom.infinityfreeapp.com/blogs/index.php">
    
    <!-- Blog Schema Markup -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Blog",
      "name": "Leaf+ Loom Blog",
      "description": "Sustainable living tips and eco-friendly product guides",
      "url": "https://leafplusloom.infinityfreeapp.com/blogs/index.php",
      "publisher": {
        "@type": "Organization",
        "name": "Leaf+ Loom",
        "logo": {
          "@type": "ImageObject",
          "url": "https://leafplusloom.infinityfreeapp.com/images/logo.png"
        }
      }
    }
    </script>
</head>
<body class="font-[system-ui] text-gray-800 overflow-x-hidden">
    
    <!-- Include Header -->
    <?php include '../includes/header.php'; ?>

    <!-- Page Header -->
    <section class="bg-gradient-to-br from-primary-green to-secondary-green text-white py-16 md:py-20 text-center">
        <div class="container mx-auto px-6">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">Our Blog</h1>
            <p class="text-lg md:text-xl">Insights on sustainable living and eco-friendly lifestyle</p>
        </div>
    </section>

    <!-- Blog Filter -->
    <section class="bg-gray-100 py-4">
        <div class="container mx-auto px-6">
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="?category=all" class="filter-tab px-5 py-2 rounded-lg <?php echo $category_filter === 'all' ? 'bg-primary-green text-white' : 'bg-white hover:bg-primary-green hover:text-white'; ?> font-medium transition-all">All Posts</a>
                <a href="?category=sustainability" class="filter-tab px-5 py-2 rounded-lg <?php echo $category_filter === 'sustainability' ? 'bg-primary-green text-white' : 'bg-white hover:bg-primary-green hover:text-white'; ?> font-medium transition-all">Sustainability</a>
                <a href="?category=tips" class="filter-tab px-5 py-2 rounded-lg <?php echo $category_filter === 'tips' ? 'bg-primary-green text-white' : 'bg-white hover:bg-primary-green hover:text-white'; ?> font-medium transition-all">Eco Tips</a>
                <a href="?category=product-care" class="filter-tab px-5 py-2 rounded-lg <?php echo $category_filter === 'product-care' ? 'bg-primary-green text-white' : 'bg-white hover:bg-primary-green hover:text-white'; ?> font-medium transition-all">Product Care</a>
                <a href="?category=lifestyle" class="filter-tab px-5 py-2 rounded-lg <?php echo $category_filter === 'lifestyle' ? 'bg-primary-green text-white' : 'bg-white hover:bg-primary-green hover:text-white'; ?> font-medium transition-all">Lifestyle</a>
            </div>
        </div>
    </section>

    <!-- Featured Post -->
    <?php if ($featured_post): ?>
    <section class="py-12 md:py-16">
        <div class="container mx-auto px-6">
            <article class="grid grid-cols-1 lg:grid-cols-2 gap-8 bg-white rounded-xl overflow-hidden shadow-lg" itemscope itemtype="https://schema.org/BlogPosting">
                <div class="relative h-64 lg:h-auto">
                    <img src="../images/blog/<?php echo htmlspecialchars($featured_post['featured_image']); ?>" 
                         alt="<?php echo htmlspecialchars($featured_post['title']); ?>" 
                         itemprop="image" 
                         class="w-full h-full object-cover">
                    <span class="absolute top-4 left-4 bg-primary-green text-white text-sm font-semibold px-4 py-2 rounded">Featured</span>
                </div>
                <div class="p-6 lg:p-8 flex flex-col justify-center">
                    <div class="flex flex-wrap gap-3 mb-4 text-sm">
                        <span class="bg-bamboo-beige px-3 py-1 rounded font-semibold capitalize"><?php echo htmlspecialchars($featured_post['category']); ?></span>
                        <time class="text-gray-600" itemprop="datePublished" datetime="<?php echo $featured_post['published_date']; ?>">
                            <?php echo formatDate($featured_post['published_date']); ?>
                        </time>
                        <span class="text-gray-600"><?php echo calculateReadingTime($featured_post['content']); ?> min read</span>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4" itemprop="headline">
                        <?php echo htmlspecialchars($featured_post['title']); ?>
                    </h2>
                    <p class="text-gray-600 mb-6 leading-relaxed" itemprop="description">
                        <?php echo htmlspecialchars(substr(strip_tags($featured_post['excerpt']), 0, 200)) . '...'; ?>
                    </p>
                    <a href="<?php echo htmlspecialchars($featured_post['slug']); ?>.php" 
                       class="inline-block bg-primary-green hover:bg-primary-green-dark text-white font-semibold px-6 py-3 rounded-lg transition-colors w-max" 
                       itemprop="url">Read More</a>
                </div>
            </article>
        </div>
    </section>
    <?php endif; ?>

    <!-- Blog Grid -->
    <section class="py-16 md:py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <?php if (count($blog_posts) > 0): ?>
            <div id="blogGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <?php foreach ($blog_posts as $post): ?>
                <!-- Blog Post -->
                <article class="blog-card bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300" itemscope itemtype="https://schema.org/BlogPosting">
                    <div class="h-56 overflow-hidden">
                        <img src="../images/blog/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                             itemprop="image" 
                             class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="p-6">
                        <div class="flex gap-3 mb-3 text-sm">
                            <span class="bg-bamboo-beige px-3 py-1 rounded font-semibold capitalize"><?php echo htmlspecialchars($post['category']); ?></span>
                            <time class="text-gray-600" itemprop="datePublished" datetime="<?php echo $post['published_date']; ?>">
                                <?php echo formatDate($post['published_date']); ?>
                            </time>
                        </div>
                        <h3 class="text-xl font-semibold mb-3" itemprop="headline">
                            <a href="<?php echo htmlspecialchars($post['slug']); ?>.php" 
                               class="text-gray-800 hover:text-primary-green transition-colors" 
                               itemprop="url">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>
                        <p class="text-gray-600 mb-4 leading-relaxed" itemprop="description">
                            <?php echo htmlspecialchars(substr(strip_tags($post['excerpt']), 0, 120)) . '...'; ?>
                        </p>
                        <a href="<?php echo htmlspecialchars($post['slug']); ?>.php" 
                           class="text-primary-green font-semibold hover:underline">Read More →</a>
                    </div>
                </article>
                <?php endforeach; ?>

            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="flex justify-center gap-2 mt-12" aria-label="Blog pagination">
                <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>&category=<?php echo $category_filter; ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-lg bg-white hover:bg-gray-100 transition-colors">← Previous</a>
                <?php else: ?>
                <button class="px-4 py-2 border border-gray-300 rounded-lg bg-white opacity-50 cursor-not-allowed" disabled>← Previous</button>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $current_page): ?>
                    <button class="px-4 py-2 border border-primary-green bg-primary-green text-white rounded-lg"><?php echo $i; ?></button>
                    <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&category=<?php echo $category_filter; ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg bg-white hover:bg-primary-green hover:text-white transition-colors"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>&category=<?php echo $category_filter; ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-lg bg-white hover:bg-gray-100 transition-colors">Next →</a>
                <?php else: ?>
                <button class="px-4 py-2 border border-gray-300 rounded-lg bg-white opacity-50 cursor-not-allowed" disabled>Next →</button>
                <?php endif; ?>
            </nav>
            <?php endif; ?>

            <?php else: ?>
            <!-- No posts found -->
            <div class="text-center py-16">
                <p class="text-2xl text-gray-600 mb-4">No blog posts found in this category.</p>
                <a href="?category=all" class="inline-block bg-primary-green text-white font-semibold px-6 py-3 rounded-lg hover:bg-primary-green-dark transition-colors">View All Posts</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="bg-gradient-to-br from-primary-green to-secondary-green text-white py-16 text-center">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Never Miss a Post</h2>
            <p class="text-lg mb-8 max-w-2xl mx-auto">Subscribe to our newsletter for the latest eco-friendly tips and product updates</p>
            <form id="newsletterForm" class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
                <input type="email" id="newsletter_email" placeholder="Enter your email" required aria-label="Email address" class="flex-1 px-6 py-3 rounded-lg border-none focus:outline-none focus:ring-2 focus:ring-white text-gray-800">
                <button type="submit" class="bg-white text-primary-green font-semibold px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors">Subscribe</button>
            </form>
            <div id="newsletterMessage" class="mt-4"></div>
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
        
        // Newsletter subscription
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('newsletter_email').value;
            const messageDiv = document.getElementById('newsletterMessage');
            
            // AJAX request to subscribe
            fetch('../ajax/subscribe_newsletter.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = '<p class="text-white bg-green-600 px-4 py-2 rounded-lg inline-block">' + data.message + '</p>';
                    document.getElementById('newsletterForm').reset();
                } else {
                    messageDiv.innerHTML = '<p class="text-white bg-red-600 px-4 py-2 rounded-lg inline-block">' + data.message + '</p>';
                }
                
                // Clear message after 5 seconds
                setTimeout(() => {
                    messageDiv.innerHTML = '';
                }, 5000);
            })
            .catch(error => {
                messageDiv.innerHTML = '<p class="text-white bg-red-600 px-4 py-2 rounded-lg inline-block">An error occurred. Please try again.</p>';
            });
        });
    </script>
</body>
</html>
