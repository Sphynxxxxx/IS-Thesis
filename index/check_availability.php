<?php
include 'config.php';

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['quantity' => $row['quantity']]);
    } else {
        echo json_encode(['quantity' => 0]);
    }
} else {
    echo json_encode(['error' => 'No product ID provided']);
}
?>