<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admingear.php");
    exit;
}

require_once __DIR__ . '/../includes/storage.php';
require_once __DIR__ . '/../classes/Inventory.php';
require_once __DIR__ . '/../database/db_connect.php';

// Instantiate Inventory for Sidebar Badges
$inv = new Inventory();
$allOrders    = $inv->getAllOrders();
$pendingOrders = count(array_filter($allOrders, fn($o) => strtolower($o['order_status'] ?? '') === 'on process'));

// Instantiate OOP Database
$db = new Database();
$conn = $db->getConnection();

// ── POST HANDLERS ────────────────────────────────────────────────────────────
$msg = ''; $msgType = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $admin_id = isset($_SESSION['admin']['id']) ? intval($_SESSION['admin']['id']) : null;

    // Helper: is there currently an active (and not yet expired) promo?
    $hasActivePromo = function () use ($conn) {
        $res = $conn->query("SELECT coupon_id, code_name FROM coupon_code WHERE is_active = 1 AND valid_until > NOW() LIMIT 1");
        return $res && $res->num_rows > 0 ? $res->fetch_assoc() : null;
    };

    // 1. ADD PROMO
    if ($action === 'add_promo') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $discount = intval($_POST['discount'] ?? 0);
        $expiry = trim($_POST['expiry'] ?? '');

        $activePromo = $hasActivePromo();

        if ($activePromo) {
            $msg = 'Cannot create a new promo code while "' . htmlspecialchars($activePromo['code_name']) . '" is still active. Deactivate or delete it first.';
            $msgType = 'danger';
        } elseif ($code && $discount > 0 && $expiry) {
            // Convert HTML datetime-local (YYYY-MM-DDTHH:MM) to MySQL DATETIME (YYYY-MM-DD HH:MM:00)
            $expiry_db = str_replace('T', ' ', $expiry) . ':00';

            $stmt = $conn->prepare("INSERT INTO coupon_code (code_name, discount_percentage, valid_until, is_active) VALUES (?, ?, ?, 1)");
            $stmt->bind_param("sis", $code, $discount, $expiry_db);
            
            if ($stmt->execute()) {
                $inv->logAdminActivity('deal_add', "Added promo code {$code} ({$discount}% off).", $admin_id);
                $msg = 'Promo code successfully added!';
                $msgType = 'success';
            } else {
                $msg = 'Database Error: That promo code name might already exist.';
                $msgType = 'danger';
            }
        } else {
            $msg = 'Please fill all promo fields correctly.';
            $msgType = 'danger';
        }
    }
    
    // 2. DELETE PROMO
    elseif ($action === 'delete_promo') {
        $coupon_id = intval($_POST['coupon_id'] ?? 0);
        
        $stmt = $conn->prepare("DELETE FROM coupon_code WHERE coupon_id = ?");
        $stmt->bind_param("i", $coupon_id);
        
        if ($stmt->execute()) {
            $inv->logAdminActivity('deal_delete', "Deleted promo code ID {$coupon_id}.", $admin_id);
            $msg = 'Promo code deleted from database.';
            $msgType = 'success';
        } else {
            $msg = 'Failed to delete promo code.';
            $msgType = 'danger';
        }
    }

    // 3. TOGGLE ACTIVE STATUS
    elseif ($action === 'toggle_active') {
        $coupon_id = intval($_POST['coupon_id'] ?? 0);
        $newStatus = intval($_POST['new_status'] ?? 0);

        if ($newStatus === 1) {
            $activePromo = $hasActivePromo();
            if ($activePromo && (int)$activePromo['coupon_id'] !== $coupon_id) {
                $msg = 'Cannot activate this promo while "' . htmlspecialchars($activePromo['code_name']) . '" is still active. Deactivate it first.';
                $msgType = 'danger';
            } else {
                $stmt = $conn->prepare("UPDATE coupon_code SET is_active = 1 WHERE coupon_id = ?");
                $stmt->bind_param("i", $coupon_id);
                if ($stmt->execute()) {
                    $inv->logAdminActivity('deal_activate', "Activated promo code ID {$coupon_id}.", $admin_id);
                    $msg = 'Promo code activated.';
                    $msgType = 'success';
                } else {
                    $msg = 'Failed to activate promo code.';
                    $msgType = 'danger';
                }
            }
        } else {
            $stmt = $conn->prepare("UPDATE coupon_code SET is_active = 0 WHERE coupon_id = ?");
            $stmt->bind_param("i", $coupon_id);
            if ($stmt->execute()) {
                $inv->logAdminActivity('deal_deactivate', "Deactivated promo code ID {$coupon_id}.", $admin_id);
                $msg = 'Promo code deactivated.';
                $msgType = 'success';
            } else {
                $msg = 'Failed to deactivate promo code.';
                $msgType = 'danger';
            }
        }
    }
}

