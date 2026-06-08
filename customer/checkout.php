<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "customer"){
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

if(isset($_POST['checkout'])){

    $address = $_POST['address'];
    $contact_number = $_POST['contact_number'];

    $cart = mysqli_query($conn,
    "SELECT cart.*, products.price
     FROM cart
     JOIN products ON cart.product_id = products.id
     WHERE cart.customer_id='$customer_id'");

    if(mysqli_num_rows($cart) == 0){
        header("Location: cart.php");
        exit();
    }

    $total = 0;

    while($item = mysqli_fetch_assoc($cart)){
        $total += $item['price'] * $item['quantity'];
    }

    mysqli_query($conn,
    "INSERT INTO orders(customer_id, total, order_status, delivery_status, address, contact_number)
     VALUES('$customer_id', '$total', 'Pending', 'Preparing', '$address', '$contact_number')");

    $order_id = mysqli_insert_id($conn);

    $cart_items = mysqli_query($conn,
    "SELECT * FROM cart WHERE customer_id='$customer_id'");

    while($item = mysqli_fetch_assoc($cart_items)){

        mysqli_query($conn,
        "INSERT INTO order_items(order_id, product_id, quantity)
         VALUES('$order_id', '{$item['product_id']}', '{$item['quantity']}')");

        mysqli_query($conn,
        "UPDATE products
         SET stock = stock - {$item['quantity']}
         WHERE id='{$item['product_id']}'");
    }

    mysqli_query($conn,
    "DELETE FROM cart WHERE customer_id='$customer_id'");

    header("Location: orders.php");
    exit();
}

header("Location: cart.php");
exit();
?>