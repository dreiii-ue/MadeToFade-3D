<?php
include "includes/config.php";

if(isset($_POST['register'])){
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");

    if(mysqli_num_rows($check) > 0) $error = "Username already exists.";
    else{
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users(fullname, username, password, role, account_status, last_login) VALUES('$fullname', '$username', '$hashed_password', 'customer', 'Active', NOW())");
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
        <link rel="icon" type="image/png" href="/images/logo.png">
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
        <main class="auth-main">
            <div class="form-container">
            <h1>Register</h1>
            <?php if(isset($error)){ ?><p><?php echo $error; ?></p><?php } ?>
            <form method="POST">
                <input type="text" name="fullname" placeholder="Full Name" required>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="register">Register</button>
            </form>
            <p>Already have an account? <a href="login.php" class="underline">Login</a>
            </p>
            </div>
        </main>

        <div class="footer site-footer">
            <div class="footer-grid">
                <div>
                    <h3>MADE TO FADE</h3>
                    <p>Minimal streetwear made for everyday wear.</p>
                </div>

                <div>
                    <h4>Information</h4>
                    <a href="about.php">About</a>
                    <a href="services.php">Services</a>
                    <a href="faq.php">FAQ</a>
                    <a href="contact.php">Contact</a>
                </div>

                <div>
                    <h4>Shop</h4>
                    <a href="index.php#popular-products">Popular Products</a>
                    <a href="index.php#shop-by-category">Product Categories</a>
                    <a href="testimonials.php">Buyer Reviews</a>
                </div>
            </div>

            <p class="footer-bottom">© 2026 Made To Fade. All rights reserved.</p>
        </div>
    </body>
</html>
