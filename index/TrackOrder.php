<?php
include 'config.php';

// SQL query to retrieve data from the database
$sql = "
    SELECT 
        o.id AS order_id,
        p.image AS product_image,
        p.product_name,
        CONCAT(c.firstname, ' ', c.lastname) AS customer_name,
        od.price,  
        CONCAT(c.town, ' - ', c.address) AS full_address,
        c.contact_number,
        o.order_date,
        o.delivery_method,
        o.reference_number,
        od.start_date,
        od.end_date,
        o.status AS order_status
    FROM 
        order_details od
    JOIN 
        orders o ON od.order_id = o.id
    JOIN 
        customer c ON o.customer_id = c.customer_id
    JOIN 
        products p ON od.product_id = p.id
    ORDER BY 
        o.order_date DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Error executing query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Track Orders</title>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css' rel='stylesheet'>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 95%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
        }
        .back-button {
            text-decoration: none;
            color: #2F5233;
            font-size: 24px;
            transition: color 0.3s;
            padding: 10px;
        }
        .back-button:hover {
            color: #3b6c4a;
        }
        h1 {
            color: #2F5233;
            margin: 0;
        }
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
        }
        .order-table th, .order-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }
        .order-table th {
            background-color: #2F5233;
            color: #fff;
            font-weight: bold;
            white-space: nowrap;
        }
        .order-table tr:hover {
            background-color: #f5f5f5;
        }
        .order-table .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .product-name {
            font-weight: bold;
            margin-top: 5px;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .ready-btn, .cancel-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            min-width: 120px;
        }
        .ready-btn {
            background-color: #28a745;
            color: white;
        }
        .cancel-btn {
            background-color: #dc3545;
            color: white;
        }
        .ready-btn:hover, .cancel-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .order-status {
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .rental-period {
            display: flex;
            flex-direction: column;
            gap: 5px;
            font-size: 0.9em;
        }
        .date-label {
            font-weight: bold;
            color: #666;
        }
        @media (max-width: 1200px) {
            .order-table {
                font-size: 14px;
            }
            .ready-btn, .cancel-btn {
                padding: 6px 12px;
                min-width: 100px;
            }
        }
        @media (max-width: 768px) {
            .container {
                width: 100%;
                padding: 10px;
            }
            .order-table {
                display: block;
                overflow-x: auto;
            }
            .product-img {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <div class='container'>
        <header>
            <a href='../Admin.php' class='back-button'><i class='fa-solid fa-house'></i></a>
            <h1>Track Orders</h1>
            <div></div>
        </header>

        <?php if ($result->num_rows > 0): ?>
            <table class='order-table'>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Price</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Order Date</th>
                        <th>Rental Period</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr id='order-<?php echo $row['order_id']; ?>'>
                            <td>
                                <div class="product-info">
                                    <img src='uploaded_img/<?php echo htmlspecialchars($row['product_image']); ?>' 
                                         alt='<?php echo htmlspecialchars($row['product_name']); ?>' 
                                         class='product-img' 
                                         onerror="this.src='uploaded_img/default_image.jpg';">
                                    <div class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td>₱<?php echo number_format($row['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['full_address']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                            <td>
                                <div class="rental-period">
                                    <span class="date-label">Start:</span>
                                    <?php echo date('M d, Y', strtotime($row['start_date'])); ?>
                                    <span class="date-label">End:</span>
                                    <?php echo date('M d, Y', strtotime($row['end_date'])); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['reference_number']); ?></td>
                            <td class='order-status' id='status-<?php echo $row['order_id']; ?>'>
                                <?php echo ucfirst($row['order_status']); ?>
                            </td>
                            <td>
                                <div class='action-buttons'>
                                    <button class='ready-btn' data-order-id='<?php echo $row['order_id']; ?>'>
                                        Claimable
                                    </button>
                                    <button class='cancel-btn' data-order-id='<?php echo $row['order_id']; ?>'>
                                        Cancel
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>

    <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
    <script>
    $(document).ready(function() {
        function fetchOrderStatuses() {
            $.ajax({
                url: 'fetch_order_status.php',
                type: 'GET',
                success: function(response) {
                    const statuses = JSON.parse(response);
                    statuses.forEach(function(order) {
                        const statusCell = $('#status-' + order.id);
                        let statusText;
                        let statusClass;

                        switch(order.status) {
                            case 'pending':
                                statusText = 'Pending';
                                statusClass = 'status-pending';
                                break;
                            case 'ready_to_pick_up':
                                statusText = 'Claimable';
                                statusClass = 'status-ready';
                                break;
                            case 'canceled':
                                statusText = 'Canceled';
                                statusClass = 'status-canceled';
                                break;
                            case 'received':
                                statusText = 'Received';
                                statusClass = 'status-received';
                                break;
                            default:
                                statusText = order.status;
                                statusClass = '';
                        }

                        statusCell.text(statusText);
                        statusCell.attr('class', 'order-status ' + statusClass);
                    });
                }
            });
        }

        $('.ready-btn').click(function() {
            const orderId = $(this).data('order-id');
            const statusCell = $('#status-' + orderId);
            const currentStatus = statusCell.text();
            
            if (currentStatus === 'Ready for Pickup') {
                if (confirm('Set this order back to Pending?')) {
                    updateOrderStatus(orderId, 'pending');
                }
            } else {
                if (confirm('Mark this order as Ready for Pickup?')) {
                    updateOrderStatus(orderId, 'ready_to_pick_up');
                }
            }
        });

        $('.cancel-btn').click(function() {
            const orderId = $(this).data('order-id');
            const statusCell = $('#status-' + orderId);
            const currentStatus = statusCell.text();
            
            if (currentStatus === 'Canceled') {
                if (confirm('Set this order back to Pending?')) {
                    updateOrderStatus(orderId, 'pending');
                }
            } else {
                if (confirm('Are you sure you want to cancel this order?')) {
                    updateOrderStatus(orderId, 'canceled');
                }
            }
        });

        function updateOrderStatus(orderId, status) {
            $.ajax({
                url: 'update_order_status.php',
                type: 'POST',
                data: { 
                    order_id: orderId, 
                    status: status 
                },
                success: function(response) {
                    if (response === 'success') {
                        fetchOrderStatuses();
                        let message = '';
                        switch(status) {
                            case 'pending':
                                message = 'Order has been set to Pending';
                                break;
                            case 'ready_to_pick_up':
                                message = 'Order is now Ready for Pickup';
                                break;
                            case 'canceled':
                                message = 'Order has been Canceled';
                                break;
                            default:
                                message = 'Order status updated successfully';
                        }
                        alert(message);
                    } else {
                        alert('Failed to update order status');
                    }
                },
                error: function() {
                    alert('An error occurred while updating the status');
                }
            });
        }

        fetchOrderStatuses();
        setInterval(fetchOrderStatuses, 30000);
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>