<?php
session_start();
require_once __DIR__ . '/../database/db_connect.php';
require_once __DIR__ . '/../classes/Inventory.php';

header('Content-Type: application/json');

// Security Check
if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_order') {
    $user_id = intval($_SESSION['user']['id']);
    $order_id = intval($_POST['order_id'] ?? 0);

    if ($order_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
        exit;
    }

    $db = new Database();
    $conn = $db->getConnection();

    // 1. Verify the order belongs to the user and is Delivered
    $checkStmt = $conn->prepare("SELECT order_status FROM orders_tbl WHERE order_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $order_id, $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found or permission denied.']);
        exit;
    }

    $order = $result->fetch_assoc();
    if ($order['order_status'] !== 'Delivered') {
        echo json_encode(['success' => false, 'message' => 'Only delivered orders can be confirmed.']);
        exit;
    }

    // 2. Update status to 'Completed' (which acts as your archive trigger based on the fetching logic)
    $updateStmt = $conn->prepare("UPDATE orders_tbl SET order_status = 'Completed' WHERE order_id = ?");
    $updateStmt->bind_param("i", $order_id);
    
    if ($updateStmt->execute()) {
        $inventory = new Inventory();
        $inventory->syncCompletedOrderPayments($order_id);
        echo json_encode(['success' => true, 'message' => 'Order confirmed and moved to archives!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error while completing order.']);
    }

    $db->closeConnection();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
