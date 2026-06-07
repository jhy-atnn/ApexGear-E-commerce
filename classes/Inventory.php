<?php

require_once __DIR__ . '/../db_connect.php';

class Inventory
{
    private $conn;

    public function __construct()
    {
        global $conn;

        if (!$conn) {
            die("Database connection is missing in Inventory class.");
        }

        $this->conn = $conn;
    }

    /**
     * Fetch ALL active products 
     * (Used in store.php to populate the main catalog)
     */
    public function getAllProducts()
    {
        $products = [];

        // JOINs are used to fetch the actual text names for brand and category
        $sql = "SELECT p.*, b.brand_name as brand, c.category_name as category 
                FROM products_tbl p 
                LEFT JOIN brands_tbl b ON p.brand_id = b.brand_id 
                LEFT JOIN categories_tbl c ON p.category_id = c.category_id
                WHERE p.is_archived = 0";

        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Key the array by product_id so it perfectly matches your old session logic
                $products[$row['product_id']] = $row;
            }
        }

        return $products;
    }

    /**
     * Fetch a SINGLE product by its ID 
     * (Used in product.php when viewing a specific item)
     */
    public function getProductById($id)
    {
        $sql = "SELECT p.*, b.brand_name as brand, c.category_name as category 
                FROM products_tbl p 
                LEFT JOIN brands_tbl b ON p.brand_id = b.brand_id 
                LEFT JOIN categories_tbl c ON p.category_id = c.category_id
                WHERE p.product_id = ? AND p.is_archived = 0";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc(); // Returns just the single associative array
        }

        return null;
    }

    /**
     * Fetch FEATURED products 
     * (Used on index.php to show top items, grabs anything with a badge)
     */
    public function getFeaturedProducts($limit = 4)
    {
        $products = [];

        $sql = "SELECT p.*, b.brand_name as brand, c.category_name as category 
                FROM products_tbl p 
                LEFT JOIN brands_tbl b ON p.brand_id = b.brand_id 
                LEFT JOIN categories_tbl c ON p.category_id = c.category_id
                WHERE p.is_archived = 0 AND p.badge IS NOT NULL
                LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[$row['product_id']] = $row;
            }
        }

        return $products;
    }


    public function searchProducts($keyword)
    {
        $products = [];

        $searchTerm = "%" . $keyword . "%";

        $sql = "SELECT p.*, b.brand_name as brand, c.category_name as category 
                FROM products_tbl p 
                LEFT JOIN brands_tbl b ON p.brand_id = b.brand_id 
                LEFT JOIN categories_tbl c ON p.category_id = c.category_id
                WHERE p.is_archived = 0 
                AND (p.name LIKE ? OR b.brand_name LIKE ? OR c.category_name LIKE ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[$row['product_id']] = $row;
            }
        }

        return $products;
    }
}
