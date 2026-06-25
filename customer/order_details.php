<?php
session_start();
include "../includes/config.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "customer") {
    header("Location: ../login.php");
    exit();
}


mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS payment_settings (
        id INT(11) NOT NULL AUTO_INCREMENT,
        payment_method VARCHAR(50) NOT NULL UNIQUE,
        account_name VARCHAR(100) DEFAULT '',
        account_number VARCHAR(100) DEFAULT '',
        qr_image VARCHAR(255) DEFAULT '',
        instructions TEXT DEFAULT NULL,
        is_active VARCHAR(5) NOT NULL DEFAULT 'Yes',
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
);

$default_payment_methods = [
    'GCash',
    'Maya',
    'QRPH',
    'Bank Transfer'
];

foreach ($default_payment_methods as $method) {
    $safe_method = mysqli_real_escape_string($conn, $method);

    mysqli_query(
        $conn,
        "INSERT IGNORE INTO payment_settings(payment_method, account_name, instructions, is_active)
         VALUES('$safe_method', 'Made To Fade', 'Please pay the exact total amount and upload your proof of payment.', 'Yes')"
    );
}

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

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customer_id = $_SESSION['user_id'];

$order = mysqli_query(
    $conn,
    "SELECT *
     FROM orders
     WHERE id='$order_id'
     AND customer_id='$customer_id'"
);

$order_data = mysqli_fetch_assoc($order);

if (!$order_data) {
    header("Location: orders.php");
    exit();
}

if (isset($_POST['upload_payment'])) {
    $payment_reference = mysqli_real_escape_string($conn, $_POST['payment_reference']);

    if ($_FILES['payment_screenshot']['name'] != "") {
        $folder = "../uploads/payments/";

        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $allowed = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        $file_name = $_FILES['payment_screenshot']['name'];
        $tmp = $_FILES['payment_screenshot']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            die("Invalid image type.");
        }

        $new_name = "payment_" . $order_id . "_" . time() . "." . $ext;

        move_uploaded_file($tmp, $folder . $new_name);

        mysqli_query(
            $conn,
            "UPDATE orders
             SET payment_screenshot='$new_name',
                 payment_reference='$payment_reference',
                 payment_status='Proof Submitted',
                 payment_reject_reason=''
             WHERE id='$order_id'
             AND customer_id='$customer_id'"
        );

        header("Location: order_details.php?id=$order_id");
        exit();
    }
}

if (isset($_POST['submit_review'])) {
    $product_id = (int)$_POST['product_id'];
    $rating = (int)$_POST['rating'];
    $review_text = mysqli_real_escape_string($conn, trim($_POST['review_text']));

    $purchased = mysqli_query(
        $conn,
        "SELECT order_items.id
         FROM order_items
         JOIN orders ON order_items.order_id = orders.id
         WHERE orders.id='$order_id'
         AND orders.customer_id='$customer_id'
         AND orders.delivery_status='Delivered'
         AND orders.payment_status='Paid'
         AND order_items.product_id='$product_id'"
    );

    if (mysqli_num_rows($purchased) == 0) {
        $error = "You can only review products from completed and paid orders.";
    } elseif ($rating < 1 || $rating > 5) {
        $error = "Please select a valid rating.";
    } elseif ($review_text == "") {
        $error = "Please enter your review.";
    } else {
        mysqli_query(
            $conn,
            "INSERT INTO product_reviews(product_id, customer_id, order_id, rating, review_text)
             VALUES('$product_id', '$customer_id', '$order_id', '$rating', '$review_text')
             ON DUPLICATE KEY UPDATE
                rating='$rating',
                review_text='$review_text',
                created_at=CURRENT_TIMESTAMP"
        );

        $success = "Review submitted successfully.";
    }
}

$items = mysqli_query(
    $conn,
    "SELECT order_items.*, products.name, products.price, products.image
     FROM order_items
     JOIN products ON order_items.product_id = products.id
     WHERE order_items.order_id='$order_id'"
);

