<?php
include 'config.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT quantity, rent_days FROM products WHERE id = '$id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        echo json_encode([
            'quantity' => (int)$product['quantity'],
            'rent_days' => (int)$product['rent_days']
        ]);
    } else {
        echo json_encode(['quantity' => 0, 'rent_days' => 0]);
    }
} else {
    echo json_encode(['error' => 'Product ID not provided']);
}

mysqli_close($conn);
?>