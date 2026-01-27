<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

require_once __DIR__ . '/../config.php';

try {
    $stmt = $conn->prepare("SELECT * FROM products");
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "status" => true,
        "count" => count($products),
        "data" => $products
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        "status" => false,
        "message" => "Internal Server Error"
    ]);
}
