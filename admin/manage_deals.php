<?php
require_once __DIR__ . '/../includes/storage.php';
require_once __DIR__ . '/../classes/Inventory.php';

$inv = new Inventory();
$allOrders    = $inv->getAllOrders();
$pendingOrders = count(array_filter($allOrders, fn($o) => strtolower($o['order_status'] ?? '') === 'on process'));
$allProducts  = $inv->getAllProducts();

// ── Data storage helpers (flat JSON files) ──────────────────────────────────
$dataDir        = __DIR__ . '/../data/';
$codesFile      = $dataDir . 'promo_codes.json';
$bundlesFile    = $dataDir . 'bundle_deals.json';

function loadJson(string $path): array {
    if (!file_exists($path)) return [];
    return json_decode(file_get_contents($path), true) ?? [];
}
function saveJson(string $path, array $data): void {
    if (!is_dir(dirname($path))) mkdir(dirname($path), 0777, true);
    file_put_contents($path, json_encode(array_values($data), JSON_PRETTY_PRINT));
}

$promoCodes  = loadJson($codesFile);
$bundleDeals = loadJson($bundlesFile);

// ── POST handlers ────────────────────────────────────────────────────────────
$msg = ''; $msgType = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Promo Code actions ──
    if ($action === 'add_code') {
        $code    = strtoupper(trim($_POST['code']    ?? ''));
        $percent = intval($_POST['percent'] ?? 0);
        $expiry  = trim($_POST['expiry'] ?? '');
        if ($code && $percent > 0 && $percent <= 100) {
            $promoCodes[] = ['id' => uniqid(), 'code' => $code, 'percent' => $percent, 'expiry' => $expiry, 'created_at' => date('Y-m-d H:i:s')];
            saveJson($codesFile, $promoCodes);
            $msg = "Promo code <strong>{$code}</strong> added!";
        } else { $msg = 'Invalid code or percent.'; $msgType = 'danger'; }
    }
    if ($action === 'delete_code') {
        $id = $_POST['code_id'] ?? '';
        $promoCodes = array_filter($promoCodes, fn($c) => $c['id'] !== $id);
        saveJson($codesFile, $promoCodes);
        $msg = 'Promo code removed.'; $msgType = 'warn';
    }

    // ── Bundle Deal actions ──
    if ($action === 'add_bundle') {
        $name        = trim($_POST['bundle_name'] ?? '');
        $expiry      = trim($_POST['bundle_expiry'] ?? '');
        $productIds  = $_POST['bundle_product_ids'] ?? [];
        $percents    = $_POST['bundle_percents']    ?? [];
        $samePercent = !empty($_POST['same_percent']);
        $flatPct     = intval($_POST['flat_percent'] ?? 0);

        if ($name && !empty($productIds) && $expiry) {
            $items = [];
            foreach ($productIds as $k => $pid) {
                $pid = intval($pid);
                $pct = $samePercent ? $flatPct : intval($percents[$k] ?? 0);
                if ($pid && $pct > 0) {
                    $items[] = ['product_id' => $pid, 'percent' => $pct];
                }
            }
            if (!empty($items)) {
                $bundleDeals[] = [
                    'id'         => uniqid(),
                    'name'       => $name,
                    'expiry'     => $expiry,
                    'items'      => $items,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                saveJson($bundlesFile, $bundleDeals);
                $msg = "Bundle deal <strong>{$name}</strong> created!";
            } else { $msg = 'No valid products/percents.'; $msgType = 'danger'; }
        } else { $msg = 'Fill in all required fields.'; $msgType = 'danger'; }
    }
    if ($action === 'delete_bundle') {
        $id = $_POST['bundle_id'] ?? '';
        $bundleDeals = array_filter($bundleDeals, fn($b) => $b['id'] !== $id);
        saveJson($bundlesFile, $bundleDeals);
        $msg = 'Bundle deal removed.'; $msgType = 'warn';
    }

    if ($msg) { header("Location: manage_deals.php?msg=" . urlencode($msg) . "&type={$msgType}"); exit; }
}

if (isset($_GET['msg'])) { $msg = $_GET['msg']; $msgType = $_GET['type'] ?? 'success'; }

