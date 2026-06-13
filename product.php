<?php
require_once __DIR__ . '/includes/storage.php';
require_once 'classes/Inventory.php';

// 1. Get the ID from the URL securely
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header("Location: store.php");
    exit();
}

// Use Inventory which will read from session fake DB (static mode)
/** @var Inventory $inv */
$inv = new Inventory();
$product = $inv->findProductById($product_id);

// If product doesn't exist, redirect back to store
if (!$product) {
    header("Location: store.php");
    exit();
}

// Determine which of this user's Completed orders containing this product
// are still eligible for a review (per-order review tracking).
$reviewableOrders = [];
if (isset($_SESSION['user']['id']) && method_exists($inv, 'getOrdersByUser')) {
    $reviewUserIdForList = intval($_SESSION['user']['id']);
    $allUserOrders = $inv->getOrdersByUser($reviewUserIdForList);
    foreach ($allUserOrders as $uo) {
        if (($uo['order_status'] ?? '') !== 'Completed') continue;
        $items = method_exists($inv, 'getOrderItems') ? $inv->getOrderItems($uo['order_id']) : [];
        foreach ($items as $item) {
            if (intval($item['product_id']) === $product_id) {
                $already = method_exists($inv, 'hasUserReviewedProductForOrder')
                    && $inv->hasUserReviewedProductForOrder($reviewUserIdForList, $product_id, intval($uo['order_id']));
                if (!$already) {
                    $reviewableOrders[] = $uo;
                }
                break;
            }
        }
    }
}
$selectedReviewOrderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$cartSuccess   = false;
$cartError     = false;
$reviewSuccess = false;
$reviewError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $qty    = (int)$_POST['quantity'];
    $stock  = isset($product['stock']) ? (int)$product['stock'] : 0;
    $isBuyNow = isset($_POST['buy_now']);

    $current_cart_qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id]['qty'] : 0;

    if (($current_cart_qty + $qty) <= $stock) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name'           => $product['name'],
                'price'          => $product['price'],      // original price (kept for backwards compat)
                'original_price' => $product['price'],      // explicit original price for sale calculation
                'sale_percent'   => $product['sale_percent'] ?? 0,
                'sale_expiry'    => $product['sale_expiry'] ?? '',
                'image'          => $product['image'],
                'qty'            => $qty
            ];
        }
        $cartSuccess = true;
        if ($isBuyNow) {
            header('Location: checkout.php');
            exit;
        }
    } else {
        $cartError = "Cannot add to cart. Only $stock left in stock.";
    }
}

// Handle submitted product review from completed order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_product_review'])) {
    if (!isset($_SESSION['user']['id'])) {
        $reviewError = 'Please log in before submitting a review.';
    } else {
        $reviewUserId  = intval($_SESSION['user']['id']);
        $reviewOrderId = intval($_POST['order_id'] ?? 0);
        $reviewRating  = intval($_POST['rating'] ?? 0);
        $reviewComment = trim($_POST['comment'] ?? '');

        if ($reviewRating < 1 || $reviewRating > 5) {
            $reviewError = 'Please choose a rating from 1 to 5 stars.';
        } elseif ($reviewOrderId <= 0) {
            $reviewError = 'Missing order reference for this review.';
        } else {
            // Verify this order belongs to the user, is Completed, and contains this product.
            $userOrders = method_exists($inv, 'getOrdersByUser') ? $inv->getOrdersByUser($reviewUserId) : [];
            $targetOrder = null;
            foreach ($userOrders as $userOrder) {
                if (intval($userOrder['order_id']) === $reviewOrderId) {
                    $targetOrder = $userOrder;
                    break;
                }
            }

            $hasCompletedPurchase = false;
            if ($targetOrder && ($targetOrder['order_status'] ?? '') === 'Completed') {
                $items = method_exists($inv, 'getOrderItems') ? $inv->getOrderItems($reviewOrderId) : [];
                foreach ($items as $item) {
                    if (intval($item['product_id']) === $product_id) {
                        $hasCompletedPurchase = true;
                        break;
                    }
                }
            }

            if (!$hasCompletedPurchase) {
                $reviewError = 'You can review this product after completing an order for it.';
            } elseif (method_exists($inv, 'hasUserReviewedProductForOrder') && $inv->hasUserReviewedProductForOrder($reviewUserId, $product_id, $reviewOrderId)) {
                $reviewError = 'You already submitted a review for this product on this order.';
            } else {
                require_once __DIR__ . '/database/db_connect.php';
                $db   = new Database();
                $conn = $db->getConnection();
                $stmt = $conn->prepare("INSERT INTO reviews_tbl (user_id, product_id, order_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiis", $reviewUserId, $product_id, $reviewOrderId, $reviewRating, $reviewComment);
                $reviewSuccess = $stmt->execute();
                $stmt->close();
                $db->closeConnection();

                if (!$reviewSuccess) {
                    $reviewError = 'Unable to save your review. Please try again.';
                }
            }
        }
    }
}

