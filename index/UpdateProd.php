<?php
@include 'config.php';

$id = isset($_GET['edit']) ? intval($_GET['edit']) : null;

if (!$id) {
    header('Location: AddProduct.php');
    exit();
}

if (isset($_POST['update_product'])) {
    $product_name = $_POST['product_name'];
    $town = $_POST['town']; // Get town value
    $location = $_POST['location'];
    $description = $_POST['description'];
    $quantity = isset($_POST['product_quantity']) ? max(0, intval($_POST['product_quantity'])) : 0;
    $rent_days = isset($_POST['rent_days']) ? intval($_POST['rent_days']) : 0;
    $categories = $_POST['categories'];
    $product_price = $_POST['product_price'];
    $product_image = isset($_FILES['product_image']['name']) ? $_FILES['product_image']['name'] : '';
    $product_image_tmp_name = isset($_FILES['product_image']['tmp_name']) ? $_FILES['product_image']['tmp_name'] : '';
    $product_image_folder = 'uploaded_img/' . $product_image;

    // Validate inputs
    if (empty($product_name) || empty($town) || empty($location) || empty($description) || $quantity < 0 || empty($product_price) || $rent_days < 0 || empty($categories)) {
        $message[] = 'Please fill out all required fields!';
    } else {
        if (!empty($product_image)) {
            $allowed_types = ['image/png', 'image/jpeg', 'image/jpg'];
            $file_type = mime_content_type($product_image_tmp_name);

            if (!in_array($file_type, $allowed_types)) {
                $message[] = 'Invalid image format. Please upload a PNG or JPEG image.';
            } else {
                // Update with a new image, automatically set status to 'approved'
                $update_data = "UPDATE products SET product_name='$product_name', town='$town', location='$location', description='$description', quantity='$quantity', rent_days='$rent_days', categories='$categories', price='$product_price', image='$product_image', status='approved' WHERE id='$id'";
                if (mysqli_query($conn, $update_data)) {
                    move_uploaded_file($product_image_tmp_name, $product_image_folder);
                    header('Location: ViewProduct.php');
                    exit();
                } else {
                    $message[] = 'Failed to update product. Please try again.';
                }
            }
        } else {
            // Update without a new image, automatically set status to 'approved'
            $update_data = "UPDATE products SET product_name='$product_name', town='$town', location='$location', description='$description', quantity='$quantity', rent_days='$rent_days', categories='$categories', price='$product_price', status='approved' WHERE id='$id'";
            if (mysqli_query($conn, $update_data)) {
                header('Location: ViewProduct.php');
                exit();
            } else {
                $message[] = 'Failed to update product. Please try again.';
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="css\lender.css">
   <style>
        /* Styling for town and barangay selects to match other form elements */
        #town-select,
        #barangay-select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            font-size: 16px;
            color: #333;
        }

        /* Style for the disabled state */
        #barangay-select:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        /* Match hover effects with other inputs */
        #town-select:focus,
        #barangay-select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }
   </style>
</head>
<body>

<?php
if (isset($message)) {
    foreach ($message as $msg) {
        echo '<span class="message">' . htmlspecialchars($msg) . '</span>';
    }
}
?>

<div class="container">
      <?php
      $select = mysqli_query($conn, "SELECT * FROM products WHERE id = '$id'");
      if ($row = mysqli_fetch_assoc($select)) {
          $current_town = isset($row['town']) ? $row['town'] : '';
          $current_location = $row['location'];
      ?>
      <form action="" method="post" enctype="multipart/form-data">
         <h3 class="title">Update the Product</h3>
         <input type="text" class="box" name="product_name" value="<?php echo htmlspecialchars($row['product_name']); ?>" placeholder="Enter the product name" required>
         
         <!-- Town selection -->
         <select id="town-select" name="town" class="box" required>
            <option value="" disabled>Select Town</option>
            <option value="Pototan" <?php echo ($current_town == 'Pototan') ? 'selected' : ''; ?>>Pototan</option>
            <option value="Zarraga" <?php echo ($current_town == 'Zarraga') ? 'selected' : ''; ?>>Zarraga</option>
         </select>
         
         <!-- Barangay selection -->
         <select id="barangay-select" name="location" class="box" required>
            <option value="" disabled>Select Barangay</option>
            <!-- Barangays will be populated via JavaScript -->
         </select>
         
         <textarea class="box" name="description" placeholder="Enter product description" required><?php echo htmlspecialchars($row['description']); ?></textarea>
         <input type="number" min="0" class="box" name="product_quantity" value="<?php echo max(0, $row['quantity']); ?>" placeholder="Enter the product quantity" required>
         <input type="number" min="0" class="box" name="rent_days" value="<?php echo htmlspecialchars($row['rent_days']); ?>" placeholder="Enter the rent days" required>
         
         <select id="categories" name="categories" class="box" required>
                <option value="" disabled>Categories</option>
                <option value="Hand Tools" <?php echo ($row['categories'] == 'Hand Tools') ? 'selected' : ''; ?>>Hand Tools</option>
                <option value="Ploughs" <?php echo ($row['categories'] == 'Ploughs') ? 'selected' : ''; ?>>Ploughs</option>
                <option value="Seeding Tools" <?php echo ($row['categories'] == 'Seeding Tools') ? 'selected' : ''; ?>>Seeding Tools</option>
                <option value="Harvesting Tools" <?php echo ($row['categories'] == 'Harvesting Tools') ? 'selected' : ''; ?>>Harvesting Tools</option>
                <option value="Tilling Tools" <?php echo ($row['categories'] == 'Tilling Tools') ? 'selected' : ''; ?>>Tilling Tools</option>
                <option value="Cutting Tools" <?php echo ($row['categories'] == 'Cutting Tools') ? 'selected' : ''; ?>>Cutting Tools</option>
                <option value="Garden Tools" <?php echo ($row['categories'] == 'Garden Tools') ? 'selected' : ''; ?>>Garden Tools</option>
         </select>
         
         <input type="number" min="0" class="box" name="product_price" value="<?php echo htmlspecialchars($row['price']); ?>" placeholder="Enter the product price" required>
         <input type="file" class="box" name="product_image" accept="image/png, image/jpeg, image/jpg">
         <input type="submit" value="Update Product" name="update_product" class="btn">
         <a href="ViewProduct.php" class="btn">Go Back!</a>
      </form>
      <?php } else { ?>
         <p>Product not found.</p>
      <?php } ?>
   </div>
</div>

<script>
    // Get the town and barangay select elements
    const townSelect = document.getElementById('town-select');
    const barangaySelect = document.getElementById('barangay-select');
    
    // Define the current location
    const currentLocation = "<?php echo $current_location; ?>";

    // Define barangays for each town
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

    // Function to populate barangay dropdown
    function populateBarangays(town) {
        // Clear existing options
        barangaySelect.innerHTML = '<option value="" disabled>Select Barangay</option>';
        
        if (town) {
            // Enable barangay select
            barangaySelect.disabled = false;
            
            // Add barangays for selected town
            barangays[town].forEach(barangay => {
                const option = document.createElement('option');
                option.value = barangay;
                option.textContent = barangay;
                
                // Check if this matches the current barangay
                if (barangay === currentLocation) {
                    option.selected = true;
                }
                
                barangaySelect.appendChild(option);
            });
        } else {
            // Disable barangay select if no town is selected
            barangaySelect.disabled = true;
        }
    }

    // When town is selected, populate the barangay dropdown
    townSelect.addEventListener('change', function() {
        populateBarangays(this.value);
    });

    // Initialize barangay dropdown on page load
    window.addEventListener('DOMContentLoaded', function() {
        populateBarangays(townSelect.value);
    });
</script>

</body>
</html>