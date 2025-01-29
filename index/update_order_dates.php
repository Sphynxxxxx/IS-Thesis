<?php
session_start();
include 'config.php';

// Assuming order ID is passed or fetched from session (you can adjust accordingly)
$order_id = $_SESSION['order_id'];

// Retrieve POST data (in JSON format)
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['start_date']) && isset($data['end_date'])) {
    $start_date = $data['start_date'];
    $end_date = $data['end_date'];
    
    // Prepare the SQL query to update the order details with start and end dates
    $query = "UPDATE order_details SET start_date = ?, end_date = ? WHERE order_id = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssi", $start_date, $end_date, $order_id);
        
        if ($stmt->execute()) {
            // Respond back with success
            echo json_encode(['success' => true]);
        } else {
            // Respond back with an error
            echo json_encode(['success' => false, 'message' => 'Failed to save rental period.']);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database query failed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
}
?>
