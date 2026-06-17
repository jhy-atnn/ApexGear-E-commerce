<?php
session_start();

// 1. Secure the admin page
if (!isset($_SESSION['admin'])) {
    header("Location: ../admingear.php");
    exit;
}

// 2. Completely remove storage.php dependency
require_once __DIR__ . '/../classes/Inventory.php';

$status_message = '';
$status_class   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $admin_id = isset($_SESSION['admin']['id']) ? intval($_SESSION['admin']['id']) : null;

    if ($action === 'add' || $action === 'edit') {
        $name          = trim($_POST['name']          ?? '');
        $brand         = trim($_POST['brand']         ?? '');
        $category      = trim($_POST['category']      ?? '');
        $price         = floatval($_POST['price']     ?? 0);
        $stock         = intval($_POST['stock']       ?? 0);
        $sale_percent  = intval($_POST['sale_percent']?? 0);
        $sale_expiry   = trim($_POST['sale_expiry']   ?? '');
        $shipping_time = trim($_POST['shipping_time'] ?? '');
        $desc          = trim($_POST['desc']          ?? '');
        $image         = '';
        $image_source  = $_POST['image_source'] ?? 'upload';

        // Securely handle image uploads and set the path for the DB
        if ($image_source === 'upload' && isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === 0) {
            $target_dir = __DIR__ . "/../assets/images/products/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $clean  = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES["image_upload"]["name"]));
            $unique = time() . "_" . $clean;
            $target = $target_dir . $unique;
            
            if (move_uploaded_file($_FILES["image_upload"]["tmp_name"], $target)) {
                $image = "assets/images/products/" . $unique;
            }
        } elseif ($image_source === 'url' && !empty($_POST['image'])) {
            $image = trim($_POST['image']);
        }

        $inv = new Inventory();
        
        // Pass data to the OOP database methods
        if ($action === 'add') {
            $newId = $inv->addProduct($name, $brand, $category, $price, '', $stock, '', '', '', $image, $desc, $shipping_time, $sale_percent, $sale_expiry);
            $inv->logAdminActivity('product_add', "Added product: {$name} (ID {$newId}).", $admin_id);
            $status_message = 'Product successfully published to store.';
            $status_class   = 'success';
        } else {
            $product_id = intval($_POST['product_id'] ?? 0);
            
            // Retain old image if no new one was uploaded
            if (empty($image)) {
                $tmp = $inv->getAllProducts();
                if (isset($tmp[$product_id])) {
                    $image = $tmp[$product_id]['image'];
                }
            }
            
            $inv->editProduct($product_id, $name, $brand, $category, $price, '', $stock, '', '', '', $image, $desc, $shipping_time, $sale_percent, $sale_expiry);
            $inv->logAdminActivity('product_edit', "Edited product: {$name} (ID {$product_id}).", $admin_id);
            $status_message = 'Product updated successfully.';
            $status_class   = 'info';
        }
    } elseif ($action === 'archive') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $inv = new Inventory();
        $inv->archiveProduct($product_id);
        $inv->logAdminActivity('product_archive', "Archived product ID {$product_id}.", $admin_id);
        $status_message = 'Product moved to archives.';
        $status_class   = 'warn';
    }
}

// Fetch live database data for the UI tables
$inv           = new Inventory();
$products      = $inv->getAllProducts();
$totalProducts = count($products);

