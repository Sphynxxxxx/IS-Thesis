<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  
require 'config.php';  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    if (!$email) {
        echo "Invalid email format.";
        exit;
    }

    $query = $conn->prepare("SELECT * FROM customer WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(16));
        $expiration = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conn->prepare("UPDATE customer SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expiration, $email);
        $update->execute();

        $resetLink = "reset_password.php?token=" . $token;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'larrydenverbiaco@gmail.com';  
            $mail->Password = 'brto wnuc kgvk xzva';  
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('larrydenverbiaco@gmail.com', 'Farming and Rental Tools');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Hello,<br><br>Click the link below to reset your password:<br><a href='" . $resetLink . "'>Reset Password</a><br><br>This link will expire in 1 hour.";

            $mail->send();
            echo json_encode(['success' => true, 'message' => 'If this email exists in our records, a reset link has been sent.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to send email.']);
        }
    } else {
        echo json_encode(['success' => true, 'message' => 'If this email exists in our records, a reset link has been sent.']);
    }
}
?>
