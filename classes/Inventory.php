<?php
/**
 * Inventory helper — session-backed fake DB adapter.
 *
 * @package ApexGear
 */
class Inventory
{
    // No external DB connection in session-backed mode

    /** @var array */
    private $currentProduct = [];

    public function __construct()
    {
        require_once __DIR__ . '/../includes/storage.php';
        // Storage initializes `\$_SESSION['fake_db']` and persistence helpers.
    }

    private function refreshSharedSeed()
    {
        // Seed export and DB file operations removed for static/no-DB mode.
        return;
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

    /**
     * Normalize an image path or SVG/data URL to a safe string for display.
     *
     * @param string|mixed $image
     * @return string
     */
    public static function normalizeProductImagePath($image)
    {
        $image = trim((string)$image);

        if ($image === '') {
            return 'https://placehold.co/400x300/eeeeee/1D1D1F?text=No+Image';
        }

        if (stripos($image, '<svg') !== false || preg_match('#^(https?:|data:)#i', $image)) {
            return $image;
        }

        $image = str_replace('\\', '/', $image);

        while (strpos($image, '../') === 0) {
            $image = substr($image, 3);
        }

        return ltrim($image, '/');
    }

    /**
     * Get product image source (resolves relative paths).
     *
     * @param string|mixed $image
     * @param string $basePath
     * @return string
     */
    public static function getProductImageSrc($image, $basePath = '')
    {
        $image = self::normalizeProductImagePath($image);

        if (stripos($image, '<svg') !== false || preg_match('#^(https?:|data:)#i', $image)) {
            return $image;
        }

        return $basePath . $image;
    }

    /**
     * Get all products from the session-backed store.
     *
     * @param bool $includeArchived  When true, archived products are included in results.
     * @return array<int,array> Map of product_id => product data
     */
    public function getAllProducts(bool $includeArchived = false): array
    {
        $products = [];

        if (!empty($_SESSION['fake_db']['products'])) {
            foreach ($_SESSION['fake_db']['products'] as $p) {
                $isArchived = !empty($p['is_archived']);

                // Skip archived products unless caller wants them
                if ($isArchived && !$includeArchived) continue;

                $categoryName = isset($p['category']) && $p['category'] ? strtolower($p['category']) : 'uncategorized';
                if (substr($categoryName, -1) === 's') $categoryName = substr($categoryName, 0, -1);

                $products[$p['product_id']] = [
                    'id'            => $p['product_id'],
                    'name'          => $p['name'],
                    'brand'         => $p['brand'] ?? null,
                    'category'      => $categoryName,
                    'price'         => (float)($p['price'] ?? 0),
                    'old_price'     => isset($p['old_price']) ? (float)$p['old_price'] : null,
                    'stock'         => (int)($p['stock'] ?? 0),
                    'rating'        => (int)($p['rating'] ?? 0),
                    'badge'         => $p['badge'] ?? null,
                    'badge_type'    => $p['badge_type'] ?? null,
                    'image'         => $p['image'] ?? 'https://placehold.co/400x300/eeeeee/1D1D1F?text=No+Image',
                    'sales'         => rand(50, 2000),
                    'desc'          => $p['desc'] ?? '',
                    'shipping_time' => $p['shipping_time'] ?? '1-3 business days',
                    // Sale fields
                    'sale_percent'  => $p['sale_percent'] ?? null,
                    'sale_expiry'   => $p['sale_expiry']  ?? null,
                    // Archive fields
                    'archived'      => $isArchived,
                    'archived_at'   => $p['archived_at'] ?? null,
                ];
            }
        }

        return $products;
    }

    /**
     * Find a single product by id.
     *
     * @param int|string $id
     * @return array|null
     */
    public function findProductById($id)
    {
        $all = $this->getAllProducts();
        $id = (int)$id;
        return $all[$id] ?? null;
    }

    private function getBrandId($brandName)
    {
        // In session-backed mode we do not maintain a brands table.
        // Return null to indicate no numeric brand id is available.
        return null;
    }

    private function getCategoryId($categoryName)
    {
        // Session-backed mode does not use category IDs. Return null.
        return null;
    }

    /**
     * Add a product to the fake DB.
     *
     * @param string $name
     * @param mixed $brand
     * @param mixed $category
     * @param float|int|string $price
     * @param float|int|string|null $old_price
     * @param int|string $stock
     * @param int|string $rating
     * @param string|null $badge
     * @param string|null $badge_type
     * @param string $image
     * @param string $desc
     * @param string|null $shipping_time
     * @param int|string|null $sale_percent
     * @param string|null $sale_expiry
     * @return int New product id
     */
    public function addProduct($name, $brand, $category, $price, $old_price, $stock, $rating, $badge, $badge_type, $image, $desc, $shipping_time = null, $sale_percent = null, $sale_expiry = null)
    {
        // Session-backed addProduct
        $price        = (float)$price;
        $old_price    = $old_price === '' ? null : (float)$old_price;
        $stock        = (int)$stock;
        $rating       = empty($rating) ? 0 : (int)$rating;
        $badge        = $badge === '' ? null : $badge;
        $badge_type   = $badge_type === '' ? null : $badge_type;
        $image        = self::normalizeProductImagePath($image);
        $shipping_time = trim((string)$shipping_time);
        if ($shipping_time === '') $shipping_time = null;
        $sale_percent = ($sale_percent !== '' && $sale_percent !== null) ? (int)$sale_percent : null;
        $sale_expiry  = ($sale_expiry  !== '' && $sale_expiry  !== null) ? trim($sale_expiry) : null;

        if (!isset($_SESSION['fake_db'])) $_SESSION['fake_db'] = [];
        if (!isset($_SESSION['fake_db']['products'])) $_SESSION['fake_db']['products'] = [];
        if (!isset($_SESSION['fake_db']['next_product_id'])) $_SESSION['fake_db']['next_product_id'] = 100;

        $newId = $_SESSION['fake_db']['next_product_id']++;

        $product = [
            'product_id'    => $newId,
            'name'          => $name,
            'brand'         => $brand,
            'category'      => $category,
            'price'         => $price,
            'old_price'     => $old_price,
            'stock'         => $stock,
            'rating'        => $rating,
            'badge'         => $badge,
            'badge_type'    => $badge_type,
            'image'         => $image,
            'desc'          => $desc,
            'shipping_time' => $shipping_time,
            'sale_percent'  => $sale_percent,
            'sale_expiry'   => $sale_expiry,
            'is_archived'   => 0,
        ];

        $_SESSION['fake_db']['products'][] = $product;
        if (function_exists('save_fake_db')) save_fake_db();

        return $newId;
    }

    /**
     * Edit an existing product.
     *
     * @param int|string $id
     * @param string $name
     * @param mixed $brand
     * @param mixed $category
     * @param float|int|string $price
     * @param float|int|string|null $old_price
     * @param int|string $stock
     * @param int|string $rating
     * @param string|null $badge
     * @param string|null $badge_type
     * @param string $image
     * @param string $desc
     * @param string|null $shipping_time
     * @param int|string|null $sale_percent
     * @param string|null $sale_expiry
     * @return bool
     */
    public function editProduct($id, $name, $brand, $category, $price, $old_price, $stock, $rating, $badge, $badge_type, $image, $desc, $shipping_time = null, $sale_percent = null, $sale_expiry = null)
    {
        // Session-backed editProduct
        $id           = (int)$id;
        $price        = (float)$price;
        $old_price    = $old_price === '' ? null : (float)$old_price;
        $stock        = (int)$stock;
        $rating       = empty($rating) ? 0 : (int)$rating;
        $badge        = $badge === '' ? null : $badge;
        $badge_type   = $badge_type === '' ? null : $badge_type;
        $image        = self::normalizeProductImagePath($image);
        $shipping_time = trim((string)$shipping_time);
        if ($shipping_time === '') $shipping_time = null;
        $sale_percent = ($sale_percent !== '' && $sale_percent !== null) ? (int)$sale_percent : null;
        $sale_expiry  = ($sale_expiry  !== '' && $sale_expiry  !== null) ? trim($sale_expiry) : null;

        if (empty($_SESSION['fake_db']['products'])) return false;

        foreach ($_SESSION['fake_db']['products'] as &$p) {
            if ((int)$p['product_id'] === $id) {
                $p['name']          = $name;
                $p['brand']         = $brand;
                $p['category']      = $category;
                $p['price']         = $price;
                $p['old_price']     = $old_price;
                $p['stock']         = $stock;
                $p['rating']        = $rating;
                $p['badge']         = $badge;
                $p['badge_type']    = $badge_type;
                $p['image']         = $image;
                $p['desc']          = $desc;
                $p['shipping_time'] = $shipping_time;
                $p['sale_percent']  = $sale_percent;
                $p['sale_expiry']   = $sale_expiry;
                if (function_exists('save_fake_db')) save_fake_db();
                return true;
            }
        }

        return false;
    }

    /**
     * Soft-archive a product (hides it from the store, recoverable).
     * Called by apex26admin.php as archiveProduct().
     *
     * @param int|string $id
     * @return bool
     */
    public function archiveProduct($id): bool
    {
        $id = (int)$id;
        if (empty($_SESSION['fake_db']['products'])) return false;
        foreach ($_SESSION['fake_db']['products'] as &$p) {
            if ((int)$p['product_id'] === $id) {
                $p['is_archived'] = 1;
                $p['archived_at'] = date('Y-m-d H:i:s');
                if (function_exists('save_fake_db')) save_fake_db();
                return true;
            }
        }
        return false;
    }

    /**
     * Restore an archived product back to the store.
     * Called by manage_archives.php as restoreProduct().
     *
     * @param int|string $id
     * @return bool
     */
    public function restoreProduct($id): bool
    {
        $id = (int)$id;
        if (empty($_SESSION['fake_db']['products'])) return false;
        foreach ($_SESSION['fake_db']['products'] as &$p) {
            if ((int)$p['product_id'] === $id) {
                $p['is_archived'] = 0;
                $p['archived_at'] = null;
                if (function_exists('save_fake_db')) save_fake_db();
                return true;
            }
        }
        return false;
    }

    /**
     * Permanently delete a product (cannot be undone).
     * Called by manage_archives.php as deleteProduct().
     *
     * @param int|string $id
     * @return bool
     */
    public function deleteProduct($id): bool
    {
        $id = (int)$id;
        if (empty($_SESSION['fake_db']['products'])) return false;
        foreach ($_SESSION['fake_db']['products'] as $key => $p) {
            if ((int)$p['product_id'] === $id) {
                array_splice($_SESSION['fake_db']['products'], $key, 1);
                if (function_exists('save_fake_db')) save_fake_db();
                return true;
            }
        }
        return false;
    }

    /* Orders and Users (session-backed) */
    /**
     * Create a new order record.
     *
     * @param int|string $userId
     * @param string $shipping_address
     * @param string $shipping_city
     * @param string $shipping_zip
     * @param float|int|string $total_amount
     * @param string $payment_method
     * @param string $payment_reference
     * @return int Order ID
     */
    public function addOrder($userId, $shipping_address, $shipping_city, $shipping_zip, $total_amount, $payment_method, $payment_reference = '')
    {
        if (!isset($_SESSION['fake_db'])) $_SESSION['fake_db'] = [];
        if (!isset($_SESSION['fake_db']['orders'])) $_SESSION['fake_db']['orders'] = [];
        if (!isset($_SESSION['fake_db']['next_order_id'])) $_SESSION['fake_db']['next_order_id'] = 1;

        $orderId = $_SESSION['fake_db']['next_order_id']++;
        $record = [
            'order_id' => $orderId,
            'user_id' => $userId,
            'shipping_address' => $shipping_address,
            'shipping_city' => $shipping_city,
            'shipping_zip' => $shipping_zip,
            'reference_number' => 'APX-' . strtoupper(uniqid()),
            'total_amount' => (float)$total_amount,
            'order_status' => 'On Process',
            'payment_method' => $payment_method,
            'payment_reference' => $payment_reference,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $_SESSION['fake_db']['orders'][] = $record;
        if (function_exists('save_fake_db')) save_fake_db();
        return $orderId;
    }

    /**
     * Add items for an order.
     *
     * @param int|string $orderId
     * @param array $items
     * @return bool
     */
    public function addOrderItems($orderId, array $items)
    {
        if (!isset($_SESSION['fake_db'])) $_SESSION['fake_db'] = [];
        if (!isset($_SESSION['fake_db']['order_items'])) $_SESSION['fake_db']['order_items'] = [];

        foreach ($items as $it) {
            $entry = [
                'order_id' => $orderId,
                'product_id' => isset($it['product_id']) ? (int)$it['product_id'] : null,
                'purchased_price' => isset($it['purchased_price']) ? (float)$it['purchased_price'] : 0,
                'quantity' => isset($it['quantity']) ? (int)$it['quantity'] : 1,
                'name' => $it['name'] ?? null,
                'image' => $it['image'] ?? null,
            ];
            $_SESSION['fake_db']['order_items'][] = $entry;
        }

        if (function_exists('save_fake_db')) save_fake_db();
        return true;
    }

    /**
     * Get list of orders for a user.
     *
     * @param int|string $userId
     * @return array
     */
    public function getOrdersByUser($userId)
    {
        $out = [];
        if (empty($_SESSION['fake_db']['orders'])) return $out;
        foreach ($_SESSION['fake_db']['orders'] as $ord) {
            if ((int)$ord['user_id'] === (int)$userId) $out[] = $ord;
        }
        return $out;
    }

    /**
     * Get order items for an order.
     *
     * @param int|string $orderId
     * @return array
     */
    public function getOrderItems($orderId)
    {
        $out = [];
        if (empty($_SESSION['fake_db']['order_items'])) return $out;
        foreach ($_SESSION['fake_db']['order_items'] as $it) {
            if ((int)$it['order_id'] === (int)$orderId) $out[] = $it;
        }
        return $out;
    }

    /**
     * Get all users (admin view).
     *
     * @return array
     */
    public function getAllUsers(): array
    {
        if (empty($_SESSION['fake_db']['users'])) return [];
        $out = [];
        foreach ($_SESSION['fake_db']['users'] as $u) {
            $out[] = [
                'id'            => $u['user_id'],
                'user_id'       => $u['user_id'],
                'username'      => $u['username']   ?? '',
                'email'         => $u['email']       ?? '',
                'name'          => trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: ($u['username'] ?? ''),
                'first_name'    => $u['first_name']  ?? null,
                'last_name'     => $u['last_name']   ?? null,
                'middle_name'   => $u['middle_name'] ?? null,
                'phone'         => $u['phone']       ?? null,
                'gender'        => $u['gender']      ?? null,
                'birthday'      => $u['birthday']    ?? null,
                'role'          => $u['role']        ?? 'customer',
                'bio'           => $u['bio']         ?? null,
                'profile_picture' => $u['profile_picture'] ?? null,
                'email_verified'  => $u['email_verified']  ?? false,
                'shipping_address' => $u['shipping_address'] ?? ($u['address'] ?? null),
                'created_at'    => $u['created_at']  ?? null,
                'last_login'    => $u['last_login']  ?? null,
            ];
        }
        return $out;
    }

    /**
     * Get all orders in the system (admin view).
     * Enriches orders with items_count and user contact info when available.
     *
     * @return array<int,array>
     */
    public function getAllOrders(): array
    {
        $out = [];
        if (empty($_SESSION['fake_db']['orders'])) return $out;
        foreach ($_SESSION['fake_db']['orders'] as $ord) {
            $items   = $this->getOrderItems($ord['order_id']);
            $user    = $this->getUserById($ord['user_id'] ?? 0);
            $ordCopy = $ord;

            // Normalise field names so all admin pages can use the same keys
            $ordCopy['id']             = $ord['order_id'];
            $ordCopy['total']          = $ord['total_amount'] ?? $ord['total'] ?? 0;
            $ordCopy['items']          = $items;
            $ordCopy['items_count']    = count($items);
            $ordCopy['customer_name']  = $user ? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['username'] ?? 'Guest') : ($ord['customer_name'] ?? 'Guest');
            $ordCopy['customer_email'] = $user['email'] ?? ($ord['email'] ?? '');
            $ordCopy['username']       = $user['username'] ?? ($ord['username'] ?? 'Guest');
            $ordCopy['email']          = $user['email']    ?? ($ord['email']    ?? '');
            $ordCopy['remarks']        = $ord['remarks']   ?? '';
            $ordCopy['updated_at']     = $ord['updated_at'] ?? null;

            $out[] = $ordCopy;
        }
        return $out;
    }

    /**
     * Update an order's status.
     *
     * @param int|string $orderId
     * @param string $newStatus
     * @param string $remarks
     * @return bool
     */
    public function updateOrderStatus($orderId, $newStatus, $remarks = '')
    {
        if (empty($_SESSION['fake_db']['orders'])) return false;
        foreach ($_SESSION['fake_db']['orders'] as &$ord) {
            if ((int)$ord['order_id'] === (int)$orderId) {
                $ord['order_status'] = $newStatus;
                $ord['remarks']      = $remarks;
                $ord['updated_at']   = date('Y-m-d H:i:s');
                if (function_exists('save_fake_db')) save_fake_db();
                return true;
            }
        }
        return false;
    }

    /**
     * Update user fields.
     *
     * @param int|string $userId
     * @param array $data
     * @return bool
     */
    public function updateUser($userId, array $data)
    {
        if (!isset($_SESSION['fake_db'])) $_SESSION['fake_db'] = [];
        if (!isset($_SESSION['fake_db']['users'])) $_SESSION['fake_db']['users'] = [];

        foreach ($_SESSION['fake_db']['users'] as &$u) {
            if ((int)$u['user_id'] === (int)$userId) {
                foreach ($data as $k => $v) {
                    $u[$k] = $v;
                }
                if (function_exists('save_fake_db')) save_fake_db();
                return true;
            }
        }
        // If user not found, optionally add
        return false;
    }

    /**
     * Find user by username.
     *
     * @param string $username
     * @return array|null
     */
    public function findUserByUsername($username)
    {
        if (empty($_SESSION['fake_db']['users'])) return null;
        foreach ($_SESSION['fake_db']['users'] as $u) {
            if (strtolower($u['username']) === strtolower($username)) return $u;
        }
        return null;
    }

    /**
     * Find user by email.
     *
     * @param string $email
     * @return array|null
     */
    public function findUserByEmail($email)
    {
        if (empty($_SESSION['fake_db']['users'])) return null;
        foreach ($_SESSION['fake_db']['users'] as $u) {
            if (strtolower($u['email']) === strtolower($email)) return $u;
        }
        return null;
    }

    /**
     * Create a new user record.
     *
     * @param string $username
     * @param string $email
     * @param string $password_hash
     * @param string $role
     * @param string|null $first_name
     * @param string|null $last_name
     * @param string|null $middle_name
     * @param string|null $gender
     * @return int New user id
     */
    public function createUser($username, $email, $password_hash, $role = 'customer', $first_name = null, $last_name = null, $middle_name = null, $gender = null)
    {
        if (!isset($_SESSION['fake_db'])) $_SESSION['fake_db'] = [];
        if (!isset($_SESSION['fake_db']['users'])) $_SESSION['fake_db']['users'] = [];
        if (!isset($_SESSION['fake_db']['next_user_id'])) $_SESSION['fake_db']['next_user_id'] = 1000;

        $user_id = $_SESSION['fake_db']['next_user_id']++;
        $userRecord = [
            'user_id'         => $user_id,
            'username'        => $username,
            'email'           => $email,
            'password_hash'   => $password_hash,
            'role'            => $role,
            'first_name'      => $first_name,
            'last_name'       => $last_name,
            'middle_name'     => $middle_name,
            'profile_picture' => null,
            'bio'             => null,
            'gender'          => $gender,
            'birthday'        => null,
            'phone'           => null,
            'email_verified'  => false,
            'shipping_address'=> null,
            'created_at'      => date('Y-m-d H:i:s'),
            'last_login'      => null,
        ];
        $_SESSION['fake_db']['users'][] = $userRecord;
        if (function_exists('save_fake_db')) save_fake_db();
        return $user_id;
    }

    /**
     * Record a user's last login timestamp.
     *
     * @param int|string $userId
     * @return bool
     */
    public function recordLogin($userId): bool
    {
        return $this->updateUser($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    /**
     * Retrieve a user by id.
     *
     * @param int|string $userId
     * @return array|null
     */
    public function getUserById($userId)
    {
        if (empty($_SESSION['fake_db']['users'])) return null;
        foreach ($_SESSION['fake_db']['users'] as $u) {
            if ((int)$u['user_id'] === (int)$userId) return $u;
        }
        return null;
    }

    /**
     * Toggle favorite for user and product.
     *
     * @param int|string $userId
     * @param int|string $productId
     * @return string 'added'|'removed'
     */
    public function toggleFavorite($userId, $productId)
    {
        if (!isset($_SESSION['fake_db'])) $_SESSION['fake_db'] = [];
        if (!isset($_SESSION['fake_db']['favorites'])) $_SESSION['fake_db']['favorites'] = [];
        if (!isset($_SESSION['fake_db']['favorites'][$userId])) $_SESSION['fake_db']['favorites'][$userId] = [];

        $idx = array_search((int)$productId, $_SESSION['fake_db']['favorites'][$userId]);
        if ($idx !== false && $idx !== null) {
            array_splice($_SESSION['fake_db']['favorites'][$userId], $idx, 1);
            if (function_exists('save_fake_db')) save_fake_db();
            return 'removed';
        }

        $_SESSION['fake_db']['favorites'][$userId][] = (int)$productId;
        if (function_exists('save_fake_db')) save_fake_db();
        return 'added';
    }

    /**
     * Get favorite product ids for a user.
     *
     * @param int|string $userId
     * @return array<int>
     */
    public function getFavorites($userId)
    {
        if (empty($_SESSION['fake_db']['favorites'][$userId])) return [];
        return $_SESSION['fake_db']['favorites'][$userId];
    }
}
