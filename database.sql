CREATE DATABASE made_to_fade;
USE made_to_fade;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100),
    username VARCHAR(50),
    password VARCHAR(100),
    role VARCHAR(20)
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    price DECIMAL(10,2),
    stock INT,
    image VARCHAR(100)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    courier_id INT,
    total DECIMAL(10,2),
    order_status VARCHAR(50),
    delivery_status VARCHAR(50),
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT
);