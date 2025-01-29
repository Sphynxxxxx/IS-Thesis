<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'cart_db2');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to fetch status of all orders
$sql = "SELECT id, status FROM orders";
$result = $conn->query($sql);

// Handle query failure
if (!$result) {
    die("Error executing query: " . $conn->error);
}

$orderStatuses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orderStatuses[] = $row;
    }
}

// Close the connection
$conn->close();

// Return the status as a JSON response
echo json_encode($orderStatuses);
?>
