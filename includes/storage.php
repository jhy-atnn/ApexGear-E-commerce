<?php
// Simple session-backed persistent storage for fake DB.
// Loads from storage/fake_db.json into $_SESSION['fake_db'] and saves on shutdown.

if (session_status() === PHP_SESSION_NONE) session_start();

$storageDir = __DIR__ . '/../storage';
$storageFile = $storageDir . '/fake_db.json';

function ensure_fake_db_structure()
{
    if (!isset($_SESSION['fake_db']) || !is_array($_SESSION['fake_db'])) {
        $_SESSION['fake_db'] = [];
    }
    $s = &$_SESSION['fake_db'];
    if (!isset($s['next_user_id'])) $s['next_user_id'] = 1000;
    if (!isset($s['next_product_id'])) $s['next_product_id'] = 100;
    if (!isset($s['next_order_id'])) $s['next_order_id'] = 1;
    if (!isset($s['users'])) $s['users'] = [];
    if (!isset($s['products'])) $s['products'] = [];
    if (!isset($s['orders'])) $s['orders'] = [];
    if (!isset($s['order_items'])) $s['order_items'] = [];
    if (!isset($s['favorites'])) $s['favorites'] = [];
}

// Load from disk if available
if (is_readable($storageFile)) {
    $raw = @file_get_contents($storageFile);
    $data = json_decode($raw, true);
    if (is_array($data)) {
        $_SESSION['fake_db'] = $data;
    }
}

ensure_fake_db_structure();

function save_fake_db()
{
    global $storageDir, $storageFile;
    if (!is_dir($storageDir)) mkdir($storageDir, 0777, true);
    $tmp = $storageFile . '.tmp';
    $data = json_encode($_SESSION['fake_db'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (file_put_contents($tmp, $data, LOCK_EX) !== false) {
        rename($tmp, $storageFile);
    }
}

register_shutdown_function('save_fake_db');

?>
