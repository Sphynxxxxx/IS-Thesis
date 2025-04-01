<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: CustomerDashboard.php");
    exit();
}

// Get customer information
$customer_email = $_SESSION['email'];
$customer_query = "SELECT customer_id, CONCAT(firstname, ' ', lastname) as full_name FROM customer WHERE email = ?";
$stmt = $conn->prepare($customer_query);
$stmt->bind_param("s", $customer_email);
$stmt->execute();
$customer_result = $stmt->get_result();
$customer = $customer_result->fetch_assoc();
$customer_id = $customer['customer_id'];

// Check if order_id is provided through GET or in session
$order_id = null;
if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    // Store in session for future use
    $_SESSION['current_order_id'] = $order_id;
} elseif (isset($_SESSION['current_order_id'])) {
    $order_id = $_SESSION['current_order_id'];
} else {
    // If no specific order is requested, get the most recent order
    $latest_order_query = "SELECT id FROM orders WHERE customer_id = ? ORDER BY order_date DESC LIMIT 1";
    $stmt = $conn->prepare($latest_order_query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $latest_result = $stmt->get_result();
    if ($latest_result->num_rows > 0) {
        $latest = $latest_result->fetch_assoc();
        $order_id = $latest['id'];
        $_SESSION['current_order_id'] = $order_id;
    }
}

// Initialize $order to avoid undefined variable error
$order = null;
$order_details_result = null;

// Only proceed if we have an order_id
if ($order_id) {
    // Get order details
    $order_query = "SELECT o.id AS order_id, o.reference_number, o.order_date, 
                    o.total_price, c.firstname, c.lastname, c.email, c.contact_number, 
                    CONCAT(c.town, ' - ', c.address) as full_address
                    FROM orders o
                    JOIN customer c ON o.customer_id = c.customer_id
                    WHERE o.id = ? AND o.customer_id = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("ii", $order_id, $customer_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    
    if ($order_result->num_rows > 0) {
        $order = $order_result->fetch_assoc();
        
        // Get order details
        $order_details_query = "
            SELECT 
                od.id AS detail_id,
                od.quantity, 
                od.price, 
                p.product_name, 
                p.image AS product_image,
                p.rent_days,
                p.penalty_amount,
                od.start_date, 
                od.end_date,
                p.id AS product_id
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            WHERE od.order_id = ?";
        $stmt = $conn->prepare($order_details_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order_details_result = $stmt->get_result();
    }
}

// Function to calculate rental duration
function calculateRentalDuration($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    return $interval->days;
}

// Function to calculate penalty
function calculatePenalty($amount, $rentalDuration, $maxRentDays, $penaltyRate = 0.05) {
    if ($rentalDuration > $maxRentDays) {
        $overdueDays = $rentalDuration - $maxRentDays;
        return $amount * $penaltyRate * $overdueDays;
    }
    return 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/Customercss.css?v=1.0">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #2F5233;
        }

        .order-header {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #ddd;
        }

        .order-header h3 {
            color: #2F5233;
            margin-bottom: 15px;
        }

        .customer-info {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #ddd;
        }

        .product-card {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .product-header {
            background-color: #2F5233;
            color: white;
            padding: 10px 15px;
        }

        .product-body {
            padding: 15px;
        }

        .product-image {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            object-fit: cover;
        }

        .rental-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
        }

        .penalty-details {
            background-color: #fff8f8;
            border-left: 3px solid #d32f2f;
            padding: 10px;
            margin-top: 15px;
            border-radius: 4px;
        }

        .overdue {
            color: #d32f2f;
            font-weight: bold;
        }

        .order-total {
            background-color: #f0f8ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #b8daff;
        }

        .order-actions {
            margin-top: 25px;
            text-align: center;
        }

        .btn-custom {
            background-color: #2F5233;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin: 0 10px;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .btn-custom:hover {
            background-color: #1e3921;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <h2>Order Confirmation</h2>
            
            <?php if ($order): ?>
            <!-- Order Reference Number -->
            <div class="order-header">
                <h3 class="text-center">Order Reference Number: <?php echo htmlspecialchars($order['reference_number']); ?></h3>
                <p class="text-center mb-0"><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
            </div>
            
            <!-- Customer Information -->
            <div class="customer-info">
                <h3>Customer Information</h3>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['firstname'] . ' ' . $order['lastname']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($order['contact_number']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($order['full_address']); ?></p>
                    </div>
                </div>
            </div>

            <h3 class="mb-4">Order Details</h3>
            <div class="order-details">
                <?php 
                $total = 0;
                $totalPenalty = 0;
                
                if ($order_details_result && $order_details_result->num_rows > 0) {
                    // First pass to calculate totals
                    while ($detail = $order_details_result->fetch_assoc()): 
                        $subtotal = $detail['quantity'] * $detail['price'];
                        $rentalDuration = calculateRentalDuration($detail['start_date'], $detail['end_date']);
                        $penalty = calculatePenalty(
                            $subtotal, 
                            $rentalDuration, 
                            $detail['rent_days'],
                            $detail['penalty_amount'] ?? 0.05
                        );
                        $total += $subtotal;
                        $totalPenalty += $penalty;
                    endwhile;

                    // Update the database with penalty and total
                    if ($totalPenalty > 0) {
                        $update_penalty_query = "UPDATE orders SET penalty_amount = ? WHERE id = ?";
                        $stmt = $conn->prepare($update_penalty_query);
                        $stmt->bind_param("di", $totalPenalty, $order['order_id']);
                        $stmt->execute();

                        // Update total price including penalty
                        $final_total = $total + $totalPenalty;
                        $update_total_query = "UPDATE orders SET total_price = ? WHERE id = ?";
                        $stmt = $conn->prepare($update_total_query);
                        $stmt->bind_param("di", $final_total, $order['order_id']);
                        $stmt->execute();
                    }

                    // Reset the result pointer to display details
                    $order_details_result->data_seek(0);
                    
                    // Display the order details
                    while ($detail = $order_details_result->fetch_assoc()): 
                        $subtotal = $detail['quantity'] * $detail['price'];
                        $rentalDuration = calculateRentalDuration($detail['start_date'], $detail['end_date']);
                        $penalty = calculatePenalty(
                            $subtotal, 
                            $rentalDuration, 
                            $detail['rent_days'],
                            $detail['penalty_amount'] ?? 0.05
                        );
                ?>
                <div class="product-card">
                    <div class="product-header">
                        <h4 class="mb-0"><?php echo htmlspecialchars($detail['product_name']); ?></h4>
                    </div>
                    <div class="product-body">
                        <div class="row">
                            <!-- Product Image Column -->
                            <div class="col-md-4 mb-3 mb-md-0">
                                <img src="uploaded_img/<?php echo htmlspecialchars($detail['product_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($detail['product_name']); ?>" 
                                     class="product-image img-fluid">
                            </div>
                            
                            <!-- Product Details Column -->
                            <div class="col-md-8">
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <p><strong>Quantity:</strong> <?php echo htmlspecialchars($detail['quantity']); ?></p>
                                    </div>
                                    <div class="col-6">
                                        <p><strong>Price:</strong> ₱<?php echo number_format($detail['price'], 2); ?></p>
                                    </div>
                                </div>
                                <p><strong>Subtotal:</strong> ₱<?php echo number_format($subtotal, 2); ?></p>
                                
                                <div class="rental-info">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Maximum Rent Days:</strong> <?php echo $detail['rent_days']; ?> days</p>
                                            <p><strong>From:</strong> <?php echo date('F j, Y', strtotime($detail['start_date'])); ?></p>
                                            <p><strong>To:</strong> <?php echo date('F j, Y', strtotime($detail['end_date'])); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Rental Duration:</strong> <?php echo $rentalDuration; ?> days</p>
                                            
                                            <?php if ($rentalDuration > $detail['rent_days']): ?>
                                            <div class="penalty-details">
                                                <p class="overdue">Overdue by: <?php echo ($rentalDuration - $detail['rent_days']); ?> days</p>
                                                <p><strong>Penalty Rate:</strong> <?php echo ($detail['penalty_amount'] * 100); ?>% per day</p>
                                                <p><strong>Penalty Amount:</strong> ₱<?php echo number_format($penalty, 2); ?></p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                } else {
                    echo '<div class="alert alert-warning">No order details found.</div>';
                }
                ?>

                <!-- Order Totals -->
                <?php if ($total > 0): ?>
                <div class="order-total">
                    <div class="row">
                        <div class="col-md-8 offset-md-2">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td><h5>Subtotal:</h5></td>
                                    <td class="text-end"><h5>₱<?php echo number_format($total, 2); ?></h5></td>
                                </tr>
                                <?php if ($totalPenalty > 0): ?>
                                <tr>
                                    <td><h5>Total Penalty:</h5></td>
                                    <td class="text-end"><h5>₱<?php echo number_format($totalPenalty, 2); ?></h5></td>
                                </tr>
                                <tr>
                                    <td><h5 class="fw-bold">Final Total:</h5></td>
                                    <td class="text-end"><h5 class="fw-bold">₱<?php echo number_format($total + $totalPenalty, 2); ?></h5></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="order-actions">
                <a href="CustomerDashboard.php" class="btn btn-custom">Back to Dashboard</a>
                <a href="download_pdf.php?order_reference=<?php echo urlencode($order['reference_number']); ?>" class="btn btn-custom">Download PDF</a>
            </div>
            <?php else: ?>
            <div class="alert alert-info text-center">
                <p class="mb-3">No order found or you don't have permission to view this order.</p>
                <a href="CustomerDashboard.php" class="btn btn-custom">Back to Dashboard</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>