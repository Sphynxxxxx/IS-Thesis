<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$email = $_SESSION['email'];

// Fetch customer ID based on the email
$sqlCustomer = "SELECT customer_id FROM customer WHERE email = ?";
$stmtCustomer = $conn->prepare($sqlCustomer);
$stmtCustomer->bind_param("s", $email);
$stmtCustomer->execute();
$resultCustomer = $stmtCustomer->get_result();

if ($resultCustomer->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Customer not found!']);
    exit();
}

$customer = $resultCustomer->fetch_assoc();
$customer_id = $customer['customer_id'];

// Fetch notifications for the logged-in user along with the order status, order ID, and reference number
$sql = "
    SELECT n.*, o.status AS order_status, o.reference_number, n.order_id
    FROM notifications n
    LEFT JOIN orders o ON n.order_id = o.id
    WHERE n.customer_id = ? 
    ORDER BY n.date_created DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: #2F5233;
            color: white;
        }

        .header-container h1 {
            margin: 0;
            flex-grow: 1;
            text-align: center;
        }

        .back-button {
            display: inline-block;
            background-color: white;
            color: #2F5233;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #e4e4e4;
        }

        .notifications-container {
            width: 80%;
            margin: 20px auto;
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .notification {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .notification:hover {
            background-color: #f1f1f1;
        }

        .notification h3 {
            margin-top: 0;
            color: #333;
        }

        .notification p {
            margin: 10px 0;
            color: #666;
        }

        .notification small {
            display: block;
            margin-top: 10px;
            color: #999;
            font-size: 0.9em;
        }

        .no-notifications {
            text-align: center;
            padding: 20px;
            background-color: #ffeb3b;
            border-radius: 5px;
        }

        .received-button {
            display: inline-block;
            background-color: #2F5233;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .received-button:hover {
            background-color: #1e3a23;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <a href="CustomerDashboard.php" class="back-button">Back</a>
        <h1>Notifications</h1>
    </div>
    <div class="notifications-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $title = htmlspecialchars($row['title']);
                $message = htmlspecialchars($row['message']);
                $date_created = $row['date_created'];
                $order_status = htmlspecialchars($row['order_status']);
                $reference_number = htmlspecialchars($row['reference_number']);
                $order_id = htmlspecialchars($row['order_id']);

                if ($order_status === 'ready_to_pick_up') {
                    $order_status = 'Claimable';
                }

                if ($order_status === 'canceled') {
                    $order_status = 'Canceled';
                }
        ?>
                <div class="notification">
                    <h3><?php echo $title; ?></h3>
                    <p><?php echo $message; ?></p>
                    <p><strong>Order Status:</strong> <?php echo $order_status; ?></p>
                    <p><strong>Order ID:</strong> <?php echo $order_id; ?></p>
                    <p><strong>Reference Number:</strong> <?php echo $reference_number; ?></p>
                    <small>Received on: <?php echo $date_created; ?></small>
                    <?php if ($order_status === 'Claimable') { ?>
                        <button class="received-button" data-order-id="<?php echo $order_id; ?>">Receive</button>
                    <?php } ?>
                </div>
        <?php
            }
        } else {
            echo "<p class='no-notifications'>No notifications available.</p>";
        }
        ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.received-button').click(function () {
                var orderId = $(this).data('order-id');

                $.ajax({
                    url: 'mark_order_received.php',
                    type: 'POST',
                    data: { order_id: orderId },
                    success: function (response) {
                        var result = JSON.parse(response);
                        if (result.success) {
                            alert('Order marked as received.');
                            location.reload();
                        } else {
                            alert('Failed to mark order as received: ' + result.message);
                        }
                    },
                    error: function () {
                        alert('An error occurred while marking the order as received.');
                    }
                });
            });
        });
    </script>
</body>
</html>
