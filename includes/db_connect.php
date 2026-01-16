<?php
// Database configuration
$host = 'sql100.infinityfree.com'; // Your InfinityFree MySQL host
$username = 'if0_40842705'; // Your database username
$password = 'DarkCamper369'; // Your database password
$database = 'if0_40842705_leafplusloom'; // Your database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");
?>
