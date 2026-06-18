<?php
session_start();
require_once __DIR__ . '/classes/Inventory.php';
require_once __DIR__ . '/includes/auth_timeout.php';
$currentPage = 'cart';

apex_enforce_login_timeout();

/** @var Inventory $inventoryManager */
$inventoryManager = new Inventory();

$couponMsg = '';
$couponMsgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $product_id = $_POST['product_id'] ?? 0;
    if ($_POST['action'] === 'checkout') {
        $quantities = isset($_POST['quantities']) && is_array($_POST['quantities']) ? $_POST['quantities'] : [];
        foreach ($quantities as $productId => $qty) {
            $productId = (int)$productId;
            $qty = (int)$qty;

            if ($productId <= 0 || !isset($_SESSION['cart'][$productId])) {
                continue;
            }

            if ($qty > 0) {
                $_SESSION['cart'][$productId]['qty'] = $qty;
            } else {
                unset($_SESSION['cart'][$productId]);
            }
        }

        header("Location: checkout.php");
        exit();
    } elseif ($_POST['action'] === 'remove') {
        unset($_SESSION['cart'][$product_id]);
    } elseif ($_POST['action'] === 'update') {
        $new_qty = (int)($_POST['qty'] ?? 0);
        if ($new_qty > 0) {
            $_SESSION['cart'][$product_id]['qty'] = $new_qty;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    } elseif ($_POST['action'] === 'apply_coupon') {
        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        $userId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
        $promo = $inventoryManager->validatePromoCode($code, $userId);
        if ($promo) {
            $_SESSION['applied_coupon'] = [
                'code'       => $promo['code_name'],
                'discount'   => (int)$promo['discount_percentage'],
                'coupon_id'  => $promo['coupon_id'],
            ];
            $_SESSION['coupon_msg'] = ['type' => 'success', 'text' => 'Coupon "' . $promo['code_name'] . '" applied!'];
        } else {
            unset($_SESSION['applied_coupon']);
            $activeForCode = $inventoryManager->getActivePromo();
            if ($userId !== null && $activeForCode && strtoupper($activeForCode['code_name']) === $code && $inventoryManager->hasUserUsedCoupon((int)$activeForCode['coupon_id'], $userId)) {
                $_SESSION['coupon_msg'] = ['type' => 'danger', 'text' => 'You have already used this promo code on a previous order.'];
            } else {
                $_SESSION['coupon_msg'] = ['type' => 'danger', 'text' => 'Invalid or expired coupon code.'];
            }
        }
        header("Location: cart.php");
        exit();
    } elseif ($_POST['action'] === 'remove_coupon') {
        unset($_SESSION['applied_coupon']);
        $_SESSION['coupon_msg'] = ['type' => 'success', 'text' => 'Coupon removed.'];
        header("Location: cart.php");
        exit();
    }
    header("Location: cart.php");
    exit();
}

// One-time flash message for coupon actions
if (isset($_SESSION['coupon_msg'])) {
    $couponMsgType = $_SESSION['coupon_msg']['type'];
    $couponMsg     = $_SESSION['coupon_msg']['text'];
    unset($_SESSION['coupon_msg']);
}

function getEffectivePriceForCart($item)
{
    return Inventory::getCartItemEffectivePrice($item);
}

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = $inventoryManager->refreshCartItemsWithLivePricing($_SESSION['cart']);
}

$subtotal = 0;
$item_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $effectivePrice = getEffectivePriceForCart($item);
        $subtotal += ($effectivePrice * $item['qty']);
        $item_count += $item['qty'];
    }
}

// ── APPLIED COUPON / DISCOUNT ──
$appliedCoupon = null;
$couponDiscountAmount = 0;
if (isset($_SESSION['applied_coupon'])) {
    // Re-validate every load in case the admin deactivated/deleted/expired it
    $userId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
    $revalidated = $inventoryManager->validatePromoCode($_SESSION['applied_coupon']['code'], $userId);
    if ($revalidated) {
        $appliedCoupon = $_SESSION['applied_coupon'];
        $couponDiscountAmount = $subtotal * ((int)$appliedCoupon['discount'] / 100);
    } else {
        $expiredCode = $_SESSION['applied_coupon']['code'];
        unset($_SESSION['applied_coupon']);
        if (!$couponMsg) {
            $couponMsg = 'Promo code "' . $expiredCode . '" has expired and was removed from your cart.';
            $couponMsgType = 'danger';
        }
    }
}

