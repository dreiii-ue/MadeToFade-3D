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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$result = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: index.php");
    exit();
}

$reviews = mysqli_query(
    $conn,
    "SELECT product_reviews.*, users.fullname
     FROM product_reviews
     JOIN users ON product_reviews.customer_id = users.id
     WHERE product_reviews.product_id = '$id'
     ORDER BY product_reviews.created_at DESC"
);

$review_summary = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total_reviews, AVG(rating) AS average_rating
     FROM product_reviews
     WHERE product_id = '$id'"
));
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/png" href="/images/logo.png">
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

<div class="product-back">
    <a href="index.php#shop-by-category" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
        Back to Products
    </a>
</div>

<div class="product-view">
    <div>
        <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    </div>

    <div>
        <p class="product-category"><?php echo htmlspecialchars($product['category']); ?></p>
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <h2>₱<?php echo number_format($product['price'], 2); ?></h2>

        <p><strong>SKU:</strong> <?php echo htmlspecialchars($product['sku']); ?></p>
        <p><strong>Color:</strong> <?php echo htmlspecialchars($product['color']); ?></p>
        <p><strong>Size:</strong> <?php echo htmlspecialchars($product['size']); ?></p>
        <p><strong>Available Stock:</strong> <?php echo $product['stock']; ?></p>

        <?php if ($review_summary['total_reviews'] > 0) { ?>
            <p>
                <strong>Rating:</strong>
                <?php echo number_format($review_summary['average_rating'], 1); ?>/5
                from <?php echo $review_summary['total_reviews']; ?> buyer review(s)
            </p>
        <?php } ?>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == "customer") { ?>
            <form method="POST" action="customer/cart.php" class="profile-form">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                <label>Quantity</label>
                <input
                    type="number"
                    name="quantity"
                    value="1"
                    min="1"
                    max="<?php echo $product['stock']; ?>"
                    required
                >

                <button type="submit" name="add_cart" class="btn">
                    Add to Cart
                </button>
            </form>
        <?php } else { ?>
            <a href="login.php" class="btn">
                Login to Purchase
            </a>
        <?php } ?>
    </div>
</div>

<div class="section">
    <div class="section-title-row">
        <div>
            <h2>Buyer Reviews</h2>
            <p>Only customers who purchased this product can leave a review.</p>
        </div>
    </div>

    <?php if ($reviews && mysqli_num_rows($reviews) > 0) { ?>
        <div class="review-list-grid">
            <?php while ($review = mysqli_fetch_assoc($reviews)) { ?>
                <div class="panel review-card real-review-card">
                    <div class="review-stars">
                        <?php for ($i = 1; $i <= 5; $i++) { ?>
                            <i class="fa-solid fa-star <?php echo $i <= $review['rating'] ? 'star-filled' : 'star-muted'; ?>"></i>
                        <?php } ?>
                    </div>

                    <p>“<?php echo htmlspecialchars($review['review_text']); ?>”</p>

                    <strong><?php echo htmlspecialchars($review['fullname']); ?></strong>
                    <small>Verified Buyer · Order #<?php echo $review['order_id']; ?></small>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="panel">
            <p>No reviews yet for this product.</p>
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
