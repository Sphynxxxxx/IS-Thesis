<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: CustomerDashboard.php");
    exit();
}

// Get the customer's ID from the session
$customer_email = $_SESSION['email'];
$customer_query = "SELECT customer_id, CONCAT(firstname, ' ', lastname) as full_name FROM customer WHERE email = ?";
$stmt = $conn->prepare($customer_query);
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$customer_result = $stmt->get_result();
$customer = $customer_result->fetch_assoc();
$customer_id = $customer['customer_id'];

// Fetch the most recent order for this customer
$order_query = "SELECT o.id AS order_id, o.reference_number, o.order_date, o.delivery_method, 
                o.total_price, c.firstname, c.lastname, c.email, c.contact_number, 
                CONCAT(c.town, ' - ', c.address) as full_address
                FROM orders o
                JOIN customer c ON o.customer_id = c.customer_id
                WHERE o.customer_id = ? 
                ORDER BY o.order_date DESC 
                LIMIT 1";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

// Fetch order details if order exists
if ($order) {
    $order_details_query = "
        SELECT 
            od.id AS detail_id,
            od.quantity, 
            od.price, 
            p.product_name, 
            p.image AS product_image,
            od.start_date, 
            od.end_date
        FROM order_details od
        JOIN products p ON od.product_id = p.id
        WHERE od.order_id = ?";
    $stmt = $conn->prepare($order_details_query);
    $stmt->bind_param("i", $order['order_id']);
    $stmt->execute();
    $order_details_result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="css/Customercss.css?v=1.0">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            
            /* Centering the content inside the container */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .main-content {
            padding: 20px;
        }

        h2 {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #2F5233;
        }

        .order-confirmation {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .order-confirmation h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #2F5233;
        }

        .order-confirmation p {
            font-size: 16px;
            margin: 5px 0;
        }

        .order-confirmation strong {
            font-weight: bold;
        }

        /* Flexbox layout for the order details */
        .order-details {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .order-details .detail {
            display: flex;
            flex-direction: column;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
        }

        .order-details .detail h4 {
            font-size: 18px;
            color: #2F5233;
        }

        .order-details .detail p {
            font-size: 16px;
        }

        input[type="date"] {
            width: 130px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f7f7f7;
            font-size: 16px;
            color: #333;
            margin-top: 5px;
        }

        input[type="date"]:focus {
            outline: none;
            border-color: #2F5233;
            background-color: #ffffff;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            font-size: 14px;
            color: #777;
        }

        footer p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <h2>Order Confirmation</h2>
            
            <?php if ($order): ?>
            <div class="order-confirmation">
                <h3>Order Reference Number: <?php echo htmlspecialchars($order['reference_number']); ?></h3>
                
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['firstname'] . ' ' . $order['lastname']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($order['contact_number']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($order['full_address']); ?></p>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                <p><strong>Delivery Method:</strong> <?php echo ucfirst(htmlspecialchars($order['delivery_method'])); ?></p>

                <h3>Order Details</h3>
                <div class="order-details">
                    <?php 
                    $total = 0;
                    while ($detail = $order_details_result->fetch_assoc()): 
                        $subtotal = $detail['quantity'] * $detail['price'];
                        $total += $subtotal;
                    ?>
                    <div class="detail">
                        <h4><?php echo htmlspecialchars($detail['product_name']); ?></h4>
                        <p><strong>Quantity:</strong> <?php echo htmlspecialchars($detail['quantity']); ?></p>
                        <p><strong>Price:</strong> ₱<?php echo number_format($detail['price'], 2); ?></p>
                        <p><strong>Subtotal:</strong> ₱<?php echo number_format($subtotal, 2); ?></p>
                        <p><strong>Rental Period:</strong><br>
                           From: <?php echo date('F j, Y', strtotime($detail['start_date'])); ?><br>
                           To: <?php echo date('F j, Y', strtotime($detail['end_date'])); ?></p>
                        <img src="uploaded_img/<?php echo htmlspecialchars($detail['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($detail['product_name']); ?>" 
                             class="product-image">
                    </div>
                    <?php endwhile; ?>

                    <div class="order-total">
                        <h3>Total Amount: ₱<?php echo number_format($total, 2); ?></h3>
                    </div>
                </div>

                <div class="order-actions">
                    <a href="CustomerDashboard.php" class="btn">Back to Dashboard</a>
                    <a href="download_pdf.php?order_reference=<?php echo urlencode($order['reference_number']); ?>" class="btn">Download PDF</a>
                </div>
            </div>
            <?php else: ?>
            <p>No order found.</p>
            <div class="order-actions">
                <a href="CustomerDashboard.php" class="btn">Back to Dashboard</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>