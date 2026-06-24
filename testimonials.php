<?php
session_start();
include "includes/config.php";

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS product_reviews (
        id INT(11) NOT NULL AUTO_INCREMENT,
        product_id INT(11) NOT NULL,
        customer_id INT(11) NOT NULL,
        order_id INT(11) NOT NULL,
        rating INT(1) NOT NULL,
        review_text TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_order_product_review (order_id, product_id, customer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
);

$reviews = mysqli_query(
    $conn,
    "SELECT product_reviews.*, users.fullname, products.name AS product_name, products.image
     FROM product_reviews
     JOIN users ON product_reviews.customer_id = users.id
     JOIN products ON product_reviews.product_id = products.id
     ORDER BY product_reviews.created_at DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buyer Reviews | Made To Fade</title>
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

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == "customer") { ?>
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
        <h1>Buyer Reviews</h1>
        <p>Real feedback from customers who purchased Made To Fade products.</p>
    </div>

    <div class="section page-content">
        <?php if ($reviews && mysqli_num_rows($reviews) > 0) { ?>
            <div class="review-list-grid">
                <?php while ($review = mysqli_fetch_assoc($reviews)) { ?>
                    <div class="panel review-card real-review-card">
                        <div class="review-product-row">
                            <img src="images/<?php echo htmlspecialchars($review['image']); ?>" alt="<?php echo htmlspecialchars($review['product_name']); ?>">

                            <div>
                                <h3><?php echo htmlspecialchars($review['product_name']); ?></h3>
                                <small>Verified Buyer · Order #<?php echo $review['order_id']; ?></small>
                            </div>
                        </div>

                        <div class="review-stars">
                            <?php for ($i = 1; $i <= 5; $i++) { ?>
                                <i class="fa-solid fa-star <?php echo $i <= $review['rating'] ? 'star-filled' : 'star-muted'; ?>"></i>
                            <?php } ?>
                        </div>

                        <p>“<?php echo htmlspecialchars($review['review_text']); ?>”</p>
                        <strong>- <?php echo htmlspecialchars($review['fullname']); ?></strong>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="panel empty-review-panel">
                <h2>No Buyer Reviews Yet</h2>
                <p>
                    Reviews will appear here after a customer receives a paid order and submits
                    a review from their order details page.
                </p>
                <a href="index.php#shop-by-category" class="btn">Shop Products</a>
            </div>
        <?php } ?>
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
