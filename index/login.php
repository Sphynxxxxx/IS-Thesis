<?php
session_start();

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "cart_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email']; 
    $pass = $_POST['password'];

    
    $stmt = $conn->prepare("SELECT password, lender_name, status FROM lender WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password, $name, $status);
        $stmt->fetch();

        
        if ($status !== 'approved') {
            echo json_encode(['success' => false, 'message' => "Your account is still pending to verify"]);
            exit();
        }

        
        if (password_verify($pass, $hashed_password)) {
            session_regenerate_id();
            $_SESSION['email'] = $email; 
            $_SESSION['lender_name'] = $name; 
            echo json_encode(['success' => true, 'message' => "Login successful!"]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => "Invalid password."]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Email not found."]);
    }

    $stmt->close();
}

$conn->close();
?>
