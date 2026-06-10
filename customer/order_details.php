<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "customer"){
    header("Location: ../login.php");
    exit();
}

$order_id = $_GET['id'];
$customer_id = $_SESSION['user_id'];

$order = mysqli_query($conn,
"SELECT * FROM orders
 WHERE id='$order_id'
 AND customer_id='$customer_id'");

$order_data = mysqli_fetch_assoc($order);

$items = mysqli_query($conn,
"SELECT order_items.*, products.name, products.price, products.image
 FROM order_items
 JOIN products ON order_items.product_id = products.id
 WHERE order_items.order_id='$order_id'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>

<div class="navbar">
    <div class="logo-area">
        <img src="../images/logo.png" alt="Logo">
    </div>

    <div>
        <a href="../index.php">Shop</a>
        <a href="home.php">Dashboard</a>
        <a href="cart.php">Cart</a>
        <a href="orders.php">My Orders</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="admin-container">

<h1>Order Details</h1>

<a href="orders.php" class="btn">Back</a>

<br><br>

<h3>Order #<?php echo $order_data['id']; ?></h3>

<p>Payment Method: <?php echo $order_data['payment_method']; ?></p>
<p>Payment Status: <?php echo $order_data['payment_status']; ?></p>
<p>Order Status: <?php echo $order_data['order_status']; ?></p>
<p>Delivery Status: <?php echo $order_data['delivery_status']; ?></p>
<p>Address: <?php echo $order_data['address']; ?></p>
<p>Contact: <?php echo $order_data['contact_number']; ?></p>
<p>Date: <?php echo $order_data['date_created']; ?></p>

<?php if($order_data['proof_image'] != ""){ ?>
    <h3>Proof of Delivery</h3>
    <a href="../uploads/proofs/<?php echo $order_data['proof_image']; ?>" target="_blank">
        <img src="../uploads/proofs/<?php echo $order_data['proof_image']; ?>" class="proof-img">
    </a>
<?php } ?>

<br><br>

<table>
<tr>
    <th>Image</th>
    <th>Product</th>
    <th>Price</th>
    <th>Quantity</th>
    <th>Subtotal</th>
</tr>

<?php while($row = mysqli_fetch_assoc($items)){ 
    $subtotal = $row['price'] * $row['quantity'];
?>
<tr>
    <td><img src="../images/<?php echo $row['image']; ?>"></td>
    <td><?php echo $row['name']; ?></td>
    <td>₱<?php echo $row['price']; ?></td>
    <td><?php echo $row['quantity']; ?></td>
    <td>₱<?php echo $subtotal; ?></td>
</tr>
<?php } ?>

</table>

<h2>Total: ₱<?php echo $order_data['total']; ?></h2>

</div>

</body>
</html>