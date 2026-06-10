DROP DATABASE IF EXISTS made_to_fade;

CREATE DATABASE made_to_fade;

USE made_to_fade;

CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
fullname VARCHAR(100),
username VARCHAR(50) UNIQUE,
password VARCHAR(100),
role VARCHAR(20)
);

CREATE TABLE products (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100),
price DECIMAL(10,2),
stock INT,
image VARCHAR(255),
category VARCHAR(50),
sku VARCHAR(100),
color VARCHAR(50),
size VARCHAR(50)
);

CREATE TABLE cart (
id INT AUTO_INCREMENT PRIMARY KEY,
customer_id INT,
product_id INT,
quantity INT DEFAULT 1
);

CREATE TABLE orders (
id INT AUTO_INCREMENT PRIMARY KEY,
customer_id INT,
courier_id INT,
total DECIMAL(10,2),
order_status VARCHAR(50),
delivery_status VARCHAR(50),
payment_method VARCHAR(50),
payment_status VARCHAR(50) DEFAULT 'Pending',
stock_deducted VARCHAR(10) DEFAULT 'No',
address VARCHAR(255),
contact_number VARCHAR(20),
date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
id INT AUTO_INCREMENT PRIMARY KEY,
order_id INT,
product_id INT,
quantity INT
);

INSERT INTO users (fullname, username, password, role) VALUES
('Admin Account', 'admin', 'admin123', 'admin'),
('Customer Account', 'customer', 'customer123', 'customer'),
('Courier Account', 'courier', 'courier123', 'courier');

INSERT INTO products
(name, price, stock, image, category, sku, color, size)
VALUES
('Made To Fade Boxy Crop Tee', 499.00, 20, 'shirt1.jpg', 'Cropped T-Shirt', 'MTF-CROP-BLK-M-1001', 'Black', 'M'),

('Vintage Fade Tee', 599.00, 15, 'shirt2.jpg', 'T-Shirt', 'MTF-TSHIRT-WHT-L-1002', 'White', 'L'),

('Oversized Street Tee', 699.00, 10, 'shirt3.jpg', 'T-Shirt', 'MTF-TSHIRT-GRY-XL-1003', 'Gray', 'XL'),

('Streetwear Jacket', 1299.00, 8, 'jacket1.jpg', 'Jacket', 'MTF-JACKET-BLK-L-1004', 'Black', 'L'),

('Cargo Pants', 899.00, 12, 'pants1.jpg', 'Pants', 'MTF-PANTS-KHK-M-1005', 'Khaki', 'M'),

('Mesh Shorts', 499.00, 20, 'shorts1.jpg', 'Shorts', 'MTF-SHORTS-BLK-M-1006', 'Black', 'M');

INSERT INTO orders
(
customer_id,
courier_id,
total,
order_status,
delivery_status,
payment_method,
payment_status,
stock_deducted,
address,
contact_number
)
VALUES
(
2,
3,
499.00,
'Processing',
'Ready for Pickup',
'Cash on Delivery',
'Pending',
'No',
'Sample Address',
'0912 123 1234'
);

INSERT INTO order_items
(order_id, product_id, quantity)
VALUES
(1, 1, 1);
