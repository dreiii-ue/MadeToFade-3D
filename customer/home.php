<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "customer"){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['update_profile'])){

    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if($password == ""){
        mysqli_query($conn,
        "UPDATE users SET
            fullname='$fullname',
            username='$username'
         WHERE id='$user_id'");
    }
    else{
        mysqli_query($conn,
        "UPDATE users SET
            fullname='$fullname',
            username='$username',
            password='$password'
         WHERE id='$user_id'");
    }

    $_SESSION['fullname'] = $fullname;

    header("Location: home.php");
    exit();
}

$user_result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($user_result);

$order_count = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM orders WHERE customer_id='$user_id'"));

$cart_count = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM cart WHERE customer_id='$user_id'"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard</title>
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

<h1>Customer Dashboard</h1>
<p>Welcome, <?php echo $_SESSION['fullname']; ?></p>

<div class="dashboard-cards">

    <div class="dashboard-card">
        <h3>My Orders</h3>
        <h2><?php echo $order_count['total']; ?></h2>
    </div>

    <div class="dashboard-card">
        <h3>Cart Items</h3>
        <h2><?php echo $cart_count['total']; ?></h2>
    </div>

    <div class="dashboard-card">
        <h3>Account Type</h3>
        <h2>Customer</h2>
    </div>

</div>

<div class="panel">

<h2>Edit Profile</h2>

<form method="POST" class="profile-form">

    <label>Full Name</label>
    <input type="text" name="fullname" value="<?php echo $user['fullname']; ?>" required>

    <label>Username</label>
    <input type="text" name="username" value="<?php echo $user['username']; ?>" required>

    <label>New Password</label>
    <input type="password" name="password" placeholder="Leave blank if no change">

    <button type="submit" name="update_profile" class="btn">Save Changes</button>

</form>

</div>

</div>

</body>
</html>