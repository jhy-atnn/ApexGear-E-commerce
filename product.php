<?php
session_start();
require_once 'classes/Inventory.php';

$inventoryManager = new Inventory();
$products = $inventoryManager->getAllProducts();

// 1. Check if an ID was passed in the URL
if (isset($_GET['id']) && array_key_exists($_GET['id'], $products)) {
    $product_id = $_GET['id'];
    $product = $products[$product_id];
} else {
    // If no valid ID is found, send them back to the store
    header("Location: store.php");
    exit();
}

// 2. Handle "Add to Cart" Submission
$cartSuccess = false;
$cartError = false; // Add an error variable

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $qty = (int)$_POST['quantity'];
    $stock = isset($product['stock']) ? (int)$product['stock'] : 0;

    // Calculate how many of this item are ALREADY in the cart
    $current_cart_qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id]['qty'] : 0;

    // Check if adding this new quantity exceeds available stock
    if (($current_cart_qty + $qty) <= $stock) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'qty' => $qty
            ];
        }
        $cartSuccess = true;
    } else {
        $cartError = "Cannot add to cart. Only $stock left in stock.";
    }
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
</head>

<body>
<?php include_once __DIR__ . '/cookie_notif.php'; ?>

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
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="store.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="deals.php">Deals</a></li>
                 
                    <li class="nav-item"><a class="nav-link" href="about">About Us</a></li>
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

            <?php if ($cartSuccess): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-white" role="alert" style="border-left: 5px solid var(--apex-accent) !important;">
                    <i class="fas fa-check-circle text-apex-accent me-2"></i>
                    <strong>Added to Cart!</strong> <?php echo htmlspecialchars($product['name']); ?> is ready for checkout.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($cartError): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 bg-white" role="alert" style="border-left: 5px solid #ff3b5c !important;">
                    <i class="fas fa-exclamation-circle text-danger me-2"></i>
                    <strong>Error!</strong> <?php echo $cartError; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-5 align-items-center apex-card">

                <div class="col-md-6 text-center">
                    <div class="p-4 rounded-4 d-flex align-items-center justify-content-center" style="min-height: 400px;">
                        <?php
                        if (strpos($product['image'], '<svg') !== false) {
                            echo $product['image'];
                        } else {
                            echo '<img src="' . htmlspecialchars($product['image']) . '" alt="img" class="img-fluid" style="max-height: 350px; object-fit: contain;">';
                        }
                        ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb small text-uppercase fw-bold">
                            <li class="breadcrumb-item"><a href="store.php" class="text-decoration-none text-muted">Store</a></li>
                            <li class="breadcrumb-item active text-apex-accent" aria-current="page">Hardware</li>
                        </ol>
                    </nav>

                    <h1 class="display-5 fw-bold mb-3 text-apex-dark" style="font-family: 'Barlow Condensed', sans-serif; text-transform: uppercase;">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h1>

                    <h2 class="text-apex-blue fw-bold mb-4" style="font-family: 'Barlow Condensed', sans-serif; font-size: 2.5rem;">
                        ₱<?php echo number_format($product['price'], 2); ?>
                    </h2>

                    <p class="text-muted lead mb-4" style="font-size: 1.1rem;">
                        <?php echo isset($product['desc']) && !empty($product['desc']) ? htmlspecialchars($product['desc']) : 'Experience next-level performance. This gear is engineered for ultimate speed, precision, and durability. Equip yourself with the best.'; ?>
                    </p>

                    <hr class="mb-4 border-secondary">

                    <form method="POST" action="product.php?id=<?php echo $product_id; ?>">
                        <input type="hidden" name="add_to_cart" value="1">

                        <?php $stock = isset($product['stock']) ? (int)$product['stock'] : 0; ?>

                        <div class="row g-3 align-items-end mb-4">
                            <div class="col-4 col-md-3">
                                <label class="form-label fw-bold text-muted small text-uppercase">Quantity</label>
                                <input type="number" name="quantity" class="form-control form-control-lg text-center bg-light fw-bold"
                                    value="<?php echo $stock > 0 ? 1 : 0; ?>" min="1" max="<?php echo $stock; ?>"
                                    <?php echo $stock <= 0 ? 'disabled' : ''; ?>>
                            </div>
                            <div class="col-8 col-md-9">
                                <label class="form-label fw-bold text-muted small text-uppercase">Configuration</label>
                                <select class="form-select form-select-lg bg-light fw-bold text-dark" <?php echo $stock <= 0 ? 'disabled' : ''; ?>>
                                    <option>Standard Edition</option>
                                    <option>Pro Edition (+₱100)</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <?php if ($stock > 0): ?>
                                <button type="submit" class="btn-apex btn-lg py-3 flex-grow-1 text-center">
                                    Add to Cart <i class="fas fa-shopping-cart ms-2"></i>
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn-apex btn-lg py-3 flex-grow-1 text-center" disabled style="background: var(--apex-muted); box-shadow: none; cursor: not-allowed; border-color: transparent; color: white;">
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                    </form>

                    <form method="POST" action="favorites_action.php" class="m-0">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                        <button type="submit" class="btn btn-light btn-lg py-3 px-4 border bg-white" style="border-color: var(--apex-border) !important;" title="Favorites">
                            <?php if (isset($_SESSION['favorites'][$product_id])): ?>
                                <i class="fas fa-heart" style="color: #ff3b5c;"></i>
                            <?php else: ?>
                                <i class="far fa-heart" style="color: var(--apex-muted);"></i>
                            <?php endif; ?>
                        </button>
                    </form>
                </div>

                <div class="d-flex gap-4 mt-4 text-muted small fw-bold">
                    <span>
                        <?php if ($stock > 0): ?>
                            <i class="fas fa-check text-success me-1"></i> <?php echo $stock; ?> In Stock
                        <?php else: ?>
                            <i class="fas fa-times text-danger me-1"></i> <span class="text-danger">Out of Stock</span>
                        <?php endif; ?>
                    </span>
                    <span><i class="fas fa-truck text-success me-1"></i> Free Shipping</span>
                    <span><i class="fas fa-shield-alt text-success me-1"></i> 1-Year Warranty</span>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/navbar.js"></script>
    <script src="assets/js/main.js"></script>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel" style="width: 400px; border-left: none; box-shadow: -4px 0 24px rgba(0,0,0,0.1);">
        <div class="offcanvas-header border-bottom pb-3 pt-4 px-4">
            <h6 class="offcanvas-title fw-bold text-dark d-flex align-items-center" id="cartOffcanvasLabel">
                <i class="fas fa-shopping-bag me-2" style="color: var(--apex-blue);"></i> Cart Summary
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
                    foreach ($_SESSION['cart'] as $id => $item):
                        $subtotal += ($item['price'] * $item['qty']);
                    ?>
                        <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                            <div style="width: 50px; height: 50px; border-radius: 6px; background: var(--apex-grey); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink: 0;">
                                <?php
                                if (strpos($item['image'], '<svg') !== false) {
                                    echo $item['image'];
                                } else {
                                    echo '<img src="' . htmlspecialchars($item['image']) . '" style="max-width: 100%; object-fit: contain;">';
                                }
                                ?>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: .85rem;"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <div class="text-muted small mt-1">
                                    <?php echo $item['qty']; ?> x ₱<?php echo number_format($item['price'], 2); ?>
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

                    <div class="d-flex flex-column gap-2">
                        <a href="checkout.php" class="btn-apex w-100 text-center py-2" style="border-radius: 50px;">Secure Checkout</a>
                        <a href="cart.php" class="btn btn-light w-100 text-center py-2 fw-bold text-apex-blue" style="border-radius: 50px; border: 1px solid var(--apex-border);">View Full Cart</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="favoritesOffcanvas" aria-labelledby="favoritesOffcanvasLabel" style="width: 400px; border-left: none; box-shadow: -4px 0 24px rgba(0,0,0,0.1);">
        <div class="offcanvas-header border-bottom pb-3 pt-4 px-4">
            <h6 class="offcanvas-title fw-bold text-dark d-flex align-items-center" id="favoritesOffcanvasLabel">
                <i class="fas fa-heart me-2" style="color: #ff3b5c;"></i> Your Favorites
            </h6>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body p-4 d-flex flex-column">
            <?php if (!isset($_SESSION['favorites']) || empty($_SESSION['favorites'])): ?>
                <div class="m-auto text-center w-100">
                    <div class="mb-4">
                        <div style="width: 80px; height: 80px; background: var(--apex-grey); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <i class="far fa-heart fa-2x text-muted opacity-50"></i>
                        </div>
                    </div>
                    <h6 class="fw-bold mb-2">No favorites yet</h6>
                    <p class="text-muted small mb-4">Tap the heart on any product to save it for later.</p>
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2" style="background: var(--apex-blue); border-color: var(--apex-blue); font-weight: 600; font-size: .9rem;" data-bs-dismiss="offcanvas">Explore Products</button>
                </div>
            <?php else: ?>
                <div class="cart-items flex-grow-1 overflow-auto pe-2">
                    <?php foreach ($_SESSION['favorites'] as $id => $item): ?>
                        <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                            <div style="width: 50px; height: 50px; border-radius: 6px; background: var(--apex-grey); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink: 0;">
                                <?php
                                if (strpos($item['image'], '<svg') !== false) {
                                    echo $item['image'];
                                } else {
                                    echo '<img src="' . htmlspecialchars($item['image']) . '" style="max-width: 100%; object-fit: contain;">';
                                }
                                ?>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: .85rem;">
                                    <a href="product.php?id=<?php echo $id; ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($item['name']); ?></a>
                                </h6>
                                <div class="fw-bold text-apex-blue small mt-1">
                                    ₱<?php echo number_format($item['price'], 2); ?>
                                </div>
                            </div>
                            <form method="POST" action="favorites_action.php" class="ms-auto m-0">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                <button type="submit" class="btn btn-sm text-danger border-0 bg-transparent" title="Remove from favorites">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
    <?php if (isset($_SESSION['user'])): ?>
    (function() {
        const panel = document.createElement('div');
        panel.id = 'profilePanel';
        panel.className = 'profile-panel';
        panel.innerHTML = `
            <div class="pp-header">
                <div class="pp-avatar"><?php echo htmlspecialchars($_SESSION['user']['avatar']); ?></div>
                <div>
                    <div class="pp-username"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></div>
                    <div class="pp-email"><?php echo htmlspecialchars($_SESSION['user']['email']); ?></div>
                </div>
            </div>
            <div class="pp-divider"></div>
            <a href="javascript:void(0)" class="pp-link"><i class="fas fa-user"></i> My Profile</a>
            <a href="javascript:void(0)" class="pp-link"><i class="fas fa-box"></i> My Orders</a>
            <a href="#favoritesOffcanvas" data-bs-toggle="offcanvas" class="pp-link"><i class="fas fa-heart"></i> Favorites</a>
            <a href="#cartOffcanvas" data-bs-toggle="offcanvas" class="pp-link"><i class="fas fa-shopping-cart"></i> My Cart</a>
            <a href="javascript:void(0)" class="pp-link"><i class="fas fa-cog"></i> Settings</a>
            <div class="pp-divider"></div>
            <a href="logout.php" class="pp-link pp-logout"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        `;
        document.body.appendChild(panel);
    })();
    function toggleProfilePanel(e) {
        e.stopPropagation();
        const panel = document.getElementById('profilePanel');
        const btn   = document.getElementById('profileToggle');
        const rect  = btn.getBoundingClientRect();
        const isOpen = panel.classList.contains('open');
        if (isOpen) { panel.classList.remove('open'); return; }
        panel.style.top   = (rect.bottom + window.scrollY + 12) + 'px';
        panel.style.left  = 'auto';
        panel.style.right = (document.documentElement.clientWidth - rect.right) + 'px';
        panel.classList.add('open');
    }
    document.addEventListener('click', function(e) {
        const panel = document.getElementById('profilePanel');
        const btn   = document.getElementById('profileToggle');
        if (panel && btn && !btn.contains(e.target) && !panel.contains(e.target)) panel.classList.remove('open');
    });
    window.addEventListener('scroll', function() {
        const panel = document.getElementById('profilePanel');
        if (panel) panel.classList.remove('open');
    });
    <?php endif; ?>
    </script>
</body>

</html>