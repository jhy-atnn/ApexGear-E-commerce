<?php
session_start();

// Make sure the path points back to your root directory
require_once '../db_connect.php'; 

// 1. Kick out anyone who tries to access this URL directly without submitting the form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/index.php");
    exit;
}

global $conn;

// 2. Grab and sanitize the text inputs
// We use the ?? operator to provide a fallback if the field is empty
$name = trim($_POST['product_name'] ?? '');
$brand_id = intval($_POST['brand_id'] ?? 0);
$category_id = intval($_POST['category_id'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$badge = !empty($_POST['badge']) ? trim($_POST['badge']) : NULL;
$badge_type = !empty($_POST['badge_type']) ? trim($_POST['badge_type']) : NULL;

// 3. Handle the Image Upload
$target_dir = "../assets/images/"; 
$db_image_path = "assets/images/placeholder.png"; // A safe fallback image

// Check if an image was actually uploaded without errors
if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0) {
    $file_name = basename($_FILES["product_image"]["name"]);
    
    // Clean the filename (removes spaces and weird characters)
    $clean_file_name = preg_replace("/[^a-zA-Z0-9.-]/", "_", $file_name);
    
    // Prepend a timestamp to make the filename 100% unique (prevents overwriting old images)
    $unique_file_name = time() . "_" . $clean_file_name;
    $target_file = $target_dir . $unique_file_name;

    // Move the file from PHP's temporary storage to your actual assets folder
    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        // This is the relative path we save to the database so store.php can read it
        $db_image_path = "assets/images/" . $unique_file_name;
    } else {
        // If upload fails, set an error session and bounce them back
        $_SESSION['admin_error'] = "Sorry, there was an error saving your image.";
        header("Location: ../admin/index.php");
        exit;
    }
}

// 4. Insert into the Database
$sql = "INSERT INTO products_tbl (name, brand_id, category_id, price, stock, badge, badge_type, image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

// Bind the parameters: string, int, int, double, int, string, string, string ("siidisss")
$stmt->bind_param("siidisss", $name, $brand_id, $category_id, $price, $stock, $badge, $badge_type, $db_image_path);

if ($stmt->execute()) {
    $_SESSION['admin_success'] = "Product successfully added to inventory!";
} else {
    $_SESSION['admin_error'] = "Database Error: " . $stmt->error;
}

// 5. Redirect back to the admin dashboard
header("Location: ../admin/index.php");
exit;
?>