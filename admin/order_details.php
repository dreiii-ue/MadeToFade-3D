<?php
session_start();
include "../includes/config.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$order_result = mysqli_query(
    $conn,
    "SELECT orders.*, users.fullname
     FROM orders
     JOIN users ON orders.customer_id = users.id
     WHERE orders.id='$order_id'"
);

$order_data = mysqli_fetch_assoc($order_result);

if (!$order_data) {
    header("Location: orders.php");
    exit();
}

$items = mysqli_query(
    $conn,
    "SELECT order_items.*, products.name, products.price, products.image
     FROM order_items
     JOIN products ON order_items.product_id = products.id
     WHERE order_items.order_id='$order_id'"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Order Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>

<div class="layout">

    <?php include "sidebar.php"; ?>

    <div class="main-content">

        <?php include "topbar.php"; ?>

        <div class="admin-page-header">
            <h1>Order Details</h1>
            <p>Review customer order items, payment, and delivery information.</p>
        </div>

        <a href="orders.php" class="btn">Back to Orders</a>

        <br><br>

        <div class="panel">
            <h2>Order #<?php echo $order_data['id']; ?></h2>

            <p><strong>Customer:</strong> <?php echo $order_data['fullname']; ?></p>
            <p><strong>Payment Method:</strong> <?php echo $order_data['payment_method']; ?></p>
            <p><strong>Payment Status:</strong> <?php echo $order_data['payment_status']; ?></p>
            <p><strong>Order Status:</strong> <?php echo $order_data['order_status']; ?></p>
            <p><strong>Delivery Status:</strong> <?php echo $order_data['delivery_status']; ?></p>
            <p><strong>Address:</strong> <?php echo $order_data['address']; ?></p>
            <p><strong>Contact:</strong> <?php echo $order_data['contact_number']; ?></p>
            <p><strong>Date:</strong> <?php echo $order_data['date_created']; ?></p>
        </div>

        <div class="panel">
            <h2>Order Items</h2>

            <div class="table-responsive">
                <table>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>

                    <?php while ($row = mysqli_fetch_assoc($items)) { ?>
                        <?php $subtotal = $row['price'] * $row['quantity']; ?>

                        <tr>
                            <td>
                                <img src="../images/<?php echo $row['image']; ?>" width="60">
                            </td>
                            <td><?php echo $row['name']; ?></td>
                            <td>₱<?php echo $row['price']; ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td>₱<?php echo $subtotal; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <h2>Total: ₱<?php echo $order_data['total']; ?></h2>
        </div>

    </div>
</div>

<script>
function updateClock() {
    const clock = document.getElementById("clock");

    if (!clock) {
        return;
    }

    const now = new Date();

    clock.innerHTML = now.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function toggleSidebar() {
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");

    if (sidebar) {
        sidebar.classList.toggle("hide-sidebar");
    }

    if (mainContent) {
        mainContent.classList.toggle("full-width");
    }
}

updateClock();
setInterval(updateClock, 1000);
</script>

</body>
</html>
