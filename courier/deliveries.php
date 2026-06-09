<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "courier"){
    header("Location: ../login.php");
    exit();
}

if(isset($_POST['update'])){

    $order_id = $_POST['order_id'];
    $delivery_status = $_POST['delivery_status'];

    mysqli_query($conn,
    "UPDATE orders
     SET delivery_status='$delivery_status'
     WHERE id='$order_id'");

    if($delivery_status == "Delivered"){

        mysqli_query($conn,
        "UPDATE orders
         SET order_status='Completed'
         WHERE id='$order_id'");
    }

    header("Location: deliveries.php");
    exit();
}

$courier_id = $_SESSION['user_id'];

$result = mysqli_query($conn,
"SELECT orders.*, users.fullname
 FROM orders
 JOIN users ON orders.customer_id = users.id
 WHERE orders.courier_id='$courier_id'
 ORDER BY orders.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Courier Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="navbar">
    <h2>MADE TO FADE COURIER</h2>

    <div>
        <a href="deliveries.php">Deliveries</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="admin-container">

<h1>My Deliveries</h1>

<p>Welcome, <?php echo $_SESSION['fullname']; ?></p>

<table>

<tr>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Total</th>
    <th>Payment Method</th>
    <th>Payment Status</th>
    <th>Address</th>
    <th>Contact</th>

    <th>Status</th>
    <th>Update</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

    <td>#<?php echo $row['id']; ?></td>

    <td><?php echo $row['fullname']; ?></td>

    <td>₱<?php echo $row['total']; ?></td>

    <td><?php echo $row['payment_method']; ?></td>
    <td>
        <span class="status <?php echo $row['payment_status'] == 'Paid' ? 'completed' : 'pending'; ?>">
            <?php echo $row['payment_status']; ?>
        </span>
    </td>

    <td><?php echo $row['address']; ?></td>
    <td><?php echo $row['contact_number']; ?></td>

    <td><?php echo $row['delivery_status']; ?></td>

    <td>

        <form method="POST">

            <input type="hidden"
                   name="order_id"
                   value="<?php echo $row['id']; ?>">

            <select name="delivery_status">

                <option value="Ready for Pickup">
                    Ready for Pickup
                </option>

                <option value="Picked Up">
                    Picked Up
                </option>

                <option value="Out for Delivery">
                    Out for Delivery
                </option>

                <option value="Delivered">
                    Delivered
                </option>

            </select>

            <button type="submit"
                    name="update"
                    class="btn">
                Update
            </button>

        </form>

    </td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>