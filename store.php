<?php
session_start();
require_once __DIR__ . '/classes/Inventory.php';
require_once __DIR__ . '/includes/product_card.php';

/** @var Inventory $inventoryManager */
$inventoryManager = new Inventory();
$products = $inventoryManager->getAllProducts();
$allProducts = $products;

$searchQuery = trim($_GET['q'] ?? '');
$activeCategory = trim($_GET['cat'] ?? '');
$activeSort = trim($_GET['sort'] ?? '');

$categoryAliases = [
    'laptops' => 'Laptop',
    'laptop' => 'Laptop',
    'desktops' => 'Desktop / PC',
    'desktop' => 'Desktop / PC',
    'pc' => 'Desktop / PC',
    'pcs' => 'Desktop / PC',
    'desktop pc' => 'Desktop / PC',
    'desktop/pc' => 'Desktop / PC',
    'desktop / pc' => 'Desktop / PC',
    'cellphones' => 'Phone',
    'cellphone' => 'Phone',
    'phones' => 'Phone',
    'phone' => 'Phone',
    'tablet' => 'Tablet',
    'tablets' => 'Tablet',
    'audio' => 'Headphones / Audio',
    'headphones' => 'Headphones / Audio',
    'headphone' => 'Headphones / Audio',
    'headphones audio' => 'Headphones / Audio',
    'headphones/audio' => 'Headphones / Audio',
    'headphones / audio' => 'Headphones / Audio',
    'accessory' => 'Accessories / Peripherals',
    'accessories' => 'Accessories / Peripherals',
    'peripherals' => 'Accessories / Peripherals',
    'peripheral' => 'Accessories / Peripherals',
    'accessories peripherals' => 'Accessories / Peripherals',
    'accessories/peripherals' => 'Accessories / Peripherals',
    'accessories / peripherals' => 'Accessories / Peripherals',
    'cpu' => 'CPU',
    'gpu' => 'GPU',
];

$normalizeCategory = static function (string $category) use (&$categoryAliases): string {
    $category = trim($category);
    if ($category === '') {
        return '';
    }

    $categoryKey = mb_strtolower($category, 'UTF-8');
    $categoryKey = preg_replace('/\s+/', ' ', $categoryKey);
    return $categoryAliases[$categoryKey] ?? $category;
};

$categoryMap = [];
foreach ($allProducts as $product) {
    $category = trim($product['category'] ?? '');
    if ($category !== '') {
        $categoryMap[mb_strtolower($category, 'UTF-8')] = $normalizeCategory($category);
    }
}

if ($activeCategory !== '') {
    $categoryKey = mb_strtolower($activeCategory, 'UTF-8');
    $activeCategory = $categoryMap[$categoryKey] ?? $normalizeCategory($activeCategory);
}

// Search filter
if ($searchQuery !== '') {
    $filteredProducts = [];
    $query = mb_strtolower($searchQuery, 'UTF-8');
    foreach ($products as $product) {
        $haystack = mb_strtolower(implode(' ', [
            $product['name'] ?? '',
            $product['brand'] ?? '',
            $product['category'] ?? '',
        ]), 'UTF-8');
        if (mb_strpos($haystack, $query, 0, 'UTF-8') !== false) {
            $filteredProducts[] = $product;
        }
    }
    $products = $filteredProducts;
}

// Category filter
if ($activeCategory !== '') {
    $normalizedActiveCategory = $normalizeCategory($activeCategory);
    $products = array_filter(
        $products,
        fn($p) =>
        mb_strtolower($normalizeCategory((string) ($p['category'] ?? '')), 'UTF-8') === mb_strtolower($normalizedActiveCategory, 'UTF-8')
    );
    $products = array_values($products);
}

// Sort
if ($activeSort === 'price_asc') {
    usort($products, fn($a, $b) => $a['price'] <=> $b['price']);
} elseif ($activeSort === 'price_desc') {
    usort($products, fn($a, $b) => $b['price'] <=> $a['price']);
}

$productCount = count($products);

// Collect all unique categories for filter chips
$allCategories = array_unique(array_filter(array_column($allProducts, 'category')));
sort($allCategories);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Store | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/auth-styles-append.css" rel="stylesheet" />
    <link href="assets/css/store-styles.css" rel="stylesheet" />
</head>

