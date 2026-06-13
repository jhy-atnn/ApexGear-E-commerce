<?php
// We no longer rely on storage.php. Use the real database.
require_once __DIR__ . '/../database/db_connect.php';

class Inventory
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // ── HELPER: Resolve or Insert Brand ID ──
    private function getBrandId($brandName)
    {
        if (empty($brandName)) return null;

        $stmt = $this->conn->prepare("SELECT brand_id FROM brand_tbl WHERE brand_name = ?");
        $stmt->bind_param("s", $brandName);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) return $row['brand_id'];

        // If brand doesn't exist, create it automatically
        $stmt = $this->conn->prepare("INSERT INTO brand_tbl (brand_name) VALUES (?)");
        $stmt->bind_param("s", $brandName);
        $stmt->execute();
        return $stmt->insert_id;
    }

    // ── HELPER: Resolve or Insert Category ID ──
    private function getCategoryId($catName)
    {
        if (empty($catName)) return null;

        $stmt = $this->conn->prepare("SELECT category_id FROM category_tbl WHERE category_name = ?");
        $stmt->bind_param("s", $catName);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) return $row['category_id'];

        // If category doesn't exist, create it automatically
        $stmt = $this->conn->prepare("INSERT INTO category_tbl (category_name) VALUES (?)");
        $stmt->bind_param("s", $catName);
        $stmt->execute();
        return $stmt->insert_id;
    }

    // ── 1. FETCH ALL PRODUCTS FOR INDEX & STORE ──
    public function getAllProducts($includeArchived = false)
    {
        // We use aliases (AS) to map your DB columns to the names the frontend expects
        $query = "
            SELECT p.product_id as id, p.name, p.price, p.`desc`, p.stock_qty as stock,
                   p.sale_percent, p.sale_valid_until as sale_expiry,
                   p.badge, p.badge_type, p.est_shipping_time as shipping_time,
                   p.archived_at, IF(p.archived_at IS NULL, 0, 1) AS archived,
                   b.brand_name as brand, c.category_name as category,
                   (SELECT image_path FROM product_images_tbl WHERE product_id = p.product_id LIMIT 1) as image
            FROM products_tbl p
            LEFT JOIN brand_tbl b ON p.brand_id = b.brand_id
            LEFT JOIN category_tbl c ON p.category_id = c.category_id
        ";
        if (!$includeArchived) {
            $query .= " WHERE p.archived_at IS NULL";
        }
        $query .= " ORDER BY p.product_id DESC";

        $result = $this->conn->query($query);
        $products = [];

        while ($row = $result->fetch_assoc()) {
            // Placeholder data for frontend features not yet in database
            $row['rating'] = rand(4, 5); // Mock rating until reviews_tbl is active
            $row['sales']  = rand(50, 500); // Mock sales for featured sorting

            $products[$row['id']] = $row;
        }
        return $products;
    }

    // ── 2. ADD PRODUCT VIA ADMIN ──
    public function addProduct($name, $brand, $category, $price, $old_price, $stock, $rating, $badge, $badge_type, $image, $desc, $shipping_time, $sale_percent, $sale_expiry)
    {
        $brand_id = $this->getBrandId($brand);
        $cat_id = $this->getCategoryId($category);

        $sale_expiry = !empty($sale_expiry) ? $sale_expiry : null;
        $sale_percent = !empty($sale_percent) ? $sale_percent : 0;

        // Insert main product
        $stmt = $this->conn->prepare("INSERT INTO products_tbl (brand_id, category_id, name, `desc`, price, sale_percent, sale_valid_until, stock_qty, badge, badge_type, est_shipping_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissdisisss", $brand_id, $cat_id, $name, $desc, $price, $sale_percent, $sale_expiry, $stock, $badge, $badge_type, $shipping_time);
        $stmt->execute();
        $product_id = $stmt->insert_id;

        // Insert image into product_images_tbl
        if (!empty($image)) {
            $imgStmt = $this->conn->prepare("INSERT INTO product_images_tbl (product_id, image_path) VALUES (?, ?)");
            $imgStmt->bind_param("is", $product_id, $image);
            $imgStmt->execute();
        }
        return $product_id;
    }

    // ── 3. EDIT PRODUCT VIA ADMIN ──
    public function editProduct($id, $name, $brand, $category, $price, $old_price, $stock, $rating, $badge, $badge_type, $image, $desc, $shipping_time, $sale_percent, $sale_expiry)
    {
        $brand_id = $this->getBrandId($brand);
        $cat_id = $this->getCategoryId($category);

        $sale_expiry = !empty($sale_expiry) ? $sale_expiry : null;
        $sale_percent = !empty($sale_percent) ? $sale_percent : 0;

        $stmt = $this->conn->prepare("UPDATE products_tbl SET brand_id=?, category_id=?, name=?, `desc`=?, price=?, sale_percent=?, sale_valid_until=?, stock_qty=?, badge=?, badge_type=?, est_shipping_time=? WHERE product_id=?");
        $stmt->bind_param("iissdisisssi", $brand_id, $cat_id, $name, $desc, $price, $sale_percent, $sale_expiry, $stock, $badge, $badge_type, $shipping_time, $id);
        $stmt->execute();

        // Handle Image Update
        if (!empty($image)) {
            $chk = $this->conn->prepare("SELECT image_id FROM product_images_tbl WHERE product_id = ?");
            $chk->bind_param("i", $id);
            $chk->execute();

            if ($chk->get_result()->num_rows > 0) {
                $imgStmt = $this->conn->prepare("UPDATE product_images_tbl SET image_path = ? WHERE product_id = ?");
                $imgStmt->bind_param("si", $image, $id);
            } else {
                $imgStmt = $this->conn->prepare("INSERT INTO product_images_tbl (product_id, image_path) VALUES (?, ?)");
                $imgStmt->bind_param("is", $id, $image);
            }
            $imgStmt->execute();
        }
    }

    // ── 4. ARCHIVE PRODUCT VIA ADMIN ──
    public function archiveProduct($id)
    {
        $date = date('Y-m-d H:i:s');
        $stmt = $this->conn->prepare("UPDATE products_tbl SET archived_at = ? WHERE product_id = ?");
        $stmt->bind_param("si", $date, $id);
        $stmt->execute();
    }

    // ── 5. HELPER FOR IMAGES ──
    public function restoreProduct($id)
    {
        $stmt = $this->conn->prepare("UPDATE products_tbl SET archived_at = NULL WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function deleteProduct($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM products_tbl WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    private function ensureAdminActivityTable()
    {
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS admin_activity_tbl (
                activity_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NULL,
                activity_type VARCHAR(50) NOT NULL,
                message TEXT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_created_at (created_at),
                KEY idx_activity_type (activity_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    public function logAdminActivity($type, $message, $adminId = null)
    {
        $this->ensureAdminActivityTable();

        if ($adminId !== null) {
            $stmt = $this->conn->prepare("INSERT INTO admin_activity_tbl (admin_id, activity_type, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $adminId, $type, $message);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO admin_activity_tbl (admin_id, activity_type, message) VALUES (NULL, ?, ?)");
            $stmt->bind_param("ss", $type, $message);
        }

        return $stmt->execute();
    }

    public function getAdminActivityFeed($limit = 12)
    {
        $this->ensureAdminActivityTable();
        $limit = max(1, min(50, intval($limit)));

        $stmt = $this->conn->prepare("
            SELECT activity_id, admin_id, activity_type, message, created_at
            FROM admin_activity_tbl
            ORDER BY created_at DESC, activity_id DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        $stmt->close();

        return $activities;
    }

    public function getRecentOrderNotifications($limit = 8)
    {
        $limit = max(1, min(30, intval($limit)));

        $stmt = $this->conn->prepare("
            SELECT o.order_id, o.order_ref_code AS reference_number, o.total_amount, o.order_status, o.created_at,
                   u.username, u.email,
                   (SELECT SUM(quantity) FROM order_items_tbl WHERE order_id = o.order_id) AS items_count
            FROM orders_tbl o
            LEFT JOIN users_tbl u ON o.user_id = u.user_id
            ORDER BY o.created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $stmt->close();

        return $orders;
    }

    public static function getProductImageSrc($imagePath, $prefix = '')
    {
        if (empty($imagePath)) return 'https://via.placeholder.com/300';
        if (strpos($imagePath, 'http') === 0 || strpos($imagePath, '<svg') !== false) return $imagePath;
        return $prefix . $imagePath;
    }

    // Temporary mock for Orders to prevent crashes until we do the Orders module
// 1. FOR ADMIN: Fetch all orders for the management dashboard
    public function getAllOrders() {
        // Fetch orders, user details, payment details, and the total item count in one query.
        $query = "
            SELECT o.*, o.order_ref_code AS reference_number,
                   u.first_name, u.last_name, u.email, u.username,
                   p.method AS payment_method,
                   p.status AS payment_status,
                   p.qr_screenshot_path AS payment_screenshot,
                   p.transaction_id,
                   (SELECT SUM(quantity) FROM order_items_tbl WHERE order_id = o.order_id) as items_count
            FROM orders_tbl o
            LEFT JOIN users_tbl u ON o.user_id = u.user_id
            LEFT JOIN payments_tbl p ON o.order_id = p.order_id
            ORDER BY o.created_at DESC
        ";

        $result = $this->conn->query($query);
        $orders = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Fetch the specific products inside this order for the Admin Modal UI
                $orderId = $row['order_id'];
                $itemQuery = "
                    SELECT oi.quantity as qty, p.name, oi.price_at_checkout AS price
                    FROM order_items_tbl oi
                    LEFT JOIN products_tbl p ON oi.product_id = p.product_id
                    WHERE oi.order_id = ?
                ";
                
                $stmt = $this->conn->prepare($itemQuery);
                if ($stmt) {
                    $stmt->bind_param("i", $orderId);
                    $stmt->execute();
                    $itemRes = $stmt->get_result();
                    
                    $items = [];
                    while($itemRow = $itemRes->fetch_assoc()) {
                        $items[] = $itemRow;
                    }
                    $row['items'] = $items;
                    $stmt->close();
                } else {
                    $row['items'] = [];
                }

                $orders[] = $row;
            }
        }
        return $orders;
    }

    public function updateOrderStatus($orderId, $orderStatus, $remarks = '', $adminId = null)
    {
        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare("UPDATE orders_tbl SET order_status = ? WHERE order_id = ?");
            $stmt->bind_param("si", $orderStatus, $orderId);
            $stmt->execute();

            if ($stmt->affected_rows < 0) {
                throw new Exception('Unable to update order status.');
            }
            $stmt->close();

            if ($adminId !== null) {
                $stmtLog = $this->conn->prepare("INSERT INTO order_status_tbl (order_id, order_status, payment_remarks, updated_by_admin) VALUES (?, ?, ?, ?)");
                $stmtLog->bind_param("issi", $orderId, $orderStatus, $remarks, $adminId);
            } else {
                $stmtLog = $this->conn->prepare("INSERT INTO order_status_tbl (order_id, order_status, payment_remarks, updated_by_admin) VALUES (?, ?, ?, NULL)");
                $stmtLog->bind_param("iss", $orderId, $orderStatus, $remarks);
            }
            $stmtLog->execute();
            $stmtLog->close();

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            return false;
        }
    }


    // ── FETCH SINGLE PRODUCT BY ID ──
    public function findProductById($id)
    {
        $query = "
            SELECT p.product_id as id, p.name, p.price, p.`desc`, p.stock_qty as stock,
                   p.sale_percent, p.sale_valid_until as sale_expiry,
                   p.badge, p.badge_type, p.est_shipping_time as shipping_time,
                   b.brand_name as brand, c.category_name as category,
                   (SELECT image_path FROM product_images_tbl WHERE product_id = p.product_id LIMIT 1) as image
            FROM products_tbl p
            LEFT JOIN brand_tbl b ON p.brand_id = b.brand_id
            LEFT JOIN category_tbl c ON p.category_id = c.category_id
            WHERE p.product_id = ? AND p.archived_at IS NULL
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Placeholder data for frontend features not yet in database
            $row['rating'] = rand(4, 5);
            $row['sales']  = rand(50, 500);
            return $row;
        }

        return null;
    }

    public function getOrdersByUser($userId)
    {
        $query = "
            SELECT o.*, o.order_ref_code AS reference_number,
                   p.method AS payment_method,
                   p.status AS payment_status,
                   p.transaction_id
            FROM orders_tbl o
            LEFT JOIN payments_tbl p ON o.order_id = p.order_id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $stmt->close();

        return $orders;
    }

    public function getOrderItems($orderId)
    {
        $query = "
            SELECT oi.quantity AS qty,
                   oi.price_at_checkout AS price,
                   p.name,
                   (SELECT image_path FROM product_images_tbl WHERE product_id = p.product_id LIMIT 1) AS image
            FROM order_items_tbl oi
            LEFT JOIN products_tbl p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $row['image'] = self::getProductImageSrc($row['image'] ?? '');
            $items[] = $row;
        }
        $stmt->close();

        return $items;
    }

}