// 5. Static fake reviews
$reviews = [
    [
        'username'       => 'JohnR_Tech',
        'avatar_letter'  => 'J',
        'rating'         => 5,
        'date'           => 'May 28, 2025',
        'review_text'    => 'Absolutely worth every peso! Build quality is top-notch and performance exceeded my expectations. Highly recommend for anyone looking for a reliable upgrade.',
    ],
    [
        'username'       => 'MariaC',
        'avatar_letter'  => 'M',
        'rating'         => 5,
        'date'           => 'Apr 14, 2025',
        'review_text'    => 'Fast delivery and the product was exactly as described. ApeX Gear never disappoints. Already planning my next purchase!',
    ],
    [
        'username'       => 'Kevin_PH',
        'avatar_letter'  => 'K',
        'rating'         => 4,
        'date'           => 'Mar 30, 2025',
        'review_text'    => 'Great product overall. Setup was a breeze and it\'s been running flawlessly for weeks. Took one star off only because the box arrived slightly dented, but the item itself is perfect.',
    ],
    [
        'username'       => 'AngelaS',
        'avatar_letter'  => 'A',
        'rating'         => 5,
        'date'           => 'Mar 12, 2025',
        'review_text'    => 'Exceeded all my expectations! Super smooth and the free shipping was a nice bonus. Will definitely buy from ApeX Gear again.',
    ],
    [
        'username'       => 'DanteVR',
        'avatar_letter'  => 'D',
        'rating'         => 4,
        'date'           => 'Feb 22, 2025',
        'review_text'    => 'Solid buy for the price. Performance is great and it looks even better in person. Customer support was also very responsive when I had a question.',
    ],
    [
        'username'       => 'PaulM',
        'avatar_letter'  => 'P',
        'rating'         => 5,
        'date'           => 'Feb 10, 2025',
        'review_text'    => 'One of the best purchases I\'ve made this year. Exactly what I needed for work and gaming. Zero complaints!',
    ],
];

$total_reviews = count($reviews);
$rating_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$sum = 0;
foreach ($reviews as $rev) {
    $r = (int)$rev['rating'];
    $rating_counts[$r]++;
    $sum += $r;
}
$avg_rating = round($sum / $total_reviews, 1);

// 6. Fetch "You Might Also Like" — pick related products from Inventory
$related_products = [];
$all = $inv->getAllProducts();
foreach ($all as $p) {
    if ((int)$p['id'] === (int)$product_id) continue;
    $related_products[] = $p;
}
shuffle($related_products);
$related_products = array_slice($related_products, 0, 6);

// Helper: render star icons
/**
 * Render star icons for a rating value.
 *
 * @param int|float $rating Numeric rating (can be fractional)
 * @param int $max Maximum stars to render
 * @return string HTML string containing star icons
 */
