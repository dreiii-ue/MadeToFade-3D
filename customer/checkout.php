<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "customer"){
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

if(isset($_POST['checkout'])){

    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    if(!preg_match("/^09[0-9]{2} [0-9]{3} [0-9]{4}$/", $contact_number)){
        die("Invalid phone number format. Use 0912 123 1234.");
    }

    if($payment_method == "Cash on Delivery"){
        $payment_status = "To Collect";
    }
    else{
        $payment_status = "Pending Verification";
    }

    $cart = mysqli_query($conn,
    "SELECT cart.*, products.name, products.price, products.stock
     FROM cart
     JOIN products ON cart.product_id = products.id
     WHERE cart.customer_id='$customer_id'");

    if(mysqli_num_rows($cart) == 0){
        header("Location: cart.php");
        exit();
    }

    $total = 0;
    $stock_error = "";

    while($item = mysqli_fetch_assoc($cart)){
        if($item['quantity'] > $item['stock']){
            $stock_error = $item['name'] . " does not have enough stock.";
        }

        $total += $item['price'] * $item['quantity'];
    }

    if($stock_error != ""){
        die($stock_error);
    }

    mysqli_query($conn,
    "INSERT INTO orders(
        customer_id,
        total,
        order_status,
        delivery_status,
        address,
        contact_number,
        payment_method,
        payment_status,
        stock_deducted,
        proof_image,
        payment_screenshot,
        payment_reference,
        payment_reject_reason
     )
     VALUES(
        '$customer_id',
        '$total',
        'Pending',
        'Preparing',
        '$address',
        '$contact_number',
        '$payment_method',
        '$payment_status',
        'No',
        '',
        '',
        '',
        ''
     )");

    $order_id = mysqli_insert_id($conn);

    $cart_items = mysqli_query($conn,
    "SELECT * FROM cart WHERE customer_id='$customer_id'");

    while($item = mysqli_fetch_assoc($cart_items)){
        mysqli_query($conn,
        "INSERT INTO order_items(order_id, product_id, quantity)
         VALUES('$order_id', '{$item['product_id']}', '{$item['quantity']}')");
    }

    mysqli_query($conn,
    "DELETE FROM cart WHERE customer_id='$customer_id'");

    header("Location: order_details.php?id=$order_id");
    exit();
}

header("Location: cart.php");
exit();
?>