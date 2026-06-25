<?php
session_start();
include "../includes/config.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

function ensurePaymentSettingsTable($conn)
{
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

    $default_methods = [
        'GCash' => 'Scan the GCash QR code, enter the exact total amount, then upload your payment screenshot and reference number.',
        'Maya' => 'Scan the Maya QR code, enter the exact total amount, then upload your payment screenshot and reference number.',
        'QRPH' => 'Scan the QR PH code using any supported bank or e-wallet, then upload your payment screenshot and reference number.',
        'Bank Transfer' => 'Transfer the exact total amount to the account shown, then upload your payment screenshot and reference number.'
    ];

    foreach ($default_methods as $method => $instructions) {
        $safe_method = mysqli_real_escape_string($conn, $method);
        $safe_instructions = mysqli_real_escape_string($conn, $instructions);

        mysqli_query(
            $conn,
            "INSERT IGNORE INTO payment_settings(payment_method, account_name, account_number, instructions, is_active)
             VALUES('$safe_method', 'Made To Fade', '', '$safe_instructions', 'Yes')"
        );
    }
}

function uploadQrImage($input_name, $method)
{
    if (!isset($_FILES[$input_name]) || $_FILES[$input_name]['name'] == '') {
        return '';
    }

    $folder = "../uploads/payment_qr/";

    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $allowed = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    $file_name = $_FILES[$input_name]['name'];
    $tmp = $_FILES[$input_name]['tmp_name'];
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        die("Invalid QR image type. Use JPG, PNG, or WEBP only.");
    }

    $safe_method = strtolower(str_replace(' ', '_', $method));
    $new_name = $safe_method . "_qr_" . time() . "." . $ext;

    move_uploaded_file($tmp, $folder . $new_name);

    return $new_name;
}

ensurePaymentSettingsTable($conn);

if (isset($_POST['update_payment'])) {
    $id = (int)$_POST['id'];
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $account_name = mysqli_real_escape_string($conn, $_POST['account_name']);
    $account_number = mysqli_real_escape_string($conn, $_POST['account_number']);
    $instructions = mysqli_real_escape_string($conn, $_POST['instructions']);
    $is_active = isset($_POST['is_active']) ? 'Yes' : 'No';

    $qr_image = uploadQrImage('qr_image', $payment_method);

    if ($qr_image != '') {
        mysqli_query(
            $conn,
            "UPDATE payment_settings
             SET account_name='$account_name',
                 account_number='$account_number',
                 qr_image='$qr_image',
                 instructions='$instructions',
                 is_active='$is_active'
             WHERE id='$id'"
        );
    } else {
        mysqli_query(
            $conn,
            "UPDATE payment_settings
             SET account_name='$account_name',
                 account_number='$account_number',
                 instructions='$instructions',
                 is_active='$is_active'
             WHERE id='$id'"
        );
    }

    header("Location: payment_settings.php?updated=1");
    exit();
}

$settings = mysqli_query(
    $conn,
    "SELECT *
     FROM payment_settings
     ORDER BY FIELD(payment_method, 'GCash', 'Maya', 'QRPH', 'Bank Transfer'), payment_method ASC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/x-icon" href="../images/favicon.ico">
</head>
<body>

<div class="layout">
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <?php include "topbar.php"; ?>

        <div class="admin-page-header">
            <h1>Payment Settings</h1>
            <p>Upload and manage QR codes shown to customers on their order details page.</p>
        </div>

        <?php if (isset($_GET['updated'])) { ?>
            <p class="success-msg">Payment settings updated successfully.</p>
        <?php } ?>

        <div class="payment-settings-grid">
            <?php while ($row = mysqli_fetch_assoc($settings)) { ?>
                <div class="panel payment-setting-card">
                    <div class="payment-setting-header">
                        <div>
                            <h2><?php echo htmlspecialchars($row['payment_method']); ?></h2>
                            <p class="muted-text">Customer payment details and QR code.</p>
                        </div>

                        <span class="status <?php echo $row['is_active'] == 'Yes' ? 'active' : 'inactive'; ?>">
                            <?php echo $row['is_active'] == 'Yes' ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>

                    <div class="payment-qr-preview-box">
                        <?php if ($row['qr_image'] != '') { ?>
                            <a href="../uploads/payment_qr/<?php echo htmlspecialchars($row['qr_image']); ?>" target="_blank">
                                <img
                                    src="../uploads/payment_qr/<?php echo htmlspecialchars($row['qr_image']); ?>"
                                    alt="<?php echo htmlspecialchars($row['payment_method']); ?> QR Code"
                                    class="payment-qr-preview"
                                >
                            </a>
                        <?php } else { ?>
                            <div class="payment-qr-empty">
                                <i class="fa-solid fa-qrcode"></i>
                                <p>No QR uploaded yet.</p>
                            </div>
                        <?php } ?>
                    </div>

                    <form method="POST" enctype="multipart/form-data" class="payment-settings-form">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="payment_method" value="<?php echo htmlspecialchars($row['payment_method']); ?>">

                        <label>Account Name</label>
                        <input
                            type="text"
                            name="account_name"
                            value="<?php echo htmlspecialchars($row['account_name']); ?>"
                            placeholder="Example: Made To Fade"
                        >

                        <label>Account Number / Mobile Number</label>
                        <input
                            type="text"
                            name="account_number"
                            value="<?php echo htmlspecialchars($row['account_number']); ?>"
                            placeholder="Example: 0912 123 4567"
                        >

                        <label>QR Code Image</label>
                        <input type="file" name="qr_image" accept="image/*">

                        <label>Customer Instructions</label>
                        <textarea
                            name="instructions"
                            placeholder="Payment instructions shown to customer"
                        ><?php echo htmlspecialchars($row['instructions']); ?></textarea>

                        <label class="checkbox-line">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="Yes"
                                <?php if ($row['is_active'] == 'Yes') echo 'checked'; ?>
                            >
                            Show this payment method to customers
                        </label>

                        <button type="submit" name="update_payment" class="btn">
                            Save <?php echo htmlspecialchars($row['payment_method']); ?>
                        </button>
                    </form>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<script>
function updateClock() {
    const clock = document.getElementById("clock");

    if (!clock) {
        return;
    }

    const now = new Date();

    clock.innerHTML = now.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function toggleSidebar() {
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");

    if (sidebar) {
        sidebar.classList.toggle("hide-sidebar");
    }

    if (mainContent) {
        mainContent.classList.toggle("full-width");
    }
}

updateClock();
setInterval(updateClock, 1000);
</script>

</body>
</html>
