<?php
session_start();
include "../includes/config.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

function columnExists($conn, $table, $column)
{
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);

    $result = mysqli_query(
        $conn,
        "SHOW COLUMNS FROM `$table` LIKE '$column'"
    );

    return $result && mysqli_num_rows($result) > 0;
}

function ensureReorderColumns($conn)
{
    if (!columnExists($conn, 'reorder_requests', 'reorder_amount')) {
        mysqli_query(
            $conn,
            "ALTER TABLE reorder_requests
             ADD COLUMN reorder_amount INT(11) NOT NULL DEFAULT 1 AFTER supplier_email"
        );
    }

    if (!columnExists($conn, 'reorder_requests', 'stock_added')) {
        mysqli_query(
            $conn,
            "ALTER TABLE reorder_requests
             ADD COLUMN stock_added VARCHAR(5) NOT NULL DEFAULT 'No' AFTER status"
        );
    }

    if (!columnExists($conn, 'reorder_requests', 'completed_at')) {
        mysqli_query(
            $conn,
            "ALTER TABLE reorder_requests
             ADD COLUMN completed_at DATETIME DEFAULT NULL AFTER created_at"
        );
    }
}

ensureReorderColumns($conn);

if (isset($_POST['submit_reorder'])) {
    $product_id = (int)$_POST['product_id'];
    $supplier_name = mysqli_real_escape_string($conn, $_POST['supplier_name']);
    $supplier_email = mysqli_real_escape_string($conn, $_POST['supplier_email']);
    $reorder_amount = (int)$_POST['reorder_amount'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    if ($reorder_amount <= 0) {
        $error = "Reorder amount must be at least 1.";
    } else {
        $insert_result = mysqli_query(
            $conn,
            "INSERT INTO reorder_requests(
                product_id,
                supplier_name,
                supplier_email,
                reorder_amount,
                message,
                status,
                stock_added
             )
             VALUES(
                '$product_id',
                '$supplier_name',
                '$supplier_email',
                '$reorder_amount',
                '$message',
                'Pending',
                'No'
             )"
        );

        if ($insert_result) {
            $success = "Reorder request submitted.";
        } else {
            $error = "Reorder request failed: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['complete'])) {
    $id = (int)$_GET['complete'];

    $request_result = mysqli_query(
        $conn,
        "SELECT * FROM reorder_requests
         WHERE id='$id'
         LIMIT 1"
    );

    $request = mysqli_fetch_assoc($request_result);

    if ($request && $request['status'] != 'Completed') {
        $product_id = (int)$request['product_id'];
        $reorder_amount = (int)$request['reorder_amount'];

        if ($request['stock_added'] != 'Yes') {
            mysqli_query(
                $conn,
                "UPDATE products
                 SET stock = stock + $reorder_amount
                 WHERE id='$product_id'"
            );
        }

        mysqli_query(
            $conn,
            "UPDATE reorder_requests
             SET status='Completed',
                 stock_added='Yes',
                 completed_at=NOW()
             WHERE id='$id'"
        );
    }

    header("Location: reorders.php");
    exit();
}

$low_products = mysqli_query(
    $conn,
    "SELECT * FROM products
     WHERE stock <= 5
     ORDER BY stock ASC"
);

$low_products_list = mysqli_query(
    $conn,
    "SELECT * FROM products
     WHERE stock <= 5
     ORDER BY stock ASC
     LIMIT 8"
);

$low_stock_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE stock <= 5")
);

$pending_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM reorder_requests WHERE status='Pending'")
);

$completed_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM reorder_requests WHERE status='Completed'")
);

