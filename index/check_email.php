<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "cart_db2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);
$email = isset($data['email']) ? $data['email'] : '';

if ($email) {
    $stmt = $conn->prepare("SELECT email FROM customer WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['error' => 'No email provided']);
}

$conn->close();
?>