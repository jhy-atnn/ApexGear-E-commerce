<?php
session_start();
require_once __DIR__ . '/../database/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in to save favorites.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    global $conn;
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);

    $check_sql = "SELECT fav_id FROM favorites_tbl WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $delete_sql = "DELETE FROM favorites_tbl WHERE user_id = ? AND product_id = ?";
        $del_stmt = $conn->prepare($delete_sql);
        $del_stmt->bind_param("ii", $user_id, $product_id);

        if ($del_stmt->execute()) {
            echo json_encode(['status' => 'removed', 'message' => 'Removed from favorites.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to remove.']);
        }
    } else {
        $insert_sql = "INSERT INTO favorites_tbl (user_id, product_id) VALUES (?, ?)";
        $ins_stmt = $conn->prepare($insert_sql);
        $ins_stmt->bind_param("ii", $user_id, $product_id);

        if ($ins_stmt->execute()) {
            echo json_encode(['status' => 'added', 'message' => 'Added to favorites!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add.']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
