<?php
session_start();
include "../includes/config.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "courier") {
    header("Location: ../login.php");
    exit();
}

function deductStock($conn, $order_id)
{
    $order_id = (int)$order_id;

    $order_result = mysqli_query(
        $conn,
        "SELECT * FROM orders WHERE id='$order_id'"
    );

    $order = mysqli_fetch_assoc($order_result);

    if ($order && $order['stock_deducted'] != "Yes") {

        $order_items = mysqli_query(
            $conn,
            "SELECT * FROM order_items WHERE order_id='$order_id'"
        );

        while ($item = mysqli_fetch_assoc($order_items)) {
            mysqli_query(
                $conn,
                "UPDATE products
                 SET stock = stock - {$item['quantity']}
                 WHERE id='{$item['product_id']}'
                 AND stock >= {$item['quantity']}"
            );
        }

        mysqli_query(
            $conn,
            "UPDATE orders
             SET stock_deducted='Yes'
             WHERE id='$order_id'"
        );
    }
}

function getNextDeliveryOptions($current_status, $payment_status)
{
    $options = [];

    if ($current_status == "Ready for Pickup") {
        $options[] = "Picked Up";
    }

    if ($current_status == "Picked Up") {
        $options[] = "Out for Delivery";
    }

    if ($current_status == "Out for Delivery" && $payment_status == "Paid") {
        $options[] = "Delivered";
    }

    return $options;
}

if (isset($_POST['collect_payment'])) {

    $order_id = (int)$_POST['order_id'];
    $courier_id = $_SESSION['user_id'];

    $check = mysqli_query(
        $conn,
        "SELECT * FROM orders
         WHERE id='$order_id'
         AND courier_id='$courier_id'"
    );

    $order = mysqli_fetch_assoc($check);

    if (
        $order &&
        $order['payment_method'] == "Cash on Delivery" &&
        $order['payment_status'] != "Paid" &&
        $order['delivery_status'] == "Out for Delivery"
    ) {
        deductStock($conn, $order_id);

        mysqli_query(
            $conn,
            "UPDATE orders
             SET payment_status='Paid'
             WHERE id='$order_id'
             AND courier_id='$courier_id'"
        );
    }

    header("Location: deliveries.php");
    exit();
}

if (isset($_POST['update'])) {

    $order_id = (int)$_POST['order_id'];
    $delivery_status = mysqli_real_escape_string($conn, $_POST['delivery_status']);
    $courier_id = $_SESSION['user_id'];

    $order_result = mysqli_query(
        $conn,
        "SELECT * FROM orders
         WHERE id='$order_id'
         AND courier_id='$courier_id'"
    );

    $order = mysqli_fetch_assoc($order_result);

    if (!$order) {
        header("Location: deliveries.php");
        exit();
    }

    $allowed_options = getNextDeliveryOptions(
        $order['delivery_status'],
        $order['payment_status']
    );

    if (!in_array($delivery_status, $allowed_options)) {
        header("Location: deliveries.php");
        exit();
    }

    if ($delivery_status == "Delivered") {

        if ($order['payment_status'] != "Paid") {
            header("Location: deliveries.php");
            exit();
        }

        if ($_FILES['proof_image']['name'] == "") {
            header("Location: deliveries.php");
            exit();
        }

        $folder = "../uploads/proofs/";

        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $allowed = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        $file_name = $_FILES['proof_image']['name'];
        $tmp = $_FILES['proof_image']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            die("Invalid image type.");
        }

        $proof_image = "delivery_" . $order_id . "_" . time() . "." . $ext;

        move_uploaded_file($tmp, $folder . $proof_image);

        mysqli_query(
            $conn,
            "UPDATE orders
             SET delivery_status='Delivered',
                 order_status='Completed',
                 proof_image='$proof_image'
             WHERE id='$order_id'
             AND courier_id='$courier_id'"
        );
    }
    else {
        mysqli_query(
            $conn,
            "UPDATE orders
             SET delivery_status='$delivery_status'
             WHERE id='$order_id'
             AND courier_id='$courier_id'"
        );
    }

    header("Location: deliveries.php");
    exit();
}

$courier_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : "";

$sql = "SELECT orders.*, users.fullname
        FROM orders
        JOIN users ON orders.customer_id = users.id
        WHERE orders.courier_id='$courier_id'
        AND orders.payment_status != 'Rejected'
        AND orders.order_status != 'Payment Rejected'";

if ($search != "") {
    $sql .= " AND (users.fullname LIKE '%$search%' OR orders.id LIKE '%$search%')";
}

if ($status != "") {
    $sql .= " AND orders.delivery_status='$status'";
}

$sql .= " ORDER BY orders.id DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Courier Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">

    <script>
        function showProof(selectBox) {
            var form = selectBox.closest("form");
            var uploadBox = form.querySelector(".upload-container");
            var fileInput = form.querySelector(".file-input");

            if (selectBox.value == "Delivered") {
                uploadBox.style.display = "block";
                fileInput.required = true;
            }
            else {
                uploadBox.style.display = "none";
                fileInput.required = false;
            }
        }
    </script>
</head>
<body class="bg-image">

