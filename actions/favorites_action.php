<?php
session_start();
require_once 'classes/Inventory.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $action = $_POST['action'];

    // Make sure the favorites session array exists
    if (!isset($_SESSION['favorites'])) {
        $_SESSION['favorites'] = [];
    }

    if ($action === 'remove') {
        // Remove item from favorites
        if (isset($_SESSION['favorites'][$product_id])) {
            unset($_SESSION['favorites'][$product_id]);
        }
    } elseif ($action === 'add') {
        // Fetch product details to save in the session
        $inventoryManager = new Inventory();
        $products = $inventoryManager->getAllProducts();

        if (isset($products[$product_id])) {
            $product = $products[$product_id];
            
            // Add to favorites (we don't need quantity for favorites, just the item details)
            $_SESSION['favorites'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image']
            ];
        }
    }

    // Redirect back to the page the user was on
    $return_url = isset($_POST['return_url']) && !empty($_POST['return_url']) 
        ? $_POST['return_url'] 
        : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'store.php');
        
    header("Location: " . $return_url);
    exit();
}

// Fallback if accessed directly without POST data
header("Location: store.php");
exit();
?>