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

    public static function isSaleActive($salePercent, $saleExpiry)
    {
        $salePercent = (int)$salePercent;
        if ($salePercent <= 0) {
            return false;
        }

        if ($saleExpiry === null || trim((string)$saleExpiry) === '') {
            return true;
        }

        $expiryTs = strtotime((string)$saleExpiry);
        return $expiryTs !== false && $expiryTs > time();
    }

    public static function salePriceFromPercent($regularPrice, $salePercent)
    {
        return round((float)$regularPrice * (1 - ((int)$salePercent / 100)), 2);
    }

    public static function applyPricingFields(array $row)
    {
        $regularPrice = (float)($row['regular_price'] ?? $row['original_price'] ?? $row['price'] ?? 0);
        $salePercent  = (int)($row['sale_percent'] ?? 0);
        $saleExpiry   = $row['sale_expiry'] ?? $row['sale_valid_until'] ?? null;
        $saleActive   = self::isSaleActive($salePercent, $saleExpiry);
        $salePrice    = $saleActive ? self::salePriceFromPercent($regularPrice, $salePercent) : null;

        $row['price']            = $regularPrice;
        $row['regular_price']    = $regularPrice;
        $row['original_price']   = $regularPrice;
        $row['effective_price']  = $saleActive ? $salePrice : $regularPrice;
        $row['sale_price']       = $salePrice;
        $row['is_sale_active']   = $saleActive;
        $row['discount_percent'] = $saleActive ? $salePercent : 0;
        $row['old_price']        = $saleActive ? $regularPrice : ($row['old_price'] ?? null);
        $row['image']            = self::normalizeProductImagePath($row['image'] ?? '');

        return $row;
    }

    public static function getCartItemEffectivePrice(array $item)
    {
        if (isset($item['price_at_checkout'])) {
            return (float)$item['price_at_checkout'];
        }

        if (isset($item['effective_price'])) {
            return (float)$item['effective_price'];
        }

        $priced = self::applyPricingFields($item);
        return (float)$priced['effective_price'];
    }

    private static function buildSessionProductItem(array $product, $qty = null)
    {
        $item = [
            'name'           => $product['name'] ?? 'Product',
            'price'          => $product['regular_price'] ?? $product['price'] ?? 0,
            'original_price' => $product['regular_price'] ?? $product['price'] ?? 0,
            'effective_price'=> $product['effective_price'] ?? $product['price'] ?? 0,
            'sale_price'     => $product['sale_price'] ?? null,
            'sale_percent'   => $product['sale_percent'] ?? 0,
            'sale_expiry'    => $product['sale_expiry'] ?? '',
            'is_sale_active' => !empty($product['is_sale_active']),
            'discount_percent' => $product['discount_percent'] ?? 0,
            'image'          => $product['image'] ?? '',
            'brand'          => $product['brand'] ?? '',
            'category'       => $product['category'] ?? '',
        ];

        if ($qty !== null) {
            $item['qty'] = max(1, (int)$qty);
        }

        return $item;
    }

    public function refreshCartItemsWithLivePricing(array $cartItems)
    {
        foreach ($cartItems as $id => $item) {
            $product = $this->findProductById((int)$id);
            if (!$product) {
                $cartItems[$id] = self::applyPricingFields($item);
                continue;
            }

            $cartItems[$id] = self::buildSessionProductItem($product, $item['qty'] ?? 1);
        }

        return $cartItems;
    }

    public function refreshFavoriteItemsWithLivePricing(array $favoriteItems)
    {
        foreach ($favoriteItems as $id => $item) {
            $product = $this->findProductById((int)$id);
            if (!$product) {
                $favoriteItems[$id] = self::applyPricingFields($item);
                continue;
            }

            $favoriteItems[$id] = self::buildSessionProductItem($product);
        }

        return $favoriteItems;
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
            $row = self::applyPricingFields($row);
            $row['rating'] = rand(4, 5); // Mock rating until reviews_tbl is active
            $row['sales']  = rand(50, 500); // Mock sales for featured sorting

            $products[$row['id']] = $row;
        }
        return $products;
    }

    // ── 2. ADD PRODUCT VIA ADMIN ──
    public function addProduct($name, $brand, $category, $price, $old_price = null, $stock = 0, $rating = null, $badge = '', $badge_type = '', $image = '', $desc = '', $shipping_time = '', $sale_percent = 0, $sale_expiry = null)
    {
        $brand_id = $this->getBrandId($brand);
        $cat_id = $this->getCategoryId($category);

        $sale_expiry = !empty($sale_expiry) ? $sale_expiry : null;
        $sale_percent = !empty($sale_percent) ? $sale_percent : 0;
        $image = self::normalizeProductImagePath($image);

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
        $image = self::normalizeProductImagePath($image);

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

    public static function normalizeProductImagePath($imagePath)
    {
        $imagePath = trim((string)$imagePath);

        if ($imagePath === '') {
            return '';
        }

        if (strpos($imagePath, '<svg') !== false) {
            return $imagePath;
        }

        if (preg_match('#^(https?:)?//#i', $imagePath) || strpos($imagePath, 'data:image/') === 0) {
            return $imagePath;
        }

        $path = str_replace('\\', '/', $imagePath);
        $path = preg_replace('#^\./+#', '', $path);
        $path = preg_replace('#^(\.\./)+#', '', $path);

        if (preg_match('#(?:^|/)(assets/images/.+)$#i', $path, $matches)) {
            return $matches[1];
        }

        $compact = strtolower(preg_replace('/[^a-z0-9._-]/i', '', $imagePath));
        $knownImageDirs = [
            'products' => 'assetsimagesproducts',
            'profiles' => 'assetsimagesprofiles',
        ];

        foreach ($knownImageDirs as $dir => $marker) {
            $markerPos = strpos($compact, $marker);
            if ($markerPos !== false) {
                $fileName = substr($compact, $markerPos + strlen($marker));
                if ($fileName !== '') {
                    return 'assets/images/' . $dir . '/' . basename($fileName);
                }
            }
        }

        $baseName = basename($path);
        if ($baseName !== '' && file_exists(__DIR__ . '/../assets/images/products/' . $baseName)) {
            return 'assets/images/products/' . $baseName;
        }

        return ltrim($path, '/');
    }

    public static function getProductImageSrc($imagePath, $prefix = '')
    {
        $imagePath = self::normalizeProductImagePath($imagePath);
        if (empty($imagePath)) return 'https://via.placeholder.com/300';
        if (preg_match('#^(https?:)?//#i', $imagePath) || strpos($imagePath, 'data:image/') === 0 || strpos($imagePath, '<svg') !== false) return $imagePath;
        return $prefix . $imagePath;
    }

    // Temporary mock for Orders to prevent crashes until we do the Orders module
// 1. FOR ADMIN: Fetch all orders for the management dashboard
    public function getAllOrders() {
        // Fetch orders, user details, payment details, and the total item count in one query.
        $query = "
            SELECT o.*, o.order_ref_code AS reference_number,
                   u.first_name, u.last_name, u.email, u.username,
                   TRIM(CONCAT_WS(' ', NULLIF(u.first_name, ''), NULLIF(u.last_name, ''))) AS customer_name,
                   TRIM(CONCAT_WS(' ', NULLIF(sa.first_name, ''), NULLIF(sa.last_name, ''))) AS shipping_name,
                   COALESCE(
                       NULLIF(TRIM(CONCAT_WS(' ', NULLIF(u.first_name, ''), NULLIF(u.last_name, ''))), ''),
                       NULLIF(TRIM(CONCAT_WS(' ', NULLIF(sa.first_name, ''), NULLIF(sa.last_name, ''))), ''),
                       NULLIF(u.username, ''),
                       'Guest'
                   ) AS display_customer_name,
                   sa.phone_number, sa.street_address, sa.city, sa.zip_code,
                   p.method AS payment_method,
                   p.status AS payment_status,
                   p.qr_screenshot_path AS payment_screenshot,
                   p.transaction_id,
                   (SELECT SUM(quantity) FROM order_items_tbl WHERE order_id = o.order_id) as items_count
            FROM orders_tbl o
            LEFT JOIN users_tbl u ON o.user_id = u.user_id
            LEFT JOIN shipping_address_tbl sa ON sa.order_ref_code = o.order_ref_code
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
                    SELECT oi.product_id, oi.quantity AS qty, oi.price_at_checkout AS price,
                           (oi.quantity * oi.price_at_checkout) AS line_total,
                           p.name, b.brand_name AS brand, c.category_name AS category,
                           (SELECT image_path FROM product_images_tbl WHERE product_id = p.product_id LIMIT 1) AS image
                    FROM order_items_tbl oi
                    LEFT JOIN products_tbl p ON oi.product_id = p.product_id
                    LEFT JOIN brand_tbl b ON p.brand_id = b.brand_id
                    LEFT JOIN category_tbl c ON p.category_id = c.category_id
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
            $row = self::applyPricingFields($row);
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
            SELECT oi.product_id,
                   oi.quantity AS qty,
                   oi.price_at_checkout AS price,
                   (oi.quantity * oi.price_at_checkout) AS line_total,
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

    // ── REVIEW & ORDER DELETION METHODS ──

    public function hasUserReviewedProduct($userId, $productId)
    {
        $query = "SELECT review_id FROM reviews_tbl WHERE user_id = ? AND product_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $hasReview = $result->num_rows > 0;
        $stmt->close();
        return $hasReview;
    }

    // Check if the user has reviewed this specific product FOR this specific order.
    public function hasUserReviewedProductForOrder($userId, $productId, $orderId)
    {
        $query = "SELECT review_id FROM reviews_tbl WHERE user_id = ? AND product_id = ? AND order_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $userId, $productId, $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $hasReview = $result->num_rows > 0;
        $stmt->close();
        return $hasReview;
    }

    public function hasUserReviewedOrder($orderId, $userId)
    {
        $query = "
            SELECT oi.product_id
            FROM order_items_tbl oi
            INNER JOIN orders_tbl o ON oi.order_id = o.order_id
            WHERE oi.order_id = ? AND o.user_id = ?
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $orderId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $productIds = [];
        while ($row = $result->fetch_assoc()) {
            $productIds[] = intval($row['product_id']);
        }
        $stmt->close();

        if (empty($productIds)) {
            return false;
        }

        foreach ($productIds as $productId) {
            if (!$this->hasUserReviewedProductForOrder($userId, $productId, $orderId)) {
                return false;
            }
        }

        return true;
    }

    public function canDeleteOrderStatusEntry($orderId, $userId)
    {
        $query = "SELECT order_status FROM orders_tbl WHERE order_id = ? AND user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $orderId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();

        if (!$order) {
            return ['can_delete' => false, 'reason' => 'Order not found.'];
        }

        $status = $order['order_status'];
        if ($status === 'Canceled') {
            return ['can_delete' => true, 'reason' => 'Canceled orders can be deleted.'];
        }

        if ($status === 'Completed') {
            if ($this->hasUserReviewedOrder($orderId, $userId)) {
                return ['can_delete' => true, 'reason' => 'Review submitted.'];
            }
            return ['can_delete' => false, 'reason' => 'Submit a review before deleting this completed order.'];
        }

        return ['can_delete' => false, 'reason' => 'Only completed or canceled orders can be deleted.'];
    }

    public function deleteOrderStatusEntry($orderId, $userId)
    {
        $eligibility = $this->canDeleteOrderStatusEntry($orderId, $userId);
        if (empty($eligibility['can_delete'])) {
            return false;
        }

        $stmt = $this->conn->prepare("DELETE FROM order_status_tbl WHERE order_id = ?");
        $stmt->bind_param("i", $orderId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

}
