<?php
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Contact | Made To Fade</title>
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
            <h1>Contact Us</h1>
            <p>Reach us for order, payment, and account concerns.</p>
        </div>

        <div class="section page-content">
            <div class="contact-grid">
                <div class="panel">
                    <h2>Contact Information</h2>
                    <p><strong>Email:</strong> support@madetofade.xyz</p>
                    <p><strong>Phone:</strong> 0912 123 1234</p>
                    <p><strong>Location:</strong> Metro Manila, Philippines</p>
                    <p>For deactivated accounts, payment issues, or delivery concerns, please contact the administrator using the details above.</p>
                </div>

                <div class="panel">
                    <h2>Send a Message</h2>
                    <form class="profile-form" method="POST">
                        <label>Full Name</label>
                        <input type="text" name="name" placeholder="Your name" required>

                        <label>Email</label>
                        <input type="email" name="email" placeholder="Your email" required>

                        <label>Message</label>
                        <textarea name="message" placeholder="Write your message here" required></textarea>

                        <button type="submit" class="btn">Submit Message</button>
                    </form>
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