$requests = mysqli_query(
    $conn,
    "SELECT reorder_requests.*, products.name, products.stock
     FROM reorder_requests
     JOIN products ON reorder_requests.product_id = products.id
     ORDER BY reorder_requests.id DESC"
);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Reorders</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="../css/style.css">
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

                <div class="reorder-hero">
                    <div>
                        <h1>Reorder System</h1>
                        <p>Create supplier reorder requests when products are low on stock.</p>
                    </div>

                    <a href="products.php?stock_filter=low" class="btn">View Low Stock</a>
                </div>

                <?php if (isset($success)) { ?>
                    <p class="success-msg"><?php echo $success; ?></p>
                <?php } ?>

                <?php if (isset($error)) { ?>
                    <p class="error-msg"><?php echo $error; ?></p>
                <?php } ?>

                <div class="reorder-summary-grid">
                    <div class="reorder-summary-card">
                        <h3>Low Stock Products</h3>
                        <h2><?php echo $low_stock_count['total']; ?></h2>
                    </div>

                    <div class="reorder-summary-card">
                        <h3>Pending Requests</h3>
                        <h2><?php echo $pending_count['total']; ?></h2>
                    </div>

                    <div class="reorder-summary-card">
                        <h3>Completed Requests</h3>
                        <h2><?php echo $completed_count['total']; ?></h2>
                    </div>
                </div>

                <div class="reorder-layout">
                    <div class="panel">
                        <h2>Low Stock List</h2>
                        <p class="muted-text">Products with 5 or fewer stocks will appear here.</p>

                        <div class="low-stock-list">
                            <?php if (mysqli_num_rows($low_products_list) > 0) { ?>
                                <?php while ($p = mysqli_fetch_assoc($low_products_list)) { ?>
                                    <div class="low-stock-item">
                                        <div>
                                            <strong><?php echo $p['name']; ?></strong>
                                            <small><?php echo $p['sku']; ?></small>
                                        </div>

                                        <div class="low-stock-number">
                                            <?php echo $p['stock']; ?> left
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } else { ?>
                                <p>No low stock products.</p>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="panel">
                        <h2>Submit Reorder Request</h2>
                        <p class="muted-text">Fill out the supplier details and request message.</p>

                        <form method="POST" class="reorder-form">
                            <label>Low Stock Product</label>
                            <select name="product_id" required>
                                <option value="">Select Product</option>
                                <?php while ($p = mysqli_fetch_assoc($low_products)) { ?>
                                    <option value="<?php echo $p['id']; ?>">
                                        <?php echo $p['name']; ?> - <?php echo $p['stock']; ?> left
                                    </option>
                                <?php } ?>
                            </select>

                            <label>Supplier Name</label>
                            <input type="text" name="supplier_name" placeholder="Supplier name" required>

                            <label>Reorder Amount</label>
                            <input
                                type="number"
                                name="reorder_amount"
                                placeholder="Example: 20"
                                min="1"
                                required
                            >

                            <label>Supplier Email</label>
                            <input type="email" name="supplier_email" placeholder="supplier@email.com" required>

                            <label>Message / Notes</label>
                            <textarea name="message" placeholder="Example: Please send another batch of this product." required></textarea>

                            <button type="submit" name="submit_reorder" class="btn">Submit Reorder</button>
                        </form>
                    </div>
                </div>

                <div class="panel">
                    <h2>Reorder Request History</h2>

                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Supplier</th>
                            <th>Email</th>
                            <th>Amount</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>

                        <?php while ($r = mysqli_fetch_assoc($requests)) { ?>
                            <tr>
                                <td>#<?php echo $r['id']; ?></td>

                                <td>
                                    <?php echo $r['name']; ?>
                                    <br>
                                    <small><?php echo $r['stock']; ?> left</small>
                                </td>

                                <td><?php echo $r['supplier_name']; ?></td>
                                <td><?php echo $r['supplier_email']; ?></td>

                                <td>
                                    <strong><?php echo $r['reorder_amount']; ?></strong>
                                    <br>
                                    <small>pcs</small>
                                </td>

                                <td><?php echo $r['message']; ?></td>

                                <td>
                                    <span class="status <?php echo $r['status'] == 'Completed' ? 'completed' : 'pending'; ?>">
                                        <?php echo $r['status']; ?>
                                    </span>
                                </td>

                                <td><?php echo $r['created_at']; ?></td>

                                <td>
                                    <?php if ($r['status'] != 'Completed') { ?>
                                        <a
                                            class="btn"
                                            href="reorders.php?complete=<?php echo $r['id']; ?>"
                                            onclick="return confirm('Mark this reorder as completed? The reorder amount will be added to product stock automatically.');"
                                        >
                                            Complete
                                        </a>
                                    <?php } else { ?>
                                        <span class="status completed">Stock Added</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
        <script>
            function updateClock() {
                let now = new Date();
                let time = now.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                document.getElementById("clock").innerHTML = time;
            }

            updateClock();
            setInterval(updateClock, 1000);

            function toggleSidebar() {
                document.querySelector(".sidebar").classList.toggle("hide-sidebar");
                document.querySelector(".main-content").classList.toggle("full-width");
            }
        </script>
    </body>
</html>
