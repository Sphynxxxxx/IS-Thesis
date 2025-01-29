<?php
@include 'config.php';

$id = isset($_GET['edit']) ? intval($_GET['edit']) : null;

if (!$id) {
    header('Location: AddProduct.php');
    exit();
}

if (isset($_POST['update_product'])) {
    $product_name = $_POST['product_name'];
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
    if (empty($product_name) || empty($location) || empty($description) || $quantity < 0 || empty($product_price) || $rent_days < 0 || empty($categories)) {
        $message[] = 'Please fill out all required fields!';
    } else {
        if (!empty($product_image)) {
            $allowed_types = ['image/png', 'image/jpeg', 'image/jpg'];
            $file_type = mime_content_type($product_image_tmp_name);

            if (!in_array($file_type, $allowed_types)) {
                $message[] = 'Invalid image format. Please upload a PNG or JPEG image.';
            } else {
                // Update with a new image, automatically set status to 'approved'
                $update_data = "UPDATE products SET product_name='$product_name', location='$location', description='$description', quantity='$quantity', rent_days='$rent_days', categories='$categories', price='$product_price', image='$product_image', status='approved' WHERE id='$id'";
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
            $update_data = "UPDATE products SET product_name='$product_name', location='$location', description='$description', quantity='$quantity', rent_days='$rent_days', categories='$categories', price='$product_price', status='approved' WHERE id='$id'";
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
      ?>
      <form action="" method="post" enctype="multipart/form-data">
         <h3 class="title">Update the Product</h3>
         <input type="text" class="box" name="product_name" value="<?php echo htmlspecialchars($row['product_name']); ?>" placeholder="Enter the product name" required>
         <select id="register-address" name="location" required>
                    <option value="" disabled selected>Select Barangay</option>
                    <option value="Abangay">Abangay</option>
                    <option value="Amamaros">Amamaros</option>
                    <option value="Bagacay">Bagacay</option>
                    <option value="Barasan">Barasan</option>
                    <option value="Batuan">Batuan</option>
                    <option value="Bongco">Bongco</option>
                    <option value="Cahaguichican">Cahaguichican</option>
                    <option value="Callan">Callan</option>
                    <option value="Cansilayan">Cansilayan</option>
                    <option value="Casalsagan">Casalsagan</option>
                    <option value="Cato-ogan">Cato-ogan</option>
                    <option value="Cau-ayan">Cau-ayan</option>
                    <option value="Culob">Culob</option>
                    <option value="Danao">Danao</option>
                    <option value="Dapitan">Dapitan</option>
                    <option value="Dawis">Dawis</option>
                    <option value="Dongsol">Dongsol</option>
                    <option value="Fernando Parcon Ward">Fernando Parcon Ward</option>
                    <option value="Guibuangan">Guibuangan</option>
                    <option value="Guinacas">Guinacas</option>
                    <option value="Igang">Igang</option>
                    <option value="Intaluan">Intaluan</option>
                    <option value="Iwa Ilaud">Iwa Ilaud</option>
                    <option value="Iwa Ilaya">Iwa Ilaya</option>
                    <option value="Jamabalud">Jamabalud</option>
                    <option value="Jebioc">Jebioc</option>
                    <option value="Lay-ahan">Lay-ahan</option>
                    <option value="Lopez Jaena Ward">Lopez Jaena Ward</option>
                    <option value="Lumbo">Lumbo</option>
                    <option value="Macatol">Macatol</option>
                    <option value="Malusgod">Malusgod</option>
                    <option value="Nabitasan">Nabitasan</option>
                    <option value="Naga">Naga</option>
                    <option value="Nanga">Nanga</option>
                    <option value="Naslo">Naslo</option>
                    <option value="Pajo">Pajo</option>
                    <option value="Palanguia">Palanguia</option>
                    <option value="Pitogo">Pitogo</option>
                    <option value="Primitivo Ledesma Ward">Primitivo Ledesma Ward</option>
                    <option value="Purog">Purog</option>
                    <option value="Rumbang">Rumbang</option>
                    <option value="San Jose Ward">San Jose Ward</option>
                    <option value="Sinuagan">Sinuagan</option>
                    <option value="Tuburan">Tuburan</option>
                    <option value="Tumcon Ilaud">Tumcon Ilaud</option>
                    <option value="Tumcon Ilaya">Tumcon Ilaya</option>
                    <option value="Ubang">Ubang</option>
                    <option value="Zarrague">Zarrague</option>
            </select>
         <textarea class="box" name="description" placeholder="Enter product description" required><?php echo htmlspecialchars($row['description']); ?></textarea>
         <input type="number" min="0" class="box" name="product_quantity" value="<?php echo max(0, $row['quantity']); ?>" placeholder="Enter the product quantity" required>
         <input type="number" min="0" class="box" name="rent_days" value="<?php echo htmlspecialchars($row['rent_days']); ?>" placeholder="Enter the rent days" required>
         <select id="categories" name="categories" required>
                <option value="" disabled selected>Categories</option>
                <option value="Hand Tools">Hand Tools</option>
                <option value="Ploughs">Ploughs</option>
                <option value="Seeding Tools">Seeding Tools</option>
                <option value="Harvesting Tools">Harvesting Tools</option>
                <option value="Tilling Tools">Tilling Tools</option>
                <option value="Cutting Tools">Cutting Tools</option>
                <option value="Garden Tools">Garden Tools</option>
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

</body>
</html>
