-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2025 at 04:08 PM
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
-- Database: `inventory_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Baked Goods and Snacks'),
(3, 'Cofee Beans'),
(5, 'Equipment and Supplies'),
(4, 'Milk and Milk alternatives'),
(2, 'Sweet'),
(8, 'Syrup and Flavoring'),
(6, 'Tea');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` varchar(50) DEFAULT NULL,
  `buy_price` decimal(25,2) DEFAULT NULL,
  `categorie_id` int(11) UNSIGNED NOT NULL,
  `media_id` int(11) DEFAULT 0,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `quantity`, `buy_price`, `categorie_id`, `media_id`, `date`) VALUES
(20, 'White Bread Gardenia', '35', 3.00, 1, 0, '2025-06-01 16:27:40'),
(21, 'Chocolate Wafer Kitkat', '40', 2.00, 2, 0, '2025-06-01 16:30:24'),
(22, 'Oreo Cookies', '50', 2.50, 2, 0, '2025-05-03 10:45:00'),
(23, 'Cheddar Cheese', '25', 5.00, 4, 0, '2025-05-04 11:00:00'),
(24, 'Peanut Butter', '35', 4.00, 1, 0, '2025-05-05 12:15:00'),
(25, 'Organic Honey', '20', 6.00, 8, 0, '2025-05-06 13:30:00'),
(26, 'Whole Wheat Pasta', '45', 3.50, 1, 0, '2025-05-07 14:45:00'),
(27, 'Tomato Sauce', '55', 2.75, 8, 0, '2025-05-08 15:00:00'),
(28, 'Fresh Milk 1L', '60', 1.50, 4, 0, '2025-05-09 16:15:00'),
(29, 'Yogurt Strawberry', '40', 2.25, 4, 0, '2025-05-10 17:30:00'),
(30, 'Coffee Beans Arabica 500g', '70', 7.00, 3, 0, '2025-05-11 18:45:00'),
(31, 'Butter Unsalted', '25', 4.75, 1, 0, '2025-05-12 08:00:00'),
(32, 'Salted Crackers', '50', 1.80, 1, 0, '2025-05-13 09:15:00'),
(33, 'Green Tea Bags', '40', 3.00, 6, 0, '2025-05-14 10:30:00'),
(34, 'Coffee Beans Robusta 500g', '30', 6.50, 3, 0, '2025-05-15 11:45:00'),
(35, 'Canned Tuna', '60', 2.80, 5, 0, '2025-05-16 12:00:00'),
(36, 'Rice Cooker', '10', 50.00, 5, 0, '2025-05-17 13:15:00'),
(37, 'Cooking Oil 1L', '80', 5.50, 8, 0, '2025-05-18 14:30:00'),
(38, 'Frozen Peas 1kg', '50', 3.75, 1, 0, '2025-05-19 15:45:00'),
(39, 'Fresh Carrots', '70', 1.20, 1, 0, '2025-05-20 16:00:00'),
(40, 'Vanilla Syrup', '45', 4.50, 8, 0, '2025-05-21 17:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `restock`
--

CREATE TABLE `restock` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restock`
--

INSERT INTO `restock` (`id`, `product_id`, `quantity`, `date`, `status`) VALUES
(21, 20, 5, '2025-06-10', 1),
(22, 23, 20, '2025-06-11', 0);

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(11) NOT NULL,
  `restock_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `date` datetime NOT NULL,
  `reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`id`, `restock_id`, `product_name`, `quantity`, `price_per_unit`, `total`, `date`, `reason`) VALUES
(14, 21, 'White Bread Gardenia', 5, 0.00, 0.00, '2025-06-01 22:36:41', 'Mismatch');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_level` int(11) NOT NULL,
  `image` varchar(255) DEFAULT 'no_image.jpg',
  `status` int(1) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `user_level`, `image`, `status`, `last_login`, `failed_attempts`, `locked_until`) VALUES
(1, 'Nasrullah', 'Nas', '227c1900a7759609b62da011740e60a1b2477386', 1, '6a69rdpu1.jpg', 1, '2025-06-17 15:53:15', 0, NULL),
(9, 'Danish Aqil', 'danish', '227c1900a7759609b62da011740e60a1b2477386', 2, 'isc6a1j89.jpg', 1, '2025-06-17 15:46:43', 5, '2025-06-17 16:02:28');

-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

CREATE TABLE `user_groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(150) NOT NULL,
  `group_level` int(11) NOT NULL,
  `group_status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_groups`
--

INSERT INTO `user_groups` (`id`, `group_name`, `group_level`, `group_status`) VALUES
(1, 'Admin', 1, 1),
(2, 'Employee', 2, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `categorie_id` (`categorie_id`),
  ADD KEY `media_id` (`media_id`);

--
-- Indexes for table `restock`
--
ALTER TABLE `restock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restock_id` (`restock_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_level` (`user_level`);

--
-- Indexes for table `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_level` (`group_level`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `restock`
--
ALTER TABLE `restock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_groups`
--
ALTER TABLE `user_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `FK_products` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`restock_id`) REFERENCES `restock` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `FK_user` FOREIGN KEY (`user_level`) REFERENCES `user_groups` (`group_level`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
