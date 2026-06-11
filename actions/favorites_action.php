<?php
require_once __DIR__ . '/../includes/storage.php';
require_once __DIR__ . '/../classes/Inventory.php';

if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in to save favorites.']);
    exit;
}

$user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : ($_SESSION['user_id'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    /** @var Inventory $inv */
    $inv = new Inventory();
    $res = $inv->toggleFavorite($user_id, $product_id);
    if ($res === 'removed') {
        echo json_encode(['status' => 'removed', 'message' => 'Removed from favorites.']);
    } else {
        echo json_encode(['status' => 'added', 'message' => 'Added to favorites!']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
