<?php
session_start();
@include 'config.php';

// Add a product
if (isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $town = mysqli_real_escape_string($conn, $_POST['town']); 
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $quantity = intval($_POST['quantity']);
    $rent_days = intval($_POST['rent_days']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $categories = mysqli_real_escape_string($conn, $_POST['categories']);
    $product_image = $_FILES['product_image']['name'];
    $product_image_tmp_name = $_FILES['product_image']['tmp_name'];
    $product_image_folder = 'uploaded_img/' . basename($product_image);
    $status = 'approved';

    // Check if all fields are filled
    if (empty($product_name) || empty($town) || empty($location) || empty($description) || empty($quantity) || empty($product_price) || empty($categories) || empty($product_image)) {
        $message[] = 'Please fill out all fields.';
    } elseif ($quantity <= 0 || $rent_days <= 0 || $product_price <= 0) {
        $message[] = 'Invalid input for numerical fields. Please enter valid values.';
    } else {
        // Check for valid file types
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $file_type = mime_content_type($product_image_tmp_name);
        if (!in_array($file_type, $allowed_types)) {
            $message[] = 'Invalid image file type. Please upload a PNG, JPEG, or JPG image.';
        } elseif (!is_uploaded_file($product_image_tmp_name)) {
            $message[] = 'File upload failed. Please try again.';
        } else {
            // Prepare SQL query
            $insert = $conn->prepare("INSERT INTO products (product_name, town, location, description, quantity, rent_days, price, categories, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param("ssssiddsss", $product_name, $town, $location, $description, $quantity, $rent_days, $product_price, $categories, $product_image, $status);
            
            if ($insert->execute()) {
                // Move the uploaded file to the folder
                if (!file_exists('uploaded_img')) {
                    mkdir('uploaded_img', 0755, true);
                }
                if (move_uploaded_file($product_image_tmp_name, $product_image_folder)) {
                    $message[] = 'New product added successfully.';
                } else {
                    $message[] = 'Failed to upload image.';
                }
                header('Location: AddProduct.php');
                exit();
            } else {
                $message[] = 'Could not add the product: ' . $insert->error;
            }
            $insert->close();
        }
    }
}

// Delete product functionality
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $select_image = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $select_image->bind_param("i", $id);
    $select_image->execute();
    $result = $select_image->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // Delete the image file if it exists
        if (file_exists('uploaded_img/' . $row['image'])) {
            unlink('uploaded_img/' . $row['image']);
        }

        // Delete the product from the database
        $delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $delete_stmt->bind_param("i", $id);
        $delete_stmt->execute();
        $delete_stmt->close();
    }

    header('Location: AddProduct.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="css\lender.css">
</head>
<body>

<?php
if (isset($message)) {
    foreach ($message as $msg) {
        echo '<span class="message">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</span>';
    }
}
?>

<div class="back-button-container">
    <a href="../admin.php" class="back-button"><i class="fa-solid fa-house"></i></a>
</div>


<div class="sidebar">
    <ul>
        <li><button onclick="window.location.reload();" class="refresh-btn">Refresh</button></li>
        <li><a href="Profile.php">Profile</a></li>
        <li><a href="order_notification.php">Orders</a></li>
        <li><a href="Logout.php">Logout</a></li>
    </ul>
</div>

<!-- Product Form -->
<div class="container">
        <form action="AddProduct.php" method="post" enctype="multipart/form-data">
            <h3>Add a New Product</h3>
            <input type="text" placeholder="Enter Product Name" name="product_name" class="box" required>
            
            <select id="town-select" name="town" class="box" required>
                <option value="" disabled selected>Select Town</option>
                <option value="Pototan">Pototan</option>
                <option value="Zarraga">Zarraga</option>
            </select>
            
            <select id="barangay-select" name="location" class="box" required disabled>
                <option value="" disabled selected>Select Barangay</option>
            </select>
            
            <input type="text" placeholder="Description" name="description" class="box" required>
            <input type="number" placeholder="Rent Days" name="rent_days" class="box" required>
            <input type="number" placeholder="Quantity" name="quantity" class="box" required>
            <select id="categories" name="categories" class="box" required>
                <option value="" disabled selected>Categories</option>
                <option value="Hand Tools">Hand Tools</option>
                <option value="Ploughs">Ploughs</option>
                <option value="Seeding Tools">Seeding Tools</option>
                <option value="Harvesting Tools">Harvesting Tools</option>
                <option value="Tilling Tools">Tilling Tools</option>
                <option value="Cutting Tools">Cutting Tools</option>
                <option value="Garden Tools">Garden Tools</option>
            </select>
            <input type="number" placeholder="Enter Rent Price" name="product_price" class="box" required>
            <input type="file" accept="image/png, image/jpeg, image/jpg" name="product_image" class="box" required>
            <input type="submit" class="btn" name="add_product" value="Add Product">
        </form>
    </div>
</div>

<script>
    const townSelect = document.getElementById('town-select');
    const barangaySelect = document.getElementById('barangay-select');

    const barangays = {
        'Pototan': [
            'Abangay', 'Amamaros', 'Bagacay', 'Barasan', 'Batuan', 'Bongco', 
            'Cahaguichican', 'Callan', 'Cansilayan', 'Casalsagan', 'Cato-ogan', 
            'Cau-ayan', 'Culob', 'Danao', 'Dapitan', 'Dawis', 'Dongsol', 
            'Fernando Parcon Ward', 'Guibuangan', 'Guinacas', 'Igang', 'Intaluan', 
            'Iwa Ilaud', 'Iwa Ilaya', 'Jamabalud', 'Jebioc', 'Lay-ahan', 
            'Lopez Jaena Ward', 'Lumbo', 'Macatol', 'Malusgod', 'Nabitasan', 
            'Naga', 'Nanga', 'Naslo', 'Pajo', 'Palanguia', 'Pitogo', 
            'Primitivo Ledesma Ward', 'Purog', 'Rumbang', 'San Jose Ward', 
            'Sinuagan', 'Tuburan', 'Tumcon Ilaud', 'Tumcon Ilaya', 'Ubang', 
            'Zarrague'
        ],
        'Zarraga': [
            'Balud Lilo-an', 'Balud I', 'Balud II', 'Dawis Centro', 'Dawis Norte', 'Dawis Sur',
            'Gines', 'Inagdangan Centro', 'Inagdangan Norte', 'Inagdangan Sur', 'Jalaud Norte', 'Jalaud Sur',
            'Libongcogon', 'Malunang', 'Pajo', 'Ilawod Poblacion' , 'Ilaya Poblacion' , 'Sambag', 'Sigangao',
            'Talauguis', 'Talibong', 'Tubigan', 'Tuburan'

        ]
    };

    townSelect.addEventListener('change', function() {
        barangaySelect.innerHTML = '<option value="" disabled selected>Select Barangay</option>';
        
        const selectedTown = this.value;
        
        if (selectedTown) {
            barangaySelect.disabled = false;
            
            barangays[selectedTown].forEach(barangay => {
                const option = document.createElement('option');
                option.value = barangay;
                option.textContent = barangay;
                barangaySelect.appendChild(option);
            });
        } else {
            barangaySelect.disabled = true;
        }
    });

    // Get the sidebar and the toggle button
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');

    // Add event listener to toggle sidebar visibility
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar if clicked outside of it
    document.addEventListener('click', (event) => {
        if (sidebar && sidebarToggle && !sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    });
</script>
</body>
</html>
