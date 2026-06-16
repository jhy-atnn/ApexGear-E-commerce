<?php
session_start();

require_once __DIR__ . '/database/db_connect.php';
require_once __DIR__ . '/classes/Inventory.php';

/** @var Inventory $inventoryManager */
$inventoryManager = new Inventory();
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = $inventoryManager->refreshCartItemsWithLivePricing($_SESSION['cart']);
}

$cart_items = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart_items) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: store.php");
    exit();
}

// ── Checkout error notice (e.g. missing receipt screenshot) ──────────────────
$checkoutError = isset($_SESSION['checkout_error']) ? $_SESSION['checkout_error'] : null;
unset($_SESSION['checkout_error']);

// ── Applied promo code (carried over from cart.php) ──────────────────────────
$appliedCoupon = null;
$couponExpiredNotice = '';
if (isset($_SESSION['applied_coupon'])) {
    $checkoutUserId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
    $revalidated = $inventoryManager->validatePromoCode($_SESSION['applied_coupon']['code'], $checkoutUserId);
    if ($revalidated) {
        $appliedCoupon = $_SESSION['applied_coupon'];
    } else {
        $couponExpiredNotice = 'Promo code "' . htmlspecialchars($_SESSION['applied_coupon']['code']) . '" has expired and was removed. Totals below no longer include this discount.';
        unset($_SESSION['applied_coupon']);
    }
}

// ── Helper: compute the effective (live-sale) price for a cart item ──────────
// Called at order placement time — if the sale is still running, it applies.
function getEffectiveCheckoutPrice($item) {
    return Inventory::getCartItemEffectivePrice($item);
}

