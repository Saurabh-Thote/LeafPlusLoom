<?php
/**
 * Leaf+Loom Admin Login Page
 * Secure authentication with session management
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

// Include database connection
require_once '../config.php';

$error_message = '';
$success_message = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        try {
            // Fetch admin details from database
            $stmt = $conn->prepare("SELECT * FROM admin WHERE admin_username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                // Verify password (assuming passwords are stored as plain text - you should hash them!)
                if ($password === $admin['admin_password']) {
                    // Password correct - create session
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_username'] = $admin['admin_username'];
                    $_SESSION['admin_full_name'] = $admin['admin_full_name'];
                    $_SESSION['admin_email'] = $admin['admin_email'];
                    $_SESSION['login_time'] = time();
                    
                    // Redirect to dashboard
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error_message = "Invalid username or password!";
                }
            } else {
                $error_message = "Invalid username or password!";
            }
        } catch(PDOException $e) {
            $error_message = "Login failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Leaf+Loom</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-primary-green: #4A7C59;
            --color-primary-green-dark: #3A6047;
            --color-secondary-green: #7FB069;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-primary-green/10 via-white to-secondary-green/10 min-h-screen flex items-center justify-center font-[system-ui]">
    
    <div class="w-full max-w-md px-6">
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            
            <!-- Header with Logo -->
            <div class="bg-gradient-to-r from-primary-green to-secondary-green p-8 text-center">
                <div class="inline-block bg-white rounded-full p-4 mb-4">
                    <svg class="w-12 h-12 text-primary-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Leaf+Loom</h1>
                <p class="text-green-100">Admin Panel Login</p>
            </div>
            
            <!-- Login Form -->
            <div class="p-8">
                
                <?php if ($error_message): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-red-700 font-medium"><?php echo htmlspecialchars($error_message); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="space-y-6">
                    
                    <!-- Username Field -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                            Username
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required
                                autocomplete="username"
                                placeholder="Enter your username"
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent transition-all"
                                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                            >
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent transition-all"
                            >
                        </div>
                    </div>
                    
                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-primary-green border-gray-300 rounded focus:ring-primary-green">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="#" class="text-sm text-primary-green hover:text-primary-green-dark font-medium">
                            Forgot password?
                        </a>
                    </div>
                    
                    <!-- Login Button -->
                    <button 
                        type="submit" 
                        name="login"
                        class="w-full bg-gradient-to-r from-primary-green to-secondary-green hover:from-primary-green-dark hover:to-primary-green text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary-green focus:ring-offset-2"
                    >
                        Sign In
                    </button>
                </form>
                
                <!-- Footer Info -->
                <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-500">
                        🌿 Secure Admin Access
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Demo Credentials (Remove in production!) -->
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
            <p class="text-xs text-yellow-800 font-semibold mb-2">📌 Demo Credentials:</p>
            <p class="text-xs text-yellow-700">Username: <code class="bg-yellow-100 px-2 py-1 rounded">saurabhtthote369</code></p>
            <p class="text-xs text-yellow-700">Password: <code class="bg-yellow-100 px-2 py-1 rounded">Saur@bh_thote963</code></p>
        </div>
        
        <!-- Back to Website -->
        <div class="text-center mt-4">
            <a href="../index.html" class="text-sm text-gray-600 hover:text-primary-green transition-colors">
                ← Back to Leaf+Loom Website
            </a>
        </div>
    </div>
    
</body>
</html>
