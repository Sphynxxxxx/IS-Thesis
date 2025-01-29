<?php

@include 'index/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    
</head>
<body>


    <!-- Main Contentqwq -->
    <div class="main-content">
        <h1>Admin Side of Farming Service Provider</h1>
        <p>Here you can manage users, booking and track orders.</p>

        
        <div class="quick-stats">
            <div class="card">
                <i class="fa-regular fa-circle-check"></i>
                <h3>Manage Users</h3>
                <p>Approve or decline pending applications.</p>
                <a href="index/AdminCustomerReg.php" class="btn">Manage Approvals</a>
            </div>
            <div class="card">
                <i class="fa-solid fa-bag-shopping"></i>
                <h3>Manage Bookings</h3>
                <p>Tracking Orders</p>
                <a href="index/TrackOrder.php" class="btn">View Orders</a>
            </div>
            <div class="card">
                <i class="fa-solid fa-money-bill"></i>
                <h3>Manage Payment</h3>
                <p>Track Payment Status</p>
                <a href="index/ManagePayments.php" class="btn">View Payment</a>
            </div>
            <div class="card">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <h3>Manage Tools And Equipment</h3>
                <p>Adding Tools and Equipment</p>
                <a href="index/AddProduct.php" class="btn">Add Product</a>
            </div>
            <div class="card">
                <i class="fa-solid fa-toolbox"></i>
                <h3>Check Tools And Equipment</h3>
                <p>View Tools and Equipment</p>
                <a href="index/ViewProduct.php" class="btn">View Product</a>
            </div>
            
        </div>

        
        
    </div>

    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center; 
            align-items: center; 
            min-height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }

        .main-content {
            text-align: center; 
            padding: 30px;
            max-width: 1000px;
            width: 100%;
        }

        .main-content h1 {
            font-size: 2.5em;
            color: #2F5233;
            margin-bottom: 20px;
        }

        .main-content p {
            font-size: 1.1em;
            color: #666;
            margin-bottom: 30px;
        }

        /* Quick Stats Cards */
        .quick-stats {
            display: flex;
            flex-direction: row; 
            justify-content: center;
            gap: 50px; 
            flex-wrap: nowrap;
            width: 100%;
            padding: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 8px;
            padding: 50px;
            width: calc(25% - 20px); 
            min-width: 200px; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            flex: 1; 
        }


        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            font-size: 1.6em;
            color: #2F5233;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 1em;
            color: #777;
            margin-bottom: 20px;
        }

        .card .btn {
            background-color: #2F5233;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }

        .card .btn:hover {
            background-color: #3b6c4a;
        }

        .fa-regular.fa-circle-check,
        .fa-solid.fa-bag-shopping,
        .fa-solid.fa-screwdriver-wrench,
        .fa-solid.fa-toolbox, 
        .fa-solid.fa-money-bill{
            font-size: 6em;
            color: #2F5233;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .quick-stats {
                flex-wrap: wrap;
            }

            .card {
                width: calc(50% - 20px); 
            }
        }

        @media (max-width: 768px) {
            .quick-stats {
                flex-direction: column;
                align-items: center;
            }

            .card {
                width: 80%;
                margin-bottom: 20px;
            }
        }



    </style>
    

</body>
</html>
