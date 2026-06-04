<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $product_id = $_POST['product_id'];
    if ($_POST['action'] === 'remove') {
        unset($_SESSION['cart'][$product_id]);
    } elseif ($_POST['action'] === 'update') {
        $new_qty = (int)$_POST['qty'];
        if ($new_qty > 0) {
            $_SESSION['cart'][$product_id]['qty'] = $new_qty;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    header("Location: cart.php");
    exit();
}

$subtotal = 0;
$item_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += ($item['price'] * $item['qty']);
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

    <div class="topbar">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-phone-alt me-1"></i>
                <a href="tel:+1234567890">+1 (234) 567-890</a>
                <span class="mx-2">|</span>
                <i class="fas fa-envelope me-1"></i>
                <a href="mailto:support@apexgear.com">support@apexgear.com</a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span>Free shipping on orders over ₱5,000</span>
                <div class="search-wrap d-none d-md-block">
                    <input type="text" placeholder="Search products…" />
                    <button><i class="fas fa-search"></i></button>
                </div>
            </div>
        </div>
    </div>

    <nav id="mainNav" class="main-nav navbar navbar-expand-lg">
        <div class="container">
            <a href="index.php" class="brand me-4">
                <img src="assets/images/ApeX Logo.png" alt="ApeX Gear Logo" class="brand-logo-img">
                <div class="brand-text" style="color: white; margin-left:-10px">ApeX</div><div class="brand-text" style="color: #00c2ff; margin-left:-10px">Gear</div>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu"
                style="color:#fff; font-size:1.3rem;">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="mainMenu">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="store.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="deals.php">Deals</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
                </ul>
                <div class="nav-icons d-flex align-items-center">
                    <a href="#"><i class="fas fa-search"></i></a>
                    <a href="#favoritesOffcanvas" data-bs-toggle="offcanvas" role="button" aria-controls="favoritesOffcanvas" style="position:relative; cursor:pointer; color: rgba(255, 255, 255, .75);">
                        <i class="fas fa-heart"></i>
                        <span class="cart-badge" style="background: #ff3b5c; color: white;"><?php echo isset($_SESSION['favorites']) ? count($_SESSION['favorites']) : 0; ?></span>
                    </a>
                    <a href="#cartOffcanvas" data-bs-toggle="offcanvas" role="button" aria-controls="cartOffcanvas" style="position:relative; cursor:pointer;">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-badge"><?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0; ?></span>
                    </a>
                    <?php if (isset($_SESSION['user'])): ?>
                        <div class="profile-btn-wrap ms-3">
                            <button class="profile-btn" id="profileToggle" onclick="toggleProfilePanel(event)">
                                <span class="profile-avatar"><?php echo htmlspecialchars($_SESSION['user']['avatar']); ?></span>
                                <span class="profile-name d-none d-lg-inline"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></span>
                                <i class="fas fa-chevron-down ms-1" style="font-size:.65rem; opacity:.6;"></i>
                            </button>
                        </div>
                    <?php else: ?>
                        <a href="auth.php" class="btn-apex ms-3" style="padding:8px 20px; font-size:.82rem;">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
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
                                        <?php foreach ($_SESSION['cart'] as $id => $item): ?>
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
                                                    ₱<?php echo number_format($item['price'], 2); ?>
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

    <footer>
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-3">
                    <a href="index.php" class="footer-logo-brand">
                        <img src="assets/images/ApeX Logo.png" alt="ApeX Gear Logo" class="footer-logo-img">
                        <div class="footer-brand-text">
                            <div class="footer-brand">ApeX<span style="margin-left:2px">Gear</span></div>
                        </div>
                    </a>
                    <p style="font-size:.85rem; line-height:1.7; color:rgba(255,255,255,.45); margin-top:-28px">Your one-stop shop for laptops, desktops, cellphones, and premium accessories. Quality gear. Unbeatable prices.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="footer-heading">Useful Links</div>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="product.php">Products</a></li>
                        <li><a href="deals.php">Deals</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="footer-heading">Support</div>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Shipping Policy</a></li>
                        <li><a href="#">Returns</a></li>
                        <li><a href="#">Order Tracking</a></li>
                        <li><a href="#">Warranty</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="footer-heading">Contact Us</div>
                    <ul>
                        <li style="color:rgba(255,255,255,.45); font-size:.85rem;"><i class="fas fa-map-marker-alt me-2" style="color:var(--apex-accent);"></i>123 Tech Ave, Silicon City, PH</li>
                        <li><a href="tel:+1234567890"><i class="fas fa-phone-alt me-2" style="color:var(--apex-accent);"></i>+1 (234) 567-890</a></li>
                        <li><a href="mailto:support@apexgear.com"><i class="fas fa-envelope me-2" style="color:var(--apex-accent);"></i>support@apexgear.com</a></li>
                        <li style="color:rgba(255,255,255,.45); font-size:.85rem;"><i class="fas fa-clock me-2" style="color:var(--apex-accent);"></i>Mon–Sat: 9AM – 9PM</li>
                    </ul>
                    <div class="footer-heading mt-4">Accept Payment</div>
                    <div class="payment-icons">
                        <div class="pay-icon">VISA</div>
                        <div class="pay-icon">MC</div>
                        <div class="pay-icon">GCash</div>
                        <div class="pay-icon">PayMaya</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container d-flex flex-wrap justify-content-between align-items-center">
                <p>© 2026 ApeX Gear. All rights reserved.</p>
                <p style="margin:0;">Terms &nbsp;|&nbsp; Privacy &nbsp;|&nbsp; Cookies</p>
            </div>
        </div>
    </footer>

    <a href="#" id="btt"><i class="fas fa-arrow-up"></i></a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/navbar.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>