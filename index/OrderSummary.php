<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: CustomerDashboard.php"); 
    exit();
}

// Check if order details are passed (via POST or SESSION)
if (!isset($_SESSION['order_details']) || empty($_SESSION['order_details'])) {
    header("Location: CustomerDashboard.php"); 
    exit();
}

$orderDetails = $_SESSION['order_details'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Summary</title>
  <link rel="stylesheet" href="css/Customercss.css?v=1.0">
</head>
<body>
  <div class="container">
    <div class="main-content">
      <header>
        <h1>Order Summary</h1>
      </header>

      <div class="summary-container">
        <table>
          <thead>
            <tr>
              <th>Product Name</th>
              <th>Price</th>
              <th>Quantity</th>
              <th>Shipping Fee</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $subtotal = 0;
            $totalShipping = 0;

            foreach ($orderDetails as $item) {
                $productName = htmlspecialchars($item['name']);
                $price = number_format($item['price'], 2);
                $quantity = $item['quantity'];
                $shippingFee = number_format($item['shippingfee'], 2);
                $total = $item['price'] * $quantity + $item['shippingfee'];

                $subtotal += $item['price'] * $quantity;
                $totalShipping += $item['shippingfee'];

                echo "
                <tr>
                  <td>{$productName}</td>
                  <td>₱{$price}</td>
                  <td>{$quantity}</td>
                  <td>₱{$shippingFee}</td>
                  <td>₱" . number_format($total, 2) . "</td>
                </tr>";
            }
            ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3"></td>
              <td><strong>Subtotal</strong></td>
              <td>₱<?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <tr>
              <td colspan="3"></td>
              <td><strong>Shipping Fee</strong></td>
              <td>₱<?php echo number_format($totalShipping, 2); ?></td>
            </tr>
            <tr>
              <td colspan="3"></td>
              <td><strong>Grand Total</strong></td>
              <td>₱<?php echo number_format($subtotal + $totalShipping, 2); ?></td>
            </tr>
          </tfoot>
        </table>

        <div class="actions">
          <a href="CustomerDashboard.php" class="btn">Back to Dashboard</a>
          <a href="completeOrder.php" class="btn btn-primary">Complete Order</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
