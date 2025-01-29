<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get the order ID from the AJAX request
if (isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    // Update the order status in the database
    $sql = "UPDATE orders SET status = 'received' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update order status.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Order ID not provided.']);
}

$conn->close();
?>
