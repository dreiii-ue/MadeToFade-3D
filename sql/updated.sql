-- Adminer 4.8.4 MySQL 8.0.21 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cart` (`id`, `customer_id`, `product_id`, `quantity`) VALUES
(1,	2,	8,	1),
(2,	4,	7,	2);

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1,	'T-Shirt',	'2026-06-24 21:58:47'),
(2,	'Cropped T-Shirt',	'2026-06-24 21:58:47'),
(3,	'Jacket',	'2026-06-24 21:58:48'),
(4,	'Pants',	'2026-06-24 21:58:48'),
(5,	'Shorts',	'2026-06-24 21:58:48'),
(46,	'RED EAST COLLECTIVE',	'2026-06-24 22:02:22');

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`) VALUES
(1,	1,	1,	1),
(2,	2,	1,	1),
(3,	3,	2,	1),
(4,	4,	1,	1),
(5,	5,	1,	1),
(6,	6,	2,	1),
(7,	7,	5,	1),
(8,	8,	8,	1),
(9,	9,	6,	1),
(10,	10,	7,	2);

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `courier_id` int DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `order_status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `delivery_status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `stock_deducted` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'No',
  `proof_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_proof` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_screenshot` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_reference` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_reject_reason` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `courier_id` (`courier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `orders` (`id`, `customer_id`, `courier_id`, `total`, `order_status`, `delivery_status`, `payment_method`, `payment_status`, `stock_deducted`, `proof_image`, `payment_proof`, `address`, `contact_number`, `date_created`, `payment_screenshot`, `payment_reference`, `payment_reject_reason`) VALUES
(1,	2,	3,	499.00,	'Completed',	'Delivered',	'Cash on Delivery',	'Paid',	'Yes',	'delivery_1_1781146477.png',	NULL,	'Rosario, Pasig City, Metro Manila, 1609, Philippines',	'0912 123 1234',	'2026-06-10 14:01:11',	'',	'',	''),
(2,	2,	NULL,	499.00,	'Pending',	'Preparing',	'Cash on Delivery',	'To Collect',	'No',	'',	NULL,	'Rosario, Pasig City, Metro Manila, 1609, Philippines',	'0912 123 1234',	'2026-06-10 16:12:29',	'',	'',	''),
(3,	2,	3,	599.00,	'Processing',	'Ready for Pickup',	'GCash',	'Pending Verification',	'No',	'',	NULL,	'Bridgetowne, Near main gate, Pasig City, Metro Manila, 1604, Philippines',	'0917 555 1212',	'2026-06-10 16:43:44',	'',	'',	''),
(4,	2,	3,	499.00,	'Processing',	'Ready for Pickup',	'GCash',	'Proof Submitted',	'No',	'',	NULL,	'Bridgetowne, Near main gate, Pasig City, Metro Manila, 1604, Philippines',	'0917 555 1212',	'2026-06-10 17:13:56',	'payment_4_1781140513.png',	'2041511124874',	''),
(5,	2,	3,	499.00,	'Processing',	'Out for Delivery',	'Cash on Delivery',	'To Collect',	'Yes',	'',	NULL,	'Rosario, Pasig City, Metro Manila, 1609, Philippines',	'0912 123 1234',	'2026-06-21 16:16:37',	'',	'',	''),
(6,	2,	3,	599.00,	'Processing',	'Out for Delivery',	'Cash on Delivery',	'Paid',	'Yes',	'',	NULL,	'Rosario, Pasig City, Metro Manila, 1609, Philippines',	'0912 123 1234',	'2026-06-21 16:28:56',	'',	'',	''),
(7,	4,	3,	1299.00,	'Completed',	'Delivered',	'GCash',	'Paid',	'Yes',	'delivery_6_1782059524.png',	NULL,	'Aurora Boulevard, Unit 12B, Quezon City, Metro Manila, 1109, Philippines',	'0918 222 3344',	'2026-06-22 09:10:00',	'payment_7_1781147143.png',	'GCASH778899',	''),
(8,	6,	3,	699.00,	'Payment Rejected',	'Preparing',	'GCash',	'Rejected',	'No',	'',	NULL,	'Legarda Street, Manila, Metro Manila, 1008, Philippines',	'0920 333 4455',	'2026-06-22 10:20:00',	'payment_8_1781147517.webp',	'BADREF001',	'Payment screenshot is unclear. Please upload a valid proof of payment.'),
(9,	6,	7,	899.00,	'Processing',	'Picked Up',	'Cash on Delivery',	'To Collect',	'Yes',	'',	NULL,	'Legarda Street, Manila, Metro Manila, 1008, Philippines',	'0920 333 4455',	'2026-06-22 11:30:00',	'',	'',	''),
(10,	4,	NULL,	1198.00,	'Pending',	'Preparing',	'GCash',	'Pending Verification',	'No',	'',	NULL,	'Aurora Boulevard, Unit 12B, Quezon City, Metro Manila, 1109, Philippines',	'0918 222 3344',	'2026-06-22 12:00:00',	'',	'',	'');

