<?php
include 'config.php';

// Get current month/year if not specified
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Calculate start and end dates for the selected month
$startDate = date('Y-m-01', strtotime("$selectedYear-$selectedMonth-01"));
$endDate = date('Y-m-t', strtotime("$selectedYear-$selectedMonth-01"));

// SQL query to get sales data
$sql = "
    SELECT 
        o.id AS order_id,
        o.reference_number,
        o.order_date,
        o.total_price,
        o.status,
        CONCAT(c.firstname, ' ', c.lastname) AS customer_name,
        c.contact_number,
        CONCAT(c.town, ' - ', c.address) AS full_address,
        GROUP_CONCAT(p.product_name SEPARATOR ', ') AS products,
        GROUP_CONCAT(od.quantity SEPARATOR ', ') AS quantities,
        MIN(od.start_date) AS rental_start,
        MAX(od.end_date) AS rental_end
    FROM orders o
    JOIN customer c ON o.customer_id = c.customer_id
    JOIN order_details od ON o.id = od.order_id
    JOIN products p ON od.product_id = p.id
    WHERE o.order_date BETWEEN ? AND ?
    GROUP BY o.id
    ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

// Prepare data for chart
$dailySales = array();
$currentDate = new DateTime($startDate);
$endDateTime = new DateTime($endDate);

while ($currentDate <= $endDateTime) {
    $dailySales[$currentDate->format('Y-m-d')] = 0;
    $currentDate->modify('+1 day');
}

// Reset result pointer and calculate daily sales
$result->data_seek(0);
while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'received') {
        $orderDate = date('Y-m-d', strtotime($row['order_date']));
        if (isset($dailySales[$orderDate])) {
            $dailySales[$orderDate] += $row['total_price'];
        }
    }
}

