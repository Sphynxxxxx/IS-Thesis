<?php
session_start();
include 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: CustomerDashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = $_SESSION['customer_id'];
    
    // Begin a transaction
    $conn->begin_transaction();

    try {
        // Insert the order into the `orders` table
        $stmt = $conn->prepare("INSERT INTO orders (customer_id) VALUES (?)");
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $orderId = $stmt->insert_id;

        // Insert each product in the cart into the `order_details` table
        foreach ($_SESSION['cart'] as $item) {
            $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $orderId, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();
        }

        // Commit the transaction
        $conn->commit();

        // Clear the cart after placing the order
        unset($_SESSION['cart']);

        // Redirect to order confirmation page
        header("Location: order_confirmation.php?order_id=" . $orderId);
        exit();

    } catch (Exception $e) {
        // Rollback the transaction if something goes wrong
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>
