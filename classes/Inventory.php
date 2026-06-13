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
    public function getAllProducts()
    {
        // We use aliases (AS) to map your DB columns to the names the frontend expects
        $query = "
            SELECT p.product_id as id, p.name, p.price, p.`desc`, p.stock_qty as stock,
                   p.sale_percent, p.sale_valid_until as sale_expiry,
                   p.badge, p.badge_type, p.est_shipping_time as shipping_time,
                   b.brand_name as brand, c.category_name as category,
                   (SELECT image_path FROM product_images_tbl WHERE product_id = p.product_id LIMIT 1) as image
            FROM products_tbl p
            LEFT JOIN brand_tbl b ON p.brand_id = b.brand_id
            LEFT JOIN category_tbl c ON p.category_id = c.category_id
            WHERE p.archived_at IS NULL
            ORDER BY p.product_id DESC
        ";

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
    public static function getProductImageSrc($imagePath, $prefix = '')
    {
        if (empty($imagePath)) return 'https://via.placeholder.com/300';
        if (strpos($imagePath, 'http') === 0 || strpos($imagePath, '<svg') !== false) return $imagePath;
        return $prefix . $imagePath;
    }

    // Temporary mock for Orders to prevent crashes until we do the Orders module
    public function getAllOrders()
    {
        return [];
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
        return [];
    }

    public function getOrderItems($orderId)
    {
        return [];
    }
}
