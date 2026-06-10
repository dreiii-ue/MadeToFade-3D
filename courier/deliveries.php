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

    // Handle the file upload process if the courier selects "Delivered"
    if($delivery_status == "Delivered" && isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == 0){
        
        // Define where the uploaded proof photos will be saved
        $target_dir = "../uploads/proofs/";
        
        // Automatically create the folder if it does not exist yet
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Get the file extension (e.g., jpg, jpeg, png)
        $image_ext = strtolower(pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION));
        
        // Name the file based on the unique Order ID (e.g., proof_1.jpg)
        $new_filename = "proof_" . $order_id . "." . $image_ext;
        $target_file = $target_dir . $new_filename;

        // Move the uploaded file from temporary storage to your target folder
        move_uploaded_file($_FILES['proof_image']['tmp_name'], $target_file);
    }

    mysqli_query($conn,
    "UPDATE orders
     SET delivery_status='$delivery_status'
     WHERE id='$order_id'");

    if($delivery_status == "Delivered"){

        mysqli_query($conn,
        "UPDATE orders
         SET order_status='Completed'
         WHERE id='$order_id'");
    }

    // Keep active filters in the URL after updating
    $redirect_url = "deliveries.php";
    if(!empty($_SERVER['QUERY_STRING'])) {
        $redirect_url .= "?" . $_SERVER['QUERY_STRING'];
    }
    header("Location: " . $redirect_url);
    exit();
}

$courier_id = $_SESSION['user_id'];

// Get filter inputs from the form
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Base query to fetch courier deliveries
$sql = "SELECT orders.*, users.fullname
        FROM orders
        JOIN users ON orders.customer_id = users.id
        WHERE orders.courier_id='$courier_id'";

if(!empty($search_query)){
    $sql .= " AND (users.fullname LIKE '%$search_query%' OR orders.id LIKE '%$search_query%')";
}

if(!empty($status_filter)){
    $sql .= " AND orders.delivery_status='$status_filter'";
}

$sql .= " ORDER BY orders.id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Courier Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* UI and Layout styles for Search/Filter and Images */
        .filter-wrapper {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .filter-wrapper input[type="text"], .filter-wrapper select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .filter-wrapper button {
            padding: 8px 15px;
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .filter-wrapper a {
            color: #cc0000;
            text-decoration: none;
            font-size: 14px;
        }
        .proof-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .upload-container {
            margin-top: 6px;
            display: none; /* Hidden by default, shown via JavaScript */
        }
        .file-input {
            font-size: 11px;
            display: block;
            margin-bottom: 5px;
        }
    </style>
    <script>
        // Shows the upload file input field ONLY when "Delivered" is chosen
        function checkStatusSelection(selectElement) {
            var form = selectElement.closest('form');
            var uploadContainer = form.querySelector('.upload-container');
            var fileInput = form.querySelector('.file-input');
            
            if (selectElement.value === 'Delivered') {
                uploadContainer.style.display = 'block';
                fileInput.required = true; // Makes file upload mandatory for delivery completion
            } else {
                uploadContainer.style.display = 'none';
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

<form method="GET" action="deliveries.php" class="filter-wrapper">
    <input type="text" name="search" placeholder="Search ID or Customer..." value="<?php echo htmlspecialchars($search_query); ?>">
    
    <select name="status">
        <option value="">-- All Status --</option>
        <option value="Ready for Pickup" <?php if($status_filter == 'Ready for Pickup') echo 'selected'; ?>>Ready for Pickup</option>
        <option value="Picked Up" <?php if($status_filter == 'Picked Up') echo 'selected'; ?>>Picked Up</option>
        <option value="Out for Delivery" <?php if($status_filter == 'Out for Delivery') echo 'selected'; ?>>Out for Delivery</option>
        <option value="Delivered" <?php if($status_filter == 'Delivered') echo 'selected'; ?>>Delivered</option>
    </select>

    <button type="submit">Search</button>

    <?php if(!empty($search_query) || !empty($status_filter)){ ?>
        <a href="deliveries.php">Reset</a>
    <?php } ?>
</form>

<table>

<tr>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Total</th>
    <th>Payment Method</th>
    <th>Payment Status</th>
    <th>Address</th>
    <th>Contact</th>
    <th>Status</th>
    <th>Proof of Delivery</th> <th>Update</th>
</tr>

<?php if(mysqli_num_rows($result) > 0) { ?>
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
        <td><?php echo $row['delivery_status']; ?></td>

        <td>
            <?php 
            // Look for matching files dynamically inside your uploads directory based on Order ID
            $extensions = ['jpg', 'jpeg', 'png'];
            $file_found = false;
            
            foreach($extensions as $ext) {
                $file_path = "../uploads/proofs/proof_" . $row['id'] . "." . $ext;
                if(file_exists($file_path)) {
                    echo '<a href="'.$file_path.'" target="_blank"><img src="'.$file_path.'" class="proof-img" alt="Proof Photo"></a>';
                    $file_found = true;
                    break;
                }
            }
            
            if(!$file_found) {
                echo '<span style="color:#aaa; font-size:12px;">No proof uploaded</span>';
            }
            ?>
        </td>

        <td>
            <form method="POST" enctype="multipart/form-data">

                <input type="hidden"
                       name="order_id"
                       value="<?php echo $row['id']; ?>">

                <select name="delivery_status" onchange="checkStatusSelection(this)">
                    <option value="Ready for Pickup" <?php if($row['delivery_status'] == 'Ready for Pickup') echo 'selected'; ?>>Ready for Pickup</option>
                    <option value="Picked Up" <?php if($row['delivery_status'] == 'Picked Up') echo 'selected'; ?>>Picked Up</option>
                    <option value="Out for Delivery" <?php if($row['delivery_status'] == 'Out for Delivery') echo 'selected'; ?>>Out for Delivery</option>
                    <option value="Delivered" <?php if($row['delivery_status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                </select>

                <div class="upload-container">
                    <input type="file" name="proof_image" class="file-input" accept="image/*">
                </div>

                <button type="submit" name="update" class="btn" style="margin-top: 5px;">
                    Update
                </button>

            </form>
        </td>

    </tr>

    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="10" style="text-align: center; padding: 20px; color: #999;">No deliveries found.</td>
    </tr>
<?php } ?>

</table>

</div>

</body>
</html>