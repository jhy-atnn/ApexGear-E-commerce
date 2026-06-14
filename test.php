<?php
// 1. Pull in your database class
require_once 'database/db_connect.php';

echo "Attempting to connect to Aiven Cloud...<br><br>";

try {
    // 2. Instantiate the class (this triggers the connection script)
    $db = new Database();
    $conn = $db->getConnection();

    // 3. Check if it worked!
    if ($conn) {
        echo "<h2>🚀 Boom! Connected to Aiven MySQL Successfully!</h2>";
        echo "Timezone synced to +08:00 (Asia/Manila). Ready for gadget inventory logs.";
    }
} catch (Exception $e) {
    echo "<h2>❌ Connection Failed.</h2>";
    echo "Error Details: " . $e->getMessage();
}
?>