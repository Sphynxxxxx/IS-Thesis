<?php
@include 'config.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Adjust the path if necessary

// Function to send email
function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'larrydenverbiaco@gmail.com'; // Replace with your email
        $mail->Password   = 'brto wnuc kgvk xzva'; // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('larrydenverbiaco@gmail.com', 'Admin'); // Sender email and name
        $mail->addAddress($to); // Recipient email

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
        return false; // Email failed to send
    }
}

// Check database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Process search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = " AND (firstname LIKE ? OR lastname LIKE ?)";
    $search_param = "%$search%";
}

// Initialize result variables
$pending_result = null;
$approved_result = null;
$declined_result = null;

// Delete customer
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // First, get the user's details for logging
    $fetch_stmt = $conn->prepare("SELECT firstname, lastname, email, status FROM customer WHERE customer_id = ?");
    $fetch_stmt->bind_param("i", $id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Prepare and execute delete statement
        $delete_stmt = $conn->prepare("DELETE FROM customer WHERE customer_id = ?");
        $delete_stmt->bind_param("i", $id);
        
        if ($delete_stmt->execute()) {
            // Log deletion
            error_log("User deleted: {$user['firstname']} {$user['lastname']} (Email: {$user['email']}, Previous Status: {$user['status']})");
            
            // Redirect back to the page with optional search parameter
            header('Location: AdminCustomerReg.php' . (!empty($search) ? "?search=" . urlencode($search) : ""));
            exit();
        } else {
            // Handle delete error
            echo "Error deleting record: " . $conn->error;
        }
        
        $delete_stmt->close();
    } else {
        echo "User not found.";
    }
    
    $fetch_stmt->close();
}

// Modified queries to include search
if (!empty($search)) {
    // Pending customers with search
    $pending_stmt = $conn->prepare("SELECT * FROM customer WHERE status = 'pending'" . $search_condition);
    $pending_stmt->bind_param("ss", $search_param, $search_param);
    $pending_stmt->execute();
    $pending_result = $pending_stmt->get_result();

    // Approved customers with search
    $approved_stmt = $conn->prepare("SELECT * FROM customer WHERE status = 'approved'" . $search_condition);
    $approved_stmt->bind_param("ss", $search_param, $search_param);
    $approved_stmt->execute();
    $approved_result = $approved_stmt->get_result();

    // Declined customers with search
    $declined_stmt = $conn->prepare("SELECT * FROM customer WHERE status = 'declined'" . $search_condition);
    $declined_stmt->bind_param("ss", $search_param, $search_param);
    $declined_stmt->execute();
    $declined_result = $declined_stmt->get_result();
} else {
    // Original queries without search
    $pending_result = $conn->query("SELECT * FROM customer WHERE status = 'pending'");
    $approved_result = $conn->query("SELECT * FROM customer WHERE status = 'approved'");
    $declined_result = $conn->query("SELECT * FROM customer WHERE status = 'declined'");
}

