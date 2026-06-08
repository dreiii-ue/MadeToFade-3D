<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">

    <div class="logo-area">
        <img src="../images/logo.png" alt="Logo">
        <p>Sales and Inventory<br>Management</p>
    </div>

    <a href="dashboard.php" class="menu-item <?php if($current_page == 'dashboard.php') echo 'active'; ?>">
        <i class="fa-solid fa-house"></i>
        Dashboard
    </a>

    <a href="orders.php" class="menu-item <?php if($current_page == 'orders.php' || $current_page == 'order_details.php') echo 'active'; ?>">
        <i class="fa-solid fa-box"></i>
        Order Tracker
    </a>

    <a href="products.php" class="menu-item <?php if($current_page == 'products.php') echo 'active'; ?>">
        <i class="fa-solid fa-clipboard-list"></i>
        Inventory
    </a>

    <a href="analytics.php" class="menu-item <?php if($current_page == 'analytics.php') echo 'active'; ?>">
        <i class="fa-solid fa-chart-column"></i>
        Reports
    </a>

    <a href="users.php" class="menu-item <?php if($current_page == 'users.php') echo 'active'; ?>">
        <i class="fa-solid fa-users"></i>
        Users
    </a>

    <div class="admin-profile">
        <strong>Admin</strong><br>
        <small><?php echo $_SESSION['fullname']; ?></small>
    </div>

    <a href="../logout.php" class="menu-item logout">
        <i class="fa-solid fa-right-from-bracket"></i>
        Logout
    </a>

</div>