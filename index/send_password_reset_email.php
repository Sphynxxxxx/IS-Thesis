<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Assuming PHPMailer is installed via Composer

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $email = $data->email;

    $token = bin2hex(random_bytes(16));  
    $resetLink = "http://localhost/FarmingAndRentalTools/reset_password.php?token=" . $token;


    $mail = new PHPMailer(true);
    
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  
        $mail->SMTPAuth = true;
        $mail->Username = 'larrydenverbiaco@gmail.com';  // Your Gmail address
        $mail->Password = 'brto wnuc kgvk xzva';  // Your Gmail password or App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('larrydenverbiaco@gmail.com', 'Your Name');
        $mail->addAddress($email);  // Send the reset link to the provided email

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = 'Hello,<br><br>Click the link below to reset your password:<br><a href="' . $resetLink . '">Reset Password</a><br><br>This link will expire in 1 hour.';

        // Send email
        $mail->send();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $mail->ErrorInfo]);
    }
}
?>
