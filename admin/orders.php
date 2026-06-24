<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

function deductStock($conn, $order_id){
    $order_result = mysqli_query($conn,
    "SELECT * FROM orders WHERE id='$order_id'");

    $order = mysqli_fetch_assoc($order_result);

    if($order['stock_deducted'] != "Yes"){

        $order_items = mysqli_query($conn,
        "SELECT * FROM order_items WHERE order_id='$order_id'");

        while($item = mysqli_fetch_assoc($order_items)){
            mysqli_query($conn,
            "UPDATE products
             SET stock = stock - {$item['quantity']}
             WHERE id='{$item['product_id']}'
             AND stock >= {$item['quantity']}");
        }

        mysqli_query($conn,
        "UPDATE orders
         SET stock_deducted='Yes'
         WHERE id='$order_id'");
    }
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

if(isset($_POST['approve_payment'])){
    $order_id = $_POST['order_id'];

    deductStock($conn, $order_id);

    mysqli_query($conn,
    "UPDATE orders 
     SET payment_status='Paid',
         payment_reject_reason=''
     WHERE id='$order_id'");

    header("Location: orders.php");
    exit();
}

if(isset($_POST['reject_payment'])){
    $order_id = $_POST['order_id'];
    $reason = mysqli_real_escape_string($conn, $_POST['reject_reason']);

    mysqli_query($conn,
    "UPDATE orders 
     SET payment_status='Rejected',
         payment_reject_reason='$reason',
         courier_id=NULL,
         order_status='Payment Rejected',
         delivery_status='Preparing'
     WHERE id='$order_id'");

    header("Location: orders.php");
    exit();
}

if(isset($_POST['mark_paid'])){
    $order_id = $_POST['order_id'];

    deductStock($conn, $order_id);

    mysqli_query($conn,
    "UPDATE orders 
     SET payment_status='Paid'
     WHERE id='$order_id'");

    header("Location: orders.php");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$payment = isset($_GET['payment']) ? mysqli_real_escape_string($conn, $_GET['payment']) : "";
$delivery = isset($_GET['delivery']) ? mysqli_real_escape_string($conn, $_GET['delivery']) : "";

$sql = "SELECT orders.*, users.fullname
        FROM orders
        JOIN users ON orders.customer_id = users.id
        WHERE 1=1";

if($search != ""){
    $sql .= " AND (orders.id LIKE '%$search%' OR users.fullname LIKE '%$search%')";
}

if($payment != ""){
    $sql .= " AND orders.payment_status='$payment'";
}

if($delivery != ""){
    $sql .= " AND orders.delivery_status='$delivery'";
}

$sql .= " ORDER BY orders.id DESC";

$orders = mysqli_query($conn, $sql);

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
    <link rel="icon" type="image/x-icon" href="../images/favicon.ico">
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

<div class="panel">
    <h2>Filter Orders</h2>
    <p class="muted-text">
        Search by order ID or customer name. Use the payment and delivery filters to quickly find orders that need action.
    </p>

    <form method="GET" class="filter-wrapper">

        <input type="text"
            name="search"
            placeholder="Search order ID or customer name"
            value="<?php echo $search; ?>">

        <select name="payment">
            <option value="">All Payment Status</option>
            <option value="Pending Verification" <?php if($payment=="Pending Verification") echo "selected"; ?>>Pending Verification</option>
            <option value="Proof Submitted" <?php if($payment=="Proof Submitted") echo "selected"; ?>>Proof Submitted</option>
            <option value="Rejected" <?php if($payment=="Rejected") echo "selected"; ?>>Rejected</option>
            <option value="To Collect" <?php if($payment=="To Collect") echo "selected"; ?>>To Collect</option>
            <option value="Paid" <?php if($payment=="Paid") echo "selected"; ?>>Paid</option>
        </select>

        <select name="delivery">
            <option value="">All Delivery Status</option>
            <option value="Preparing" <?php if($delivery=="Preparing") echo "selected"; ?>>Preparing</option>
            <option value="Ready for Pickup" <?php if($delivery=="Ready for Pickup") echo "selected"; ?>>Ready for Pickup</option>
            <option value="Picked Up" <?php if($delivery=="Picked Up") echo "selected"; ?>>Picked Up</option>
            <option value="Out for Delivery" <?php if($delivery=="Out for Delivery") echo "selected"; ?>>Out for Delivery</option>
            <option value="Delivered" <?php if($delivery=="Delivered") echo "selected"; ?>>Delivered</option>
        </select>

        <button type="submit" class="btn">Filter</button>

        <a href="orders.php" class="btn">Reset</a>

    </form>
</div>

<br>

<table>
<tr>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Total</th>
    <th>Payment</th>
    <th>Proof</th>
    <th>Payment Action</th>
    <th>Order Status</th>
    <th>Delivery Status</th>
    <th>Courier</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($orders)){ ?>
<tr>
    <td>#<?php echo $row['id']; ?></td>

    <td><?php echo $row['fullname']; ?></td>

    <td>₱<?php echo $row['total']; ?></td>

    <td>
        <strong><?php echo $row['payment_method']; ?></strong><br>
        <span class="status <?php echo $row['payment_status'] == 'Paid' ? 'completed' : 'pending'; ?>">
            <?php echo $row['payment_status']; ?>
        </span>
    </td>

    <td>
        <?php if($row['payment_screenshot'] != ""){ ?>
            <a href="../uploads/payments/<?php echo $row['payment_screenshot']; ?>" target="_blank">
                <img src="../uploads/payments/<?php echo $row['payment_screenshot']; ?>" class="proof-img">
            </a>
            <br>
            <small>Ref: <?php echo $row['payment_reference']; ?></small>
        <?php } else { ?>
            No payment proof
        <?php } ?>
    </td>

    <td>
        <?php if($row['payment_status'] == "Proof Submitted"){ ?>

            <form method="POST">
                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                <button type="submit" name="approve_payment" class="btn">Approve</button>
            </form>

            <br>

            <form method="POST">
                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                <input type="text" name="reject_reason" placeholder="Reject reason" required>
                <button type="submit" name="reject_payment" class="btn">Reject</button>
            </form>

        <?php } elseif($row['payment_status'] == "To Collect"){ ?>

            COD

        <?php } elseif($row['payment_status'] != "Paid"){ ?>

            Waiting

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
        <form method="POST" class="assign-form">

            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">

            <select name="courier_id" required>
                <option value="">Select Courier</option>

                <?php
                mysqli_data_seek($couriers, 0);
                while($courier = mysqli_fetch_assoc($couriers)){
                ?>
                    <option value="<?php echo $courier['id']; ?>"
                        <?php if($row['courier_id'] == $courier['id']) echo "selected"; ?>>
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