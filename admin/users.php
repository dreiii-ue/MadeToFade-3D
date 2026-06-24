<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

mysqli_query($conn, "UPDATE users SET account_status='Inactive' WHERE account_status='Active' AND last_login IS NOT NULL AND last_login < DATE_SUB(NOW(), INTERVAL 3 MONTH)");

$edit = false;

if(isset($_POST['add'])){
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $account_status = mysqli_real_escape_string($conn, $_POST['account_status']);

    mysqli_query($conn, "INSERT INTO users(fullname, username, password, role, account_status, last_login) VALUES('$fullname', '$username', '$password', '$role', '$account_status', NOW())");
    header("Location: users.php");
    exit();
}

if(isset($_POST['update'])){
    $id = (int)$_POST['id'];
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $account_status = mysqli_real_escape_string($conn, $_POST['account_status']);

    mysqli_query($conn, "UPDATE users SET fullname='$fullname', username='$username', role='$role', account_status='$account_status' WHERE id='$id'");
    header("Location: users.php");
    exit();
}

if(isset($_GET['toggle'])){
    $id = (int)$_GET['toggle'];
    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT account_status FROM users WHERE id='$id'"));
    if($u){
        $new_status = ($u['account_status'] == 'Active') ? 'Inactive' : 'Active';
        mysqli_query($conn, "UPDATE users SET account_status='$new_status' WHERE id='$id'");
    }
    header("Location: users.php");
    exit();
}

if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
    header("Location: users.php");
    exit();
}

if(isset($_GET['edit'])){
    $edit = true;
    $id = (int)$_GET['edit'];
    $edit_result = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");
    $user_edit = mysqli_fetch_assoc($edit_result);
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$role_filter = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : "";
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : "";

$sql = "SELECT * FROM users WHERE 1=1";

if ($search != "") {
    $sql .= " AND (fullname LIKE '%$search%' OR username LIKE '%$search%' OR id LIKE '%$search%')";
}

if ($role_filter != "") {
    $sql .= " AND role='$role_filter'";
}

if ($status_filter != "") {
    $sql .= " AND account_status='$status_filter'";
}

$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Manage Users</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" type="text/css" href="../css/style.css">
        <link rel="icon" type="image/x-icon" href="../images/favicon.ico">
    </head>
    <body>
        <div class="layout">
            <?php include "sidebar.php"; ?>
            <div class="main-content">
                <div class="topbar">
                    <div class="topbar-left">
                        <i class="fa-solid fa-bars" onclick="toggleSidebar()">
                        </i>
                    </div>
                    <div class="topbar-right">
                        <div class="topbar-item">
                            <i class="fa-regular fa-calendar">
                            </i>
                            <span><?php echo date("M d, Y"); ?></span>
                        </div>
                        <div class="divider">
                        </div>
                        <div class="topbar-item">
                            <i class="fa-regular fa-clock">
                            </i>
                            <span id="clock">
                            </span>
                        </div>
                    </div>
                </div>
                <h1>Manage Users</h1>
                <a href="dashboard.php" class="btn">Back</a>
                <br>
                <br>
                <div class="panel">
                    <p>Users with no login activity for 3 months are automatically marked Inactive. If an inactive user tries to log in, they will be told to contact the administrator at support@madetofade.xyz or 0912 123 1234.</p>
                </div>

                
                <div class="panel">
                    <h2>Add User</h2>
                    <form method="POST" class="product-form">
                        <input type="text" name="fullname" placeholder="Full Name" required>
                        <input type="text" name="username" placeholder="Username" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <select name="role" required>
                            <option value="customer">Customer</option>
                            <option value="courier">Courier</option>
                            <option value="admin">Admin</option>
                        </select>
                        <select name="account_status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        <button type="submit" name="add">Add User</button>
                    </form>
                </div>

                <div class="panel">
                    <h2>Filter Users</h2>
                    <p class="muted-text">Search users by ID, full name, or username. You can also filter by role and account status.</p>

                    <form method="GET" class="filter-wrapper">
                        <input
                            type="text"
                            name="search"
                            placeholder="Search user ID, full name, or username"
                            value="<?php echo htmlspecialchars($search); ?>"
                        >

                        <select name="role">
                            <option value="">All Roles</option>
                            <option value="customer" <?php if ($role_filter == 'customer') echo 'selected'; ?>>Customer</option>
                            <option value="courier" <?php if ($role_filter == 'courier') echo 'selected'; ?>>Courier</option>
                            <option value="admin" <?php if ($role_filter == 'admin') echo 'selected'; ?>>Admin</option>
                        </select>

                        <select name="status">
                            <option value="">All Status</option>
                            <option value="Active" <?php if ($status_filter == 'Active') echo 'selected'; ?>>Active</option>
                            <option value="Inactive" <?php if ($status_filter == 'Inactive') echo 'selected'; ?>>Inactive</option>
                        </select>

                        <button type="submit" class="btn">Filter</button>
                        <a href="users.php" class="btn">Reset</a>
                    </form>
                </div>
                <?php if($edit){ ?>
                <div class="panel">
                    <h2>Edit User</h2>
                    <form method="POST" class="product-form">
                        <input type="hidden" name="id" value="<?php echo $user_edit['id']; ?>">
                        <input type="text" name="fullname" value="<?php echo $user_edit['fullname']; ?>" required>
                        <input type="text" name="username" value="<?php echo $user_edit['username']; ?>" required>
                        <select name="role">
                            <option value="customer" <?php if($user_edit['role']=='customer') echo 'selected'; ?>>Customer</option>
                            <option value="courier" <?php if($user_edit['role']=='courier') echo 'selected'; ?>>Courier</option>
                            <option value="admin" <?php if($user_edit['role']=='admin') echo 'selected'; ?>>Admin</option>
                        </select>
                        <select name="account_status">
                            <option value="Active" <?php if($user_edit['account_status']=='Active') echo 'selected'; ?>>Active</option>
                            <option value="Inactive" <?php if($user_edit['account_status']=='Inactive') echo 'selected'; ?>>Inactive</option>
                        </select>
                        <button type="submit" name="update">Update User</button>
                    </form>
                </div>
                <?php } ?>


                <table>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Action</th>
                    </tr>
                    <?php while($row = mysqli_fetch_assoc($result)){ ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['fullname']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['role']; ?></td>
                        <td>
                            <span class="status <?php echo strtolower($row['account_status']); ?>"><?php echo $row['account_status']; ?></span>
                        </td>
                        <td><?php echo $row['last_login']; ?></td>
                        <td>
                            <a href="users.php?edit=<?php echo $row['id']; ?>" class="btn">Edit</a>
                            <a
                                href="users.php?toggle=<?php echo $row['id']; ?>"
                                class="btn"
                                onclick="return confirm('<?php echo $row['account_status'] == 'Active' ? 'Are you sure you want to deactivate this account?' : 'Are you sure you want to activate this account?'; ?>')"
                            >
                                <?php echo $row['account_status'] == 'Active' ? 'Deactivate' : 'Activate'; ?>
                            </a>
                            <a
                                href="users.php?delete=<?php echo $row['id']; ?>"
                                class="btn"
                                onclick="return confirm('Are you sure you want to delete this user?')"
                            >
                                Delete
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
                
            </div>

            
        </div>
        <script>function updateClock(){let now=new Date();let time=now.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});document.getElementById('clock').innerHTML=time;}updateClock();setInterval(updateClock,1000);function toggleSidebar(){document.querySelector('.sidebar').classList.toggle('hide-sidebar');document.querySelector('.main-content').classList.toggle('full-width');}</script>
    </body>
</html>