// Approve customer
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE customer SET status = 'approved' WHERE customer_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        // Fetch user details to get email
        $fetch_stmt = $conn->prepare("SELECT email, firstname FROM customer WHERE customer_id = ?");
        $fetch_stmt->bind_param("i", $id);
        $fetch_stmt->execute();
        $result = $fetch_stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $email = $user['email'];
            $firstname = $user['firstname'];

            // Send email notification
            $subject = "Your Registration Has Been Approved";
            $body = "Dear $firstname,<br><br>
                     We are pleased to inform you that your registration has been approved.<br><br>
                     Thank you,<br>
                     Admin Team";
            sendEmail($email, $subject, $body);
        }

        header('Location: AdminCustomerReg.php' . (!empty($search) ? "?search=" . urlencode($search) : ""));
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
        // Fetch user details to get email
        $fetch_stmt = $conn->prepare("SELECT email, firstname FROM customer WHERE customer_id = ?");
        $fetch_stmt->bind_param("i", $id);
        $fetch_stmt->execute();
        $result = $fetch_stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $email = $user['email'];
            $firstname = $user['firstname'];

            // Send email notification
            $subject = "Your Registration Has Been Declined";
            $body = "Dear $firstname,<br><br>
                     We regret to inform you that your registration has been declined.<br><br>
                     Thank you,<br>
                     Admin Team";
            sendEmail($email, $subject, $body);
        }

        header('Location: AdminCustomerReg.php' . (!empty($search) ? "?search=" . urlencode($search) : ""));
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Approval</title>
    <link rel="stylesheet" href="css\Adminstyles.css?v=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 25px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Search Container */
        .search-container {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .search-container form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }

        .search-input {
            padding: 12px 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 350px;
            font-size: 15px;
        }

        .search-button, .clear-search {
            padding: 12px 25px;
            background-color: #2F5233;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
        }

        .clear-search {
            background-color: #6c757d;
            text-decoration: none;
        }

        /* Accordion Styles */
        .accordion-section {
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .accordion-header {
            background-color: #2F5233;
            padding: 0px 20px;
            color: white;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .accordion-content {
            display: none;
            padding: 20px;
            background-color: white;
        }

        .accordion-content.active {
            display: block;
        }

        .counter-badge {
            background-color: white;
            color: #2F5233;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .data-table tr:hover {
            background-color: #f8f9fa;
        }

        .data-table img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .action-buttons a {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 13px;
        }

        .approve { background-color: #2F5233; }
        .decline { background-color: #dc3545; }
        .delete { background-color: #6c757d; }

        /* Responsive */
        @media (max-width: 768px) {
            .container { width: 95%; padding: 15px; }
            .search-input { width: 100%; }
            .search-container form { flex-direction: column; }
            .data-table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <!--<a href="AdminCustomerReg.php">Service Admin Approval</a>-->
        <a href="../Admin.php">Back to Dashboard</a>
    </div>
    
    <h2>Manage Users</h2>
    <div class="container">
        <!-- Search Box -->
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="Search by name..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-button">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="AdminCustomerReg.php" class="clear-search">Clear Search</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Pending Registrations -->
        <div class="accordion-section">
            <div class="accordion-header" onclick="toggleAccordion(this)">
                <h2>Pending Registrations</h2>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="counter-badge"><?php echo $pending_result->num_rows; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <div class="accordion-content">
                <?php if ($pending_result && $pending_result->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $pending_result->fetch_assoc()): ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($row['images']); ?>" alt="User"></td>
                                    <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?approve=<?php echo $row['customer_id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="approve">Approve</a>
                                            <a href="?decline=<?php echo $row['customer_id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="decline">Decline</a>
                                            <a href="?delete=<?php echo $row['customer_id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" onclick="return confirm('Are you sure?');" class="delete">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No pending registrations<?php echo !empty($search) ? ' matching your search' : ''; ?>.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Verified Registrations -->
        <div class="accordion-section">
            <div class="accordion-header" onclick="toggleAccordion(this)">
                <h2>Verified Registrations</h2>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="counter-badge"><?php echo $approved_result->num_rows; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <div class="accordion-content">
                <?php if ($approved_result && $approved_result->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $approved_result->fetch_assoc()): ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($row['images']); ?>" alt="User"></td>
                                    <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?delete=<?php echo $row['customer_id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" onclick="return confirm('Are you sure?');" class="delete">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No approved registrations<?php echo !empty($search) ? ' matching your search' : ''; ?>.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Declined Registrations -->
        <div class="accordion-section">
            <div class="accordion-header" onclick="toggleAccordion(this)">
                <h2>Declined Registrations</h2>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="counter-badge"><?php echo $declined_result->num_rows; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <div class="accordion-content">
                <?php if ($declined_result && $declined_result->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $declined_result->fetch_assoc()): ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($row['images']); ?>" alt="User"></td>
                                    <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?delete=<?php echo $row['customer_id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" onclick="return confirm('Are you sure?');" class="delete">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No declined registrations<?php echo !empty($search) ? ' matching your search' : ''; ?>.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function toggleAccordion(header) {
        const content = header.nextElementSibling;
        const icon = header.querySelector('.fas');
        
        // Close all other accordions
        document.querySelectorAll('.accordion-content').forEach(item => {
            if (item !== content) {
                item.classList.remove('active');
                item.previousElementSibling.querySelector('.fas').classList.remove('fa-chevron-up');
                item.previousElementSibling.querySelector('.fas').classList.add('fa-chevron-down');
            }
        });
        
        // Toggle current accordion
        content.classList.toggle('active');
        if (content.classList.contains('active')) {
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        } else {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }

    // Open first accordion by default
    document.addEventListener('DOMContentLoaded', function() {
        const firstAccordion = document.querySelector('.accordion-header');
        if (firstAccordion) {
            toggleAccordion(firstAccordion);
        }
    });
    </script>
</body>
</html>
