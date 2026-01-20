<?php
// Include database config
require_once '../config.php';

// Pagination settings
$blogs_per_page = 9;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $blogs_per_page;

// Category filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Prepare SQL based on category
try {
    if ($category_filter === 'all') {
        // Get total count
        $count_sql = "SELECT COUNT(*) as total FROM blogs WHERE status = 'published'";
        $count_stmt = $conn->query($count_sql);
        $total_blogs = $count_stmt->fetch()['total'];
        
        // Get blogs with pagination - FIXED with underscore columns
        $sql = "SELECT * FROM blogs WHERE status = 'published' ORDER BY published_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':limit', $blogs_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    } else {
        // Get total count for category
        $count_sql = "SELECT COUNT(*) as total FROM blogs WHERE status = 'published' AND category = :category";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bindValue(':category', $category_filter, PDO::PARAM_STR);
        $count_stmt->execute();
        $total_blogs = $count_stmt->fetch()['total'];
        
        // Get blogs by category with pagination - FIXED with underscore columns
        $sql = "SELECT * FROM blogs WHERE status = 'published' AND category = :category ORDER BY published_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':category', $category_filter, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $blogs_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $blogs = $stmt->fetchAll();
    
    // Calculate total pages
    $total_pages = ceil($total_blogs / $blogs_per_page);
    
    // Get featured blog - FIXED with underscore columns
    $featured_sql = "SELECT * FROM blogs WHERE status = 'published' AND is_featured = 1 ORDER BY published_at DESC LIMIT 1";
    $featured_stmt = $conn->query($featured_sql);
    $featured_blog = $featured_stmt->fetch();
    
    // If no featured blog, get most recent
    if (!$featured_blog) {
        $featured_sql = "SELECT * FROM blogs WHERE status = 'published' ORDER BY published_at DESC LIMIT 1";
        $featured_stmt = $conn->query($featured_sql);
        $featured_blog = $featured_stmt->fetch();
    }
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Helper function to format date
function formatDate($date) {
    if (!$date) return 'Not published';
    return date('M d, Y', strtotime($date));
}

// Helper function to create excerpt
function createExcerpt($text, $length = 150) {
    $text = strip_tags($text);
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
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
    <meta property="og:url" content="http://localhost/LeafplusLoom/blogs/index.php">
    
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
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
    
    <link rel="canonical" href="http://localhost/LeafplusLoom/blogs/index.php">
    
    <!-- Blog Schema Markup -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Blog",
      "name": "Leaf+ Loom Blog",
      "description": "Sustainable living tips and eco-friendly product guides",
      "url": "http://localhost/LeafplusLoom/blogs/index.php",
      "publisher": {
        "@type": "Organization",
        "name": "Leaf+ Loom",
        "logo": {
          "@type": "ImageObject",
          "url": "http://localhost/LeafplusLoom/images/logo.png"
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
            <p class="text-sm mt-2 opacity-90">Total Articles: <?php echo $total_blogs; ?></p>
        </div>
    </section>

    <!-- Blog Filter -->
    <section class="bg-gray-100 py-4">
        <div class="container mx-auto px-6">
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="?category=all" class="filter-tab px-5 py-2 rounded-lg <?php echo $category_filter === 'all' ? 'bg-primary-green text-white' : 'bg-white hover:bg-primary-green hover:text-white'; ?> font-medium transition-all">
                    All Posts
                </a>
                <a href="?category=Sustainability" class="filter-tab px-5 py-2 rounded-lg <?php echo $category_filter === 'Sustainability' ? 'bg-primary-green text-white' : 'bg-white hover:bg-primary-green hover:text-white'; ?> font-medium transition-all">
                    Sustainability
                </a>
                <a href="?category=Hair Care" class="filter-tab px-5 py-2 rounded-lg <?php echo $category_filter === 'Hair Care' ? 'bg-primary-green text-white' : 'bg-white hover:bg-primary-green hover:text-white'; ?> font-medium transition-all">
                    Hair Care
                </a>
                <a href="?category=Lifestyle" class="filter-tab px-5 py-2 rounded-lg <?php echo $category_filter === 'Lifestyle' ? 'bg-primary-green text-white' : 'bg-white hover:bg-primary-green hover:text-white'; ?> font-medium transition-all">
                    Lifestyle
                </a>
                <a href="?category=Product Care" class="filter-tab px-5 py-2 rounded-lg <?php echo $category_filter === 'Product Care' ? 'bg-primary-green text-white' : 'bg-white hover:bg-primary-green hover:text-white'; ?> font-medium transition-all">
                    Product Care
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Post -->
    <?php if ($featured_blog): ?>
    <section class="py-12 md:py-16">
        <div class="container mx-auto px-6">
            <article class="grid grid-cols-1 lg:grid-cols-2 gap-8 bg-white rounded-xl overflow-hidden shadow-lg" itemscope itemtype="https://schema.org/BlogPosting">
                <div class="relative h-64 lg:h-auto">
                    <img src="../<?php echo htmlspecialchars($featured_blog['featured_image']); ?>" 
                         alt="<?php echo htmlspecialchars($featured_blog['title']); ?>" 
                         itemprop="image" 
                         class="w-full h-full object-cover"
                         onerror="this.src='../images/blog/default-blog.jpg'">
                    <span class="absolute top-4 left-4 bg-primary-green text-white text-sm font-semibold px-4 py-2 rounded">Featured</span>
                </div>
                <div class="p-6 lg:p-8 flex flex-col justify-center">
                    <div class="flex flex-wrap gap-3 mb-4 text-sm">
                        <span class="bg-bamboo-beige px-3 py-1 rounded font-semibold"><?php echo htmlspecialchars($featured_blog['category']); ?></span>
                        <time class="text-gray-600" itemprop="datePublished" datetime="<?php echo $featured_blog['published_at']; ?>">
                            <?php echo formatDate($featured_blog['published_at']); ?>
                        </time>
                        <span class="text-gray-600">⏱ <?php echo $featured_blog['reading_time']; ?> min read</span>
                        <span class="text-gray-600">👁 <?php echo number_format($featured_blog['views_count']); ?> views</span>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4" itemprop="headline">
                        <?php echo htmlspecialchars($featured_blog['title']); ?>
                    </h2>
                    <p class="text-gray-600 mb-6 leading-relaxed" itemprop="description">
                        <?php echo htmlspecialchars($featured_blog['excerpt']); ?>
                    </p>
                    <div class="flex items-center gap-3 mb-6">
                        <?php if ($featured_blog['author_image']): ?>
                        <img src="../<?php echo htmlspecialchars($featured_blog['author_image']); ?>" 
                             alt="<?php echo htmlspecialchars($featured_blog['author_name']); ?>" 
                             class="w-10 h-10 rounded-full object-cover"
                             onerror="this.src='../images/author/default-author.jpg'">
                        <?php else: ?>
                        <div class="w-10 h-10 rounded-full bg-primary-green text-white flex items-center justify-center font-bold">
                            <?php echo strtoupper(substr($featured_blog['author_name'], 0, 1)); ?>
                        </div>
                        <?php endif; ?>
                        <span class="text-sm text-gray-700">By <strong><?php echo htmlspecialchars($featured_blog['author_name']); ?></strong></span>
                    </div>
                    <a href="blog-post.php?slug=<?php echo urlencode($featured_blog['slug']); ?>" 
                       class="inline-block bg-primary-green hover:bg-primary-green-dark text-white font-semibold px-6 py-3 rounded-lg transition-colors w-max" 
                       itemprop="url">Read Full Article →</a>
                </div>
            </article>
        </div>
    </section>
    <?php endif; ?>

    <!-- Blog Grid -->
    <section class="py-16 md:py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <?php if (count($blogs) > 0): ?>
            <div id="blogGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <?php foreach ($blogs as $blog): ?>
                <!-- Blog Post Card -->
                <article class="blog-card bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300" itemscope itemtype="https://schema.org/BlogPosting">
                    <div class="h-56 overflow-hidden">
                        <a href="blog-post.php?slug=<?php echo urlencode($blog['slug']); ?>">
                            <img src="../<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($blog['title']); ?>" 
                                 itemprop="image" 
                                 class="w-full h-full object-cover hover:scale-110 transition-transform duration-500"
                                 onerror="this.src='../images/blog/default-blog.jpg'">
                        </a>
                    </div>
                    <div class="p-6">
                        <div class="flex gap-3 mb-3 text-sm flex-wrap">
                            <span class="bg-bamboo-beige px-3 py-1 rounded font-semibold"><?php echo htmlspecialchars($blog['category']); ?></span>
                            <time class="text-gray-600" itemprop="datePublished" datetime="<?php echo $blog['published_at']; ?>">
                                <?php echo formatDate($blog['published_at']); ?>
                            </time>
                        </div>
                        <h3 class="text-xl font-semibold mb-3 line-clamp-2" itemprop="headline">
                            <a href="blog-post.php?slug=<?php echo urlencode($blog['slug']); ?>" 
                               class="text-gray-800 hover:text-primary-green transition-colors" 
                               itemprop="url">
                                <?php echo htmlspecialchars($blog['title']); ?>
                            </a>
                        </h3>
                        <p class="text-gray-600 mb-4 leading-relaxed line-clamp-3" itemprop="description">
                            <?php echo createExcerpt($blog['excerpt'], 120); ?>
                        </p>
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <span>⏱ <?php echo $blog['reading_time']; ?> min read</span>
                            <span>👁 <?php echo number_format($blog['views_count']); ?></span>
                        </div>
                        <div class="flex items-center gap-3 mb-4 pb-4 border-b">
                            <?php if ($blog['author_image']): ?>
                            <img src="../<?php echo htmlspecialchars($blog['author_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($blog['author_name']); ?>" 
                                 class="w-8 h-8 rounded-full object-cover"
                                 onerror="this.src='../images/author/default-author.jpg'">
                            <?php else: ?>
                            <div class="w-8 h-8 rounded-full bg-primary-green text-white flex items-center justify-center text-xs font-bold">
                                <?php echo strtoupper(substr($blog['author_name'], 0, 1)); ?>
                            </div>
                            <?php endif; ?>
                            <span class="text-sm text-gray-600"><?php echo htmlspecialchars($blog['author_name']); ?></span>
                        </div>
                        <a href="blog-post.php?slug=<?php echo urlencode($blog['slug']); ?>" 
                           class="text-primary-green font-semibold hover:underline inline-flex items-center gap-1">
                            Read More 
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>

            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="flex justify-center gap-2 mt-12" aria-label="Blog pagination">
                <!-- Previous Button -->
                <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>&category=<?php echo urlencode($category_filter); ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-lg bg-white hover:bg-gray-100 transition-colors">
                    ← Previous
                </a>
                <?php else: ?>
                <button class="px-4 py-2 border border-gray-300 rounded-lg bg-white opacity-50 cursor-not-allowed" disabled>
                    ← Previous
                </button>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php 
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                // Show first page if not in range
                if ($start_page > 1): ?>
                    <a href="?page=1&category=<?php echo urlencode($category_filter); ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg bg-white hover:bg-primary-green hover:text-white transition-colors">
                        1
                    </a>
                    <?php if ($start_page > 2): ?>
                        <span class="px-2 py-2">...</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <?php if ($i == $current_page): ?>
                    <button class="px-4 py-2 border border-primary-green bg-primary-green text-white rounded-lg font-semibold">
                        <?php echo $i; ?>
                    </button>
                    <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category_filter); ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg bg-white hover:bg-primary-green hover:text-white transition-colors">
                        <?php echo $i; ?>
                    </a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <!-- Show last page if not in range -->
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="px-2 py-2">...</span>
                    <?php endif; ?>
                    <a href="?page=<?php echo $total_pages; ?>&category=<?php echo urlencode($category_filter); ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg bg-white hover:bg-primary-green hover:text-white transition-colors">
                        <?php echo $total_pages; ?>
                    </a>
                <?php endif; ?>

                <!-- Next Button -->
                <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>&category=<?php echo urlencode($category_filter); ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-lg bg-white hover:bg-gray-100 transition-colors">
                    Next →
                </a>
                <?php else: ?>
                <button class="px-4 py-2 border border-gray-300 rounded-lg bg-white opacity-50 cursor-not-allowed" disabled>
                    Next →
                </button>
                <?php endif; ?>
            </nav>
            
            <!-- Pagination Info -->
            <div class="text-center mt-4 text-sm text-gray-600">
                Showing <?php echo (($current_page - 1) * $blogs_per_page) + 1; ?> 
                to <?php echo min($current_page * $blogs_per_page, $total_blogs); ?> 
                of <?php echo $total_blogs; ?> articles
            </div>
            <?php endif; ?>

            <?php else: ?>
            <!-- No posts found -->
            <div class="text-center py-16">
                <div class="text-6xl mb-4">📝</div>
                <p class="text-2xl text-gray-600 mb-4">No blog posts found<?php echo $category_filter !== 'all' ? ' in this category' : ''; ?>.</p>
                <?php if ($category_filter !== 'all'): ?>
                <a href="?category=all" class="inline-block bg-primary-green text-white font-semibold px-6 py-3 rounded-lg hover:bg-primary-green-dark transition-colors">
                    View All Posts
                </a>
                <?php endif; ?>
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
                <input type="email" 
                       id="newsletter_email" 
                       placeholder="Enter your email" 
                       required 
                       aria-label="Email address" 
                       class="flex-1 px-6 py-3 rounded-lg border-none focus:outline-none focus:ring-2 focus:ring-white text-gray-800">
                <button type="submit" class="bg-white text-primary-green font-semibold px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors">
                    Subscribe
                </button>
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
            if (menu) menu.classList.toggle('hidden');
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
            
            messageDiv.innerHTML = '<p class="text-white bg-green-600 px-4 py-2 rounded-lg inline-block">✓ Thank you for subscribing!</p>';
            this.reset();
            
            setTimeout(() => {
                messageDiv.innerHTML = '';
            }, 5000);
        });
    </script>
</body>
</html>
