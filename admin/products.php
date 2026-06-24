<?php
session_start();
include "../includes/config.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

$edit = false;

mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);

$default_categories = [
    "T-Shirt",
    "Cropped T-Shirt",
    "Jacket",
    "Pants",
    "Shorts"
];

foreach ($default_categories as $default_category) {
    $safe_category = mysqli_real_escape_string($conn, $default_category);

    mysqli_query(
        $conn,
        "INSERT IGNORE INTO categories(name)
         VALUES('$safe_category')"
    );
}

function uploadImage($input_name)
{
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
        die("Invalid image type.");
    }

    $new_name = time() . "_" . rand(1000, 9999) . "." . $ext;

    move_uploaded_file($tmp, "../images/" . $new_name);

    return $new_name;
}

if (isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);

    if ($category_name == "") {
        $category_error = "Category name is required.";
    } else {
        mysqli_query(
            $conn,
            "INSERT IGNORE INTO categories(name)
             VALUES('$category_name')"
        );

        $category_success = "Category added successfully.";
    }
}

if (isset($_GET['delete_category'])) {
    $category_id = (int)$_GET['delete_category'];

    $category_result = mysqli_query(
        $conn,
        "SELECT * FROM categories
         WHERE id='$category_id'"
    );

    $category_data = mysqli_fetch_assoc($category_result);

    if ($category_data) {
        $category_name = mysqli_real_escape_string($conn, $category_data['name']);

        $used_result = mysqli_query(
            $conn,
            "SELECT COUNT(*) AS total
             FROM products
             WHERE category='$category_name'"
        );

        $used = mysqli_fetch_assoc($used_result);

        if ($used['total'] > 0) {
            $category_error = "Cannot delete category because it is used by products.";
        } else {
            mysqli_query(
                $conn,
                "DELETE FROM categories
                 WHERE id='$category_id'"
            );

            header("Location: products.php");
            exit();
        }
    }
}

if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $size = mysqli_real_escape_string($conn, $_POST['size']);

    $sku = "MTF-" . strtoupper(str_replace(" ", "", $category)) . "-" . strtoupper($color) . "-" . strtoupper($size) . "-" . rand(1000, 9999);

    $image = uploadImage("image");

    mysqli_query(
        $conn,
        "INSERT INTO products(name, price, stock, image, category, sku, color, size)
         VALUES('$name', '$price', '$stock', '$image', '$category', '$sku', '$color', '$size')"
    );

    header("Location: products.php");
    exit();
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $size = mysqli_real_escape_string($conn, $_POST['size']);

    $sku = "MTF-" . strtoupper(str_replace(" ", "", $category)) . "-" . strtoupper($color) . "-" . strtoupper($size) . "-" . rand(1000, 9999);

    if ($_FILES['image']['name'] != "") {
        $image = uploadImage("image");

        mysqli_query(
            $conn,
            "UPDATE products SET
                name='$name',
                price='$price',
                stock='$stock',
                image='$image',
                category='$category',
                sku='$sku',
                color='$color',
                size='$size'
             WHERE id='$id'"
        );
    } else {
        mysqli_query(
            $conn,
            "UPDATE products SET
                name='$name',
                price='$price',
                stock='$stock',
                category='$category',
                sku='$sku',
                color='$color',
                size='$size'
             WHERE id='$id'"
        );
    }

    header("Location: products.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    mysqli_query(
        $conn,
        "DELETE FROM products
         WHERE id='$id'"
    );

    header("Location: products.php");
    exit();
}