$tax = $subtotal * 0.08;
$grand_total = ($subtotal - $couponDiscountAmount) + $tax;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* ── CART HEADER BAR ── */
        .cart-header-bar {
            background: var(--apex-blue);
            padding: 0 0;
        }

        .cart-header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }

        .cart-header-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .cart-header-logo img {
            height: 42px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 0 4px rgba(0, 194, 255, .3));
        }

        .cart-header-logo .logo-wordmark {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.7rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -.01em;
            color: #fff;
        }

        .cart-header-logo .logo-wordmark span {
            color: var(--apex-accent);
        }

        .cart-header-label {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .5);
        }

        /* ── PAGE BODY ── */
        .cart-page {
            background: var(--apex-grey);
            flex: 1;
            padding: 40px 0 80px;
        }

        /* ── BACK LINK ── */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: .82rem;
            font-weight: 600;
            color: var(--apex-muted);
            text-decoration: none;
            margin-bottom: 28px;
            transition: color .18s;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }

        .back-link:hover {
            color: var(--apex-blue-mid);
        }

        .back-link i {
            font-size: .75rem;
        }

        /* ── PAGE HEADING ── */
        .cart-heading {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 2.2rem;
            font-weight: 900;
            color: var(--apex-text);
            margin-bottom: 4px;
            letter-spacing: .01em;
        }

        .cart-heading span {
            color: var(--apex-blue-mid);
        }

        .cart-subtext {
            font-size: .85rem;
            color: var(--apex-muted);
            margin-bottom: 32px;
        }

        /* ── CART CARD ── */
        .cart-card {
            background: #fff;
            border: 1px solid var(--apex-border);
            border-radius: 14px;
            overflow: hidden;
        }

        /* ── TABLE ── */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table thead tr {
            background: var(--apex-grey);
            border-bottom: 1px solid var(--apex-border);
        }

        .cart-table thead th {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: var(--apex-muted);
            padding: 13px 20px;
        }

        .cart-table tbody tr {
            border-bottom: 1px solid var(--apex-border);
            transition: background .15s;
        }

        .cart-table tbody tr:last-child {
            border-bottom: none;
        }

        .cart-table tbody tr:hover {
            background: #fafbff;
        }

        .cart-table td {
            padding: 18px 20px;
            vertical-align: middle;
        }

        /* ── PRODUCT CELL ── */
        .product-thumb {
            width: 62px;
            height: 62px;
            border-radius: 10px;
            background: var(--apex-grey);
            border: 1px solid var(--apex-border);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .product-thumb img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .product-name {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: var(--apex-text);
            letter-spacing: .02em;
        }

        .product-sub {
            font-size: .75rem;
            color: var(--apex-muted);
            margin-top: 2px;
        }

        /* ── PRICE CELL ── */
        .price-main {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--apex-blue);
        }

        .price-original {
            font-size: .75rem;
            color: var(--apex-muted);
            text-decoration: line-through;
            margin-top: 2px;
        }

        .badge-sale {
            display: inline-block;
            background: rgba(255, 59, 92, .1);
            color: #ff3b5c;
            font-size: .66rem;
            font-weight: 700;
            letter-spacing: .6px;
            padding: 2px 6px;
            border-radius: 4px;
            margin-top: 3px;
            font-family: 'Barlow Condensed', sans-serif;
        }

        .sale-timer {
            font-size: .67rem;
            color: #ff3b5c;
            margin-top: 3px;
        }

        /* ── QTY CONTROLS ── */
        .qty-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .qty-input {
            width: 54px;
            height: 34px;
            border: 1px solid var(--apex-border);
            border-radius: 7px;
            background: var(--apex-grey);
            color: var(--apex-text);
            font-weight: 700;
            font-size: .88rem;
            text-align: center;
            outline: none;
            transition: border-color .2s;
        }

        .qty-input:focus {
            border-color: var(--apex-blue-mid);
        }

        .btn-qty-update {
            width: 34px;
            height: 34px;
            border: 1px solid var(--apex-border);
            background: var(--apex-grey);
            border-radius: 7px;
            color: var(--apex-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .2s;
            font-size: .78rem;
        }

        .btn-qty-update:hover {
            background: var(--apex-blue);
            border-color: var(--apex-blue);
            color: #fff;
        }

        /* ── REMOVE BTN ── */
        .btn-remove {
            width: 34px;
            height: 34px;
            border: 1px solid rgba(255, 59, 92, .2);
            background: rgba(255, 59, 92, .06);
            border-radius: 7px;
            color: #ff3b5c;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .2s;
            font-size: .78rem;
        }

        .btn-remove:hover {
            background: #ff3b5c;
            border-color: #ff3b5c;
            color: #fff;
        }

        /* ── EMPTY STATE ── */
        .empty-cart {
            padding: 68px 24px;
            text-align: center;
        }

        .empty-cart .empty-icon {
            width: 80px;
            height: 80px;
            background: var(--apex-grey);
            border: 1px solid var(--apex-border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
            font-size: 1.8rem;
            color: var(--apex-muted);
        }

        .empty-cart h4 {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--apex-text);
            margin-bottom: 8px;
        }

        .empty-cart p {
            color: var(--apex-muted);
            font-size: .88rem;
            margin-bottom: 26px;
        }

        /* ── BTN APEX (shop / continue) ── */
        .btn-apex-sm {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--apex-blue);
            color: #fff;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: .88rem;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background .2s, transform .2s;
        }

        .btn-apex-sm:hover {
            background: var(--apex-blue-mid);
            color: #fff;
            transform: translateY(-1px);
        }

        /* ── CONTINUE SHOPPING LINK ── */
        .continue-link {
            font-size: .82rem;
            color: var(--apex-muted);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color .18s;
        }

        .continue-link:hover {
            color: var(--apex-blue-mid);
        }

        /* ── SUMMARY CARD ── */
        .summary-card {
            background: #fff;
            border: 1px solid var(--apex-border);
            border-radius: 14px;
            padding: 26px;
            position: sticky;
            top: 20px;
        }

        .summary-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--apex-text);
            padding-bottom: 16px;
            border-bottom: 1px solid var(--apex-border);
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: .88rem;
        }

        .summary-row .lbl {
            color: var(--apex-muted);
        }

        .summary-row .val {
            font-weight: 600;
            color: var(--apex-text);
        }

        .summary-row .val.free {
            color: #10b981;
            font-weight: 700;
        }

        .summary-divider {
            border: none;
            border-top: 1px solid var(--apex-border);
            margin: 18px 0;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .summary-total .tot-lbl {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--apex-text);
        }

        .summary-total .tot-amt {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.65rem;
            font-weight: 900;
            color: var(--apex-blue);
        }

        /* ── CHECKOUT BTN ── */
        .btn-checkout {
            display: block;
            width: 100%;
            background: var(--apex-blue);
            color: #fff;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: .95rem;
            font-weight: 800;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            text-decoration: none;
            text-align: center;
            padding: 14px 20px;
            border-radius: 9px;
            border: none;
            cursor: pointer;
            transition: background .2s, box-shadow .2s, transform .2s;
            box-shadow: 0 4px 18px rgba(11, 47, 168, .25);
        }

        .btn-checkout:hover {
            background: var(--apex-blue-mid);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(11, 47, 168, .35);
        }

        .btn-checkout:disabled,
        .btn-checkout-disabled {
            background: var(--apex-grey);
            color: var(--apex-muted);
            box-shadow: none;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* ── SHIPPING BADGE ── */
        .shipping-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, .08);
            border: 1px solid rgba(16, 185, 129, .2);
            border-radius: 7px;
            padding: 9px 13px;
            font-size: .78rem;
            color: #10b981;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .shipping-badge i {
            font-size: .8rem;
        }

        /* ── SECURE NOTE ── */
        .secure-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-size: .72rem;
            color: var(--apex-muted);
            margin-top: 12px;
        }

        .secure-note i {
            color: #10b981;
        }

        /* ── COUPON SECTION ── */
        .coupon-section {
            margin-top: 16px;
        }

        .coupon-label {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: var(--apex-muted);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .coupon-label i {
            font-size: .68rem;
            color: var(--apex-blue-mid);
        }

        .coupon-input-wrap {
            display: flex;
            gap: 7px;
        }

        .coupon-input {
            flex: 1;
            height: 38px;
            border: 1px solid var(--apex-border);
            border-radius: 8px;
            background: var(--apex-grey);
            color: var(--apex-text);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: .92rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 0 12px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .coupon-input::placeholder {
            color: var(--apex-muted);
            font-weight: 400;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: .78rem;
        }

        .coupon-input:focus {
            border-color: var(--apex-blue-mid);
            box-shadow: 0 0 0 3px rgba(0, 100, 255, .08);
        }

        .coupon-btn {
            height: 38px;
            padding: 0 14px;
            background: var(--apex-blue);
            color: #fff;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: .82rem;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background .2s, transform .15s;
            white-space: nowrap;
        }

        .coupon-btn:hover {
            background: var(--apex-blue-mid);
            transform: translateY(-1px);
        }

        .coupon-note {
            font-size: .7rem;
            color: var(--apex-muted);
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .coupon-note i {
            font-size: .65rem;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 991.98px) {
            .summary-card {
                position: static;
                margin-top: 24px;
            }
        }

        @media (max-width: 768px) {

            .cart-table thead th:nth-child(3),
            .cart-table td:nth-child(3) {
                display: none;
            }

            .cart-page {
                padding: 28px 0 60px;
            }
        }

        @media (max-width: 575.98px) {
            .cart-header-inner {
                height: auto;
                min-height: 58px;
                gap: 12px;
                padding: 10px 0;
            }

            .cart-header-logo {
                min-width: 0;
                gap: 8px;
            }

            .cart-header-logo img {
                height: 34px;
            }

            .cart-header-logo .logo-wordmark {
                font-size: 1.35rem;
            }

            .cart-header-label {
                font-size: .64rem;
                letter-spacing: 1.4px;
                white-space: nowrap;
            }

            .cart-heading {
                font-size: 1.85rem;
            }

            .cart-subtext {
                margin-bottom: 22px;
            }

            .cart-card {
                border-radius: 12px;
            }

            .cart-table {
                min-width: 560px;
            }

            .cart-table td,
            .cart-table thead th {
                padding-left: 14px;
                padding-right: 14px;
            }

            .product-thumb {
                width: 54px;
                height: 54px;
            }

            .product-name {
                max-width: 160px;
                overflow-wrap: anywhere;
            }

            .qty-wrap {
                gap: 5px;
            }

            .qty-input {
                width: 48px;
            }

            .btn-qty-update,
            .btn-remove {
                width: 32px;
                height: 32px;
            }

            .summary-card {
                padding: 22px 20px;
                border-radius: 12px;
            }

            .summary-total .tot-amt {
                font-size: 1.42rem;
            }

            .coupon-input-wrap {
                flex-direction: column;
            }

            .coupon-btn,
            .coupon-input {
                width: 100%;
            }

            .mt-3.d-flex.justify-content-end {
                justify-content: center !important;
            }
        }
    </style>
</head>

<body>
    <?php include_once __DIR__ . '/includes/cookie_notif.php'; ?>

    <!-- ── STATIC HEADER BAR ── -->
    <div class="cart-header-bar">
        <div class="container">
            <div class="cart-header-inner">
                <a href="index.php" class="cart-header-logo">
                    <img src="assets/images/ApeX Logo.png" alt="ApeX Gear">
                    <span class="logo-wordmark">ApeX<span>Gear</span></span>
                </a>
                <span class="cart-header-label">Cart Items</span>
            </div>
        </div>
    </div>

    <!-- ── CART PAGE ── -->
    <div class="cart-page">
        <div class="container">

            <!-- Back button -->
            <button class="back-link" type="button" onclick="goBackFromCart(event)">
                <i class="fas fa-arrow-left"></i> Back
            </button>

            <!-- Heading -->
            <h2 class="cart-heading">Your <span>Shopping Cart</span></h2>
            <p class="cart-subtext">
                <?php if ($item_count > 0): ?>
                    <?php echo $item_count; ?> item<?php echo $item_count !== 1 ? 's' : ''; ?> in your cart
                <?php else: ?>
                    Your cart is currently empty
                <?php endif; ?>
            </p>

            <div class="row g-4">

                <!-- ── ITEMS ── -->
                <div class="col-lg-8">
                    <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
                        <div class="cart-card empty-cart">
                            <div class="empty-icon"><i class="fas fa-shopping-cart"></i></div>
                            <h4>No items yet</h4>
                            <p>You haven't added any premium gear yet.<br>Browse our collection and level up your setup.</p>
                            <a href="store.php" class="btn-apex-sm">
                                <i class="fas fa-store"></i> Browse Store
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="cart-card">
                            <div class="table-responsive">
                                <table class="cart-table">
                                    <thead>
                                        <tr>
                                            <th style="width:46%">Product</th>
                                            <th style="width:22%">Price</th>
                                            <th style="width:22%">Qty</th>
                                            <th style="width:10%; text-align:right; padding-right:24px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['cart'] as $id => $item):
                                            $effectivePrice = getEffectivePriceForCart($item);
                                            $originalPrice  = floatval($item['original_price'] ?? $item['price']);
                                            $isOnSale = $effectivePrice < $originalPrice;
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="product-thumb">
                                                            <?php
                                                            if (strpos($item['image'], '<svg') !== false) {
                                                                echo $item['image'];
                                                            } else {
                                                                echo '<img src="' . htmlspecialchars($item['image']) . '">';
                                                            }
                                                            ?>
                                                        </div>
                                                        <div>
                                                            <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                                            <div class="product-sub">Standard Edition</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="price-main">₱<?php echo number_format($effectivePrice, 2); ?></div>
                                                    <?php if ($isOnSale): ?>
                                                        <div class="price-original">₱<?php echo number_format($originalPrice, 2); ?></div>
                                                        <span class="badge-sale"><?php echo intval($item['discount_percent'] ?? $item['sale_percent'] ?? 0); ?>% OFF</span>
                                                        <?php if (!empty($item['sale_expiry'])): ?>
                                                            <div class="sale-timer sale-countdown-cart" data-expiry="<?php echo strtotime($item['sale_expiry']); ?>"></div>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form method="POST" action="cart.php" class="qty-wrap">
                                                        <input type="hidden" name="action" value="update">
                                                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                                        <input type="number" name="qty" value="<?php echo $item['qty']; ?>" class="qty-input" min="1" data-product-id="<?php echo (int)$id; ?>">
                                                        <button type="submit" class="btn-qty-update" title="Update quantity">
                                                            <i class="fas fa-sync-alt"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td style="text-align:right; padding-right:24px;">
                                                    <form method="POST" action="cart.php">
                                                        <input type="hidden" name="action" value="remove">
                                                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                                        <button type="submit" class="btn-remove" title="Remove item">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3 d-flex justify-content-end">
                            <a href="store.php" class="continue-link">
                                <i class="fas fa-arrow-left" style="font-size:.72rem;"></i> Continue Shopping
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ── ORDER SUMMARY ── -->
                <div class="col-lg-4">
                    <div class="summary-card">
                        <div class="summary-title">Order Summary</div>

                        <?php if ($item_count > 0): ?>
                            <div class="shipping-badge">
                                <i class="fas fa-truck"></i>
                                You are qualified for FREE shipping!
                            </div>
                        <?php endif; ?>

                        <div class="summary-row">
                            <span class="lbl">Subtotal (<?php echo $item_count; ?> item<?php echo $item_count !== 1 ? 's' : ''; ?>)</span>
                            <span class="val">₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <?php if ($appliedCoupon): ?>
                        <div class="summary-row" style="color:var(--apex-accent, #00c2ff);">
                            <span class="lbl">Promo (<?php echo htmlspecialchars($appliedCoupon['code']); ?> &minus;<?php echo (int)$appliedCoupon['discount']; ?>%)</span>
                            <span class="val">&minus;₱<?php echo number_format($couponDiscountAmount, 2); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="summary-row">
                            <span class="lbl">Estimated Tax (8%)</span>
                            <span class="val">₱<?php echo number_format($tax, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="lbl">Shipping</span>
                            <span class="val free"><i class="fas fa-check-circle" style="font-size:.72rem;margin-right:3px;"></i> FREE</span>
                        </div>

                        <!-- ── APPLY A COUPON ── -->
                        <div class="coupon-section">
                            <div class="coupon-label">
                                <i class="fas fa-tag"></i> Apply a Coupon
                            </div>

                            <?php if ($couponMsg): ?>
                                <div class="coupon-note" style="color: <?php echo $couponMsgType === 'success' ? '#009c60' : '#d62842'; ?>; margin-bottom:8px;">
                                    <i class="fas <?php echo $couponMsgType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i> <?php echo htmlspecialchars($couponMsg); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($appliedCoupon): ?>
                                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; background:rgba(0,194,255,.08); border:1px dashed rgba(0,194,255,.4); border-radius:8px; padding:8px 12px;">
                                    <span style="font-weight:700; font-size:.85rem;"><i class="fas fa-ticket-alt me-1"></i> <?php echo htmlspecialchars($appliedCoupon['code']); ?> applied</span>
                                    <form method="POST" action="cart.php" style="margin:0;">
                                        <input type="hidden" name="action" value="remove_coupon">
                                        <button type="submit" class="coupon-btn" style="padding:4px 10px; font-size:.75rem;">Remove</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <form method="POST" action="cart.php" class="coupon-input-wrap">
                                    <input type="hidden" name="action" value="apply_coupon">
                                    <input
                                        type="text"
                                        class="coupon-input"
                                        id="couponCode"
                                        name="coupon_code"
                                        placeholder="Enter code"
                                        maxlength="20"
                                        oninput="this.value = this.value.toUpperCase()"
                                        autocomplete="off"
                                        spellcheck="false">
                                    <button type="submit" class="coupon-btn">Apply</button>
                                </form>
                            <?php endif; ?>

                            <div class="coupon-note">
                                <i class="fas fa-info-circle"></i> Coupon codes are case-insensitive.
                            </div>
                        </div>

                        <hr class="summary-divider">

                        <div class="summary-total">
                            <span class="tot-lbl">Grand Total</span>
                            <span class="tot-amt">₱<?php echo number_format($grand_total, 2); ?></span>
                        </div>

                        <?php if ($item_count > 0): ?>
                            <form method="POST" action="cart.php" id="checkoutCartForm" style="margin:0;">
                                <input type="hidden" name="action" value="checkout">
                                <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                                    <input type="hidden" name="quantities[<?php echo (int)$id; ?>]" value="<?php echo (int)$item['qty']; ?>" data-checkout-product-id="<?php echo (int)$id; ?>">
                                <?php endforeach; ?>
                            </form>
                            <button type="submit" form="checkoutCartForm" class="btn-checkout">
                                <i class="fas fa-lock" style="font-size:.8rem; margin-right:5px;"></i>
                                Proceed to Checkout
                            </button>
                        <?php else: ?>
                            <span class="btn-checkout btn-checkout-disabled">Proceed to Checkout</span>
                        <?php endif; ?>

                        <div class="secure-note">
                            <i class="fas fa-shield-alt"></i>
                            AES 256-bit encrypted checkout
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <a href="#" id="btt"><i class="fas fa-arrow-up"></i></a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sale countdown
        function updateCartCountdowns() {
            document.querySelectorAll('.sale-countdown-cart[data-expiry]').forEach(el => {
                const exp = parseInt(el.dataset.expiry) * 1000;
                const diff = exp - Date.now();
                if (diff <= 0) {
                    el.textContent = 'Sale ended';
                    el.style.color = '#999';
                    return;
                }
                const d = Math.floor(diff / 86400000);
                const h = Math.floor((diff % 86400000) / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                if (d > 0) el.textContent = `⏱ ${d}d ${h}h ${m}m left`;
                else if (h > 0) el.textContent = `⏱ ${h}h ${m}m ${s}s left`;
                else el.textContent = `⏱ ${m}m ${s}s left`;
            });
        }
        updateCartCountdowns();
        setInterval(updateCartCountdowns, 1000);

        function getValidCartQty(input) {
            const qty = parseInt(input.value, 10);
            return Number.isFinite(qty) && qty > 0 ? qty : null;
        }

        function syncCheckoutQuantity(input) {
            const productId = input.dataset.productId;
            const qty = getValidCartQty(input);
            if (!productId || qty === null) return;

            const checkoutQty = document.querySelector(`[data-checkout-product-id="${productId}"]`);
            if (checkoutQty) {
                checkoutQty.value = qty;
            }
        }

        let qtyUpdateTimer = null;
        document.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('input', () => {
                syncCheckoutQuantity(input);

                clearTimeout(qtyUpdateTimer);
                if (getValidCartQty(input) === null) return;

                qtyUpdateTimer = setTimeout(() => {
                    const form = input.closest('form');
                    if (form) form.requestSubmit();
                }, 650);
            });

            input.addEventListener('change', () => {
                clearTimeout(qtyUpdateTimer);

                if (getValidCartQty(input) === null) {
                    input.value = input.min || 1;
                }

                syncCheckoutQuantity(input);
                const form = input.closest('form');
                if (form) form.requestSubmit();
            });
        });

        const checkoutCartForm = document.getElementById('checkoutCartForm');
        if (checkoutCartForm) {
            checkoutCartForm.addEventListener('submit', () => {
                document.querySelectorAll('.qty-input').forEach(syncCheckoutQuantity);
            });
        }

        function goBackFromCart(event) {
            event.preventDefault();
            const currentUrl = window.location.href;
            const referrer = document.referrer;

            if (referrer && referrer !== currentUrl && !referrer.includes('cart.php')) {
                window.location.href = referrer;
                return;
            }

            window.location.href = 'store.php';
        }
    </script>
</body>

</html>
