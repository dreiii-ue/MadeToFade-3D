<?php
include "includes/config.php";

if(isset($_POST['reset'])){

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if($new_password != $confirm_password){
        $error = "Passwords do not match.";
    }
    else{
        $check = mysqli_query($conn,
        "SELECT * FROM users WHERE username='$username'");

        if(mysqli_num_rows($check) > 0){

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            mysqli_query($conn,
            "UPDATE users 
             SET password='$hashed_password'
             WHERE username='$username'");

            $success = "Password updated. You can now login.";
        }
        else{
            $error = "Username not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
</head>
<body class="bg-image">

<div class="navbar">
    <div class="logo-area">
        <img src="images/logo.png" alt="Logo">
    </div>

    <div>
        <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="services.php">Services</a>
                <a href="faq.php">FAQ</a>
                <a href="testimonials.php">Reviews</a>
                <a href="contact.php">Contact</a>
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

    <input type="password" name="confirm_password" placeholder="Confirm Password" required>

    <button type="submit" name="reset">Reset Password</button>

</form>

<p><a href="login.php">Back to Login</a></p>

</div>

</body>
</html>