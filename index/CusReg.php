<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "cart_db2";

// Create a new database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Database connection failed: " . $conn->connect_error]));
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $firstname = $conn->real_escape_string(trim($_POST['firstname']));
    $lastname = $conn->real_escape_string(trim($_POST['lastname']));
    $contact_number = $conn->real_escape_string(trim($_POST['contact']));
    $address = $conn->real_escape_string(trim($_POST['address']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // Validate if required fields are not empty
    if (empty($firstname) || empty($lastname)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "First name and last name are required."]);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Invalid email format."]);
        exit();
    }

    // Hash the password securely
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // Validate and process the uploaded image
    $images = null;
    $allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif']; 
    $maxFileSize = 10 * 1024 * 1024; // 10 MB

    if (isset($_FILES['images']) && $_FILES['images']['error'] === UPLOAD_ERR_OK) {
        $imagesTmpPath = $_FILES['images']['tmp_name'];
        $imagesName = basename($_FILES['images']['name']);
        $imagesMimeType = mime_content_type($imagesTmpPath); 
        $imagesSize = $_FILES['images']['size'];

        // Validate file type
        if (!in_array($imagesMimeType, $allowedFileTypes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Invalid image type. Only JPG, PNG, and GIF are allowed."]);
            exit();
        }

        // Validate file size
        if ($imagesSize > $maxFileSize) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Image size exceeds the 10MB limit."]);
            exit();
        }

        // Create uploads directory if it doesn't exist
        if (!file_exists('Cus_uploads')) {
            mkdir('Cus_uploads', 0777, true);
        }

        // Sanitize and generate unique file name
        $imagesName = preg_replace("/[^a-zA-Z0-9\._-]/", "", $imagesName); 
        $imagesPath = 'Cus_uploads/' . uniqid('', true) . '_' . $imagesName; 

        // Move the uploaded file to the destination folder
        if (!move_uploaded_file($imagesTmpPath, $imagesPath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => "Image upload failed."]);
            exit();
        }

        $images = $imagesPath;
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "No image uploaded or upload error."]);
        exit();
    }

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT email FROM customer WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Email already exists!"]);
        $checkEmail->close();
        exit();
    }
    $checkEmail->close();

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO customer (firstname, lastname, contact_number, address, email, password, images, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("sssssss", $firstname, $lastname, $contact_number, $address, $email, $password, $images);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => "Registration successful! Awaiting approval."]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => "Error: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>