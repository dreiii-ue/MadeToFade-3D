<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role'])){
    header("Location: ../login.php");
    exit();
}

if($_SESSION['role'] != "customer"){
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

if(isset($_POST['add_cart'])){
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    $check = mysqli_query($conn,
    "SELECT * FROM cart WHERE customer_id='$customer_id' AND product_id='$product_id'");

    if(mysqli_num_rows($check) > 0){
        mysqli_query($conn,
        "UPDATE cart SET quantity = quantity + $quantity
         WHERE customer_id='$customer_id' AND product_id='$product_id'");
    }
    else{
        mysqli_query($conn,
        "INSERT INTO cart(customer_id, product_id, quantity)
         VALUES('$customer_id', '$product_id', '$quantity')");
    }

    header("Location: cart.php");
    exit();
}

if(isset($_GET['remove'])){
    $id = $_GET['remove'];

    mysqli_query($conn,
    "DELETE FROM cart WHERE id='$id' AND customer_id='$customer_id'");

    header("Location: cart.php");
    exit();
}

$result = mysqli_query($conn,
"SELECT cart.*, products.name, products.price, products.image
 FROM cart
 JOIN products ON cart.product_id = products.id
 WHERE cart.customer_id='$customer_id'");

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Cart</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>

<div class="navbar">
    <div class="logo-area">
        <img src="images/logo.png" alt="Logo">
    </div>

    <div>
        <a href="../index.php">Shop</a>
        <a href="cart.php">Cart</a>
        <a href="orders.php">My Orders</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="admin-container">

<h1>My Cart</h1>

<table>
<tr>
    <th>Image</th>
    <th>Product</th>
    <th>Price</th>
    <th>Qty</th>
    <th>Subtotal</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)){ 
    $subtotal = $row['price'] * $row['quantity'];
    $total += $subtotal;
?>
<tr>
    <td><img src="../images/<?php echo $row['image']; ?>" width="60"></td>
    <td><?php echo $row['name']; ?></td>
    <td>₱<?php echo $row['price']; ?></td>
    <td><?php echo $row['quantity']; ?></td>
    <td>₱<?php echo $subtotal; ?></td>
    <td>
        <a href="cart.php?remove=<?php echo $row['id']; ?>"
           class="btn"
           onclick="return confirm('Remove this item?')">
           Remove
        </a>
    </td>
</tr>
<?php } ?>

</table>

<h2>Total: ₱<?php echo $total; ?></h2>

<?php if($total > 0){ ?>

<form method="POST" action="checkout.php" class="checkout-form">

    <input type="text"
           name="address"
           placeholder="Delivery Address"
           required>

    <input type="text"
       name="contact_number"
       placeholder="0912 123 1234"
       pattern="09[0-9]{2} [0-9]{3} [0-9]{4}"
       required>

    <select name="payment_method" required>
        <option value="">Payment Method</option>
        <option value="Cash on Delivery">Cash on Delivery</option>
        <option value="GCash">GCash</option>
        <option value="Maya">Maya</option>
        <option value="Bank Transfer">Bank Transfer</option>
    </select>

    <button type="submit" name="checkout" class="btn">
        Checkout
    </button>

</form>

<?php } ?>

</div>

</body>
</html>