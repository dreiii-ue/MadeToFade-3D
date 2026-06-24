<?php
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Services | Made To Fade</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </head>
    <body>
        <div class="navbar">
            <div class="logo-area">
                <img src="images/logo.png" alt="Logo">
            </div>

            <div>
                <a href="index.php">Home</a>
                <a href="testimonials.php">Reviews</a>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == "customer"){ ?>
                    <a href="customer/home.php">Dashboard</a>
                    <a href="customer/cart.php">Cart</a>
                    <a href="customer/orders.php">My Orders</a>
                    <a href="logout.php">Logout</a>
                <?php } else { ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php } ?>
            </div>
        </div>

        <div class="page-hero-small">
            <h1>Our Services</h1>
            <p>What Made To Fade offers to customers.</p>
        </div>

        <div class="section page-content">
            <div class="info-grid">
                <div class="panel">
                    <i class="fa-solid fa-store page-icon"></i>
                    <h3>Online Shopping</h3>
                    <p>Browse available products, check stock, and place orders online.</p>
                </div>

                <div class="panel">
                    <i class="fa-solid fa-truck page-icon"></i>
                    <h3>Delivery Tracking</h3>
                    <p>Track your order status from preparation to delivery.</p>
                </div>

                <div class="panel">
                    <i class="fa-solid fa-money-bill page-icon"></i>
                    <h3>Flexible Payment</h3>
                    <p>Choose Cash on Delivery, GCash, Maya, or Bank Transfer.</p>
                </div>

                <div class="panel">
                    <i class="fa-solid fa-headset page-icon"></i>
                    <h3>Customer Support</h3>
                    <p>Contact the administrator for order and account concerns.</p>
                </div>
            </div>
        </div>

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
