<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "customer"){
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

$active_orders = mysqli_query($conn,
"SELECT * FROM orders
 WHERE customer_id='$customer_id'
 AND NOT (delivery_status='Delivered' AND payment_status='Paid')
 ORDER BY id DESC");

$order_history = mysqli_query($conn,
"SELECT * FROM orders
 WHERE customer_id='$customer_id'
 AND delivery_status='Delivered'
 AND payment_status='Paid'
 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body class="bg-image">

<div class="navbar customer-navbar">
            <div class="logo-area">
                <a href="../index.php" class="nav-logo-link">
                    <img src="../images/logo.png" alt="Made To Fade Logo">
                </a>
            </div>

            <div class="nav-links">
                <a href="../index.php">Shop</a>
                <a href="home.php">Dashboard</a>
                <a href="cart.php">Cart</a>
                <a href="orders.php">My Orders</a>
                <a href="../logout.php">Logout</a>
            </div>
        </div>

<div class="admin-container">

<div class="panel">
<h1>My Orders</h1>
<p>Track active orders and view your completed order history.</p>
</div>

<div class="panel">

<h2>Active Orders</h2>

<table>
<tr>
    <th>Order ID</th>
    <th>Total</th>
    <th>Payment Method</th>
    <th>Payment Status</th>
    <th>Order Status</th>
    <th>Delivery Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>

<?php if(mysqli_num_rows($active_orders) > 0){ ?>
<?php while($row = mysqli_fetch_assoc($active_orders)){ ?>
<tr>
    <td>#<?php echo $row['id']; ?></td>
    <td>₱<?php echo $row['total']; ?></td>
    <td><?php echo $row['payment_method']; ?></td>

    <td>
        <span class="status <?php echo $row['payment_status'] == 'Paid' ? 'completed' : 'pending'; ?>">
            <?php echo $row['payment_status']; ?>
        </span>
    </td>

    <td><?php echo $row['order_status']; ?></td>
    <td><?php echo $row['delivery_status']; ?></td>
    <td><?php echo $row['date_created']; ?></td>

    <td>
        <a href="order_details.php?id=<?php echo $row['id']; ?>" class="btn">View</a>
    </td>
</tr>
<?php } ?>
<?php } else { ?>
<tr>
    <td colspan="8" style="text-align:center;">No active orders.</td>
</tr>
<?php } ?>

</table>

</div>

<div class="panel">

<h2>Order History</h2>

<table>
<tr>
    <th>Order ID</th>
    <th>Total</th>
    <th>Payment Method</th>
    <th>Payment Status</th>
    <th>Delivery Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>

<?php if(mysqli_num_rows($order_history) > 0){ ?>
<?php while($row = mysqli_fetch_assoc($order_history)){ ?>
<tr>
    <td>#<?php echo $row['id']; ?></td>
    <td>₱<?php echo $row['total']; ?></td>
    <td><?php echo $row['payment_method']; ?></td>

    <td>
        <span class="status completed">
            <?php echo $row['payment_status']; ?>
        </span>
    </td>

    <td>
        <span class="status delivered">
            <?php echo $row['delivery_status']; ?>
        </span>
    </td>

    <td><?php echo $row['date_created']; ?></td>

    <td>
        <a href="order_details.php?id=<?php echo $row['id']; ?>" class="btn">View</a>
    </td>
</tr>
<?php } ?>
<?php } else { ?>
<tr>
    <td colspan="7" style="text-align:center;">No completed orders yet.</td>
</tr>
<?php } ?>

</table>

</div>

</div>

</body>
</html>