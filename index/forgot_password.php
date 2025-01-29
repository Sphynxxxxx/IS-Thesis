<?php
// Include database connection
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if email exists in the database
    $query = $conn->prepare("SELECT * FROM customer WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(16)); // Secure random token
        $expiration = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

        // Store the token and expiration in the database
        $update = $conn->prepare("UPDATE customer SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expiration, $email);
        $update->execute();

        // Prepare reset link and email details
        $resetLink = "http://localhost/dump/FarmingAndRentalTools/reset_password.php?token=" . $token;
        $subject = "Password Reset Request";
        $message = "Hello,\n\nClick the link below to reset your password:\n" . $resetLink . "\n\nThis link will expire in 1 hour.";
        $headers = "From: larrydenverbiaco@gmail.com\r\n";

        // Send the email
        if (mail($email, $subject, $message, $headers)) {
            echo "Password reset email sent!";
        } else {
            echo "Failed to send email.";
        }
    } else {
        echo "Email not found.";
    }
}
?>