// ── FETCH PROMOS FROM DB ──
$promoCodes = [];
$res = $conn->query("SELECT * FROM coupon_code ORDER BY is_active DESC, valid_until ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $promoCodes[] = $row;
    }
}

// Currently active, non-expired promo (enforces the "one active promo" rule on the form)
$currentActivePromo = null;
foreach ($promoCodes as $p) {
    if ((int)$p['is_active'] === 1 && strtotime($p['valid_until']) > time()) {
        $currentActivePromo = $p;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promo Codes | ApeX Gear Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin-style.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-w: 260px; --sidebar-bg: #080f1e;
            --sidebar-hover: rgba(0,194,255,.08); --accent: #00c2ff;
            --panel-bg: #f4f6fb; --card-bg: #ffffff;
            --border: #e3e8f0; --text-main: #0d1b2e;
            --text-muted: #6b7a99; --blue: #0b2fa8;
            --danger: #ff3b5c; --success: #00d68f; --warning: #f5c518;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Barlow', sans-serif; background: var(--panel-bg); color: var(--text-main); display: flex; min-height: 100vh; }

        /* Alerts */
        .apex-alert { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; font-size: .87rem; font-weight: 500; animation: fade-in .3s ease; }
        .apex-alert.success { background: rgba(0,214,143,.1); color: #009c60; border: 1px solid rgba(0,214,143,.25); }
        .apex-alert.danger { background: rgba(255,59,92,.1); color: #d62842; border: 1px solid rgba(255,59,92,.25); }
        @keyframes fade-in { from{opacity:0;transform:translateY(-10px);} to{opacity:1;transform:translateY(0);} }
        .close-btn { margin-left: auto; background: none; border: none; cursor: pointer; opacity: .6; transition: opacity .2s; }
        .close-btn:hover { opacity: 1; }

        /* Panels */
        .panel { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; height: 100%; }
        .panel-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: #fff; }
        .panel-title { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 1.15rem; text-transform: uppercase; display: flex; align-items: center; gap: 8px; }
        .panel-body { padding: 24px; }

        /* Deal Cards */
        .deal-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 18px; margin-bottom: 16px; position: relative; transition: transform .2s, box-shadow .2s; }
        .deal-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(13,27,46,.06); }
        .deal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed var(--border); }
        .deal-badge { font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; padding: 4px 10px; border-radius: 20px; }
        .deal-badge.active { background: rgba(0,214,143,.1); color: #009c60; }
        .deal-badge.inactive { background: rgba(107,122,153,.12); color: var(--text-muted); }
        .deal-badge.expired { background: rgba(255,59,92,.1); color: var(--danger); }
        
        .act-btn { background: var(--panel-bg); border: 1px solid var(--border); border-radius: 6px; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-muted); transition: all .2s; }
        .act-btn.btn-del:hover { background: var(--danger); color: #fff; border-color: var(--danger); }

        .deal-code { font-family: 'Barlow Condensed', sans-serif; font-weight: 900; font-size: 1.6rem; color: var(--text-main); line-height: 1.1; margin-bottom: 6px; letter-spacing: .02em; }
        .deal-detail { font-size: .84rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px; margin-bottom: 4px; font-weight: 500; }

        /* Form */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: .78rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 6px; letter-spacing: .04em; }
        .apex-input { width: 100%; padding: 12px 14px; border: 1.5px solid var(--border); border-radius: 8px; font-size: .9rem; background: #fff; transition: border-color .2s; outline: none; }
        .apex-input:focus { border-color: var(--accent); }
        .apex-btn { width: 100%; padding: 12px; background: var(--blue); color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: .9rem; text-transform: uppercase; letter-spacing: .05em; cursor: pointer; transition: background .2s; margin-top: 10px;}
        .apex-btn:hover { background: #082280; }

        /* Timer */
        .timer-display { display: flex; gap: 8px; margin-top: 10px; }
        .timer-block { background: var(--text-main); color: #fff; border-radius: 6px; padding: 4px 8px; text-align: center; min-width: 44px; }
        .timer-val { font-family: monospace; font-size: 1.1rem; font-weight: 700; line-height: 1; }
        .timer-lbl { font-size: .6rem; text-transform: uppercase; opacity: .7; margin-top: 2px; }
        .timer-expired { color: var(--danger); font-weight: 700; font-size: .85rem; background: rgba(255,59,92,.1); padding: 4px 10px; border-radius: 6px; }
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
        <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
        <a href="manage_deals.php" class="active"><i class="fas fa-ticket-alt"></i> Promo Codes</a>
        
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
        <div class="topbar-left">
            <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <span class="topbar-title">Promo Codes</span>
            <div class="topbar-divider"></div>
            <span class="topbar-crumb">Manage Discounts</span>
        </div>
    </header>

    <main class="page-body">
        
        <?php if ($msg): ?>
        <div class="apex-alert <?php echo $msgType; ?>">
            <i class="fas <?php echo $msgType==='success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($msg); ?></span>
            <button class="close-btn" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            
            <!-- ── CREATE PROMO FORM ── -->
            <div class="col-lg-4">
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-title"><i class="fas fa-plus-circle" style="color:var(--accent);"></i> Add Promo Code</span>
                    </div>
                    <div class="panel-body">
                        <?php if ($currentActivePromo): ?>
                            <div style="background:rgba(245,197,24,.1); border:1px solid rgba(245,197,24,.3); border-radius:10px; padding:14px 16px; margin-bottom:18px; font-size:.83rem; color:#9a7d00; display:flex; gap:10px; align-items:flex-start;">
                                <i class="fas fa-exclamation-triangle" style="margin-top:2px;"></i>
                                <span>Promo <strong><?php echo htmlspecialchars($currentActivePromo['code_name']); ?></strong> is currently active. Deactivate or delete it before adding a new one.</span>
                            </div>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_promo">
                            
                            <div class="form-group">
                                <label class="form-label" for="promo_code">Code Name (e.g. SUMMER20)</label>
                                <input type="text" class="apex-input" id="promo_code" name="code" placeholder="Enter code" required style="text-transform:uppercase;" <?php echo $currentActivePromo ? 'disabled' : ''; ?>>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="promo_disc">Discount Percentage (%)</label>
                                <input type="number" class="apex-input" id="promo_disc" name="discount" placeholder="e.g. 15" required min="1" max="100" <?php echo $currentActivePromo ? 'disabled' : ''; ?>>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="promo_expiry">Expiration Date & Time</label>
                                <input type="datetime-local" class="apex-input" id="promo_expiry" name="expiry" required min="<?php echo date('Y-m-d\TH:i'); ?>" <?php echo $currentActivePromo ? 'disabled' : ''; ?>>
                            </div>
                            
                            <button type="submit" class="apex-btn" <?php echo $currentActivePromo ? 'disabled style="opacity:.5;cursor:not-allowed;"' : ''; ?>><i class="fas fa-check"></i> Save Promo Code</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ── ACTIVE PROMOS LIST ── -->
            <div class="col-lg-8">
                <div class="panel">
                    <div class="panel-header">
                        <span class="panel-title"><i class="fas fa-ticket-alt" style="color:var(--warning);"></i> Active Promos</span>
                        <span style="background:var(--panel-bg);border:1px solid var(--border);border-radius:20px;font-size:.75rem;font-weight:700;padding:4px 12px;color:var(--text-muted);"><?php echo count($promoCodes); ?> total code(s)</span>
                    </div>
                    <div class="panel-body" style="background:#fafbfc;">
                        
                        <?php if (empty($promoCodes)): ?>
                            <div style="text-align:center; padding:50px 10px; color:var(--text-muted); font-size:.9rem; border:2px dashed var(--border); border-radius:12px;">
                                <i class="fas fa-ticket-alt" style="font-size:2rem; opacity:0.3; display:block; margin-bottom:12px;"></i>
                                No promo codes available in the database.
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                            <?php foreach ($promoCodes as $p): 
                                $notExpired = strtotime($p['valid_until']) > time();
                                $isActive = $notExpired && (int)$p['is_active'] === 1;
                                if (!$notExpired) {
                                    $statusClass = 'expired';
                                    $statusText  = 'Expired';
                                } elseif ($isActive) {
                                    $statusClass = 'active';
                                    $statusText  = 'Active';
                                } else {
                                    $statusClass = 'inactive';
                                    $statusText  = 'Inactive';
                                }
                            ?>
                                <div class="col-md-6">
                                    <div class="deal-card">
                                        <div class="deal-header">
                                            <span class="deal-badge <?php echo $statusClass; ?>" id="badge-<?php echo $p['coupon_id']; ?>" data-expiry="<?php echo strtotime($p['valid_until']); ?>"><?php echo $statusText; ?></span>
                                            <div style="display:flex; gap:8px;">
                                                <?php if ($notExpired): ?>
                                                <form method="POST" style="margin:0;">
                                                    <input type="hidden" name="action" value="toggle_active">
                                                    <input type="hidden" name="coupon_id" value="<?php echo $p['coupon_id']; ?>">
                                                    <input type="hidden" name="new_status" value="<?php echo $isActive ? 0 : 1; ?>">
                                                    <button type="submit" class="act-btn" title="<?php echo $isActive ? 'Deactivate' : 'Activate'; ?> Promo">
                                                        <i class="fas <?php echo $isActive ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <form method="POST" style="margin:0;" onsubmit="return confirm('Delete this promo code from the database?');">
                                                    <input type="hidden" name="action" value="delete_promo">
                                                    <input type="hidden" name="coupon_id" value="<?php echo $p['coupon_id']; ?>">
                                                    <button type="submit" class="act-btn btn-del" title="Delete Promo"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="deal-body">
                                            <div class="deal-code"><?php echo htmlspecialchars($p['code_name']); ?></div>
                                            <div class="deal-detail"><i class="fas fa-tag me-2" style="color:var(--danger);"></i> <strong style="color:var(--danger);"><?php echo $p['discount_percentage']; ?>% OFF</strong></div>
                                            
                                            <div class="deal-detail timer-display" data-expiry="<?php echo strtotime($p['valid_until']); ?>">
                                                <div class="timer-block"><div class="timer-val" data-part="d">00</div><div class="timer-lbl">Days</div></div>
                                                <div class="timer-block"><div class="timer-val" data-part="h">00</div><div class="timer-lbl">Hrs</div></div>
                                                <div class="timer-block"><div class="timer-val" data-part="m">00</div><div class="timer-lbl">Mins</div></div>
                                                <div class="timer-block"><div class="timer-val" data-part="s">00</div><div class="timer-lbl">Secs</div></div>
                                            </div>
                                            <div style="font-size:.75rem;color:var(--text-muted);margin-top:12px; border-top:1px solid var(--border); padding-top:12px;">
                                                <i class="fas fa-calendar-alt me-1"></i> Expires: <?php echo date('M d, Y h:i A', strtotime($p['valid_until'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </div>
    </main>
    <footer style="padding:18px 32px;border-top:1px solid var(--border);background:var(--card-bg);text-align:center;">
        <p style="font-size:.78rem;color:var(--text-muted);margin:0;">© 2026 ApeX Gear Admin Panel — All rights reserved.</p>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Timer Logic ──────────────────────────────────────────────────────────────
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

    // ── Auto-flip status badges to "Expired" when their countdown reaches zero ──
    document.querySelectorAll('.deal-badge[data-expiry]').forEach(badge => {
        const exp = parseInt(badge.dataset.expiry) * 1000;
        if (Date.now() >= exp && !badge.classList.contains('expired')) {
            badge.classList.remove('active', 'inactive');
            badge.classList.add('expired');
            badge.innerHTML = 'Expired';

            // Hide the activate/deactivate toggle button since the promo is no longer valid
            const card = badge.closest('.deal-card');
            if (card) {
                const toggleForm = card.querySelector('form[method="POST"] input[name="action"][value="toggle_active"]');
                if (toggleForm) {
                    toggleForm.closest('form').style.display = 'none';
                }
            }
        }
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
