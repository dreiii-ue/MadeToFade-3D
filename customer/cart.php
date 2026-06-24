<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "customer"){
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

if(isset($_POST['add_cart'])){
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    if($quantity < 1) $quantity = 1;

    $stock_result = mysqli_query($conn, "SELECT stock FROM products WHERE id='$product_id'");
    $stock_row = mysqli_fetch_assoc($stock_result);
    $stock = $stock_row ? (int)$stock_row['stock'] : 0;

    $check = mysqli_query($conn, "SELECT * FROM cart WHERE customer_id='$customer_id' AND product_id='$product_id'");

    if(mysqli_num_rows($check) > 0){
        $cart_row = mysqli_fetch_assoc($check);
        $new_qty = $cart_row['quantity'] + $quantity;
        if($new_qty > $stock) $new_qty = $stock;
        mysqli_query($conn, "UPDATE cart SET quantity='$new_qty' WHERE customer_id='$customer_id' AND product_id='$product_id'");
    }
    else{
        if($quantity > $stock) $quantity = $stock;
        mysqli_query($conn, "INSERT INTO cart(customer_id, product_id, quantity) VALUES('$customer_id', '$product_id', '$quantity')");
    }

    header("Location: cart.php");
    exit();
}

if(isset($_POST['update_qty'])){
    $cart_id = (int)$_POST['cart_id'];
    $action = $_POST['action'];

    $cart_item = mysqli_query($conn, "SELECT cart.*, products.stock FROM cart JOIN products ON cart.product_id=products.id WHERE cart.id='$cart_id' AND cart.customer_id='$customer_id'");
    $item = mysqli_fetch_assoc($cart_item);

    if($item){
        $qty = (int)$item['quantity'];
        if($action == "add" && $qty < (int)$item['stock']) $qty++;
        if($action == "subtract") $qty--;

        if($qty <= 0){
            mysqli_query($conn, "DELETE FROM cart WHERE id='$cart_id' AND customer_id='$customer_id'");
        }
        else{
            mysqli_query($conn, "UPDATE cart SET quantity='$qty' WHERE id='$cart_id' AND customer_id='$customer_id'");
        }
    }

    header("Location: cart.php");
    exit();
}

if(isset($_GET['remove'])){
    $id = (int)$_GET['remove'];
    mysqli_query($conn, "DELETE FROM cart WHERE id='$id' AND customer_id='$customer_id'");
    header("Location: cart.php");
    exit();
}

$result = mysqli_query($conn,
"SELECT cart.*, products.name, products.price, products.image, products.stock
 FROM cart
 JOIN products ON cart.product_id = products.id
 WHERE cart.customer_id='$customer_id'");

$addresses = mysqli_query($conn, "SELECT * FROM user_addresses WHERE user_id='$customer_id' ORDER BY is_default DESC, id DESC");
$total = 0;
?>
<!DOCTYPE html>
<html>
    <head>
        <title>My Cart</title>
        <link rel="stylesheet" type="text/css" href="../css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="icon" type="image/x-icon" href="../images/favicon.ico">
    </head>
    <body>
        <div class="navbar customer-navbar">
            <div class="logo-area">
                <a href="../index.php" class="nav-logo-link">
                    <img src="../images/logo.png" alt="Made To Fade Logo">
                </a>
            </div>

            <div class="nav-links">
                <a href="../index.php">Shop</a>
                <a href="home.php">Dashboard</a>
                <a href="cart.php">Cart</a>
                <a href="orders.php">My Orders</a>
                <a href="../logout.php">Logout</a>
            </div>
        </div>
        <div class="admin-container">
            <h1>My Cart</h1>
            <p class="checkout-note">Use the plus and minus buttons to adjust product quantity before checking out.</p>

            <?php if (mysqli_num_rows($result) > 0) { ?>
                <table>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>

                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <?php
                        $subtotal = $row['price'] * $row['quantity'];
                        $total += $subtotal;
                        ?>

                        <tr>
                            <td>
                                <img src="../images/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['name']); ?><br>
                                <small>Stock: <?php echo $row['stock']; ?></small>
                            </td>

                            <td>₱<?php echo number_format($row['price'], 2); ?></td>

                            <td>
                                <form method="POST" class="qty-control">
                                    <input type="hidden" name="cart_id" value="<?php echo $row['id']; ?>">

                                    <button
                                        type="submit"
                                        name="action"
                                        value="subtract"
                                        class="small-btn qty-btn"
                                        aria-label="Decrease quantity"
                                    >−</button>

                                    <input
                                        type="text"
                                        class="qty-value"
                                        value="<?php echo $row['quantity']; ?>"
                                        readonly
                                    >

                                    <button
                                        type="submit"
                                        name="action"
                                        value="add"
                                        class="small-btn qty-btn"
                                        aria-label="Increase quantity"
                                    >+</button>

                                    <input type="hidden" name="update_qty" value="1">
                                </form>
                            </td>

                            <td>₱<?php echo number_format($subtotal, 2); ?></td>

                            <td>
                                <a
                                    href="cart.php?remove=<?php echo $row['id']; ?>"
                                    class="btn"
                                    onclick="return confirm('Remove this item?')"
                                >
                                    Remove
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>

                <br>

                <h2>Total: ₱<?php echo number_format($total, 2); ?></h2>

                <div class="panel">
                    <h2>Checkout Details</h2>
                    <p class="muted-text">
                        Select one of your saved delivery addresses. If you do not have one yet, complete the new address form below.
                    </p>

                    <form method="POST" action="checkout.php" class="checkout-form checkout-details-form">
                        <?php if (mysqli_num_rows($addresses) > 0) { ?>
                            <div class="form-full">
                                <label>Saved Delivery Address</label>
                                <select name="saved_address_id" required>
                                    <option value="">Select saved address</option>
                                    <?php while ($a = mysqli_fetch_assoc($addresses)) { ?>
                                        <option value="<?php echo $a['id']; ?>">
                                            <?php echo htmlspecialchars($a['full_name'] ?? ''); ?> -
                                            <?php echo htmlspecialchars($a['contact_number']); ?> -
                                            <?php echo htmlspecialchars(substr($a['address'], 0, 80)); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } else { ?>
                            <div class="form-full">
                                <h3>New Delivery Address</h3>
                            </div>

                            <div>
                                <label>Full Name</label>
                                <input type="text" name="full_name" placeholder="Receiver full name" required>
                            </div>

                            <div>
                                <label>Phone Number</label>
                                <input
                                    type="text"
                                    name="contact_number"
                                    placeholder="0912 123 1234"
                                    pattern="09[0-9]{2} [0-9]{3} [0-9]{4}"
                                    required
                                >
                            </div>

                            <div class="form-full">
                                <label>Address Line 1</label>
                                <input type="text" name="address_line1" placeholder="House no., street, barangay" required>
                            </div>

                            <div class="form-full">
                                <label>Address Line 2 <span class="optional-text">Optional</span></label>
                                <input type="text" name="address_line2" placeholder="Apartment, floor, landmark, etc.">
                            </div>

                            <div>
                                <label>City</label>
                                <input type="text" name="city" placeholder="City" required>
                            </div>

                            <div>
                                <label>Province / Region</label>
                                <input type="text" name="province_region" placeholder="Province or Region" required>
                            </div>

                            <div>
                                <label>Postal Code</label>
                                <input type="text" name="postal_code" placeholder="Postal Code" inputmode="numeric" pattern="[0-9]+" maxlength="10" required>
                            </div>

                            <div>
                                <label>Country</label>
                                <input type="text" name="country" value="Philippines" required>
                            </div>
                        <?php } ?>

                        <div>
                            <label>Payment Method</label>
                            <select name="payment_method" required>
                                <option value="">Select payment method</option>
                                <option value="Cash on Delivery">Cash on Delivery</option>
                                <option value="GCash">GCash</option>
                                <option value="Maya">Maya</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <div class="checkout-button-box">
                            <button type="submit" name="checkout" class="btn">Checkout</button>
                        </div>
                    </form>
                </div>
            <?php } else { ?>
                <div class="panel empty-cart-panel">
                    <div class="empty-cart-icon">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>

                    <h2>Your cart is empty</h2>
                    <p>Add items first before checking out.</p>

                    <a href="../index.php#popular-products" class="btn">
                        Shop Now
                    </a>
                </div>
            <?php } ?>
        </div>
    </body>
</html>
