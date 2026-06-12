<?php
session_start();

// Ensure user/admin is logged in if required for your system
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_GET['order_id'])) {
    die("Error: No order ID specified.");
}

$order_id = (int)$_GET['order_id'];

// Fetch the main order and customer details
$query = "
    SELECT o.order_number, o.created_at, o.payment_method, 
           o.subtotal, o.tax_amount, o.shipping_fee, o.total_amount, o.status,
           c.first_name, c.last_name, c.address_line1, c.city, c.zip_code, 
           c.phone_number, c.email
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    WHERE o.order_id = ? LIMIT 1
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    die("Error: Order record not found.");
}

// Fetch the items for this specific order
$items_query = "
    SELECT item_name, quantity, price, total_price 
    FROM order_items 
    WHERE order_id = ?
";
$items_stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($items_stmt, "i", $order_id);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);

$order_items = [];
$total_items_count = 0;
while ($row = mysqli_fetch_assoc($items_result)) {
    $order_items[] = $row;
    $total_items_count += $row['quantity'];
}

$fullname = $order['first_name'] . ' ' . $order['last_name'];
$formatted_date = date('F d, Y \a\t h:i A', strtotime($order['created_at']));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt | <?php echo htmlspecialchars($order['order_number']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0b1c3f;
            --accent-green: #00d27a;
            --light-green-bg: #dcf3e8;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
            --bg-body: #f8f9fa;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-body);
            color: #333;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Top Success Message */
        .success-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .success-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background-color: var(--accent-green);
            color: white;
            border-radius: 50%;
            font-size: 28px;
            margin-bottom: 15px;
        }

        .success-header h1 {
            color: var(--primary-blue);
            font-size: 24px;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .success-header p {
            color: var(--text-muted);
            margin: 0;
            font-size: 15px;
        }

        /* Receipt Card */
        .receipt-card {
            background: #fff;
            width: 100%;
            max-width: 650px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            overflow: hidden;
            padding: 40px;
            box-sizing: border-box;
        }

        /* Receipt Header */
        .receipt-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .receipt-header h2 {
            color: var(--primary-blue);
            font-size: 20px;
            margin: 0 0 5px 0;
        }

        .receipt-header .sub-text {
            color: var(--text-muted);
            font-size: 13px;
            margin: 0 0 15px 0;
        }

        .receipt-header .order-number {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary-blue);
            margin: 0 0 5px 0;
            letter-spacing: 1px;
        }

        .receipt-header .order-date {
            color: var(--text-muted);
            font-size: 13px;
            margin: 0;
        }

        /* Section Titles */
        .section-title {
            color: var(--primary-blue);
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 25px 0 15px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Shipping Details */
        .shipping-details {
            margin-bottom: 30px;
        }
        
        .shipping-details strong {
            display: block;
            color: #333;
            margin-bottom: 5px;
            font-size: 15px;
        }

        .shipping-details p {
            margin: 0 0 4px 0;
            color: var(--text-muted);
            font-size: 14px;
        }
        
        .shipping-details .contact-info {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Payment Method */
        .payment-method p {
            margin: 0;
            font-weight: 600;
            font-size: 15px;
            color: #333;
        }

        /* Order Items Table */
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .order-items-table th {
            text-align: left;
            padding: 10px 0;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 13px;
            border-bottom: 1px solid var(--border-color);
        }

        .order-items-table th.right, 
        .order-items-table td.right {
            text-align: right;
        }
        
        .order-items-table th.center, 
        .order-items-table td.center {
            text-align: center;
        }

        .order-items-table td {
            padding: 15px 0;
            font-size: 14px;
            color: #333;
            border-bottom: 1px solid #f1f3f5;
        }

        .order-items-table td.item-name {
            color: var(--text-muted);
        }

        .order-items-table td.total-price {
            font-weight: 600;
            color: #333;
        }

        .items-footer {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        /* Totals Section */
        .totals-section {
            width: 100%;
            margin-left: auto;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .totals-row.shipping .amount {
            color: var(--accent-green);
            font-weight: 600;
        }

        .totals-row .amount {
            color: #333;
        }

        /* Grand Total */
        .grand-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .grand-total .label {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .grand-total .amount {
            font-size: 22px;
            font-weight: 700;
            color: #333;
        }

        /* Status Banner */
        .status-banner {
            background-color: var(--light-green-bg);
            border-radius: 6px;
            padding: 15px 20px;
            margin-top: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-banner i {
            color: #0b7a44;
            font-size: 18px;
        }

        .status-banner span {
            color: #0b7a44;
            font-size: 14px;
        }
        
        .status-banner strong {
            font-weight: 600;
        }

        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .receipt-card {
                box-shadow: none;
                border: none;
                padding: 20px;
            }
            .success-header {
                display: none; /* Usually hide the success message when physically printing */
            }
        }
    </style>
</head>
<body>

    <div class="success-header">
        <div class="success-icon">
            <i class="bi bi-check"></i>
        </div>
        <h1>PAYMENT SUCCESSFUL</h1>
        <p>Thank you for your order! Your high-performance gear is being prepped for shipment.</p>
    </div>

    <div class="receipt-card">
        
        <div class="receipt-header">
            <h2>ONLINE RECEIPT</h2>
            <p class="sub-text">Payment Completed Successfully<br>Order Number</p>
            <p class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></p>
            <p class="order-date"><?php echo htmlspecialchars($formatted_date); ?></p>
        </div>

        <div class="section-title">
            <i class="bi bi-geo-alt-fill"></i> SHIPPING DETAILS
        </div>
        <div class="shipping-details">
            <strong><?php echo htmlspecialchars($fullname); ?></strong>
            <p><?php echo htmlspecialchars($order['address_line1']); ?></p>
            <p><?php echo htmlspecialchars($order['city'] . ', ' . $order['zip_code']); ?></p>
            <div class="contact-info">
                <i class="bi bi-telephone-fill" style="color: var(--primary-blue);"></i> <?php echo htmlspecialchars($order['phone_number']); ?>
            </div>
            <div class="contact-info">
                <i class="bi bi-envelope-fill" style="color: var(--primary-blue);"></i> <?php echo htmlspecialchars($order['email']); ?>
            </div>
        </div>

        <div class="section-title">
            <i class="bi bi-credit-card-2-front-fill"></i> PAYMENT METHOD
        </div>
        <div class="payment-method">
            <p><?php echo htmlspecialchars($order['payment_method']); ?></p>
        </div>

        <div class="section-title">
            <i class="bi bi-bag-fill"></i> ORDER ITEMS (<?php echo count($order_items); ?> ITEM<?php echo count($order_items) > 1 ? 'S' : ''; ?>)
        </div>
        <table class="order-items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="center">Qty</th>
                    <th class="right">Price</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                <tr>
                    <td class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td class="center"><?php echo (int)$item['quantity']; ?></td>
                    <td class="right">₱<?php echo number_format($item['price'], 2); ?></td>
                    <td class="right total-price">₱<?php echo number_format($item['total_price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="items-footer">
            Total Quantity: <strong><?php echo $total_items_count; ?></strong> items
        </div>

        <div class="totals-section">
            <div class="totals-row">
                <span>Subtotal</span>
                <span class="amount">₱<?php echo number_format($order['subtotal'], 2); ?></span>
            </div>
            <div class="totals-row">
                <span>Tax (8%)</span>
                <span class="amount">₱<?php echo number_format($order['tax_amount'], 2); ?></span>
            </div>
            <div class="totals-row shipping">
                <span>Shipping</span>
                <span class="amount"><?php echo ($order['shipping_fee'] == 0) ? 'FREE' : '₱' . number_format($order['shipping_fee'], 2); ?></span>
            </div>
        </div>

        <div class="grand-total">
            <span class="label">Total Amount Due</span>
            <span class="amount">₱<?php echo number_format($order['total_amount'], 2); ?></span>
        </div>

        <div class="status-banner">
            <i class="bi bi-check-circle-fill"></i>
            <span><strong>Order Status:</strong> <?php echo htmlspecialchars($order['status']); ?></span>
        </div>

    </div>

</body>
</html>