if (isset($_GET['edit'])) {
    $edit = true;
    $id = $_GET['edit'];

    $edit_result = mysqli_query(
        $conn,
        "SELECT * FROM products
         WHERE id='$id'"
    );

    $product = mysqli_fetch_assoc($edit_result);
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : "";
$stock_filter = isset($_GET['stock_filter']) ? mysqli_real_escape_string($conn, $_GET['stock_filter']) : "";

$sql = "SELECT * FROM products WHERE 1=1";

if ($search != "") {
    $sql .= " AND (name LIKE '%$search%' OR sku LIKE '%$search%' OR color LIKE '%$search%')";
}

if ($category_filter != "") {
    $sql .= " AND category='$category_filter'";
}

if ($stock_filter == "low") {
    $sql .= " AND stock <= 5";
}

if ($stock_filter == "available") {
    $sql .= " AND stock > 5";
}

$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

$categories = mysqli_query(
    $conn,
    "SELECT * FROM categories
     ORDER BY name ASC"
);

$form_categories = mysqli_query(
    $conn,
    "SELECT * FROM categories
     ORDER BY name ASC"
);

$filter_categories = mysqli_query(
    $conn,
    "SELECT * FROM categories
     ORDER BY name ASC"
);

$category_list = mysqli_query(
    $conn,
    "SELECT c.*,
        (
            SELECT COUNT(*)
            FROM products p
            WHERE p.category COLLATE utf8mb4_general_ci = c.name COLLATE utf8mb4_general_ci
        ) AS product_count
     FROM categories c
     ORDER BY c.name ASC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../images/favicon.ico">
</head>
<body>

<div class="layout">

<?php include "sidebar.php"; ?>

<div class="main-content">

<?php include "topbar.php"; ?>

<h1>Inventory Management</h1>



    <div class="panel">
        <h2><?php echo $edit ? "Edit Product" : "Add Product"; ?></h2>

        <form method="POST" enctype="multipart/form-data" class="product-form">

            <input type="hidden" name="id" value="<?php echo $edit ? $product['id'] : ''; ?>">

            <input
                type="text"
                name="name"
                placeholder="Product Name"
                value="<?php echo $edit ? $product['name'] : ''; ?>"
                required
            >

            <input
                type="number"
                name="price"
                placeholder="Price"
                value="<?php echo $edit ? $product['price'] : ''; ?>"
                required
            >

            <input
                type="number"
                name="stock"
                placeholder="Stock"
                value="<?php echo $edit ? $product['stock'] : ''; ?>"
                required
            >

            <select name="category" required>
                <option value="">Select Category</option>

                <?php while ($cat = mysqli_fetch_assoc($form_categories)) { ?>
                    <option
                        value="<?php echo $cat['name']; ?>"
                        <?php if ($edit && $product['category'] == $cat['name']) echo "selected"; ?>
                    >
                        <?php echo $cat['name']; ?>
                    </option>
                <?php } ?>
            </select>

            <input
                type="text"
                name="color"
                placeholder="Color"
                value="<?php echo $edit ? $product['color'] : ''; ?>"
                required
            >

            <select name="size" required>
                <option value="">Size</option>
                <option value="XS" <?php if ($edit && $product['size'] == "XS") echo "selected"; ?>>XS</option>
                <option value="S" <?php if ($edit && $product['size'] == "S") echo "selected"; ?>>S</option>
                <option value="M" <?php if ($edit && $product['size'] == "M") echo "selected"; ?>>M</option>
                <option value="L" <?php if ($edit && $product['size'] == "L") echo "selected"; ?>>L</option>
                <option value="XL" <?php if ($edit && $product['size'] == "XL") echo "selected"; ?>>XL</option>
            </select>

            <input type="file" name="image" <?php if (!$edit) echo "required"; ?>>

            <button type="submit" name="<?php echo $edit ? 'update' : 'add'; ?>" class="btn">
                <?php echo $edit ? 'Update Product' : 'Add Product'; ?>
            </button>

        </form>
    </div>

    <div class="panel">
        <h2>Add Category</h2>
        <p class="muted-text">Create a new product category for your inventory.</p>

        <?php if (isset($category_success)) { ?>
            <p class="success-msg"><?php echo $category_success; ?></p>
        <?php } ?>

        <?php if (isset($category_error)) { ?>
            <p class="error-msg"><?php echo $category_error; ?></p>
        <?php } ?>

        <form method="POST" class="profile-form">
            <label>Category Name</label>

            <input
                type="text"
                name="category_name"
                placeholder="Example: Accessories"
                required
            >

            <button type="submit" name="add_category" class="btn">
                Add Category
            </button>
        </form>

        <br>

        <h3>Current Categories</h3>

        <div class="address-list">
            <?php while ($cat = mysqli_fetch_assoc($category_list)) { ?>
                <div class="address-item">
                    <div>
                        <strong><?php echo $cat['name']; ?></strong>
                        <br>
                        <small><?php echo $cat['product_count']; ?> products</small>
                    </div>

                    <?php if ($cat['product_count'] == 0) { ?>
                        <a
                            href="products.php?delete_category=<?php echo $cat['id']; ?>"
                            class="btn small-btn"
                            onclick="return confirm('Delete this category?');"
                        >
                            Delete
                        </a>
                    <?php } else { ?>
                        <span class="badge">In Use</span>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>


<div class="panel">

    <h2>Filter Products</h2>

    <form method="GET" class="filter-wrapper">

        <input
            type="text"
            name="search"
            placeholder="Search product, SKU, color"
            value="<?php echo $search; ?>"
        >

        <select name="category">
            <option value="">All Categories</option>

            <?php while ($cat = mysqli_fetch_assoc($filter_categories)) { ?>
                <option
                    value="<?php echo $cat['name']; ?>"
                    <?php if ($category_filter == $cat['name']) echo "selected"; ?>
                >
                    <?php echo $cat['name']; ?>
                </option>
            <?php } ?>
        </select>

        <select name="stock_filter">
            <option value="">All Stock</option>
            <option value="low" <?php if ($stock_filter == "low") echo "selected"; ?>>Low Stock</option>
            <option value="available" <?php if ($stock_filter == "available") echo "selected"; ?>>Available Stock</option>
        </select>

        <button type="submit" class="btn">Filter</button>

        <a href="products.php" class="btn">Reset</a>

    </form>
</div>

<div class="table-responsive">
    <table>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>SKU</th>
            <th>Product</th>
            <th>Category</th>
            <th>Color</th>
            <th>Size</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Action</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>

                <td>
                    <img src="../images/<?php echo $row['image']; ?>" width="70">
                </td>

                <td><?php echo $row['sku']; ?></td>

                <td><?php echo $row['name']; ?></td>

                <td>
                    <span class="badge">
                        <?php echo $row['category']; ?>
                    </span>
                </td>

                <td><?php echo $row['color']; ?></td>

                <td><?php echo $row['size']; ?></td>

                <td>₱<?php echo $row['price']; ?></td>

                <td><?php echo $row['stock']; ?></td>

                <td>
                    <a href="products.php?edit=<?php echo $row['id']; ?>" class="btn">
                        Edit
                    </a>

                    <a
                        href="products.php?delete=<?php echo $row['id']; ?>"
                        class="btn"
                        onclick="return confirm('Delete product?')"
                    >
                        Delete
                    </a>
                </td>
            </tr>
        <?php } ?>

    </table>
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