<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../classes/Inventory.php';

try {
    $inventoryManager = new Inventory();
    $query = trim($_GET['q'] ?? '');
    
    // Validate query
    if (empty($query) || strlen($query) < 2) {
        http_response_code(400);
        echo json_encode(['error' => 'Query too short']);
        exit;
    }
    
    $allProducts = $inventoryManager->getAllProducts();
    $results = [];
    
    // Search in products
    $queryLower = mb_strtolower($query, 'UTF-8');
    foreach ($allProducts as $product) {
        $haystack = mb_strtolower(implode(' ', [
            $product['name'] ?? '',
            $product['brand'] ?? '',
            $product['category'] ?? '',
        ]), 'UTF-8');
        
        if (mb_strpos($haystack, $queryLower, 0, 'UTF-8') !== false) {
            $results[] = [
                'id' => $product['id'] ?? '',
                'name' => $product['name'] ?? 'Unknown',
                'brand' => $product['brand'] ?? '',
                'price' => $product['price'] ?? 0,
                'image' => $product['image'] ?? '',
                'category' => $product['category'] ?? '',
            ];
        }
    }
    
    // Limit to 8 results
    $results = array_slice($results, 0, 8);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($results),
        'results' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
