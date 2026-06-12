<!-- ── Footer ── -->
<footer>
    <?php include_once __DIR__ . '/order_status.php'; ?>
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-3">
                <a href="index.php" class="footer-logo-brand text-decoration-none">
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
                    <li><a href="#">News</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="footer-heading">Support</div>
                <ul>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="shipping_policy.php">Shipping Policy</a></li>
                    <li><a href="returns.php">Returns</a></li>
                    <li><a href="order_tracking.php">Order Tracking</a></li>
                    <li><a href="warranty.php">Warranty</a></li>
                    <li><a href="privacy_policy.php">Privacy Policy</a></li>
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
                <div class="payment-icons d-flex gap-2 flex-wrap mt-2">
                    <div class="pay-icon d-flex align-items-center justify-content-center px-2" title="Visa"><i class="fab fa-cc-visa fs-2"></i></div>
                    <div class="pay-icon d-flex align-items-center justify-content-center px-2" title="Mastercard"><i class="fab fa-cc-mastercard fs-2"></i></div>
                    <div class="pay-icon d-flex align-items-center justify-content-center px-2 gap-1" title="GCash"><i class="fas fa-mobile-screen"></i><span style="font-size: 0.75rem; font-weight: bold;">GCash</span></div>
                    <div class="pay-icon d-flex align-items-center justify-content-center px-2 gap-1" title="Maya"><i class="fas fa-wallet"></i><span style="font-size: 0.75rem; font-weight: bold;">Maya</span></div>
                    <div class="pay-icon d-flex align-items-center justify-content-center px-2 gap-1" title="Cash on Delivery"><i class="fas fa-hand-holding-usd"></i><span style="font-size: 0.75rem; font-weight: bold;">COD</span></div>
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

<!-- JavaScript Files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/navbar.js"></script>
<script src="assets/js/main.js"></script>

<!-- ── Cart Offcanvas ── -->
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

<!-- ── Favorites Offcanvas ── -->
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
                        <form method="POST" action="actions/favorites_action.php" class="ms-auto m-0">
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
