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