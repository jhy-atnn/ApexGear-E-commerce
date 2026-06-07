<?php
class Inventory
{
    private $conn;
    private $currentProduct = [];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        global $conn;
        $dbConnectPath = __DIR__ . '/../database/db_connect.php';
        if (file_exists($dbConnectPath)) {
            require_once $dbConnectPath;
            $this->conn = $conn;
        }
    }

    public function getInventory()
    {
        return $this->getAllProducts();
    }

    public function setInventory(array $items)
    {
        // No longer supported in DB mode
    }

    public function getCurrentProduct()
    {
        return $this->currentProduct;
    }

    public function setCurrentProduct(array $product)
    {
        $this->currentProduct = $product;
    }

    public function getAllProducts()
    {
        $products = [];
        if (!$this->conn) return $products;

        $sql = "SELECT p.*, b.brand_name as brand, c.category_name as category 
                FROM products_tbl p
                LEFT JOIN brands_tbl b ON p.brand_id = b.brand_id
                LEFT JOIN categories_tbl c ON p.category_id = c.category_id
                WHERE p.is_archived = 0";
                
        $result = $this->conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categoryName = $row['category'] ? strtolower($row['category']) : 'uncategorized';
                // normalize category name like "Laptops" to "laptop"
                if (substr($categoryName, -1) === 's') {
                    $categoryName = substr($categoryName, 0, -1);
                }

                $products[$row['product_id']] = [
                    'id' => $row['product_id'],
                    'name' => $row['name'],
                    'brand' => $row['brand'],
                    'category' => $categoryName,
                    'price' => (float)$row['price'],
                    'old_price' => $row['old_price'] ? (float)$row['old_price'] : null,
                    'stock' => (int)$row['stock'],
                    'rating' => (int)$row['rating'],
                    'badge' => $row['badge'],
                    'badge_type' => $row['badge_type'],
                    'image' => $row['image'],
                    'sales' => rand(50, 2000), // mocked sales field as used by frontend
                    'desc' => '' // we don't have desc in DB products_tbl, keep empty or null
                ];
            }
        }
        return $products;
    }

    private function getBrandId($brandName)
    {
        if (!$this->conn) return null;
        $sql = "SELECT brand_id FROM brands_tbl WHERE LOWER(brand_name) = LOWER(?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $brandName);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['brand_id'];
        }
        // Insert new brand if not found
        $sql = "INSERT INTO brands_tbl (brand_name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $brandName);
        $stmt->execute();
        return $stmt->insert_id;
    }

    private function getCategoryId($categoryName)
    {
        if (!$this->conn) return null;
        // The DB has "Laptops", "Peripherals". We map "laptop" -> "Laptops", etc.
        $searchCategory = ucfirst($categoryName);
        if (in_array(strtolower($categoryName), ['laptop', 'peripheral', 'phone', 'desktop'])) {
            $searchCategory .= 's';
        }
        
        $sql = "SELECT category_id FROM categories_tbl WHERE LOWER(category_name) = LOWER(?) OR LOWER(category_name) = LOWER(?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $categoryName, $searchCategory);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['category_id'];
        }
        // Insert new category if not found
        $sql = "INSERT INTO categories_tbl (category_name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $searchCategory);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public function addProduct($name, $brand, $category, $price, $old_price, $stock, $rating, $badge, $badge_type, $image, $desc)
    {
        if (!$this->conn) return;

        $brand_id = $this->getBrandId($brand);
        $category_id = $this->getCategoryId($category);

        $price = (float)$price;
        $old_price = empty($old_price) ? null : (float)$old_price;
        $stock = (int)$stock;
        $rating = empty($rating) ? 0 : (int)$rating;
        $badge = empty($badge) ? null : $badge;
        $badge_type = empty($badge_type) ? null : $badge_type;
        $image = empty($image) ? 'https://placehold.co/400x300/eeeeee/1D1D1F?text=No+Image' : $image;
        
        $sql = "INSERT INTO products_tbl (name, brand_id, category_id, price, old_price, stock, rating, badge, badge_type, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siiddiisss", $name, $brand_id, $category_id, $price, $old_price, $stock, $rating, $badge, $badge_type, $image);
        $stmt->execute();
    }

    public function editProduct($id, $name, $brand, $category, $price, $old_price, $stock, $rating, $badge, $badge_type, $image, $desc)
    {
        if (!$this->conn) return;
        
        $id = (int)$id;
        $brand_id = $this->getBrandId($brand);
        $category_id = $this->getCategoryId($category);

        $price = (float)$price;
        $old_price = empty($old_price) ? null : (float)$old_price;
        $stock = (int)$stock;
        $rating = empty($rating) ? 0 : (int)$rating;
        $badge = empty($badge) ? null : $badge;
        $badge_type = empty($badge_type) ? null : $badge_type;
        $image = empty($image) ? 'https://placehold.co/400x300/eeeeee/1D1D1F?text=No+Image' : $image;

        $sql = "UPDATE products_tbl 
                SET name = ?, brand_id = ?, category_id = ?, price = ?, old_price = ?, stock = ?, rating = ?, badge = ?, badge_type = ?, image = ? 
                WHERE product_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siiddiisssi", $name, $brand_id, $category_id, $price, $old_price, $stock, $rating, $badge, $badge_type, $image, $id);
        $stmt->execute();
    }

    public function deleteProduct($id)
    {
        if (!$this->conn) return;
        $id = (int)$id;
        
        $sql = "UPDATE products_tbl SET is_archived = 1 WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}
