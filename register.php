<?php
include "includes/config.php";

if(isset($_POST['register'])){

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $check = mysqli_query($conn,
    "SELECT * FROM users WHERE username='$username'");

    if(mysqli_num_rows($check) > 0){
        $error = "Username already exists.";
    }
    else{
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        mysqli_query($conn,
        "INSERT INTO users(fullname, username, password, role)
         VALUES('$fullname', '$username', '$hashed_password', 'customer')");

        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body class="bg-image">

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

    <?php if(isset($error)){ ?>
        <p><?php echo $error; ?></p>
    <?php } ?>

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