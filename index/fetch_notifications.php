<?php
session_start();
include 'config.php';

$user_id = $_SESSION['customer_id']; 

// Fetch notifications for the user
$sql = "SELECT * FROM notifications WHERE customer_id = ? AND status = 'unread' ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'message' => $row['message'],
        'date' => $row['created_at']
    ];
}

echo json_encode(['notifications' => $notifications]);
?>
