/* ==========================================
   SHOPPING CART FUNCTIONALITY
   ========================================== */

// Initialize cart from localStorage or create empty cart
function initCart() {
    if (!localStorage.getItem('leafloom-cart')) {
        localStorage.setItem('leafloom-cart', JSON.stringify([]));
    }
    updateCartCount();
}

// Get cart items from localStorage
function getCart() {
    return JSON.parse(localStorage.getItem('leafloom-cart')) || [];
}

// Save cart to localStorage
function saveCart(cart) {
    localStorage.setItem('leafloom-cart', JSON.stringify(cart));
    updateCartCount();
}

// Add item to cart
function addToCart(productName, price, quantity = 1) {
    let cart = getCart();
    
    // Check if product already exists in cart
    const existingProductIndex = cart.findIndex(item => item.name === productName);
    
    if (existingProductIndex > -1) {
        // Product exists, update quantity
        cart[existingProductIndex].quantity += quantity;
    } else {
        // Add new product
        const product = {
            id: Date.now(), // Unique ID
            name: productName,
            price: price,
            quantity: quantity
        };
        cart.push(product);
    }
    
    saveCart(cart);
    showCartNotification(`${productName} added to cart!`);
}

// Remove item from cart
function removeFromCart(productId) {
    let cart = getCart();
    cart = cart.filter(item => item.id !== productId);
    saveCart(cart);
}

// Update item quantity
function updateQuantity(productId, newQuantity) {
    let cart = getCart();
    const product = cart.find(item => item.id === productId);
    
    if (product) {
        if (newQuantity <= 0) {
            removeFromCart(productId);
        } else {
            product.quantity = newQuantity;
            saveCart(cart);
        }
    }
}

// Calculate cart total
function getCartTotal() {
    const cart = getCart();
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

// Update cart count in navbar
function updateCartCount() {
    const cart = getCart();
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartCountElement = document.getElementById('cart-count');
    
    if (cartCountElement) {
        cartCountElement.textContent = totalItems;
    }
}

// Show cart notification
function showCartNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'cart-notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background-color: var(--primary-green);
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Open cart modal/page
function openCart() {
    const cart = getCart();
    
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    // Display cart items (can be enhanced with modal)
    let cartHTML = 'Your Cart:\n\n';
    cart.forEach(item => {
        cartHTML += `${item.name} - ₹${item.price} x ${item.quantity} = ₹${item.price * item.quantity}\n`;
    });
    cartHTML += `\nTotal: ₹${getCartTotal()}`;
    
    alert(cartHTML);
}

// Clear entire cart
function clearCart() {
    localStorage.setItem('leafloom-cart', JSON.stringify([]));
    updateCartCount();
}

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', function() {
    initCart();
});

// Add CSS animation for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
