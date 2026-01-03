<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare("
            INSERT INTO products (
                title, slug, description, short_description, main_image, 
                price, original_price, stock_quantity, stock_status, sku, 
                category, is_featured, is_new_arrival, status, created_at
            ) VALUES (
                :title, :slug, :description, :short_description, :main_image, 
                :price, :original_price, :stock_quantity, :stock_status, :sku, 
                :category, :is_featured, :is_new_arrival, :status, NOW()
            )
        ");
        
        $slug = strtolower(str_replace(' ', '-', $_POST['title']));
        
        $stmt->execute([
            'title' => $_POST['title'],
            'slug' => $slug,
            'description' => $_POST['description'],
            'short_description' => $_POST['short_description'],
            'main_image' => $_POST['main_image'],
            'price' => $_POST['price'],
            'original_price' => $_POST['original_price'] ?? null,
            'stock_quantity' => $_POST['stock_quantity'],
            'stock_status' => $_POST['stock_status'],
            'sku' => $_POST['sku'],
            'category' => $_POST['category'],
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_new_arrival' => isset($_POST['is_new_arrival']) ? 1 : 0,
            'status' => $_POST['status']
        ]);
        
        header("Location: admin-products-list.php?success=1");
        exit;
        
    } catch(PDOException $e) {
        header("Location: admin-products-list.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}
?>
