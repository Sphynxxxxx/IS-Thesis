<?php
// update_stock.php

include 'config.php';

if (isset($_POST['productId']) && isset($_POST['quantity'])) {
    $productId = $_POST['productId'];
    $quantity = $_POST['quantity'];

    // Update the stock quantity in the database
    $sql = "UPDATE products SET quantity = quantity - $quantity WHERE id = $productId";
    if ($conn->query($sql) === TRUE) {
        echo "Stock updated successfully";
    } else {
        echo "Error updating stock: " . $conn->error;
    }

    $conn->close();
}
?>
