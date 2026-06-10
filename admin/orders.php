<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

if(isset($_POST['assign'])){
    $order_id = $_POST['order_id'];
    $courier_id = $_POST['courier_id'];

    mysqli_query($conn, "UPDATE orders SET
                         courier_id='$courier_id',
                         order_status='Processing',
                         delivery_status='Ready for Pickup'
                         WHERE id='$order_id'");

    header("Location: orders.php");
    exit();
}

if(isset($_POST['mark_paid'])){
    $order_id = $_POST['order_id'];

    mysqli_query($conn,
    "UPDATE orders 
     SET payment_status='Paid'
     WHERE id='$order_id'");

    $order_items = mysqli_query($conn,
    "SELECT * FROM order_items WHERE order_id='$order_id'");

    while($item = mysqli_fetch_assoc($order_items)){
        mysqli_query($conn,
        "UPDATE products
         SET stock = stock - {$item['quantity']}
         WHERE id='{$item['product_id']}'");
    }

    header("Location: orders.php");
    exit();
}

$orders = mysqli_query($conn,
"SELECT orders.*, users.fullname
 FROM orders
 JOIN users ON orders.customer_id = users.id
 ORDER BY orders.id DESC");

$couriers = mysqli_query($conn,
"SELECT * FROM users WHERE role='courier'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders</title>

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>

<div class="layout">

<?php include "sidebar.php"; ?>

<div class="main-content">

    <div class="topbar">

        <div class="topbar-left">
            <i class="fa-solid fa-bars" onclick="toggleSidebar()"></i>
        </div>

        <div class="topbar-right">

            <div class="topbar-item">
                <i class="fa-regular fa-calendar"></i>
                <span><?php echo date("M d, Y"); ?></span>
            </div>

            <div class="divider"></div>

            <div class="topbar-item">
                <i class="fa-regular fa-clock"></i>
                <span id="clock"></span>
            </div>

        </div>

    </div>

<h1>Manage Orders</h1>

<a href="dashboard.php" class="btn">Back</a>

<br><br>
<form method="GET" action="products.php" style="margin-bottom:15px; display:flex; gap:10px; align-items:center;">
  
    <select name="courier_name" class="form-control">
        <option value="">-- Select Courier --</option>
        <
    </select>

    <select name="delivery_status" class="form-control">
        <option value="">-- All Status --</option>
        <option value="pending" <?php if(isset($_GET['delivery_status']) && $_GET['delivery_status']=="pending") echo "selected"; ?>>Pending</option>
        <option value="delivered" <?php if(isset($_GET['delivery_status']) && $_GET['delivery_status']=="delivered") echo "selected"; ?>>Delivered</option>
        <option value="cancelled" <?php if(isset($_GET['delivery_status']) && $_GET['delivery_status']=="cancelled") echo "selected"; ?>>Cancelled</option>
    </select>

    <button type="submit" class="btn btn-dark">Search</button>

    <a href="products.php" class="btn btn-link text-danger">Reset</a>
</form>

<table>
<tr>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Total</th>
    <th>Address</th>
    <th>Contact</th>
    <th>Payment Method</th>
    <th>Payment Status</th>
    <th>Payment Action</th>
    <th>Order Status</th>
    <th>Delivery Status</th>
    <th>Assign Courier</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($orders)){ ?>
<tr>
    <td>#<?php echo $row['id']; ?></td>

    <td><?php echo $row['fullname']; ?></td>

    <td>₱<?php echo $row['total']; ?></td>

    <td><?php echo $row['address']; ?></td>

    <td><?php echo $row['contact_number']; ?></td>

    <td><?php echo $row['payment_method']; ?></td>

    <td>
        <span class="status <?php echo $row['payment_status'] == 'Paid' ? 'completed' : 'pending'; ?>">
            <?php echo $row['payment_status']; ?>
        </span>
    </td>

    <td>
        <?php if($row['payment_status'] != "Paid"){ ?>
            <form method="POST">
                <input type="hidden"
                       name="order_id"
                       value="<?php echo $row['id']; ?>">

                <button type="submit"
                        name="mark_paid"
                        class="btn">
                    Mark Paid
                </button>
            </form>
        <?php } else { ?>
            Paid
        <?php } ?>
    </td>

    <td>
        <?php
        $class = "pending";

        if($row['order_status'] == "Processing"){
            $class = "processing";
        }

        if($row['order_status'] == "Completed"){
            $class = "completed";
        }
        ?>

        <span class="status <?php echo $class; ?>">
            <?php echo $row['order_status']; ?>
        </span>
    </td>

    <td>
        <?php
        $delivery_class = "preparing";

        if($row['delivery_status'] == "Ready for Pickup"){
            $delivery_class = "pickup";
        }

        if($row['delivery_status'] == "Picked Up"){
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
        <form method="POST">

            <input type="hidden"
                   name="order_id"
                   value="<?php echo $row['id']; ?>">

            <select name="courier_id" required>
                <option value="">Select Courier</option>

                <?php
                mysqli_data_seek($couriers, 0);

                while($courier = mysqli_fetch_assoc($couriers)){
                ?>
                    <option value="<?php echo $courier['id']; ?>">
                        <?php echo $courier['fullname']; ?>
                    </option>
                <?php } ?>
            </select>

            <button type="submit" name="assign">
                Assign
            </button>

        </form>
    </td>

    <td>
        <a href="order_details.php?id=<?php echo $row['id']; ?>" class="btn">
            View
        </a>
    </td>
</tr>
<?php } ?>

</table>

</div>

</div>

<script>
function updateClock(){

    let now = new Date();

    let time = now.toLocaleTimeString([],{
        hour:'2-digit',
        minute:'2-digit'
    });

    document.getElementById("clock").innerHTML = time;
}

updateClock();
setInterval(updateClock,1000);

function toggleSidebar(){
    document.querySelector(".sidebar").classList.toggle("hide-sidebar");
    document.querySelector(".main-content").classList.toggle("full-width");
}
</script>

</body>
</html>