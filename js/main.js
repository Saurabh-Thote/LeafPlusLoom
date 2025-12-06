/* ==========================================
   MAIN JAVASCRIPT - LEAF+ LOOM
   ========================================== */

// Mobile Menu Toggle
function toggleMenu() {
    const navLinks = document.getElementById('navLinks');
    navLinks.classList.toggle('active');
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const navLinks = document.getElementById('navLinks');
    const menuToggle = document.querySelector('.menu-toggle');
    
    if (navLinks && navLinks.classList.contains('active')) {
        if (!navLinks.contains(event.target) && !menuToggle.contains(event.target)) {
            navLinks.classList.remove('active');
        }
    }
});

// Smooth Scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Product Filter Function
function filterProducts() {
    const category = document.getElementById('category').value;
    const products = document.querySelectorAll('.product-card');
    
    products.forEach(product => {
        if (category === 'all') {
            product.style.display = 'block';
        } else {
            // This would need data-category attributes on product cards
            const productCategory = product.getAttribute('data-category');
            if (productCategory === category) {
                product.style.display = 'block';
            } else {
                product.style.display = 'none';
            }
        }
    });
}

// Product Sort Function
function sortProducts() {
    const sortValue = document.getElementById('sort').value;
    const productGrid = document.getElementById('productGrid');
    const products = Array.from(document.querySelectorAll('.product-card'));
    
    products.sort((a, b) => {
        const priceA = parseInt(a.querySelector('.price').textContent.replace('₹', ''));
        const priceB = parseInt(b.querySelector('.price').textContent.replace('₹', ''));
        
        switch(sortValue) {
            case 'price-low':
                return priceA - priceB;
            case 'price-high':
                return priceB - priceA;
            case 'newest':
                // Would need data-date attributes
                return 0;
            default:
                return 0;
        }
    });
    
    // Re-append sorted products
    productGrid.innerHTML = '';
    products.forEach(product => productGrid.appendChild(product));
}

// Blog Filter Function
function filterBlog(category) {
    const blogPosts = document.querySelectorAll('.blog-card');
    const filterTabs = document.querySelectorAll('.filter-tab');
    
    // Update active tab
    filterTabs.forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filter posts
    blogPosts.forEach(post => {
        if (category === 'all') {
            post.style.display = 'block';
        } else {
            const postCategory = post.querySelector('.post-category').textContent.toLowerCase();
            if (postCategory.includes(category)) {
                post.style.display = 'block';
            } else {
                post.style.display = 'none';
            }
        }
    });
}

// Newsletter Subscription
function subscribeNewsletter(event) {
    event.preventDefault();
    const emailInput = event.target.querySelector('input[type="email"]');
    const email = emailInput.value;
    
    // Here you would typically send this to your backend
    console.log('Newsletter subscription:', email);
    
    // Show success message
    alert(`Thank you for subscribing with ${email}!`);
    emailInput.value = '';
}

// Contact Form Submission
function submitContactForm(event) {
    event.preventDefault();
    
    const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        subject: document.getElementById('subject').value,
        message: document.getElementById('message').value
    };
    
    // Here you would typically send this to your backend
    console.log('Form submission:', formData);
    
    // Show success message
    const formMessage = document.getElementById('formMessage');
    formMessage.textContent = 'Thank you! Your message has been sent successfully. We will get back to you soon.';
    formMessage.className = 'form-message success';
    
    // Reset form
    document.getElementById('contactForm').reset();
    
    // Hide message after 5 seconds
    setTimeout(() => {
        formMessage.style.display = 'none';
    }, 5000);
}

// Scroll to Top Button (Optional Enhancement)
function createScrollToTop() {
    const scrollBtn = document.createElement('button');
    scrollBtn.innerHTML = '↑';
    scrollBtn.className = 'scroll-to-top';
    scrollBtn.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background-color: var(--primary-green);
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 24px;
        cursor: pointer;
        display: none;
        z-index: 1000;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    `;
    
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollBtn.style.display = 'block';
        } else {
            scrollBtn.style.display = 'none';
        }
    });
    
    document.body.appendChild(scrollBtn);
}

// Lazy Loading Images
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Form Validation Helper
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Initialize all functions on DOM load
document.addEventListener('DOMContentLoaded', function() {
    // Create scroll to top button
    createScrollToTop();
    
    // Initialize lazy loading
    lazyLoadImages();
    
    // Add active class to current page in navigation
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-links a');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage || 
            (currentPage === '' && link.getAttribute('href') === 'index.html')) {
            link.classList.add('active');
        }
    });
});

// Loading Animation (Optional)
window.addEventListener('load', function() {
    document.body.classList.add('loaded');
});

// Print friendly function
function printPage() {
    window.print();
}

// Share functionality (for blog posts)
function shareContent(platform, url, title) {
    let shareUrl = '';
    
    switch(platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`;
            break;
        case 'pinterest':
            shareUrl = `https://pinterest.com/pin/create/button/?url=${encodeURIComponent(url)}`;
            break;
    }
    
    window.open(shareUrl, '_blank', 'width=600,height=400');
}

// Search functionality (can be enhanced)
function searchProducts(query) {
    const products = document.querySelectorAll('.product-card');
    query = query.toLowerCase();
    
    products.forEach(product => {
        const title = product.querySelector('h3').textContent.toLowerCase();
        const description = product.querySelector('.product-desc').textContent.toLowerCase();
        
        if (title.includes(query) || description.includes(query)) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}
