<?php
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>About | Made To Fade</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="icon" type="image/x-icon" href="images/favicon.ico">
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
            <h1>About Made To Fade</h1>
            <p>Streetwear made simple, clean, and wearable.</p>
        </div>

        <div class="section page-content">
            <div class="panel page-panel">
                <h2>Who We Are</h2>
                <p>Made To Fade is a local streetwear brand focused on minimal everyday clothing. Our goal is to create clean pieces that are easy to style, comfortable to wear, and suitable for daily use.</p>
                <p>We focus on simple designs, quality prints, and practical clothing pieces for students, creatives, and streetwear enthusiasts.</p>
            </div>

            <div class="info-grid">
                <div class="panel">
                    <i class="fa-solid fa-shirt page-icon"></i>
                    <h3>Minimal Style</h3>
                    <p>Clean designs that can match different outfits.</p>
                </div>

                <div class="panel">
                    <i class="fa-solid fa-box page-icon"></i>
                    <h3>Reliable Orders</h3>
                    <p>Simple order tracking from checkout to delivery.</p>
                </div>

                <div class="panel">
                    <i class="fa-solid fa-heart page-icon"></i>
                    <h3>Made for Everyday</h3>
                    <p>Comfortable clothing for regular wear.</p>
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
