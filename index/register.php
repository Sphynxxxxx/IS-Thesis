<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "cart_db";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Database connection failed: " . $conn->connect_error]));
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $name = $conn->real_escape_string(trim($_POST['lender_name']));
    $contact_number = $conn->real_escape_string(trim($_POST['contact']));
    $address = $conn->real_escape_string(trim($_POST['address']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Invalid email format."]);
        exit();
    }

    // Check for duplicate email
    $emailCheck = $conn->prepare("SELECT id FROM lender WHERE email = ?");
    $emailCheck->bind_param("s", $email);
    $emailCheck->execute();
    $emailCheck->store_result();

    if ($emailCheck->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Email already exists."]);
        exit();
    }
    $emailCheck->close();

    // Hash the password securely
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    
    $images = null;
    $allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif']; 
    $maxFileSize = 10 * 1024 * 1024; 

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

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO lender (lender_name, contact_number, address, email, password, images, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("ssssss", $name, $contact_number, $address, $email, $password, $images);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => "Registration successful! Awaiting approval."]);
    } else {
        if ($stmt->errno == 1062) { 
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Duplicate entry for email."]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => "Database error: " . $stmt->error]);
        }
    }

    $stmt->close();
}

$conn->close();
?>
