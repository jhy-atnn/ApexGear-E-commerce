<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admingear.php");
    exit;
}

// Ensure strict OOP logic
require_once __DIR__ . '/../classes/Inventory.php';
require_once __DIR__ . '/../database/db_connect.php';

$status_message = '';
$status_class   = 'alert-info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $order_id     = intval($_POST['order_id']     ?? 0);
        $order_status = trim($_POST['order_status']   ?? '');
        $remarks      = trim($_POST['remarks']        ?? '');

        // Allowed statuses matching your tracking modal progress steps
        $allowed      = ['Pending', 'On Process', 'Shipped', 'Delivered', 'Canceled', 'Completed'];

        if ($order_id > 0 && in_array($order_status, $allowed, true)) {

            $fulfillmentStatuses = ['On Process', 'Shipped', 'Delivered'];
            $blockedByPayment    = false;

            if (in_array($order_status, $fulfillmentStatuses, true)) {
                $db   = new Database();
                $conn = $db->getConnection();

                $chk = $conn->prepare("SELECT status AS payment_status, method AS payment_method FROM payments_tbl WHERE order_id = ? LIMIT 1");
                $chk->bind_param("i", $order_id);
                $chk->execute();
                $chkRow = $chk->get_result()->fetch_assoc();
                $chk->close();
                $db->closeConnection();

                $pmStatus = strtolower(trim($chkRow['payment_status'] ?? 'pending'));
                $pmMethod = strtolower(trim($chkRow['payment_method'] ?? ''));
                $isCOD    = (strpos($pmMethod, 'cash on delivery') !== false || strpos($pmMethod, 'cod') !== false);

                if (!$isCOD && $pmStatus !== 'paid') {
                    $blockedByPayment = true;
                }
            }

            if ($blockedByPayment) {
                $status_message = 'This order\'s payment must be approved before its fulfillment status can be updated.';
                $status_class   = 'alert-danger';
            } else {
                $inv = new Inventory();
                $admin_id = isset($_SESSION['admin']['id']) ? intval($_SESSION['admin']['id']) : null;
                $ok  = $inv->updateOrderStatus($order_id, $order_status, $remarks, $admin_id);

                if ($ok) {
                    $status_message = 'Order status successfully updated to ' . htmlspecialchars($order_status) . '.';
                    $status_class   = 'alert-success';
                    $activityType = $order_status === 'Completed' ? 'order_completed' : 'order_status';
                    $inv->logAdminActivity($activityType, "Updated order #{$order_id} status to {$order_status}.", $admin_id);

                    // ── NOTIFICATION SYSTEM TRIGGER ──
                    $db = new Database();
                    $conn = $db->getConnection();

                    if ($order_status === 'Completed') {
                        if ($inv->syncCompletedOrderPayments($order_id)) {
                            $status_message .= ' Payment status was also set to Paid.';
                            $inv->logAdminActivity('payment_status', "Updated order #{$order_id} payment status to Paid.", $admin_id);
                        }
                    }

                    // Fetch the user_id and reference number for this specific order
                    $fetchStmt = $conn->prepare("SELECT user_id, order_ref_code AS reference_number FROM orders_tbl WHERE order_id = ?");
                    $fetchStmt->bind_param("i", $order_id);
                    $fetchStmt->execute();
                    $res = $fetchStmt->get_result();

                    if ($row = $res->fetch_assoc()) {
                        $user_id = $row['user_id'];
                        $ref = $row['reference_number'];

                        // Verify it is a registered user (not a guest checkout) before sending notification
                        if (!empty($user_id)) {
                            // Construct the notification message
                            $msg = "Your order ({$ref}) status has been updated to: {$order_status}.";
                            if (!empty($remarks)) {
                                $msg .= " Note: " . $remarks;
                            }

                            // Insert directly into the notifications_tbl
                            $notifStmt = $conn->prepare("INSERT INTO notifications_tbl (user_id, message) VALUES (?, ?)");
                            $notifStmt->bind_param("is", $user_id, $msg);
                            $notifStmt->execute();
                        }
                    }
                    $db->closeConnection();
                    // ─────────────────────────────────

                } else {
                    $status_message = 'Failed to update order.';
                    $status_class   = 'alert-danger';
                }
            }
        } else {
            $status_message = 'Invalid order data or status.';
            $status_class   = 'alert-danger';
        }
    } elseif (isset($_POST['update_payment_status'])) {
        // ── APPROVE / REJECT PAYMENT (validates the uploaded QR/e-wallet receipt) ──
        $order_id       = intval($_POST['order_id'] ?? 0);
        $payment_status = trim($_POST['payment_status'] ?? '');
        $allowedPay     = ['Pending', 'Paid', 'Rejected'];

        if ($order_id > 0 && in_array($payment_status, $allowedPay, true)) {
            $db   = new Database();
            $conn = $db->getConnection();

            $stmt = $conn->prepare("UPDATE payments_tbl SET status = ? WHERE order_id = ?");
            $stmt->bind_param("si", $payment_status, $order_id);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                $status_message = 'Payment status updated to ' . htmlspecialchars($payment_status) . '.';
                $status_class   = 'alert-success';

                $admin_id = isset($_SESSION['admin']['id']) ? intval($_SESSION['admin']['id']) : null;
                $inv = new Inventory();
                if (method_exists($inv, 'logAdminActivity')) {
                    $inv->logAdminActivity('payment_status', "Updated order #{$order_id} payment status to {$payment_status}.", $admin_id);
                }

                // ── NOTIFICATION SYSTEM TRIGGER ──
                $fetchStmt = $conn->prepare("SELECT user_id, order_ref_code AS reference_number FROM orders_tbl WHERE order_id = ?");
                $fetchStmt->bind_param("i", $order_id);
                $fetchStmt->execute();
                $res = $fetchStmt->get_result();

                if ($row = $res->fetch_assoc()) {
                    $user_id = $row['user_id'];
                    $ref = $row['reference_number'];

                    if (!empty($user_id)) {
                        if ($payment_status === 'Paid') {
                            $msg = "Good news! Your payment for order ({$ref}) has been verified and approved.";
                        } elseif ($payment_status === 'Rejected') {
                            $msg = "We couldn't verify your payment receipt for order ({$ref}). Please contact support or re-submit a valid receipt.";
                        } else {
                            $msg = "Your payment for order ({$ref}) is now pending verification.";
                        }

                        $notifStmt = $conn->prepare("INSERT INTO notifications_tbl (user_id, message) VALUES (?, ?)");
                        $notifStmt->bind_param("is", $user_id, $msg);
                        $notifStmt->execute();
                    }
                }
                $db->closeConnection();
                // ─────────────────────────────────
            } else {
                $status_message = 'Failed to update payment status.';
                $status_class   = 'alert-danger';
            }
        } else {
            $status_message = 'Invalid payment status update request.';
            $status_class   = 'alert-danger';
        }
    }
}