$result->data_seek(0); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments & Sales</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .back-button {
            text-decoration: none;
            color: #2F5233;
            font-size: 24px;
        }

        h1 {
            color: #2F5233;
            margin: 0;
        }

        .filters {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filters select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
            cursor: pointer;
        }

        .month-label {
            font-weight: bold;
            color: #2F5233;
            margin-right: 5px;
        }

        .current-period {
            text-align: center;
            margin: 20px 0;
            color: #2F5233;
            font-size: 1.2em;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .summary-card h3 {
            margin: 0 0 10px 0;
            color: #2F5233;
            font-size: 1.1em;
        }

        .summary-card p {
            margin: 0;
            font-size: 1.5em;
            color: #333;
            font-weight: bold;
        }

        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            height: 400px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #2F5233;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
            display: inline-block;
        }

        .status-pending { 
            background-color: #fff3cd;
            color: #856404;
        }
        .status-ready { 
            background-color: #d4edda;
            color: #155724;
        }
        .status-received { 
            background-color: #cce5ff;
            color: #004085;
        }
        .status-canceled { 
            background-color: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .container {
                padding: 10px;
            }
            
            th, td {
                padding: 8px;
            }

            .chart-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="../Admin.php" class="back-button"><i class="fa-solid fa-house"></i></a>
            <h1>Sales & Payment Management</h1>
            <div></div>
        </div>

        <div class="filters">
            <form method="GET" action="" class="filter-form">
                <span class="month-label">Select Month:</span>
                <select name="month" id="month">
                    <?php
                    for ($i = 1; $i <= 12; $i++) {
                        $monthName = date('F', mktime(0, 0, 0, $i, 1));
                        $selected = $i == $selectedMonth ? 'selected' : '';
                        echo "<option value='$i' $selected>$monthName</option>";
                    }
                    ?>
                </select>

                <span class="month-label">Year:</span>
                <select name="year" id="year">
                    <?php
                    $currentYear = date('Y');
                    for ($year = $currentYear; $year >= $currentYear - 2; $year--) {
                        $selected = $year == $selectedYear ? 'selected' : '';
                        echo "<option value='$year' $selected>$year</option>";
                    }
                    ?>
                </select>
            </form>
        </div>

        <div class="current-period">
            Showing sales for <?php echo date('F Y', strtotime("$selectedYear-$selectedMonth-01")); ?>
        </div>

        <?php
        // Initialize summary variables
        $totalRevenue = 0;
        $totalOrders = 0;
        $activeRentals = 0;
        $statusCounts = [
            'pending' => 0,
            'ready_to_pick_up' => 0,
            'received' => 0,
            'canceled' => 0
        ];

        if ($result->num_rows > 0):
            // Create DateTime objects for the start and end of the selected month
            $monthStart = new DateTime($startDate);
            $monthEnd = new DateTime($endDate);

            while ($row = $result->fetch_assoc()) {
                if ($row['status'] === 'received') {
                    $totalRevenue += $row['total_price'];
                }
                $totalOrders++;
                
                // Calculate active rentals based on rental period
                $rentalStart = new DateTime($row['rental_start']);
                $rentalEnd = new DateTime($row['rental_end']);
                
                // Check if rental overlaps with the selected month
                if (
                    ($rentalStart <= $monthEnd && $rentalEnd >= $monthStart) && 
                    // Exclude canceled orders
                    $row['status'] !== 'canceled'
                ) {
                    $activeRentals++;
                }
                
                $statusCounts[$row['status']]++;
            }
            $result->data_seek(0);
        ?>

        <div class="summary-cards">
            <div class="summary-card">
                <h3>Revenue (Received Orders)</h3>
                <p>₱<?php echo number_format($totalRevenue, 2); ?></p>
            </div>
            <div class="summary-card">
                <h3>Total Orders</h3>
                <p><?php echo $totalOrders; ?></p>
            </div>
            <div class="summary-card">
                <h3>Active Rentals</h3>
                <p><?php echo $activeRentals; ?></p>
            </div>
            <div class="summary-card">
                <h3>Completion Rate</h3>
                <p><?php echo $totalOrders > 0 ? round(($statusCounts['received'] / $totalOrders) * 100, 1) : 0; ?>%</p>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order Date</th>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Products</th>
                        <th>Rental Period</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['reference_number']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['customer_name']); ?><br>
                            <small><?php echo htmlspecialchars($row['contact_number']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($row['products']); ?></td>
                        <td>
                            <?php echo date('M d', strtotime($row['rental_start'])); ?> - 
                            <?php echo date('M d, Y', strtotime($row['rental_end'])); ?>
                        </td>
                        <td>₱<?php echo number_format($row['total_price'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php 
                                echo $row['status'] == 'ready_to_pick_up' ? 'ready' : $row['status']; 
                            ?>">
                                <?php echo ucwords(str_replace('_', ' ', $row['status'])); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
            <p>No orders found for the selected month.</p>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const monthSelect = document.getElementById('month');
        const yearSelect = document.getElementById('year');
        
        monthSelect.addEventListener('change', function() {
            document.querySelector('.filter-form').submit();
        });
        
        yearSelect.addEventListener('change', function() {
            document.querySelector('.filter-form').submit();
        });

        // Chart initialization
        const salesData = <?php echo json_encode($dailySales); ?>;
        const dates = Object.keys(salesData);
        const sales = Object.values(salesData);

        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates.map(date => date.slice(-2)),
                datasets: [{
                    label: 'Daily Sales (₱)',
                    data: sales,
                    fill: false,
                    borderColor: '#2F5233',
                    tension: 0.4,
                    pointBackgroundColor: '#2F5233'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Daily Sales for ' + <?php echo json_encode(date('F Y', strtotime("$selectedYear-$selectedMonth-01"))); ?>,
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                        y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toFixed(2);
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Day of Month'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        backgroundColor: 'rgba(47, 82, 51, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        callbacks: {
                            label: function(context) {
                                return 'Sales: ₱' + context.parsed.y.toFixed(2);
                            },
                            title: function(context) {
                                return 'Date: ' + dates[context[0].dataIndex];
                            }
                        }
                    }
                }
            }
        });
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>