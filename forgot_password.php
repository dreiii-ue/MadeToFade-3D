<?php
include "includes/config.php";

if(isset($_POST['reset'])){

    $username = $_POST['username'];
    $new_password = $_POST['new_password'];

    $check = mysqli_query($conn,
    "SELECT * FROM users WHERE username='$username'");

    if(mysqli_num_rows($check) > 0){

        mysqli_query($conn,
        "UPDATE users 
         SET password='$new_password'
         WHERE username='$username'");

        $success = "Password updated. You can now login.";
    }
    else{
        $error = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>

<div class="navbar">
    <h2>MADE TO FADE</h2>

    <div>
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
    </div>
</div>

<div class="form-container">

<h1>Reset Password</h1>

<?php if(isset($success)){ ?>
    <p><?php echo $success; ?></p>
<?php } ?>

<?php if(isset($error)){ ?>
    <p><?php echo $error; ?></p>
<?php } ?>

<form method="POST">

    <input type="text" name="username" placeholder="Enter Username" required>

    <input type="password" name="new_password" placeholder="New Password" required>

    <button type="submit" name="reset">Reset Password</button>

</form>

<p><a href="login.php">Back to Login</a></p>

</div>

</body>
</html>