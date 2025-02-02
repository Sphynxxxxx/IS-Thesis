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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/Customercss.css?v=1.0">
    <style>
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .cart-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .summary-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .order-item {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .order-item-image img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }

        .order-item-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .item-name {
            font-size: 1.1em;
            font-weight: 600;
            color: #333;
        }

        .item-price {
            font-size: 1.2em;
            color: #2F5233;
            font-weight: 600;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }

        .quantity-btn {
            background: #2F5233;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }

        .quantity-btn:hover {
            background: #1e351f;
        }

        .quantity-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .quantity-display {
            font-size: 16px;
            min-width: 30px;
            text-align: center;
        }

        .remove-btn {
            background: #ff4444;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .remove-btn:hover {
            background: #cc0000;
        }

        .date-picker-container {
            margin: 20px 0;
        }

        .date-picker {
            margin: 10px 0;
        }

        .date-picker label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }

        .date-picker input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .delivery-method {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
        }

        .total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            font-size: 1.2em;
            font-weight: 600;
        }

        .place-order {
            width: 100%;
            padding: 15px;
            background: #2F5233;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.1em;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .place-order:hover {
            background: #1e351f;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background: #f8f9fa;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .back-button i {
            margin-right: 5px;
        }

        .section-title {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .summary-section {
                position: static;
            }

            .quantity-btn {
                width: 25px;
                height: 25px;
                font-size: 14px;
            }
            
            .quantity-display {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <!-- Sidebar -->
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

        <!-- Order Content -->
        <div class="container">
            <div class="cart-section">
                <a href="CustomerDashboard.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
                <h2 class="section-title">Shopping Cart</h2>
                <div id="order-list">
                    <!-- Order items will be inserted here -->
                </div>
            </div>

            <div class="summary-section">
                <h2 class="section-title">Order Summary</h2>
                
                <div class="date-picker-container">
                    <h3>Rental Period</h3>
                    <div class="date-picker">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>
                    <div class="date-picker">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" required>
                    </div>
                </div>

                <div class="delivery-method">
                    <h3>Delivery Method</h3>
                    <label>
                        <input type="radio" name="delivery-method" value="pickup" checked> 
                        Pick Up
                    </label>
                </div>

                <div class="summary-details">
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span id="subtotal">₱0.00</span>
                    </div>
                    <div class="total">
                        <span>Total</span>
                        <span id="total-amount">₱0.00</span>
                    </div>
                </div>

                <button class="place-order">Place Order</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const orderList = document.getElementById('order-list');
            const subtotalElement = document.getElementById('subtotal');
            const totalAmountElement = document.getElementById('total-amount');
            const placeOrderButton = document.querySelector('.place-order');

            // Function to check product availability from database
            async function checkAvailability(productId) {
                try {
                    const response = await fetch(`check_availability.php?id=${productId}`);
                    const data = await response.json();
                    return data.quantity;
                } catch (error) {
                    console.error('Error checking availability:', error);
                    return 0;
                }
            }

            async function loadOrderItems() {
                const orderItems = JSON.parse(sessionStorage.getItem('orderItems') || '[]');
                let subtotal = 0;

                orderList.innerHTML = '';
                
                // First, check availability for all items
                const availabilityPromises = orderItems.map(async (item) => {
                    try {
                        const response = await fetch(`check_availability.php?id=${item.id}`);
                        const data = await response.json();
                        return { ...item, availableQuantity: data.quantity };
                    } catch (error) {
                        console.error('Error checking availability for item', item.id, error);
                        return { ...item, availableQuantity: 0 };
                    }
                });

                const updatedOrderItems = await Promise.all(availabilityPromises);
                sessionStorage.setItem('orderItems', JSON.stringify(updatedOrderItems));

                updatedOrderItems.forEach(item => {
                    const itemTotal = item.price * item.quantity;
                    subtotal += itemTotal;

                    const orderItem = document.createElement('div');
                    orderItem.className = 'order-item';
                    orderItem.innerHTML = `
                        <div class="order-item-image">
                            <img src="uploaded_img/${item.image}" alt="${item.name}" onerror="this.src='uploaded_img/default_image.jpg';">
                        </div>
                        <div class="order-item-details">
                            <span class="item-name">${item.name}</span>
                            <span class="item-price">₱${parseFloat(item.price).toFixed(2)}</span>
                            <div class="quantity-controls">
                                <button class="quantity-btn minus" data-id="${item.id}" ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                                <span class="quantity-display">${item.quantity}</span>
                                <button class="quantity-btn plus" data-id="${item.id}" ${item.quantity >= item.availableQuantity ? 'disabled' : ''}>+</button>
                            </div>
                            <span class="item-total">Total: ₱${itemTotal.toFixed(2)}</span>
                            <span class="stock-info">Available Stock: ${item.availableQuantity || 0}</span>
                            ${item.quantity >= item.availableQuantity ? '<span class="quantity-warning">Maximum quantity reached</span>' : ''}
                        </div>
                        <div class="item-controls">
                            <button class="remove-btn" data-id="${item.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                    orderList.appendChild(orderItem);
                });

                subtotalElement.textContent = `₱${subtotal.toFixed(2)}`;
                totalAmountElement.textContent = `₱${subtotal.toFixed(2)}`;

                if (updatedOrderItems.length === 0) {
                    orderList.innerHTML = '<p style="text-align: center; padding: 20px;">Your cart is empty</p>';
                }
            }

            orderList.addEventListener('click', async (e) => {
                const button = e.target;
                if (button.classList.contains('quantity-btn')) {
                    const itemId = button.dataset.id;
                    const isPlus = button.classList.contains('plus');
                    
                    let orderItems = JSON.parse(sessionStorage.getItem('orderItems') || '[]');
                    const itemIndex = orderItems.findIndex(item => item.id === itemId);
                    
                    if (itemIndex !== -1) {
                        const item = orderItems[itemIndex];
                        const currentStock = await checkAvailability(item.id);
                        
                        if (isPlus) {
                            if (item.quantity < currentStock) {
                                item.quantity++;
                            } else {
                                alert(`Sorry, only ${currentStock} items available in stock.`);
                                return;
                            }
                        } else if (item.quantity > 1) {
                            item.quantity--;
                        }
                        
                        sessionStorage.setItem('orderItems', JSON.stringify(orderItems));
                        loadOrderItems();
                    }
                }
                
                if (e.target.closest('.remove-btn')) {
                    const itemId = e.target.closest('.remove-btn').dataset.id;
                    let orderItems = JSON.parse(sessionStorage.getItem('orderItems') || '[]');
                    orderItems = orderItems.filter(item => item.id !== itemId);
                    sessionStorage.setItem('orderItems', JSON.stringify(orderItems));
                    loadOrderItems();
                }
            });

            placeOrderButton.addEventListener('click', async () => {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                const deliveryMethod = document.querySelector('input[name="delivery-method"]:checked').value;

                if (!startDate || !endDate) {
                    alert('Please select start and end dates for your rental period.');
                    return;
                }

                if (new Date(startDate) > new Date(endDate)) {
                    alert('End date cannot be before start date.');
                    return;
                }

                const orderItems = JSON.parse(sessionStorage.getItem('orderItems') || '[]');
                if (orderItems.length === 0) {
                    alert('Your cart is empty.');
                    return;
                }

                // Verify stock availability before placing order
                for (const item of orderItems) {
                    const currentStock = await checkAvailability(item.id);
                    if (item.quantity > currentStock) {
                        alert(`Sorry, "${item.name}" only has ${currentStock} items available in stock.`);
                        return;
                    }
                }

                const orderData = {
                    orderDetails: orderItems,
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
                        sessionStorage.removeItem('orderItems');
                        alert('Your order has been placed successfully!');
                        window.location.href = 'order_details.php';
                    } else {
                        alert(data.message || 'An error occurred while placing your order.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            });

            // Set minimum date for date inputs
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').min = today;
            document.getElementById('end_date').min = today;

            // Load items when page loads
            loadOrderItems();

            // Sidebar toggle functionality
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            
            sidebarToggle.addEventListener('click', () => {
                sidebar.style.left = sidebar.style.left === '0px' ? '-250px' : '0px';
            });

            // Close sidebar when clicking outside
            document.addEventListener('click', (e) => {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.style.left = '-250px';
                }
            });

            // Date validation
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            startDateInput.addEventListener('change', () => {
                endDateInput.min = startDateInput.value;
                if (endDateInput.value && new Date(endDateInput.value) < new Date(startDateInput.value)) {
                    endDateInput.value = startDateInput.value;
                }
            });

            // Update quantity limits
            function updateQuantityButtons(item, quantityDisplay) {
                const minusBtn = quantityDisplay.previousElementSibling;
                const plusBtn = quantityDisplay.nextElementSibling;
                
                if (item.quantity <= 1) {
                    minusBtn.disabled = true;
                } else {
                    minusBtn.disabled = false;
                }

                if (item.quantity >= item.availableQuantity) {
                    plusBtn.disabled = true;
                } else {
                    plusBtn.disabled = false;
                }
            }
        });
    </script>
</body>
</html>