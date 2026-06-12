<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "customer"){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user_result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($user_result);

if(isset($_POST['update_profile'])){

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);

    $check_username = mysqli_query($conn,
    "SELECT * FROM users 
     WHERE username='$username'
     AND id!='$user_id'");

    if(mysqli_num_rows($check_username) > 0){
        $error = "Username already exists.";
    }
    else{
        mysqli_query($conn,
        "UPDATE users SET
            fullname='$fullname',
            username='$username'
         WHERE id='$user_id'");

        $_SESSION['fullname'] = $fullname;

        if(
            $_POST['old_password'] != "" ||
            $_POST['new_password'] != "" ||
            $_POST['confirm_password'] != ""
        ){
            $old_password = $_POST['old_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if($old_password == "" || $new_password == "" || $confirm_password == ""){
                $error = "Complete all password fields.";
            }
            else{
                $valid_old_password = password_verify($old_password, $user['password']);

                if(!$valid_old_password && $old_password == $user['password']){
                    $valid_old_password = true;
                }

                if(!$valid_old_password){
                    $error = "Current password is incorrect.";
                }
                elseif($new_password != $confirm_password){
                    $error = "New passwords do not match.";
                }
                else{
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    mysqli_query($conn,
                    "UPDATE users
                     SET password='$hashed_password'
                     WHERE id='$user_id'");

                    $success = "Profile and password updated successfully.";
                }
            }
        }
        else{
            $success = "Profile updated successfully.";
        }
    }

    $user_result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
    $user = mysqli_fetch_assoc($user_result);
}

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

<?php if(isset($success)){ ?>
    <p class="success-msg"><?php echo $success; ?></p>
<?php } ?>

<?php if(isset($error)){ ?>
    <p class="error-msg"><?php echo $error; ?></p>
<?php } ?>

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

    <h3>Change Password</h3>

    <label>Current Password</label>
    <input type="password" name="old_password" placeholder="Enter current password">

    <label>New Password</label>
    <input type="password" name="new_password" placeholder="Enter new password">

    <label>Confirm New Password</label>
    <input type="password" name="confirm_password" placeholder="Confirm new password">

    <button type="submit" name="update_profile" class="btn">Save Changes</button>

</form>

</div>

</div>

</body>
</html>