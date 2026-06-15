-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2026 at 03:24 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


DROP DATABASE IF EXISTS made_to_fade;

CREATE DATABASE made_to_fade;

USE made_to_fade;


--
-- Database: `made_to_fade`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `courier_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `order_status` varchar(50) DEFAULT NULL,
  `delivery_status` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'Pending',
  `stock_deducted` varchar(10) DEFAULT 'No',
  `proof_image` varchar(255) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT current_timestamp(),
  `payment_screenshot` varchar(255) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_reject_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `courier_id`, `total`, `order_status`, `delivery_status`, `payment_method`, `payment_status`, `stock_deducted`, `proof_image`, `payment_proof`, `address`, `contact_number`, `date_created`, `payment_screenshot`, `payment_reference`, `payment_reject_reason`) VALUES
(1, 2, 3, 499.00, 'Processing', 'Out for Delivery', 'Cash on Delivery', 'Paid', 'No', '', NULL, 'Sample Address', '0912 123 1234', '2026-06-10 22:01:11', NULL, NULL, NULL),
(2, 2, NULL, 499.00, 'Pending', 'Preparing', 'Cash on Delivery', 'Pending', 'No', '', NULL, 'Pasig', '0912 123 1234', '2026-06-11 00:12:29', NULL, NULL, NULL),
(3, 2, NULL, 499.00, 'Pending', 'Preparing', 'GCash', 'Pending', 'No', '', NULL, 'Bridgetowne ', '0912 123 1234', '2026-06-11 00:43:44', NULL, NULL, NULL),
(4, 2, NULL, 499.00, 'Pending', 'Preparing', 'GCash', 'Proof Submitted', 'No', '', NULL, 'Bridgetowne ', '0912 123 1234', '2026-06-11 01:13:56', 'payment_4_1781140513.png', '2041511124874', '');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`) VALUES
(1, 1, 1, 1),
(2, 2, 1, 1),
(3, 3, 1, 1),
(4, 4, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `stock`, `image`, `category`, `sku`, `color`, `size`) VALUES
(1, 'RED EAST Warriors', 499.00, 18, '20f79e78-8c6e-43ad-a9fa-a6d71a5cd2c0.jpg', 'T-Shirt', 'MTF-T-SHIRT-WHITE-L-5709', 'White', 'L'),
(2, 'RED EAST Warriors', 599.00, 15, '456625ac-43de-4e92-9bdc-deaee3ca48e7.jpg', 'T-Shirt', 'MTF-T-SHIRT-RED-L-5938', 'Red', 'L'),
(3, 'RED EAST Warriors', 699.00, 10, 'edc5812f-2b7e-43be-a920-384df9dfdd20.jpg', 'T-Shirt', 'MTF-T-SHIRT-BLACK-L-1317', 'Black', 'L');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`) VALUES
(1, 'Admin Account', 'admin', '$2y$10$t6G/2q5/kFZfbfgXcJQFxuUV5DXgPRCoqhiAo48n/BqQXqOeLYBti', 'admin'),
(2, 'Customer Account', 'customer', '$2y$10$..7RNSvt6LxfOEKua271fu40kE2MV3YEmdKUfaFCnEc0sL0fJbvaK', 'customer'),
(3, 'Courier Account', 'courier', '$2y$10$XO6XHtQfoq/WXyz9bjN8w.xAx5.TIBrd1EIqBIjUxzplnw3W3QM.W', 'courier'),
(4, 'Eira', 'eira', 'eira123', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
