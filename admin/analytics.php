<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

$total_sales = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT SUM(total) AS total FROM orders"));

$total_orders = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM orders"));

$completed_orders = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM orders WHERE order_status='Completed'"));

$pending_orders = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM orders WHERE order_status='Pending'"));

$total_products = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM products"));

$low_stock = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM products WHERE stock <= 5"));

$top_products = mysqli_query($conn,
"SELECT products.name, SUM(order_items.quantity) AS sold
 FROM order_items
 JOIN products ON order_items.product_id = products.id
 GROUP BY products.id
 ORDER BY sold DESC
 LIMIT 5");

$recent_sales = mysqli_query($conn,
"SELECT orders.*, users.fullname
 FROM orders
 JOIN users ON orders.customer_id = users.id
 ORDER BY orders.id DESC
 LIMIT 8");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
    <link rel="stylesheet" href="../css/style.css">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="layout">

<?php include "sidebar.php"; ?>

<div class="main-content">

<h1>Reports</h1>
<p>Sales, orders, and inventory summary</p>

<div class="dashboard-cards">

    <div class="dashboard-card blue-card">
        <h3>Total Sales</h3>
        <h2>₱<?php echo $total_sales['total'] ?? 0; ?></h2>
    </div>

    <div class="dashboard-card">
        <h3>Total Orders</h3>
        <h2><?php echo $total_orders['total']; ?></h2>
    </div>

    <div class="dashboard-card">
        <h3>Completed</h3>
        <h2><?php echo $completed_orders['total']; ?></h2>
    </div>

    <div class="dashboard-card">
        <h3>Pending</h3>
        <h2><?php echo $pending_orders['total']; ?></h2>
    </div>

</div>

<div class="dashboard-grid">

    <div class="panel">
        <h2>Recent Sales</h2>

        <table>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
            </tr>

            <?php while($row = mysqli_fetch_assoc($recent_sales)){ ?>
            <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td><?php echo $row['fullname']; ?></td>
                <td>₱<?php echo $row['total']; ?></td>
                <td>
                    <?php
                    $class = "pending";

                    if($row['order_status'] == "Processing"){
                        $class = "processing";
                    }

                    if($row['order_status'] == "Completed"){
                        $class = "completed";
                    }
                    ?>

                    <span class="status <?php echo $class; ?>">
                        <?php echo $row['order_status']; ?>
                    </span>
                </td>
                <td><?php echo $row['date_created']; ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>

    <div class="panel">
        <h2>Inventory Report</h2>

        <div class="report-list">
            <div>
                <span>Total Products</span>
                <strong><?php echo $total_products['total']; ?></strong>
            </div>

            <div>
                <span>Low Stock Items</span>
                <strong><?php echo $low_stock['total']; ?></strong>
            </div>

            <div>
                <span>Completed Orders</span>
                <strong><?php echo $completed_orders['total']; ?></strong>
            </div>

            <div>
                <span>Pending Orders</span>
                <strong><?php echo $pending_orders['total']; ?></strong>
            </div>
        </div>
    </div>

</div>

<div class="panel">
    <h2>Top Selling Products</h2>

    <table>
        <tr>
            <th>Product</th>
            <th>Total Sold</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($top_products)){ ?>
        <tr>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['sold']; ?></td>
        </tr>
        <?php } ?>
    </table>
</div>

</div>

</div>

</body>
</html>