<body>
    <?php include_once __DIR__ . '/includes/cookie_notif.php'; ?>

    <?php
    $currentPage = 'store';
    include 'includes/navbar.php';
    ?>

    <!-- ── STORE HERO ───────────────────────────────────────── -->
    <header class="store-hero">
        <div class="container">
            <div class="store-hero-copy" style="top: 70%; transform: translateY(-3%);">

                <h1>Product<br><em>Store</em></h1>
                <p>Equip your setup with the latest and best in tech hardware.</p>

                <!-- Inline search -->
                <form method="GET" action="store.php" class="store-hero-search">
                    <input
                        type="text"
                        name="q"
                        class="store-product-search"
                        placeholder="Search products, brands…"
                        value="<?php echo htmlspecialchars($searchQuery); ?>"
                        autocomplete="off" />
                    <button type="submit"><i class="fas fa-search me-1"></i> Search</button>
                    <div class="store-search-panel" aria-label="Search products"></div>
                </form>
            </div>

            <!-- Decorative laptop image -->
            <div class="store-hero-img" aria-hidden="true">
                <img src="assets/images/products/zephyrusg14.png" alt="" />
            </div>
        </div>
    </header>

    <!-- ── CATALOG ──────────────────────────────────────────── -->
    <section class="store-catalog-section">
        <div class="container">

            <!-- Toolbar -->
            <div class="catalog-toolbar">
                <div class="catalog-summary">
                    <span class="catalog-count"><?php echo $productCount; ?> Product<?php echo $productCount === 1 ? '' : 's'; ?></span>
                    <?php if ($searchQuery !== ''): ?>
                        <span class="catalog-context">Results for "<?php echo htmlspecialchars($searchQuery); ?>"</span>
                    <?php elseif ($activeCategory !== ''): ?>
                        <span class="catalog-context">Filtered by: <?php echo htmlspecialchars(ucfirst($activeCategory)); ?></span>
                    <?php else: ?>
                        <span class="catalog-context">All current inventory</span>
                    <?php endif; ?>
                </div>
                <div class="apex-filter-row">
                    <div class="apex-sort-wrap apex-category-wrap">
                        <span class="apex-sort-label"><i class="fas fa-filter me-1"></i> Category</span>
                        <div class="apex-sort-dropdown" id="categoryDropdown">
                            <?php
                            $categoryOptions = [
                                '' => 'All Categories',
                                'Laptop' => 'Laptop',
                                'Desktop / PC' => 'Desktop / PC',
                                'Tablet' => 'Tablet',
                                'Phone' => 'Phone',
                                'Headphones / Audio' => 'Headphones / Audio',
                                'Accessories / Peripherals' => 'Accessories / Peripherals',
                                'CPU' => 'CPU',
                                'GPU' => 'GPU',
                            ];
                            foreach ($allCategories as $categoryName) {
                                $categoryOptions[$categoryName] = $categoryName;
                            }
                            $currentCategoryLabel = isset($categoryOptions[$activeCategory]) ? $categoryOptions[$activeCategory] : 'Category';
                            ?>
                            <button class="apex-sort-btn" onclick="toggleCategory(event)" type="button">
                                <i class="fas fa-layer-group me-2"></i>
                                <span><?php echo htmlspecialchars($currentCategoryLabel); ?></span>
                                <i class="fas fa-chevron-down apex-sort-caret ms-2"></i>
                            </button>
                            <ul class="apex-sort-menu" id="categoryMenu">
                                <?php foreach ($categoryOptions as $val => $label): ?>
                                    <li>
                                        <a href="#" class="apex-sort-item <?php echo $activeCategory === $val ? 'active' : ''; ?>"
                                            onclick="applyCategory(<?php echo htmlspecialchars(json_encode($val), ENT_QUOTES, 'UTF-8'); ?>); return false;">
                                            <?php echo htmlspecialchars($label); ?>
                                            <?php if ($activeCategory === $val): ?>
                                                <i class="fas fa-check ms-auto"></i>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="apex-sort-wrap">
                        <span class="apex-sort-label"><i class="fas fa-sliders-h me-1"></i> Sort</span>
                        <div class="apex-sort-dropdown" id="sortDropdown">
                            <?php
                            $sortOptions = [
                                ''           => ['label' => 'Featured',          'icon' => 'fa-star'],
                                'price_asc'  => ['label' => 'Price: Low to High', 'icon' => 'fa-arrow-up'],
                                'price_desc' => ['label' => 'Price: High to Low', 'icon' => 'fa-arrow-down'],
                            ];
                            $currentLabel = $sortOptions[$activeSort]['label'] ?? 'Featured';
                            $currentIcon  = $sortOptions[$activeSort]['icon'] ?? 'fa-star';
                            ?>
                            <button class="apex-sort-btn" onclick="toggleSort(event)" type="button">
                                <i class="fas <?php echo $currentIcon; ?> me-2"></i>
                                <span><?php echo $currentLabel; ?></span>
                                <i class="fas fa-chevron-down apex-sort-caret ms-2"></i>
                            </button>
                            <ul class="apex-sort-menu" id="sortMenu">
                                <?php foreach ($sortOptions as $val => $opt): ?>
                                    <li>
                                        <a href="#" class="apex-sort-item <?php echo $activeSort === $val ? 'active' : ''; ?>"
                                            onclick="applySort('<?php echo $val; ?>'); return false;">
                                            <i class="fas <?php echo $opt['icon']; ?> me-2"></i>
                                            <?php echo $opt['label']; ?>
                                            <?php if ($activeSort === $val): ?>
                                                <i class="fas fa-check ms-auto"></i>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product grid -->
            <div class="row g-4">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center py-5">
                        <?php if ($searchQuery !== ''): ?>
                            <div style="color: var(--apex-muted); font-size: 2.5rem; margin-bottom: 12px;"><i class="fas fa-search"></i></div>
                            <h4 style="color: var(--apex-text); font-family: 'Barlow Condensed', sans-serif; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;">No results for "<?php echo htmlspecialchars($searchQuery); ?>"</h4>
                            <p style="color: var(--apex-muted); margin-top: 6px;">Try a different keyword or browse all products.</p>
                            <a href="store.php" class="btn-shop d-inline-block mt-3" style="padding: 10px 24px; border-radius: 6px;">Browse All</a>
                        <?php else: ?>
                            <div style="color: var(--apex-muted); font-size: 2.5rem; margin-bottom: 12px;"><i class="fas fa-box-open"></i></div>
                            <h4 style="color: var(--apex-text); font-family: 'Barlow Condensed', sans-serif; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;">No product available</h4>
                            <p style="color: var(--apex-muted); margin-top: 6px;">Please check back soon for updated inventory.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-sm-6 col-md-4 col-lg-3 fade-up">
                            <?php renderProductCard($product); ?>
                            <?php if (false): ?>
                            <div class="product-card h-100 position-relative">

                                <?php
                                $badgeType = $product['badge_type'] ?? 'normal';
                                $badgeClass = match ($badgeType) {
                                    'sale'   => 'sale',
                                    'new'    => 'new',
                                    'ribbon' => 'sale',
                                    default  => '',
                                };
                                ?>
                                <?php if (!empty($product['badge'])): ?>
                                    <div class="product-badge <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($product['badge']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="product-img">
                                    <?php
                                    $rawImage = $product['image'] ?? '';
                                    if (strpos($rawImage, '<svg') !== false) {
                                        echo $rawImage;
                                    } else {
                                        $productImage = Inventory::getProductImageSrc($rawImage);
                                        echo '<img src="' . htmlspecialchars($productImage) . '" alt="' . htmlspecialchars($product['name']) . '" loading="lazy">';
                                    }
                                    ?>
                                </div>

                                <div class="product-body">
                                    <div class="product-tags">
                                        <span class="filter-tag brand">
                                            <i class="fas fa-tag me-1" style="opacity:.55;"></i>
                                            <?php echo htmlspecialchars($product['brand'] ?? 'Unknown'); ?>
                                        </span>
                                        <span class="filter-tag category">
                                            <i class="fas fa-layer-group me-1" style="opacity:.55;"></i>
                                            <?php echo htmlspecialchars(ucfirst($product['category'] ?? 'Accessories')); ?>
                                        </span>
                                    </div>

                                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>

                                    <div class="product-rating">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                        <span>(<?php echo $product['rating'] ?? rand(50, 400); ?>)</span>
                                    </div>

                                    <?php $stock = (int)($product['stock'] ?? 0); ?>
                                    <?php
                                    // Compute sale price if sale is active
                                    $salePct    = (int)($product['sale_percent'] ?? 0);
                                    $saleExp    = $product['sale_expiry'] ?? '';
                                    $saleExpiryTs = !empty($saleExp) ? strtotime((string)$saleExp) : 0;
                                    $saleActive = $salePct > 0 && (!empty($saleExp) ? $saleExpiryTs > time() : true);
                                    $salePrice  = $saleActive ? round($product['price'] * (1 - $salePct / 100), 2) : null;
                                    ?>

                                    <?php if ($saleActive): ?>
                                        <div class="sale-badge-strip" style="position:absolute;top:14px;left:14px;z-index:3;background:#ff3b5c;color:#fff;font-family:'Barlow Condensed',sans-serif;font-weight:900;font-size:.78rem;padding:4px 10px;border-radius:4px;letter-spacing:.06em;">
                                            <?php echo $salePct; ?>% OFF
                                        </div>
                                    <?php endif; ?>

                                    <div class="product-price">
                                        <?php if ($saleActive && $salePrice !== null): ?>
                                            <span style="color:#ff3b5c;">₱<?php echo number_format($salePrice, 2); ?></span>
                                            <span class="old" style="text-decoration:line-through;color:var(--apex-muted);font-size:.85rem;margin-left:6px;">₱<?php echo number_format($product['price'], 2); ?></span>
                                        <?php else: ?>
                                            ₱<?php echo number_format($product['price'], 2); ?>
                                            <?php if (!empty($product['old_price'])): ?>
                                                <span class="old">₱<?php echo number_format($product['old_price'], 2); ?></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <span class="d-block" style="font-size:.72rem; font-family:'Barlow',sans-serif; font-weight:600; margin-top:4px; color:<?php echo $stock > 0 ? 'var(--apex-muted)' : '#ff3b5c'; ?>;">
                                            <?php echo $stock > 0 ? 'In stock: ' . $stock : 'Out of Stock'; ?>
                                        </span>
                                        <?php if ($saleActive && $saleExpiryTs > 0): ?>
                                            <span class="sale-countdown d-block" data-expiry="<?php echo $saleExpiryTs; ?>" style="font-size:.68rem;font-family:'Barlow Condensed',sans-serif;font-weight:700;color:#ff3b5c;margin-top:3px;letter-spacing:.04em;">
                                                <i class="fas fa-clock me-1"></i><span class="cdown-text">Loading...</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="product-actions">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-shop stretched-link">
                                            View Details
                                        </a>
                                        <form method="POST" action="actions/favorites_action.php" class="m-0 position-relative" style="display:inline-block; z-index:2;">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                            <button type="submit" class="btn-wish border-0" title="Add to Favorites">
                                                <?php if (isset($_SESSION['favorites'][$product['id']])): ?>
                                                    <i class="fas fa-heart" style="color:#ff3b5c;"></i>
                                                <?php else: ?>
                                                    <i class="far fa-heart"></i>
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include_once __DIR__ . '/includes/sale_countdown_script.php'; ?>

    <script>
        // ── Sale countdown timers ─────────────────────────────────────────────
        function applySort(val) {
            const url = new URL(window.location.href);
            if (val) url.searchParams.set('sort', val);
            else url.searchParams.delete('sort');
            window.location.href = url.toString();
        }

        function applyCategory(val) {
            const url = new URL(window.location.href);
            if (val) url.searchParams.set('cat', val);
            else url.searchParams.delete('cat');
            window.location.href = url.toString();
        }

        function toggleSort(e) {
            e.stopPropagation();
            const menu = document.getElementById('sortMenu');
            const caret = document.querySelector('#sortDropdown .apex-sort-caret');
            const open = menu.classList.toggle('open');
            if (caret) {
                caret.style.transform = open ? 'rotate(180deg)' : '';
            }
        }

        function toggleCategory(e) {
            e.stopPropagation();
            const menu = document.getElementById('categoryMenu');
            const caret = document.querySelector('#categoryDropdown .apex-sort-caret');
            const open = menu.classList.toggle('open');
            if (caret) {
                caret.style.transform = open ? 'rotate(180deg)' : '';
            }
        }

        document.addEventListener('click', () => {
            const sortMenu = document.getElementById('sortMenu');
            const categoryMenu = document.getElementById('categoryMenu');
            const carets = document.querySelectorAll('.apex-sort-caret');
            if (sortMenu) sortMenu.classList.remove('open');
            if (categoryMenu) categoryMenu.classList.remove('open');
            carets.forEach(caret => {
                caret.style.transform = '';
            });
        });
    </script>

</body>

</html>
