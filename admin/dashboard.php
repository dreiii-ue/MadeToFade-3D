<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

$sales = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT SUM(total) AS total FROM orders"));

$orders = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM orders"));

$products = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM products"));

$users = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM users"));

$low_stock = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM products WHERE stock <= 5"));

$recent_orders = mysqli_query($conn,
"SELECT orders.*, users.fullname
 FROM orders
 JOIN users ON orders.customer_id = users.id
 ORDER BY orders.id DESC
 LIMIT 5");

$low_products = mysqli_query($conn,
"SELECT * FROM products
 WHERE stock <= 5
 LIMIT 4");
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Admin Dashboard</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" type="text/css" href="../css/style.css">
    </head>
    <body>
        <div class="layout">
            <?php include "sidebar.php"; ?>
            <div class="main-content">
                <div class="topbar">
                    <div class="topbar-left">
                        <i class="fa-solid fa-bars" onclick="toggleSidebar()">
                        </i>
                    </div>
                    <div class="topbar-right">
                        <div class="topbar-item">
                            <i class="fa-regular fa-calendar">
                            </i>
                            <span><?php echo date("M d, Y"); ?></span>
                        </div>
                        <div class="divider">
                        </div>
                        <div class="topbar-item">
                            <i class="fa-regular fa-clock">
                            </i>
                            <span id="clock">
                            </span>
                        </div>
                    </div>
                </div>
                <h1>Dashboard</h1>
                <p>Sales and Inventory Management</p>
                <div class="dashboard-cards">
                    <div class="dashboard-card blue-card">
                        <h3>Total Revenue</h3>
                        <h2>₱<?php echo $sales['total'] ?? 0; ?></h2>
                        <p>All completed sales</p>
                    </div>
                    <div class="dashboard-card">
                        <h3>Total Orders</h3>
                        <h2><?php echo $orders['total']; ?></h2>
                        <p>All customer orders</p>
                    </div>
                    <div class="dashboard-card">
                        <h3>Total Products</h3>
                        <h2><?php echo $products['total']; ?></h2>
                        <p>Inventory items</p>
                    </div>
                    <div class="dashboard-card">
                        <h3>Low Stock</h3>
                        <h2><?php echo $low_stock['total']; ?></h2>
                        <p>Products need restock</p>
                    </div>
                </div>
                <div class="dashboard-grid">
                    <div class="panel">
                        <h2>Recent Orders</h2>
                        <table>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Order Status</th>
                                <th>Delivery</th>
                            </tr>
                            <?php while($row = mysqli_fetch_assoc($recent_orders)){ ?>
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
                                <td>
<?php

                        $delivery_class = "preparing";

                        if($row['delivery_status'] == "Ready for Pickup"){
                            $delivery_class = "pickup";
                        }

                        if($row['delivery_status'] == "Picked Up"){
                            $delivery_class = "pickup";
                        }

                        if($row['delivery_status'] == "Out for Delivery"){
                            $delivery_class = "delivery";
                        }

                        if($row['delivery_status'] == "Delivered"){
                            $delivery_class = "delivered";
                        }

                        ?>
                                    <span class="status <?php echo $delivery_class; ?>">
                                    <?php echo $row['delivery_status']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php } ?>
                        </table>
                    </div>
                    <div class="panel low-stock-panel">
                        <div class="panel-title-row">
                            <div>
                                <h2>Low Stock Items</h2>
                                <p>Products with 5 stocks or below.</p>
                            </div>
                            <a href="reorders.php" class="btn small-btn">View Reorders</a>
                        </div>

                        <div class="low-stock-list">
                            <?php if (mysqli_num_rows($low_products) > 0) { ?>
                                <?php while ($row = mysqli_fetch_assoc($low_products)) { ?>
                                    <div class="low-stock-row">
                                        <img
                                            src="../images/<?php echo $row['image']; ?>"
                                            alt="<?php echo htmlspecialchars($row['name']); ?>"
                                        >

                                        <div class="low-stock-info">
                                            <strong><?php echo $row['name']; ?></strong>
                                            <span><?php echo $row['category']; ?> • <?php echo $row['color']; ?> / <?php echo $row['size']; ?></span>
                                        </div>

                                        <div class="low-stock-count">
                                            <?php echo $row['stock']; ?> left
                                        </div>

                                        <a
                                            href="reorders.php?product_id=<?php echo $row['id']; ?>"
                                            class="btn small-btn"
                                        >
                                            Reorder
                                        </a>
                                    </div>
                                <?php } ?>
                            <?php } else { ?>
                                <p>No low stock items.</p>
                            <?php } ?>
                        </div>
                    </div>
                <div class="panel">
                    <h2>Quick Actions</h2>
                    <div class="quick-actions">
                        <a href="products.php" class="action-card">
                        <div class="action-icon">
                            <i class="fa-solid fa-box">
                            </i>
                        </div>
                        <h3>Products</h3>
                        <p>Manage inventory items</p>
                        </a>
                        <a href="orders.php" class="action-card">
                        <div class="action-icon">
                            <i class="fa-solid fa-clipboard-list">
                            </i>
                        </div>
                        <h3>Orders</h3>
                        <p>Assign courier and manage orders</p>
                        </a>
                        <a href="users.php" class="action-card">
                        <div class="action-icon">
                            <i class="fa-solid fa-users">
                            </i>
                        </div>
                        <h3>Users</h3>
                        <p>Manage user accounts</p>
                        </a>
                        <a href="analytics.php" class="action-card">
                        <div class="action-icon">
                            <i class="fa-solid fa-chart-line">
                            </i>
                        </div>
                        <h3>Analytics</h3>
                        <p>View sales reports</p>
                        </a>
                        <a href="reorders.php" class="action-card">
                        <div class="action-icon">
                            <i class="fa-solid fa-truck-ramp-box">
                            </i>
                        </div>
                        <h3>Reorders</h3>
                        <p>Request supplier restock</p>
                        </a>
                    </div>
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
