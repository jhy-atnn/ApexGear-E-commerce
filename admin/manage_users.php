<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admingear.php");
    exit;
}

// 1. COMPLETELY REMOVED storage.php AND Inventory.php DEPENDENCIES
require_once __DIR__ . '/../database/db_connect.php';

// Instantiate OOP Database
$db = new Database();
$conn = $db->getConnection();

// 2. NEW: Native SQL fetch for the Pending Orders sidebar badge
$pendingQuery = "SELECT COUNT(*) as pending_count FROM orders_tbl WHERE order_status = 'Pending' OR order_status = 'On Process'";
$pendingResult = $conn->query($pendingQuery);
$pendingRow = $pendingResult->fetch_assoc();
$pendingOrders = $pendingRow['pending_count'] ?? 0;

// ── HANDLE ARCHIVE ACTION ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'archive') {
    $archiveId = intval($_POST['user_id']);

    // Fetch user details first
    $stmt = $conn->prepare("SELECT * FROM users_tbl WHERE user_id = ?");
    $stmt->bind_param("i", $archiveId);
    $stmt->execute();
    $usr = $stmt->get_result()->fetch_assoc();

    if ($usr) {
        // Insert into archived_users_tbl
        $ins = $conn->prepare("INSERT INTO archived_users_tbl (original_user_id, first_name, last_name, m_name, gender, username, email) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->bind_param("issssss", $usr['user_id'], $usr['first_name'], $usr['last_name'], $usr['m_name'], $usr['gender'], $usr['username'], $usr['email']);

        if ($ins->execute()) {
            // Delete from active users_tbl
            $del = $conn->prepare("DELETE FROM users_tbl WHERE user_id = ?");
            $del->bind_param("i", $archiveId);
            $del->execute();
            header("Location: manage_users.php?success=archived");
            exit;
        }
    }
}

// ── SEARCH & FETCH ACTIVE USERS ──
$search = trim($_GET['q'] ?? '');
$query = "
    SELECT u.user_id, u.first_name, u.last_name, u.m_name, u.gender, u.username, u.email, 
           p.bio, p.street_address, p.city, p.zip_code, p.phone_number, p.image_path 
    FROM users_tbl u 
    LEFT JOIN users_profiles_tbl p ON u.user_id = p.user_id
";

