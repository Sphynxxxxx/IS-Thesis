<?php
session_start();
include 'config.php';

// Early validation checks
if (!isset($_SESSION['email']) || !isset($_SESSION['customer_id'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized access. Please log in.'
    ]);
    exit();
}

// Get raw POST data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Validate input
if (!isset($data['orderDetails']) || !is_array($data['orderDetails']) || empty($data['orderDetails'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid order data.'
    ]);
    exit();
}

$customerId = $_SESSION['customer_id'];
$deliveryMethod = $data['deliveryMethod'] ?? 'pickup';
$referenceNumber = 'ORD-' . uniqid();

try {
    // Start transaction
    $conn->begin_transaction();

    // Calculate total price
    $totalPrice = 0;

    // Insert order
    $orderStmt = $conn->prepare("INSERT INTO orders (customer_id, delivery_method, reference_number, total_price) VALUES (?, ?, ?, ?)");
    $orderStmt->bind_param("issd", $customerId, $deliveryMethod, $referenceNumber, $totalPrice);
    $orderStmt->execute();
    $orderId = $conn->insert_id;

    // Prepare statements
    $detailsStmt = $conn->prepare("
        INSERT INTO order_details 
        (order_id, product_id, quantity, price, shippingfee) 
        VALUES (?, ?, ?, ?, ?)
    ");

    $productStmt = $conn->prepare("
        UPDATE products 
        SET quantity = quantity - ? 
        WHERE id = ? AND quantity >= ?
    ");

    // Process each order item
    foreach ($data['orderDetails'] as $item) {
        $productId = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $shippingFee = $item['shippingFee'];

        // Validate product availability
        $checkStmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
        $checkStmt->bind_param("i", $productId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $productData = $result->fetch_assoc();

        if (!$productData || $productData['quantity'] < $quantity) {
            throw new Exception("Insufficient quantity for product ID: $productId");
        }

        // Calculate item total
        $itemTotal = $price * $quantity;
        $totalPrice += $itemTotal;

        // Insert order details
        $detailsStmt->bind_param(
            "iisdd", 
            $orderId, 
            $productId, 
            $quantity, 
            $price, 
            $shippingFee
        );
        $detailsStmt->execute();

        // Update product quantity
        $productStmt->bind_param("iii", $quantity, $productId, $quantity);
        $productStmt->execute();

        // Check if update was successful
        if ($conn->affected_rows === 0) {
            throw new Exception("Failed to update product quantity for ID: $productId");
        }
    }

    // Update order total price
    $updateTotalStmt = $conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
    $updateTotalStmt->bind_param("di", $totalPrice, $orderId);
    $updateTotalStmt->execute();

    // Commit transaction
    $conn->commit();

    // Respond with success
    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'reference_number' => $referenceNumber,
        'total_price' => $totalPrice
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    // Log error (consider using proper error logging in production)
    error_log("Order processing error: " . $e->getMessage());

    // Respond with error
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Order processing failed. ' . $e->getMessage()
    ]);
}

// Close statements and connection
$orderStmt->close();
$detailsStmt->close();
$productStmt->close();
$conn->close();
?>