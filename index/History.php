<?php
// Include database connection
include 'config.php';
session_start();

if (!isset($_SESSION['email'])) {
    echo "Please log in to view your order history.";
    exit;
}

// Get the logged-in user's email
$userEmail = $_SESSION['email'];

// Query to get the customer ID based on email
$sqlCustomer = "SELECT customer_id FROM customer WHERE email = ?";
$stmt = $conn->prepare($sqlCustomer);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$resultCustomer = $stmt->get_result();

if ($resultCustomer->num_rows === 0) {
    echo "No user found with this email.";
    exit;
}

$customer = $resultCustomer->fetch_assoc();
$customerId = $customer['customer_id'];

// Query to get orders for this customer
$sqlOrders = "
    SELECT o.id AS order_id, o.order_date, o.delivery_method, o.reference_number, o.total_price
    FROM orders o
    WHERE o.customer_id = ?
    ORDER BY o.order_date DESC
";
$stmt = $conn->prepare($sqlOrders);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$resultOrders = $stmt->get_result();

// Check if orders exist
if ($resultOrders->num_rows === 0) {
    echo "No orders found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Order History</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Heading Styles */
        h1, h2, h3 {
            color: #2F5233;
            font-weight: 600;
            margin-bottom: 10px;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Button */
        button {
            background-color: #2F5233;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #3b6c4a;
        }

        button:disabled {
            background-color: #aab0a2;
            cursor: not-allowed;
        }

        /* Order Information */
        .order-info {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        th {
            background-color: #2F5233;
            color: #fff;
            font-weight: bold;
        }

        /* Table rows */
        tr:nth-child(even) {
            background-color: #f7f7f7;
        }

        tr:nth-child(odd) {
            background-color: #fff;
        }

        /* Product Image */
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            h1 {
                font-size: 24px;
            }

            table {
                font-size: 12px;
            }

            button {
                font-size: 14px;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>

<?php
echo "<a href='CustomerDashboard.php'><button>Back to Dashboard</button></a>";
// Display orders
echo "<h1>Your Order History</h1>";
while ($order = $resultOrders->fetch_assoc()) {
    echo "<div class='order-info'>";
    echo "<h2>Order #" . htmlspecialchars($order['reference_number']) . "</h2>";
    echo "<p>Order Date: " . htmlspecialchars($order['order_date']) . "</p>";
    echo "<p>Delivery Method: " . htmlspecialchars($order['delivery_method']) . "</p>";
    echo "<p>Total Price: ₱" . htmlspecialchars($order['total_price']) . "</p>";

    // Query to get order details
    $sqlOrderDetails = "
        SELECT od.product_id, od.quantity, od.price, p.product_name, p.image 
        FROM order_details od
        JOIN products p ON od.product_id = p.id
        WHERE od.order_id = ?
    ";

    $stmtDetails = $conn->prepare($sqlOrderDetails);
    $stmtDetails->bind_param("i", $order['order_id']);
    $stmtDetails->execute();
    $resultDetails = $stmtDetails->get_result();

    // Display order details
    echo "<h3>Order Details:</h3>";
    echo "<table>";
    echo "<tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Image</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Delivery Method</th> <!-- Changed to Delivery Method -->
        </tr>";
    while ($detail = $resultDetails->fetch_assoc()) {
        echo "<tr>
            <td>" . htmlspecialchars($detail['product_id']) . "</td>
            <td>" . htmlspecialchars($detail['product_name']) . "</td>
            <td><img class='product-image' src='uploaded_img/" . htmlspecialchars($detail['image']) . "' alt='Product Name: " . htmlspecialchars($detail['product_name']) . "'></td>
            <td>" . htmlspecialchars($detail['quantity']) . "</td>
            <td>₱" . htmlspecialchars($detail['price']) . "</td>
            <td>" . htmlspecialchars($order['delivery_method']) . "</td> <!-- Show delivery method -->
        </tr>";
    }
    echo "</table>";
    echo "</div><hr>";
}

$stmt->close();
$conn->close();
?>

</body>
</html>
