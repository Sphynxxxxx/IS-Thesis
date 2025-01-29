<?php

@include 'config.php';

// Approve customer
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE customer SET status = 'approved' WHERE customer_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header('Location: AdminCustomerReg.php');
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $stmt->close();
}

// Decline customer
if (isset($_GET['decline'])) {
    $id = intval($_GET['decline']);
    $stmt = $conn->prepare("UPDATE customer SET status = 'declined' WHERE customer_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header('Location: AdminCustomerReg.php');
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $stmt->close();
}

// Delete customer
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    
    $select_image = $conn->prepare("SELECT images FROM customer WHERE customer_id = ?");
    $select_image->bind_param("i", $id);
    $select_image->execute();
    $result = $select_image->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_path = 'Cus_uploads/' . $row['images']; 
        if (file_exists($image_path)) {
            unlink($image_path); 
        }
    }

    // Delete the record from the database
    $delete_stmt = $conn->prepare("DELETE FROM customer WHERE customer_id = ?");
    $delete_stmt->bind_param("i", $id);
    if ($delete_stmt->execute()) {
        header('Location: AdminCustomerReg.php');
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
    $delete_stmt->close();
}

// Fetch pending customer
$pending_result = $conn->query("SELECT * FROM customer WHERE status = 'pending'");

// Fetch approved customer
$approved_result = $conn->query("SELECT * FROM customer WHERE status = 'approved'");

// Fetch declined customer
$declined_result = $conn->query("SELECT * FROM customer WHERE status = 'declined'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Approval</title>
    <link rel="stylesheet" href="css\Adminstyles.css?v=1.0">
</head>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 90%;
        max-width: 1200px;
        margin: 30px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }

    .back-button-container {
        margin: 10px 0;
    }

    .back-button {
        text-decoration: none;
        color: #000000;
        padding: 10px 15px;
        border-radius: 5px;
        font-size: 30px;
        transition: background-color 0.3s;
    }

    .back-button i {
        margin-right: 5px;
    }

    .back-button:hover {
        color: #0056b3;
    }

    .table {
        margin-bottom: 30px;
    }

    .table h2 {
        background-color: #2F5233
        color: #fff;
        padding: 10px;
        border-radius: 8px;
        font-size: 18px;
    }

    .table ul {
        list-style: none;
        padding: 0;
    }

    .table ul li {
        background-color: #f9f9f9;
        padding: 15px;
        margin: 10px 0;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
    }

    .table ul li:hover {
        background-color: #f1f1f1;
    }

    .table ul li img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 20px;
    }

    .table ul li .info {
        flex: 1;
        margin-right: 20px;
    }

    .table ul li a {
        color: #007bff;
        text-decoration: none;
        font-weight: bold;
        margin: 0 5px;
    }

    .table ul li a:hover {
        text-decoration: underline;
    }

    /* Responsive styling */
    @media (max-width: 768px) {
        .container {
            width: 95%;
        }

        .table ul li {
            flex-direction: column;
            align-items: flex-start;
        }

        .table ul li img {
            margin-bottom: 10px;
        }
    }

</style>
<body>

    <div class="sidebar">
        <a href="AdminCustomerReg.php">Service Admin Approval</a>
        <a href="../Admin.php">Back to Dashboard</a>
    </div>
    
    <h2>Service Admin Approval</h2>
    <div class="container">

        
        <div class="table pending">
            <h2>Pending Registrations</h2>
            <?php if ($pending_result && $pending_result->num_rows > 0): ?>
                <ul>
                    <?php while ($row = $pending_result->fetch_assoc()): ?>
                        <li>
                            <div class="info">
                                <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                                <span>Contact: <?php echo htmlspecialchars($row['contact_number']); ?></span><br>
                                <span>Address: <?php echo htmlspecialchars($row['address']); ?></span><br>
                                <span>Email: <?php echo htmlspecialchars($row['email']); ?></span>
                            </div>
                            <img src="<?php echo htmlspecialchars($row['images']); ?>" alt="User Image">
                            <div class="actions">
                                <a href="?approve=<?php echo $row['customer_id']; ?>" class="approve">Approve</a>
                                <a href="?decline=<?php echo $row['customer_id']; ?>" class="decline">Decline</a>
                                <a href="?delete=<?php echo $row['customer_id']; ?>" onclick="return confirm('Are you sure?');" class="delete">Delete</a>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No pending registrations.</p>
            <?php endif; ?>
        </div>

        <!-- Approved customers -->
        <div class="table approved">
            <h2>Verified Registrations</h2>
            <?php if ($approved_result && $approved_result->num_rows > 0): ?>
                <ul>
                    <?php while ($row = $approved_result->fetch_assoc()): ?>
                        <li>
                            <div class="info">
                                <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                                <span>Contact: <?php echo htmlspecialchars($row['contact_number']); ?></span><br>
                                <span>Address: <?php echo htmlspecialchars($row['address']); ?></span><br>
                                <span>Email: <?php echo htmlspecialchars($row['email']); ?></span>
                            </div>
                            <img src="<?php echo htmlspecialchars($row['images']); ?>" alt="User Image">
                            <div class="actions">
                                <a href="?delete=<?php echo $row['customer_id']; ?>" onclick="return confirm('Are you sure?');" class="delete">Delete</a>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No approved registrations.</p>
            <?php endif; ?>
        </div>

        <!-- Declined customers -->
        <div class="table declined">
            <h2>Declined Registrations</h2>
            <?php if ($declined_result && $declined_result->num_rows > 0): ?>
                <ul>
                    <?php while ($row = $declined_result->fetch_assoc()): ?>
                        <li>
                            <div class="info">
                                <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                                <span>Contact: <?php echo htmlspecialchars($row['contact_number']); ?></span><br>
                                <span>Address: <?php echo htmlspecialchars($row['address']); ?></span><br>
                                <span>Email: <?php echo htmlspecialchars($row['email']); ?></span>
                            </div>
                            <img src="<?php echo htmlspecialchars($row['images']); ?>" alt="User Image">
                            <div class="actions">
                                <a href="?delete=<?php echo $row['customer_id']; ?>" onclick="return confirm('Are you sure?');" class="delete">Delete</a>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No declined registrations.</p>
            <?php endif; ?>
        </div>

    </div>

    <?php $conn->close(); ?>

    
</body>
</html>
