<?php
session_start();
include "includes/config.php";

$id = $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
$product = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $product['name']; ?></title>
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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

<div class="product-back">
    <a href="index.php" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
        Back to Products
    </a>
</div>

<div class="product-view">

    <div>
        <img src="images/<?php echo $product['image']; ?>">
    </div>

    <div>
        <h1><?php echo $product['name']; ?></h1>

        <h2>₱<?php echo $product['price']; ?></h2>

        <p><strong>SKU:</strong> <?php echo $product['sku']; ?></p>
        <p><strong>Category:</strong> <?php echo $product['category']; ?></p>
        <p><strong>Color:</strong> <?php echo $product['color']; ?></p>
        <p><strong>Size:</strong> <?php echo $product['size']; ?></p>
        <p><strong>Available Stock:</strong> <?php echo $product['stock']; ?></p>

        <form method="POST" action="customer/cart.php">

            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

            <label>Quantity</label><br>

            <input type="number"
                   name="quantity"
                   value="1"
                   min="1"
                   max="<?php echo $product['stock']; ?>"
                   required>

            <br>

            <button type="submit" name="add_cart" class="btn">
                Add to Cart
            </button>

        </form>
    </div>

</div>

</body>
</html>