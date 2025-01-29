-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2025 at 06:30 PM
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
(20, 'larry', 'denverr', 'Zarraga', '09123456771', 'Tubigan', 'lry4750@gmail.com', '$2y$10$ql3DUEAbvyxRldpTmr3Ux.6FqMPVrp4G9bpce/pFp.EEFkX9amnMa', 'Cus_uploads/6799121ce68fc5.58074754_profileNobg.png', 'approved', '6799203e8c667_profile.png', NULL, NULL),
(21, 'larry', 'denverr', NULL, '09123456781', 'Poblacion Sur', 'larrydenverbiaco@gmail.com', '$2y$10$pb9Bjy4NwUjT0p/EW0is2.KCoJdJfFr85AwmsU16jRl0OGcaDkoJm', 'Cus_uploads/67991ee6597224.49621571_profile.png', 'pending', NULL, NULL, NULL);

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
(24, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-01-30 01:06:09', 34),
(25, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-01-30 01:11:06', 35),
(26, 20, 'Order Status Update', 'You have successfully placed your order.', '2025-01-30 01:16:08', 36);

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
  `status` enum('pending','ready_to_pick_up','canceled','received') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `order_date`, `reference_number`, `delivery_method`, `total_price`, `status`) VALUES
(34, 20, '2025-01-29 17:06:09', 'REF-20250129-98D718', 'pickup', 112.00, 'received'),
(35, 20, '2025-01-29 17:11:06', 'REF-20250129-34227F', 'pickup', 90.00, 'received'),
(36, 20, '2025-01-29 17:16:08', 'REF-20250129-C520A5', 'pickup', 180.00, 'received');

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
(32, 34, 23, 2, 11.00, '2025-01-30', '2025-02-02'),
(34, 35, 29, 1, 90.00, '2025-02-13', '2025-02-28');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `location`, `description`, `quantity`, `categories`, `price`, `image`, `status`, `rent_days`, `created_at`) VALUES
(23, 'sasas', 'Bongco', 'Amazing Product', 80, 'Hand Tools', 11.00, 'garden tool2.jpg', 'approved', 11, '2024-12-11 10:31:01'),
(24, 'sasas', 'Bongco', 'Original', 0, 'Seeding Tools', 100.00, 'seedingtool2.jpg', 'approved', 11, '2024-12-11 14:35:58'),
(25, 'tool 6', 'Callan', 'Excellent', 6, 'Ploughs', 180.00, 'ploughs2.jpg', 'approved', 4, '2024-12-11 14:52:03'),
(26, 'tool 10', 'Cansilayan', 'New', 13, 'Garden Tools', 50.00, 'garden tool2.jpg', 'approved', 10, '2024-12-11 14:54:21'),
(27, 'tool 55', 'Bongco', 'Brand New', 3, 'Tilling Tools', 90.00, 'tilling2.jpg', 'approved', 60, '2024-12-11 14:55:30'),
(28, 'tool 100', 'Cato-ogan', 'wowow', 11, 'Seeding Tools', 100.00, 'seedingtool2.jpg', 'approved', 5, '2024-12-11 16:17:05'),
(29, 'tool 31', 'Cahaguichican', 'qwregfgdfgdf', 10, 'Harvesting Tools', 90.00, 'harvesting tool1.jpg', 'approved', 99, '2024-12-11 16:20:25');

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
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

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
