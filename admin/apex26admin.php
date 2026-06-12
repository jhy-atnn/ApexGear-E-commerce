<?php
require_once __DIR__ . '/../includes/storage.php';
require_once __DIR__ . '/../classes/Inventory.php';

/** @var Inventory $inventoryManager */
$inventoryManager = new Inventory();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add' || $action === 'edit') {
        $name          = trim($_POST['name']          ?? '');
        $brand         = trim($_POST['brand']         ?? '');
        $category      = trim($_POST['category']      ?? '');
        $price         = $_POST['price']              ?? 0;
        $stock         = $_POST['stock']              ?? 0;
        $sale_percent  = trim($_POST['sale_percent']  ?? '');
        $sale_expiry   = trim($_POST['sale_expiry']   ?? '');
        $shipping_time = trim($_POST['shipping_time'] ?? '');
        $desc          = trim($_POST['desc']          ?? '');
        $image         = '';
        $image_source  = $_POST['image_source']       ?? 'upload';

        if ($image_source === 'upload' && isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === 0) {
            $target_dir = __DIR__ . "/../assets/images/products/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $clean_file_name  = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES["image_upload"]["name"]));
            $unique_file_name = time() . "_" . $clean_file_name;
            $target_file      = $target_dir . $unique_file_name;
            if (move_uploaded_file($_FILES["image_upload"]["tmp_name"], $target_file)) {
                $image = "assets/images/products/" . $unique_file_name;
            }
        } elseif ($image_source === 'url' && !empty($_POST['image'])) {
            $image = $_POST['image'];
        }

        if ($action === 'add') {
            $inventoryManager->addProduct($name, $brand, $category, $price, '', $stock, '', '', '', $image, $desc, $shipping_time, $sale_percent, $sale_expiry);
            header("Location: apex26admin.php?success=added");
            exit;
        } else {
            $product_id = $_POST['product_id'] ?? 0;
            if (empty($image)) {
                $products_temp = $inventoryManager->getAllProducts();
                if (isset($products_temp[$product_id])) $image = $products_temp[$product_id]['image'];
            }
            $inventoryManager->editProduct($product_id, $name, $brand, $category, $price, '', $stock, '', '', '', $image, $desc, $shipping_time, $sale_percent, $sale_expiry);
            header("Location: apex26admin.php?success=edited");
            exit;
        }
    } elseif ($action === 'archive') {
        $product_id = $_POST['product_id'] ?? 0;
        $inventoryManager->archiveProduct($product_id);
        header("Location: apex26admin.php?success=archived");
        exit;
    }
}

$products      = $inventoryManager->getAllProducts();
$totalProducts = count($products);
$totalStock    = array_sum(array_column($products, 'stock'));
$lowStock      = count(array_filter($products, fn($p) => isset($p['stock']) && $p['stock'] < 5));

