<?php
session_start();
require_once '../database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: apex26admin.php");
    exit;
}

// Sanitize inputs
$name = trim($_POST['product_name'] ?? '');
$brand_id = intval($_POST['brand_id'] ?? 0);
$category_id = intval($_POST['category_id'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$badge = !empty($_POST['badge']) ? trim($_POST['badge']) : NULL;
$badge_type = !empty($_POST['badge_type']) ? trim($_POST['badge_type']) : NULL;
$desc = trim($_POST['desc'] ?? '');

$db_image_path = "assets/images/placeholder.png";

// Handle Image Upload
if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0) {
    $target_dir = "../assets/images/uploads/";

    // Create directory if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $clean_file_name = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES["product_image"]["name"]));
    $unique_file_name = time() . "_" . $clean_file_name;
    $target_file = $target_dir . $unique_file_name;

    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        $db_image_path = "assets/images/uploads/" . $unique_file_name;
    }
}

// Insert into Database
$sql = "INSERT INTO products_tbl (name, brand_id, category_id, price, stock, badge, badge_type, image, `desc`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("siidissss", $name, $brand_id, $category_id, $price, $stock, $badge, $badge_type, $db_image_path, $desc);

if ($stmt->execute()) {
    $_SESSION['admin_success'] = "Product successfully added to the database!";
} else {
    $_SESSION['admin_error'] = "Error saving product: " . $conn->error;
}

$stmt->close();
header("Location: apex26admin.php");
exit;
