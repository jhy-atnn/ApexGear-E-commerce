<?php
session_start();

require_once __DIR__ . '/../database/db_connect.php';

$db = new Database();
$conn = $db->getConnection();

if (!isset($_GET['order_id'])) {
    die("Error: No order ID specified.");
}

$order_id = (int)$_GET['order_id'];

// ── 1. Fetch order + shipping address ──────────────────────────────────────────
$query = "
    SELECT
        o.order_id,
        o.order_ref_code,
        o.coupon_code,
        o.discount_amount,
        o.subtotal,
        o.tax,
        o.shipping_fee,
        o.total_amount,
        o.order_status,
        o.created_at,
        s.first_name,
        s.last_name,
        s.phone_number,
        u.email,
        s.street_address,
        s.city,
        s.zip_code
    FROM orders_tbl o
    LEFT JOIN shipping_address_tbl s ON s.order_ref_code = o.order_ref_code
    LEFT JOIN users_tbl u ON o.user_id = u.user_id
    WHERE o.order_id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    die("Error: Order record not found.");
}

// ── 2. Fetch payment method ────────────────────────────────────────────────────
$pay_query = "
    SELECT method, status, card_last_four, transaction_id
    FROM payments_tbl
    WHERE order_id = ?
    LIMIT 1
";
$pay_stmt = mysqli_prepare($conn, $pay_query);
mysqli_stmt_bind_param($pay_stmt, "i", $order_id);
mysqli_stmt_execute($pay_stmt);
$payment = mysqli_fetch_assoc(mysqli_stmt_get_result($pay_stmt));

// ── 3. Fetch order items (JOIN products for name) ─────────────────────────────
$items_query = "
    SELECT
        p.name   AS item_name,
        oi.quantity,
        oi.price_at_checkout AS price,
        (oi.quantity * oi.price_at_checkout) AS total_price
    FROM order_items_tbl oi
    JOIN products_tbl p ON p.product_id = oi.product_id
    WHERE oi.order_id = ?
";
$items_stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($items_stmt, "i", $order_id);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);

$order_items       = [];
$total_items_count = 0;
while ($row = mysqli_fetch_assoc($items_result)) {
    $order_items[]      = $row;
    $total_items_count += $row['quantity'];
}

// ── 4. Coupon info ─────────────────────────────────────────────────────────────
$coupon_label    = 'N/A';
$coupon_discount = 0;
if (!empty($order['coupon_code'])) {
    $coupon_label    = htmlspecialchars($order['coupon_code']);
    $coupon_discount = floatval($order['discount_amount']);
}

