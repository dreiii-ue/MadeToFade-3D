<?php
session_start();
include "../includes/config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

$edit = false;

if(isset($_POST['add'])){

    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];
    $color = $_POST['color'];
    $size = $_POST['size'];

    $sku = "MTF-" . strtoupper(str_replace(" ", "", $category)) . "-" . strtoupper($color) . "-" . strtoupper($size) . "-" . rand(1000,9999);

    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];

    move_uploaded_file($tmp, "../images/".$image);

    mysqli_query($conn,
    "INSERT INTO products(name, price, stock, image, category, sku, color, size)
     VALUES('$name', '$price', '$stock', '$image', '$category', '$sku', '$color', '$size')");

    header("Location: products.php");
    exit();
}

if(isset($_POST['update'])){

    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];
    $color = $_POST['color'];
    $size = $_POST['size'];

    $sku = "MTF-" . strtoupper(str_replace(" ", "", $category)) . "-" . strtoupper($color) . "-" . strtoupper($size) . "-" . rand(1000,9999);

    if($_FILES['image']['name'] != ""){

        $image = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];

        move_uploaded_file($tmp, "../images/".$image);

        mysqli_query($conn,
        "UPDATE products SET
         name='$name',
         price='$price',
         stock='$stock',
         image='$image',
         category='$category',
         sku='$sku',
         color='$color',
         size='$size'
         WHERE id='$id'");
    }
    else{
        mysqli_query($conn,
        "UPDATE products SET
         name='$name',
         price='$price',
         stock='$stock',
         category='$category',
         sku='$sku',
         color='$color',
         size='$size'
         WHERE id='$id'");
    }

    header("Location: products.php");
    exit();
}

if(isset($_GET['delete'])){
    $id = $_GET['delete'];

    mysqli_query($conn, "DELETE FROM products WHERE id='$id'");

    header("Location: products.php");
    exit();
}

if(isset($_GET['edit'])){
    $edit = true;
    $id = $_GET['edit'];

    $edit_result = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
    $product = mysqli_fetch_assoc($edit_result);
}

$result = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="layout">

<?php include "sidebar.php"; ?>

<div class="main-content">

<h1>Inventory Management</h1>

<form method="POST" enctype="multipart/form-data" class="product-form">

    <input type="hidden" name="id" value="<?php echo $edit ? $product['id'] : ''; ?>">

    <input type="text" name="name" placeholder="Product Name"
           value="<?php echo $edit ? $product['name'] : ''; ?>" required>

    <input type="number" name="price" placeholder="Price"
           value="<?php echo $edit ? $product['price'] : ''; ?>" required>

    <input type="number" name="stock" placeholder="Stock"
           value="<?php echo $edit ? $product['stock'] : ''; ?>" required>

    <select name="category" required>
        <option value="">Select Category</option>
        <option value="T-Shirt" <?php if($edit && $product['category']=="T-Shirt") echo "selected"; ?>>T-Shirt</option>
        <option value="Cropped T-Shirt" <?php if($edit && $product['category']=="Cropped T-Shirt") echo "selected"; ?>>Cropped T-Shirt</option>
        <option value="Jacket" <?php if($edit && $product['category']=="Jacket") echo "selected"; ?>>Jacket</option>
        <option value="Pants" <?php if($edit && $product['category']=="Pants") echo "selected"; ?>>Pants</option>
        <option value="Shorts" <?php if($edit && $product['category']=="Shorts") echo "selected"; ?>>Shorts</option>
    </select>

    <input type="text" name="color" placeholder="Color"
           value="<?php echo $edit ? $product['color'] : ''; ?>" required>

    <select name="size" required>
        <option value="">Size</option>
        <option value="XS" <?php if($edit && $product['size']=="XS") echo "selected"; ?>>XS</option>
        <option value="S" <?php if($edit && $product['size']=="S") echo "selected"; ?>>S</option>
        <option value="M" <?php if($edit && $product['size']=="M") echo "selected"; ?>>M</option>
        <option value="L" <?php if($edit && $product['size']=="L") echo "selected"; ?>>L</option>
        <option value="XL" <?php if($edit && $product['size']=="XL") echo "selected"; ?>>XL</option>
    </select>

    <input type="file" name="image">

    <button type="submit" name="<?php echo $edit ? 'update' : 'add'; ?>" class="btn">
        <?php echo $edit ? 'Update Product' : 'Add Product'; ?>
    </button>

</form>

<br>

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

<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
    <td><?php echo $row['id']; ?></td>
    <td><img src="../images/<?php echo $row['image']; ?>" width="70"></td>
    <td><?php echo $row['sku']; ?></td>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['category']; ?></td>
    <td><?php echo $row['color']; ?></td>
    <td><?php echo $row['size']; ?></td>
    <td>₱<?php echo $row['price']; ?></td>
    <td><?php echo $row['stock']; ?></td>
    <td>
        <a href="products.php?edit=<?php echo $row['id']; ?>" class="btn">Edit</a>

        <a href="products.php?delete=<?php echo $row['id']; ?>"
           class="btn"
           onclick="return confirm('Delete product?')">
           Delete
        </a>
    </td>
</tr>
<?php } ?>

</table>

</div>
</div>

</body>
</html>