-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 06, 2025 at 06:03 PM
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
(20, 'patrick', 'denverr', 'Zarraga', '09123456771', 'Tubigan', 'lry4750@gmail.com', '$2y$10$ql3DUEAbvyxRldpTmr3Ux.6FqMPVrp4G9bpce/pFp.EEFkX9amnMa', 'Cus_uploads/6799121ce68fc5.58074754_profileNobg.png', 'approved', '6799203e8c667_profile.png', NULL, NULL),
(22, 'denver', 'asasas', NULL, '09123456781', 'Talauguis', 'larrydenverbiaco@gmail.com', '$2y$10$7QE.CrgAK0nODKZnhtKOgOnisOLA32y8ZJzDAa7xSQK2x8DNBY.jS', 'Cus_uploads/67a4e55180f721.61419273_Untitled-1.png', 'approved', NULL, NULL, NULL);

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
(19, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-01-30 00:22:54', NULL),
(20, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-01-30 00:53:42', NULL),
(21, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-01-30 00:58:14', NULL),
(22, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-01-30 01:00:48', NULL),
(23, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-01-30 01:01:47', NULL),
(24, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-01-30 01:06:09', NULL),
(25, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-01-30 01:11:06', NULL),
(26, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-01-30 01:16:08', NULL),
(27, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-02 18:44:57', NULL),
(28, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-02 19:16:33', NULL),
(29, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-02 19:29:30', NULL),
(30, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-02 19:35:55', NULL),
(31, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-02 19:49:17', NULL),
(32, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-02 21:10:07', NULL),
(33, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-06 18:48:26', NULL),
(34, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-06 19:00:44', NULL),
(35, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-06 19:06:42', NULL),
(36, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-06 19:07:19', NULL),
(37, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-06 19:08:02', NULL),
(38, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-06 19:13:26', NULL),
(39, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-06 19:15:36', NULL),
(40, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-06 19:19:33', NULL),
(41, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-06 19:24:01', 51),
(42, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-06 19:25:12', 52),
(43, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-06 19:35:37', 53),
(44, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-02-06 19:36:06', 54);

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
(51, 20, '2025-02-06 11:24:01', 'REF-20250206-DBF71B', 'pickup', 180.00, 'received', 0.00),
(52, 20, '2025-02-06 11:25:12', 'REF-20250206-4C1215', 'pickup', 189.00, 'canceled', 9.00),
(53, 20, '2025-02-06 11:35:37', 'REF-20250206-FE2C12', 'pickup', 207.00, 'received', 27.00),
(54, 20, '2025-02-06 11:36:06', 'REF-20250206-BAA9DC', 'pickup', 52.50, 'received', 2.50);

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
(50, 51, 25, 1, 180.00, '2025-02-06', '2025-02-11'),
(51, 52, 25, 1, 180.00, '2025-02-06', '2025-02-11'),
(52, 53, 25, 1, 180.00, '2025-02-06', '2025-02-13'),
(53, 54, 26, 1, 50.00, '2025-02-06', '2025-02-17');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
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

INSERT INTO `products` (`id`, `product_name`, `location`, `description`, `quantity`, `categories`, `price`, `image`, `status`, `rent_days`, `created_at`, `penalty_amount`) VALUES
(23, 'sasas', 'Batuan', 'Amazing Product', 76, 'Seeding Tools', 11.00, 'garden tool2.jpg', 'approved', 11, '2024-12-11 10:31:01', 0.05),
(24, 'sasas', 'Casalsagan', 'Original', 9, 'Seeding Tools', 100.00, 'seedingtool2.jpg', 'approved', 11, '2024-12-11 14:35:58', 0.05),
(25, 'tool 6', 'Cahaguichican', 'Excellent', 9, 'Seeding Tools', 180.00, 'ploughs2.jpg', 'approved', 4, '2024-12-11 14:52:03', 0.05),
(26, 'tool 10', 'Callan', 'New', 8, 'Seeding Tools', 50.00, 'garden tool2.jpg', 'approved', 10, '2024-12-11 14:54:21', 0.05),
(27, 'tool 55', 'Bongco', 'Brand New', 0, 'Tilling Tools', 90.00, 'tilling2.jpg', 'approved', 60, '2024-12-11 14:55:30', 0.05),
(28, 'tool 100', 'Barasan', 'wowow', 0, 'Harvesting Tools', 100.00, 'seedingtool2.jpg', 'approved', 5, '2024-12-11 16:17:05', 0.05),
(29, 'tool 31', 'Cahaguichican', 'qwregfgdfgdf', 10, 'Harvesting Tools', 90.00, 'harvesting tool1.jpg', 'approved', 99, '2024-12-11 16:20:25', 0.05);

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
  ADD PRIMARY KEY (`id`);

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
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

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
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
