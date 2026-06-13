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
                // Helper: compute effective (live sale) price
                function getEffectivePriceModal($item) {
                    $basePrice  = floatval($item['original_price'] ?? $item['price']);
                    $salePct    = intval($item['sale_percent'] ?? 0);
                    $saleExpiry = $item['sale_expiry'] ?? '';
                    $saleActive = $salePct > 0 && (!empty($saleExpiry) ? strtotime($saleExpiry) > time() : true);
                    return $saleActive ? round($basePrice * (1 - $salePct / 100), 2) : $basePrice;
                }

                $subtotal = 0;
                foreach ($_SESSION['cart'] as $id => $item):
                    $effectivePrice = getEffectivePriceModal($item);
                    $originalPrice  = floatval($item['original_price'] ?? $item['price']);
                    $isOnSale = $effectivePrice < $originalPrice;
                    $subtotal += ($effectivePrice * $item['qty']);
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
                                <?php echo $item['qty']; ?> x ₱<?php echo number_format($effectivePrice, 2); ?>
                                <?php if ($isOnSale): ?><br><small style="color:#ff3b5c;font-weight:700;"><?php echo intval($item['sale_percent']); ?>% OFF</small><?php endif; ?>
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