$order = mysqli_query(
    $conn,
    "SELECT *
     FROM orders
     WHERE id='$order_id'
     AND customer_id='$customer_id'"
);

$order_data = mysqli_fetch_assoc($order);


$payment_setting = null;

if ($order_data['payment_method'] != "Cash on Delivery") {
    $safe_payment_method = mysqli_real_escape_string($conn, $order_data['payment_method']);

    $payment_setting_result = mysqli_query(
        $conn,
        "SELECT *
         FROM payment_settings
         WHERE payment_method='$safe_payment_method'
         LIMIT 1"
    );

    $payment_setting = mysqli_fetch_assoc($payment_setting_result);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../images/favicon.ico">
</head>
<body class="bg-image">

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
    <div class="panel">
        <h1>Order Details</h1>

        <a href="orders.php" class="btn">Back</a>

        <br><br>

        <?php if (isset($success)) { ?>
            <p class="success-msg"><?php echo $success; ?></p>
        <?php } ?>

        <?php if (isset($error)) { ?>
            <p class="error-msg"><?php echo $error; ?></p>
        <?php } ?>

        <h3>Order #<?php echo $order_data['id']; ?></h3>

        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order_data['payment_method']); ?></p>

        <p>
            <strong>Payment Status:</strong>
            <span class="status <?php echo $order_data['payment_status'] == 'Paid' ? 'completed' : 'pending'; ?>">
                <?php echo htmlspecialchars($order_data['payment_status']); ?>
            </span>
        </p>

        <p><strong>Order Status:</strong> <?php echo htmlspecialchars($order_data['order_status']); ?></p>
        <p><strong>Delivery Status:</strong> <?php echo htmlspecialchars($order_data['delivery_status']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($order_data['address']); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($order_data['contact_number']); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($order_data['date_created']); ?></p>

        <?php if ($order_data['payment_reject_reason'] != "") { ?>
            <p class="error-msg">
                Payment rejected: <?php echo htmlspecialchars($order_data['payment_reject_reason']); ?>
            </p>
        <?php } ?>
    </div>


    <?php if ($order_data['payment_method'] != "Cash on Delivery" && $order_data['payment_status'] != "Paid") { ?>
        <div class="panel payment-instructions-panel">
            <div class="payment-instructions-grid">
                <div>
                    <h2><?php echo htmlspecialchars($order_data['payment_method']); ?> Payment</h2>
                    <p class="muted-text">Scan the QR code or use the account details below. Pay the exact total amount.</p>

                    <div class="payment-detail-list">
                        <p><strong>Total Amount:</strong> ₱<?php echo number_format($order_data['total'], 2); ?></p>

                        <?php if ($payment_setting) { ?>
                            <p><strong>Account Name:</strong> <?php echo htmlspecialchars($payment_setting['account_name']); ?></p>
                            <p><strong>Account Number:</strong> <?php echo htmlspecialchars($payment_setting['account_number']); ?></p>

                            <?php if ($payment_setting['instructions'] != '') { ?>
                                <p><strong>Instructions:</strong> <?php echo nl2br(htmlspecialchars($payment_setting['instructions'])); ?></p>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="error-msg">Payment details are not available yet. Please contact the admin.</p>
                        <?php } ?>
                    </div>
                </div>

                <div class="payment-qr-card">
                    <?php if ($payment_setting && $payment_setting['qr_image'] != '') { ?>
                        <img
                            src="../uploads/payment_qr/<?php echo htmlspecialchars($payment_setting['qr_image']); ?>"
                            alt="<?php echo htmlspecialchars($order_data['payment_method']); ?> QR Code"
                            class="payment-qr-image"
                        >
                    <?php } else { ?>
                        <div class="payment-qr-empty customer-qr-empty">
                            <i class="fa-solid fa-qrcode"></i>
                            <p>No QR code uploaded yet.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if ($order_data['payment_method'] != "Cash on Delivery" && $order_data['payment_status'] != "Paid") { ?>
        <div class="panel">
            <h2>Submit Proof of Payment</h2>

            <form method="POST" enctype="multipart/form-data" class="profile-form">
                <label>Reference Number</label>
                <input
                    type="text"
                    name="payment_reference"
                    placeholder="GCash / Maya / Bank Reference No."
                    value="<?php echo htmlspecialchars($order_data['payment_reference']); ?>"
                    required
                >

                <label>Payment Screenshot</label>
                <input
                    type="file"
                    name="payment_screenshot"
                    accept="image/*"
                    required
                >

                <button type="submit" name="upload_payment" class="btn">
                    Submit Payment Proof
                </button>
            </form>
        </div>
    <?php } ?>

    <?php if ($order_data['payment_screenshot'] != "") { ?>
        <div class="panel">
            <h2>Payment Screenshot</h2>
            <p><strong>Reference:</strong> <?php echo htmlspecialchars($order_data['payment_reference']); ?></p>

            <a href="../uploads/payments/<?php echo htmlspecialchars($order_data['payment_screenshot']); ?>" target="_blank">
                <img src="../uploads/payments/<?php echo htmlspecialchars($order_data['payment_screenshot']); ?>" class="proof-img">
            </a>
        </div>
    <?php } ?>

    <?php if ($order_data['proof_image'] != "") { ?>
        <div class="panel">
            <h2>Proof of Delivery</h2>

            <a href="../uploads/proofs/<?php echo htmlspecialchars($order_data['proof_image']); ?>" target="_blank">
                <img src="../uploads/proofs/<?php echo htmlspecialchars($order_data['proof_image']); ?>" class="proof-img">
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
                <th>Review</th>
            </tr>

            <?php while ($row = mysqli_fetch_assoc($items)) { ?>
                <?php
                $subtotal = $row['price'] * $row['quantity'];
                $review = mysqli_fetch_assoc(mysqli_query(
                    $conn,
                    "SELECT *
                     FROM product_reviews
                     WHERE order_id='$order_id'
                     AND product_id='{$row['product_id']}'
                     AND customer_id='$customer_id'"
                ));

                $can_review = $order_data['delivery_status'] == "Delivered" && $order_data['payment_status'] == "Paid";
                ?>

                <tr>
                    <td>
                        <img src="../images/<?php echo htmlspecialchars($row['image']); ?>">
                    </td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>₱<?php echo number_format($row['price'], 2); ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td>₱<?php echo number_format($subtotal, 2); ?></td>
                    <td>
                        <?php if ($can_review) { ?>
                            <form method="POST" class="review-mini-form">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">

                                <div class="star-rating-input" aria-label="Select rating">
                                    <?php for ($i = 5; $i >= 1; $i--) { ?>
                                        <input
                                            type="radio"
                                            id="rating-<?php echo $row['product_id']; ?>-<?php echo $i; ?>"
                                            name="rating"
                                            value="<?php echo $i; ?>"
                                            <?php if (($review && $review['rating'] == $i) || (!$review && $i == 5)) echo "checked"; ?>
                                            required
                                        >
                                        <label
                                            for="rating-<?php echo $row['product_id']; ?>-<?php echo $i; ?>"
                                            title="<?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?>"
                                        >
                                            <i class="fa-solid fa-star"></i>
                                        </label>
                                    <?php } ?>
                                </div>

                                <textarea
                                    name="review_text"
                                    placeholder="Write your review"
                                    required
                                ><?php echo $review ? htmlspecialchars($review['review_text']) : ''; ?></textarea>

                                <button type="submit" name="submit_review" class="btn">
                                    <?php echo $review ? 'Update Review' : 'Submit Review'; ?>
                                </button>
                            </form>
                        <?php } else { ?>
                            <small>Available after paid and delivered.</small>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <h2>Total: ₱<?php echo number_format($order_data['total'], 2); ?></h2>
    </div>
</div>

</body>
</html>
