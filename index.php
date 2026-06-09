<?php
session_start();
include "includes/config.php";

$result = mysqli_query($conn, "SELECT * FROM products WHERE stock > 0");
?>

<!-- DREI -->

<!DOCTYPE html>
<html>
<head>
    <title>Made To Fade</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>

<div class="navbar">
    <h2>MADE TO FADE</h2>

    <div>
        <a href="index.php">Home</a>

        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == "customer"){ ?>
            <a href="customer/home.php">Dashboard</a>
            <a href="customer/cart.php">Cart</a>
            <a href="logout.php">Logout</a>
        <?php } else { ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php } ?>
    </div>
</div>

<div class="hero">
    <h1>MADE TO FADE</h1>
    <p>Minimal streetwear made for everyday wear.</p>
</div>

<div class="section">

<h2>Available Products</h2>

<div class="products">

<?php while($row = mysqli_fetch_assoc($result)){ ?>
    <div class="card">

        <img src="images/<?php echo $row['image']; ?>">

        <h3><?php echo $row['name']; ?></h3>

        <p>₱<?php echo $row['price']; ?></p>

        <p>Stock: <?php echo $row['stock']; ?></p>

        <a href="product.php?id=<?php echo $row['id']; ?>" class="btn">
            View Product
        </a>

    </div>
<?php } ?>

</div>

</div>

<div class="footer">
    <p>© 2026 Made To Fade. All rights reserved.</p>
</div>

</body>
</html>