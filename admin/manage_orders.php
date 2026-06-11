<?php
require_once __DIR__ . '/../includes/storage.php';
require_once __DIR__ . '/../classes/Inventory.php';

$status_message = '';
$status_class = 'alert-info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $order_status = trim($_POST['order_status'] ?? '');
    $allowed_statuses = ['Delivered', 'Canceled', 'On Process', 'Shipped'];

    if ($order_id > 0 && in_array($order_status, $allowed_statuses, true)) {
        /** @var Inventory $inv */
        $inv = new Inventory();
        $ok = $inv->updateOrderStatus($order_id, $order_status);
        if ($ok) {
            $status_message = 'Order status successfully updated.';
            $status_class = 'alert-success';
        } else {
            $status_message = 'Order not found.';
            $status_class = 'alert-danger';
        }
    } else {
        $status_message = 'Invalid order data submitted.';
        $status_class = 'alert-danger';
    }
}

/** @var Inventory $inv */
$inv = new Inventory();
$orders = $inv->getAllOrders();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | ApeX Gear Admin</title>
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
            overflow-x: hidden;
        }

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
            font-size: .9rem;
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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .coming-soon {
            text-align: center;
        }

        .coming-soon i {
            font-size: 3.5rem;
            color: var(--border);
            margin-bottom: 20px;
            display: block;
        }

        .coming-soon h2 {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-size: 2.4rem;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .coming-soon p {
            color: var(--text-muted);
            font-size: .95rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            padding: 10px 22px;
            background: var(--blue);
            color: #fff;
            border-radius: 9px;
            text-decoration: none;
            font-weight: 700;
            font-size: .88rem;
            transition: opacity .18s;
        }

        .btn-back:hover {
            opacity: .85;
            color: #fff;
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
            <a href="manage_products.php"><i class="fas fa-box-open"></i> Manage Products</a>
            <a href="manage_orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="manage_archives.php"><i class="fas fa-archive"></i> Archives</a>
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
            <span class="topbar-title">Orders</span>
        </header>
        <main class="page-body">
            <div class="admin-panel">
                <div class="order-card mb-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2>Order Management</h2>
                            <p class="text-muted mb-0">Review recent orders and update statuses in real time.</p>
                        </div>
                        <div>
                            <a href="apex26admin.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
                        </div>
                    </div>

                    <?php if ($status_message): ?>
                        <div class="alert <?php echo htmlspecialchars($status_class); ?>" role="alert">
                            <?php echo htmlspecialchars($status_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($orders)): ?>
                        <div class="alert alert-warning">
                            No orders have been placed yet.
                        </div>
                    <?php else: ?>
                        <div class="order-table">
                            <table class="table table-borderless align-middle">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Amount</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Placed</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <?php
                                        $statusClass = 'pending';
                                        if (strtolower($order['order_status']) === 'delivered') {
                                            $statusClass = 'delivered';
                                        } elseif (strtolower($order['order_status']) === 'canceled') {
                                            $statusClass = 'canceled';
                                        } elseif (strtolower($order['order_status']) === 'shipped') {
                                            $statusClass = 'shipped';
                                        } elseif (strtolower($order['order_status']) === 'on process') {
                                            $statusClass = 'process';
                                        }
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($order['reference_number']); ?></strong></td>
                                            <td>
                                                <?php echo htmlspecialchars($order['username'] ?: 'Guest'); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($order['email'] ?: 'No email'); ?></small>
                                            </td>
                                            <td><?php echo intval($order['items_count']); ?></td>
                                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($order['payment_method'] ?: 'N/A'); ?></td>
                                            <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($order['order_status']); ?></span></td>
                                            <td><?php echo date('F d, Y', strtotime($order['created_at'])); ?></td>
                                            <td class="order-actions">
                                                <form method="POST" class="d-flex gap-2 align-items-center">
                                                    <input type="hidden" name="order_id" value="<?php echo intval($order['order_id']); ?>">
                                                    <select name="order_status" class="form-select form-select-sm">
                                                        <option value="On Process" <?php echo $order['order_status'] === 'On Process' ? 'selected' : ''; ?>>On Process</option>
                                                        <option value="Shipped" <?php echo $order['order_status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="Delivered" <?php echo $order['order_status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                        <option value="Canceled" <?php echo $order['order_status'] === 'Canceled' ? 'selected' : ''; ?>>Canceled</option>
                                                    </select>
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
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
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>