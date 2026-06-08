<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

$edit = false;

if(isset($_POST['add'])){
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    mysqli_query($conn,
    "INSERT INTO users(fullname, username, password, role)
     VALUES('$fullname', '$username', '$password', '$role')");

    header("Location: users.php");
    exit();
}


if(isset($_POST['update'])){
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $role = $_POST['role'];

    mysqli_query($conn, "UPDATE users SET
                         fullname='$fullname',
                         username='$username',
                         role='$role'
                         WHERE id='$id'");

    header("Location: users.php");
    exit();
}

if(isset($_GET['delete'])){
    $id = $_GET['delete'];

    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");

    header("Location: users.php");
    exit();
}

if(isset($_GET['edit'])){
    $edit = true;
    $id = $_GET['edit'];

    $edit_result = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");
    $user_edit = mysqli_fetch_assoc($edit_result);
}

$result = mysqli_query($conn, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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

<h1>Manage Users</h1>

<a href="dashboard.php" class="btn">Back</a>

<br><br>

<?php if($edit){ ?>



<form method="POST" class="product-form">

    <input type="hidden" name="id" value="<?php echo $user_edit['id']; ?>">

    <input type="text" name="fullname"
           value="<?php echo $user_edit['fullname']; ?>" required>

    <input type="text" name="username"
           value="<?php echo $user_edit['username']; ?>" required>

    <select name="role">
        <option value="customer" <?php if($user_edit['role']=="customer") echo "selected"; ?>>Customer</option>
        <option value="courier" <?php if($user_edit['role']=="courier") echo "selected"; ?>>Courier</option>
        <option value="admin" <?php if($user_edit['role']=="admin") echo "selected"; ?>>Admin</option>
    </select>

    <button type="submit" name="update">Update User</button>

</form>

<br>

<?php } ?>

<form method="POST" class="product-form">

    <input type="text" name="fullname" placeholder="Full Name" required>

    <input type="text" name="username" placeholder="Username" required>

    <input type="password" name="password" placeholder="Password" required>

    <select name="role" required>
        <option value="customer">Customer</option>
        <option value="courier">Courier</option>
        <option value="admin">Admin</option>
    </select>

    <button type="submit" name="add">Add User</button>

</form>

<br>


<table>
<tr>
    <th>ID</th>
    <th>Full Name</th>
    <th>Username</th>
    <th>Role</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['fullname']; ?></td>
    <td><?php echo $row['username']; ?></td>
    <td><?php echo $row['role']; ?></td>
    <td>
        <a href="users.php?edit=<?php echo $row['id']; ?>" class="btn">Edit</a>

        <a href="users.php?delete=<?php echo $row['id']; ?>"
           class="btn"
           onclick="return confirm('Delete this user?')">
           Delete
        </a>
    </td>
</tr>
<?php } ?>

</table>

</div>

</body>

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


</html>