<?php
require_once __DIR__ . '/../includes/storage.php';
require_once __DIR__ . '/../classes/Inventory.php';

$inv = new Inventory();
$allOrders = $inv->getAllOrders();
$pendingOrders = count(array_filter($allOrders, fn($o) => strtolower($o['order_status'] ?? '') === 'on process'));

// Load users from session-backed storage
$users = $inv->getAllUsers();

// Search / filter
$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $sl = strtolower($search);
    $users = array_filter($users, function($u) use ($sl) {
        return str_contains(strtolower($u['name']     ?? ''), $sl)
            || str_contains(strtolower($u['email']    ?? ''), $sl)
            || str_contains(strtolower($u['username'] ?? ''), $sl);
    });
}
$userCount = count($users);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users | ApeX Gear Admin</title>
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
            --danger: #ff3b5c; --success: #00d68f;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Barlow', sans-serif; background: var(--panel-bg); color: var(--text-main); display: flex; min-height: 100vh; }

        /* Stats row */
        .stat-row { display: grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 20px 22px; display: flex; align-items: center; gap: 16px; }
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
        .stat-icon.blue { background: rgba(11,47,168,.1); color: var(--blue); }
        .stat-icon.cyan { background: rgba(0,194,255,.1); color: var(--accent); }
        .stat-icon.green { background: rgba(0,214,143,.1); color: var(--success); }
        .stat-val { font-family: 'Barlow Condensed', sans-serif; font-weight: 900; font-size: 1.7rem; line-height: 1; }
        .stat-lbl { font-size: .74rem; color: var(--text-muted); margin-top: 3px; font-weight: 500; }

        /* Search bar */
        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-input { flex: 1; max-width: 360px; padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 9px; font-size: .88rem; background: var(--card-bg); outline: none; transition: border-color .18s; }
        .search-input:focus { border-color: var(--accent); }
        .search-btn { padding: 9px 18px; background: var(--blue); color: #fff; border: none; border-radius: 9px; font-weight: 700; font-size: .84rem; cursor: pointer; }
        .search-btn:hover { opacity: .88; }

        /* Table */
        .panel { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
        .panel-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .panel-title { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 1.15rem; text-transform: uppercase; }
        .panel-count { background: var(--panel-bg); border: 1px solid var(--border); border-radius: 20px; font-size: .75rem; font-weight: 700; padding: 3px 12px; color: var(--text-muted); }
        .tbl { width: 100%; border-collapse: collapse; }
        .tbl thead th { background: var(--panel-bg); font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--text-muted); padding: 12px 16px; border-bottom: 1px solid var(--border); }
        .tbl tbody tr { border-bottom: 1px solid var(--border); }
        .tbl tbody tr:last-child { border-bottom: none; }
        .tbl tbody tr:hover { background: #f9fafc; cursor: pointer; }
        .tbl td { padding: 13px 16px; font-size: .86rem; vertical-align: middle; }

        .avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, var(--blue), var(--accent)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: .88rem; flex-shrink: 0; }
        .user-name { font-weight: 600; }
        .user-meta { font-size: .75rem; color: var(--text-muted); }

        .role-pill { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .7rem; font-weight: 700; text-transform: uppercase; }
        .role-admin { background: rgba(11,47,168,.1); color: var(--blue); }
        .role-customer { background: rgba(0,194,255,.1); color: var(--accent); }

        .act-btn { border: none; border-radius: 7px; padding: 5px 12px; font-size: .77rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: opacity .15s; }
        .btn-view { background: var(--panel-bg); color: var(--blue); border: 1px solid var(--border); }
        .btn-view:hover { background: rgba(11,47,168,.08); }

        .empty-state { text-align: center; padding: 56px 24px; }
        .empty-state i { font-size: 2.8rem; color: var(--border); display: block; margin-bottom: 16px; }
        .empty-state p { color: var(--text-muted); font-size: .9rem; }

        /* User detail modal */
        .modal-header-custom { background: linear-gradient(135deg, #080f1e, var(--blue)); padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; }
        .modal-title-custom { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 1.25rem; color: #fff; text-transform: uppercase; }
        .modal-body-custom { padding: 24px; }
        .detail-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .detail-field { background: var(--panel-bg); border-radius: 9px; padding: 12px 16px; }
        .detail-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--text-muted); margin-bottom: 4px; }
        .detail-value { font-size: .88rem; font-weight: 500; word-break: break-all; }
        .detail-section-title { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 1rem; text-transform: uppercase; color: var(--blue); margin: 18px 0 10px; display: flex; align-items: center; gap: 8px; }
        .detail-section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }
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
        <a href="manage_users.php" class="active"><i class="fas fa-users"></i> Users</a>
        <a href="manage_deals.php"><i class="fas fa-percentage"></i> Deals &amp; Promos</a>
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
            <span class="topbar-title">Users</span>
            <div class="topbar-divider"></div>
            <span class="topbar-crumb">Registered Customers</span>
        </div>
    </header>

    <main class="page-body">

        <!-- Stats -->
        <?php
        $totalUsers    = count($users ?: []);
        $withAddress   = count(array_filter($users ?: [], fn($u) => !empty($u['address']) || !empty($u['shipping_address'])));
        $verifiedUsers = count(array_filter($users ?: [], fn($u) => !empty($u['email_verified'])));
        ?>
        <div class="stat-row">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                <div><div class="stat-val"><?php echo $totalUsers; ?></div><div class="stat-lbl">Total Users</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon cyan"><i class="fas fa-map-marker-alt"></i></div>
                <div><div class="stat-val"><?php echo $withAddress; ?></div><div class="stat-lbl">With Shipping Address</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div><div class="stat-val"><?php echo $verifiedUsers; ?></div><div class="stat-lbl">Verified Accounts</div></div>
            </div>
        </div>

        <!-- Search -->
        <form method="GET" class="search-bar">
            <input type="text" name="q" class="search-input" placeholder="Search by name, email, or username…" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="search-btn"><i class="fas fa-search me-1"></i> Search</button>
            <?php if ($search): ?><a href="manage_users.php" style="align-self:center;font-size:.82rem;color:var(--text-muted);text-decoration:none;margin-left:4px;"><i class="fas fa-times me-1"></i>Clear</a><?php endif; ?>
        </form>

        <!-- Table -->
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title"><i class="fas fa-users me-2" style="color:var(--accent);"></i>All Users</span>
                <span class="panel-count"><?php echo $userCount; ?> user(s)</span>
            </div>
            <?php if (empty($users)): ?>
            <div class="empty-state">
                <i class="fas fa-user-slash"></i>
                <p><?php echo $search ? 'No users match your search.' : 'No registered users yet.<br>Users who sign up will appear here.'; ?></p>
            </div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Registered</th>
                        <th>Last Login</th>
                        <th style="text-align:right;padding-right:20px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i = 1; foreach ($users as $u): ?>
                    <tr onclick="openUserModal(<?php echo htmlspecialchars(json_encode($u), ENT_QUOTES); ?>)" title="Click to view details">
                        <td style="color:var(--text-muted);font-size:.8rem;"><?php echo $i++; ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="avatar"><?php echo strtoupper(substr($u['name'] ?? $u['username'] ?? 'U', 0, 1)); ?></div>
                                <div>
                                    <div class="user-name"><?php echo htmlspecialchars($u['name'] ?? '—'); ?></div>
                                    <div class="user-meta"><?php echo htmlspecialchars($u['email'] ?? ''); ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-family:monospace;font-size:.82rem;color:var(--text-muted);">@<?php echo htmlspecialchars($u['username'] ?? '—'); ?></td>
                        <td>
                            <?php $role = strtolower($u['role'] ?? 'customer'); ?>
                            <span class="role-pill role-<?php echo $role === 'admin' ? 'admin' : 'customer'; ?>">
                                <?php echo ucfirst($role); ?>
                            </span>
                        </td>
                        <td style="color:var(--text-muted);font-size:.83rem;"><?php echo htmlspecialchars($u['phone'] ?? '—'); ?></td>
                        <td style="color:var(--text-muted);font-size:.8rem;"><?php echo !empty($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : '—'; ?></td>
                        <td style="color:var(--text-muted);font-size:.8rem;"><?php echo !empty($u['last_login']) ? date('M d, Y H:i', strtotime($u['last_login'])) : '—'; ?></td>
                        <td style="text-align:right;padding-right:20px;" onclick="event.stopPropagation()">
                            <button class="act-btn btn-view" onclick="openUserModal(<?php echo htmlspecialchars(json_encode($u), ENT_QUOTES); ?>)">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>

    </main>
    <footer style="padding:18px 32px;border-top:1px solid var(--border);background:var(--card-bg);text-align:center;">
        <p style="font-size:.78rem;color:var(--text-muted);margin:0;">© 2026 ApeX Gear Admin Panel — All rights reserved.</p>
    </footer>
</div>

<!-- ── USER DETAIL MODAL ── -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border:none;border-radius:16px;overflow:hidden;">
            <div class="modal-header-custom">
                <span class="modal-title-custom"><i class="fas fa-user me-2"></i>User Details</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body-custom" id="userModalBody">
                <!-- Filled by JS -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openUserModal(u) {
    const addr = u.shipping_address || u.address || {};
    const addrStr = typeof addr === 'string' ? addr : [addr.street, addr.city, addr.province, addr.zip, addr.country].filter(Boolean).join(', ');

    document.getElementById('userModalBody').innerHTML = `
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
            <div class="avatar" style="width:54px;height:54px;font-size:1.3rem;border-radius:50%;background:linear-gradient(135deg,#0b2fa8,#00c2ff);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;flex-shrink:0;">
                ${(u.name || u.username || 'U').charAt(0).toUpperCase()}
            </div>
            <div>
                <div style="font-family:'Barlow Condensed',sans-serif;font-weight:900;font-size:1.4rem;">${esc(u.name || '—')}</div>
                <div style="font-size:.82rem;color:#6b7a99;">${esc(u.email || '')}</div>
            </div>
        </div>

        <div class="detail-section-title"><i class="fas fa-id-card me-1"></i>Account Info</div>
        <div class="detail-row">
            <div class="detail-field"><div class="detail-label">Username</div><div class="detail-value">@${esc(u.username || '—')}</div></div>
            <div class="detail-field"><div class="detail-label">Role</div><div class="detail-value">${esc(u.role || 'customer')}</div></div>
            <div class="detail-field"><div class="detail-label">Phone</div><div class="detail-value">${esc(u.phone || '—')}</div></div>
            <div class="detail-field"><div class="detail-label">Email Verified</div><div class="detail-value">${u.email_verified ? '✅ Yes' : '❌ No'}</div></div>
            <div class="detail-field"><div class="detail-label">Registered</div><div class="detail-value">${esc(u.created_at || '—')}</div></div>
            <div class="detail-field"><div class="detail-label">Last Login</div><div class="detail-value">${esc(u.last_login || '—')}</div></div>
        </div>

        <div class="detail-section-title"><i class="fas fa-map-marker-alt me-1"></i>Shipping Address</div>
        <div class="detail-field" style="margin-bottom:14px;">
            <div class="detail-value" style="font-size:.88rem;">${addrStr || '<span style="color:#6b7a99;">No address saved</span>'}</div>
        </div>

        ${u.profile_bio || u.bio ? `
        <div class="detail-section-title"><i class="fas fa-align-left me-1"></i>Profile Bio</div>
        <div class="detail-field"><div class="detail-value">${esc(u.profile_bio || u.bio || '')}</div></div>` : ''}
    `;
    new bootstrap.Modal(document.getElementById('userModal')).show();
}
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
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
