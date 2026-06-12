<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "customer"){
    header("Location: ../login.php");
    exit();
}

$order_id = (int)$_GET['id'];
$customer_id = $_SESSION['user_id'];

$order = mysqli_query($conn,
"SELECT * FROM orders
 WHERE id='$order_id'
 AND customer_id='$customer_id'");

$order_data = mysqli_fetch_assoc($order);

if(!$order_data){
    header("Location: orders.php");
    exit();
}

if(isset($_POST['upload_payment'])){

    $payment_reference = mysqli_real_escape_string($conn, $_POST['payment_reference']);

    if($_FILES['payment_screenshot']['name'] != ""){

        $folder = "../uploads/payments/";

        if(!file_exists($folder)){
            mkdir($folder, 0777, true);
        }

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $file_name = $_FILES['payment_screenshot']['name'];
        $tmp = $_FILES['payment_screenshot']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if(!in_array($ext, $allowed)){
            die("Invalid image type.");
        }

        $new_name = "payment_" . $order_id . "_" . time() . "." . $ext;

        move_uploaded_file($tmp, $folder . $new_name);

        mysqli_query($conn,
        "UPDATE orders SET
            payment_screenshot='$new_name',
            payment_reference='$payment_reference',
            payment_status='Proof Submitted',
            payment_reject_reason=''
         WHERE id='$order_id'
         AND customer_id='$customer_id'");

        header("Location: order_details.php?id=$order_id");
        exit();
    }
}

$items = mysqli_query($conn,
"SELECT order_items.*, products.name, products.price, products.image
 FROM order_items
 JOIN products ON order_items.product_id = products.id
 WHERE order_items.order_id='$order_id'");

$order = mysqli_query($conn,
"SELECT * FROM orders
 WHERE id='$order_id'
 AND customer_id='$customer_id'");

$order_data = mysqli_fetch_assoc($order);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body class="bg-image">

<div class="navbar">
    <div class="logo-area">
        <img src="../images/logo.png" alt="Logo">
    </div>

    <div>
        <a href="../index.php">Shop</a>
        <a href="home.php">Dashboard</a>
        <a href="cart.php">Cart</a>
        <a href="orders.php">My Orders</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="admin-container">

<div class="panel">

<h1>Order Details</h1>

<a href="orders.php" class="btn">Back</a>

<br><br>

<h3>Order #<?php echo $order_data['id']; ?></h3>

<p><strong>Payment Method:</strong> <?php echo $order_data['payment_method']; ?></p>

<p>
    <strong>Payment Status:</strong>
    <span class="status <?php echo $order_data['payment_status'] == 'Paid' ? 'completed' : 'pending'; ?>">
        <?php echo $order_data['payment_status']; ?>
    </span>
</p>

<p><strong>Order Status:</strong> <?php echo $order_data['order_status']; ?></p>
<p><strong>Delivery Status:</strong> <?php echo $order_data['delivery_status']; ?></p>
<p><strong>Address:</strong> <?php echo $order_data['address']; ?></p>
<p><strong>Contact:</strong> <?php echo $order_data['contact_number']; ?></p>
<p><strong>Date:</strong> <?php echo $order_data['date_created']; ?></p>

<?php if($order_data['payment_reject_reason'] != ""){ ?>
    <p class="error-msg">
        Payment rejected: <?php echo $order_data['payment_reject_reason']; ?>
    </p>
<?php } ?>

</div>

<?php if($order_data['payment_method'] != "Cash on Delivery" && $order_data['payment_status'] != "Paid"){ ?>

<div class="panel">
    <h2>Submit Proof of Payment</h2>

    <form method="POST" enctype="multipart/form-data" class="profile-form">

        <label>Reference Number</label>
        <input type="text"
               name="payment_reference"
               placeholder="GCash / Maya / Bank Reference No."
               value="<?php echo $order_data['payment_reference']; ?>"
               required>

        <label>Payment Screenshot</label>
        <input type="file"
               name="payment_screenshot"
               accept="image/*"
               required>

        <button type="submit" name="upload_payment" class="btn">
            Submit Payment Proof
        </button>

    </form>
</div>

<?php } ?>

<?php if($order_data['payment_screenshot'] != ""){ ?>
<div class="panel">
    <h2>Payment Screenshot</h2>

    <p><strong>Reference:</strong> <?php echo $order_data['payment_reference']; ?></p>

    <a href="../uploads/payments/<?php echo $order_data['payment_screenshot']; ?>" target="_blank">
        <img src="../uploads/payments/<?php echo $order_data['payment_screenshot']; ?>" class="proof-img">
    </a>
</div>
<?php } ?>

<?php if($order_data['proof_image'] != ""){ ?>
<div class="panel">
    <h2>Proof of Delivery</h2>

    <a href="../uploads/proofs/<?php echo $order_data['proof_image']; ?>" target="_blank">
        <img src="../uploads/proofs/<?php echo $order_data['proof_image']; ?>" class="proof-img">
    </a>
</div>
<?php } ?>

<div class="panel">

<h2>Ordered Items</h2>

<table>
<tr>
    <th>Image</th>
    <th>Product</th>
    <th>Price</th>
    <th>Quantity</th>
    <th>Subtotal</th>
</tr>

<?php while($row = mysqli_fetch_assoc($items)){ 
    $subtotal = $row['price'] * $row['quantity'];
?>
<tr>
    <td><img src="../images/<?php echo $row['image']; ?>"></td>
    <td><?php echo $row['name']; ?></td>
    <td>₱<?php echo $row['price']; ?></td>
    <td><?php echo $row['quantity']; ?></td>
    <td>₱<?php echo $subtotal; ?></td>
</tr>
<?php } ?>

</table>

<h2>Total: ₱<?php echo $order_data['total']; ?></h2>

</div>

</div>

</body>
</html>