<?php
session_start();
include "../includes/config.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "customer") {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($user_result);

function buildFullAddress($data)
{
    $parts = [
        $data['address_line1'],
        $data['address_line2'],
        $data['city'],
        $data['province_region'],
        $data['postal_code'],
        $data['country']
    ];

    $parts = array_filter($parts, function ($value) {
        return trim($value) !== "";
    });

    return implode(", ", $parts);
}

if (isset($_POST['update_profile'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);

    $check_username = mysqli_query(
        $conn,
        "SELECT * FROM users
         WHERE username='$username'
         AND id!='$user_id'"
    );

    if (mysqli_num_rows($check_username) > 0) {
        $error = "Username already exists.";
    } else {
        mysqli_query(
            $conn,
            "UPDATE users
             SET fullname='$fullname', username='$username'
             WHERE id='$user_id'"
        );

        $_SESSION['fullname'] = $fullname;
        $success = "Profile updated successfully.";

        if (
            $_POST['old_password'] != "" ||
            $_POST['new_password'] != "" ||
            $_POST['confirm_password'] != ""
        ) {
            $old_password = $_POST['old_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($old_password == "" || $new_password == "" || $confirm_password == "") {
                $error = "Complete all password fields.";
            } else {
                $valid_old_password = password_verify($old_password, $user['password']);

                if (!$valid_old_password && $old_password == $user['password']) {
                    $valid_old_password = true;
                }

                if (!$valid_old_password) {
                    $error = "Current password is incorrect.";
                } elseif ($new_password != $confirm_password) {
                    $error = "New passwords do not match.";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    mysqli_query(
                        $conn,
                        "UPDATE users
                         SET password='$hashed_password'
                         WHERE id='$user_id'"
                    );

                    $success = "Profile and password updated successfully.";
                }
            }
        }
    }

    $user_result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
    $user = mysqli_fetch_assoc($user_result);
}

if (isset($_POST['add_address'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $address_line1 = mysqli_real_escape_string($conn, $_POST['address_line1']);
    $address_line2 = mysqli_real_escape_string($conn, $_POST['address_line2']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $province_region = mysqli_real_escape_string($conn, $_POST['province_region']);
    $postal_code = mysqli_real_escape_string($conn, $_POST['postal_code']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);

    if (!preg_match("/^09[0-9]{2} [0-9]{3} [0-9]{4}$/", $contact_number)) {
        $error = "Invalid phone number format. Use 0912 123 1234.";
    } else {
        $address_data = [
            'address_line1' => $address_line1,
            'address_line2' => $address_line2,
            'city' => $city,
            'province_region' => $province_region,
            'postal_code' => $postal_code,
            'country' => $country
        ];

        $address = mysqli_real_escape_string($conn, buildFullAddress($address_data));

        $count = mysqli_fetch_assoc(
            mysqli_query(
                $conn,
                "SELECT COUNT(*) AS total
                 FROM user_addresses
                 WHERE user_id='$user_id'"
            )
        );

        $is_default = ($count['total'] == 0) ? "Yes" : "No";

        mysqli_query(
            $conn,
            "INSERT INTO user_addresses(
                user_id,
                full_name,
                contact_number,
                address_line1,
                address_line2,
                city,
                province_region,
                postal_code,
                country,
                address,
                is_default
             )
             VALUES(
                '$user_id',
                '$full_name',
                '$contact_number',
                '$address_line1',
                '$address_line2',
                '$city',
                '$province_region',
                '$postal_code',
                '$country',
                '$address',
                '$is_default'
             )"
        );

        $success = "Address added successfully.";
    }
}

if (isset($_GET['default_address'])) {
    $id = (int)$_GET['default_address'];

    mysqli_query($conn, "UPDATE user_addresses SET is_default='No' WHERE user_id='$user_id'");
    mysqli_query($conn, "UPDATE user_addresses SET is_default='Yes' WHERE id='$id' AND user_id='$user_id'");

    header("Location: home.php");
    exit();
}

if (isset($_GET['delete_address'])) {
    $id = (int)$_GET['delete_address'];

    mysqli_query($conn, "DELETE FROM user_addresses WHERE id='$id' AND user_id='$user_id'");

    header("Location: home.php");
    exit();
}

$order_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE customer_id='$user_id'")
);

$cart_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM cart WHERE customer_id='$user_id'")
);

$addresses = mysqli_query(
    $conn,
    "SELECT * FROM user_addresses
     WHERE user_id='$user_id'
     ORDER BY is_default DESC, id DESC"
);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Customer Dashboard</title>
        <link rel="stylesheet" type="text/css" href="../css/style.css">
    </head>
    <body>
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
            <h1>Customer Dashboard</h1>
            <p>Welcome, <?php echo $_SESSION['fullname']; ?></p>

            <?php if (isset($success)) { ?>
                <p class="success-msg"><?php echo $success; ?></p>
            <?php } ?>

            <?php if (isset($error)) { ?>
                <p class="error-msg"><?php echo $error; ?></p>
            <?php } ?>

            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <h3>My Orders</h3>
                    <h2><?php echo $order_count['total']; ?></h2>
                </div>

                <div class="dashboard-card">
                    <h3>Cart Items</h3>
                    <h2><?php echo $cart_count['total']; ?></h2>
                </div>

                <div class="dashboard-card">
                    <h3>Account Type</h3>
                    <h2>Customer</h2>
                </div>
            </div>

            <div class="customer-profile-grid">
                <div class="panel address-panel">
                    <h2>Saved Addresses & Contacts</h2>
                    <p class="muted-text">Manage delivery details used during checkout.</p>

                    <div class="address-list">
                        <?php if (mysqli_num_rows($addresses) > 0) { ?>
                            <?php while ($a = mysqli_fetch_assoc($addresses)) { ?>
                                <div class="address-item address-card-vertical">
                                    <div class="address-card-main">
                                        <div class="address-card-header">
                                            <strong><?php echo htmlspecialchars($a['full_name']); ?></strong>

                                            <?php if ($a['is_default'] == 'Yes') { ?>
                                                <span class="badge">Default</span>
                                            <?php } ?>
                                        </div>

                                        <p class="address-phone">
                                            <?php echo htmlspecialchars($a['contact_number']); ?>
                                        </p>

                                        <p><?php echo htmlspecialchars($a['address_line1']); ?></p>

                                        <?php if ($a['address_line2'] != '') { ?>
                                            <p><?php echo htmlspecialchars($a['address_line2']); ?></p>
                                        <?php } ?>

                                        <p>
                                            <?php echo htmlspecialchars($a['city']); ?>,
                                            <?php echo htmlspecialchars($a['province_region']); ?>
                                            <?php echo htmlspecialchars($a['postal_code']); ?>
                                        </p>

                                        <p><?php echo htmlspecialchars($a['country']); ?></p>
                                    </div>

                                    <div class="address-actions">
                                        <?php if ($a['is_default'] != 'Yes') { ?>
                                            <a class="btn small-btn" href="home.php?default_address=<?php echo $a['id']; ?>">
                                                Set Default
                                            </a>
                                        <?php } ?>

                                        <a
                                            class="btn small-btn"
                                            onclick="return confirm('Delete this address?')"
                                            href="home.php?delete_address=<?php echo $a['id']; ?>"
                                        >
                                            Delete
                                        </a>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="empty-box">
                                No saved address yet. Add your first address below.
                            </div>
                        <?php } ?>
                    </div>

                    <div class="address-form-box">
                        <h3>Add New Address</h3>

                        <form method="POST" class="address-form compact-address-form">
                            <label>Full Name</label>
                            <input type="text" name="full_name" placeholder="Receiver full name" required>

                            <label>Phone Number</label>
                            <input
                                type="text"
                                name="contact_number"
                                placeholder="0912 123 1234"
                                pattern="09[0-9]{2} [0-9]{3} [0-9]{4}"
                                required
                            >

                            <label>Address Line 1</label>
                            <input type="text" name="address_line1" placeholder="House no., street, barangay" required>

                            <label>Address Line 2 <span class="optional-text">Optional</span></label>
                            <input type="text" name="address_line2" placeholder="Apartment, floor, landmark, etc.">

                            <label>City</label>
                            <input type="text" name="city" placeholder="City" required>

                            <label>Province / Region</label>
                            <input type="text" name="province_region" placeholder="Province or Region" required>

                            <label>Postal Code</label>
                            <input type="text" name="postal_code" placeholder="Postal Code" required>

                            <label>Country</label>
                            <input type="text" name="country" value="Philippines" required>

                            <button type="submit" name="add_address" class="btn">Add Address</button>
                        </form>
                    </div>
                </div>

                <div class="panel profile-panel">
                    <h2>Edit Profile</h2>

                    <form method="POST" class="profile-form">
                        <label>Full Name</label>
                        <input type="text" name="fullname" value="<?php echo $user['fullname']; ?>" required>

                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo $user['username']; ?>" required>

                        <h3>Change Password</h3>

                        <label>Current Password</label>
                        <input type="password" name="old_password" placeholder="Enter current password">

                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="Enter new password">

                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Confirm new password">

                        <button type="submit" name="update_profile" class="btn">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
