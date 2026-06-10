<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "courier"){
    header("Location: ../login.php");
    exit();
}

if(isset($_POST['update'])){

    $order_id = $_POST['order_id'];
    $delivery_status = $_POST['delivery_status'];

    if($delivery_status == "Delivered" && $_FILES['proof_image']['name'] != ""){

        $folder = "../uploads/proofs/";

        if(!file_exists($folder)){
            mkdir($folder, 0777, true);
        }

        $file_name = $_FILES['proof_image']['name'];
        $tmp = $_FILES['proof_image']['tmp_name'];

        $proof_image = "proof_" . $order_id . "_" . $file_name;

        move_uploaded_file($tmp, $folder . $proof_image);

        mysqli_query($conn,
        "UPDATE orders SET
            delivery_status='Delivered',
            order_status='Completed',
            proof_image='$proof_image'
         WHERE id='$order_id'");
    }
    else{
        mysqli_query($conn,
        "UPDATE orders
         SET delivery_status='$delivery_status'
         WHERE id='$order_id'");
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
        WHERE orders.courier_id='$courier_id'";

if($search != ""){
    $sql .= " AND (users.fullname LIKE '%$search%' OR orders.id LIKE '%$search%')";
}

if($status != ""){
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
    function showProof(selectBox){
        var form = selectBox.closest("form");
        var uploadBox = form.querySelector(".upload-container");
        var fileInput = form.querySelector(".file-input");

        if(selectBox.value == "Delivered"){
            uploadBox.style.display = "block";
            fileInput.required = true;
        }
        else{
            uploadBox.style.display = "none";
            fileInput.required = false;
        }
    }
    </script>
</head>
<body>

<div class="navbar">
    <h2>MADE TO FADE COURIER</h2>

    <div>
        <a href="deliveries.php">Deliveries</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="admin-container">

<h1>My Deliveries</h1>
<p>Welcome, <?php echo $_SESSION['fullname']; ?></p>

<form method="GET" class="filter-wrapper">

    <input type="text" name="search" placeholder="Search ID or Customer"
           value="<?php echo htmlspecialchars($search); ?>">

    <select name="status">
        <option value="">All Status</option>
        <option value="Ready for Pickup" <?php if($status=="Ready for Pickup") echo "selected"; ?>>Ready for Pickup</option>
        <option value="Picked Up" <?php if($status=="Picked Up") echo "selected"; ?>>Picked Up</option>
        <option value="Out for Delivery" <?php if($status=="Out for Delivery") echo "selected"; ?>>Out for Delivery</option>
        <option value="Delivered" <?php if($status=="Delivered") echo "selected"; ?>>Delivered</option>
    </select>

    <button type="submit" class="btn">Search</button>

    <?php if($search != "" || $status != ""){ ?>
        <a href="deliveries.php" class="btn">Reset</a>
    <?php } ?>

</form>

<br>

<table>
<tr>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Total</th>
    <th>Payment Method</th>
    <th>Payment Status</th>
    <th>Address</th>
    <th>Contact</th>
    <th>Delivery Status</th>
    <th>Proof</th>
    <th>Update</th>
</tr>

<?php if(mysqli_num_rows($result) > 0){ ?>

<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
    <td>#<?php echo $row['id']; ?></td>
    <td><?php echo $row['fullname']; ?></td>
    <td>₱<?php echo $row['total']; ?></td>
    <td><?php echo $row['payment_method']; ?></td>

    <td>
        <span class="status <?php echo $row['payment_status'] == 'Paid' ? 'completed' : 'pending'; ?>">
            <?php echo $row['payment_status']; ?>
        </span>
    </td>

    <td><?php echo $row['address']; ?></td>
    <td><?php echo $row['contact_number']; ?></td>

    <td>
        <?php
        $delivery_class = "preparing";

        if($row['delivery_status'] == "Ready for Pickup" || $row['delivery_status'] == "Picked Up"){
            $delivery_class = "pickup";
        }

        if($row['delivery_status'] == "Out for Delivery"){
            $delivery_class = "delivery";
        }

        if($row['delivery_status'] == "Delivered"){
            $delivery_class = "delivered";
        }
        ?>

        <span class="status <?php echo $delivery_class; ?>">
            <?php echo $row['delivery_status']; ?>
        </span>
    </td>

    <td>
        <?php if($row['proof_image'] != ""){ ?>
            <a href="../uploads/proofs/<?php echo $row['proof_image']; ?>" target="_blank">
                <img src="../uploads/proofs/<?php echo $row['proof_image']; ?>" class="proof-img">
            </a>
        <?php } else { ?>
            No proof
        <?php } ?>
    </td>

    <td>
        <form method="POST" enctype="multipart/form-data">

            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">

            <select name="delivery_status" onchange="showProof(this)">
                <option value="Ready for Pickup" <?php if($row['delivery_status']=="Ready for Pickup") echo "selected"; ?>>Ready for Pickup</option>
                <option value="Picked Up" <?php if($row['delivery_status']=="Picked Up") echo "selected"; ?>>Picked Up</option>
                <option value="Out for Delivery" <?php if($row['delivery_status']=="Out for Delivery") echo "selected"; ?>>Out for Delivery</option>
                <option value="Delivered" <?php if($row['delivery_status']=="Delivered") echo "selected"; ?>>Delivered</option>
            </select>

            <div class="upload-container">
                <input type="file" name="proof_image" class="file-input" accept="image/*">
            </div>

            <button type="submit" name="update" class="btn">Update</button>

        </form>
    </td>
</tr>
<?php } ?>

<?php } else { ?>
<tr>
    <td colspan="10" style="text-align:center;">No deliveries found.</td>
</tr>
<?php } ?>

</table>

</div>

</body>
</html>