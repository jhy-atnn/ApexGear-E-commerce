<?php
session_start(); // 1. Manually start the session 

// 2. Handle POST actions (Updates & Removals)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $product_id = $_POST['product_id'] ?? 0;

    if ($_POST['action'] === 'remove') {
        unset($_SESSION['cart'][$product_id]);
    } elseif ($_POST['action'] === 'update') {
        $new_qty = (int)($_POST['qty'] ?? 0);
        if ($new_qty > 0) {
            $_SESSION['cart'][$product_id]['qty'] = $new_qty;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }

    // CRITICAL: This redirect MUST stay strictly inside this IF block to prevent infinite loops!
    header("Location: cart.php");
    exit();
}

// ── Helper: compute the effective (sale) price for a cart item ──────────────
// Fetches live sale data from DB and returns the currently applicable price.
function getEffectivePriceForCart($item) {
    // If the item has live sale data embedded (from add-to-cart action), use it;
    // otherwise fall back to the stored session price.
    $basePrice  = floatval($item['original_price'] ?? $item['price']);
    $salePct    = intval($item['sale_percent'] ?? 0);
    $saleExpiry = $item['sale_expiry'] ?? '';

    $saleActive = $salePct > 0 && (!empty($saleExpiry) ? strtotime($saleExpiry) > time() : true);
    if ($saleActive) {
        return round($basePrice * (1 - $salePct / 100), 2);
    }
    return $basePrice;
}

// 3. Calculate Totals using effective (live-sale) prices
$subtotal = 0;
$item_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $effectivePrice = getEffectivePriceForCart($item);
        $subtotal += ($effectivePrice * $item['qty']);
        $item_count += $item['qty'];
    }
}
$tax = $subtotal * 0.08;
$grand_total = $subtotal + $tax;
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
</head>

<body>

    <?php include 'includes\navbar.php'; ?>

    <section class="inner-page">
        <div class="container">

            <h2 class="sec-title mb-4">Your <span>Shopping Cart</span></h2>

            <div class="row g-5">

                <div class="col-lg-8">
                    <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
                        <div class="apex-card text-center p-5">
                            <div class="mb-4"><i class="fas fa-shopping-cart fa-4x text-muted opacity-50"></i></div>
                            <h4 class="text-muted mb-3">Your cart is currently empty.</h4>
                            <p class="text-muted mb-4">Looks like you haven't added any premium gear yet.</p>
                            <a href="store.php" class="btn-apex">Continue Shopping</a>
                        </div>
                    <?php else: ?>
                        <div class="apex-card p-0 overflow-hidden">
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th scope="col" style="width: 50%; padding-left: 20px;">Product</th>
                                            <th scope="col" style="width: 20%;">Price</th>
                                            <th scope="col" style="width: 20%;">Qty</th>
                                            <th scope="col" style="width: 10%; padding-right: 20px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['cart'] as $id => $item):
                                            $effectivePrice = getEffectivePriceForCart($item);
                                            $originalPrice  = floatval($item['original_price'] ?? $item['price']);
                                            $isOnSale = $effectivePrice < $originalPrice;
                                        ?>
                                            <tr class="border-bottom">
                                                <td class="py-4" style="padding-left: 20px;">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div style="width: 60px; height: 60px; border-radius: 8px; background: var(--apex-grey); display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                                            <?php
                                                            if (strpos($item['image'], '<svg') !== false) {
                                                                echo $item['image'];
                                                            } else {
                                                                echo '<img src="' . htmlspecialchars($item['image']) . '" style="max-width: 100%; object-fit: contain;">';
                                                            }
                                                            ?>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                            <small class="text-muted">Standard Edition</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="fw-bold text-apex-blue">
                                                    ₱<?php echo number_format($effectivePrice, 2); ?>
                                                    <?php if ($isOnSale): ?>
                                                        <br><small style="text-decoration:line-through;color:var(--apex-muted);font-weight:400;">₱<?php echo number_format($originalPrice, 2); ?></small>
                                                        <br><small style="color:#ff3b5c;font-weight:700;"><?php echo intval($item['sale_percent'] ?? 0); ?>% OFF</small>
                                                        <?php if (!empty($item['sale_expiry'])): ?>
                                                            <br><small class="sale-countdown-cart" data-expiry="<?php echo strtotime($item['sale_expiry']); ?>" style="color:#ff3b5c;font-size:.68rem;"></small>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form method="POST" action="cart.php" class="d-flex align-items-center gap-2">
                                                        <input type="hidden" name="action" value="update">
                                                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                                        <input type="number" name="qty" value="<?php echo $item['qty']; ?>" class="form-control form-control-sm text-center fw-bold bg-light" style="width: 60px;" min="1">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Update Qty"><i class="fas fa-sync-alt"></i></button>
                                                    </form>
                                                </td>
                                                <td class="text-end" style="padding-right: 20px;">
                                                    <form method="POST" action="cart.php">
                                                        <input type="hidden" name="action" value="remove">
                                                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                                        <button type="submit" class="btn btn-sm text-danger fw-bold border-0 bg-transparent"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <div class="apex-card overflow-hidden" style="top: 100px;">
                        <h5 class="fw-bold mb-4 font-monospace text-uppercase" style="font-family: 'Barlow Condensed', sans-serif; font-size: 1.4rem;">Order Summary</h5>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal (<?php echo $item_count; ?> items)</span>
                            <span class="fw-bold text-dark">₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Estimated Tax (8%)</span>
                            <span class="fw-bold text-dark">₱<?php echo number_format($tax, 2); ?></span>
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

                        <?php if ($item_count > 0): ?>
                            <a href="checkout.php" class="btn-apex w-100 text-center">Proceed to Checkout</a>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100 py-3 fw-bold font-monospace text-uppercase" style="font-family: 'Barlow Condensed', sans-serif;" disabled>Proceed to Checkout</button>
                        <?php endif; ?>

                        <div class="text-center mt-3">
                            <small class="text-muted"><i class="fas fa-lock text-success"></i> Secure AES 256-bit encryption</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <a href="#" id="btt"><i class="fas fa-arrow-up"></i></a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/navbar.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
    // ── Sale countdown in cart ────────────────────────────────────────────────
    function updateCartCountdowns() {
        document.querySelectorAll('.sale-countdown-cart[data-expiry]').forEach(el => {
            const exp = parseInt(el.dataset.expiry) * 1000;
            const diff = exp - Date.now();
            if (diff <= 0) { el.textContent = 'Sale ended'; el.style.color = '#999'; return; }
            const d = Math.floor(diff / 86400000);
            const h = Math.floor((diff % 86400000) / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            if (d > 0) el.textContent = `⏱ Sale: ${d}d ${h}h ${m}m left`;
            else if (h > 0) el.textContent = `⏱ Sale: ${h}h ${m}m ${s}s left`;
            else el.textContent = `⏱ Sale: ${m}m ${s}s left`;
        });
    }
    updateCartCountdowns();
    setInterval(updateCartCountdowns, 1000);
    </script>
</body>

</html>