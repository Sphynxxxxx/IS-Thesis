<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!$data || !isset($data['orderDetails']) || empty($data['orderDetails'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit();
}

// Start a database transaction
$conn->begin_transaction();

try {
    // Get customer ID using email from session
    $email = $_SESSION['email'];
    $customerQuery = "SELECT customer_id FROM customer WHERE email = ?";
    $stmt = $conn->prepare($customerQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Customer not found');
    }
    
    $customer = $result->fetch_assoc();
    $customerId = $customer['customer_id'];

    // Generate unique reference number
    $referenceNumber = 'REF-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

    // Calculate total price
    $totalPrice = 0;
    foreach ($data['orderDetails'] as $item) {
        $totalPrice += $item['price'] * $item['quantity'];
    }

    // Insert into orders table
    $orderQuery = "INSERT INTO orders (customer_id, reference_number, delivery_method, total_price, order_date, status) 
                   VALUES (?, ?, ?, ?, NOW(), 'pending')";
    $stmt = $conn->prepare($orderQuery);
    $deliveryMethod = $data['deliveryMethod'] ?? 'pickup';
    $stmt->bind_param("issd", $customerId, $referenceNumber, $deliveryMethod, $totalPrice);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create order: " . $stmt->error);
    }
    
    $orderId = $conn->insert_id;

    // Insert order details and update product quantities
    $detailQuery = "INSERT INTO order_details (order_id, product_id, quantity, price, start_date, end_date) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $detailStmt = $conn->prepare($detailQuery);

    foreach ($data['orderDetails'] as $item) {
        // Check product availability
        $stockQuery = "SELECT quantity FROM products WHERE id = ? FOR UPDATE";
        $stockStmt = $conn->prepare($stockQuery);
        $stockStmt->bind_param("i", $item['id']);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result();
        
        if ($stockResult->num_rows === 0) {
            throw new Exception("Product not found: ID " . $item['id']);
        }
        
        $product = $stockResult->fetch_assoc();
        if ($product['quantity'] < $item['quantity']) {
            throw new Exception("Insufficient stock for product ID " . $item['id']);
        }

        // Insert order detail
        $detailStmt->bind_param(
            "iiidss", 
            $orderId, 
            $item['id'], 
            $item['quantity'], 
            $item['price'],
            $data['start_date'],
            $data['end_date']
        );
        
        if (!$detailStmt->execute()) {
            throw new Exception("Failed to create order detail: " . $detailStmt->error);
        }

        // Update product quantity
        $updateStock = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateStock);
        $updateStmt->bind_param("ii", $item['quantity'], $item['id']);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Failed to update product quantity: " . $updateStmt->error);
        }
    }

    // Create notification
    $notifyQuery = "INSERT INTO notifications (customer_id, title, message, date_created, order_id) 
                    VALUES (?, 'Order Status Update', 'You have successfully placed your order.', NOW(), ?)";
    $notifyStmt = $conn->prepare($notifyQuery);
    $notifyStmt->bind_param("ii", $customerId, $orderId);
    
    if (!$notifyStmt->execute()) {
        throw new Exception("Failed to create notification: " . $notifyStmt->error);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully!',
        'referenceNumber' => $referenceNumber
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>