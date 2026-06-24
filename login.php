<?php
session_start();
include "includes/config.php";

mysqli_query($conn, "UPDATE users SET account_status='Inactive' WHERE account_status='Active' AND last_login IS NOT NULL AND last_login < DATE_SUB(NOW(), INTERVAL 3 MONTH)");

if(isset($_POST['login'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");

    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);

        if(isset($user['account_status']) && $user['account_status'] == "Inactive"){
            $error = "Your account has been deactivated. Please contact the administrator at support@madetofade.xyz or 0912 123 1234.";
        }
        else{
            $valid_password = password_verify($password, $user['password']);

            if(!$valid_password && $password == $user['password']){
                $valid_password = true;
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                mysqli_query($conn, "UPDATE users SET password='$new_hash' WHERE id='{$user['id']}'");
            }

            if($valid_password){
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];

                mysqli_query($conn, "UPDATE users SET last_login=NOW(), account_status='Active' WHERE id='{$user['id']}'");

                if($user['role'] == "admin") header("Location: admin/dashboard.php");
                elseif($user['role'] == "courier") header("Location: courier/deliveries.php");
                else header("Location: customer/home.php");
                exit();
            }
            else $error = "Invalid username or password";
        }
    }
    else $error = "Invalid username or password";
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
        <main class="auth-main">
            <div class="form-container">
            <h1>Login</h1>
            <?php if(isset($error)){ ?><p><?php echo $error; ?></p><?php } ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
            </form>
            <p>
                <a href="forgot_password.php">Forgot Password?</a>
            </p>

            <p>
                Don't have an account yet?
                <a href="register.php" class="underline">Create an account</a>
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
