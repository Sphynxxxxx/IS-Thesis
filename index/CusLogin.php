<?php
session_start();

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "cart_db2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']); 
    $pass = $_POST['password'];

    // Updated query to fetch firstname and lastname instead of name
    $stmt = $conn->prepare("SELECT password, firstname, lastname, status, customer_id FROM customer WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password, $firstname, $lastname, $status, $customer_id);
        $stmt->fetch();

        // Check account status
        if ($status !== 'approved') {
            echo json_encode(['success' => false, 'message' => "Your account is still pending approval"]);
            exit();
        }

        // Verify password
        if (password_verify($pass, $hashed_password)) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Store user information in session
            $_SESSION['email'] = $email;
            $_SESSION['firstname'] = $firstname;
            $_SESSION['lastname'] = $lastname;
            $_SESSION['customer_id'] = $customer_id;
            $_SESSION['fullname'] = $firstname . ' ' . $lastname; // If you need the full name
            
            echo json_encode([
                'success' => true, 
                'message' => "Login successful!",
                'user' => [
                    'email' => $email,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'customer_id' => $customer_id
                ]
            ]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => "Invalid password"]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Email not found"]);
    }

    $stmt->close();
}

$conn->close();
?>