<div class="navbar customer-navbar courier-navbar">
    <div class="logo-area">
        <a href="deliveries.php" class="nav-logo-link">
            <img src="../images/logo.png" alt="Made To Fade Logo">
        </a>
        <div class="nav-brand-text">
            <strong>Courier Portal</strong>
            <span>Delivery Management</span>
        </div>
    </div>

    <div class="nav-links">
        <a href="deliveries.php">Deliveries</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="admin-container">

    <div class="panel">
        <h1>My Deliveries</h1>
        <p>Welcome, <?php echo $_SESSION['fullname']; ?></p>

        <p>
            <strong>Delivery Flow:</strong>
            Ready for Pickup → Picked Up → Out for Delivery → Payment Paid → Delivered.
        </p>

        <p>
            <strong>COD Rule:</strong>
            Mark as Collected only appears when the order is Out for Delivery and the payment method is Cash on Delivery.
        </p>
    </div>

    <div class="panel">
        <form method="GET" class="filter-wrapper">

            <input
                type="text"
                name="search"
                placeholder="Search ID or Customer"
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <select name="status">
                <option value="">All Status</option>
                <option value="Ready for Pickup" <?php if ($status == "Ready for Pickup") echo "selected"; ?>>Ready for Pickup</option>
                <option value="Picked Up" <?php if ($status == "Picked Up") echo "selected"; ?>>Picked Up</option>
                <option value="Out for Delivery" <?php if ($status == "Out for Delivery") echo "selected"; ?>>Out for Delivery</option>
                <option value="Delivered" <?php if ($status == "Delivered") echo "selected"; ?>>Delivered</option>
            </select>

            <button type="submit" class="btn">Search</button>

            <?php if ($search != "" || $status != "") { ?>
                <a href="deliveries.php" class="btn">Reset</a>
            <?php } ?>

        </form>
    </div>

    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Address</th>
            <th>Contact</th>
            <th>Delivery Status</th>
            <th>Delivery Proof</th>
            <th>Update</th>
        </tr>

        <?php if (mysqli_num_rows($result) > 0) { ?>

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                <?php
                    $next_options = getNextDeliveryOptions(
                        $row['delivery_status'],
                        $row['payment_status']
                    );

                    $show_collect_button = (
                        $row['payment_method'] == "Cash on Delivery" &&
                        $row['payment_status'] != "Paid" &&
                        $row['delivery_status'] == "Out for Delivery"
                    );
                ?>

                <tr>
                    <td>#<?php echo $row['id']; ?></td>

                    <td><?php echo $row['fullname']; ?></td>

                    <td>₱<?php echo $row['total']; ?></td>

                    <td>
                        <strong><?php echo $row['payment_method']; ?></strong>
                        <br>

                        <span class="status <?php echo $row['payment_status'] == "Paid" ? "completed" : "pending"; ?>">
                            <?php echo $row['payment_status']; ?>
                        </span>

                        <?php if ($show_collect_button) { ?>
                            <br><br>

                            <form method="POST">
                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?php echo $row['id']; ?>"
                                >

                                <button
                                    type="submit"
                                    name="collect_payment"
                                    class="btn"
                                >
                                    Mark as Collected
                                </button>
                            </form>
                        <?php } ?>
                    </td>

                    <td><?php echo $row['address']; ?></td>

                    <td><?php echo $row['contact_number']; ?></td>

                    <td>
                        <span class="status pending">
                            <?php echo $row['delivery_status']; ?>
                        </span>
                    </td>

                    <td>
                        <?php if ($row['proof_image'] != "") { ?>
                            <a
                                href="../uploads/proofs/<?php echo $row['proof_image']; ?>"
                                target="_blank"
                            >
                                <img
                                    src="../uploads/proofs/<?php echo $row['proof_image']; ?>"
                                    class="proof-img"
                                >
                            </a>
                        <?php } else { ?>
                            No proof
                        <?php } ?>
                    </td>

                    <td>
                        <?php if (count($next_options) > 0) { ?>
                            <form method="POST" enctype="multipart/form-data">

                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?php echo $row['id']; ?>"
                                >

                                <select
                                    name="delivery_status"
                                    onchange="showProof(this)"
                                    required
                                >
                                    <option value="">Select next status</option>

                                    <?php foreach ($next_options as $option) { ?>
                                        <option value="<?php echo $option; ?>">
                                            <?php echo $option; ?>
                                        </option>
                                    <?php } ?>
                                </select>

                                <div class="upload-container" style="display:none;">
                                    <input
                                        type="file"
                                        name="proof_image"
                                        class="file-input"
                                        accept="image/*"
                                    >
                                </div>

                                <button
                                    type="submit"
                                    name="update"
                                    class="btn"
                                >
                                    Update
                                </button>
                            </form>
                        <?php } else { ?>
                            <?php if ($row['delivery_status'] == "Out for Delivery" && $row['payment_status'] != "Paid") { ?>
                                <small>Payment must be paid before delivery can be completed.</small>
                            <?php } elseif ($row['delivery_status'] == "Delivered") { ?>
                                <small>Order completed.</small>
                            <?php } else { ?>
                                <small>No action available.</small>
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>

            <?php } ?>

        <?php } else { ?>

            <tr>
                <td colspan="9" style="text-align:center;">
                    No deliveries found.
                </td>
            </tr>

        <?php } ?>
    </table>

</div>

</body>
</html>
