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

$sql = "SELECT id, categories, product_name, location, description, rent_days, price,  created_at, image, quantity FROM products WHERE status = 'approved'";
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
            <!-- Close button -->
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
            <div class="profile-picture">
                <a href="CusProfile.php">
                    <?php if ($profileImage && file_exists("Cusprofile_pics/" . $profileImage)): ?>
                        <img src="Cusprofile_pics/<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Picture" height="50" width="50">
                    <?php else: ?>
                        <img src="Cusprofile_pics/default.png" alt="Default Profile Picture" height="50" width="50">
                    <?php endif; ?>
                </a>
            </div>
            <div class="notification-bell">
                <a href="notifications.php">
                    <i class="fa-solid fa-bell" style="font-size: 24px; color: #2F5233;"></i>
                </a>
            </div>
        </div>
        <div class="table-info"></div>
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
            <div class="item <?php echo $outOfStockClass; ?>" data-id="<?php echo $row['id']; ?>" data-categories ="<?php echo $row['categories']; ?>" data-name="<?php echo $productName; ?>" data-price="<?php echo $price; ?>" data-shippingfee="<?php echo $shippingFee; ?>" data-quantity="<?php echo $availableQuantity; ?>">
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

  <!-- Order Summary -->
  <div class="order-summary" style = "display: none;">
    <button class="close-summary" id="closeSummaryBtn">X</button>
    <h3>Order Summary</h3>

    <!-- Order List Section -->
    <div id="order-list"></div>

    <!-- Rental Period Section -->
    <div class="date-picker-container">
        <h4>Rental Period</h4>
        <div class="date-picker">
            <label for="start-date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" placeholder="Select start date" required>
        </div>
        <div class="date-picker">
            <label for="end-date">End Date:</label>
            <input type="date" id="end_date" name="end_date" placeholder="Select end date" required>
        </div>
    </div>

    <!-- Delivery Method Section -->
    <div class="delivery-method">
        <h4>Delivery Method</h4>
        <label>
        <input type="radio" name="delivery-method" value="pickup" checked> 
        Pick Up
        </label>
    </div>

    <!-- Total Calculation Section -->
    <div class="total">
        <p><strong>Total</strong></p>
        <p id="subtotal"><strong>₱0.00</strong></p>
    </div>
    

    <!-- Place Order Button -->
    <button class="place-order">Place Order</button>
  </div>

  <script src="scripts.js"></script>
  <script>
      document.addEventListener('DOMContentLoaded', () => {
            const orderSummary = document.querySelector('.order-summary');
            const closeSummaryBtn = document.getElementById('closeSummaryBtn');
            const orderList = document.getElementById('order-list');
            const subtotalElement = document.getElementById('subtotal');
            const shippingFeeElement = document.getElementById('shippingfee');
            const totalAmountElement = document.getElementById('total-amount');
            const placeOrderButton = document.querySelector('.place-order');
            const shippingFeeContainer = document.getElementById('shipping-fee-container');
            const deliveryMethodRadios = document.querySelectorAll('input[name="delivery-method"]');

            let orderItems = []; 
            let deliveryMethod = 'pickup'; 

            function showOrderSummary() {
                orderSummary.style.display = 'block';
            }

            function hideOrderSummary() {
                orderSummary.style.display = 'none';
            }
            // Update the order summary dynamically
            function updateOrderSummary() {
                orderList.innerHTML = '';
                let subtotal = 0;
                let totalShippingFee = 0;

                orderItems.forEach(item => {
                    const { id, name, price, quantity, shippingFee, image } = item;
                    subtotal += price * quantity;

                    // Calculate shipping fee: if Pick Up, set it to 0
                    if (deliveryMethod === 'pickup') {
                        totalShippingFee += 0; 
                    } else {
                        totalShippingFee += shippingFee * quantity;
                    }

                    const orderItem = document.createElement('div');
                    orderItem.className = 'order-item';
                    orderItem.innerHTML = `
                        <div class="order-item-image">
                            <img src="uploaded_img/${image}" alt="${name}" onerror="this.src='uploaded_img/default_image.jpg';">
                        </div>
                        <div class="order-item-details">
                            <p><strong>${name}</strong></p>
                            <p>₱${price.toFixed(2)} x ${quantity} = ₱${(price * quantity).toFixed(2)}</p>
                            ${deliveryMethod === 'cod' ? `<p>Shipping Fee: ₱${(shippingFee * quantity).toFixed(2)}</p>` : ''}
                        </div>
                        <div class="remove-btn-container">
                            <button class="remove-btn" data-id="${id}">Remove</button>
                        </div>
                    `;

                    // Add remove functionality
                    const removeButton = orderItem.querySelector('.remove-btn');
                    removeButton.addEventListener('click', (e) => {
                        const itemId = e.target.getAttribute('data-id');
                        removeItemFromOrder(itemId);
                    });

                    orderList.appendChild(orderItem);
                });

                // Update totals
                subtotalElement.textContent = `₱${subtotal.toFixed(2)}`;

                // Handle shipping fee visibility based on delivery method
                if (deliveryMethod === 'pickup') {
                    shippingFeeContainer.style.display = 'none';
                    shippingFeeElement.textContent = '₱0.00';
                    totalAmountElement.textContent = `₱${subtotal.toFixed(2)}`;
                } else {
                    shippingFeeContainer.style.display = 'flex';
                    shippingFeeElement.textContent = `₱${totalShippingFee.toFixed(2)}`;
                    totalAmountElement.textContent = `₱${(subtotal + totalShippingFee).toFixed(2)}`;
                }
            }

            function removeItemFromOrder(itemId) {
                // Remove the item from the orderItems array
                orderItems = orderItems.filter(item => item.id !== itemId);

                // Update the order summary display
                updateOrderSummary();
            }


            // Delivery method change event
            deliveryMethodRadios.forEach(radio => {
                radio.addEventListener('change', (event) => {
                    deliveryMethod = event.target.value;
                    updateOrderSummary();
                });
            });

            // Add item to the order summary when Rent button is clicked
            document.querySelectorAll('.rent-btn').forEach(button => {
                button.addEventListener('click', (event) => {
                    const itemElement = event.target.closest('.item');
                    const itemId = itemElement.dataset.id;
                    const itemName = itemElement.dataset.name;
                    const itemPrice = parseFloat(itemElement.dataset.price);
                    const itemShippingFee = parseFloat(itemElement.dataset.shippingfee);
                    const itemImage = itemElement.querySelector('img').src.split('/').pop(); 

                    const quantityElement = itemElement.querySelector('.quantity');
                    const quantity = parseInt(quantityElement.textContent);

                    if (quantity <= 0) {
                        alert('Please select a quantity greater than 0.');
                        return;
                    }

                    // Check if item already exists in the order list
                    const existingItem = orderItems.find(item => item.id === itemId);

                    if (existingItem) {
                        // Update quantity if already in the list
                        existingItem.quantity += quantity;
                    } else {
                        // Add new item to the list
                        orderItems.push({
                            id: itemId,
                            name: itemName,
                            price: itemPrice,
                            shippingFee: itemShippingFee,
                            quantity,
                            image: itemImage,
                        });
                    }

                    // Reset quantity in the product listing
                    quantityElement.textContent = '0';

                    // Update the order summary display
                    updateOrderSummary();
                    showOrderSummary();
                });
            });

            // Handle quantity adjustment buttons
            document.querySelectorAll('.minus-btn').forEach(button => {
                button.addEventListener('click', (event) => {
                    const itemElement = event.target.closest('.item');
                    const quantityElement = itemElement.querySelector('.quantity');
                    const currentQuantity = parseInt(quantityElement.textContent);
                    const availableQuantity = parseInt(itemElement.dataset.quantity);
                    const plusButton = itemElement.querySelector('.plus-btn');
                    
                    if (currentQuantity > 0) {
                        quantityElement.textContent = currentQuantity - 1;
                    }

                    // Enable the plus button if the quantity is less than the available stock
                    if (currentQuantity - 1 < availableQuantity) {
                        plusButton.removeAttribute('disabled');
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

                    // Disable the plus button if the quantity reaches the available stock
                    if (currentQuantity + 1 >= availableQuantity) {
                        event.target.setAttribute('disabled', 'true');
                    }
                });
            });

            // Ensure the plus button is re-enabled when quantity changes
            document.querySelectorAll('.quantity').forEach(quantityElement => {
                quantityElement.addEventListener('DOMSubtreeModified', () => {
                    const itemElement = quantityElement.closest('.item');
                    const currentQuantity = parseInt(quantityElement.textContent);
                    const availableQuantity = parseInt(itemElement.dataset.quantity);
                    const plusButton = itemElement.querySelector('.plus-btn');

                    // Re-enable the plus button if the quantity is less than the available stock
                    if (currentQuantity < availableQuantity) {
                        plusButton.removeAttribute('disabled');
                    }
                });
            });

            




            placeOrderButton.addEventListener('click', () => {
            if (orderItems.length === 0) {
                alert('Please add items to your order.');
                return;
            }

            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (!startDate || !endDate) {
                alert('Please select start and end dates for your rental period.');
                return;
            }

            if (new Date(startDate) > new Date(endDate)) {
                alert('End date cannot be before start date.');
                return;
            }

            const orderData = {
                orderDetails: orderItems.map(item => ({
                    id: item.id,
                    lender_id: item.lender_id,
                    quantity: item.quantity,
                    price: item.price,
                    start_date: startDate,
                    end_date: endDate
                })),
                deliveryMethod,
                start_date: startDate,
                end_date: endDate
            };

            fetch('saveOrder.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Your order has been placed successfully!');
                        window.location.href = 'order_details.php'; 
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });


                
            });
            
            document.querySelectorAll('.rent-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const orderSummary = document.querySelector('.order-summary');
                    orderSummary.style.display = 'block'; // Show the order summary
                });
            });

            document.querySelector('.close-summary-btn').addEventListener('click', () => {
                const orderSummary = document.querySelector('.order-summary');
                orderSummary.style.display = 'none'; // Hide the order summary
            });

            document.addEventListener('DOMContentLoaded', () => {
                const notificationBell = document.querySelector('.notification-bell a');
                const notificationsContainer = document.createElement('div');
                notificationsContainer.classList.add('notifications-container');
                document.body.appendChild(notificationsContainer);

                // Fetch notifications from the server
                function fetchNotifications() {
                    fetch('fetch_notifications.php') // PHP script to get notifications for the customer
                        .then(response => response.json())
                        .then(data => {
                            notificationsContainer.innerHTML = ''; // Clear previous notifications
                            if (data && data.notifications.length > 0) {
                                data.notifications.forEach(notification => {
                                    const notificationElement = document.createElement('div');
                                    notificationElement.classList.add('notification');
                                    notificationElement.innerHTML = `
                                        <p><strong>${notification.title}</strong></p>
                                        <p>${notification.message}</p>
                                        <p><small>${notification.date}</small></p>
                                        <hr>
                                        <button class="mark-as-read" data-id="${notification.id}">Mark as Read</button>
                                    `;
                                    notificationsContainer.appendChild(notificationElement);
                                });
                            } else {
                                notificationsContainer.innerHTML = '<p>No new notifications.</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching notifications:', error);
                        });
                }

                // Show the notifications container when the notification bell is clicked
                notificationBell.addEventListener('click', () => {
                    notificationsContainer.classList.toggle('active');
                    fetchNotifications();
                });

                // Mark notification as read
                notificationsContainer.addEventListener('click', event => {
                    if (event.target.classList.contains('mark-as-read')) {
                        const notificationId = event.target.getAttribute('data-id');
                        fetch('mark_as_read.php', { // PHP script to mark notification as read
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: notificationId }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                event.target.parentElement.classList.add('read');
                                event.target.remove(); // Remove the "Mark as Read" button after it's marked
                            } else {
                                alert('Error marking notification as read.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                    }
                });

                // Fetch notifications initially when the page loads
                fetchNotifications();
            });



        });



  </script>
</body>
</html>