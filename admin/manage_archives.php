<?php
require_once __DIR__ . '/../includes/storage.php';
require_once __DIR__ . '/../classes/Inventory.php';

$inv = new Inventory();
$admin_id = isset($_SESSION['admin']['id']) ? intval($_SESSION['admin']['id']) : null;

// Handle restore / permanent delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action     = $_POST['action'];
    $product_id = intval($_POST['product_id'] ?? 0);

    if ($action === 'restore' && $product_id) {
        $inv->restoreProduct($product_id);
        $inv->logAdminActivity('product_restore', "Restored product ID {$product_id} from archives.", $admin_id);
        header('Location: manage_archives.php?success=restored');
        exit;
    }
    if ($action === 'delete_permanent' && $product_id) {
        $inv->deleteProduct($product_id);
        $inv->logAdminActivity('product_delete', "Permanently deleted product ID {$product_id}.", $admin_id);
        header('Location: manage_archives.php?success=deleted');
        exit;
    }
}

// Fetch data
$allProducts    = $inv->getAllProducts(true);   // pass true to include archived; fallback handled below
$archivedProducts = array_filter($allProducts ?? [], fn($p) => !empty($p['archived']));

$allOrders      = $inv->getAllOrders();
$completedOrders = array_filter($allOrders ?? [], fn($o) => strtolower($o['order_status'] ?? '') === 'completed');

