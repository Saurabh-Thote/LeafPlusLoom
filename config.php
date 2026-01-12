<?php
/**
 * Leaf+Loom Configuration File
 * Simple database connection and site settings
 */

// Database Configuration
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_USER', 'if0_40842705');
define('DB_PASS', 'DarkCamper369');
define('DB_NAME', 'if0_40842705_leafplusloom');

// Create Database Connection (PDO)
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    
    // Set error mode to exceptions
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
