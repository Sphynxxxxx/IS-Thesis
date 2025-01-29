<?php
include 'config.php'; 

// Fetch only the necessary fields and approved products
$sql = "SELECT id, product_name, lender_name, location, price, image FROM products WHERE status = 'approved'";
$result = $conn->query($sql);

$products = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Encode the approved products to JSON format
header('Content-Type: application/json');  
echo json_encode($products);

$conn->close();
?>
