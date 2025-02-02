<?php
session_start();
include 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: CustomerDashboard.php"); 
    exit();
}
$email = $_SESSION['email'];
$profile_query = "SELECT profile_image FROM customer WHERE email = '$email'";
$profile_result = $conn->query($profile_query);

if ($profile_result && $profile_result->num_rows > 0) {
    $profile_row = $profile_result->fetch_assoc();
    $profileImage = $profile_row['profile_image'] ? $profile_row['profile_image'] : 'default.png';
} else {
    $profileImage = 'default.png';
}

$sql = "SELECT id, categories, product_name, location, description, rent_days, price, created_at, image, quantity FROM products WHERE status = 'approved'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farming Tool and Rental System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/Customercss.css?v=1.0">
</head>
<body>
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="sidebar" id="sidebar">
                <div class="logo">
                    <h2>Farming Tool and Rental System</h2>
                </div>
                <nav>
                    <ul>
                        <li><a href="CusProfile.php">Profile</a></li>
                        <li><a href="History.php">History</a></li>
                        <li><a href="CusLogout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <header>
            <div class="sidebar-toggle">
                <i class="fa-solid fa-bars"></i> 
            </div>
            <div class="user-welcome">
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['firstname']); ?>!</p>
            </div>
            <div class="search-container">
                <input id="search-box" type="text" placeholder="Search Product here...">
                <div class="icons-container">
                    <div class="cart-icon">
                        <a href="order_summary.php">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </a>
                    </div>
                    <div class="notification-bell">
                        <a href="notifications.php">
                            <i class="fa-solid fa-bell"></i>
                        </a>
                    </div>
                    <div class="profile-picture">
                        <a href="CusProfile.php">
                            <?php if ($profileImage && file_exists("Cusprofile_pics/" . $profileImage)): ?>
                                <img src="Cusprofile_pics/<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Picture">
                            <?php else: ?>
                                <img src="Cusprofile_pics/default.png" alt="Default Profile Picture">
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Categories Buttons -->
        <div class="menu-categories">
            <button data-category="all">All</button>
            <button data-category="Hand Tools">Hand Tools</button>
            <button data-category="Ploughs">Ploughs</button>
            <button data-category="Seeding Tools">Seeding Tools</button>
            <button data-category="Harvesting Tools">Harvesting Tools</button>
            <button data-category="Tilling Tools">Tilling Tools</button>
            <button data-category="Cutting Tools">Cutting Tools</button>
            <button data-category="Garden Tools">Garden Tools</button>
        </div>

        <!-- Menu Items -->
        <div class="menu-items" id="menu-items">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $categories = htmlspecialchars($row['categories']);
                    $productName = htmlspecialchars($row['product_name']);
                    $location = htmlspecialchars($row['location']);
                    $description = htmlspecialchars($row['description']);
                    $rent_days = $row['rent_days'];  
                    $price = number_format($row['price'], 2);
                    $image = htmlspecialchars($row['image']);
                    $availableQuantity = $row['quantity'];
                    $outOfStockClass = $availableQuantity <= 0 ? 'out-of-stock' : '';
                    $outOfStockLabel = $availableQuantity <= 0 ? '<div class="out-of-stock-label">Out of Stock</div>' : '';
            ?>
                <div class="item <?php echo $outOfStockClass; ?>" data-id="<?php echo $row['id']; ?>" data-categories="<?php echo $categories; ?>" data-name="<?php echo $productName; ?>" data-price="<?php echo $price; ?>" data-quantity="<?php echo $availableQuantity; ?>">
                    <?php echo $outOfStockLabel; ?>
                    <div class="categories">
                        <?php echo $categories; ?>
                    </div>
                    <p><strong>Product Name:</strong> <?php echo $productName; ?></p>
                    <p><strong>Location:</strong> <?php echo $location; ?></p>
                    <p><strong>Description:</strong> <?php echo $description; ?></p>
                    <p><strong>Rent Days:</strong> <?php echo $rent_days; ?> </p>
                    <img src="uploaded_img/<?php echo $image; ?>" alt="<?php echo $productName; ?>" onerror="this.src='uploaded_img/default_image.jpg';">
                    <h3 class="item-price" style="color: red;">₱<?php echo $price; ?></h3>
                    <p><strong>Available:</strong> <?php echo max(0, $availableQuantity); ?></p>
                    <div class="quantity-control">
                        <button class="minus-btn" <?php echo $availableQuantity <= 0 ? 'disabled' : ''; ?>>-</button>
                        <span class="quantity">0</span>
                        <button class="plus-btn" <?php echo $availableQuantity <= 0 ? 'disabled' : ''; ?>>+</button>
                        <button class="rent-btn" <?php echo $availableQuantity <= 0 ? 'disabled' : ''; ?>>Rent</button>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p>No products available</p>";
            }
            $conn->close();
            ?>
        </div>
    </div>
    <script src = scripts.js></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Handle quantity adjustment buttons
            document.querySelectorAll('.minus-btn').forEach(button => {
                button.addEventListener('click', (event) => {
                    const itemElement = event.target.closest('.item');
                    const quantityElement = itemElement.querySelector('.quantity');
                    const currentQuantity = parseInt(quantityElement.textContent);
                    
                    if (currentQuantity > 0) {
                        quantityElement.textContent = currentQuantity - 1;
                    }
                });
            });

            document.querySelectorAll('.plus-btn').forEach(button => {
                button.addEventListener('click', (event) => {
                    const itemElement = event.target.closest('.item');
                    const quantityElement = itemElement.querySelector('.quantity');
                    const currentQuantity = parseInt(quantityElement.textContent);
                    const availableQuantity = parseInt(itemElement.dataset.quantity);

                    if (currentQuantity < availableQuantity) {
                        quantityElement.textContent = currentQuantity + 1;
                    }
                });
            });

            // Handle rent button clicks
            document.querySelectorAll('.rent-btn').forEach(button => {
                button.addEventListener('click', (event) => {
                    const itemElement = event.target.closest('.item');
                    const itemId = itemElement.dataset.id;
                    const itemName = itemElement.dataset.name;
                    const itemPrice = parseFloat(itemElement.dataset.price);
                    const itemImage = itemElement.querySelector('img').src.split('/').pop();
                    const quantityElement = itemElement.querySelector('.quantity');
                    const quantity = parseInt(quantityElement.textContent);

                    if (quantity <= 0) {
                        alert('Please select a quantity greater than 0.');
                        return;
                    }

                    // Store the item in session storage
                    let orderItems = JSON.parse(sessionStorage.getItem('orderItems') || '[]');
                    
                    // Check if item already exists
                    const existingItem = orderItems.find(item => item.id === itemId);
                    if (existingItem) {
                        existingItem.quantity += quantity;
                    } else {
                        orderItems.push({
                            id: itemId,
                            name: itemName,
                            price: itemPrice,
                            quantity,
                            image: itemImage,
                        });
                    }
                    
                    sessionStorage.setItem('orderItems', JSON.stringify(orderItems));
                    
                    // Reset quantity display
                    quantityElement.textContent = '0';
                    
                    // Redirect to order summary page
                    window.location.href = 'order_summary.php';
                });
            });

            // Category filtering
            document.querySelectorAll('.menu-categories button').forEach(button => {
                button.addEventListener('click', () => {
                    const category = button.dataset.category;
                    document.querySelectorAll('.item').forEach(item => {
                        if (category === 'all' || item.dataset.categories === category) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });

            // Search functionality
            const searchBox = document.getElementById('search-box');
            searchBox.addEventListener('input', () => {
                const searchTerm = searchBox.value.toLowerCase();
                document.querySelectorAll('.item').forEach(item => {
                    const itemName = item.dataset.name.toLowerCase();
                    if (itemName.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>