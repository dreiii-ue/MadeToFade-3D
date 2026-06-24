<?php

$conn = mysqli_connect(
    "db.fr-pari1.bengt.wasmernet.com",
    "user_d4399a30",
    "pw_11751dfd",
    "db_10580911",
    10272
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>

