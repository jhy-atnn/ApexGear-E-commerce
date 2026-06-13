<?php
if (!function_exists('renderProductCard')) {
    function renderProductCard(array $product, array $options = [])
    {
        $productId = (int)($product['id'] ?? $product['product_id'] ?? 0);
        $name = $product['name'] ?? 'Product';
        $brand = $product['brand'] ?? 'Unknown';
        $category = $product['category'] ?? 'Accessories';
        $stock = (int)($product['stock'] ?? $product['stock_qty'] ?? 0);
        $rating = $product['rating'] ?? 5;
        $badge = trim((string)($product['badge'] ?? ''));
        $badgeType = $product['badge_type'] ?? 'normal';
        $returnUrl = $options['return_url'] ?? ($_SERVER['REQUEST_URI'] ?? 'store.php');
        $buttonText = $options['button_text'] ?? 'View Details';
        $showWishlist = $options['show_wishlist'] ?? true;

        $regularPrice = (float)($product['regular_price'] ?? $product['original_price'] ?? $product['price'] ?? 0);
        $salePct = (int)($product['discount_percent'] ?? $product['sale_percent'] ?? 0);
        $saleExp = $product['sale_expiry'] ?? $product['sale_valid_until'] ?? '';
        $saleExpiryTs = !empty($saleExp) ? strtotime((string)$saleExp) : 0;
        $saleActive = !empty($product['is_sale_active']) || Inventory::isSaleActive($salePct, $saleExp);
        $salePrice = $saleActive
            ? (float)($product['sale_price'] ?? $product['effective_price'] ?? Inventory::salePriceFromPercent($regularPrice, $salePct))
            : null;

        $badgeClass = match ($badgeType) {
            'sale', 'ribbon' => 'sale',
            'new' => 'new',
            default => '',
        };
        ?>
        <div class="product-card h-100 position-relative">
            <?php if ($saleActive || $badge !== ''): ?>
                <div class="product-badge-stack" style="position:absolute;top:14px;left:14px;z-index:3;display:flex;gap:4px;align-items:center;max-width:calc(100% - 28px);">
                    <?php if ($saleActive): ?>
                        <span class="sale-badge-strip" style="background:#ff3b5c;color:#fff;font-family:'Barlow Condensed',sans-serif;font-weight:900;font-size:.78rem;padding:4px 10px;border-radius:4px;letter-spacing:.06em;white-space:nowrap;">
                            <?php echo $salePct; ?>% OFF
                        </span>
                    <?php endif; ?>
                    <?php if ($badge !== ''): ?>
                        <span class="product-badge <?php echo $badgeClass; ?>" style="position:static;white-space:nowrap;">
                            <?php echo htmlspecialchars($badge); ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="product-img">
                <?php
                $rawImage = $product['image'] ?? '';
                if (strpos($rawImage, '<svg') !== false) {
                    echo $rawImage;
                } else {
                    $productImage = Inventory::getProductImageSrc($rawImage);
                    echo '<img src="' . htmlspecialchars($productImage) . '" alt="' . htmlspecialchars($name) . '" loading="lazy">';
                }
                ?>
            </div>

            <div class="product-body">
                <div class="product-tags">
                    <span class="filter-tag brand">
                        <i class="fas fa-tag me-1" style="opacity:.55;"></i>
                        <?php echo htmlspecialchars($brand); ?>
                    </span>
                    <span class="filter-tag category">
                        <i class="fas fa-layer-group me-1" style="opacity:.55;"></i>
                        <?php echo htmlspecialchars(ucfirst($category)); ?>
                    </span>
                </div>

                <div class="product-name"><?php echo htmlspecialchars($name); ?></div>

                <div class="product-rating">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    <span>(<?php echo htmlspecialchars((string)$rating); ?>)</span>
                </div>

                <div class="product-price">
                    <?php if ($saleActive && $salePrice !== null): ?>
                        <span style="color:#ff3b5c;">&#8369;<?php echo number_format($salePrice, 2); ?></span>
                        <span class="old" style="text-decoration:line-through;color:var(--apex-muted);font-size:.85rem;margin-left:6px;">&#8369;<?php echo number_format($regularPrice, 2); ?></span>
                    <?php else: ?>
                        &#8369;<?php echo number_format($regularPrice, 2); ?>
                        <?php if (!empty($product['old_price']) && (float)$product['old_price'] > $regularPrice): ?>
                            <span class="old">&#8369;<?php echo number_format((float)$product['old_price'], 2); ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                    <span class="d-block" style="font-size:.72rem;font-family:'Barlow',sans-serif;font-weight:600;margin-top:4px;color:<?php echo $stock > 0 ? 'var(--apex-muted)' : '#ff3b5c'; ?>;">
                        <?php echo $stock > 0 ? 'In stock: ' . $stock : 'Out of Stock'; ?>
                    </span>
                    <?php if ($saleActive && $saleExpiryTs > 0): ?>
                        <span class="sale-countdown d-block" data-expiry="<?php echo $saleExpiryTs; ?>" style="font-size:.68rem;font-family:'Barlow Condensed',sans-serif;font-weight:700;color:#ff3b5c;margin-top:3px;letter-spacing:.04em;">
                            <i class="fas fa-clock me-1"></i><span class="cdown-text">Loading...</span>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="product-actions">
                    <a href="product.php?id=<?php echo $productId; ?>" class="btn-shop stretched-link">
                        <?php echo htmlspecialchars($buttonText); ?>
                    </a>
                    <?php if ($showWishlist): ?>
                        <form method="POST" action="actions/favorites_action.php" class="m-0 position-relative" style="display:inline-block; z-index:2;">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($returnUrl); ?>">
                            <button type="submit" class="btn-wish border-0" title="Add to Favorites">
                                <?php if (isset($_SESSION['favorites'][$productId])): ?>
                                    <i class="fas fa-heart" style="color:#ff3b5c;"></i>
                                <?php else: ?>
                                    <i class="far fa-heart"></i>
                                <?php endif; ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