// Product index for quick lookup
$productIndex = [];
foreach ($allProducts as $p) { $productIndex[$p['id']] = $p; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deals &amp; Promos | ApeX Gear Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin-style.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-w:260px; --sidebar-bg:#080f1e;
            --sidebar-hover:rgba(0,194,255,.08); --accent:#00c2ff;
            --panel-bg:#f4f6fb; --card-bg:#ffffff;
            --border:#e3e8f0; --text-main:#0d1b2e;
            --text-muted:#6b7a99; --blue:#0b2fa8;
            --danger:#ff3b5c; --success:#00d68f;
            --gold:#f5c518;
        }
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Barlow',sans-serif; background:var(--panel-bg); color:var(--text-main); display:flex; min-height:100vh; }

        /* Section tabs */
        .deal-tabs { display:flex; gap:8px; margin-bottom:26px; }
        .deal-tab { padding:9px 24px; border-radius:9px; font-weight:700; font-size:.84rem; cursor:pointer; border:2px solid var(--border); background:var(--card-bg); color:var(--text-muted); transition:all .18s; }
        .deal-tab.active { background:var(--blue); color:#fff; border-color:var(--blue); }
        .deal-tab:hover:not(.active) { border-color:var(--blue); color:var(--blue); }

        /* Panel */
        .panel { background:var(--card-bg); border:1px solid var(--border); border-radius:14px; overflow:hidden; margin-bottom:28px; }
        .panel-header { padding:18px 24px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .panel-title { font-family:'Barlow Condensed',sans-serif; font-weight:800; font-size:1.15rem; text-transform:uppercase; }
        .panel-body { padding:24px; }

        /* Form fields */
        .field-group { margin-bottom:16px; }
        .field-label { display:block; font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--text-muted); margin-bottom:6px; }
        .field-control { width:100%; padding:9px 13px; border:1.5px solid var(--border); border-radius:9px; font-size:.88rem; background:var(--card-bg); outline:none; transition:border-color .18s; font-family:'Barlow',sans-serif; }
        .field-control:focus { border-color:var(--accent); }
        .field-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .field-row-3 { display:grid; grid-template-columns:2fr 1fr 1fr; gap:12px; align-items:end; }

        /* Buttons */
        .btn-add { display:inline-flex; align-items:center; gap:7px; padding:10px 22px; background:var(--blue); color:#fff; border:none; border-radius:9px; font-weight:700; font-size:.87rem; cursor:pointer; transition:opacity .18s; }
        .btn-add:hover { opacity:.88; }
        .btn-danger { background:rgba(255,59,92,.1); color:var(--danger); border:none; border-radius:7px; padding:5px 12px; font-size:.77rem; font-weight:700; cursor:pointer; }
        .btn-danger:hover { opacity:.8; }

        /* Table */
        .tbl { width:100%; border-collapse:collapse; }
        .tbl thead th { background:var(--panel-bg); font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--text-muted); padding:11px 16px; border-bottom:1px solid var(--border); }
        .tbl tbody tr { border-bottom:1px solid var(--border); }
        .tbl tbody tr:last-child { border-bottom:none; }
        .tbl tbody tr:hover { background:#f9fafc; }
        .tbl td { padding:13px 16px; font-size:.86rem; vertical-align:middle; }

        .code-badge { display:inline-block; font-family:monospace; font-size:.95rem; font-weight:800; background:rgba(245,197,24,.12); color:#b08800; border:1px solid rgba(245,197,24,.3); padding:4px 12px; border-radius:6px; letter-spacing:.05em; }
        .pct-pill { display:inline-block; background:rgba(255,59,92,.1); color:var(--danger); font-weight:800; font-size:.85rem; padding:3px 10px; border-radius:20px; }
        .pct-pill.green { background:rgba(0,214,143,.1); color:#009c60; }
        .expired-badge { color:var(--danger); font-size:.75rem; font-weight:700; }

        .empty-state { text-align:center; padding:44px 24px; }
        .empty-state i { font-size:2.4rem; color:var(--border); display:block; margin-bottom:14px; }
        .empty-state p { color:var(--text-muted); font-size:.88rem; }

        /* Alerts */
        .apex-alert { display:flex; align-items:center; gap:12px; padding:14px 18px; border-radius:10px; margin-bottom:18px; font-size:.87rem; font-weight:500; }
        .apex-alert.success { background:rgba(0,214,143,.1); color:#009c60; border:1px solid rgba(0,214,143,.25); }
        .apex-alert.warn    { background:rgba(245,197,24,.1); color:#b08800; border:1px solid rgba(245,197,24,.3); }
        .apex-alert.danger  { background:rgba(255,59,92,.1); color:var(--danger); border:1px solid rgba(255,59,92,.2); }
        .apex-alert .close-btn { margin-left:auto; background:none; border:none; cursor:pointer; color:inherit; opacity:.6; }

        /* Bundle builder */
        .bundle-product-row { display:grid; grid-template-columns:2fr 100px 36px; gap:10px; align-items:center; margin-bottom:10px; background:var(--panel-bg); border-radius:9px; padding:10px 14px; }
        .bundle-product-name { font-size:.85rem; font-weight:600; }
        .bundle-product-meta { font-size:.72rem; color:var(--text-muted); }
        .remove-row-btn { background:none; border:none; color:var(--danger); cursor:pointer; font-size:1rem; padding:0; }

        /* Countdown timer display */
        .timer-display { display:inline-flex; gap:6px; align-items:center; font-family:'Barlow Condensed',sans-serif; }
        .timer-block { background:var(--blue); color:#fff; border-radius:6px; padding:3px 8px; font-weight:800; font-size:.92rem; min-width:32px; text-align:center; }
        .timer-sep { font-weight:900; font-size:.9rem; color:var(--text-muted); }
        .timer-expired { color:var(--danger); font-weight:700; font-size:.8rem; }

        /* Bundle card */
        .bundle-card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; padding:18px 20px; margin-bottom:14px; }
        .bundle-card-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
        .bundle-card-name { font-family:'Barlow Condensed',sans-serif; font-weight:900; font-size:1.15rem; text-transform:uppercase; }
        .bundle-item-list { display:flex; flex-wrap:wrap; gap:8px; }
        .bundle-item { background:var(--panel-bg); border:1px solid var(--border); border-radius:8px; padding:6px 12px; font-size:.8rem; display:flex; align-items:center; gap:6px; }
        .bundle-item .item-name { font-weight:600; }
        .bundle-item .item-pct { color:var(--danger); font-weight:800; font-size:.78rem; }

        /* Same percent toggle */
        .toggle-row { display:flex; align-items:center; gap:10px; margin-bottom:16px; }
        .toggle-label { font-size:.82rem; font-weight:600; color:var(--text-main); cursor:pointer; }
        .toggle-input { width:16px; height:16px; cursor:pointer; }
    </style>
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
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
        <a href="manage_archives.php"><i class="fas fa-archive"></i> Archives</a>
        <div class="sidebar-section-label">Users &amp; Deals</div>
        <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
        <a href="manage_deals.php" class="active"><i class="fas fa-percentage"></i> Deals &amp; Promos</a>
        <div class="sidebar-section-label">Store</div>
        <a href="../index.php" target="_blank"><i class="fas fa-store"></i> View Live Store</a>
        <a href="../index.php?page=products" target="_blank"><i class="fas fa-tags"></i> Product Catalog</a>
    </nav>
    <div class="sidebar-footer">
        <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Site</a>
    </div>
</aside>

<div class="main-wrap">
    <header class="topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <span class="topbar-title">Deals &amp; Promos</span>
            <div class="topbar-divider"></div>
            <span class="topbar-crumb">Promo Codes &amp; Bundle Deals</span>
        </div>
    </header>

    <main class="page-body">
        <?php if ($msg): ?>
        <div class="apex-alert <?php echo htmlspecialchars($msgType); ?>">
            <i class="fas fa-<?php echo $msgType === 'success' ? 'check-circle' : ($msgType === 'warn' ? 'exclamation-triangle' : 'times-circle'); ?>"></i>
            <span><?php echo $msg; ?></span>
            <button class="close-btn" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="deal-tabs">
            <button class="deal-tab active" onclick="switchDealTab('codes', this)">
                <i class="fas fa-ticket-alt me-1"></i> Promo Codes
                <span class="ms-1">(<?php echo count($promoCodes); ?>)</span>
            </button>
            <button class="deal-tab" onclick="switchDealTab('bundles', this)">
                <i class="fas fa-layer-group me-1"></i> Bundle Deals
                <span class="ms-1">(<?php echo count($bundleDeals); ?>)</span>
            </button>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             PROMO CODES
        ═══════════════════════════════════════════════════════════ -->
        <div id="tab-codes">
            <!-- Add Code Form -->
            <div class="panel" style="margin-bottom:22px;">
                <div class="panel-header">
                    <span class="panel-title"><i class="fas fa-plus-circle me-2" style="color:var(--accent);"></i>Add Promo Code</span>
                </div>
                <div class="panel-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_code">
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Code Name <span style="color:var(--danger);">*</span></label>
                                <input type="text" name="code" class="field-control" placeholder="e.g. APEX20" style="text-transform:uppercase;" required>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Discount % <span style="color:var(--danger);">*</span></label>
                                <input type="number" name="percent" class="field-control" min="1" max="100" placeholder="e.g. 20" required>
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Valid Until <span style="color:var(--danger);">*</span></label>
                            <input type="date" name="expiry" class="field-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <button type="submit" class="btn-add"><i class="fas fa-plus"></i> Add Promo Code</button>
                    </form>
                </div>
            </div>

            <!-- Codes Table -->
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title"><i class="fas fa-ticket-alt me-2" style="color:var(--gold);"></i>Active Promo Codes</span>
                    <span style="background:var(--panel-bg);border:1px solid var(--border);border-radius:20px;font-size:.75rem;font-weight:700;padding:3px 12px;color:var(--text-muted);"><?php echo count($promoCodes); ?> code(s)</span>
                </div>
                <?php if (empty($promoCodes)): ?>
                <div class="empty-state"><i class="fas fa-ticket-alt"></i><p>No promo codes yet. Add one above.</p></div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Valid Until</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th style="text-align:right;padding-right:20px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($promoCodes as $c): 
                        $expired = !empty($c['expiry']) && strtotime($c['expiry']) < strtotime(date('Y-m-d'));
                        $daysLeft = !empty($c['expiry']) ? (int)((strtotime($c['expiry']) - time()) / 86400) : null;
                    ?>
                        <tr>
                            <td><span class="code-badge"><?php echo htmlspecialchars($c['code']); ?></span></td>
                            <td><span class="pct-pill"><?php echo $c['percent']; ?>% OFF</span></td>
                            <td style="font-size:.83rem;"><?php echo !empty($c['expiry']) ? date('M d, Y', strtotime($c['expiry'])) : '—'; ?></td>
                            <td>
                                <?php if ($expired): ?>
                                    <span class="expired-badge"><i class="fas fa-times-circle me-1"></i>Expired</span>
                                <?php elseif ($daysLeft !== null && $daysLeft <= 3): ?>
                                    <span style="color:#b08800;font-size:.78rem;font-weight:700;"><i class="fas fa-exclamation-triangle me-1"></i>Expires in <?php echo $daysLeft; ?>d</span>
                                <?php else: ?>
                                    <span style="color:var(--success);font-size:.78rem;font-weight:700;"><i class="fas fa-check-circle me-1"></i>Active</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--text-muted);font-size:.8rem;"><?php echo !empty($c['created_at']) ? date('M d, Y', strtotime($c['created_at'])) : '—'; ?></td>
                            <td style="text-align:right;padding-right:20px;">
                                <form method="POST" onsubmit="return confirm('Delete this promo code?');" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_code">
                                    <input type="hidden" name="code_id" value="<?php echo htmlspecialchars($c['id']); ?>">
                                    <button type="submit" class="btn-danger"><i class="fas fa-trash-alt me-1"></i>Delete</button>
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

        <!-- ═══════════════════════════════════════════════════════════
             BUNDLE DEALS
        ═══════════════════════════════════════════════════════════ -->
        <div id="tab-bundles" style="display:none;">
            <!-- Bundle Builder -->
            <div class="panel" style="margin-bottom:22px;">
                <div class="panel-header">
                    <span class="panel-title"><i class="fas fa-plus-circle me-2" style="color:var(--accent);"></i>Create Bundle Deal</span>
                </div>
                <div class="panel-body">
                    <form method="POST" id="bundleForm">
                        <input type="hidden" name="action" value="add_bundle">

                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Bundle Deal Name <span style="color:var(--danger);">*</span></label>
                                <input type="text" name="bundle_name" class="field-control" placeholder="e.g. Summer Gaming Bundle" required>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Sale Ends On <span style="color:var(--danger);">*</span></label>
                                <input type="datetime-local" name="bundle_expiry" class="field-control" required>
                            </div>
                        </div>

                        <!-- Same percent toggle -->
                        <div class="toggle-row">
                            <input type="checkbox" id="samePercentToggle" class="toggle-input" name="same_percent" onchange="toggleSamePercent(this.checked)">
                            <label for="samePercentToggle" class="toggle-label">Apply same discount % to all selected products</label>
                        </div>
                        <div id="flatPercentField" class="field-group" style="display:none;max-width:240px;">
                            <label class="field-label">Flat Discount %</label>
                            <input type="number" name="flat_percent" id="flatPercentInput" min="1" max="99" class="field-control" placeholder="e.g. 15">
                        </div>

                        <!-- Product picker -->
                        <div class="field-group">
                            <label class="field-label">Add Products to Bundle</label>
                            <div style="display:flex;gap:10px;margin-bottom:10px;">
                                <select id="productPicker" class="field-control" style="max-width:420px;">
                                    <option value="">— Select a product —</option>
                                    <?php foreach ($allProducts as $p): 
                                        if (!empty($p['archived'])) continue;
                                        // Skip products already on their own sale
                                        $hasSale = !empty($p['sale_percent']) && !empty($p['sale_expiry']) && strtotime($p['sale_expiry']) >= time();
                                    ?>
                                    <option value="<?php echo $p['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($p['name']); ?>"
                                        data-price="<?php echo number_format($p['price'], 2); ?>"
                                        <?php if ($hasSale) echo 'disabled title="Already has an active sale"'; ?>>
                                        <?php echo htmlspecialchars($p['name']); ?> — ₱<?php echo number_format($p['price'], 2); ?>
                                        <?php if ($hasSale) echo ' [On Sale]'; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn-add" onclick="addProductRow()"><i class="fas fa-plus"></i> Add</button>
                            </div>
                            <div id="bundleProductRows"></div>
                        </div>

                        <button type="submit" class="btn-add" onclick="return validateBundle()"><i class="fas fa-layer-group me-1"></i> Create Bundle Deal</button>
                    </form>
                </div>
            </div>

            <!-- Existing Bundles -->
            <div id="existingBundles">
            <?php if (empty($bundleDeals)): ?>
                <div class="panel">
                    <div class="empty-state"><i class="fas fa-layer-group"></i><p>No bundle deals yet. Create one above.</p></div>
                </div>
            <?php else: ?>
                <?php foreach ($bundleDeals as $bundle): 
                    $expired = !empty($bundle['expiry']) && strtotime($bundle['expiry']) < time();
                    $expiryTs = !empty($bundle['expiry']) ? strtotime($bundle['expiry']) : null;
                ?>
                <div class="bundle-card">
                    <div class="bundle-card-header">
                        <div>
                            <div class="bundle-card-name"><?php echo htmlspecialchars($bundle['name']); ?></div>
                            <div style="margin-top:6px;">
                                <?php if ($expired): ?>
                                    <span class="expired-badge"><i class="fas fa-times-circle me-1"></i>Deal Expired</span>
                                <?php elseif ($expiryTs): ?>
                                    <div class="timer-display" data-expiry="<?php echo $expiryTs; ?>">
                                        <i class="fas fa-clock" style="color:var(--accent);font-size:.85rem;"></i>
                                        <span class="timer-block" data-part="d">--</span><span class="timer-sep">d</span>
                                        <span class="timer-block" data-part="h">--</span><span class="timer-sep">h</span>
                                        <span class="timer-block" data-part="m">--</span><span class="timer-sep">m</span>
                                        <span class="timer-block" data-part="s">--</span><span class="timer-sep">s</span>
                                    </div>
                                    <div style="font-size:.72rem;color:var(--text-muted);margin-top:4px;">
                                        Ends <?php echo date('M d, Y g:i A', $expiryTs); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <form method="POST" onsubmit="return confirm('Delete this bundle deal?');">
                            <input type="hidden" name="action" value="delete_bundle">
                            <input type="hidden" name="bundle_id" value="<?php echo htmlspecialchars($bundle['id']); ?>">
                            <button type="submit" class="btn-danger"><i class="fas fa-trash-alt me-1"></i>Delete</button>
                        </form>
                    </div>
                    <div class="bundle-item-list">
                        <?php foreach ($bundle['items'] as $item): 
                            $p = $productIndex[$item['product_id']] ?? null;
                            $name = $p ? htmlspecialchars($p['name']) : '#'.$item['product_id'];
                        ?>
                        <div class="bundle-item">
                            <i class="fas fa-box" style="color:var(--text-muted);font-size:.75rem;"></i>
                            <span class="item-name"><?php echo $name; ?></span>
                            <span class="item-pct">-<?php echo $item['percent']; ?>%</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
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
// ── Tab switcher ────────────────────────────────────────────────────────────
function switchDealTab(tab, btn) {
    document.getElementById('tab-codes').style.display   = tab === 'codes'   ? '' : 'none';
    document.getElementById('tab-bundles').style.display = tab === 'bundles' ? '' : 'none';
    document.querySelectorAll('.deal-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

// ── Bundle builder ──────────────────────────────────────────────────────────
let addedProductIds = new Set();

function addProductRow() {
    const picker = document.getElementById('productPicker');
    const pid = picker.value;
    if (!pid) { alert('Please select a product first.'); return; }
    if (addedProductIds.has(pid)) { alert('Product already added.'); return; }
    addedProductIds.add(pid);

    const opt = picker.options[picker.selectedIndex];
    const name = opt.dataset.name;
    const price = opt.dataset.price;
    const sameOn = document.getElementById('samePercentToggle').checked;

    const row = document.createElement('div');
    row.className = 'bundle-product-row';
    row.dataset.pid = pid;
    row.innerHTML = `
        <div>
            <div class="bundle-product-name">${name}</div>
            <div class="bundle-product-meta">₱${price}</div>
            <input type="hidden" name="bundle_product_ids[]" value="${pid}">
        </div>
        <div>
            <input type="number" name="bundle_percents[]" class="field-control pct-input" min="1" max="99"
                placeholder="%" style="text-align:center;"
                ${sameOn ? 'style="display:none;" disabled' : 'required'}>
        </div>
        <button type="button" class="remove-row-btn" onclick="removeProductRow(this, '${pid}')">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.getElementById('bundleProductRows').appendChild(row);
    picker.value = '';
}

function removeProductRow(btn, pid) {
    btn.closest('.bundle-product-row').remove();
    addedProductIds.delete(pid);
}

function toggleSamePercent(on) {
    document.getElementById('flatPercentField').style.display = on ? '' : 'none';
    document.querySelectorAll('.pct-input').forEach(inp => {
        inp.disabled = on;
        inp.style.display = on ? 'none' : '';
        if (on) inp.removeAttribute('required'); else inp.setAttribute('required', '');
    });
}

function validateBundle() {
    const rows = document.querySelectorAll('.bundle-product-row');
    if (rows.length === 0) { alert('Add at least one product to the bundle.'); return false; }
    const sameOn = document.getElementById('samePercentToggle').checked;
    if (sameOn) {
        const fp = parseInt(document.getElementById('flatPercentInput').value);
        if (!fp || fp < 1 || fp > 99) { alert('Enter a valid flat discount %.'); return false; }
    }
    return true;
}

// ── Countdown timers ─────────────────────────────────────────────────────────
function updateTimers() {
    document.querySelectorAll('.timer-display[data-expiry]').forEach(el => {
        const exp = parseInt(el.dataset.expiry) * 1000;
        const diff = exp - Date.now();
        if (diff <= 0) {
            el.innerHTML = '<span class="timer-expired"><i class="fas fa-times-circle me-1"></i>Expired</span>';
            return;
        }
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        el.querySelector('[data-part="d"]').textContent = String(d).padStart(2,'0');
        el.querySelector('[data-part="h"]').textContent = String(h).padStart(2,'0');
        el.querySelector('[data-part="m"]').textContent = String(m).padStart(2,'0');
        el.querySelector('[data-part="s"]').textContent = String(s).padStart(2,'0');
    });
}
updateTimers();
setInterval(updateTimers, 1000);

// ── Sidebar ──────────────────────────────────────────────────────────────────
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