// Pending orders count for sidebar badge
$inv2 = new Inventory();
$allOrders = $inv2->getAllOrders();
$pendingOrders = count(array_filter($allOrders, fn($o) => strtolower($o['order_status'] ?? '') === 'on process'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin-style.css" rel="stylesheet">
    <style>
        /* ── PAGE-SPECIFIC ADDITIONS ── */
        .greeting-bar {
            background: linear-gradient(135deg, #080f1e 0%, #0b2fa8 100%);
            border-radius: 14px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .greeting-bar h1 {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-size: 2rem;
            margin: 0 0 4px;
        }
        .greeting-bar p { margin: 0; font-size: .88rem; opacity: .7; }
        .greeting-bar .badge-pill {
            background: rgba(0,194,255,.18);
            border: 1px solid rgba(0,194,255,.35);
            color: #00c2ff;
            font-size: .72rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            white-space: nowrap;
        }

        /* Notification panel */
        .notif-panel {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 24px;
            overflow: hidden;
        }
        .notif-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .notif-header h6 {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin: 0;
        }
        .notif-list { list-style: none; margin: 0; padding: 0; }
        .notif-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 20px;
            border-bottom: 1px solid var(--border);
            font-size: .84rem;
        }
        .notif-list li:last-child { border-bottom: none; }
        .notif-dot {
            width: 8px; height: 8px; border-radius: 50%;
            margin-top: 5px; flex-shrink: 0;
        }
        .notif-dot.warn  { background: #f5c518; }
        .notif-dot.info  { background: var(--accent); }
        .notif-dot.danger{ background: var(--danger); }
        .notif-dot.success { background: var(--success); }
        .notif-time { margin-left: auto; color: var(--text-muted); font-size: .75rem; white-space: nowrap; }
        .notif-empty { padding: 24px 20px; text-align: center; color: var(--text-muted); font-size: .85rem; }

        /* Sale badge in table */
        .sale-pill {
            display: inline-block;
            background: rgba(255,59,92,.1);
            color: var(--danger);
            font-size: .68rem;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 10px;
            margin-left: 5px;
        }
        .shipping-pill {
            display: inline-block;
            background: rgba(0,194,255,.1);
            color: #007aad;
            font-size: .68rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
        }

        /* Archive button */
        .act-btn-archive {
            border-color: rgba(245,197,24,.35);
            color: #b8860b;
            background: rgba(245,197,24,.07);
        }
        .act-btn-archive:hover {
            background: #f5c518;
            color: #000;
            border-color: #f5c518;
        }
    </style>
</head>
<body>

<!-- Sidebar overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar" id="sidebar">
    <a href="../index.php" class="sidebar-brand">
        <img src="../assets/images/ApeX Logo.png" alt="ApeX Gear">
        <div class="sidebar-brand-text">
            <span class="t1">ApeX </span><span class="t2">Gear</span>
        </div>
        <span class="sidebar-badge">Admin</span>
    </a>
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Main</div>
        <a href="apex26admin.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="manage_orders.php">
            <i class="fas fa-shopping-cart"></i> Orders
            <?php if ($pendingOrders > 0): ?>
                <span class="nav-badge"><?php echo $pendingOrders; ?></span>
            <?php endif; ?>
        </a>
        <a href="manage_products.php"><i class="fas fa-boxes"></i> Manage Products</a>
        <a href="manage_archives.php"><i class="fas fa-archive"></i> Archives</a>
        <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
        <a href="manage_deals.php"><i class="fas fa-percentage"></i> Deals &amp; Promos</a>
        <div class="sidebar-section-label">Store</div>
        <a href="../index.php" target="_blank"><i class="fas fa-store"></i> View Live Store</a>
        <a href="../index.php?page=products" target="_blank"><i class="fas fa-tags"></i> Product Catalog</a>
    </nav>
    <div class="sidebar-footer">
        <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Site</a>
    </div>
</aside>

<!-- ══ MAIN WRAP ══ -->
<div class="main-wrap">

    <!-- Top Bar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <span class="topbar-title">Dashboard</span>
            <div class="topbar-divider"></div>
            <span class="topbar-crumb">Inventory &amp; Products</span>
        </div>
        <div class="topbar-right">
            <a href="../index.php" target="_blank" class="btn-topbar"><i class="fas fa-external-link-alt"></i> Live Store</a>
        </div>
    </header>

    <!-- Page Body -->
    <main class="page-body">

        <!-- Greeting -->
        <div class="greeting-bar">
            <div>
                <h1>Good <?php
                    $h = (int)date('H');
                    echo $h < 12 ? 'morning' : ($h < 17 ? 'afternoon' : 'evening');
                ?>, Admin!</h1>
                <p>Here's what's happening with your store today.</p>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_GET['success'])): ?>
            <?php
            $alertMap = [
                'added'    => ['success', 'check-circle',  'Product published to store.'],
                'edited'   => ['info',    'edit',          'Product details updated.'],
                'archived' => ['warn',    'archive',       'Product moved to archive.'],
                'deleted'  => ['danger',  'trash-alt',     'Product permanently deleted.'],
            ];
            $key = $_GET['success'];
            if (isset($alertMap[$key])):
                [$cls, $ico, $msg] = $alertMap[$key];
            ?>
            <div class="apex-alert <?php echo $cls; ?>">
                <i class="fas fa-<?php echo $ico; ?>"></i>
                <span><?php echo htmlspecialchars($msg); ?></span>
                <button class="close-btn" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Notifications Panel -->
        <div class="notif-panel">
            <div class="notif-header">
                <h6><i class="fas fa-bell me-2" style="color:var(--accent);"></i>Notifications &amp; Updates</h6>
                <span style="font-size:.75rem; color:var(--text-muted);">Today, <?php echo date('M d, Y'); ?></span>
            </div>
            <?php
            $notifs = [];
            if ($lowStock > 0)
                $notifs[] = ['warn',   "fas fa-exclamation-triangle", "{$lowStock} product(s) are running low on stock (under 5 units).", "Just now"];
            if ($pendingOrders > 0)
                $notifs[] = ['info',   "fas fa-shopping-cart", "{$pendingOrders} order(s) are currently On Process and need attention.", "Just now"];

            // Expiring sales
            $expiringSoon = 0;
            foreach ($products as $p) {
                if (!empty($p['sale_expiry']) && !empty($p['sale_percent'])) {
                    $days = (strtotime($p['sale_expiry']) - time()) / 86400;
                    if ($days >= 0 && $days <= 3) $expiringSoon++;
                }
            }
            if ($expiringSoon > 0)
                $notifs[] = ['warn', "fas fa-tag", "{$expiringSoon} sale(s) expire within 3 days — review your deals.", date('M d')];

            if (empty($notifs)):
            ?>
            <div class="notif-empty"><i class="fas fa-check-circle me-2" style="color:var(--success);"></i>All clear — no urgent updates right now.</div>
            <?php else: ?>
            <ul class="notif-list">
                <?php foreach ($notifs as [$type, $icon, $text, $time]): ?>
                <li>
                    <div class="notif-dot <?php echo $type; ?>"></div>
                    <span><i class="<?php echo $icon; ?> me-1" style="opacity:.6;"></i><?php echo htmlspecialchars($text); ?></span>
                    <span class="notif-time"><?php echo $time; ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <!-- Stat Cards -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-box-open"></i></div>
                <div>
                    <div class="stat-num"><?php echo $totalProducts; ?></div>
                    <div class="stat-lbl">Total Products</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon cyan"><i class="fas fa-cubes"></i></div>
                <div>
                    <div class="stat-num"><?php echo number_format($totalStock); ?></div>
                    <div class="stat-lbl">Units in Stock</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <div class="stat-num"><?php echo $lowStock; ?></div>
                    <div class="stat-lbl">Low Stock Items</div>
                </div>
            </div>
        </div>
</div><!-- /main-wrap -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('show');
}

function populateEditForm(btn) {
    document.getElementById('edit_product_id').value   = btn.dataset.id;
    document.getElementById('edit_name').value         = btn.dataset.name;
    document.getElementById('edit_brand').value        = btn.dataset.brand;
    document.getElementById('edit_category').value     = btn.dataset.category;
    document.getElementById('edit_price').value        = btn.dataset.price;
    document.getElementById('edit_stock').value        = btn.dataset.stock;
    document.getElementById('edit_sale_percent').value = btn.dataset.sale_percent;
    document.getElementById('edit_sale_expiry').value  = btn.dataset.sale_expiry;
    document.getElementById('edit_shipping_time').value= btn.dataset.shipping_time;
    document.getElementById('edit_image').value        = btn.dataset.image;
    document.getElementById('edit_desc').value         = btn.dataset.desc;
}

document.addEventListener('DOMContentLoaded', function () {
    function bindToggle(upId, urlId, upSecId, urlSecId) {
        const up = document.getElementById(upId);
        const url = document.getElementById(urlId);
        if (!up || !url) return;
        const toggle = () => {
            document.getElementById(upSecId).style.display  = up.checked  ? 'block' : 'none';
            document.getElementById(urlSecId).style.display = url.checked ? 'block' : 'none';
        };
        up.addEventListener('change', toggle);
        url.addEventListener('change', toggle);
    }
    bindToggle('upload_radio',       'url_radio',       'upload_section',       'url_section');
    bindToggle('edit_upload_radio',  'edit_url_radio',  'edit_upload_section',  'edit_url_section');
    bindToggle('modal_upload_radio', 'modal_url_radio', 'modal_upload_section', 'modal_url_section');
});
</script>
</body>
</html>
