<?php
require 'vendor/autoload.php';  
require 'config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $query = $conn->prepare("SELECT * FROM customer WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $query->bind_param("s", $token);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE customer SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
            $update->bind_param("ss", $newPassword, $token);
            $update->execute();

            echo "Password has been reset successfully!";
        } else {
            echo '
            <form method="POST">
                <label for="password">New Password:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit">Reset Password</button>
            </form>';
        }
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided.";
}
?>