$pendingOrders = count(array_filter($allOrders, fn($o) => strtolower($o['order_status'] ?? '') === 'on process'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archives | ApeX Gear Admin</title>
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

        /* Tabs */
        .arc-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
        }

        .arc-tab {
            padding: 9px 22px;
            border-radius: 9px;
            font-weight: 700;
            font-size: .84rem;
            cursor: pointer;
            border: 2px solid var(--border);
            background: var(--card-bg);
            color: var(--text-muted);
            transition: all .18s;
        }

        .arc-tab.active {
            background: var(--blue);
            color: #fff;
            border-color: var(--blue);
        }

        .arc-tab:hover:not(.active) {
            border-color: var(--blue);
            color: var(--blue);
        }

        /* Table panel */
        .panel {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 28px;
        }

        .panel-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .panel-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 1.15rem;
            text-transform: uppercase;
        }

        .panel-count {
            background: var(--panel-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 700;
            padding: 3px 12px;
            color: var(--text-muted);
        }

        .tbl {
            width: 100%;
            border-collapse: collapse;
        }

        .tbl thead th {
            background: var(--panel-bg);
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--text-muted);
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }

        .tbl tbody tr {
            border-bottom: 1px solid var(--border);
        }

        .tbl tbody tr:last-child {
            border-bottom: none;
        }

        .tbl tbody tr:hover {
            background: #f9fafc;
        }

        .tbl td {
            padding: 14px 16px;
            font-size: .86rem;
            vertical-align: middle;
        }

        .product-thumb {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: var(--panel-bg);
            object-fit: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-thumb svg {
            width: 32px;
            height: 32px;
        }

        .product-name {
            font-weight: 600;
            font-size: .88rem;
        }

        .product-meta {
            font-size: .75rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .act-btn {
            border: none;
            border-radius: 7px;
            padding: 6px 14px;
            font-size: .78rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: opacity .15s;
        }

        .btn-restore {
            background: rgba(0, 214, 143, .12);
            color: #009c60;
        }

        .btn-restore:hover {
            opacity: .8;
        }

        .btn-delete {
            background: rgba(255, 59, 92, .1);
            color: var(--danger);
        }

        .btn-delete:hover {
            opacity: .8;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 700;
        }

        .status-delivered {
            background: rgba(0, 214, 143, .12);
            color: #009c60;
        }

        .empty-state {
            text-align: center;
            padding: 56px 24px;
        }

        .empty-state i {
            font-size: 2.8rem;
            color: var(--border);
            display: block;
            margin-bottom: 16px;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: .9rem;
        }

        /* Alerts */
        .apex-alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: .87rem;
            font-weight: 500;
        }

        .apex-alert.success {
            background: rgba(0, 214, 143, .1);
            color: #009c60;
            border: 1px solid rgba(0, 214, 143, .25);
        }

        .apex-alert.danger {
            background: rgba(255, 59, 92, .1);
            color: var(--danger);
            border: 1px solid rgba(255, 59, 92, .25);
        }

        .apex-alert .close-btn {
            margin-left: auto;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            color: inherit;
            opacity: .6;
        }
    </style>
</head>

<body>
    <?php $currentAdminPage = 'manage_archives.php'; include __DIR__ . '/includes/admin_sidebar.php'; ?>
    <?php if (false): ?><div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
    <aside class="sidebar" id="sidebar">
        <a href="../index.php" class="sidebar-brand">
            <img src="../assets/images/ApeX Logo.png" alt="ApeX Gear">
            <div class="sidebar-brand-text"><span class="t1">ApeX </span><span class="t2">Gear</span></div>
            <span class="sidebar-badge">Admin</span>
        </a>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Main</div>
            <a href="apex26admin.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="manage_orders.php">
                <i class="fas fa-shopping-cart"></i> Orders
                <?php if ($pendingOrders > 0): ?><span class="nav-badge"><?php echo $pendingOrders; ?></span><?php endif; ?>
            </a>
            <a href="manage_products.php"><i class="fas fa-boxes"></i> Manage Products</a>
            <a href="manage_archives.php" class="active"><i class="fas fa-archive"></i> Archives</a>
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
    </aside><?php endif; ?>

    <div class="main-wrap">
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <span class="topbar-title">Archives</span>
                <div class="topbar-divider"></div>
                <span class="topbar-crumb">Archived Records</span>
            </div>
        </header>

        <main class="page-body">
            <?php if (isset($_GET['success'])): ?>
                <div class="apex-alert <?php echo $_GET['success'] === 'restored' ? 'success' : 'danger'; ?>">
                    <i class="fas fa-<?php echo $_GET['success'] === 'restored' ? 'check-circle' : 'trash-alt'; ?>"></i>
                    <span><?php echo $_GET['success'] === 'restored' ? 'Product restored successfully.' : 'Product permanently deleted.'; ?></span>
                    <button class="close-btn" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="arc-tabs">
                <button class="arc-tab active" onclick="switchTab('products', this)">
                    <i class="fas fa-boxes me-1"></i> Archived Products
                    <span class="ms-1">(<?php echo count($archivedProducts); ?>)</span>
                </button>
                <button class="arc-tab" onclick="switchTab('orders', this)">
                    <i class="fas fa-check-double me-1"></i> Completed Orders
                    <span class="ms-1">(<?php echo count($completedOrders); ?>)</span>
                </button>
            </div>

            <!-- ── ARCHIVED PRODUCTS ── -->
            <div id="tab-products">
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-title"><i class="fas fa-archive me-2" style="color:var(--accent);"></i>Archived Products</span>
                        <span class="panel-count"><?php echo count($archivedProducts); ?> record(s)</span>
                    </div>
                    <?php if (empty($archivedProducts)): ?>
                        <div class="empty-state">
                            <i class="fas fa-archive"></i>
                            <p>No archived products yet.<br>Products you archive from Manage Products will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x:auto;">
                            <table class="tbl">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Archived On</th>
                                        <th style="text-align:right;padding-right:20px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($archivedProducts as $p): ?>
                                        <tr>
                                            <td>
                                                <div style="display:flex;align-items:center;gap:12px;">
                                                    <div class="product-thumb">
                                                        <?php if (!empty($p['image']) && strpos($p['image'], '<svg') !== false): ?>
                                                            <?php echo $p['image']; ?>
                                                        <?php elseif (!empty($p['image'])): ?>
                                                            <img src="../<?php echo htmlspecialchars($p['image']); ?>" alt="">
                                                        <?php else: ?>
                                                            <i class="fas fa-box" style="color:var(--text-muted);"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div class="product-name"><?php echo htmlspecialchars($p['name'] ?? '—'); ?></div>
                                                        <div class="product-meta"><?php echo htmlspecialchars($p['brand'] ?? ''); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="color:var(--text-muted);font-size:.83rem;"><?php echo ucfirst(htmlspecialchars($p['category'] ?? '—')); ?></td>
                                            <td style="font-weight:600;">₱<?php echo number_format($p['price'] ?? 0, 2); ?></td>
                                            <td style="color:var(--text-muted);"><?php echo intval($p['stock'] ?? 0); ?> units</td>
                                            <td style="color:var(--text-muted);font-size:.8rem;"><?php echo !empty($p['archived_at']) ? date('M d, Y', strtotime($p['archived_at'])) : '—'; ?></td>
                                            <td style="text-align:right;padding-right:20px;white-space:nowrap;">
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="restore">
                                                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                                    <button type="submit" class="act-btn btn-restore"><i class="fas fa-undo"></i> Restore</button>
                                                </form>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this product? This cannot be undone.');">
                                                    <input type="hidden" name="action" value="delete_permanent">
                                                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                                    <button type="submit" class="act-btn btn-delete ms-1"><i class="fas fa-trash-alt"></i> Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ── COMPLETED ORDERS ── -->
            <div id="tab-orders" style="display:none;">
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-title"><i class="fas fa-check-double me-2" style="color:var(--success);"></i>Completed Orders</span>
                        <span class="panel-count"><?php echo count($completedOrders); ?> record(s)</span>
                    </div>
                    <?php if (empty($completedOrders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <p>No completed orders yet.<br>Orders marked as Completed will appear here automatically.</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x:auto;">
                            <table class="tbl">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Order Details</th>
                                        <th>Promo</th>
                                        <th>Total</th>
                                        <th>Completed On</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($completedOrders as $o): ?>
                                        <tr>
                                            <td style="font-weight:700;font-family:'Barlow Condensed',sans-serif;"><?php echo htmlspecialchars($o['reference_number'] ?? ('#' . intval($o['order_id'] ?? 0))); ?></td>
                                            <td>
                                                <div class="product-name"><?php echo htmlspecialchars($o['customer_name'] ?? $o['username'] ?? $o['name'] ?? '—'); ?></div>
                                                <div class="product-meta"><?php echo htmlspecialchars($o['customer_email'] ?? $o['email'] ?? ''); ?></div>
                                            </td>
                                            <td style="font-size:.82rem;min-width:260px;">
                                                <?php
                                                $items = $o['items'] ?? [];
                                                if (is_string($items)) $items = json_decode($items, true) ?? [];
                                                if (empty($items)) {
                                                    echo '<span style="color:var(--text-muted);">No item details stored.</span>';
                                                } else {
                                                    foreach ($items as $i) {
                                                        $itemName = htmlspecialchars($i['name'] ?? 'Item');
                                                        $brand = htmlspecialchars($i['brand'] ?? '');
                                                        $qty = intval($i['qty'] ?? $i['quantity'] ?? 1);
                                                        $price = floatval($i['price'] ?? $i['price_at_checkout'] ?? 0);
                                                        $lineTotal = floatval($i['line_total'] ?? ($qty * $price));
                                                        echo '<div style="margin-bottom:8px;">';
                                                        echo '<div style="font-weight:700;">' . $itemName . '</div>';
                                                        echo '<div style="color:var(--text-muted);font-size:.76rem;">' . ($brand ? $brand . ' · ' : '') . 'Qty: ' . $qty . ' × ₱' . number_format($price, 2) . ' = ₱' . number_format($lineTotal, 2) . '</div>';
                                                        echo '</div>';
                                                    }
                                                }
                                                ?>
                                            </td>
                                            <td style="font-size:.8rem;color:var(--text-muted);min-width:180px;">
                                                <div>Subtotal: ₱<?php echo number_format($o['subtotal'] ?? 0, 2); ?></div>
                                                <?php if (!empty($o['coupon_code'])): ?>
                                                    <div style="color:var(--success);">Discount: &minus;₱<?php echo number_format($o['discount_amount'] ?? 0, 2); ?></div>
                                                <?php endif; ?>
                                                <div>Tax: ₱<?php echo number_format($o['tax'] ?? 0, 2); ?></div>
                                                <div>Shipping: ₱<?php echo number_format($o['shipping_fee'] ?? 0, 2); ?></div>
                                                <div>Status: <span class="status-pill status-delivered"><?php echo htmlspecialchars($o['order_status'] ?? 'Completed'); ?></span></div>
                                            </td>
                                            <td>
                                                <?php if (!empty($o['coupon_code'])): ?>
                                                    <div style="font-weight:700;font-family:'Barlow Condensed',sans-serif;font-size:.85rem;"><?php echo htmlspecialchars($o['coupon_code']); ?></div>
                                                    <div style="font-size:.76rem;color:var(--success);font-weight:600;">&minus;₱<?php echo number_format($o['discount_amount'] ?? 0, 2); ?></div>
                                                <?php else: ?>
                                                    <span style="color:var(--text-muted);font-size:.8rem;">&mdash;</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-weight:600;">₱<?php echo number_format($o['total_amount'] ?? $o['total'] ?? 0, 2); ?></td>
                                            <td style="color:var(--text-muted);font-size:.8rem;">
                                                <?php echo !empty($o['created_at']) ? date('M d, Y', strtotime($o['created_at'])) : (isset($o['date']) ? date('M d, Y', strtotime($o['date'])) : '—'); ?>
                                            </td>
                                            <td style="color:var(--text-muted);font-size:.82rem;"><?php echo htmlspecialchars($o['remarks'] ?? '—'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
        <footer style="padding:18px 32px;border-top:1px solid var(--border);background:var(--card-bg);text-align:center;">
            <p style="font-size:.78rem;color:var(--text-muted);margin:0;">© 2026 ApeX Gear Admin Panel — All rights reserved.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function switchTab(tab, btn) {
            document.getElementById('tab-products').style.display = tab === 'products' ? '' : 'none';
            document.getElementById('tab-orders').style.display = tab === 'orders' ? '' : 'none';
            document.querySelectorAll('.arc-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('show');
        }
    </script>
</body>

</html>
