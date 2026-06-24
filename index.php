<?php
session_start();
include "includes/config.php";

$popular_products = mysqli_query(
    $conn,
    "SELECT products.*, COALESCE(SUM(order_items.quantity), 0) AS total_sold
     FROM products
     LEFT JOIN order_items ON products.id = order_items.product_id
     WHERE products.stock > 0
     GROUP BY products.id
     ORDER BY total_sold DESC, products.id DESC
     LIMIT 4"
);

$categories = mysqli_query(
    $conn,
    "SELECT DISTINCT category
     FROM products
     WHERE stock > 0
     AND category IS NOT NULL
     AND category != ''
     ORDER BY category ASC"
);

$category_links = [];

if ($categories) {
    while ($category = mysqli_fetch_assoc($categories)) {
        $category_links[] = $category['category'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Made To Fade</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/png" href="/images/logo.png">
</head>
<body>

<div class="navbar">
    <div class="logo-area">
        <img src="images/logo.png" alt="Made To Fade Logo">
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

<div class="hero home-hero">
    <span class="hero-label">Minimal Streetwear</span>
    <h1>MADE TO FADE</h1>
    <p>Clean everyday pieces built for comfort, style, and easy wear.</p>
    <a href="#popular-products" class="btn hero-btn">Shop Now</a>
</div>

<?php if (count($category_links) > 0) { ?>
    <div class="category-scroll-bar">
        <?php foreach ($category_links as $category_name) { ?>
            <?php $category_id = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $category_name)); ?>
            <a href="#category-<?php echo htmlspecialchars($category_id); ?>">
                <?php echo htmlspecialchars($category_name); ?>
            </a>
        <?php } ?>
    </div>
<?php } ?>

<div class="section" id="popular-products">
    <div class="section-title-row">
        <div>
            <h2>Popular Products</h2>
            <p>Most ordered and featured items from Made To Fade.</p>
        </div>
    </div>

    <div class="products product-grid-enhanced">
        <?php if ($popular_products && mysqli_num_rows($popular_products) > 0) { ?>
            <?php while ($row = mysqli_fetch_assoc($popular_products)) { ?>
                <div class="card product-card">
                    <div class="product-image-wrap">
                        <img
                            src="images/<?php echo htmlspecialchars($row['image']); ?>"
                            alt="<?php echo htmlspecialchars($row['name']); ?>"
                        >

                        <?php if ($row['total_sold'] > 0) { ?>
                            <span class="product-badge">
                                <?php echo $row['total_sold']; ?> sold
                            </span>
                        <?php } else { ?>
                            <span class="product-badge">New</span>
                        <?php } ?>
                    </div>

                    <div class="product-card-body">
                        <p class="product-category">
                            <?php echo htmlspecialchars($row['category']); ?>
                        </p>

                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>

                        <p class="product-variant">
                            <?php echo htmlspecialchars($row['color']); ?> / <?php echo htmlspecialchars($row['size']); ?>
                        </p>

                        <div class="product-card-footer">
                            <strong>₱<?php echo number_format($row['price'], 2); ?></strong>
                            <span><?php echo $row['stock']; ?> in stock</span>
                        </div>

                        <a href="product.php?id=<?php echo $row['id']; ?>" class="btn">
                            View Product
                        </a>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="panel form-full">
                <p>No available products right now.</p>
            </div>
        <?php } ?>
    </div>
</div>

<div class="section category-shop-section" id="shop-by-category">
    <div class="section-title-row">
        <div>
            <h2>Shop by Category</h2>
            <p>Choose a category above or browse all available products below.</p>
        </div>
    </div>

    <?php if (count($category_links) > 0) { ?>
        <div class="category-product-list">
            <?php foreach ($category_links as $category_name) { ?>
                <?php
                $safe_category = mysqli_real_escape_string($conn, $category_name);
                $category_id = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $category_name));

                $category_products = mysqli_query(
                    $conn,
                    "SELECT *
                     FROM products
                     WHERE stock > 0
                     AND category = '$safe_category'
                     ORDER BY id DESC"
                );
                ?>

                <div class="category-block" id="category-<?php echo htmlspecialchars($category_id); ?>">
                    <div class="category-header">
                        <div>
                            <h3><?php echo htmlspecialchars($category_name); ?></h3>
                            <p>Available <?php echo htmlspecialchars(strtolower($category_name)); ?> items.</p>
                        </div>

                        <span class="category-count">
                            <?php echo mysqli_num_rows($category_products); ?> items
                        </span>
                    </div>

                    <div class="products category-products-grid">
                        <?php while ($row = mysqli_fetch_assoc($category_products)) { ?>
                            <div class="card product-card compact-product-card">
                                <div class="product-image-wrap">
                                    <img
                                        src="images/<?php echo htmlspecialchars($row['image']); ?>"
                                        alt="<?php echo htmlspecialchars($row['name']); ?>"
                                    >
                                </div>

                                <div class="product-card-body">
                                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>

                                    <p class="product-variant">
                                        <?php echo htmlspecialchars($row['color']); ?> / <?php echo htmlspecialchars($row['size']); ?>
                                    </p>

                                    <div class="product-card-footer">
                                        <strong>₱<?php echo number_format($row['price'], 2); ?></strong>
                                        <span><?php echo $row['stock']; ?> left</span>
                                    </div>

                                    <a href="product.php?id=<?php echo $row['id']; ?>" class="btn">
                                        View Product
                                    </a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="panel">
            <p>No product categories available right now.</p>
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
            <a href="#popular-products">Popular Products</a>
            <a href="#shop-by-category">Product Categories</a>
            <a href="testimonials.php">Buyer Reviews</a>
        </div>
    </div>

    <p class="footer-bottom">© 2026 Made To Fade. All rights reserved.</p>
</div>

</body>
</html>