function renderStars($rating, $max = 5) {
    $html = '';
    for ($i = 1; $i <= $max; $i++) {
        if ($rating >= $i) {
            $html .= '<i class="fas fa-star" style="color:#f5a623;"></i>';
        } elseif ($rating >= $i - 0.5) {
            $html .= '<i class="fas fa-star-half-alt" style="color:#f5a623;"></i>';
        } else {
            $html .= '<i class="far fa-star" style="color:#f5a623;"></i>';
        }
    }
    return $html;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/auth-styles-append.css" rel="stylesheet" />
    <style>
        /* ── Reviews Section ── */
        .section-heading {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--apex-dark, #0d1117);
        }

        .reviews-wrapper {
            background: #fff;
            border-radius: 16px;
            border: 1px solid var(--apex-border, #e8ecf0);
            padding: 2rem;
        }

        /* Big score */
        .rating-score {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 5rem;
            font-weight: 900;
            line-height: 1;
            color: var(--apex-dark, #0d1117);
        }

        .rating-score-sub {
            font-size: .85rem;
            color: var(--apex-muted, #8a9aaa);
            font-weight: 600;
        }

        /* Bar rows */
        .rating-bar-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .82rem;
            font-weight: 600;
            color: var(--apex-muted, #8a9aaa);
        }

        .rating-bar-row .progress {
            flex: 1;
            height: 7px;
            border-radius: 99px;
            background: #f0f2f5;
        }

        .rating-bar-row .progress-bar {
            background: #f5a623;
            border-radius: 99px;
        }

        .rating-bar-row .bar-count {
            width: 20px;
            text-align: right;
            color: var(--apex-dark, #0d1117);
        }

        /* Review cards */
        .review-card {
            background: #fafbfc;
            border: 1px solid var(--apex-border, #e8ecf0);
            border-radius: 12px;
            padding: 1.2rem 1.4rem;
        }

        .reviewer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--apex-blue, #00c2ff);
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .reviewer-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ── Description & Shipping Info Tabs ── */
        .info-tabs-section {
            background: #fff;
            border-radius: 16px;
            border: 1px solid var(--apex-border, #e8ecf0);
            overflow: hidden;
        }

        .info-tab-header {
            display: flex;
            border-bottom: 1px solid var(--apex-border, #e8ecf0);
            background: #f7f8fa;
        }

        .info-tab-btn {
            flex: 1;
            padding: .85rem 1.5rem;
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            border: none;
            background: transparent;
            color: var(--apex-muted, #8a9aaa);
            cursor: pointer;
            transition: color .2s, background .2s;
            border-bottom: 3px solid transparent;
            margin-bottom: -1px;
        }

        .info-tab-btn.active {
            color: var(--apex-dark, #0d1117);
            background: #fff;
            border-bottom-color: var(--apex-blue, #00c2ff);
        }

        .info-tab-pane {
            display: none;
            padding: 1.5rem;
        }

        .info-tab-pane.active {
            display: block;
        }

        .info-tab-pane p {
            color: #555;
            font-size: .95rem;
            line-height: 1.7;
            margin: 0;
        }

        .shipping-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .shipping-item {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
        }

        .shipping-item-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #555;
            font-size: .9rem;
        }

        .shipping-item-label {
            font-size: .72rem;
            color: var(--apex-muted, #8a9aaa);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: .15rem;
        }

        .shipping-item-value {
            font-weight: 700;
            font-size: .9rem;
            color: var(--apex-dark, #0d1117);
        }

        /* ── Related Products ── */
        /* ── Related Products Slider ── */
        .related-slider-wrapper {
            padding: 0 2.5rem;
        }

        .related-slider-track {
            display: flex;
            gap: 1rem;
            overflow: hidden;
            scroll-behavior: smooth;
        }

        .related-slider-item {
            flex: 0 0 calc(25% - .75rem);
            min-width: 0;
        }

        @media (max-width: 991px) {
            .related-slider-item { flex: 0 0 calc(33.333% - .67rem); }
        }

        @media (max-width: 767px) {
            .related-slider-item { flex: 0 0 calc(50% - .5rem); }
        }

        @media (max-width: 479px) {
            .related-slider-item { flex: 0 0 calc(100%); }
        }

        .related-slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid var(--apex-border, #e8ecf0);
            background: #fff;
            color: var(--apex-dark, #0d1117);
            font-size: .85rem;
            cursor: pointer;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            transition: background .2s, color .2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .related-slider-btn:hover {
            background: var(--apex-blue, #00c2ff);
            color: #fff;
            border-color: var(--apex-blue, #00c2ff);
        }

        .related-slider-prev { left: 0; }
        .related-slider-next { right: 0; }

        .related-section {
            background: #f7f8fa;
            padding: 3.5rem 0;
        }

        .related-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid var(--apex-border, #e8ecf0);
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
            text-decoration: none;
            display: block;
        }

        .related-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,.09);
        }

        .related-card-img {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f2f5;
            padding: 1rem;
        }

        .related-card-img img {
            max-height: 150px;
            object-fit: contain;
        }

        .related-card-body {
            padding: 1rem 1.2rem 1.2rem;
        }

        .related-card-name {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            color: var(--apex-dark, #0d1117);
            line-height: 1.3;
            margin-bottom: .4rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .related-card-price {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--apex-blue, #00c2ff);
        }

        .related-card-stars {
            font-size: .72rem;
            color: #f5a623;
        }

        @media (max-width: 575px) {
            .rating-score { font-size: 3.5rem; }
            .reviews-wrapper { padding: 1.2rem; }
        }
    </style>
</head>

<body>
    <?php include_once __DIR__ . '/includes/cookie_notif.php'; ?>

    <!-- INJECT NAVBAR -->
    <?php
    $currentPage = 'product';
    include 'includes/navbar.php';
    ?>

    <!-- ── Product Detail ── -->
    <section class="inner-page" style="padding-top: 40px; padding-bottom: 20px;">
        <div class="container">

            <?php if ($cartSuccess): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-white" role="alert"
                    style="border-left: 5px solid var(--apex-accent) !important;">
                    <i class="fas fa-check-circle text-apex-accent me-2"></i>
                    <strong>Added to Cart!</strong> <?php echo htmlspecialchars($product['name']); ?> is ready for checkout.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($cartError): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 bg-white" role="alert"
                    style="border-left: 5px solid #ff3b5c !important;">
                    <i class="fas fa-exclamation-circle text-danger me-2"></i>
                    <strong>Error!</strong> <?php echo $cartError; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($reviewSuccess): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-white" role="alert" id="submit-review">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    <strong>Review submitted!</strong> You can now delete the matching completed order status entry.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif (!empty($reviewError)): ?>
                <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 bg-white" role="alert" id="submit-review">
                    <i class="fas fa-exclamation-circle text-warning me-2"></i>
                    <strong>Review not submitted.</strong> <?php echo htmlspecialchars($reviewError); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-5 align-items-center apex-card">

                <!-- Product Image -->
                <div class="col-md-6 text-center">
                    <div class="p-4 rounded-4 d-flex align-items-center justify-content-center"
                        style="min-height: 400px;">
                        <?php
                        if (strpos($product['image'], '<svg') !== false) {
                            echo $product['image'];
                        } else {
                            echo '<img src="' . htmlspecialchars($product['image']) . '" alt="img" class="img-fluid" style="max-height: 350px; object-fit: contain;">';
                        }
                        ?>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb small text-uppercase fw-bold">
                            <li class="breadcrumb-item">
                                <a href="store.php" class="text-decoration-none text-muted">Store</a>
                            </li>
                            <li class="breadcrumb-item active text-apex-accent" aria-current="page">
                                <?php echo htmlspecialchars($product['category'] ?? 'Hardware'); ?>
                            </li>
                        </ol>
                    </nav>

                    <h1 class="display-5 fw-bold mb-3 text-apex-dark"
                        style="font-family: 'Barlow Condensed', sans-serif; text-transform: uppercase;">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h1>

                    <!-- Inline avg stars under title if reviews exist -->
                    <?php if ($total_reviews > 0): ?>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div><?php echo renderStars($avg_rating); ?></div>
                            <span class="fw-bold" style="font-size:.9rem;"><?php echo $avg_rating; ?></span>
                            <span class="text-muted small">(<?php echo $total_reviews; ?> review<?php echo $total_reviews !== 1 ? 's' : ''; ?>)</span>
                            <a href="#reviews" class="text-muted small text-decoration-underline">See all</a>
                        </div>
                    <?php endif; ?>

                    <h2 class="text-apex-blue fw-bold mb-4"
                        style="font-family: 'Barlow Condensed', sans-serif; font-size: 2.5rem;">
                        ₱<?php echo number_format($product['price'], 2); ?>
                    </h2>

                    <hr class="mb-4 border-secondary">

                    <form method="POST" action="product.php?id=<?php echo $product_id; ?>">
                        <input type="hidden" name="add_to_cart" value="1">

                        <?php $stock = isset($product['stock']) ? (int)$product['stock'] : 0; ?>

                        <div class="row g-3 align-items-end mb-4">
                            <div class="col-4 col-md-3">
                                <label class="form-label fw-bold text-muted small text-uppercase">Quantity</label>
                                <input type="number" name="quantity"
                                    class="form-control form-control-lg text-center bg-light fw-bold"
                                    value="<?php echo $stock > 0 ? 1 : 0; ?>"
                                    min="1" max="<?php echo $stock; ?>"
                                    <?php echo $stock <= 0 ? 'disabled' : ''; ?>>
                            </div>
                            <div class="col-8 col-md-9">
                                <label class="form-label fw-bold text-muted small text-uppercase">Configuration</label>
                                <select class="form-select form-select-lg bg-light fw-bold text-dark"
                                    <?php echo $stock <= 0 ? 'disabled' : ''; ?>>
                                    <option>Standard Edition</option>
                                    <option>Pro Edition (+₱100)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 text-muted small">
                            Estimated shipping: <?php echo htmlspecialchars($product['shipping_time'] ?? '1-3 business days'); ?>
                        </div>
                        <div class="d-flex gap-2 mb-3">
                            <?php if ($stock > 0): ?>
                                <button type="submit" name="buy_now" value="1" class="btn-apex btn-lg py-3 flex-grow-1 text-center">
                                    Buy Now <i class="fas fa-bolt ms-2"></i>
                                </button>
                                <button type="submit" class="btn-apex btn-lg py-3 flex-grow-1 text-center">
                                    Add to Cart <i class="fas fa-shopping-cart ms-2"></i>
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn-apex btn-lg py-3 flex-grow-1 text-center" disabled
                                    style="background: var(--apex-muted); box-shadow: none; cursor: not-allowed; border-color: transparent; color: white;">
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>

                    <form method="POST" action="actions/favorites_action.php" class="m-0">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                        <button type="submit"
                            class="btn btn-light btn-lg py-3 px-4 border bg-white"
                            style="border-color: var(--apex-border) !important;" title="Favorites">
                            <?php if (isset($_SESSION['favorites'][$product_id])): ?>
                                <i class="fas fa-heart" style="color: #ff3b5c;"></i>
                            <?php else: ?>
                                <i class="far fa-heart" style="color: var(--apex-muted);"></i>
                            <?php endif; ?>
                        </button>
                    </form>
                </div>

        </div>
        </div><!-- end .container / .row -->
    </section>

    <!-- ═══════════════════════════════════════
         DESCRIPTION & SHIPPING INFO
    ═══════════════════════════════════════ -->
    <section class="py-5" style="background:#f7f8fa;">
        <div class="container">
            <div class="row g-4">
                <!-- Description & Fit -->
                <div class="col-md-6">
                    <div class="info-tabs-section">
                        <div class="info-tab-header">
                            <button class="info-tab-btn active" onclick="switchTab(this,'desc-pane')">Description &amp; Fit</button>
                        </div>
                        <div id="desc-pane" class="info-tab-pane active">
                            <p><?php echo isset($product['desc']) && !empty($product['desc'])
                                ? htmlspecialchars($product['desc'])
                                : 'Experience next-level performance. This gear is engineered for ultimate speed, precision, and durability — built to help you play harder, work smarter, and stay ahead of the curve. Equip yourself with the best.'; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="col-md-6">
                    <div class="info-tabs-section">
                        <div class="info-tab-header">
                            <button class="info-tab-btn active" onclick="switchTab(this,'ship-pane')">Additional Info</button>
                        </div>
                        <div id="ship-pane" class="info-tab-pane active">
                            <div class="shipping-grid">
                                <div class="shipping-item">
                                    <div class="shipping-item-icon">
                                        <?php if ($stock > 0): ?>
                                            <i class="fas fa-check" style="color:#28a745;"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times" style="color:#ff3b5c;"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="shipping-item-label">Stock</div>
                                        <div class="shipping-item-value">
                                            <?php echo $stock > 0 ? $stock . ' In Stock' : 'Out of Stock'; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="shipping-item">
                                    <div class="shipping-item-icon"><i class="fas fa-truck"></i></div>
                                    <div>
                                        <div class="shipping-item-label">Shipping</div>
                                        <div class="shipping-item-value">Free Shipping</div>
                                    </div>
                                </div>
                                <div class="shipping-item">
                                    <div class="shipping-item-icon"><i class="fas fa-clock"></i></div>
                                    <div>
                                        <div class="shipping-item-label">Est. Shipping</div>
                                        <div class="shipping-item-value"><?php echo htmlspecialchars($product['shipping_time'] ?? '1-3 business days'); ?></div>
                                    </div>
                                </div>
                                <div class="shipping-item">
                                    <div class="shipping-item-icon"><i class="fas fa-shield-alt"></i></div>
                                    <div>
                                        <div class="shipping-item-label">Warranty</div>
                                        <div class="shipping-item-value">1-Year Warranty</div>
                                    </div>
                                </div>
                                <div class="shipping-item">
                                    <div class="shipping-item-icon"><i class="fas fa-tag"></i></div>
                                    <div>
                                        <div class="shipping-item-label">Category</div>
                                        <div class="shipping-item-value">
                                            <?php echo htmlspecialchars($product['category'] ?? 'N/A'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════
         RATINGS & REVIEWS SECTION
    ═══════════════════════════════════════ -->
    <section id="reviews" class="py-5" style="background:#f7f8fa;">
        <div class="container">

            <h2 class="section-heading mb-4">Ratings &amp; Reviews</h2>

            <div class="row g-4">

                <!-- LEFT: Score summary -->
                <div class="col-lg-4">
                    <div class="reviews-wrapper" style="height:fit-content;">

                        <?php if ($total_reviews > 0): ?>
                            <div class="d-flex align-items-flex-start gap-4 mb-4">
                                <div>
                                    <div class="rating-score"><?php echo $avg_rating; ?></div>
                                    <div class="mb-1"><?php echo renderStars($avg_rating); ?></div>
                                    <div class="rating-score-sub"><?php echo $total_reviews; ?> Review<?php echo $total_reviews !== 1 ? 's' : ''; ?></div>
                                </div>
                            </div>

                            <!-- Bar breakdown -->
                            <div class="d-flex flex-column gap-2">
                                <?php for ($s = 5; $s >= 1; $s--): ?>
                                    <?php
                                    $pct = $total_reviews > 0 ? round(($rating_counts[$s] / $total_reviews) * 100) : 0;
                                    ?>
                                    <div class="rating-bar-row">
                                        <i class="fas fa-star" style="color:#f5a623; font-size:.75rem;"></i>
                                        <span><?php echo $s; ?></span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width:<?php echo $pct; ?>%"></div>
                                        </div>
                                        <span class="bar-count"><?php echo $rating_counts[$s]; ?></span>
                                    </div>
                                <?php endfor; ?>
                            </div>

                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="far fa-star fa-3x mb-3" style="color:#ddd;"></i>
                                <p class="fw-bold text-muted mb-1">No reviews yet</p>
                                <p class="text-muted small">Be the first to share your thoughts!</p>
                            </div>
                        <?php endif; ?>

                    </div><!-- end reviews-wrapper -->
                </div>

                <!-- RIGHT: Review cards -->
                <div class="col-lg-8">

                    <!-- Submit a Review -->
                    <div class="review-card mb-4" id="submit-review">
                        <h5 class="fw-bold text-dark mb-3" style="font-family:'Barlow Condensed', sans-serif; text-transform:uppercase;">
                            Submit a Review
                        </h5>
                        <?php if (empty($reviewableOrders)): ?>
                            <p class="text-muted small mb-0">
                                You can review this product after completing an order for it
                                (and once you haven't already reviewed it for that order).
                            </p>
                        <?php else: ?>
                        <form method="POST" action="product.php?id=<?php echo $product_id; ?>#submit-review">
                            <input type="hidden" name="submit_product_review" value="1">

                            <?php if (count($reviewableOrders) === 1): ?>
                                <input type="hidden" name="order_id" value="<?php echo intval($reviewableOrders[0]['order_id']); ?>">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Reviewing order</label>
                                    <div class="form-control-plaintext small fw-semibold">
                                        Ref: <?php echo htmlspecialchars($reviewableOrders[0]['reference_number'] ?? $reviewableOrders[0]['order_id']); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Which order is this review for?</label>
                                    <select class="form-select" name="order_id" required>
                                        <option value="">Choose order</option>
                                        <?php foreach ($reviewableOrders as $ro): ?>
                                            <option value="<?php echo intval($ro['order_id']); ?>"
                                                <?php echo ($selectedReviewOrderId === intval($ro['order_id'])) ? 'selected' : ''; ?>>
                                                Ref: <?php echo htmlspecialchars($ro['reference_number'] ?? $ro['order_id']); ?>
                                                (<?php echo date('M d, Y', strtotime($ro['created_at'])); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Rating</label>
                                <select class="form-select" name="rating" required>
                                    <option value="">Choose rating</option>
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Comment</label>
                                <textarea class="form-control" name="comment" rows="4"
                                          placeholder="Share your experience with this product"></textarea>
                            </div>

                            <button type="submit" class="btn-apex px-4 py-2">Submit Review</button>
                        </form>
                        <?php endif; ?>
                    </div>

                    <!-- First 5 reviews -->
                    <div class="d-flex flex-column gap-3 mb-4">
                        <?php foreach (array_slice($reviews, 0, 5) as $rev): ?>
                            <div class="review-card">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="reviewer-avatar">
                                        <?php echo htmlspecialchars($rev['avatar_letter']); ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark" style="font-size:.9rem;">
                                            <?php echo htmlspecialchars($rev['username']); ?>
                                        </div>
                                        <div style="font-size:.72rem;"><?php echo renderStars((int)$rev['rating']); ?></div>
                                    </div>
                                    <div class="text-muted" style="font-size:.78rem; white-space:nowrap;">
                                        <?php echo htmlspecialchars($rev['date']); ?>
                                    </div>
                                </div>
                                <p class="mb-0 text-muted" style="font-size:.9rem; line-height:1.6;">
                                    <?php echo htmlspecialchars($rev['review_text']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($total_reviews > 5): ?>
                        <button class="btn btn-outline-secondary rounded-pill px-4 py-2 mb-4"
                            style="font-size:.85rem; font-weight:600;"
                            id="loadMoreReviews">
                            Show more reviews (<?php echo $total_reviews - 5; ?> more)
                        </button>

                        <div id="extraReviews" class="d-flex flex-column gap-3 mb-4" style="display:none!important;">
                            <?php foreach (array_slice($reviews, 5) as $rev): ?>
                                <div class="review-card">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="reviewer-avatar">
                                            <?php echo htmlspecialchars($rev['avatar_letter']); ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark" style="font-size:.9rem;">
                                                <?php echo htmlspecialchars($rev['username']); ?>
                                            </div>
                                            <div style="font-size:.72rem;"><?php echo renderStars((int)$rev['rating']); ?></div>
                                        </div>
                                        <div class="text-muted" style="font-size:.78rem; white-space:nowrap;">
                                            <?php echo htmlspecialchars($rev['date']); ?>
                                        </div>
                                    </div>
                                    <p class="mb-0 text-muted" style="font-size:.9rem; line-height:1.6;">
                                        <?php echo htmlspecialchars($rev['review_text']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>



                </div>
            </div><!-- end .row -->
        </div>
    </section>

    <!-- ═══════════════════════════════════════
         YOU MIGHT ALSO LIKE
    ═══════════════════════════════════════ -->
    <?php if (!empty($related_products)): ?>
        <section class="related-section">
            <div class="container">
                <h2 class="section-heading text-center mb-4">You Might Also Like</h2>

                <div class="related-slider-wrapper position-relative">
                    <!-- Prev button -->
                    <button class="related-slider-btn related-slider-prev" id="relPrev" aria-label="Previous">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <div class="related-slider-track" id="relTrack">
                        <?php foreach ($related_products as $rel): ?>
                            <div class="related-slider-item">
                                <a href="product.php?id=<?php echo $rel['product_id']; ?>" class="related-card">
                                    <div class="related-card-img">
                                        <?php
                                        if (strpos($rel['image'], '<svg') !== false) {
                                            echo $rel['image'];
                                        } else {
                                            echo '<img src="' . htmlspecialchars($rel['image']) . '" alt="' . htmlspecialchars($rel['name']) . '">';
                                        }
                                        ?>
                                    </div>
                                    <div class="related-card-body">
                                        <div class="related-card-name"><?php echo htmlspecialchars($rel['name']); ?></div>
                                        <div class="related-card-price">₱<?php echo number_format($rel['price'], 2); ?></div>
                                        <?php if (isset($rel['stock']) && (int)$rel['stock'] <= 0): ?>
                                            <span class="badge mt-1" style="background:#ff3b5c; font-size:.7rem;">Out of Stock</span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Next button -->
                    <button class="related-slider-btn related-slider-next" id="relNext" aria-label="Next">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ── Footer (external) ── -->
    <?php include_once __DIR__ . '/includes/footer.php'; ?>

    <!-- ── Load More Reviews JS ── -->
    <script>
        const loadMoreBtn = document.getElementById('loadMoreReviews');
        const extraReviews = document.getElementById('extraReviews');
        if (loadMoreBtn && extraReviews) {
            loadMoreBtn.addEventListener('click', function () {
                extraReviews.style.setProperty('display', 'flex', 'important');
                extraReviews.style.flexDirection = 'column';
                loadMoreBtn.style.display = 'none';
            });
        }
    </script>

    <!-- ── Info Tabs JS ── -->
    <script>
        function switchTab(btn, paneId) {
            const section = btn.closest('.info-tabs-section');
            section.querySelectorAll('.info-tab-btn').forEach(b => b.classList.remove('active'));
            section.querySelectorAll('.info-tab-pane').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(paneId).classList.add('active');
        }
    </script>

    <!-- ── Related Slider JS ── -->
    <script>
        (function () {
            const track = document.getElementById('relTrack');
            const prevBtn = document.getElementById('relPrev');
            const nextBtn = document.getElementById('relNext');
            if (!track) return;

            function getVisibleCount() {
                const w = window.innerWidth;
                if (w <= 479) return 1;
                if (w <= 767) return 2;
                if (w <= 991) return 3;
                return 4;
            }

            function getItemWidth() {
                const item = track.querySelector('.related-slider-item');
                if (!item) return 0;
                return item.offsetWidth + parseInt(getComputedStyle(track).gap || 16);
            }

            let currentIndex = 0;

            function totalItems() {
                return track.querySelectorAll('.related-slider-item').length;
            }

            function slide(dir) {
                const visible = getVisibleCount();
                const max = Math.max(0, totalItems() - visible);
                currentIndex = Math.min(max, Math.max(0, currentIndex + dir));
                track.style.transform = `translateX(-${currentIndex * getItemWidth()}px)`;
                track.style.transition = 'transform .35s ease';
                prevBtn.disabled = currentIndex === 0;
                nextBtn.disabled = currentIndex >= max;
            }

            // Make track use transform instead of overflow
            track.style.overflow = 'visible';
            track.parentElement.style.overflow = 'hidden';

            prevBtn.addEventListener('click', () => slide(-1));
            nextBtn.addEventListener('click', () => slide(1));

            window.addEventListener('resize', () => slide(0));
            slide(0);
        })();
    </script>

</body>
</html>