// Separate active sales for the "Active Deals" tab
$onSale = array_filter(
    $products,
    fn($p) =>
    !empty($p['sale_percent']) && (empty($p['sale_expiry']) || strtotime($p['sale_expiry']) >= time())
);
usort($onSale, fn($a, $b) => intval($b['sale_percent']) - intval($a['sale_percent']));
?>
    
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | ApeX Gear Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin-style.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-w: 260px;
            --sidebar-bg: #080f1e;
            --sidebar-hover: rgba(0, 194, 255, .08);
            --accent: #00c2ff;
            --panel-bg: #f4f6fb;
            --card-bg: #ffffff;
            --border: #e3e8f0;
            --text-main: #0d1b2e;
            --text-muted: #6b7a99;
            --blue: #0b2fa8;
            --danger: #ff3b5c;
            --success: #00d68f;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Barlow', sans-serif;
            background: var(--panel-bg);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 26px 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            text-decoration: none;
        }

        .sidebar-brand img {
            height: 34px;
        }

        .t1 {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-size: 1.45rem;
            color: #fff;
        }

        .t2 {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-size: 1.45rem;
            color: var(--accent);
        }

        .sidebar-badge {
            margin-left: auto;
            background: var(--accent);
            color: var(--sidebar-bg);
            font-size: .6rem;
            font-weight: 800;
            letter-spacing: .06em;
            padding: 2px 7px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .sidebar-section-label {
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .25);
            padding: 20px 24px 8px;
        }

        .sidebar-nav {
            flex: 1;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 11px 24px;
            color: rgba(255, 255, 255, .58);
            text-decoration: none;
            font-size: .88rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all .18s;
        }

        .sidebar-nav a:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .sidebar-nav a.active {
            background: rgba(0, 194, 255, .12);
            color: var(--accent);
            border-left-color: var(--accent);
            font-weight: 600;
        }

        .sidebar-nav a i {
            width: 18px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255, 255, 255, .07);
        }

        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, .4);
            font-size: .82rem;
            text-decoration: none;
        }

        .sidebar-footer a:hover {
            color: #fff;
        }

        /* Layout */
        .main-wrap {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            text-transform: uppercase;
        }

        .page-body {
            padding: 28px 32px 48px;
            flex: 1;
        }

        /* Tabs */
        .tab-nav {
            display: flex;
            gap: 0;
            border-bottom: 2px solid var(--border);
            margin-bottom: 24px;
        }

        .tab-btn {
            padding: 10px 22px;
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            color: var(--text-muted);
            cursor: pointer;
            transition: all .18s;
        }

        .tab-btn.active {
            color: var(--blue);
            border-bottom-color: var(--blue);
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        /* Panel */
        .panel {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
        }

        .panel-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .panel-body {
            padding: 22px 24px;
        }

        /* Form fields */
        .field-group {
            margin-bottom: 14px;
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .field-label {
            display: block;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .field-control {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: .88rem;
            font-family: 'Barlow', sans-serif;
            color: var(--text-main);
            background: #fff;
            outline: none;
            transition: border .18s;
        }

        .field-control:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(11, 47, 168, .1);
        }

        .btn-submit {
            width: 100%;
            padding: 11px;
            background: var(--blue);
            color: #fff;
            border: none;
            border-radius: 9px;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.05rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            cursor: pointer;
            margin-top: 4px;
            transition: background .15s;
        }

        .btn-submit:hover {
            background: #0928a0;
        }

        .radio-group {
            display: flex;
            gap: 18px;
            margin-top: 4px;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .84rem;
            cursor: pointer;
        }

        /* Table */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inv-table thead th {
            background: #f8fafd;
            padding: 10px 14px;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .inv-table tbody td {
            padding: 14px 14px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .inv-table tbody tr:last-child td {
            border-bottom: none;
        }

        .inv-table tbody tr:hover td {
            background: rgba(0, 194, 255, .025);
        }

        .product-thumb {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            overflow: hidden;
            background: #f0f3f9;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .product-thumb img,
        .product-thumb svg {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-name {
            font-size: .88rem;
            font-weight: 600;
        }

        .product-meta {
            font-size: .7rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-top: 2px;
        }

        .price-tag {
            font-weight: 700;
            color: var(--blue);
            font-size: .9rem;
        }

        .stock-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 700;
        }

        .stock-badge.ok {
            background: rgba(0, 214, 143, .12);
            color: #00a870;
        }

        .stock-badge.low {
            background: rgba(255, 59, 92, .12);
            color: var(--danger);
        }

        .stock-badge.zero {
            background: rgba(107, 122, 153, .12);
            color: var(--text-muted);
        }

        .sale-pill {
            display: inline-block;
            background: rgba(255, 59, 92, .1);
            color: var(--danger);
            font-size: .68rem;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 10px;
        }

        .shipping-pill {
            display: inline-block;
            background: rgba(0, 194, 255, .1);
            color: #007aad;
            font-size: .68rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
        }

        .act-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 11px;
            border-radius: 7px;
            font-size: .75rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid transparent;
            transition: all .15s;
            text-decoration: none;
        }

        .act-btn-edit {
            border-color: rgba(11, 47, 168, .3);
            color: var(--blue);
            background: rgba(11, 47, 168, .05);
        }

        .act-btn-edit:hover {
            background: var(--blue);
            color: #fff;
            border-color: var(--blue);
        }

        .act-btn-archive {
            border-color: rgba(245, 197, 24, .35);
            color: #8a6d00;
            background: rgba(245, 197, 24, .07);
        }

        .act-btn-archive:hover {
            background: #f5c518;
            color: #000;
            border-color: #f5c518;
        }

        /* Deals cards */
        .deals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
        }

        .deal-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        .deal-card .deal-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--danger);
            color: #fff;
            font-size: .7rem;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: 8px;
        }

        .deal-card .deal-body {
            padding: 14px;
        }

        .deal-card .deal-name {
            font-weight: 700;
            font-size: .88rem;
            margin-bottom: 4px;
        }

        .deal-card .deal-price {
            color: var(--blue);
            font-weight: 800;
        }

        .deal-card .deal-sale-price {
            color: var(--danger);
            font-size: .82rem;
        }

        .deal-card .deal-expiry {
            font-size: .7rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .featured-crown {
            color: #f5c518;
            font-size: .85rem;
        }

        /* Alert */
        .apex-alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 18px;
            border-radius: 10px;
            font-size: .88rem;
            font-weight: 500;
            margin-bottom: 20px;
            position: relative;
        }

        .apex-alert.success {
            background: rgba(0, 214, 143, .1);
            border-left: 4px solid var(--success);
            color: #007a52;
        }

        .apex-alert.info {
            background: rgba(11, 47, 168, .08);
            border-left: 4px solid var(--blue);
            color: var(--blue);
        }

        .apex-alert.warn {
            background: rgba(245, 197, 24, .1);
            border-left: 4px solid #f5c518;
            color: #8a6d00;
        }

        .apex-alert .close-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: .9rem;
            color: inherit;
            opacity: .6;
            cursor: pointer;
        }

        /* Modal */
        .modal-content {
            border: none;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .18);
        }

        .modal-header-custom {
            padding: 20px 26px;
            background: var(--sidebar-bg);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header-custom .modal-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 1.3rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .modal-header-custom .btn-close {
            filter: invert(1);
            opacity: .6;
        }

        .modal-body-custom {
            padding: 26px;
            background: #fff;
        }

        .modal-footer-custom {
            padding: 16px 26px;
            background: #f8fafd;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-cancel {
            padding: 9px 20px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            background: #fff;
            color: var(--text-muted);
            font-weight: 600;
            font-size: .85rem;
            cursor: pointer;
        }

        .btn-save {
            padding: 9px 24px;
            border: none;
            border-radius: 8px;
            background: var(--blue);
            color: #fff;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            text-transform: uppercase;
            cursor: pointer;
        }

        .btn-save:hover {
            background: #0928a0;
        }

        @media (max-width: 768px) {
            .main-wrap {
                margin-left: 0;
            }

            .page-body {
                padding: 18px 16px 40px;
            }

            .field-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <a href="../index.php" class="sidebar-brand">
            <img src="../assets/images/ApeX Logo.png" alt="ApeX Gear">
            <div><span class="t1">ApeX </span><span class="t2">Gear</span></div>
            <span class="sidebar-badge">Admin</span>
        </a>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Main</div>
            <a href="apex26admin.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="manage_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="manage_products.php" class="active"><i class="fas fa-boxes"></i> Manage Products</a>
            <a href="manage_archives.php"><i class="fas fa-archive"></i> Archives</a>
            <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
            <a href="report.php"><i class="fas fa-chart-pie"></i> Reports &amp; Analytics</a>
            <a href="manage_deals.php"><i class="fas fa-percentage"></i> Deals &amp; Promos</a>
            <div class="sidebar-section-label">Store</div>
            <a href="../index.php" target="_blank"><i class="fas fa-store"></i> View Live Store</a>
            <a href="../index.php?page=products" target="_blank"><i class="fas fa-tags"></i> Product Catalog</a>
        </nav>
        <div class="sidebar-footer" style="display: flex; flex-direction: column; gap: 14px;">
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Site</a>
            <a href="admin_logout.php" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <div class="main-wrap">
        <header class="topbar">
            <span class="topbar-title">Manage Products</span>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-1"></i> Add Product
            </button>
        </header>

        <main class="page-body">

            <?php if ($status_message): ?>
                <div class="apex-alert <?php echo $status_class; ?>">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($status_message); ?></span>
                    <button class="close-btn" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="tab-nav">
                <button class="tab-btn active" onclick="switchTab('tab-all', this)">All Products (<?php echo $totalProducts; ?>)</button>
                <button class="tab-btn" onclick="switchTab('tab-deals', this)">Active Deals (<?php echo count($onSale); ?>)</button>
                <button class="tab-btn" onclick="switchTab('tab-add', this)">+ Add New</button>
            </div>

            <!-- ALL PRODUCTS TAB -->
            <div class="tab-pane active" id="tab-all">
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-title">Product Inventory</span>
                        <span style="background:var(--accent);color:var(--sidebar-bg);font-size:.7rem;font-weight:800;padding:4px 12px;border-radius:20px;letter-spacing:.06em;"><?php echo $totalProducts; ?> Items</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="inv-table">
                            <thead>
                                <tr>
                                    <th style="padding-left:20px;">ID</th>
                                    <th>Product</th>
                                    <th>Price / Sale</th>
                                    <th>Shipping</th>
                                    <th>Stock</th>
                                    <th style="text-align:right;padding-right:20px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted);">
                                            <i class="fas fa-box-open" style="font-size:2rem;opacity:.3;display:block;margin-bottom:10px;"></i>
                                            No products yet. Add your first one!
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product):
                                        $s            = (int)($product['stock'] ?? 0);
                                        $stockClass   = $s === 0 ? 'zero' : ($s < 5 ? 'low' : 'ok');
                                        $stockLabel   = $s === 0 ? 'Out' : $s;
                                        $productImage = Inventory::getProductImageSrc($product['image'] ?? '', '../');
                                        $salePct      = $product['sale_percent'] ?? '';
                                        $saleExp      = $product['sale_expiry']  ?? '';
                                        $saleExpired  = !empty($saleExp) && strtotime($saleExp) < time();
                                        $saleActive   = !empty($salePct) && !$saleExpired;
                                    ?>
                                        <tr>
                                            <td class="text-muted fw-bold" style="padding-left:20px;font-size:.8rem;">#<?php echo $product['id']; ?></td>
                                            <td>
                                                <div style="display:flex;align-items:center;gap:12px;">
                                                    <div class="product-thumb">
                                                        <?php if (strpos($product['image'] ?? '', '<svg') !== false): echo $product['image'];
                                                        else: ?><img src="<?php echo htmlspecialchars($productImage); ?>" alt=""><?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                                        <div class="product-meta"><?php echo htmlspecialchars($product['brand'] ?? 'No Brand'); ?> &bull; <?php echo htmlspecialchars(ucfirst($product['category'] ?? '')); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="price-tag">₱<?php echo number_format($product['price'], 2); ?></div>
                                                <?php if ($saleActive): ?>
                                                    <span class="sale-pill"><?php echo (int)$salePct; ?>% OFF</span>
                                                    <?php if (!empty($saleExp)): ?>
                                                        <div style="font-size:.68rem;color:var(--text-muted);">until <?php echo date('M d, Y h:i A', strtotime($saleExp)); ?></div>
                                                    <?php endif; ?>
                                                <?php elseif ($saleExpired): ?>
                                                    <div style="font-size:.68rem;color:var(--danger);">Sale expired</div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($product['shipping_time'])): ?>
                                                    <span class="shipping-pill"><i class="fas fa-truck me-1"></i><?php echo htmlspecialchars($product['shipping_time']); ?></span>
                                                <?php else: ?>
                                                    <span style="color:var(--text-muted);font-size:.8rem;">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="stock-badge <?php echo $stockClass; ?>"><?php echo $stockLabel; ?></span></td>
                                            <td style="text-align:right;padding-right:20px;white-space:nowrap;">
                                                <button type="button" class="act-btn act-btn-edit me-1"
                                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                                    data-id="<?php echo $product['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                    data-brand="<?php echo htmlspecialchars($product['brand'] ?? ''); ?>"
                                                    data-category="<?php echo htmlspecialchars($product['category'] ?? ''); ?>"
                                                    data-price="<?php echo $product['price']; ?>"
                                                    data-stock="<?php echo $product['stock'] ?? 0; ?>"
                                                    data-sale_percent="<?php echo htmlspecialchars($salePct); ?>"
                                                    data-sale_expiry="<?php echo htmlspecialchars($saleExp); ?>"
                                                    data-shipping_time="<?php echo htmlspecialchars($product['shipping_time'] ?? ''); ?>"
                                                    data-image="<?php echo htmlspecialchars($product['image'] ?? ''); ?>"
                                                    data-desc="<?php echo htmlspecialchars($product['desc'] ?? ''); ?>"
                                                    onclick="populateEditForm(this)">
                                                    <i class="fas fa-pen"></i> Edit
                                                </button>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="archive">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <button type="submit" class="act-btn act-btn-archive"
                                                        onclick="return confirm('Archive this product?')">
                                                        <i class="fas fa-archive"></i> Archive
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ACTIVE DEALS TAB -->
            <div class="tab-pane" id="tab-deals">
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-title">Active Deals &amp; Featured</span>
                        <span style="font-size:.78rem;color:var(--text-muted);">Highest % sale = Featured Deal in storefront</span>
                    </div>
                    <div class="panel-body">
                        <?php if (empty($onSale)): ?>
                            <div style="text-align:center;padding:32px;color:var(--text-muted);">
                                <i class="fas fa-tag" style="font-size:2rem;opacity:.3;display:block;margin-bottom:10px;"></i>
                                No active sales. Add a sale % to a product to see it here.
                            </div>
                        <?php else: ?>
                            <div class="deals-grid">
                                <?php foreach (array_values($onSale) as $i => $p):
                                    $salePrice = $p['price'] * (1 - (int)$p['sale_percent'] / 100);
                                    $productImage = Inventory::getProductImageSrc($p['image'] ?? '', '../');
                                ?>
                                    <div class="deal-card">
                                        <?php if ($i === 0): ?>
                                            <div class="deal-badge"><i class="fas fa-crown me-1"></i>Featured</div>
                                        <?php else: ?>
                                            <div class="deal-badge"><?php echo (int)$p['sale_percent']; ?>% OFF</div>
                                        <?php endif; ?>
                                        <div style="height:100px;background:#f0f3f9;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                                            <?php if (strpos($p['image'] ?? '', '<svg') !== false): echo $p['image'];
                                            else: ?><img src="<?php echo htmlspecialchars($productImage); ?>" style="height:100%;object-fit:cover;" alt=""><?php endif; ?>
                                        </div>
                                        <div class="deal-body">
                                            <div class="deal-name">
                                                <?php if ($i === 0): ?><i class="fas fa-crown featured-crown me-1"></i><?php endif; ?>
                                                <?php echo htmlspecialchars($p['name']); ?>
                                            </div>
                                            <div>
                                                <span class="deal-price">₱<?php echo number_format($salePrice, 2); ?></span>
                                                <span style="text-decoration:line-through;color:var(--text-muted);font-size:.8rem;margin-left:6px;">₱<?php echo number_format($p['price'], 2); ?></span>
                                            </div>
                                            <?php if (!empty($p['sale_expiry'])): ?>
                                                <div class="deal-expiry"><i class="fas fa-clock me-1"></i>Expires <?php echo date('M d, Y h:i A', strtotime($p['sale_expiry'])); ?></div>
                                                <div class="deal-expiry" style="margin-top:4px;">
                                                    <?php
                                                    $diff = strtotime($p['sale_expiry']) - time();
                                                    if ($diff > 0) {
                                                        $d = floor($diff / 86400); $h = floor(($diff % 86400) / 3600); $m = floor(($diff % 3600) / 60);
                                                        echo '<span style="color:var(--success);font-weight:700;">';
                                                        if ($d > 0) echo $d . 'd ';
                                                        echo $h . 'h ' . $m . 'm remaining</span>';
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($p['shipping_time'])): ?>
                                                <div style="margin-top:6px;"><span class="shipping-pill" style="font-size:.65rem;"><i class="fas fa-truck me-1"></i><?php echo htmlspecialchars($p['shipping_time']); ?></span></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ADD NEW TAB -->
            <div class="tab-pane" id="tab-add">
                <div class="panel">
                    <div class="panel-header"><span class="panel-title">Add New Product</span></div>
                    <div class="panel-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add">
                            <div class="field-group">
                                <label class="field-label">Product Name</label>
                                <input type="text" name="name" class="field-control" required placeholder="e.g. Razer DeathAdder V3">
                            </div>
                            <div class="field-row">
                                <div class="field-group">
                                    <label class="field-label">Brand</label>
                                    <select name="brand" class="field-control" required>
                                        <option value="" disabled selected>Select Brand</option>
                                        <option>Apple</option>
                                        <option>ASUS</option>
                                        <option>Corsair</option>
                                        <option>Dell</option>
                                        <option>HP</option>
                                        <option>Lenovo</option>
                                        <option>Intel</option>
                                        <option>Logitech</option>
                                        <option>NVIDIA</option>
                                        <option>Samsung</option>
                                        <option>Sony</option>
                                        <option>Razer</option>
                                    </select>
                                </div>
                                <div class="field-group">
                                    <label class="field-label">Category</label>
                                    <select name="category" class="field-control" required>
                                        <option value="" disabled selected>Select Category</option>
                                        <option value="laptop">Laptop</option>
                                        <option value="desktop">Desktop / PC</option>
                                        <option value="tablet">Tablet</option>
                                        <option value="phone">Phone</option>
                                        <option value="audio">Headphones / Audio</option>
                                        <option value="peripheral">Accessories / Peripherals</option>
                                        <option value="cpu">CPU</option>
                                        <option value="gpu">GPU</option>
                                    </select>
                                </div>
                            </div>
                            <div class="field-row">
                                <div class="field-group">
                                    <label class="field-label">Price (₱)</label>
                                    <input type="number" step="0.01" name="price" class="field-control" required placeholder="0.00">
                                </div>
                                <div class="field-group">
                                    <label class="field-label">Stock Qty</label>
                                    <input type="number" name="stock" class="field-control" required placeholder="10">
                                </div>
                            </div>
                            <div class="field-row">
                                <div class="field-group">
                                    <label class="field-label">Sale % <small style="font-weight:400;color:var(--text-muted);">(optional)</small></label>
                                    <input type="number" min="1" max="99" name="sale_percent" class="field-control" placeholder="e.g. 20">
                                </div>
                                <div class="field-group">
                                    <label class="field-label">Sale Expiry Date &amp; Time</label>
                                    <input type="datetime-local" name="sale_expiry" class="field-control" min="<?php echo date('Y-m-d\TH:i'); ?>">
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Estimated Shipping Time</label>
                                <input type="text" name="shipping_time" class="field-control" placeholder="e.g. 2-3 business days">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Image Source</label>
                                <div class="radio-group">
                                    <label class="radio-item"><input type="radio" name="image_source" id="ap_upload" value="upload" checked><span>Upload File</span></label>
                                    <label class="radio-item"><input type="radio" name="image_source" id="ap_url" value="url"><span>Image URL / SVG</span></label>
                                </div>
                            </div>
                            <div id="ap_upload_sec" class="field-group">
                                <label class="field-label">Upload Image</label>
                                <input type="file" name="image_upload" class="field-control" accept="image/*">
                            </div>
                            <div id="ap_url_sec" class="field-group" style="display:none;">
                                <label class="field-label">Image URL or SVG</label>
                                <input type="text" name="image" class="field-control" placeholder="<svg...> or https://...">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Description</label>
                                <textarea name="desc" class="field-control" rows="3" style="resize:vertical;" placeholder="Specs and details..."></textarea>
                            </div>
                            <button type="submit" class="btn-submit"><i class="fas fa-plus-circle me-2"></i>Publish to Store</button>
                        </form>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- EDIT MODAL -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header-custom">
                    <span class="modal-title"><i class="fas fa-pen me-2"></i>Edit Product</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body-custom">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="product_id" id="edit_product_id">
                        <div class="field-group">
                            <label class="field-label">Product Name</label>
                            <input type="text" name="name" id="edit_name" class="field-control" required>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Brand</label>
                                <select name="brand" id="edit_brand" class="field-control">
                                    <option>Apple</option>
                                    <option>ASUS</option>
                                    <option>Corsair</option>
                                    <option>Dell</option>
                                    <option>HP</option>
                                    <option>Lenovo</option>
                                    <option>Intel</option>
                                    <option>Logitech</option>
                                    <option>NVIDIA</option>
                                    <option>Samsung</option>
                                    <option>Sony</option>
                                    <option>Razer</option>
                                </select>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Category</label>
                                <select name="category" id="edit_category" class="field-control">
                                    <option value="laptop">Laptop</option>
                                    <option value="desktop">Desktop / PC</option>
                                    <option value="tablet">Tablet</option>
                                    <option value="phone">Phone</option>
                                    <option value="audio">Headphones / Audio</option>
                                    <option value="peripheral">Accessories / Peripherals</option>
                                    <option value="cpu">CPU</option>
                                    <option value="gpu">GPU</option>
                                </select>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Price (₱)</label>
                                <input type="number" step="0.01" name="price" id="edit_price" class="field-control" required>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Stock Qty</label>
                                <input type="number" name="stock" id="edit_stock" class="field-control" required>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Sale %</label>
                                <input type="number" min="1" max="99" name="sale_percent" id="edit_sale_percent" class="field-control" placeholder="e.g. 20">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Sale Expiry Date &amp; Time</label>
                                <input type="datetime-local" name="sale_expiry" id="edit_sale_expiry" class="field-control">
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Estimated Shipping Time</label>
                            <input type="text" name="shipping_time" id="edit_shipping_time" class="field-control" placeholder="e.g. 2-3 business days">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Image Source</label>
                            <div class="radio-group">
                                <label class="radio-item"><input type="radio" name="image_source" id="edit_upload_radio" value="upload" checked><span>Upload File</span></label>
                                <label class="radio-item"><input type="radio" name="image_source" id="edit_url_radio" value="url"><span>Image URL / SVG</span></label>
                            </div>
                        </div>
                        <div id="edit_upload_section" class="field-group">
                            <label class="field-label">Upload Image</label>
                            <input type="file" name="image_upload" class="field-control" accept="image/*">
                            <small style="font-size:.7rem;color:var(--text-muted);margin-top:4px;display:block;">Leave blank to keep current image.</small>
                        </div>
                        <div id="edit_url_section" class="field-group" style="display:none;">
                            <label class="field-label">Image URL or SVG</label>
                            <input type="text" name="image" id="edit_image" class="field-control">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Description</label>
                            <textarea name="desc" id="edit_desc" class="field-control" rows="3" style="resize:vertical;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-save"><i class="fas fa-check me-1"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ADD MODAL (topbar shortcut) -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header-custom">
                    <span class="modal-title"><i class="fas fa-plus me-2"></i>Add New Product</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body-custom" style="max-height:70vh;overflow-y:auto;">
                        <input type="hidden" name="action" value="add">
                        <div class="field-group">
                            <label class="field-label">Product Name</label>
                            <input type="text" name="name" class="field-control" required placeholder="e.g. Razer DeathAdder V3">
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Brand</label>
                                <select name="brand" class="field-control" required>
                                    <option value="" disabled selected>Select Brand</option>
                                    <option>Apple</option>
                                    <option>ASUS</option>
                                    <option>Corsair</option>
                                    <option>Dell</option>
                                    <option>HP</option>
                                    <option>Lenovo</option>
                                    <option>Intel</option>
                                    <option>Logitech</option>
                                    <option>NVIDIA</option>
                                    <option>Samsung</option>
                                    <option>Sony</option>
                                    <option>Razer</option>
                                </select>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Category</label>
                                <select name="category" class="field-control" required>
                                    <option value="" disabled selected>Select Category</option>
                                    <option value="laptop">Laptop</option>
                                    <option value="desktop">Desktop / PC</option>
                                    <option value="tablet">Tablet</option>
                                    <option value="phone">Phone</option>
                                    <option value="audio">Headphones / Audio</option>
                                    <option value="peripheral">Accessories / Peripherals</option>
                                    <option value="cpu">CPU</option>
                                    <option value="gpu">GPU</option>
                                </select>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Price (₱)</label>
                                <input type="number" step="0.01" name="price" class="field-control" required placeholder="0.00">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Stock Qty</label>
                                <input type="number" name="stock" class="field-control" required placeholder="10">
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Sale %</label>
                                <input type="number" min="1" max="99" name="sale_percent" class="field-control" placeholder="e.g. 30">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Sale Expiry</label>
                                <input type="datetime-local" name="sale_expiry" class="field-control" min="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Estimated Shipping Time</label>
                            <input type="text" name="shipping_time" class="field-control" placeholder="e.g. 2-3 business days">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Image Source</label>
                            <div class="radio-group">
                                <label class="radio-item"><input type="radio" name="image_source" id="am_upload" value="upload" checked><span>Upload</span></label>
                                <label class="radio-item"><input type="radio" name="image_source" id="am_url" value="url"><span>URL / SVG</span></label>
                            </div>
                        </div>
                        <div id="am_upload_sec" class="field-group">
                            <input type="file" name="image_upload" class="field-control" accept="image/*">
                        </div>
                        <div id="am_url_sec" class="field-group" style="display:none;">
                            <input type="text" name="image" class="field-control" placeholder="https://...">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Description</label>
                            <textarea name="desc" class="field-control" rows="3" style="resize:vertical;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-save"><i class="fas fa-plus me-1"></i>Publish</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function switchTab(id, btn) {
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            btn.classList.add('active');
        }

        function populateEditForm(btn) {
            const d = btn.dataset;
            document.getElementById('edit_product_id').value = d.id;
            document.getElementById('edit_name').value = d.name;
            document.getElementById('edit_brand').value = d.brand;
            document.getElementById('edit_category').value = d.category;
            document.getElementById('edit_price').value = d.price;
            document.getElementById('edit_stock').value = d.stock;
            document.getElementById('edit_sale_percent').value = d.sale_percent;
            // Convert "YYYY-MM-DD HH:MM:SS" or "YYYY-MM-DD" to "YYYY-MM-DDTHH:MM" for datetime-local
            let expiry = d.sale_expiry || '';
            if (expiry) {
                expiry = expiry.replace(' ', 'T').substring(0, 16);
            }
            document.getElementById('edit_sale_expiry').value = expiry;
            document.getElementById('edit_shipping_time').value = d.shipping_time;
            document.getElementById('edit_image').value = d.image;
            document.getElementById('edit_desc').value = d.desc;
        }

        document.addEventListener('DOMContentLoaded', function() {
            function bindToggle(upId, urlId, upSecId, urlSecId) {
                const up = document.getElementById(upId);
                const url = document.getElementById(urlId);
                if (!up || !url) return;
                const go = () => {
                    document.getElementById(upSecId).style.display = up.checked ? 'block' : 'none';
                    document.getElementById(urlSecId).style.display = url.checked ? 'block' : 'none';
                };
                up.addEventListener('change', go);
                url.addEventListener('change', go);
            }
            bindToggle('ap_upload', 'ap_url', 'ap_upload_sec', 'ap_url_sec');
            bindToggle('edit_upload_radio', 'edit_url_radio', 'edit_upload_section', 'edit_url_section');
            bindToggle('am_upload', 'am_url', 'am_upload_sec', 'am_url_sec');
        });
    </script>
</body>

</html>
