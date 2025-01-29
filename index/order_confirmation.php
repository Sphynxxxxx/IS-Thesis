<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: CustomerDashboard.php");
    exit();
}

echo "<h1>Your order has been placed successfully!</h1>";


?>