$order_successful = false;
$receipt_number = '';
$receipt_data = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $receipt_number = 'APX-' . strtoupper(uniqid());

    // 1. Extract Shipping Details
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $city      = trim($_POST['city'] ?? '');
    $zipcode   = trim($_POST['zipcode'] ?? '');
    $paymentMethodRaw = trim($_POST['payment_method'] ?? '');

    if ($firstName !== '' && !preg_match('/^[A-Za-z\s]+$/', $firstName)) {
        $_SESSION['checkout_error'] = 'Please only use letters for First Name.';
        header('Location: checkout.php');
        exit;
    }
    if ($lastName !== '' && !preg_match('/^[A-Za-z\s]+$/', $lastName)) {
        $_SESSION['checkout_error'] = 'Please only use letters for Last Name.';
        header('Location: checkout.php');
        exit;
    }
    if ($phone !== '' && !preg_match('/^[0-9]+$/', $phone)) {
        $_SESSION['checkout_error'] = 'Use numbers only for Phone Number.';
        header('Location: checkout.php');
        exit;
    }
    if ($zipcode !== '' && !preg_match('/^[0-9]+$/', $zipcode)) {
        $_SESSION['checkout_error'] = 'Use numbers only for ZIP / Postal Code.';
        header('Location: checkout.php');
        exit;
    }

    // 1b. Validate Payment Method Fields
    if ($paymentMethodRaw === 'gcash') {
        $gcashName = trim($_POST['gcash_name'] ?? '');
        $gcashMobile = trim($_POST['gcash_mobile'] ?? '');
        if ($gcashName !== '' && !preg_match('/^[A-Za-z\s]+$/', $gcashName)) {
            $_SESSION['checkout_error'] = 'Use letters only for GCash Account Name.';
            header('Location: checkout.php');
            exit;
        }
        if ($gcashMobile !== '' && !preg_match('/^[0-9]+$/', $gcashMobile)) {
            $_SESSION['checkout_error'] = 'Number Only for GCash Mobile Number.';
            header('Location: checkout.php');
            exit;
        }
    } elseif ($paymentMethodRaw === 'paypal') {
        $paypalNumber = trim($_POST['paypal_Number'] ?? '');
        if ($paypalNumber !== '' && !preg_match('/^[0-9]+$/', $paypalNumber)) {
            $_SESSION['checkout_error'] = 'Number Only for PayPal Number.';
            header('Location: checkout.php');
            exit;
        }
    } elseif ($paymentMethodRaw === 'maya') {
        $mayaName = trim($_POST['maya_name'] ?? '');
        $mayaMobile = trim($_POST['maya_mobile'] ?? '');
        if ($mayaName !== '' && !preg_match('/^[A-Za-z\s]+$/', $mayaName)) {
            $_SESSION['checkout_error'] = 'Use letters only for Maya Account Name.';
            header('Location: checkout.php');
            exit;
        }
        if ($mayaMobile !== '' && !preg_match('/^[0-9]+$/', $mayaMobile)) {
            $_SESSION['checkout_error'] = 'Number Only for Maya Mobile Number.';
            header('Location: checkout.php');
            exit;
        }
    }

    // 2. Setup receipt data for the frontend display
    $receipt_data = array(
        'firstName' => htmlspecialchars($firstName),
        'lastName'  => htmlspecialchars($lastName),
        'email'     => htmlspecialchars($email),
        'phone'     => htmlspecialchars($phone),
        'address'   => htmlspecialchars($address),
        'city'      => htmlspecialchars($city),
        'zipcode'   => htmlspecialchars($zipcode),
        'paymentMethod' => htmlspecialchars($paymentMethodRaw),
        'orderDate' => date('F d, Y \a\t h:i A'),
        'items'     => $cart_items
    );

    // 3. Extract Specific Payment Details (Card Last 4, GCash Number, etc.)
    $cardLast4 = null;
    $transactionId = null;

    if ($paymentMethodRaw === 'card') {
        $receipt_data['cardName'] = htmlspecialchars($_POST['card_name'] ?? '');
        $cardNum = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
        $cardLast4 = substr($cardNum, -4);
        $receipt_data['cardLast4'] = '****' . $cardLast4;
    } elseif ($paymentMethodRaw === 'gcash') {
        $receipt_data['gcashName'] = htmlspecialchars($_POST['gcash_name'] ?? '');
        $transactionId = htmlspecialchars($_POST['gcash_mobile'] ?? '');
        $receipt_data['gcashMobile'] = $transactionId;
    } elseif ($paymentMethodRaw === 'paypal') {
        $transactionId = htmlspecialchars($_POST['paypal_email'] ?? '');
        $receipt_data['paypalEmail'] = $transactionId;
    } elseif ($paymentMethodRaw === 'maya') {
        $receipt_data['mayaName'] = htmlspecialchars($_POST['maya_name'] ?? '');
        $transactionId = htmlspecialchars($_POST['maya_mobile'] ?? '');
        $receipt_data['mayaMobile'] = $transactionId;
    }

    // 3a. Require & save a payment receipt screenshot for QR/e-wallet methods
    $receiptImagePath = null;
    if (in_array($paymentMethodRaw, ['gcash', 'paypal', 'maya'])) {
        $receiptFieldName = $paymentMethodRaw . '_receipt';

        if (!isset($_FILES[$receiptFieldName]) || $_FILES[$receiptFieldName]['error'] !== UPLOAD_ERR_OK || $_FILES[$receiptFieldName]['size'] <= 0) {
            $_SESSION['checkout_error'] = 'Please upload a screenshot of your payment receipt to complete your order.';
            header("Location: checkout.php");
            exit();
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $fileType = function_exists('mime_content_type') ? mime_content_type($_FILES[$receiptFieldName]['tmp_name']) : $_FILES[$receiptFieldName]['type'];

        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['checkout_error'] = 'Your receipt must be an image file (JPG, PNG, GIF, or WEBP).';
            header("Location: checkout.php");
            exit();
        }

        $uploadDir = __DIR__ . '/assets/uploads/receipts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($_FILES[$receiptFieldName]['name'], PATHINFO_EXTENSION));
        $safeExt = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? $ext : 'png';
        $receiptFileName = $receipt_number . '_receipt.' . $safeExt;

        if (!move_uploaded_file($_FILES[$receiptFieldName]['tmp_name'], $uploadDir . $receiptFileName)) {
            $_SESSION['checkout_error'] = 'There was a problem uploading your receipt. Please try again.';
            header("Location: checkout.php");
            exit();
        }

        $receiptImagePath = 'assets/uploads/receipts/' . $receiptFileName;
        $receipt_data['receiptImage'] = $receiptImagePath;
    }

    // 4. Calculate Totals using live sale prices at checkout time
    $subtotal = 0;
    $item_count = 0;
    foreach ($cart_items as $cartKey => $item) {
        $effectivePrice = getEffectiveCheckoutPrice($item);
        $cart_items[$cartKey]['effective_price'] = $effectivePrice;
        $cart_items[$cartKey]['price_at_checkout'] = $effectivePrice;
        $subtotal += ($effectivePrice * $item['qty']);
        $item_count += $item['qty'];
    }

    // Apply promo code discount (if any) to the subtotal before tax
    $discount_amount = 0.00;
    $couponCodeForOrder = null;
    $couponIdForOrder = null;
    if ($appliedCoupon) {
        $discount_amount = round($subtotal * ((int)$appliedCoupon['discount'] / 100), 2);
        $couponCodeForOrder = $appliedCoupon['code'];
        $couponIdForOrder = $appliedCoupon['coupon_id'] ?? null;
    }
    $discounted_subtotal = $subtotal - $discount_amount;

    $tax = $discounted_subtotal * 0.08;
    $shipping_fee = 0.00; // Assuming free shipping as stated in UI
    $grand_total = $discounted_subtotal + $tax + $shipping_fee;

    $receipt_data['itemCount'] = $item_count;
    $receipt_data['subtotal'] = $subtotal;
    $receipt_data['discountAmount'] = $discount_amount;
    $receipt_data['couponCode'] = $couponCodeForOrder;
    $receipt_data['tax'] = $tax;
    $receipt_data['grandTotal'] = $grand_total;
    $receipt_data['receiptNumber'] = $receipt_number;
    $receipt_data['items'] = $cart_items;

    // ==========================================
    // DATABASE INSERTIONS START HERE
    // ==========================================
    $db = new Database();
    $conn = $db->getConnection();

    // Disable foreign key checks to prevent errors when items from the fake/session DB are used
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // Fallback to 0 if guest checkout (though your system prefers logged-in users)
    $dbUserId = isset($_SESSION['user']['id']) ? intval($_SESSION['user']['id']) : 0;

    // A. Insert into `orders_tbl`
    $stmtOrder = $conn->prepare("INSERT INTO orders_tbl (user_id, order_ref_code, coupon_id, coupon_code, discount_amount, subtotal, tax, shipping_fee, total_amount, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmtOrder->bind_param("isisddddd", $dbUserId, $receipt_number, $couponIdForOrder, $couponCodeForOrder, $discount_amount, $subtotal, $tax, $shipping_fee, $grand_total);
    $stmtOrder->execute();
    $orderId = $stmtOrder->insert_id; // Capture the new Order ID
    $stmtOrder->close();

    // Log the initial order status
    $stmtStatus = $conn->prepare("INSERT INTO order_status_tbl (order_id, order_status) VALUES (?, 'Pending')");
    $stmtStatus->bind_param("i", $orderId);
    $stmtStatus->execute();
    $stmtStatus->close();

    // B. Insert into `order_items_tbl` & update stock
    $stmtItems = $conn->prepare("INSERT INTO order_items_tbl (order_id, product_id, quantity, price_at_checkout) VALUES (?, ?, ?, ?)");
    $stmtStock = $conn->prepare("UPDATE products_tbl SET stock_qty = stock_qty - ? WHERE product_id = ? AND stock_qty >= ?");

    foreach ($cart_items as $pId => $item) {
        $pIdInt = intval($pId);
        $qty = intval($item['qty']);
        $price = getEffectiveCheckoutPrice($item); // locks in sale price if still valid

        // Insert item record
        $stmtItems->bind_param("iiid", $orderId, $pIdInt, $qty, $price);
        $stmtItems->execute();

        // Deduct stock safely
        $stmtStock->bind_param("iii", $qty, $pIdInt, $qty);
        $stmtStock->execute();
    }
    $stmtItems->close();
    $stmtStock->close();

    // C. Insert into `shipping_address_tbl`
    $stmtShip = $conn->prepare("INSERT INTO shipping_address_tbl (user_id, order_ref_code, first_name, last_name, phone_number, street_address, city, zip_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtShip->bind_param("isssssss", $dbUserId, $receipt_number, $firstName, $lastName, $phone, $address, $city, $zipcode);
    $stmtShip->execute();
    $stmtShip->close();

    // D. Insert into `payments_tbl`
    // Standardize payment method names
    $paymentMethodMap = [
        'card' => 'Credit / Debit Card',
        'cod' => 'Cash on Delivery',
        'gcash' => 'GCash',
        'paypal' => 'PayPal',
        'maya' => 'Maya'
    ];
    $paymentMethodDB = $paymentMethodMap[$paymentMethodRaw] ?? ucfirst($paymentMethodRaw);
    $paymentStatusDB = $paymentMethodRaw === 'cod' ? 'Pending' : 'Paid';

    $stmtPayment = $conn->prepare("INSERT INTO payments_tbl (order_id, method, status, card_last_four, transaction_id, qr_screenshot_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtPayment->bind_param("isssss", $orderId, $paymentMethodDB, $paymentStatusDB, $cardLast4, $transactionId, $receiptImagePath);
    $stmtPayment->execute();
    $stmtPayment->close();

    // E. Clear user's database cart if they are logged in
    if ($dbUserId > 0) {
        $stmtClearCart = $conn->prepare("DELETE FROM cart_items_tbl WHERE user_id = ?");
        $stmtClearCart->bind_param("i", $dbUserId);
        $stmtClearCart->execute();
        $stmtClearCart->close();
    }

    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    $db->closeConnection();
    // ==========================================
    // DATABASE INSERTIONS END HERE
    // ==========================================

    // Record one-time promo code usage so this user can't reuse it
    if ($appliedCoupon && !empty($appliedCoupon['coupon_id']) && $dbUserId > 0) {
        $inventoryManager->recordCouponUsage($appliedCoupon['coupon_id'], $dbUserId);
    }

    // Clear the cart session
    unset($_SESSION['cart']);
    unset($_SESSION['applied_coupon']);
    $order_successful = true;
} else {
    // Only calculate totals for display on the form
    $subtotal = 0;
    $item_count = 0;
    foreach ($cart_items as $item) {
        $effectivePrice = getEffectiveCheckoutPrice($item);
        $subtotal += ($effectivePrice * $item['qty']);
        $item_count += $item['qty'];
    }

    $discount_amount = 0.00;
    if ($appliedCoupon) {
        $discount_amount = round($subtotal * ((int)$appliedCoupon['discount'] / 100), 2);
    }
    $discounted_subtotal = $subtotal - $discount_amount;

    $tax = $discounted_subtotal * 0.08;
    $grand_total = $discounted_subtotal + $tax;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .form-control.is-invalid {
            border-color: #dc3545 !important;
            background-image: none;
        }

        .form-control.is-invalid:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        @media print {
            body {
                background: white;
            }

            nav,
            footer,
            .d-none,
            .offcanvas {
                display: none !important;
            }

            .inner-page {
                padding: 0;
            }

            .container {
                max-width: 100%;
                padding: 20px;
            }

            .apex-card {
                box-shadow: none;
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }

            .bg-light {
                background-color: white !important;
                border: 1px solid #ddd !important;
            }

            .alert {
                border: 1px solid #ddd !important;
                background: white !important;
                color: #333 !important;
            }

            table {
                page-break-inside: avoid;
            }

            #downloadPdfBtn,
            [onclick*="print"] {
                display: none !important;
            }

            .btn-apex,
            .btn {
                display: none !important;
            }

            h1,
            h2,
            h3,
            h4,
            h5,
            h6,
            p,
            span,
            td,
            th {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <?php include_once __DIR__ . '/includes\cookie_notif.php'; ?>

    <nav class="main-nav navbar navbar-expand-lg">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="brand me-4">
                <img src="assets/images/ApeX Logo.png" alt="ApeX Gear Logo" class="brand-logo-img">
                <div class="brand-text" style="color: white; margin-left:-10px">ApeX</div>
                <div class="brand-text" style="color: #00c2ff; margin-left:-10px">Gear</div>
            </a>

            <div class="text-white small fw-bold">
                <i class="fas fa-lock text-apex-accent"></i> Secure Checkout
            </div>
        </div>
    </nav>

    <section class="inner-page">
        <div class="container">

            <?php if ($order_successful): ?>
                <div class="row justify-content-center mt-4">
                    <div class="col-md-8 text-center">
                        <div class="apex-card border-top border-5" style="border-top-color: #00d68f !important;">
                            <div class="mb-4">
                                <i class="fas fa-check-circle" style="font-size: 4rem; color: #00d68f;"></i>
                            </div>
                            <h1 class="fw-bold mb-3" style="font-family: 'Barlow Condensed', sans-serif; text-transform: uppercase;">Payment Successful</h1>
                            <p class="text-muted lead mb-4">Thank you for your order! Your high-performance gear is being prepped for shipment.</p>

                            <div class="bg-light rounded-4 p-5 mb-5 text-start w-100 border" style="border: 2px solid var(--apex-border);">
                                <div class="text-center mb-4 pb-3 border-bottom">
                                    <h3 class="fw-bold mb-2" style="font-family: 'Barlow Condensed', sans-serif; letter-spacing: 0.05em;">ONLINE RECEIPT</h3>
                                    <p class="text-muted small mb-0">Payment Completed Successfully</p>
                                    <p class="text-muted small mb-1">Order Number</p>
                                    <h4 class="fw-bold text-apex-blue font-monospace"><?php echo $receipt_number; ?></h4>
                                    <p class="text-muted small mt-2 mb-0"><?php echo $receipt_data['orderDate']; ?></p>
                                </div>

                                <div class="mb-4 pb-3 border-bottom">
                                    <h6 class="fw-bold text-uppercase mb-3" style="font-family: 'Barlow Condensed', sans-serif; font-size: 0.9rem; letter-spacing: 0.05em; color: var(--apex-blue);">
                                        <i class="fas fa-map-marker-alt me-2"></i>Shipping Details
                                    </h6>
                                    <div class="ms-4">
                                        <p class="mb-2 text-dark fw-bold"><?php echo $receipt_data['firstName'] . ' ' . $receipt_data['lastName']; ?></p>
                                        <p class="mb-1 text-muted small"><?php echo $receipt_data['address']; ?></p>
                                        <p class="mb-1 text-muted small"><?php echo $receipt_data['city']; ?>, <?php echo $receipt_data['zipcode']; ?></p>
                                        <p class="mb-1 text-muted small"><i class="fas fa-phone me-2" style="color: var(--apex-blue);"></i><?php echo $receipt_data['phone']; ?></p>
                                        <p class="mb-0 text-muted small"><i class="fas fa-envelope me-2" style="color: var(--apex-blue);"></i><?php echo $receipt_data['email']; ?></p>
                                    </div>
                                </div>

                                <div class="mb-4 pb-3 border-bottom">
                                    <h6 class="fw-bold text-uppercase mb-3" style="font-family: 'Barlow Condensed', sans-serif; font-size: 0.9rem; letter-spacing: 0.05em; color: var(--apex-blue);">
                                        <i class="fas fa-credit-card me-2"></i>Payment Method
                                    </h6>
                                    <div class="ms-4">
                                        <p class="mb-0 text-dark fw-bold text-capitalize">
                                            <?php
                                            $methods = array(
                                                'card' => 'Credit/Debit Card',
                                                'cod' => 'Cash on Delivery',
                                                'gcash' => 'GCash',
                                                'paypal' => 'PayPal',
                                                'maya' => 'Maya'
                                            );
                                            echo isset($methods[$receipt_data['paymentMethod']]) ? $methods[$receipt_data['paymentMethod']] : ucfirst($receipt_data['paymentMethod']);
                                            ?>
                                        </p>
                                        <?php if ($receipt_data['paymentMethod'] === 'card' && isset($receipt_data['cardLast4'])): ?>
                                            <p class="mb-0 text-muted small mt-1">Card: <?php echo $receipt_data['cardLast4']; ?></p>
                                        <?php elseif ($receipt_data['paymentMethod'] === 'gcash' && isset($receipt_data['gcashMobile'])): ?>
                                            <p class="mb-0 text-muted small mt-1"><?php echo $receipt_data['gcashName']; ?></p>
                                            <p class="mb-0 text-muted small"><?php echo $receipt_data['gcashMobile']; ?></p>
                                        <?php elseif ($receipt_data['paymentMethod'] === 'paypal' && isset($receipt_data['paypalEmail'])): ?>
                                            <p class="mb-0 text-muted small mt-1"><?php echo $receipt_data['paypalEmail']; ?></p>
                                        <?php elseif ($receipt_data['paymentMethod'] === 'maya' && isset($receipt_data['mayaMobile'])): ?>
                                            <p class="mb-0 text-muted small mt-1"><?php echo $receipt_data['mayaName']; ?></p>
                                            <p class="mb-0 text-muted small"><?php echo $receipt_data['mayaMobile']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-4 pb-3 border-bottom">
                                    <h6 class="fw-bold text-uppercase mb-3" style="font-family: 'Barlow Condensed', sans-serif; font-size: 0.9rem; letter-spacing: 0.05em; color: var(--apex-blue);">
                                        <i class="fas fa-shopping-bag me-2"></i>Order Items (<?php echo $receipt_data['itemCount']; ?> <?php echo $receipt_data['itemCount'] == 1 ? 'Item' : 'Items'; ?>)
                                    </h6>
                                    <div class="ms-4">
                                        <table class="w-100" style="font-size: 0.9rem;">
                                            <thead>
                                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                                    <th class="text-start pb-2 text-muted fw-bold">Item</th>
                                                    <th class="text-center pb-2 text-muted fw-bold">Qty</th>
                                                    <th class="text-end pb-2 text-muted fw-bold">Price</th>
                                                    <th class="text-end pb-2 text-muted fw-bold">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($receipt_data['items'] as $item):
                                                    $rcptPrice = getEffectiveCheckoutPrice($item);
                                                    $rcptOrig  = floatval($item['original_price'] ?? $item['price']);
                                                    $rcptSale  = $rcptPrice < $rcptOrig;
                                                ?>
                                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                                        <td class="py-2 text-dark">
                                                            <?php echo htmlspecialchars($item['name']); ?>
                                                            <?php if ($rcptSale): ?><br><small style="color:#ff3b5c;font-weight:700;"><?php echo intval($item['discount_percent'] ?? $item['sale_percent'] ?? 0); ?>% OFF applied</small><?php endif; ?>
                                                        </td>
                                                        <td class="text-center py-2 text-dark"><?php echo $item['qty']; ?></td>
                                                        <td class="text-end py-2 text-dark">
                                                            ₱<?php echo number_format($rcptPrice, 2); ?>
                                                            <?php if ($rcptSale): ?><br><small style="text-decoration:line-through;color:#aaa;">₱<?php echo number_format($rcptOrig, 2); ?></small><?php endif; ?>
                                                        </td>
                                                        <td class="text-end py-2 fw-bold text-dark">₱<?php echo number_format($rcptPrice * $item['qty'], 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <div class="small text-muted mt-3">
                                            <p class="mb-0">Total Quantity: <strong class="text-dark"><?php echo $receipt_data['itemCount']; ?></strong> items</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Subtotal</span>
                                        <span class="text-dark">₱<?php echo number_format($receipt_data['subtotal'], 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Promo <?php echo !empty($receipt_data['couponCode']) ? '(' . htmlspecialchars($receipt_data['couponCode']) . ')' : ''; ?></span>
                                        <?php if (!empty($receipt_data['discountAmount'])): ?>
                                            <span class="text-success">&minus;₱<?php echo number_format($receipt_data['discountAmount'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Tax (8%)</span>
                                        <span class="text-dark">₱<?php echo number_format($receipt_data['tax'], 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Shipping</span>
                                        <span class="text-success fw-bold">FREE</span>
                                    </div>
                                    <div style="border-top: 2px solid var(--apex-border); padding-top: 1rem;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold fs-5 text-dark">Total Amount Due</span>
                                            <span class="fw-bold fs-4 text-apex-blue">₱<?php echo number_format($receipt_data['grandTotal'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-success alert-dismissible fade show mt-4 mb-0" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Order Status:</strong> Processing &bull; Paid
                                </div>
                            </div>

                            <div class="d-flex gap-3 justify-content-center mb-4">
                                <a href="actions/print_receipt.php?order_id=<?php echo $orderId; ?>" target="_blank" class="btn" style="background: var(--apex-blue); color: white; border: none; padding: 10px 25px; border-radius: 50px; font-weight: 600; text-decoration: none;">
                                    <i class="fas fa-download me-2"></i>Download PDF
                                </a>
                                <a href="actions/print_receipt.php?order_id=<?php echo $orderId; ?>" target="_blank" class="btn" style="background: #6c757d; color: white; border: none; padding: 10px 25px; border-radius: 50px; font-weight: 600; text-decoration: none;">
                                    <i class="fas fa-print me-2"></i>Print Receipt
                                </a>
                            </div>

                            <a href="store.php" class="btn-apex" style="background: var(--apex-dark); color: white;">Return to Store</a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="mb-3">
                    <a href="#" class="btn btn-outline-secondary" onclick="(function(e){ e.preventDefault(); if(document.referrer){ history.back(); } else { window.location.href='store.php'; } })(event);">&larr; Back</a>
                </div>

                <h2 class="sec-title mb-4">Complete <span>Your Order</span></h2>

                <?php if ($couponExpiredNotice): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span><?php echo $couponExpiredNotice; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($checkoutError): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($checkoutError); ?></span>
                    </div>
                <?php endif; ?>
                <div id="checkoutFieldAlert" class="alert alert-danger d-none mb-4" role="alert"></div>

                <form method="POST" action="checkout.php" enctype="multipart/form-data" class="row g-5">

                    <div class="col-lg-8">
                        <div class="apex-card mb-4">
                            <h5 class="fw-bold mb-4 border-bottom pb-3 text-uppercase" style="font-family: 'Barlow Condensed', sans-serif; font-size: 1.4rem;">Shipping Details</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">First Name</label>
                                    <input id="checkoutFirstName" type="text" class="form-control bg-light" name="first_name" required value="<?php echo isset($_SESSION['user']['first_name']) ? htmlspecialchars($_SESSION['user']['first_name']) : ''; ?>" oninput="filterLettersOnly(this)">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Last Name</label>
                                    <input id="checkoutLastName" type="text" class="form-control bg-light" name="last_name" required value="<?php echo isset($_SESSION['user']['last_name']) ? htmlspecialchars($_SESSION['user']['last_name']) : ''; ?>" oninput="filterLettersOnly(this)">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                                    <input type="email" class="form-control bg-light" name="email" required placeholder="receipts@example.com" value="<?php echo isset($_SESSION['user']['email']) ? htmlspecialchars($_SESSION['user']['email']) : ''; ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Phone Number</label>
                                    <input id="checkoutPhone" type="tel" class="form-control bg-light" name="phone" required placeholder="09171234567" value="<?php echo isset($_SESSION['user']['phone']) ? htmlspecialchars($_SESSION['user']['phone']) : ''; ?>" oninput="filterNumbersOnly(this)">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Street Address</label>
                                    <input type="text" class="form-control bg-light" name="address" required value="<?php echo isset($_SESSION['user']['street_address']) ? htmlspecialchars($_SESSION['user']['street_address']) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">City</label>
                                    <input type="text" class="form-control bg-light" name="city" required value="<?php echo isset($_SESSION['user']['city']) ? htmlspecialchars($_SESSION['user']['city']) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">ZIP / Postal Code</label>
                                    <input id="checkoutZipcode" type="text" class="form-control bg-light" name="zipcode" required value="<?php echo isset($_SESSION['user']['postal_code']) ? htmlspecialchars($_SESSION['user']['postal_code']) : ''; ?>" oninput="filterNumbersOnly(this)">
                                </div>
                            </div>
                        </div>

                        <div class="apex-card">
                            <h5 class="fw-bold mb-4 border-bottom pb-3 text-uppercase" style="font-family: 'Barlow Condensed', sans-serif; font-size: 1.4rem;">Payment Method</h5>

                            <div class="d-flex gap-2 mb-4" style="flex-wrap: nowrap; overflow-x: auto;">
                                <div class="payment-method border rounded px-3 py-2 bg-light fw-bold text-apex-blue" data-method="card" style="cursor:pointer; border-color: var(--apex-blue) !important; flex: 1; min-width: 140px; text-align: center; transition: all 0.3s ease; white-space: nowrap;">
                                    <i class="fas fa-credit-card me-2"></i> Credit/Debit
                                </div>

                                <div class="payment-method border rounded px-3 py-2 bg-light fw-bold text-apex-blue" data-method="cod" style="cursor:pointer; border-color: var(--apex-blue) !important; flex: 1; min-width: 120px; text-align: center; transition: all 0.3s ease; white-space: nowrap;">
                                    <i class="fas fa-money-bill-wave me-2"></i> COD
                                </div>

                                <div class="payment-method border rounded px-3 py-2 bg-light fw-bold text-apex-blue" data-method="gcash" style="cursor:pointer; border-color: var(--apex-blue) !important; flex: 1; min-width: 120px; text-align: center; transition: all 0.3s ease; white-space: nowrap;">
                                    <i class="fas fa-mobile-alt me-2"></i> GCash
                                </div>

                                <div class="payment-method border rounded px-3 py-2 bg-light fw-bold text-apex-blue" data-method="paypal" style="cursor:pointer; border-color: var(--apex-blue) !important; flex: 1; min-width: 120px; text-align: center; transition: all 0.3s ease; white-space: nowrap;">
                                    <i class="fab fa-paypal me-2"></i> PayPal
                                </div>

                                <div class="payment-method border rounded px-3 py-2 bg-light fw-bold text-apex-blue" data-method="maya" style="cursor:pointer; border-color: var(--apex-blue) !important; flex: 1; min-width: 100px; text-align: center; transition: all 0.3s ease; white-space: nowrap;">
                                    <i class="fas fa-wallet me-2"></i> Maya
                                </div>
                            </div>

                            <input type="hidden" id="selected_payment_method" name="payment_method" value="">

                            <div id="card-fields" class="payment-fields row g-3" style="display: none;">
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Name on Card</label>
                                    <input type="text" class="form-control bg-light payment-required" name="card_name" placeholder="John Doe">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Card Number</label>
                                    <input type="text" class="form-control bg-light payment-required" name="card_number" placeholder="0000 0000 0000 0000" pattern="\d{16}" title="16 digit card number" maxlength="19">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Expiration Date</label>
                                    <input type="text" class="form-control bg-light payment-required" name="card_expiry" placeholder="MM/YY" maxlength="5">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">CVV</label>
                                    <input type="password" class="form-control bg-light payment-required" name="card_cvv" placeholder="123" maxlength="3">
                                </div>
                            </div>

                            <div id="cod-fields" class="payment-fields row g-3" style="display: none;">
                                <div class="col-12">
                                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Cash on Delivery</strong>
                                        <p class="mb-0 small mt-2">You will pay ₱<?php echo number_format($grand_total, 2); ?> when the package arrives at your address.</p>
                                    </div>
                                </div>
                            </div>

                            <div id="gcash-fields" class="payment-fields row g-3" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Account Name</label>
                                    <input type="text" class="form-control bg-light payment-required" name="gcash_name" placeholder="Name" oninput="filterLettersOnly(this)">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Mobile Number</label>
                                    <input type="tel" class="form-control bg-light payment-required" name="gcash_mobile" placeholder="09171234567" oninput="filterNumbersOnly(this)">
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-info" role="alert">
                                        <small><i class="fas fa-check-circle me-2"></i>Payment reference will be sent to your G Cash account.</small>
                                    </div>
                                </div>
                                <div class="col-12 text-center">
                                    <label class="form-label small fw-bold text-muted text-uppercase d-block mb-2">Scan to Pay via GCash</label>
                                    <img src="assets/images/bank%20qr%20codes/gcash-qr-card.png" alt="GCash QR Code" style="max-width: 400px; width: 100%; border-radius: 12px; border: 1px solid var(--apex-border); padding: 8px; background: #fff;">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Upload Payment Receipt <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control bg-light payment-required" name="gcash_receipt" accept="image/png, image/jpeg, image/webp, image/gif">
                                    <small class="text-muted">Scan the QR code above to pay, then upload a screenshot of your payment confirmation. Your order cannot be processed without this.</small>
                                </div>
                            </div>

                            <div id="paypal-fields" class="payment-fields row g-3" style="display: none;">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">PayPal Email</label>
                                    <input type="email" class="form-control bg-light payment-required" name="paypal_email" placeholder="your@paypal.com">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">PayPal Number</label>
                                    <input type="tel" class="form-control bg-light payment-required" name="paypal_Number" placeholder="09171234567" oninput="filterNumbersOnly(this)">
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-info" role="alert">
                                        <small><i class="fas fa-external-link-alt me-2"></i>You will be redirected to PayPal to complete the payment.</small>
                                    </div>
                                </div>
                                <div class="col-12 text-center">
                                    <label class="form-label small fw-bold text-muted text-uppercase d-block mb-2">Scan to Pay via PayPal</label>
                                    <img src="assets/images/bank%20qr%20codes/paypal-qr-card.png" alt="PayPal QR Code" style="max-width: 400px; width: 100%; border-radius: 12px; border: 1px solid var(--apex-border); padding: 8px; background: #fff;">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Upload Payment Receipt <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control bg-light payment-required" name="paypal_receipt" accept="image/png, image/jpeg, image/webp, image/gif">
                                    <small class="text-muted">Scan the QR code above to pay, then upload a screenshot of your payment confirmation. Your order cannot be processed without this.</small>
                                </div>
                            </div>

                            <div id="maya-fields" class="payment-fields row g-3" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Account Name</label>
                                    <input type="text" class="form-control bg-light payment-required" name="maya_name" placeholder="Name" oninput="filterLettersOnly(this)">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Mobile Number</label>
                                    <input type="tel" class="form-control bg-light payment-required" name="maya_mobile" placeholder="09171234567" oninput="filterNumbersOnly(this)">
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-info" role="alert">
                                        <small><i class="fas fa-check-circle me-2"></i>Payment instructions will be sent to your registered account.</small>
                                    </div>
                                </div>
                                <div class="col-12 text-center">
                                    <label class="form-label small fw-bold text-muted text-uppercase d-block mb-2">Scan to Pay via Maya</label>
                                    <img src="assets/images/bank%20qr%20codes/maya-qr-card.png" alt="Maya QR Code" style="max-width: 400px; width: 100%; border-radius: 12px; border: 1px solid var(--apex-border); padding: 8px; background: #fff;">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Upload Payment Receipt <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control bg-light payment-required" name="maya_receipt" accept="image/png, image/jpeg, image/webp, image/gif">
                                    <small class="text-muted">Scan the QR code above to pay, then upload a screenshot of your payment confirmation. Your order cannot be processed without this.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="apex-card sticky-top" style="top: 100px;">
                            <h5 class="fw-bold mb-4 text-uppercase" style="font-family: 'Barlow Condensed', sans-serif; font-size: 1.4rem;">In Your Cart</h5>

                            <div class="mb-4 border-bottom pb-3">
                                <?php foreach ($cart_items as $item):
                                    $chkPrice = getEffectiveCheckoutPrice($item);
                                    $chkOrig  = floatval($item['original_price'] ?? $item['price']);
                                    $chkSale  = $chkPrice < $chkOrig;
                                ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                                        <span class="text-muted text-truncate me-2" style="max-width: 200px;">
                                            <span class="text-apex-blue fw-bold"><?php echo $item['qty']; ?>x</span> <?php echo htmlspecialchars($item['name']); ?>
                                            <?php if ($chkSale): ?><span style="color:#ff3b5c;font-weight:700;font-size:.7rem;display:block;"><?php echo intval($item['discount_percent'] ?? $item['sale_percent'] ?? 0); ?>% OFF</span><?php endif; ?>
                                        </span>
                                        <span class="fw-bold text-dark">₱<?php echo number_format($chkPrice * $item['qty'], 2); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-bold">₱<?php echo number_format($subtotal, 2); ?></span>
                            </div>

                            <?php if (!empty($appliedCoupon)): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Promo (<?php echo htmlspecialchars($appliedCoupon['code']); ?>)</span>
                                <span class="fw-bold text-success">&minus;₱<?php echo number_format($discount_amount, 2); ?></span>
                            </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Tax</span>
                                <span class="fw-bold">₱<?php echo number_format($tax, 2); ?></span>
                            </div>

                            <div class="d-flex justify-content-between mb-4">
                                <span class="text-muted">Shipping</span>
                                <span class="fw-bold text-success">FREE</span>
                            </div>

                            <hr class="mb-4">

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="fw-bold fs-5 text-dark">Total</span>
                                <span class="fw-bold fs-4 text-apex-blue">₱<?php echo number_format($grand_total, 2); ?></span>
                            </div>

                            <input type="hidden" name="place_order" value="1">
                            <button type="submit" class="btn-apex w-100 py-3 text-center">Pay ₱<?php echo number_format($grand_total, 2); ?></button>

                            <div class="text-center mt-3">
                                <small class="text-muted">By confirming your order, you agree to our Terms of Service.</small>
                            </div>
                        </div>
                    </div>

                </form>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/navbar.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
        // PDF Download functionality
        document.addEventListener('DOMContentLoaded', function() {
            const downloadBtn = document.getElementById('downloadPdfBtn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function() {
                    // Find the receipt container
                    const receiptElement = document.querySelector('.bg-light.rounded-4.p-5');

                    if (receiptElement) {
                        // Clone the receipt for PDF export
                        const element = receiptElement.cloneNode(true);

                        // Remove buttons div from clone
                        const buttonsToRemove = element.querySelectorAll('.d-flex.gap-3.justify-content-center');
                        buttonsToRemove.forEach(btn => btn.remove());

                        const opt = {
                            margin: 10,
                            filename: 'APX_Gear_Receipt_<?php echo isset($receipt_number) ? htmlspecialchars($receipt_number) : 'receipt'; ?>.pdf',
                            image: {
                                type: 'jpeg',
                                quality: 0.98
                            },
                            html2canvas: {
                                scale: 2,
                                useCORS: true
                            },
                            jsPDF: {
                                orientation: 'portrait',
                                unit: 'mm',
                                format: 'a4'
                            }
                        };

                        html2pdf().set(opt).from(element).save();
                    } else {
                        alert('Receipt not found. Please try again.');
                    }
                });
            }
        });
    </script>

    <script>
        function showCheckoutAlert(message) {
            const el = document.getElementById('checkoutFieldAlert');
            if (!el) return;
            el.textContent = message;
            el.classList.remove('d-none');
        }

        function hideCheckoutAlert() {
            const el = document.getElementById('checkoutFieldAlert');
            if (!el) return;
            el.classList.add('d-none');
        }

        function filterLettersOnly(el) {
            const original = el.value;
            const filtered = original.replace(/[^A-Za-z\s]/g, '');
            if (original !== filtered) {
                el.value = filtered;
                showCheckoutAlert('Please only use letters');
            } else {
                hideCheckoutAlert();
            }
        }

        function filterNumbersOnly(el) {
            const original = el.value;
            const filtered = original.replace(/[^0-9]/g, '');
            if (original !== filtered) {
                el.value = filtered;
                showCheckoutAlert('Use numbers only');
            } else {
                hideCheckoutAlert();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethods = document.querySelectorAll('.payment-method');
            const selectedMethodInput = document.getElementById('selected_payment_method');
            const form = document.querySelector('form');

            // Initialize - set first payment method as default
            if (paymentMethods.length > 0) {
                paymentMethods[0].click();
            }

            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    const selectedMethod = this.getAttribute('data-method');

                    // Update the hidden input
                    selectedMethodInput.value = selectedMethod;

                    // Remove active state from all methods
                    paymentMethods.forEach(m => {
                        m.classList.remove('active');
                    });

                    // Set active state for clicked method
                    this.classList.add('active');

                    // Hide all payment field sections
                    const allFields = document.querySelectorAll('.payment-fields');
                    allFields.forEach(field => {
                        field.style.display = 'none';
                    });

                    // Show the selected payment method's fields
                    const fieldsToShow = document.getElementById(selectedMethod + '-fields');
                    if (fieldsToShow) {
                        fieldsToShow.style.display = '';
                        // Trigger animation
                        setTimeout(() => {
                            fieldsToShow.scrollIntoView({
                                behavior: 'smooth',
                                block: 'nearest'
                            });
                        }, 100);
                    }
                });
            });

            // Form submission validation
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Validate payment method is selected
                    if (!selectedMethodInput.value) {
                        alert('Please select a payment method.');
                        return false;
                    }

                    // Validate shipping name and number fields
                    const firstNameField = document.getElementById('checkoutFirstName');
                    const lastNameField = document.getElementById('checkoutLastName');
                    const phoneField = document.getElementById('checkoutPhone');
                    const zipcodeField = document.getElementById('checkoutZipcode');
                    const lettersOnly = /^[A-Za-z\s]+$/;
                    const numbersOnly = /^[0-9]+$/;

                    if (firstNameField && !lettersOnly.test(firstNameField.value.trim())) {
                        showCheckoutAlert('Please only use letters');
                        firstNameField.focus();
                        return false;
                    }
                    if (lastNameField && !lettersOnly.test(lastNameField.value.trim())) {
                        showCheckoutAlert('Please only use letters');
                        lastNameField.focus();
                        return false;
                    }
                    if (phoneField && !numbersOnly.test(phoneField.value.trim())) {
                        showCheckoutAlert('Use numbers only');
                        phoneField.focus();
                        return false;
                    }
                    if (zipcodeField && !numbersOnly.test(zipcodeField.value.trim())) {
                        showCheckoutAlert('Use numbers only');
                        zipcodeField.focus();
                        return false;
                    }

                    // Check all required payment fields for the selected method
                    const selectedMethod = selectedMethodInput.value;
                    const paymentFields = document.querySelectorAll(`#${selectedMethod}-fields .payment-required`);
                    let allFilled = true;
                    let emptyFieldName = '';

                    paymentFields.forEach(field => {
                        if (!field.value.trim()) {
                            allFilled = false;
                            emptyFieldName = field.previousElementSibling?.textContent || field.name;
                            field.classList.add('is-invalid');
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });

                    if (!allFilled) {
                        alert(`Please fill in all required fields for ${selectedMethod.toUpperCase()}. Missing: ${emptyFieldName}`);
                        return false;
                    }

                    hideCheckoutAlert();
                    // If all validation passes, submit the form
                    form.submit();
                });

                // Add real-time validation for payment fields
                const paymentRequiredFields = document.querySelectorAll('.payment-required');
                paymentRequiredFields.forEach(field => {
                    field.addEventListener('input', function() {
                        if (this.value.trim()) {
                            this.classList.remove('is-invalid');
                        }
                    });
                });
            }
        });
    </script>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel" style="width: 400px; border-left: none; box-shadow: -4px 0 24px rgba(0,0,0,0.1);">

        <div class="offcanvas-header border-bottom pb-3 pt-4 px-4">
            <h6 class="offcanvas-title fw-bold text-dark d-flex align-items-center" id="cartOffcanvasLabel">
                <i class="fas fa-shopping-bag me-2" style="color: var(--apex-blue);"></i> Your Cart
            </h6>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body p-4 d-flex flex-column">
            <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
                <div class="m-auto text-center w-100">
                    <div class="mb-4">
                        <div style="width: 80px; height: 80px; background: var(--apex-grey); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <i class="fas fa-shopping-bag fa-2x text-muted opacity-50"></i>
                        </div>
                    </div>
                    <h6 class="fw-bold mb-2">Your cart is empty</h6>
                    <p class="text-muted small mb-4">Looks like you haven't added any gadgets yet.</p>
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2" style="background: var(--apex-blue); border-color: var(--apex-blue); font-weight: 600; font-size: .9rem;" data-bs-dismiss="offcanvas">Continue Shopping</button>
                </div>
            <?php else: ?>
                <div class="cart-items flex-grow-1 overflow-auto pe-2">
                    <?php
                    $subtotal = 0;
                    foreach ($cart_items as $id => $item):
                        $ocPrice = getEffectiveCheckoutPrice($item);
                        $ocOrig  = floatval($item['original_price'] ?? $item['price']);
                        $ocSale  = $ocPrice < $ocOrig;
                        $subtotal += ($ocPrice * $item['qty']);
                    ?>
                        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                            <div style="width: 60px; height: 60px; border-radius: 8px; background: var(--apex-grey); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink: 0;">
                                <?php
                                if (strpos($item['image'], '<svg') !== false) {
                                    echo $item['image'];
                                } else {
                                    echo '<img src="' . htmlspecialchars($item['image']) . '" style="max-width: 100%; object-fit: contain;">';
                                }
                                ?>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <h6 class="mb-1 fw-bold text-dark text-truncate" style="font-size: .9rem;"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <?php if ($ocSale): ?><small style="color:#ff3b5c;font-weight:700;"><?php echo intval($item['discount_percent'] ?? $item['sale_percent'] ?? 0); ?>% OFF</small><?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Qty: <?php echo $item['qty']; ?></span>
                                    <span class="fw-bold text-apex-blue">₱<?php echo number_format($ocPrice * $item['qty'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-footer pt-3 mt-auto border-top">
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold text-muted text-uppercase small" style="letter-spacing: 0.05em;">Subtotal</span>
                        <span class="fw-bold text-dark fs-5">₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn-apex w-100 text-center py-3" style="border-radius: 50px;">Proceed to Checkout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>