if ($search !== '') {
    $searchTerm = '%' . $search . '%';
    $query .= " WHERE u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

$users = [];
while ($row = $result->fetch_assoc()) {
    // Fetch their latest shipping address if any
    $shipStmt = $conn->prepare("SELECT street_address, city, zip_code, phone_number FROM shipping_address_tbl WHERE user_id = ? ORDER BY address_id DESC LIMIT 1");
    $shipStmt->bind_param("i", $row['user_id']);
    $shipStmt->execute();
    $shipRes = $shipStmt->get_result();
    $row['shipping'] = $shipRes->fetch_assoc();

    $users[] = $row;
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

        /* Stats row */
        .stat-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .stat-icon.blue {
            background: rgba(11, 47, 168, .1);
            color: var(--blue);
        }

        .stat-icon.cyan {
            background: rgba(0, 194, 255, .1);
            color: var(--accent);
        }

        .stat-val {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-size: 1.7rem;
            line-height: 1;
        }

        .stat-lbl {
            font-size: .74rem;
            color: var(--text-muted);
            margin-top: 3px;
            font-weight: 500;
        }

        /* Search bar */
        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-input {
            flex: 1;
            max-width: 360px;
            padding: 9px 14px;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            font-size: .88rem;
            background: var(--card-bg);
            outline: none;
            transition: border-color .18s;
        }

        .search-input:focus {
            border-color: var(--accent);
        }

        .search-btn {
            padding: 9px 18px;
            background: var(--blue);
            color: #fff;
            border: none;
            border-radius: 9px;
            font-weight: 700;
            font-size: .84rem;
            cursor: pointer;
        }

        .search-btn:hover {
            opacity: .88;
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

        .apex-alert .close-btn {
            margin-left: auto;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            color: inherit;
            opacity: .6;
        }

        /* Table */
        .panel {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
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
            cursor: pointer;
        }

        .tbl td {
            padding: 13px 16px;
            font-size: .86rem;
            vertical-align: middle;
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: .88rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .user-name {
            font-weight: 600;
        }

        .user-meta {
            font-size: .75rem;
            color: var(--text-muted);
        }

        .role-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            background: rgba(0, 194, 255, .1);
            color: var(--accent);
        }

        .act-btn {
            border: none;
            border-radius: 7px;
            padding: 5px 12px;
            font-size: .77rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: opacity .15s;
        }

        .btn-view {
            background: var(--panel-bg);
            color: var(--blue);
            border: 1px solid var(--border);
        }

        .btn-view:hover {
            background: rgba(11, 47, 168, .08);
        }

        .btn-archive {
            background: rgba(245, 197, 24, .1);
            color: #8a6d00;
            border: 1px solid rgba(245, 197, 24, .3);
        }

        .btn-archive:hover {
            background: #f5c518;
            color: #000;
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

        /* User detail modal */
        .modal-header-custom {
            background: linear-gradient(135deg, #080f1e, var(--blue));
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title-custom {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 1.25rem;
            color: #fff;
            text-transform: uppercase;
        }

        .modal-body-custom {
            padding: 24px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 14px;
        }

        .detail-field {
            background: var(--panel-bg);
            border-radius: 9px;
            padding: 12px 16px;
        }

        .detail-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: .88rem;
            font-weight: 500;
            word-break: break-all;
        }

        .detail-section-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 1rem;
            text-transform: uppercase;
            color: var(--blue);
            margin: 18px 0 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
    </style>
</head>

<body>
    <?php $currentAdminPage = 'manage_users.php'; include __DIR__ . '/includes/admin_sidebar.php'; ?>
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
            <a href="manage_archives.php"><i class="fas fa-archive"></i> Archives</a>
            <a href="manage_users.php" class="active"><i class="fas fa-users"></i> Users</a>
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
                <span class="topbar-title">Users</span>
                <div class="topbar-divider"></div>
                <span class="topbar-crumb">Registered Customers</span>
            </div>
        </header>

        <main class="page-body">

            <?php if (isset($_GET['success']) && $_GET['success'] === 'archived'): ?>
                <div class="apex-alert success">
                    <i class="fas fa-check-circle"></i>
                    <span>User account successfully archived.</span>
                    <button class="close-btn" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                </div>
            <?php endif; ?>

            <div class="stat-row">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="stat-val"><?php echo $userCount; ?></div>
                        <div class="stat-lbl">Active Users</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon cyan"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <div class="stat-val"><?php echo count(array_filter($users, fn($u) => !empty($u['shipping']))); ?></div>
                        <div class="stat-lbl">With Shipping Data</div>
                    </div>
                </div>
            </div>

            <form method="GET" class="search-bar">
                <input type="text" name="q" class="search-input" placeholder="Search by name, email, or username…" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn"><i class="fas fa-search me-1"></i> Search</button>
                <?php if ($search): ?><a href="manage_users.php" style="align-self:center;font-size:.82rem;color:var(--text-muted);text-decoration:none;margin-left:4px;"><i class="fas fa-times me-1"></i>Clear</a><?php endif; ?>
            </form>

            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title"><i class="fas fa-users me-2" style="color:var(--accent);"></i>Active Users</span>
                    <span class="panel-count"><?php echo $userCount; ?> active user(s)</span>
                </div>
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <p><?php echo $search ? 'No users match your search.' : 'No registered users yet.'; ?></p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>User</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Phone</th>
                                    <th style="text-align:right;padding-right:20px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u):
                                    $fullName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                                    $initial = strtoupper(substr($fullName ?: $u['username'], 0, 1));
                                ?>
                                    <tr onclick='openUserModal(<?php echo json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' title="Click to view details">
                                        <td style="color:var(--text-muted);font-size:.8rem;"><?php echo $u['user_id']; ?></td>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:10px;">
                                                <div class="avatar">
                                                    <?php if (!empty($u['image_path'])): ?>
                                                        <img src="<?php echo str_starts_with($u['image_path'], 'http') ? htmlspecialchars($u['image_path']) : '../' . htmlspecialchars($u['image_path']); ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                                                    <?php else: ?>
                                                        <?php echo $initial; ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="user-name"><?php echo htmlspecialchars($fullName); ?></div>
                                                    <div class="user-meta"><?php echo htmlspecialchars($u['email'] ?? ''); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="font-family:monospace;font-size:.82rem;color:var(--text-muted);">@<?php echo htmlspecialchars($u['username'] ?? '—'); ?></td>
                                        <td><span class="role-pill">Customer</span></td>
                                        <td style="color:var(--text-muted);font-size:.83rem;"><?php echo htmlspecialchars($u['phone_number'] ?? '—'); ?></td>
                                        <td style="text-align:right;padding-right:20px;" onclick="event.stopPropagation()">
                                            <button class="act-btn btn-view" onclick='openUserModal(<?php echo json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Archive this user? They will be moved to the archives and can no longer log in.');">
                                                <input type="hidden" name="action" value="archive">
                                                <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                                <button type="submit" class="act-btn btn-archive ms-1"><i class="fas fa-archive"></i> Archive</button>
                                            </form>
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

    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border:none;border-radius:16px;overflow:hidden;">
                <div class="modal-header-custom">
                    <span class="modal-title-custom"><i class="fas fa-user me-2"></i>User Details</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body-custom" id="userModalBody">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openUserModal(u) {
            // 1. Profile Address Construction
            const profAddrParts = [u.street_address, u.city, u.zip_code].filter(Boolean);
            const profAddr = profAddrParts.length > 0 ? profAddrParts.join(', ') : '<span style="color:#6b7a99;">No profile address saved</span>';

            // 2. Shipping Address Construction
            let shipAddr = '<span style="color:#6b7a99;">No shipping address used yet</span>';
            if (u.shipping) {
                const shipParts = [u.shipping.street_address, u.shipping.city, u.shipping.zip_code].filter(Boolean);
                shipAddr = shipParts.join(', ') + ` <br><small style="color:var(--text-muted);">(Phone: ${u.shipping.phone_number || 'N/A'})</small>`;
            }

            // 3. Name & Avatar Construction
            const fullName = [u.first_name, u.m_name, u.last_name].filter(Boolean).join(' ');
            const initial = fullName.charAt(0).toUpperCase() || 'U';

            let avatarHtml = initial;
            if (u.image_path) {
                const imgSrc = u.image_path.startsWith('http') ? u.image_path : '../' + u.image_path;
                avatarHtml = `<img src="${esc(imgSrc)}" alt="Profile" style="width:100%;height:100%;object-fit:cover;">`;
            }

            document.getElementById('userModalBody').innerHTML = `
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
            <div class="avatar" style="width:54px;height:54px;font-size:1.3rem;border-radius:50%;background:linear-gradient(135deg,#0b2fa8,#00c2ff);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;flex-shrink:0;overflow:hidden;">
                ${avatarHtml}
            </div>
            <div>
                <div style="font-family:'Barlow Condensed',sans-serif;font-weight:900;font-size:1.4rem;">${esc(fullName)}</div>
                <div style="font-size:.82rem;color:#6b7a99;">${esc(u.email || '')}</div>
            </div>
        </div>

        <div class="detail-section-title"><i class="fas fa-id-card me-1"></i>Account Info</div>
        <div class="detail-row">
            <div class="detail-field"><div class="detail-label">Username</div><div class="detail-value">@${esc(u.username || '—')}</div></div>
            <div class="detail-field"><div class="detail-label">Gender</div><div class="detail-value">${esc(u.gender || '—')}</div></div>
            <div class="detail-field"><div class="detail-label">Primary Phone</div><div class="detail-value">${esc(u.phone_number || '—')}</div></div>
        </div>

        <div class="detail-section-title"><i class="fas fa-home me-1"></i>Main Profile Address</div>
        <div class="detail-field" style="margin-bottom:14px;">
            <div class="detail-value" style="font-size:.88rem;">${profAddr}</div>
        </div>

        <div class="detail-section-title"><i class="fas fa-truck me-1"></i>Latest Shipping Address</div>
        <div class="detail-field" style="margin-bottom:14px;">
            <div class="detail-value" style="font-size:.88rem;">${shipAddr}</div>
        </div>

        ${u.bio ? `
        <div class="detail-section-title"><i class="fas fa-align-left me-1"></i>Profile Bio</div>
        <div class="detail-field"><div class="detail-value">${esc(u.bio)}</div></div>` : ''}
    `;
            new bootstrap.Modal(document.getElementById('userModal')).show();
        }

        function esc(s) {
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
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
