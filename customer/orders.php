<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "customer"){
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

$result = mysqli_query($conn,
"SELECT * FROM orders WHERE customer_id='$customer_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>

<div class="navbar">
    <h2>MADE TO FADE</h2>

    <div>
        <a href="home.php">Shop</a>
        <a href="cart.php">Cart</a>
        <a href="orders.php">My Orders</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="admin-container">

<h1>My Orders</h1>

<table>
<tr>
    <th>Order ID</th>
    <th>Total</th>
    <th>Order Status</th>
    <th>Delivery Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
    <td><?php echo $row['id']; ?></td>
    <td>₱<?php echo $row['total']; ?></td>
    <td><?php echo $row['order_status']; ?></td>
    <td><?php echo $row['delivery_status']; ?></td>
    <td><?php echo $row['date_created']; ?></td>
    <td>
        <a href="order_details.php?id=<?php echo $row['id']; ?>" class="btn">
            View
        </a>
    </td>
</tr>
<?php } ?>

</table>

</div>

</body>
</html>