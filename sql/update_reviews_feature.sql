-- Add real buyer product reviews feature
-- Run this on existing databases if you do not want to reset your tables.

CREATE TABLE IF NOT EXISTS `product_reviews` (
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
