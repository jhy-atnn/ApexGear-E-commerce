<?php
session_start();
require_once __DIR__ . '/../database/db_connect.php';
require_once __DIR__ . '/../includes/auth_timeout.php';

apex_enforce_login_timeout();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = intval($_POST['product_id'] ?? 0);
    $return_url = $_POST['return_url'] ?? '../store.php';

    if ($product_id > 0) {
        // Initialize session array if it doesn't exist yet
        if (!isset($_SESSION['favorites'])) {
            $_SESSION['favorites'] = [];
        }

        $db = new Database();
        $conn = $db->getConnection();
        
        // Check if user is logged in to save to database
        $user_id = isset($_SESSION['user']['id']) ? intval($_SESSION['user']['id']) : null;

        if ($action === 'add' || $action === 'toggle') {
            
            // If toggle is clicked and it's already a favorite, we remove it
            if ($action === 'toggle' && isset($_SESSION['favorites'][$product_id])) {
                unset($_SESSION['favorites'][$product_id]);
                if ($user_id) {
                    $del = $conn->prepare("DELETE FROM favorites_tbl WHERE user_id = ? AND product_id = ?");
                    $del->bind_param("ii", $user_id, $product_id);
                    $del->execute();
                }
            } else {
                // Otherwise, we Add it
                $stmt = $conn->prepare("SELECT name, price FROM products_tbl WHERE product_id = ? AND archived_at IS NULL");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $res = $stmt->get_result();

                if ($row = $res->fetch_assoc()) {
                    // Save to Session (for UI speed)
                    $_SESSION['favorites'][$product_id] = [
                        'name' => $row['name'],
                        'price' => $row['price']
                    ];

                    // Save to Database (for persistence)
                    if ($user_id) {
                        $check = $conn->prepare("SELECT favorite_id FROM favorites_tbl WHERE user_id = ? AND product_id = ?");
                        $check->bind_param("ii", $user_id, $product_id);
                        $check->execute();
                        
                        if ($check->get_result()->num_rows === 0) {
                            $ins = $conn->prepare("INSERT INTO favorites_tbl (user_id, product_id) VALUES (?, ?)");
                            $ins->bind_param("ii", $user_id, $product_id);
                            $ins->execute();
                        }
                    }
                }
            }
        } elseif ($action === 'remove') {
            // Explicitly Remove it (From Modal)
            if (isset($_SESSION['favorites'][$product_id])) {
                unset($_SESSION['favorites'][$product_id]);
            }
            if ($user_id) {
                $del = $conn->prepare("DELETE FROM favorites_tbl WHERE user_id = ? AND product_id = ?");
                $del->bind_param("ii", $user_id, $product_id);
                $del->execute();
            }
        }
        $db->closeConnection();
    }

    // Redirect the user back to the page they were looking at seamlessly
    header("Location: " . $return_url);
    exit;
}
?>
