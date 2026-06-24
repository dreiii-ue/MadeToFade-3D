<?php
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>FAQ | Made To Fade</title>
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
            <h1>Frequently Asked Questions</h1>
            <p>Quick answers about ordering, payment, and delivery.</p>
        </div>

        <div class="section page-content">
            <div class="faq-list">
                <div class="panel faq-item">
                    <h3>How do I order?</h3>
                    <p>Create an account, choose a product, add it to your cart, then proceed to checkout.</p>
                </div>

                <div class="panel faq-item">
                    <h3>What payment methods are available?</h3>
                    <p>We accept Cash on Delivery, GCash, Maya, and Bank Transfer.</p>
                </div>

                <div class="panel faq-item">
                    <h3>Can I save multiple delivery addresses?</h3>
                    <p>Yes. Customers can save delivery addresses and choose one during checkout.</p>
                </div>

                <div class="panel faq-item">
                    <h3>Why was my payment rejected?</h3>
                    <p>Payment may be rejected if proof is unclear, invalid, or the reference number is incorrect. Check your order details for the rejection reason.</p>
                </div>

                <div class="panel faq-item">
                    <h3>Who do I contact for account issues?</h3>
                    <p>Email support@madetofade.xyz or contact 0912 123 1234.</p>
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