DROP TABLE IF EXISTS `product_reviews`;
CREATE TABLE `product_reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `order_id` int NOT NULL,
  `rating` int NOT NULL,
  `review_text` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_order_product_review` (`order_id`,`product_id`,`customer_id`),
  KEY `product_id` (`product_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `product_reviews` (`id`, `product_id`, `customer_id`, `order_id`, `rating`, `review_text`, `created_at`) VALUES
(1,	1,	2,	1,	5,	'Great product, good service, and fast delivery. The shirt fits well and feels comfortable.',	'2026-06-21 19:00:00'),
(2,	5,	4,	7,	5,	'The jacket quality is nice and the delivery proof was uploaded properly. Very satisfied.',	'2026-06-22 12:30:00'),
(3,	2,	2,	6,	4,	'Good shirt and smooth ordering process. I like the simple design.',	'2026-06-22 13:00:00');

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock` int DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sku` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `color` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `size` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` (`id`, `name`, `price`, `stock`, `image`, `category`, `sku`, `color`, `size`) VALUES
(1,	'RED EAST Warriors Tee',	499.00,	17,	'20f79e78-8c6e-43ad-a9fa-a6d71a5cd2c0.jpg',	'RED EAST COLLECTIVE',	'MTF-REDEASTCOLLECTIVE-WHITE-L-6169',	'White',	'L'),
(2,	'RED EAST Warriors Tee',	599.00,	15,	'456625ac-43de-4e92-9bdc-deaee3ca48e7.jpg',	'RED EAST COLLECTIVE',	'MTF-REDEASTCOLLECTIVE-RED-L-3730',	'Red',	'L'),
(3,	'RED EAST Warriors Tee',	699.00,	5,	'edc5812f-2b7e-43be-a920-384df9dfdd20.jpg',	'RED EAST COLLECTIVE',	'MTF-REDEASTCOLLECTIVE-BLACK-L-7156',	'Black',	'L'),
(4,	'Made To Fade Cropped Tee',	549.00,	4,	'1782336300_4779.png',	'Cropped T-Shirt',	'MTF-CROPPEDT-SHIRT-BLACK-M-4602',	'Black',	'M'),
(5,	'Washed Street Jacket',	1299.00,	3,	'1782336562_1868.png',	'Jacket',	'MTF-JACKET-GRAY-L-8717',	'Gray',	'L'),
(6,	'Cargo Utility Pants',	899.00,	8,	'1782336723_3004.png',	'Pants',	'MTF-PANTS-BLACK-M-3405',	'Black',	'M'),
(7,	'Everyday Street Shorts',	599.00,	6,	'1782336046_6982.png',	'Shorts',	'MTF-SHORTS-KHAKI-M-9796',	'Khaki',	'M'),
(8,	'Oversized Graphic Tee',	699.00,	12,	'1782337083_5962.png',	'T-Shirt',	'MTF-T-SHIRT-WHITE-XL-5023',	'White',	'XL'),
(9,	'Minimal Boxy Tee',	499.00,	2,	'1782336938_8735.png',	'T-Shirt',	'MTF-T-SHIRT-BLACK-M-9219',	'Black',	'M'),
(10,	'Classic Denim Jacket',	1499.00,	9,	'1782336367_2723.png',	'Jacket',	'MTF-JACKET-BLUE-L-7832',	'Blue',	'L');

DROP TABLE IF EXISTS `reorder_requests`;
CREATE TABLE `reorder_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `supplier_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `supplier_email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `reorder_amount` int NOT NULL DEFAULT '1',
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `stock_added` varchar(5) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'No',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `reorder_requests` (`id`, `product_id`, `supplier_name`, `supplier_email`, `reorder_amount`, `message`, `status`, `stock_added`, `created_at`, `completed_at`) VALUES
(1,	3,	'Fade Streetwear Supply',	'supplier@fadestreetwear.com',	20,	'Please restock the black RED EAST Warriors Tee. Current stock is low.',	'Pending',	'No',	'2026-06-22 09:00:00',	NULL),
(2,	4,	'Cotton Club PH',	'orders@cottonclub.ph',	25,	'Requesting another batch of cropped tees for next week.',	'Completed',	'Yes',	'2026-06-20 14:10:00',	'2026-06-21 10:30:00'),
(3,	5,	'Urban Supply Co.',	'restock@urbansupply.co',	15,	'Need jackets for upcoming promotion.',	'Pending',	'No',	'2026-06-22 13:20:00',	NULL),
(4,	9,	'Boxy Basics Supplier',	'sales@boxybasics.com',	30,	'Please prepare reorder for Minimal Boxy Tee.',	'Pending',	'No',	'2026-06-22 15:45:00',	NULL);

DROP TABLE IF EXISTS `user_addresses`;
CREATE TABLE `user_addresses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `address_line1` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `address_line2` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `province_region` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `country` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Philippines',
  `address` text COLLATE utf8mb4_general_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `is_default` varchar(5) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'No',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user_addresses` (`id`, `user_id`, `full_name`, `address_line1`, `address_line2`, `city`, `province_region`, `postal_code`, `country`, `address`, `contact_number`, `is_default`, `created_at`) VALUES
(1,	2,	'Andrei Eduarte',	'Rosario',	'',	'Pasig City',	'Metro Manila',	'1609',	'Philippines',	'Rosario, Pasig City, Metro Manila, 1609, Philippines',	'0912 123 1234',	'Yes',	'2026-06-21 16:16:37'),
(3,	4,	'Eira Santos',	'Aurora Boulevard',	'Unit 12B',	'Quezon City',	'Metro Manila',	'1109',	'Philippines',	'Aurora Boulevard, Unit 12B, Quezon City, Metro Manila, 1109, Philippines',	'0918 222 3344',	'Yes',	'2026-06-21 17:00:00'),
(4,	6,	'Maria Lopez',	'Legarda Street',	'',	'Manila',	'Metro Manila',	'1008',	'Philippines',	'Legarda Street, Manila, Metro Manila, 1008, Philippines',	'0920 333 4455',	'Yes',	'2026-06-21 18:00:00');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`, `account_status`, `last_login`, `created_at`) VALUES
(1,	'Admin Account',	'admin',	'$2y$12$ff68r1nwgThCWFdR7CDm8OL11P80V095/FDdn9LU80ejkl4APe7sO',	'admin',	'Active',	'2026-06-24 21:58:34',	'2026-06-21 15:26:05'),
(2,	'Customer Account',	'customer',	'$2y$12$sk4JlTo3IFSrlZkuBP9BMuEBjccAM1/PA.cv0H4G5e0b14zA3hopi',	'customer',	'Active',	'2026-06-24 21:02:07',	'2026-06-21 15:26:05'),
(3,	'Courier Account',	'courier',	'$2y$12$RKvXqZy9jooYXOS/Bw82d.sQJYx0HsDpXSWsBIn.TLY3AVRnortTC',	'courier',	'Active',	'2026-06-22 08:30:15',	'2026-06-21 15:26:05'),
(4,	'Eira Asia',	'eira',	'$2y$12$zlRzy3tIcu2bHEJABoQ9jOa94Qp.41j6AEckCezB4./OHsKkijEva',	'customer',	'Active',	'2026-06-24 21:03:12',	'2026-06-21 15:26:05'),
(5,	'Inactive Demo User',	'inactive',	'inactive123',	'customer',	'Inactive',	NULL,	'2026-01-10 09:00:00'),
(6,	'Maria Lopez',	'maria',	'maria123',	'customer',	'Active',	NULL,	'2026-06-20 10:00:00'),
(7,	'Second Courier',	'courier2',	'courier2123',	'courier',	'Active',	NULL,	'2026-06-20 10:05:00');

-- 2026-06-24 22:09:33