// ── 5. Derived display values ──────────────────────────────────────────────────
$fullname        = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
$formatted_date  = date('F d, Y \a\t h:i A', strtotime($order['created_at']));
$payment_method  = $payment['method'] ?? 'N/A';
$payment_detail  = '';
if (!empty($payment['card_last_four'])) {
    $payment_detail = '****' . $payment['card_last_four'];
} elseif (!empty($payment['transaction_id'])) {
    $payment_detail = $payment['transaction_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt | <?php echo htmlspecialchars($order['order_ref_code']); ?></title>
    <style>
        /* ── Reset & base ─────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Courier New', Courier, monospace;
            background: #e8e8e8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 16px 60px;
            color: #1a1a1a;
        }

        /* ── Action bar (hidden on print) ────────────── */
        .action-bar {
            width: 100%;
            max-width: 420px;
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 24px;
        }

        .btn-print {
            flex: 1;
            background: #0b1c3f;
            color: #fff;
            border: none;
            padding: 11px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            transition: background .2s;
        }
        .btn-print:hover { background: #1a3a6b; }

        .btn-back {
            flex: 1;
            background: transparent;
            color: #0b1c3f;
            border: 2px solid #0b1c3f;
            padding: 11px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .5px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            transition: all .2s;
        }
        .btn-back:hover { background: #0b1c3f; color: #fff; }

        /* ── Receipt paper ────────────────────────────── */
        .receipt {
            background: #fff;
            width: 100%;
            max-width: 420px;
            padding: 0;
            /* Torn-paper top & bottom */
            position: relative;
            filter: drop-shadow(0 4px 24px rgba(0,0,0,.18));
        }

        /* Jagged top edge */
        .receipt::before {
            content: '';
            display: block;
            width: 100%;
            height: 18px;
            background:
                radial-gradient(circle at 10px -4px, transparent 8px, #fff 8px) 0 0 / 20px 18px,
                radial-gradient(circle at 10px -4px, #e8e8e8 8px, transparent 8px) 0 0 / 20px 18px;
        }
        /* Jagged bottom edge */
        .receipt::after {
            content: '';
            display: block;
            width: 100%;
            height: 18px;
            background:
                radial-gradient(circle at 10px 22px, transparent 8px, #fff 8px) 0 0 / 20px 18px,
                radial-gradient(circle at 10px 22px, #e8e8e8 8px, transparent 8px) 0 0 / 20px 18px;
        }

        .receipt-inner {
            padding: 8px 28px 20px;
        }

        /* ── Store header ─────────────────────────────── */
        .store-header {
            text-align: center;
            padding: 14px 0 18px;
            border-bottom: 1px dashed #bbb;
            margin-bottom: 16px;
        }

        .store-name {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 3px;
            color: #0b1c3f;
            text-transform: uppercase;
        }
        .store-name span { color: #00a8e8; }

        .store-tagline {
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #888;
            margin-top: 3px;
        }

        /* ── Success badge ────────────────────────────── */
        .success-badge {
            text-align: center;
            margin: 16px 0 18px;
        }

        .check-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #00d27a;
            color: #fff;
            font-size: 22px;
            margin-bottom: 8px;
        }

        .success-badge h2 {
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #0b1c3f;
            font-weight: 900;
        }

        .order-ref {
            font-size: 13px;
            font-weight: 700;
            color: #0b1c3f;
            letter-spacing: 1.5px;
            margin-top: 6px;
            word-break: break-all;
        }

        .order-date {
            font-size: 10px;
            color: #888;
            margin-top: 3px;
        }

        /* ── Dashed divider ───────────────────────────── */
        .divider {
            border: none;
            border-top: 1px dashed #bbb;
            margin: 14px 0;
        }

        /* ── Section label ────────────────────────────── */
        .section-lbl {
            font-size: 9.5px;
            font-weight: 900;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 8px;
        }

        /* ── Key-value row ────────────────────────────── */
        .kv {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 5px;
            gap: 8px;
        }
        .kv .k { color: #555; flex-shrink: 0; }
        .kv .v {
            text-align: right;
            color: #1a1a1a;
            font-weight: 600;
            word-break: break-all;
        }
        .kv .v.muted { color: #888; font-weight: 400; }

        /* ── Items table ──────────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 4px;
        }
        .items-table th {
            font-size: 9.5px;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #888;
            padding: 0 0 7px 0;
            border-bottom: 1px dashed #bbb;
            white-space: nowrap;
        }
        .items-table th:last-child,
        .items-table td:last-child { text-align: right; }
        .items-table th:nth-child(2),
        .items-table td:nth-child(2) { text-align: center; }
        .items-table td {
            padding: 8px 0 2px;
            vertical-align: top;
            color: #1a1a1a;
        }
        .items-table td.name { font-weight: 600; max-width: 170px; }
        .items-table td.price { white-space: nowrap; }
        .items-table tr:last-child td { padding-bottom: 6px; }

        /* ── Totals block ─────────────────────────────── */
        .totals { margin-top: 4px; }
        .totals .row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .totals .row .lbl { color: #555; }
        .totals .row .amt { font-weight: 600; }
        .totals .row.discount .amt { color: #00a044; }
        .totals .row.shipping .amt { color: #00a044; }
        .totals .grand {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #1a1a1a;
        }
        .totals .grand .lbl {
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .totals .grand .amt {
            font-size: 20px;
            font-weight: 900;
            color: #0b1c3f;
        }

        /* ── Status banner ────────────────────────────── */
        .status-banner {
            background: #efffF6;
            border: 1px dashed #00d27a;
            border-radius: 4px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            margin-top: 14px;
        }
        .status-dot {
            width: 9px; height: 9px;
            border-radius: 50%;
            background: #00d27a;
            flex-shrink: 0;
        }
        .status-banner strong { color: #0b7a44; }

        /* ── Footer note ──────────────────────────────── */
        .receipt-footer {
            text-align: center;
            margin-top: 22px;
            padding-top: 14px;
            border-top: 1px dashed #bbb;
        }
        .receipt-footer p {
            font-size: 9.5px;
            color: #aaa;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            line-height: 1.8;
        }
        .receipt-footer .barcode {
            font-size: 32px;
            letter-spacing: -2px;
            color: #1a1a1a;
            line-height: 1;
            margin: 10px 0 6px;
        }

        /* ── PRINT STYLES ─────────────────────────────── */
        @media print {
            @page { margin: 0; size: 80mm auto; }
            body {
                background: #fff;
                padding: 0;
            }
            .action-bar { display: none !important; }
            .receipt {
                filter: none;
                max-width: 100%;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <a class="btn-back" href="../index.php">
            &#8592; Go Back
        </a>
        <button class="btn-print" onclick="window.print()">
            &#128438; Print Receipt
        </button>
    </div>

    <div class="receipt">
        <div class="receipt-inner">

            <!-- Store header -->
            <div class="store-header">
                <div class="store-name">ApeX<span>Gear</span></div>
                <div class="store-tagline">High-Performance Tech Store</div>
            </div>

            <!-- Success badge -->
            <div class="success-badge">
                <div class="check-circle">&#10003;</div>
                <h2>Payment Successful</h2>
                <div class="order-ref"><?php echo htmlspecialchars($order['order_ref_code']); ?></div>
                <div class="order-date"><?php echo htmlspecialchars($formatted_date); ?></div>
            </div>

            <hr class="divider">

            <!-- Shipping details -->
            <div class="section-lbl">Shipping Details</div>
            <div class="kv"><span class="k">Name</span><span class="v"><?php echo htmlspecialchars($fullname ?: 'N/A'); ?></span></div>
            <div class="kv"><span class="k">Address</span><span class="v"><?php echo htmlspecialchars(($order['street_address'] ?? '') . ', ' . ($order['city'] ?? '') . ' ' . ($order['zip_code'] ?? '')); ?></span></div>
            <div class="kv"><span class="k">Phone</span><span class="v"><?php echo htmlspecialchars($order['phone_number'] ?? 'N/A'); ?></span></div>
            <div class="kv"><span class="k">Email</span><span class="v"><?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></span></div>

            <hr class="divider">

            <!-- Payment method -->
            <div class="section-lbl">Payment Method</div>
            <div class="kv">
                <span class="k">Method</span>
                <span class="v"><?php echo htmlspecialchars($payment_method); ?></span>
            </div>
            <?php if ($payment_detail): ?>
            <div class="kv">
                <span class="k">Reference</span>
                <span class="v muted"><?php echo htmlspecialchars($payment_detail); ?></span>
            </div>
            <?php endif; ?>

            <hr class="divider">

            <!-- Coupon -->
            <div class="section-lbl">Promo / Coupon</div>
            <div class="kv">
                <span class="k">Coupon Code</span>
                <span class="v <?php echo $coupon_label === 'N/A' ? 'muted' : ''; ?>"><?php echo $coupon_label; ?></span>
            </div>
            <?php if ($coupon_discount > 0): ?>
            <div class="kv">
                <span class="k">Discount</span>
                <span class="v" style="color:#00a044;">-&#8369;<?php echo number_format($coupon_discount, 2); ?></span>
            </div>
            <?php endif; ?>

            <hr class="divider">

            <!-- Order items -->
            <div class="section-lbl">
                Order Items &mdash; <?php echo $total_items_count; ?> <?php echo $total_items_count === 1 ? 'item' : 'items'; ?>
            </div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td class="name"><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td class="center"><?php echo (int)$item['quantity']; ?></td>
                        <td class="price">&#8369;<?php echo number_format($item['price'], 2); ?></td>
                        <td class="price">&#8369;<?php echo number_format($item['total_price'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <hr class="divider">

            <!-- Totals -->
            <div class="totals">
                <div class="row">
                    <span class="lbl">Subtotal</span>
                    <span class="amt">&#8369;<?php echo number_format($order['subtotal'], 2); ?></span>
                </div>
                <?php if ($coupon_discount > 0): ?>
                <div class="row discount">
                    <span class="lbl">Discount (<?php echo $coupon_label; ?>)</span>
                    <span class="amt">-&#8369;<?php echo number_format($coupon_discount, 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="row">
                    <span class="lbl">Tax (8%)</span>
                    <span class="amt">&#8369;<?php echo number_format($order['tax'], 2); ?></span>
                </div>
                <div class="row shipping">
                    <span class="lbl">Shipping</span>
                    <span class="amt"><?php echo ($order['shipping_fee'] == 0) ? 'FREE' : '&#8369;' . number_format($order['shipping_fee'], 2); ?></span>
                </div>
                <div class="grand">
                    <span class="lbl">Total</span>
                    <span class="amt">&#8369;<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>

            <!-- Status -->
            <div class="status-banner">
                <div class="status-dot"></div>
                <div>
                    <strong>Order Status:</strong>
                    <?php echo htmlspecialchars($order['order_status']); ?>
                </div>
            </div>

            <!-- Footer -->
            <div class="receipt-footer">
                <div class="barcode">
                    ||| || ||| || || ||| | || |||
                </div>
                <p>
                    <?php echo htmlspecialchars($order['order_ref_code']); ?><br>
                    Thank you for shopping with ApeX Gear!<br>
                    Keep this receipt for your records.
                </p>
            </div>

        </div>
    </div>

</body>
</html>