-- Made To Fade Complete Clean SQL
-- Safe for Wasmer/Adminer and phpMyAdmin
-- This script drops and recreates the project tables.

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `product_reviews`;
DROP TABLE IF EXISTS `reorder_requests`;
DROP TABLE IF EXISTS `user_addresses`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `fullname` VARCHAR(100) DEFAULT NULL,
    `username` VARCHAR(50) DEFAULT NULL,
    `password` VARCHAR(255) DEFAULT NULL,
    `role` VARCHAR(20) DEFAULT NULL,
    `account_status` VARCHAR(20) NOT NULL DEFAULT 'Active',
    `last_login` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `products` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) DEFAULT NULL,
    `price` DECIMAL(10,2) DEFAULT NULL,
    `stock` INT(11) DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `category` VARCHAR(50) DEFAULT NULL,
    `sku` VARCHAR(100) DEFAULT NULL,
    `color` VARCHAR(50) DEFAULT NULL,
    `size` VARCHAR(50) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cart` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `customer_id` INT(11) DEFAULT NULL,
    `product_id` INT(11) DEFAULT NULL,
    `quantity` INT(11) DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `customer_id` (`customer_id`),
    KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `orders` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `customer_id` INT(11) DEFAULT NULL,
    `courier_id` INT(11) DEFAULT NULL,
    `total` DECIMAL(10,2) DEFAULT NULL,
    `order_status` VARCHAR(50) DEFAULT NULL,
    `delivery_status` VARCHAR(50) DEFAULT NULL,
    `payment_method` VARCHAR(50) DEFAULT NULL,
    `payment_status` VARCHAR(50) DEFAULT 'Pending',
    `stock_deducted` VARCHAR(10) DEFAULT 'No',
    `proof_image` VARCHAR(255) DEFAULT NULL,
    `payment_proof` VARCHAR(255) DEFAULT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `contact_number` VARCHAR(20) DEFAULT NULL,
    `date_created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `payment_screenshot` VARCHAR(255) DEFAULT NULL,
    `payment_reference` VARCHAR(100) DEFAULT NULL,
    `payment_reject_reason` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `customer_id` (`customer_id`),
    KEY `courier_id` (`courier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `order_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) DEFAULT NULL,
    `product_id` INT(11) DEFAULT NULL,
    `quantity` INT(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`),
    KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user_addresses` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `address_line1` VARCHAR(255) NOT NULL,
    `address_line2` VARCHAR(255) DEFAULT NULL,
    `city` VARCHAR(100) NOT NULL,
    `province_region` VARCHAR(100) NOT NULL,
    `postal_code` VARCHAR(20) NOT NULL,
    `country` VARCHAR(100) NOT NULL DEFAULT 'Philippines',
    `address` TEXT NOT NULL,
    `contact_number` VARCHAR(20) NOT NULL,
    `is_default` VARCHAR(5) NOT NULL DEFAULT 'No',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `reorder_requests` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL,
    `supplier_name` VARCHAR(100) NOT NULL,
    `supplier_email` VARCHAR(100) NOT NULL,
    `reorder_amount` INT(11) NOT NULL DEFAULT 1,
    `message` TEXT NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
    `stock_added` VARCHAR(5) NOT NULL DEFAULT 'No',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `product_reviews` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL,
    `customer_id` INT(11) NOT NULL,
    `order_id` INT(11) NOT NULL,
    `rating` INT(1) NOT NULL,
    `review_text` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_order_product_review` (`order_id`, `product_id`, `customer_id`),
    KEY `product_id` (`product_id`),
    KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`, `account_status`, `last_login`) VALUES
(1, 'Admin Account', 'admin', '$2y$12$wpRJXwpY3hnPp1.Vtm/Sb.kLHhMw2.rnZkCiLURPdnEDKyPm/bJoW', 'admin', 'Active', NOW()),
(2, 'Customer Account', 'customer', '$2y$12$xvdSB8QdnTxghv7Mk/LalOGK21/sEP5UxU/9xRAx.F4mLusPJTfQq', 'customer', 'Active', NOW()),
(3, 'Courier Account', 'courier', '$2y$12$8/MP86gyR7A/abMzPeMdeO8mUBxnxINoMJ9oqg3qTQ3FLPbSTF3VW', 'courier', 'Active', NOW()),
(4, 'Eira', 'eira', '$2y$12$xG6cD4cZv4300WNLK0DTHucruiViBKYKOLIrN1ygITQlZlAbScd4S', 'customer', 'Active', NULL);

INSERT INTO `products` (`id`, `name`, `price`, `stock`, `image`, `category`, `sku`, `color`, `size`) VALUES
(1, 'RED EAST Warriors', 499.00, 17, '20f79e78-8c6e-43ad-a9fa-a6d71a5cd2c0.jpg', 'T-Shirt', 'MTF-T-SHIRT-WHITE-L-5709', 'White', 'L'),
(2, 'RED EAST Warriors', 599.00, 15, '456625ac-43de-4e92-9bdc-deaee3ca48e7.jpg', 'T-Shirt', 'MTF-T-SHIRT-RED-L-5938', 'Red', 'L'),
(3, 'RED EAST Warriors', 699.00, 5, 'edc5812f-2b7e-43be-a920-384df9dfdd20.jpg', 'T-Shirt', 'MTF-T-SHIRT-BLACK-L-4724', 'Black', 'L');

INSERT INTO `user_addresses` (`id`, `user_id`, `full_name`, `address_line1`, `address_line2`, `city`, `province_region`, `postal_code`, `country`, `address`, `contact_number`, `is_default`) VALUES
(1, 2, 'Andrei', 'Rosario', '', 'Pasig City', 'Metro Manila', '1609', 'Philippines', 'Rosario, Pasig City, Metro Manila, 1609, Philippines', '0912 123 1234', 'Yes');

-- Optional sample orders for testing admin/courier pages.
INSERT INTO `orders` (`id`, `customer_id`, `courier_id`, `total`, `order_status`, `delivery_status`, `payment_method`, `payment_status`, `stock_deducted`, `proof_image`, `payment_proof`, `address`, `contact_number`, `payment_screenshot`, `payment_reference`, `payment_reject_reason`) VALUES
(1, 2, 3, 499.00, 'Processing', 'Out for Delivery', 'Cash on Delivery', 'Paid', 'Yes', '', NULL, 'Rosario, Pasig City, Metro Manila, 1609, Philippines', '0912 123 1234', '', '', ''),
(2, 2, NULL, 499.00, 'Pending', 'Preparing', 'Cash on Delivery', 'To Collect', 'No', '', NULL, 'Rosario, Pasig City, Metro Manila, 1609, Philippines', '0912 123 1234', '', '', ''),
(3, 2, 3, 599.00, 'Processing', 'Ready for Pickup', 'GCash', 'Pending Verification', 'No', '', NULL, 'Rosario, Pasig City, Metro Manila, 1609, Philippines', '0912 123 1234', '', '', '');

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`) VALUES
(1, 1, 1, 1),
(2, 2, 1, 1),
(3, 3, 2, 1);

COMMIT;
