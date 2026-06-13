<?php
// actions/delete_order_status_action.php
// Handles AJAX bulk-delete of order status entries from order_status modal.

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_order_status_entries'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

require_once __DIR__ . '/../classes/Inventory.php';

$userId      = intval($_SESSION['user']['id']);
$inv         = new Inventory();
$selectedIds = $_POST['selected_orders'] ?? [];

$deletedCount = 0;
$blockedCount = 0;
$deletedIds   = [];

foreach ($selectedIds as $selectedId) {
    $orderId = intval($selectedId);
    if ($orderId <= 0) continue;

    if (!method_exists($inv, 'canDeleteOrderStatusEntry') || !method_exists($inv, 'deleteOrderStatusEntry')) {
        break;
    }

    $eligibility = $inv->canDeleteOrderStatusEntry($orderId, $userId);
    if (!empty($eligibility['can_delete'])) {
        if ($inv->deleteOrderStatusEntry($orderId, $userId)) {
            $deletedCount++;
            $deletedIds[] = $orderId;
        }
    } else {
        $blockedCount++;
    }
}

if ($deletedCount > 0) {
    echo json_encode([
        'success'     => true,
        'message'     => $deletedCount . ' order status entr' . ($deletedCount === 1 ? 'y' : 'ies') . ' deleted.',
        'deleted_ids' => $deletedIds,
    ]);
} elseif ($blockedCount > 0) {
    echo json_encode([
        'success'     => false,
        'message'     => 'Selected completed orders can only be deleted after submitting a review.',
        'deleted_ids' => [],
    ]);
} else {
    echo json_encode([
        'success'     => false,
        'message'     => 'No eligible order status entries were selected.',
        'deleted_ids' => [],
    ]);
}
