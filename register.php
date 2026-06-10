<?php
include "includes/config.php";

if(isset($_POST['register'])){

    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    mysqli_query($conn,
    "INSERT INTO users(fullname, username, password, role)
     VALUES('$fullname', '$username', '$password', 'customer')");

    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body class="dark-bg">

<div class="navbar">
    <div class="logo-area">
        <img src="images/logo.png" alt="Logo">
    </div>

    <div>
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
    </div>
</div>

<div class="form-container">

    <h1>Register</h1>

    <form method="POST">

        <input type="text" name="fullname" placeholder="Full Name" required>

        <input type="text" name="username" placeholder="Username" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="register">Register</button>

    </form>

    <p>Already have an account? <a href="login.php" class="underline">Login</a></p>

</div>

</body>
</html>