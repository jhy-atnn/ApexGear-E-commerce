<?php
require_once __DIR__ . '/../includes/storage.php';
require_once __DIR__ . '/../classes/Inventory.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: apex26admin.php");
    exit;
}

$name = trim($_POST['product_name'] ?? '');
$brand_id = intval($_POST['brand_id'] ?? 0);
$category_id = intval($_POST['category_id'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$badge = !empty($_POST['badge']) ? trim($_POST['badge']) : NULL;
$badge_type = !empty($_POST['badge_type']) ? trim($_POST['badge_type']) : NULL;
$desc = trim($_POST['desc'] ?? ''); // <--- Grab the description

$db_image_path = "https://placehold.co/400x300/eeeeee/1D1D1F?text=No+Image";

// Handle Image Upload
if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0) {
    $target_dir = "../assets/images/products/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $clean_file_name = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES["product_image"]["name"]));
    $unique_file_name = time() . "_" . $clean_file_name;
    $target_file = $target_dir . $unique_file_name;

    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        $db_image_path = "assets/images/products/" . $unique_file_name;
    }
}

/** @var Inventory $inv */
$inv = new Inventory();
$newId = $inv->addProduct($name, null, null, $price, null, $stock, 0, $badge, $badge_type, $db_image_path, $desc);
if ($newId === false || $newId === null) {
    $_SESSION['admin_error'] = "Failed to add product.";
    header("Location: apex26admin.php");
    exit;
}

$_SESSION['admin_success'] = "Product successfully added!";
header("Location: apex26admin.php");
exit;
