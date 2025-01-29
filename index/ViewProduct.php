<?php
session_start();
@include 'config.php';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']); 
    $select_image = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $select_image->bind_param("i", $id);
    $select_image->execute();
    $result = $select_image->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        if (file_exists('uploaded_img/' . $row['image'])) {
            unlink('uploaded_img/' . $row['image']);
        }

        $delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $delete_stmt->bind_param("i", $id);
        $delete_stmt->execute();
        $delete_stmt->close();
    }

    header('Location: ViewProduct.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="css/lender.css">
</head>
<body>
<style>
    /* General Styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        margin: 0;
        padding: 0;
        color: #333;
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    /* Header and Navigation */
    nav {
        background-color: #2F5233; 
        padding: 15px;
    }

    nav ul {
        list-style: none;
        display: flex;
        justify-content: space-between;
        margin: 0;
        padding: 0;
    }

    nav ul li a {
        color: white;
        font-size: 16px;
        padding: 10px 15px;
        display: block;
        transition: background-color 0.3s;
    }

    nav ul li a:hover {
        background-color: #3a6f4c; 
    }

    /* Product Table */
    .product-display {
        margin: 30px auto;
        max: 1200px;
        padding: 0 15px;
    }

    .product-display-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #ddd;
    }

    .product-display-table th, .product-display-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .product-display-table th {
        background-color: #2F5233;
        color: white;
        font-size: 14px;
    }

    .product-display-table td {
        font-size: 20px;
    }

    .product-display-table td img {
        width: 60px;
        height: auto;
    }

    .product-display-table td .btn {
        padding: 8px 15px;
        background-color: #2F5233;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
        font-size: 14px;
    }

    .product-display-table td .btn:hover {
        background-color: #3a6f4c; 
    }

    /* Modal for Image */
    #imageModal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
    }

    #imageModal .modal-content {
        max-width: 80%;
        margin: auto;
        display: block;
    }

    #imageModal #modalCaption {
        color: white;
        text-align: center;
        margin-top: 10px;
    }

    #imageModal .close {
        color: white;
        font-size: 30px;
        position: absolute;
        top: 10px;
        right: 25px;
        cursor: pointer;
    }

    #imageModal .close:hover {
        color: #ff6347; /* Change close icon color */
    }

    /* Minimalist Buttons */
    .btn {
        font-size: 14px;
        padding: 8px 15px;
        border-radius: 5px;
        background-color: #2F5233;
        color: white;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn:hover {
        background-color: #3a6f4c;
    }

    .back-button-container {
        margin: 10px 0;
    }

    .back-button {
        text-decoration: none;
        color: #2F5233;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 16px;
        transition: background-color 0.3s;
    }

    .back-button i {
        font-size: 30px;
    }

    .back-button:hover {
        color: #0056b3;
    }

    /* Mobile Responsiveness */
    @media screen and (max-width: 768px) {
        .product-display-table {
            font-size: 12px;
        }

        .product-display-table th, .product-display-table td {
            padding: 10px;
        }

        .btn {
            font-size: 12px;
            padding: 6px 10px;
        }

        #imageModal .modal-content {
            max-width: 90%;
        }
    }

</style>

<div class="back-button-container">
    <a href="../Admin.php" class="back-button"><i class="fa-solid fa-house"></i></a>
</div>

<div class="product-display">
    <table class="product-display-table">
        <thead>
        <tr>
            <th>Product Image</th>
            <th>Product Name</th>
            <th>Location</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Rent Days</th>
            <th>Rent Price</th>
            <th>Category</th>
            <th>Status</th> 
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $select = $conn->query("SELECT * FROM products");
        while ($row = $select->fetch_assoc()) { ?>
            <tr>
                <td>
                    <img class="product-image" src="uploaded_img/<?php echo htmlspecialchars($row['image']); ?>" height="100" alt="Product: <?php echo htmlspecialchars($row['product_name']); ?>">
                </td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['location']); ?></td>
                <td class="description"><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo max(0, $row['quantity']); ?></td>
                <td><?php echo htmlspecialchars($row['rent_days']); ?></td>
                <td>₱<?php echo htmlspecialchars($row['price']); ?></td>
                <td><?php echo htmlspecialchars($row['categories']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <a href="UpdateProd.php?edit=<?php echo $row['id']; ?>" class="btn"><i class="fas fa-edit"></i> Edit</a>
                    <a href="ViewProduct.php?delete=<?php echo $row['id']; ?>" class="btn" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fas fa-trash"></i> Delete</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<div id="imageModal" class="modal">
    <span class="close" id="closeModal">&times;</span>
    <img class="modal-content" id="expandedImage">
    <div id="modalCaption"></div>
</div>

<script>
var modal = document.getElementById("imageModal");
var modalImg = document.getElementById("expandedImage");
var captionText = document.getElementById("modalCaption");
var closeBtn = document.getElementById("closeModal");
var images = document.querySelectorAll('.product-image');
images.forEach(function(image) {
    image.onclick = function() {
        modal.style.display = "block";
        modalImg.src = this.src; 
        captionText.innerHTML = this.alt;
    };
});

closeBtn.onclick = function() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target === modal) {
        modal.style.display = "none";
    }
}

// Select the sidebar and the hamburger icon
const sidebar = document.querySelector('.sidebar');
const sidebarToggle = document.querySelector('.sidebar-toggle');

// Toggle the sidebar active class when the hamburger icon is clicked
sidebarToggle.addEventListener('click', function() {
    sidebar.classList.toggle('active');
});
</script>

</body>
</html>
