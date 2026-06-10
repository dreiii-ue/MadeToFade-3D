<?php
session_start();
include "includes/config.php";

if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = mysqli_query($conn,
    "SELECT * FROM users 
     WHERE username='$username' 
     AND password='$password'");

    if(mysqli_num_rows($result) > 0){

        $user = mysqli_fetch_assoc($result);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];

        if($user['role'] == "admin"){
            header("Location: admin/dashboard.php");
        }
        elseif($user['role'] == "courier"){
            header("Location: courier/deliveries.php");
        }
        else{
            header("Location: customer/home.php");
        }

        exit();
    }
    else{
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body class="bg-image">

<div class="navbar">
    <div class="logo-area">
        <img src="images/logo.png" alt="Logo">
    </div>

    <div>
        <a href="index.php">Home</a>
        <a href="register.php">Register</a>
    </div>
</div>

<div class="form-container">

    <h1>Login</h1>

    <?php if(isset($error)){ ?>
        <p><?php echo $error; ?></p>
    <?php } ?>

    <form method="POST">

        <input type="text" name="username" placeholder="Username" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="login">Login</button>

    </form>

    <p>No account yet? <a href="register.php" class="underline">Register</a></p>

</div>

</body>
</html>