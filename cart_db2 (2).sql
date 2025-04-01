-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2025 at 02:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cart_db2`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `town` varchar(50) DEFAULT NULL,
  `contact_number` varchar(15) NOT NULL,
  `address` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `images` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'pending',
  `profile_image` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `firstname`, `lastname`, `town`, `contact_number`, `address`, `email`, `password`, `images`, `status`, `profile_image`, `reset_token`, `reset_token_expiry`) VALUES
(31, 'patrick', 'Catalan', 'Pototan', '09123456781', 'Dapitan', 'larrydenverbiaco@gmail.com', '$2y$10$h4yM8w9HJbdvw96JdWEt2OL3f1Lm.3mVQgtsFB.17nFrclUQxnsPi', 'Cus_uploads/67e2ca57366746.14116490_photo_2025-03-23_22-06-41.jpg', 'approved', '67ebcd4ce2a3d_58794964-32a2-4e4b-8b66-7a97e29e4c36.jpg', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date_created` datetime NOT NULL,
  `order_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `customer_id`, `title`, `message`, `date_created`, `order_id`) VALUES
(53, 31, 'Order Status Update', 'You have successfully placed your order.', '2025-04-01 19:43:05', 63),
(54, 31, 'Order Status Update', 'You have successfully placed your order.', '2025-04-01 19:46:00', 64);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reference_number` varchar(255) NOT NULL,
  `delivery_method` enum('pickup','cod') NOT NULL DEFAULT 'pickup',
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','ready_to_pick_up','canceled','received') DEFAULT 'pending',
  `penalty_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `order_date`, `reference_number`, `delivery_method`, `total_price`, `status`, `penalty_amount`) VALUES
(63, 31, '2025-04-01 11:43:05', 'REF-20250401-F77B97', 'pickup', 320.00, 'pending', 20.00),
(64, 31, '2025-04-01 11:46:00', 'REF-20250401-7E2930', 'pickup', 370.00, 'received', 70.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`, `start_date`, `end_date`) VALUES
(63, 63, 34, 2, 50.00, '2025-04-01', '2025-04-05'),
(64, 63, 35, 2, 100.00, '2025-04-01', '2025-04-05'),
(65, 64, 34, 2, 50.00, '2025-04-01', '2025-04-10'),
(66, 64, 35, 2, 100.00, '2025-04-01', '2025-04-10');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `town` varchar(255) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `categories` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'pending',
  `rent_days` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `penalty_amount` decimal(10,2) DEFAULT 0.05
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `town`, `location`, `description`, `quantity`, `categories`, `price`, `image`, `status`, `rent_days`, `created_at`, `penalty_amount`) VALUES
(34, 'garab', 'Zarraga', 'Barangay I (Poblacion)', 'solid', 17, 'Tilling Tools', 50.00, 'images.jpg', 'approved', 10, '2025-04-01 11:36:15', 0.05),
(35, 'mini rake', 'Zarraga', 'Cansilayan', 'New', 1, 'Harvesting Tools', 100.00, '81KFG3Nf1EL._AC_SL1500_.jpg', 'approved', 2, '2025-04-01 11:37:16', 0.05),
(36, 'pliers', 'Pototan', 'Bongco', 'used once', 5, 'Hand Tools', 43.00, 'download (1).jpg', 'approved', 3, '2025-04-01 11:38:03', 0.05),
(37, 'Set seeding tools', 'Zarraga', 'Barangay III (Poblacion)', 'brand new', 3, 'Seeding Tools', 150.00, '51PLVmpcJUL.jpg', 'approved', 5, '2025-04-01 11:38:43', 0.05),
(38, 'scissors', 'Pototan', 'Fernando Parcon Ward', 'Used but not abuses', 7, 'Cutting Tools', 34.00, 'dressmaking-shears-cutting-tool-sewing.jpg', 'approved', 5, '2025-04-01 11:39:32', 0.05);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `fk_order_id` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_customer` (`customer_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
