<?php
session_start();
include "../includes/config.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "customer") {
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

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

if (isset($_POST['checkout'])) {
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $saved_address_id = isset($_POST['saved_address_id']) ? (int)$_POST['saved_address_id'] : 0;

    $address = "";
    $contact_number = "";

    if ($saved_address_id > 0) {
        $saved_address = mysqli_query(
            $conn,
            "SELECT * FROM user_addresses
             WHERE id='$saved_address_id'
             AND user_id='$customer_id'"
        );

        $saved_data = mysqli_fetch_assoc($saved_address);

        if ($saved_data) {
            $address = mysqli_real_escape_string($conn, buildFullAddress($saved_data));
            $contact_number = mysqli_real_escape_string($conn, $saved_data['contact_number']);
        }
    } else {
        $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
        $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number'] ?? '');
        $address_line1 = mysqli_real_escape_string($conn, $_POST['address_line1'] ?? '');
        $address_line2 = mysqli_real_escape_string($conn, $_POST['address_line2'] ?? '');
        $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
        $province_region = mysqli_real_escape_string($conn, $_POST['province_region'] ?? '');
        $postal_code = mysqli_real_escape_string($conn, $_POST['postal_code'] ?? '');
        $country = mysqli_real_escape_string($conn, $_POST['country'] ?? '');

        if (
            $full_name == "" ||
            $contact_number == "" ||
            $address_line1 == "" ||
            $city == "" ||
            $province_region == "" ||
            $postal_code == "" ||
            $country == ""
        ) {
            die("Please complete the delivery address form.");
        }

        if (!preg_match("/^09[0-9]{2} [0-9]{3} [0-9]{4}$/", $contact_number)) {
            die("Invalid phone number format. Use 0912 123 1234.");
        }

        if (!preg_match("/^[0-9]+$/", $postal_code)) {
            die("Postal code must contain numbers only.");
        }

        $address_data = [
            'address_line1' => $address_line1,
            'address_line2' => $address_line2,
            'city' => $city,
            'province_region' => $province_region,
            'postal_code' => $postal_code,
            'country' => $country
        ];

        $address = mysqli_real_escape_string($conn, buildFullAddress($address_data));

        $address_count = mysqli_fetch_assoc(
            mysqli_query(
                $conn,
                "SELECT COUNT(*) AS total
                 FROM user_addresses
                 WHERE user_id='$customer_id'"
            )
        );

        $is_default = ($address_count['total'] == 0) ? "Yes" : "No";

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
                '$customer_id',
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
    }

    if ($address == "" || $contact_number == "") {
        die("Please select or enter a delivery address.");
    }

    $payment_status = ($payment_method == "Cash on Delivery") ? "To Collect" : "Pending Verification";

    $cart = mysqli_query(
        $conn,
        "SELECT cart.*, products.name, products.price, products.stock
         FROM cart
         JOIN products ON cart.product_id = products.id
         WHERE cart.customer_id='$customer_id'"
    );

    if (mysqli_num_rows($cart) == 0) {
        header("Location: cart.php");
        exit();
    }

    $total = 0;
    $stock_error = "";

    while ($item = mysqli_fetch_assoc($cart)) {
        if ($item['quantity'] > $item['stock']) {
            $stock_error = $item['name'] . " does not have enough stock.";
        }

        $total += $item['price'] * $item['quantity'];
    }

    if ($stock_error != "") {
        die($stock_error);
    }

    mysqli_query(
        $conn,
        "INSERT INTO orders(
            customer_id,
            total,
            order_status,
            delivery_status,
            address,
            contact_number,
            payment_method,
            payment_status,
            stock_deducted,
            proof_image,
            payment_screenshot,
            payment_reference,
            payment_reject_reason
         )
         VALUES(
            '$customer_id',
            '$total',
            'Pending',
            'Preparing',
            '$address',
            '$contact_number',
            '$payment_method',
            '$payment_status',
            'No',
            '',
            '',
            '',
            ''
         )"
    );

    $order_id = mysqli_insert_id($conn);

    $cart_items = mysqli_query(
        $conn,
        "SELECT * FROM cart WHERE customer_id='$customer_id'"
    );

    while ($item = mysqli_fetch_assoc($cart_items)) {
        mysqli_query(
            $conn,
            "INSERT INTO order_items(order_id, product_id, quantity)
             VALUES('$order_id', '{$item['product_id']}', '{$item['quantity']}')"
        );
    }

    mysqli_query($conn, "DELETE FROM cart WHERE customer_id='$customer_id'");

    header("Location: order_details.php?id=$order_id");
    exit();
}

header("Location: cart.php");
exit();
?>
