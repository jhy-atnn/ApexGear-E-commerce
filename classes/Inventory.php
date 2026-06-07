<?php
class Inventory
{
    private $inventory = [];
    private $currentProduct = [];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['inventory'])) {
            $_SESSION['inventory'] = [];
        }

        $this->inventory = &$_SESSION['inventory'];
    }

    public function getInventory()
    {
        return $this->inventory;
    }

    public function setInventory(array $items)
    {
        $_SESSION['inventory'] = $items;
        $this->inventory = &$_SESSION['inventory'];
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
        return $_SESSION['inventory'];
    }

    // Add a new product
    public function addProduct($name, $brand, $category, $price, $old_price, $stock, $rating, $badge, $badge_type, $image, $desc)
    {
        // Find the next available ID
        $newId = empty($_SESSION['inventory']) ? 1 : max(array_keys($_SESSION['inventory'])) + 1;

        $_SESSION['inventory'][$newId] = [
            'id' => $newId,
            'name' => $name,
            'brand' => $brand,
            'category' => $category,
            'price' => (float)$price,
            'old_price' => empty($old_price) ? null : (float)$old_price,
            'stock' => (int)$stock,
            'rating' => empty($rating) ? 0 : (int)$rating,
            'badge' => empty($badge) ? null : $badge,
            'badge_type' => empty($badge_type) ? null : $badge_type,
            'image' => empty($image) ? 'https://placehold.co/400x300/eeeeee/1D1D1F?text=No+Image' : $image,
            'desc' => $desc,
            'featured' => false // Default to false
        ];
    }

    // Edit an existing product
    public function editProduct($id, $name, $brand, $category, $price, $old_price, $stock, $rating, $badge, $badge_type, $image, $desc)
    {
        $id = (int)$id; // Force ID to be an integer

        if (isset($_SESSION['inventory'][$id])) {
            $_SESSION['inventory'][$id]['name'] = $name;
            $_SESSION['inventory'][$id]['brand'] = $brand;
            $_SESSION['inventory'][$id]['category'] = $category;
            $_SESSION['inventory'][$id]['price'] = (float)$price;
            $_SESSION['inventory'][$id]['old_price'] = empty($old_price) ? null : (float)$old_price;
            $_SESSION['inventory'][$id]['stock'] = (int)$stock;
            $_SESSION['inventory'][$id]['rating'] = empty($rating) ? 0 : (int)$rating;
            $_SESSION['inventory'][$id]['badge'] = empty($badge) ? null : $badge;
            $_SESSION['inventory'][$id]['badge_type'] = empty($badge_type) ? null : $badge_type;
            $_SESSION['inventory'][$id]['image'] = empty($image) ? 'https://placehold.co/400x300/eeeeee/1D1D1F?text=No+Image' : $image;
            $_SESSION['inventory'][$id]['desc'] = $desc;
        }
    }

    // Delete a product
    public function deleteProduct($id)
    {
        $id = (int)$id; // Ensure ID is an integer

        if (isset($_SESSION['inventory'][$id])) {
            unset($_SESSION['inventory'][$id]);
        }
    }
}
