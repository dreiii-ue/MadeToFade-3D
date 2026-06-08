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
    image VARCHAR(255)
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
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    address VARCHAR(255),
    contact_number VARCHAR(20)
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

INSERT INTO products (name, price, stock, image) VALUES
('Made To Fade Boxy Crop Tee', 499.00, 20, 'shirt1.jpg'),
('Vintage Fade Tee', 599.00, 15, 'shirt2.jpg'),
('Oversized Street Tee', 699.00, 10, 'shirt3.jpg');

INSERT INTO orders 
(customer_id, courier_id, total, order_status, delivery_status, address, contact_number)
VALUES
(2, 3, 499.00, 'Processing', 'Ready for Pickup', 'Sample Address', '09123456789');

INSERT INTO order_items (order_id, product_id, quantity)
VALUES
(1, 1, 1);