// Fetch all orders for the admin table
$inv    = new Inventory();
$inv->syncCompletedOrderPayments();
$orders = $inv->getAllOrders();

$processingOrders = [];
$completedOrders = [];
$cancelledOrders = [];

foreach ($orders as $order) {
    $st = strtolower($order['order_status'] ?? '');
    if (in_array($st, ['pending', 'on process', 'shipped'])) {
        $processingOrders[] = $order;
    } elseif (in_array($st, ['delivered', 'completed'])) {
        $completedOrders[] = $order;
    } elseif ($st === 'canceled') {
        $cancelledOrders[] = $order;
    } else {
        $processingOrders[] = $order; // fallback
    }
}

$tabs = [
    'processing' => [
        'id' => 'processing',
        'label' => 'Processing Orders',
        'orders' => $processingOrders,
        'active' => true
    ],
    'completed' => [
        'id' => 'completed',
        'label' => 'Completed / Delivered',
        'orders' => $completedOrders,
        'active' => false
    ],
    'cancelled' => [
        'id' => 'cancelled',
        'label' => 'Cancelled Orders',
        'orders' => $cancelledOrders,
        'active' => false
    ]
];
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
    <link rel="icon" href="assets\images\ApeX Logo.png" type="image/png">
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
            font-size: .9rem;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--danger);
            color: #fff;
            font-size: .6rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 20px;
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

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .status-badge.pending {
            background: rgba(245, 197, 24, .15);
            color: #8a6d00;
        }

        .status-badge.process {
            background: rgba(0, 194, 255, .12);
            color: #007aad;
        }

        .status-badge.shipped {
            background: rgba(11, 47, 168, .1);
            color: var(--blue);
        }

        .status-badge.delivered {
            background: rgba(0, 214, 143, .12);
            color: #00835a;
        }

        .status-badge.canceled {
            background: rgba(255, 59, 92, .1);
            color: var(--danger);
        }

        /* Payment status badges */
        .pay-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 700;
        }

        .pay-badge.success {
            background: rgba(0, 214, 143, .12);
            color: #00835a;
        }

        .pay-badge.pending {
            background: rgba(245, 197, 24, .15);
            color: #8a6d00;
        }

        .pay-badge.failed {
            background: rgba(255, 59, 92, .1);
            color: var(--danger);
        }

        /* Order card */
        .order-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 28px 28px 8px;
            margin-bottom: 24px;
        }

        /* Custom Tabs */
        .custom-tabs {
            border-bottom: 2px solid var(--border);
            margin-bottom: 20px;
        }

        .custom-tabs .nav-link {
            color: var(--text-muted);
            font-weight: 600;
            font-size: .9rem;
            padding: 12px 24px;
            border: none;
            border-bottom: 3px solid transparent;
            border-radius: 0;
            background: transparent;
            transition: all .2s;
        }

        .custom-tabs .nav-link:hover {
            color: var(--blue);
            border-color: rgba(11, 47, 168, .2);
        }

        .custom-tabs .nav-link.active {
            color: var(--blue);
            border-bottom-color: var(--blue);
            background: transparent;
        }

        /* Progress stepper */
        .progress-stepper {
            display: flex;
            align-items: center;
            gap: 0;
            margin: 6px 0 2px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 11px;
            left: 55%;
            width: 90%;
            height: 2px;
            background: var(--border);
            z-index: 0;
        }

        .step:not(:last-child).done::after {
            background: var(--success);
        }

        .step-dot {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid var(--border);
            background: #fff;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .55rem;
            color: var(--text-muted);
        }

        .step.done .step-dot {
            background: var(--success);
            border-color: var(--success);
            color: #fff;
        }

        .step.active .step-dot {
            background: var(--accent);
            border-color: var(--accent);
            color: var(--sidebar-bg);
        }

        .step-lbl {
            font-size: .6rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-top: 4px;
            text-align: center;
            white-space: nowrap;
        }

        .step.done .step-lbl,
        .step.active .step-lbl {
            color: var(--text-main);
        }

        /* Order table */
        .order-table {
            overflow-x: auto;
        }

        .order-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table th {
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

        .order-table td {
            padding: 14px 14px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            font-size: .85rem;
        }

        .order-table tr:last-child td {
            border-bottom: none;
        }

        .order-table tr:hover td {
            background: rgba(0, 194, 255, .03);
        }

        .ref-code {
            font-family: 'Barlow Condensed', monospace;
            font-weight: 800;
            font-size: .95rem;
            color: var(--blue);
            letter-spacing: .04em;
        }

        /* View details button */
        .btn-view {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 11px;
            border-radius: 7px;
            font-size: .75rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid rgba(11, 47, 168, .25);
            color: var(--blue);
            background: rgba(11, 47, 168, .05);
            text-decoration: none;
            transition: all .15s;
        }

        .btn-view:hover {
            background: var(--blue);
            color: #fff;
            border-color: var(--blue);
        }

        /* Actions column */
        .actions-cell {
            min-width: 230px;
            vertical-align: top;
            padding-top: 14px;
            padding-bottom: 14px;
        }

        .action-stack {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .action-buttons-row {
            display: flex;
            gap: 6px;
        }

        .action-buttons-row .btn-view {
            flex: 1;
            justify-content: center;
            padding: 5px 8px;
            white-space: nowrap;
        }

        .btn-view-receipt {
            border-color: rgba(0, 194, 255, .3) !important;
            color: #007aad !important;
            background: rgba(0, 194, 255, .07) !important;
        }

        .btn-view-receipt:hover {
            background: var(--accent) !important;
            color: #fff !important;
            border-color: var(--accent) !important;
        }

        .action-form {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .action-form .form-select {
            flex: 1;
            min-width: 0;
            font-size: .74rem;
            padding: 3px 6px;
            height: auto;
            line-height: 1.3;
        }

        .action-form .btn {
            font-size: .72rem;
            padding: 4px 12px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .action-divider {
            border-top: 1px dashed var(--border);
            margin: 2px 0;
        }

        .action-hint {
            font-size: .68rem;
            color: var(--text-muted);
            font-style: italic;
            display: flex;
            align-items: center;
            gap: 4px;
            line-height: 1.3;
        }

        .action-label {
            font-size: .62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text-muted);
        }

        /* Modal styles */
        .modal-content {
            border: none;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .18);
        }

        .modal-header-custom {
            padding: 18px 24px;
            background: var(--sidebar-bg);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header-custom .modal-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 800;
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .modal-header-custom .btn-close {
            filter: invert(1);
            opacity: .6;
        }

        .modal-body-custom {
            padding: 24px;
            background: #fff;
            max-height: 75vh;
            overflow-y: auto;
        }

        .modal-footer-custom {
            padding: 14px 24px;
            background: #f8fafd;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Detail grid inside modal */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 20px;
            margin-bottom: 18px;
        }

        .detail-item label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--text-muted);
            display: block;
            margin-bottom: 3px;
        }

        .detail-item span {
            font-size: .9rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .section-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 16px 0;
        }

        /* Order items list */
        .items-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .items-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 6px;
            background: #f8fafd;
            font-size: .84rem;
        }

        .items-list li .item-name {
            font-weight: 600;
        }

        .items-list li .item-price {
            color: var(--blue);
            font-weight: 700;
        }

        /* Payment section */
        .payment-box {
            background: #f8fafd;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px;
            margin-top: 12px;
        }

        .payment-box .pay-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: .84rem;
        }

        .payment-box .pay-row:last-child {
            margin-bottom: 0;
        }

        .payment-box .pay-label {
            color: var(--text-muted);
            font-weight: 600;
        }

        .payment-box .pay-value {
            font-weight: 700;
        }

        .screenshot-thumb {
            width: 100%;
            max-width: 260px;
            border-radius: 8px;
            border: 1px solid var(--border);
            margin-top: 10px;
            cursor: pointer;
        }

        .privacy-note {
            font-size: .72rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 8px;
        }

        /* Remarks textarea */
        .remarks-field {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: .85rem;
            font-family: 'Barlow', sans-serif;
            resize: vertical;
            color: var(--text-main);
        }

        .remarks-field:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(11, 47, 168, .1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-wrap {
                margin-left: 0;
            }

            .page-body {
                padding: 18px 16px 40px;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php
    $pendingCount = count(array_filter($orders ?? [], fn($o) => strtolower($o['order_status'] ?? '') === 'on process'));
    $currentAdminPage = 'manage_orders.php';
    include __DIR__ . '/includes/admin_sidebar.php';
    ?>
    <?php if (false): ?><aside class="sidebar">
            <a href="../index.php" class="sidebar-brand">
                <img src="../assets/images/ApeX Logo.png" alt="ApeX Gear">
                <div><span class="t1">ApeX </span><span class="t2">Gear</span></div>
                <span class="sidebar-badge">Admin</span>
            </a>
            <nav class="sidebar-nav">
                <div class="sidebar-section-label">Main</div>
                <a href="apex26admin.php"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="manage_orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders
                    <?php
                    $pendingCount = count(array_filter($orders ?? [], fn($o) => strtolower($o['order_status'] ?? '') === 'on process'));
                    if ($pendingCount > 0) echo '<span class="nav-badge">' . $pendingCount . '</span>';
                    ?>
                </a>
                <a href="manage_products.php"><i class="fas fa-boxes"></i> Manage Products</a>
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
        </aside><?php endif; ?>

    <div class="main-wrap">
        <header class="topbar">
            <span class="topbar-title">Orders</span>
            <span style="font-size:.82rem;color:var(--text-muted);"><?php echo count($orders); ?> total orders</span>
        </header>

        <main class="page-body">

            <?php if ($status_message): ?>
                <div class="alert <?php echo htmlspecialchars($status_class); ?> d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="fas fa-<?php echo strpos($status_class, 'success') !== false ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($status_message); ?>
                </div>
            <?php endif; ?>

            <div class="order-card">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h2 style="font-family:'Barlow Condensed',sans-serif;font-weight:900;font-size:1.6rem;text-transform:uppercase;">Order Management</h2>
                        <p class="text-muted mb-0" style="font-size:.86rem;">Review customer orders, update statuses, and validate payments.</p>
                    </div>
                    <a href="apex26admin.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left me-2"></i>Dashboard</a>
                </div>

                <ul class="nav nav-tabs custom-tabs" id="orderTabs" role="tablist">
                    <?php foreach ($tabs as $tab): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $tab['active'] ? 'active' : ''; ?>" id="<?php echo $tab['id']; ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo $tab['id']; ?>" type="button" role="tab" aria-controls="<?php echo $tab['id']; ?>" aria-selected="<?php echo $tab['active'] ? 'true' : 'false'; ?>">
                                <?php echo htmlspecialchars($tab['label']); ?>
                                <span class="badge bg-secondary ms-2 rounded-pill" style="font-size: 0.7em;"><?php echo count($tab['orders']); ?></span>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content" id="orderTabsContent">
                    <?php foreach ($tabs as $tab): ?>
                        <div class="tab-pane fade <?php echo $tab['active'] ? 'show active' : ''; ?>" id="<?php echo $tab['id']; ?>" role="tabpanel" aria-labelledby="<?php echo $tab['id']; ?>-tab">
                            <?php if (empty($tab['orders'])): ?>
                                <div class="alert alert-warning">No orders found in this category.</div>
                            <?php else: ?>
                                <div class="order-table">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Reference</th>
                                                <th>Customer</th>
                                                <th>Items</th>
                                                <th>Promo</th>
                                                <th>Amount</th>
                                                <th>Payment</th>
                                                <th>Pay Status</th>
                                                <th>Progress</th>
                                                <th>Order Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tab['orders'] as $order):
                                                $st = strtolower($order['order_status'] ?? '');
                                                $statusClass = match ($st) {
                                                    'completed'  => 'delivered',
                                                    'delivered'  => 'delivered',
                                                    'canceled'   => 'canceled',
                                                    'shipped'    => 'shipped',
                                                    'on process' => 'process',
                                                    default      => 'pending',
                                                };

                                                // Determine progress steps
                                                $steps      = ['On Process', 'Shipped', 'Delivered'];
                                                $stepIndex  = match ($st) {
                                                    'on process' => 0,
                                                    'shipped'    => 1,
                                                    'delivered', 'completed' => 2,
                                                    default      => -1,
                                                };

                                                // Payment status
                                                $payStatus = strtolower($order['payment_status'] ?? 'pending');
                                                $payClass  = match ($payStatus) {
                                                    'success', 'paid', 'completed' => 'success',
                                                    'failed', 'rejected'           => 'failed',
                                                    default                         => 'pending',
                                                };
                                                $payLabel  = match (true) {
                                                    $payClass === 'success'        => 'Paid',
                                                    $payStatus === 'rejected'       => 'Rejected',
                                                    $payClass === 'failed'          => 'Failed',
                                                    default                          => 'Pending',
                                                };

                                                // Payment method display (no card details)
                                                $payMethod = $order['payment_method'] ?? 'N/A';
                                                $isCard    = stripos($payMethod, 'card') !== false || stripos($payMethod, 'credit') !== false || stripos($payMethod, 'debit') !== false;
                                                $isCOD     = stripos($payMethod, 'cash on delivery') !== false || stripos($payMethod, 'cod') !== false;

                                                // Uploaded payment receipt (GCash / Maya / PayPal QR payments)
                                                $receiptRaw = $order['qr_screenshot_path'] ?? $order['payment_screenshot'] ?? null;
                                                $receiptUrl = null;
                                                if (!empty($receiptRaw)) {
                                                    $receiptUrl = preg_match('#^(https?://|/|\.\./)#i', $receiptRaw) ? $receiptRaw : '../' . $receiptRaw;
                                                }
                                                $hasReceipt = !$isCard && !empty($receiptUrl);
                                                // Normalize for the JS modal too
                                                $order['payment_screenshot'] = $receiptUrl;

                                                // Fulfillment status updates require an approved payment (COD is exempt)
                                                $fulfillmentLocked = !$isCOD && $payStatus !== 'paid';

                                                $customerName = trim((string)($order['display_customer_name'] ?? $order['customer_name'] ?? $order['username'] ?? ''));
                                                if ($customerName === '') {
                                                    $customerName = 'Guest';
                                                }
                                            ?>
                                                <tr>
                                                    <td><span class="ref-code"><?php echo htmlspecialchars($order['reference_number'] ?? '—'); ?></span></td>
                                                    <td>
                                                        <div style="font-weight:600;"><?php echo htmlspecialchars($customerName); ?></div>
                                                        <div style="font-size:.75rem;color:var(--text-muted);"><?php echo htmlspecialchars($order['email'] ?: '—'); ?></div>
                                                    </td>
                                                    <td style="text-align:center;"><?php echo intval($order['items_count'] ?? 0); ?></td>
                                                    <td>
                                                        <?php if (!empty($order['coupon_code'])): ?>
                                                            <span class="ref-code" style="font-size:.72rem;"><?php echo htmlspecialchars($order['coupon_code']); ?></span>
                                                            <div style="font-size:.72rem;color:var(--success);font-weight:700;">&minus;₱<?php echo number_format($order['discount_amount'] ?? 0, 2); ?></div>
                                                        <?php else: ?>
                                                            <span style="color:var(--text-muted);font-size:.78rem;">&mdash;</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="font-weight:700;color:var(--blue);">₱<?php echo number_format($order['total_amount'] ?? 0, 2); ?></td>
                                                    <td><?php echo htmlspecialchars($payMethod); ?></td>
                                                    <td><span class="pay-badge <?php echo $payClass; ?>"><i class="fas fa-circle" style="font-size:.45rem;"></i><?php echo $payLabel; ?></span></td>
                                                    <td style="min-width:160px;">
                                                        <?php if ($st === 'canceled'): ?>
                                                            <span style="font-size:.75rem;color:var(--danger);font-weight:600;"><i class="fas fa-times-circle me-1"></i>Canceled</span>
                                                        <?php else: ?>
                                                            <div class="progress-stepper">
                                                                <?php foreach ($steps as $i => $stepLabel):
                                                                    $cls = '';
                                                                    if ($stepIndex > $i) $cls = 'done';
                                                                    elseif ($stepIndex === $i) $cls = 'active';
                                                                ?>
                                                                    <div class="step <?php echo $cls; ?> <?php echo ($i < count($steps) - 1 && $stepIndex > $i) ? 'done' : ''; ?>">
                                                                        <div class="step-dot"><i class="fas fa-check" style="font-size:.55rem;"></i></div>
                                                                        <div class="step-lbl"><?php echo $stepLabel === 'On Process' ? 'Processing' : $stepLabel; ?></div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($order['order_status'] ?? '—'); ?></span></td>
                                                    <td style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;"><?php echo date('M d, Y', strtotime($order['created_at'] ?? 'now')); ?></td>
                                                    <td class="actions-cell">
                                                        <div class="action-stack">
                                                            <div class="action-buttons-row">
                                                                <!-- View Details -->
                                                                <button type="button" class="btn-view"
                                                                    onclick="openOrderModal(<?php echo htmlspecialchars(json_encode($order)); ?>)">
                                                                    <i class="fas fa-eye"></i> View
                                                                </button>
                                                                <!-- View uploaded payment receipt -->
                                                                <?php if ($hasReceipt): ?>
                                                                    <a href="<?php echo htmlspecialchars($receiptUrl); ?>" target="_blank" rel="noopener" class="btn-view btn-view-receipt">
                                                                        <i class="fas fa-receipt"></i> Receipt
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>

                                                            <?php if (!$isCOD): ?>
                                                                <div class="action-divider"></div>
                                                                <div class="action-label">Payment</div>
                                                                <!-- Approve / Reject payment -->
                                                                <form method="POST" class="action-form">
                                                                    <input type="hidden" name="order_id" value="<?php echo intval($order['order_id']); ?>">
                                                                    <select name="payment_status" class="form-select form-select-sm">
                                                                        <option value="Pending" <?php echo $payStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                        <option value="Paid" <?php echo $payStatus === 'paid' ? 'selected' : ''; ?>>Approve</option>
                                                                        <option value="Rejected" <?php echo $payStatus === 'rejected' ? 'selected' : ''; ?>>Reject</option>
                                                                    </select>
                                                                    <button type="submit" name="update_payment_status" class="btn btn-outline-primary btn-sm">Set</button>
                                                                </form>
                                                            <?php endif; ?>

                                                            <div class="action-divider"></div>
                                                            <div class="action-label">Order Status</div>
                                                            <!-- Quick status update -->
                                                            <form method="POST" class="action-form">
                                                                <input type="hidden" name="order_id" value="<?php echo intval($order['order_id']); ?>">
                                                                <select name="order_status" class="form-select form-select-sm" <?php echo $fulfillmentLocked ? 'disabled' : ''; ?>>
                                                                    <?php foreach (['On Process', 'Shipped', 'Delivered', 'Completed', 'Canceled'] as $s): ?>
                                                                        <option value="<?php echo $s; ?>" <?php echo $order['order_status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                                <button type="submit" name="update_status" class="btn btn-primary btn-sm" <?php echo $fulfillmentLocked ? 'disabled' : ''; ?>>Update</button>
                                                            </form>
                                                            <?php if ($fulfillmentLocked): ?>
                                                                <div class="action-hint"><i class="fas fa-lock"></i>Approve payment to unlock</div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </main>
    </div>

    <!-- ══ ORDER DETAIL MODAL ══ -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header-custom">
                    <span class="modal-title"><i class="fas fa-receipt me-2"></i>Order Details</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body-custom" id="orderDetailBody">
                    <!-- Populated by JS -->
                </div>
                <div class="modal-footer-custom" style="flex-wrap: wrap;">
                    <div id="modal_lock_hint" class="action-hint" style="display:none; width:100%; margin-bottom:2px;">
                        <i class="fas fa-lock"></i> Approve this order's payment before updating its fulfillment status.
                    </div>
                    <!-- Dynamic update form -->
                    <form method="POST" id="modalUpdateForm" class="d-flex gap-2 align-items-center w-100">
                        <input type="hidden" name="order_id" id="modal_order_id">
                        <label style="font-size:.8rem;font-weight:600;color:var(--text-muted);white-space:nowrap;">Status:</label>
                        <select name="order_status" id="modal_order_status" class="form-select form-select-sm" style="width:140px;">
                            <option value="On Process">On Process</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Completed">Completed</option>
                            <option value="Canceled">Canceled</option>
                        </select>
                        <label style="font-size:.8rem;font-weight:600;color:var(--text-muted);white-space:nowrap;">Remarks:</label>
                        <input type="text" name="remarks" id="modal_remarks" class="form-control form-control-sm" placeholder="Optional note..." style="flex:1;">
                        <button type="submit" name="update_status" class="btn btn-primary btn-sm px-3">Save</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openOrderModal(order) {
            // Populate hidden form fields
            document.getElementById('modal_order_id').value = order.order_id;
            document.getElementById('modal_order_status').value = order.order_status || 'On Process';
            document.getElementById('modal_remarks').value = order.remarks || '';

            const payMethod = order.payment_method || 'N/A';
            const isCard = /card|credit|debit/i.test(payMethod);
            const isCOD = /cash on delivery|\bcod\b/i.test(payMethod);
            const payStatus = (order.payment_status || 'pending').toLowerCase();
            const payClass = ['success', 'paid', 'completed'].includes(payStatus) ? 'success' : (['failed', 'rejected'].includes(payStatus) ? 'failed' : 'pending');
            const payLabel = payClass === 'success' ? 'Paid' : (payStatus === 'rejected' ? 'Rejected' : (payClass === 'failed' ? 'Failed' : 'Pending'));
            const customerName = order.display_customer_name || order.customer_name || order.username || 'Guest';

            // Lock fulfillment status updates until the payment receipt is approved (COD is exempt)
            const fulfillmentLocked = !isCOD && payStatus !== 'paid';
            const modalStatusSelect = document.getElementById('modal_order_status');
            const modalSaveBtn = document.querySelector('#modalUpdateForm button[name="update_status"]');
            const modalLockHint = document.getElementById('modal_lock_hint');
            modalStatusSelect.disabled = fulfillmentLocked;
            modalSaveBtn.disabled = fulfillmentLocked;
            modalLockHint.style.display = fulfillmentLocked ? 'flex' : 'none';

            const streetAddress = order.street_address || '';
            const city = order.city || '';
            const postalCode = order.zip_code || '';
            const phone = order.phone_number || '';
            const hasShippingAddress = streetAddress || city || postalCode || phone;
            const shippingHtml = hasShippingAddress ? `
    <hr class="section-divider">
    <div style="font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">Shipping Address</div>
    <div class="detail-grid" style="margin-bottom:6px;">
        <div class="detail-item">
            <label>Street Address</label>
            <span>${escHtml(streetAddress || 'N/A')}</span>
        </div>
        <div class="detail-item">
            <label>City</label>
            <span>${escHtml(city || 'N/A')}</span>
        </div>
        <div class="detail-item">
            <label>Postal Code</label>
            <span>${escHtml(postalCode || 'N/A')}</span>
        </div>
        <div class="detail-item">
            <label>Phone Number</label>
            <span>${escHtml(phone || 'N/A')}</span>
        </div>
    </div>` : `
    <hr class="section-divider">
    <div style="font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">Shipping Address</div>
    <p style="color:var(--text-muted);font-size:.85rem;margin-bottom:6px;"><em>No shipping address recorded for this order.</em></p>`;

            // Build items HTML
            let itemsHtml = '<p style="color:var(--text-muted);font-size:.82rem;"><em>No item details stored.</em></p>';
            if (order.items && Array.isArray(order.items) && order.items.length) {
                itemsHtml = '<ul class="items-list">' + order.items.map(it =>
                    `<li><span class="item-name">${escHtml(it.name || 'Item')} <span style="color:var(--text-muted);font-weight:400;">x${it.qty || 1}</span></span>
             <span class="item-price">₱${parseFloat(it.price || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span></li>`
                ).join('') + '</ul>';
            }

            // Screenshot section (GCash/Maya/PayPal only)
            let screenshotHtml = '';
            if (!isCard && order.payment_screenshot) {
                screenshotHtml = `
            <div style="margin-top:10px;">
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin-bottom:6px;">Payment Screenshot</div>
                <img src="${escHtml(order.payment_screenshot)}" class="screenshot-thumb" alt="Payment Screenshot"
                    onclick="window.open(this.src,'_blank')" title="Click to view full size">
            </div>`;
            } else if (isCard) {
                screenshotHtml = `
            <div class="privacy-note mt-2">
                <i class="fas fa-lock"></i>
                Card payment — sensitive card details are not stored or visible here.
            </div>`;
            }

            const body = `
    <div class="detail-grid">
        <div class="detail-item">
            <label>Reference #</label>
            <span style="font-family:'Barlow Condensed',monospace;color:var(--blue);font-size:1rem;">${escHtml(order.reference_number || '—')}</span>
        </div>
        <div class="detail-item">
            <label>Order Date</label>
            <span>${order.created_at ? new Date(order.created_at).toLocaleDateString('en-PH',{year:'numeric',month:'long',day:'numeric'}) : '—'}</span>
        </div>
        <div class="detail-item">
            <label>Customer</label>
            <span>${escHtml(customerName)}</span>
        </div>
        <div class="detail-item">
            <label>Email</label>
            <span>${escHtml(order.email || '—')}</span>
        </div>
        <div class="detail-item">
            <label>Order Status</label>
            <span>${statusBadgeHtml(order.order_status)}</span>
        </div>
        <div class="detail-item">
            <label>Subtotal</label>
            <span>₱${parseFloat(order.subtotal||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</span>
        </div>
        ${order.coupon_code ? `
        <div class="detail-item">
            <label>Promo Code</label>
            <span style="font-family:'Barlow Condensed',monospace;">${escHtml(order.coupon_code)}</span>
        </div>
        <div class="detail-item">
            <label>Discount</label>
            <span style="color:var(--success);">&minus;₱${parseFloat(order.discount_amount||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</span>
        </div>` : ''}
        <div class="detail-item">
            <label>Total Amount</label>
            <span style="color:var(--blue);font-size:1rem;">₱${parseFloat(order.total_amount||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</span>
        </div>
    </div>

    ${order.remarks ? `<div style="background:#f8fafd;border-left:3px solid var(--accent);padding:10px 14px;border-radius:0 8px 8px 0;margin-bottom:16px;font-size:.85rem;"><strong>Remarks:</strong> ${escHtml(order.remarks)}</div>` : ''}

    ${shippingHtml}

    <hr class="section-divider">
    <div style="font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Order Items</div>
    ${itemsHtml}

    <hr class="section-divider">
    <div style="font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">Payment Information</div>
    <div class="payment-box">
        <div class="pay-row">
            <span class="pay-label">Payment Method</span>
            <span class="pay-value">${escHtml(payMethod)}</span>
        </div>
        <div class="pay-row">
            <span class="pay-label">Payment Status</span>
            <span class="pay-badge ${payClass}"><i class="fas fa-circle" style="font-size:.45rem;"></i> ${payLabel}</span>
        </div>
        ${order.transaction_id ? `<div class="pay-row"><span class="pay-label">Transaction / Ref ID</span><span class="pay-value" style="font-family:monospace;">${escHtml(order.transaction_id)}</span></div>` : ''}
        ${order.payment_date ? `<div class="pay-row"><span class="pay-label">Payment Date</span><span class="pay-value">${new Date(order.payment_date).toLocaleString('en-PH')}</span></div>` : ''}
        ${screenshotHtml}
    </div>`;

            document.getElementById('orderDetailBody').innerHTML = body;
            new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
        }

        function escHtml(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function statusBadgeHtml(status) {
            const s = (status || '').toLowerCase();
            const cls = (s === 'delivered' || s === 'completed') ? 'delivered' : s === 'canceled' ? 'canceled' : s === 'shipped' ? 'shipped' : s === 'on process' ? 'process' : 'pending';
            return `<span class="status-badge ${cls}">${escHtml(status || 'Pending')}</span>`;
        }
    </script>
</body>

</html>