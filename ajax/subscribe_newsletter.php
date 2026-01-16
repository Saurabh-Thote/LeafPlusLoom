<?php
header('Content-Type: application/json');

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Check if email already exists
    $check_sql = "SELECT id FROM newsletter_subscribers WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This email is already subscribed']);
        exit;
    }
    
    // Insert new subscriber
    $insert_sql = "INSERT INTO newsletter_subscribers (email, subscribed_date) VALUES (?, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("s", $email);
    
    if ($insert_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Thank you for subscribing!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Subscription failed. Please try again.']);
    }
    
    $insert_stmt->close();
    $check_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>
