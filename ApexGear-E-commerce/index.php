<?php
session_start();
unset($_SESSION['inventory']);

if (!isset($_SESSION['inventory'])) {
    $_SESSION['inventory'] = [
        1 => ['id' => 1, 'category' => 'laptop', 'brand' => 'Lenovo', 'name' => 'Legion 5 Pro — RTX 4070, 16" QHD 165Hz', 'price' => 119995.00, 'old_price' => 127999.00, 'rating' => 124, 'badge' => 'New', 'badge_type' => 'new', 'image' => 'assets/images/products/legion5pro.png', 'stock' => 10],
        2 => ['id' => 2, 'category' => 'laptop', 'brand' => 'ASUS', 'name' => 'ROG Zephyrus G14 — Ryzen 9, RTX 4060', 'price' => 109995.00, 'old_price' => 119995.00, 'rating' => 89, 'badge' => 'SALE', 'badge_type' => 'ribbon', 'image' => 'assets/images/products/zephyrusg14.png', 'stock' => 5],
        3 => ['id' => 3, 'category' => 'peripheral', 'brand' => 'Razer', 'name' => 'BlackWidow V4 Pro — Mechanical, RGB, Wireless', 'price' => 13495.00, 'old_price' => 15995.00, 'rating' => 4210, 'badge' => 'Popular', 'badge_type' => 'normal', 'image' => 'assets/images/products/blackwidow.png', 'stock' => 20],
        4 => ['id' => 4, 'category' => 'peripheral', 'brand' => 'Logitech', 'name' => 'G Pro X Superlight 2 — Wireless Gaming Mouse', 'price' => 7795.00, 'old_price' => 8995.00, 'rating' => 4340, 'badge' => null, 'badge_type' => null, 'image' => 'assets/images/products/superlight.png', 'stock' => 15],
        5 => ['id' => 5, 'category' => 'audio', 'brand' => 'Sony', 'name' => 'WH-1000XM5 — Noise Cancelling, 30hr Battery', 'price' => 15499.00, 'old_price' => 20999.00, 'rating' => 512, 'badge' => 'New', 'badge_type' => 'new', 'image' => 'assets/images/products/sonywh.png', 'stock' => 12],
        6 => ['id' => 6, 'category' => 'phone', 'brand' => 'Samsung', 'name' => 'Galaxy S24 Ultra — 200MP, 5000mAh, Titanium', 'price' => 84990.00, 'old_price' => 94990.00, 'rating' => 445, 'badge' => 'SALE', 'badge_type' => 'ribbon', 'image' => 'assets/images/products/s24ultra.png', 'stock' => 8],
        7 => ['id' => 7, 'category' => 'desktop', 'brand' => 'Dell', 'name' => 'XPS Tower — Intel i9-14900K, RTX 4080, 64GB', 'price' => 185000.00, 'old_price' => 200000.00, 'rating' => 78, 'badge' => null, 'badge_type' => null, 'image' => 'assets/images/products/xpstower.png', 'stock' => 3],
        8 => ['id' => 8, 'category' => 'peripheral', 'brand' => 'LG', 'name' => 'UltraGear 27" — 4K, 144Hz, 1ms, IPS, G-Sync', 'price' => 39995.00, 'old_price' => 45995.00, 'rating' => 193, 'badge' => 'On Sale', 'badge_type' => 'sale', 'image' => 'assets/images/products/ultragear.png', 'stock' => 7],
        9 => ['id' => 9, 'category' => 'phone', 'brand' => 'Apple', 'name' => 'iPhone 17 Pro — Cosmic Orange - Aluminum', 'price' => 109990.00, 'old_price' => 119990.00, 'rating' => 445, 'badge' => 'On Sale', 'badge_type' => 'sale', 'image' => 'assets/images/products/iphone17pro.png', 'stock' => 15],
        10 => ['id' => 10, 'category' => 'laptop', 'brand' => 'HP', 'name' => 'Spectre x360 14" — OLED, Intel Evo', 'price' => 79995.00, 'old_price' => 95000.00, 'rating' => 156, 'badge' => '–15%', 'badge_type' => 'sale', 'image' => 'assets/images/products/hpspectre.png', 'stock' => 10],
        11 => ['id' => 11, 'category' => 'phone', 'brand' => 'Apple', 'name' => 'iPhone 15 Pro — 256GB, Natural Titanium', 'price' => 69990.00, 'old_price' => 76990.00, 'rating' => 892, 'badge' => '–10%', 'badge_type' => 'sale', 'image' => 'assets/images/products/iph15pro.png', 'stock' => 25],
        12 => ['id' => 12, 'category' => 'peripheral', 'brand' => 'Corsair', 'name' => 'K100 Air — Ultra-Thin, Wireless, RGB', 'price' => 11995.00, 'old_price' => 16500.00, 'rating' => 340, 'badge' => '–25%', 'badge_type' => 'sale', 'image' => 'assets/images/products/corsairk100air.png', 'stock' => 15],
        13 => ['id' => 13, 'category' => 'gpu', 'brand' => 'NVIDIA', 'name' => 'GeForce RTX 4070 Super — 12GB GDDR6X', 'price' => 36500.00, 'old_price' => 42000.00, 'rating' => 215, 'badge' => '–13%', 'badge_type' => 'sale', 'image' => 'assets/images/products/nvidiartx4070.png', 'stock' => 5]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home | ApeX Gear</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/auth-styles-append.css" rel="stylesheet" />
</head>

<body>
    <?php include_once __DIR__ . '/cookie_notif.php'; ?>

    <!-- INJECT NAVBAR -->
    <?php
    $currentPage = 'home';
    include 'includes\navbar.php';
    ?>

    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="hero">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-lg-6 hero-content">
                                <div class="hero-eyebrow">New Arrivals 2026</div>
                                <h1 class="hero-title">
                                    Laptops,<br>Desktops,<br><em>&amp; More</em>
                                </h1>
                                <p class="hero-sub">Next-level performance for gamers, creators, and professionals. Gear up with ApeX.</p>
                                <div class="d-flex gap-3 flex-wrap">
                                    <a href="#featured" class="btn-apex">Shop Now</a>
                                    <a href="#" class="btn-apex-outline">View Deals</a>
                                </div>
                            </div>
                            <div class="col-lg-6 hero-img-wrap">
                                <img src="assets/images/feature_laptop.png" alt="ApeX hero illustration" style="display:block; width:100%; max-width:650px; height:auto; margin:0 auto;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="hero" style="background: linear-gradient(135deg, #060f2e 0%, #0b2fa8 40%, #0066cc 100%);">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-lg-6 hero-content">
                                <div class="hero-eyebrow">Gaming Peripherals</div>
                                <h1 class="hero-title">
                                    Keyboards<br>&amp; Mouse<br><em>Built to Win</em>
                                </h1>
                                <p class="hero-sub">Precision, speed, and feel — every keystroke counts. Dominate with ApeX Gear.</p>
                                <div class="d-flex gap-3 flex-wrap">
                                    <a href="#featured" class="btn-apex">Shop Peripherals</a>
                                    <a href="#" class="btn-apex-outline">See Brands</a>
                                </div>
                            </div>
                            <div class="col-lg-6 hero-img-wrap">
                                <img src="assets/images/feature_mouse.png" alt="ApeX hero illustration" style="display:block; width:100%; max-width:250px; height:auto; margin:0 auto;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="hero" style="background: linear-gradient(135deg, #060f2e 0%, #102090 55%, #0b50c8 100%);">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-lg-6 hero-content">
                                <div class="hero-eyebrow">Audio &amp; More</div>
                                <h1 class="hero-title">
                                    Headphones<br>&amp; Sound<br><em>Redefined</em>
                                </h1>
                                <p class="hero-sub">Immersive audio, noise cancellation, and elite comfort. Hear the difference with ApeX.</p>
                                <div class="d-flex gap-3 flex-wrap">
                                    <a href="#featured" class="btn-apex">Shop Audio</a>
                                    <a href="#" class="btn-apex-outline">New Arrivals</a>
                                </div>
                            </div>
                            <div class="col-lg-6 hero-img-wrap">
                                <img src="assets/images/feature_headphone.png" alt="ApeX hero illustration" style="display:block; width:100%; max-width:250px; height:auto; margin:0 auto;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" style="width:48px;">
            <i class="fas fa-chevron-left" style="color:#fff; font-size:1.4rem;"></i>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" style="width:48px;">
            <i class="fas fa-chevron-right" style="color:#fff; font-size:1.4rem;"></i>
        </button>
    </div>

    <div class="stats-strip">
        <div class="container">
            <div class="row g-0">
                <div class="col-6 col-md-3 stat-item">
                    <div class="stat-num">12K+</div>
                    <div class="stat-lbl">Products</div>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <div class="stat-num">48K</div>
                    <div class="stat-lbl">Happy Customers</div>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <div class="stat-num">10+</div>
                    <div class="stat-lbl">Top Brands</div>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <div class="stat-num">24/7</div>
                    <div class="stat-lbl">Support</div>
                </div>
            </div>
        </div>
    </div>

    <section class="brands-section overflow-hidden">
        <div class="container">
            <div class="text-center mb-1 fade-up">
                <h2 class="sec-title">Our <span>Trusted Brands</span></h2>
                <div class="sec-divider mx-auto"></div>
            </div>
        </div>

        <div class="brand-marquee fade-up">
            <div class="brand-track">
                <div class="brand-logo-wrap"><img src="assets/images/lenovo.png" alt="Lenovo" class="brand-img"></div>
                <div class="brand-logo-wrap"><img src="assets/images/asus.png" alt="Asus" class="brand-img"></div>
                <div class="brand-logo-wrap"><img src="assets/images/dell.png" alt="Dell" class="brand-img"></div>
                <div class="brand-logo-wrap"><img src="assets/images/hp.png" alt="HP" class="brand-img"></div>
                <div class="brand-logo-wrap"><img src="assets/images/apple.png" alt="Apple" class="brand-img"></div>
                <div class="brand-logo-wrap"><img src="assets/images/samsung.png" alt="Samsung" class="brand-img"></div>
                <div class="brand-logo-wrap"><img src="assets/images/razer.png" alt="Razer" class="brand-img"></div>
                <div class="brand-logo-wrap"><img src="assets/images/logitech.png" alt="Logitech" class="brand-img"></div>
                <div class="brand-logo-wrap"><img src="assets/images/sony.png" alt="Sony" class="brand-img"></div>
                <div class="brand-logo-wrap"><img src="assets/images/nvidia.png" alt="NVIDIA" class="brand-img"></div>
                <div class="brand-logo-wrap"><img src="assets/images/corsair.png" alt="Corsair" class="brand-img"></div>
                <div class="brand-logo-wrap"><img src="assets/images/intel.png" alt="Intel" class="brand-img"></div>
            </div>
        </div>
    </section>

    <section class="cat-section">
        <div class="container">
            <div class="text-center mb-2 fade-up">
                <h2 class="sec-title">Browse by <span>Category</span></h2>
            </div>
            <div class="sec-divider mx-auto fade-up"></div>
            <div class="row g-3 fade-up">
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a href="store.php" class="cat-card">
                        <div class="cat-icon-wrap"><i class="fas fa-laptop"></i></div>
                        <div class="cat-name">Laptops</div>
                        <span class="btn-shop">Shop Now</span>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a href="store.php" class="cat-card">
                        <div class="cat-icon-wrap"><i class="fas fa-desktop"></i></div>
                        <div class="cat-name">Desktops</div>
                        <span class="btn-shop">Shop Now</span>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a href="store.php" class="cat-card">
                        <div class="cat-icon-wrap"><i class="fas fa-mobile-alt"></i></div>
                        <div class="cat-name">Cellphones</div>
                        <span class="btn-shop">Shop Now</span>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a href="store.php" class="cat-card">
                        <div class="cat-icon-wrap"><i class="fas fa-headphones-alt"></i></div>
                        <div class="cat-name">Headphones</div>
                        <span class="btn-shop">Shop Now</span>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a href="store.php" class="cat-card">
                        <div class="cat-icon-wrap"><i class="fas fa-tablet"></i></div>
                        <div class="cat-name">Tablet</div>
                        <span class="btn-shop">Shop Now</span>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a href="store.php" class="cat-card">
                        <div class="cat-icon-wrap"><i class="fas fa-microchip"></i></div>
                        <div class="cat-name">CPU</div>
                        <span class="btn-shop">Shop Now</span>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a href="store.php" class="cat-card">
                        <div class="cat-icon-wrap"><i class="fas fa-tv"></i></div>
                        <div class="cat-name">GPU</div>
                        <span class="btn-shop">Shop Now</span>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a href="store.php" class="cat-card">
                        <div class="cat-icon-wrap"><i class="fas fa-keyboard"></i></div>
                        <div class="cat-name">Accessories</div>
                        <span class="btn-shop">Shop Now</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="featured-section" id="featured">
        <div class="container">
            <div class="text-center mb-2 fade-up">
                <h2 class="sec-title">Featured <span>Products</span></h2>
            </div>
            <div class="sec-divider mx-auto fade-up"></div>

            <div class="filter-tabs text-center fade-up">
                <button class="filter-tab active" onclick="filterProducts(this,'all')">All</button>
                <button class="filter-tab" onclick="filterProducts(this,'laptop')">Laptops</button>
                <button class="filter-tab" onclick="filterProducts(this,'desktop')">Desktops</button>
                <button class="filter-tab" onclick="filterProducts(this,'phone')">CellPhones</button>
                <button class="filter-tab" onclick="filterProducts(this,'audio')">Headphones</button>
                <button class="filter-tab" onclick="filterProducts(this,'tablet')">Tablets</button>
                <button class="filter-tab" onclick="filterProducts(this,'cpu')">CPU</button>
                <button class="filter-tab" onclick="filterProducts(this,'gpu')">GPU</button>
                <button class="filter-tab" onclick="filterProducts(this,'peripheral')">Accessories</button>
            </div>

            <div class="row g-4" id="productGrid">
                <?php foreach ($_SESSION['inventory'] as $product) {
                    $cat       = isset($product['category']) ? $product['category'] : 'laptop';
                    $badgeType = isset($product['badge_type']) ? $product['badge_type'] : null;
                    $badge     = isset($product['badge']) ? $product['badge'] : null;
                    $brand     = isset($product['brand']) ? $product['brand'] : 'ApeX';
                    $rating    = isset($product['rating']) ? $product['rating'] : '12';
                    $oldPrice  = isset($product['old_price']) ? $product['old_price'] : null;
                ?>
                    <div class="col-sm-6 col-md-4 col-lg-3 fade-up product-item" data-cat="<?php echo htmlspecialchars($cat); ?>">
                        <div class="product-card h-100 position-relative">

                            <?php if ($badgeType === 'new'): ?>
                                <div class="product-badge new"><?php echo htmlspecialchars($badge); ?></div>
                            <?php elseif ($badgeType === 'ribbon'): ?>
                                <div class="ribbon"></div>
                                <div class="ribbon-text"><?php echo htmlspecialchars($badge); ?></div>
                            <?php elseif ($badgeType === 'sale'): ?>
                                <div class="product-badge sale"><?php echo htmlspecialchars($badge); ?></div>
                            <?php elseif ($badge): ?>
                                <div class="product-badge"><?php echo htmlspecialchars($badge); ?></div>
                            <?php endif; ?>

                            <div class="product-img">
                                <?php
                                $imgData = isset($product['image']) ? $product['image'] : '';
                                if (strpos($imgData, '<svg') !== false) {
                                    echo $imgData;
                                } elseif (!empty($imgData)) {
                                    echo '<img src="' . htmlspecialchars($imgData) . '" alt="' . htmlspecialchars($product['name']) . '" style="max-height: 160px; max-width: 100%; object-fit: contain;">';
                                }
                                ?>
                            </div>

                            <div class="product-body">
                                <div class="product-tags">
                                    <span class="filter-tag brand">
                                        <i class="fas fa-tag me-1" style="opacity: 0.6;"></i> <?php echo htmlspecialchars($brand); ?>
                                    </span>
                                    <span class="filter-tag category">
                                        <i class="fas fa-layer-group me-1" style="opacity: 0.6;"></i> <?php echo htmlspecialchars(ucfirst($cat)); ?>
                                    </span>
                                </div>
                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="product-rating">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    <span>(<?php echo htmlspecialchars($rating); ?>)</span>
                                </div>
                                <?php $stock = isset($product['stock']) ? (int)$product['stock'] : 0; ?>
                                <div class="product-price">
                                    ₱<?php echo number_format($product['price'], 2); ?>
                                    <?php if (isset($product['old_price']) && $product['old_price']): ?>
                                        <span class="old">₱<?php echo number_format($product['old_price'], 2); ?></span>
                                    <?php endif; ?>
                                    <span class="d-block small fw-bold mt-1" style="font-size: 0.75rem; color: <?php echo $stock > 0 ? 'var(--apex-muted)' : '#ff3b5c'; ?>;">
                                        <?php echo $stock > 0 ? "Stock: " . $stock : "Out of Stock"; ?>
                                    </span>
                                </div>
                                <div class="product-actions">
                                    <?php if ($stock > 0): ?>
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-shop stretched-link">View Details</a>
                                    <?php else: ?>
                                        <a href="#" class="btn-shop stretched-link" style="background: var(--apex-muted); pointer-events: none; border-color: transparent; color: white;">Sold Out</a>
                                    <?php endif; ?>

                                    <form method="POST" action="favorites_action.php" class="m-0 position-relative" style="display: inline-block; z-index: 2;">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                        <button type="submit" class="btn-wish border-0" title="Add to Favorites">
                                            <?php if (isset($_SESSION['favorites'][$product['id']])): ?>
                                                <i class="fas fa-heart" style="color: #ff3b5c;"></i>
                                            <?php else: ?>
                                                <i class="far fa-heart"></i>
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="text-center mt-5 fade-up">
                <a href="store.php" class="btn-apex" style="background: var(--apex-blue); color:#fff; box-shadow: 0 0 24px rgba(11,47,168,.35);">
                    View All Products &nbsp;<i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <section class="sale-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-2 fade-up">
                <div>
                    <h2 class="sec-title">Flash <span>Deals</span></h2>
                </div>
                <a href="deals.php" style="color: var(--apex-blue); font-weight:600; font-size:.88rem; text-decoration:none;">
                    View All Deals <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="sec-divider left fade-up"></div>
            <div class="row g-4 fade-up">
                <div class="col-lg-5">
                    <div style="background: linear-gradient(135deg, var(--apex-blue) 0%, var(--apex-dark) 100%); border-radius: 10px; padding: 36px 32px; height: 100%; position:relative; overflow:hidden;">
                        <div style="position:absolute; top:-30px; right:-30px; width:200px; height:200px; background:rgba(0,194,255,.08); border-radius:50%;"></div>
                        <div style="position:absolute; bottom:-50px; left:-20px; width:160px; height:160px; background:rgba(21,73,212,.15); border-radius:50%;"></div>
                        <div style="position:relative;">
                            <div style="font-family:'Barlow Condensed',sans-serif; font-weight:700; font-size:.78rem; letter-spacing:.25em; text-transform:uppercase; color:var(--apex-accent); margin-bottom:8px;">Limited Time</div>
                            <h3 style="font-family:'Barlow Condensed',sans-serif; font-weight:900; font-size:2.4rem; text-transform:uppercase; color:#fff; line-height:1; margin-bottom:12px;">Up to <span style="color:var(--apex-accent)">40% Off</span><br>Gaming Gear</h3>
                            <p style="color:rgba(255,255,255,.6); font-size:.88rem; margin-bottom:24px;">Massive discounts on keyboards, Mouse, headsets, and more. Don't miss out.</p>
                            <div class="d-flex gap-2 mb-28" id="countdown" style="margin-bottom:24px;">
                                <div style="background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15); border-radius:6px; padding:10px 14px; text-align:center; min-width:56px;">
                                    <div style="font-family:'Barlow Condensed',sans-serif; font-weight:900; font-size:1.6rem; color:#fff;" id="cd-h">08</div>
                                    <div style="font-size:.6rem; color:rgba(255,255,255,.4); letter-spacing:.1em; text-transform:uppercase;">HRS</div>
                                </div>
                                <div style="background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15); border-radius:6px; padding:10px 14px; text-align:center; min-width:56px;">
                                    <div style="font-family:'Barlow Condensed',sans-serif; font-weight:900; font-size:1.6rem; color:#fff;" id="cd-m">34</div>
                                    <div style="font-size:.6rem; color:rgba(255,255,255,.4); letter-spacing:.1em; text-transform:uppercase;">MIN</div>
                                </div>
                                <div style="background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15); border-radius:6px; padding:10px 14px; text-align:center; min-width:56px;">
                                    <div style="font-family:'Barlow Condensed',sans-serif; font-weight:900; font-size:1.6rem; color:var(--apex-accent);" id="cd-s">22</div>
                                    <div style="font-size:.6rem; color:rgba(255,255,255,.4); letter-spacing:.1em; text-transform:uppercase;">SEC</div>
                                </div>
                            </div>
                            <a href="deals.php" class="btn-apex">Shop Flash Deals</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="row g-3">
                        <!-- Flash Deal 1: HP Spectre (ID 10) -->
                        <div class="col-sm-6">
                            <div class="product-card h-100 position-relative" style="border-color:#ff3b5c;">
                                <div class="product-badge sale">–15%</div>
                                <div class="product-img" style="height:150px; display: flex; align-items: center; justify-content: center;">
                                    <img src="assets/images/products/hpspectre.png" alt="HP Spectre x360 14" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                                </div>
                                <div class="product-body">
                                    <div class="product-brand">HP</div>
                                    <div class="product-name">Spectre x360 14" — OLED, Intel Evo</div>
                                    <div class="product-price" style="font-size:1.2rem;">₱79,995.00 <span class="old">₱95,000.00</span></div>
                                    <a href="product.php?id=10" class="btn-shop d-block text-center mt-2 stretched-link">View Deal</a>
                                </div>
                            </div>
                        </div>

                        <!-- Flash Deal 2: iPhone 15 Pro (ID 11) -->
                        <div class="col-sm-6">
                            <div class="product-card h-100 position-relative" style="border-color:#ff3b5c;">
                                <div class="product-badge sale">–10%</div>
                                <div class="product-img" style="height:150px; display: flex; align-items: center; justify-content: center;">
                                    <img src="assets/images/products/iph15pro.png" alt="iPhone 15 Pro" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                                </div>
                                <div class="product-body">
                                    <div class="product-brand">Apple</div>
                                    <div class="product-name">iPhone 15 Pro — 256GB, Natural Titanium</div>
                                    <div class="product-price" style="font-size:1.2rem;">₱69,990.00 <span class="old">₱76,990.00</span></div>
                                    <a href="product.php?id=11" class="btn-shop d-block text-center mt-2 stretched-link">View Deal</a>
                                </div>
                            </div>
                        </div>

                        <!-- Flash Deal 3: Corsair Keyboard (ID 12) -->
                        <div class="col-sm-6">
                            <div class="product-card h-100 position-relative" style="border-color:#ff3b5c;">
                                <div class="product-badge sale">–25%</div>
                                <div class="product-img" style="height:150px; display: flex; align-items: center; justify-content: center;">
                                    <img src="assets/images/products/corsairk100air.png" alt="Corsair K100 Air" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                                </div>
                                <div class="product-body">
                                    <div class="product-brand">Corsair</div>
                                    <div class="product-name">K100 Air — Ultra-Thin, Wireless, RGB</div>
                                    <div class="product-price" style="font-size:1.2rem;">₱11,995.00 <span class="old">₱16,500.00</span></div>
                                    <a href="product.php?id=12" class="btn-shop d-block text-center mt-2 stretched-link">View Deal</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="product-card h-100 position-relative" style="border-color:#ff3b5c;">
                                <div class="product-badge sale">–13%</div>
                                <div class="product-img" style="height:150px; display: flex; align-items: center; justify-content: center;">
                                    <img src="assets/images/products/nvidiartx4070.png" alt="NVIDIA RTX 4070 Super" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                                </div>
                                <div class="product-body">
                                    <div class="product-brand">NVIDIA</div>
                                    <div class="product-name">GeForce RTX 4070 Super — 12GB GDDR6X</div>
                                    <div class="product-price" style="font-size:1.2rem;">₱36,500.00 <span class="old">₱42,000.00</span></div>
                                    <a href="product.php?id=13" class="btn-shop d-block text-center mt-2 stretched-link">View Deal</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="why-section">
        <div class="container" style="position:relative; z-index:1;">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5 fade-up">
                    <div style="font-family:'Barlow Condensed',sans-serif; font-weight:700; font-size:.78rem; letter-spacing:.25em; color:var(--apex-accent); text-transform:uppercase; margin-bottom:10px;">Why Choose Us</div>
                    <h2 class="why-title">Why Choose<br><span>ApeX Gear?</span></h2>
                    <p class="why-body">We're not just an online store — we're a community of tech enthusiasts. Every product we sell is carefully curated for performance, value, and reliability. From budget builds to flagship setups, ApeX Gear delivers.</p>
                    <a href="about.php" class="btn-apex">Learn More</a>
                </div>
                <div class="col-lg-7">
                    <div class="row g-3 fade-up">
                        <div class="col-sm-6">
                            <div class="why-card">
                                <i class="fas fa-shield-alt"></i>
                                <div class="why-card-title">Authentic Products</div>
                                <p>Every item is 100% genuine, sourced directly from authorized distributors and brand partners.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="why-card">
                                <i class="fas fa-headset"></i>
                                <div class="why-card-title">24/7 Expert Support</div>
                                <p>Our tech-savvy team is always available via chat, email, or phone to help you pick the right gear.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="why-card">
                                <i class="fas fa-bolt"></i>
                                <div class="why-card-title">Fast Delivery</div>
                                <p>Lightning-fast nationwide shipping. Most orders arrive within 1–3 business days.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="why-card">
                                <i class="fas fa-undo"></i>
                                <div class="why-card-title">Easy Returns</div>
                                <p>Changed your mind? No problem. Our hassle-free 30-day return policy has you covered.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="trust-strip">
        <div class="container">
            <div class="row g-4">
                <div class="col-6 col-md-3">
                    <div class="trust-item">
                        <div class="trust-icon-wrap"><i class="fas fa-truck"></i></div>
                        <div class="trust-label">Free Shipping</div>
                        <div class="trust-sub">On orders over ₱2,000</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="trust-item">
                        <div class="trust-icon-wrap"><i class="fas fa-thumbs-up"></i></div>
                        <div class="trust-label">Satisfaction</div>
                        <div class="trust-sub">100% Guaranteed</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="trust-item">
                        <div class="trust-icon-wrap"><i class="fas fa-redo-alt"></i></div>
                        <div class="trust-label">30-Day Returns</div>
                        <div class="trust-sub">Hassle-free returns</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="trust-item">
                        <div class="trust-icon-wrap"><i class="fas fa-lock"></i></div>
                        <div class="trust-label">Secure Checkout</div>
                        <div class="trust-sub">256-bit SSL encryption</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="newsletter-section">
        <div class="container" style="position:relative; z-index:1;">
            <div class="row align-items-center g-4">
                <div class="col-lg-6 fade-up">
                    <h2 class="newsletter-title">Join the <span>ApeX</span> Inner Circle</h2>
                    <p class="newsletter-sub">Get exclusive deals, early access to new arrivals, and tech tips delivered straight to your inbox.</p>
                </div>
                <div class="col-lg-6 fade-up">
                    <div class="newsletter-form">
                        <input type="email" placeholder="Enter your email address…" />
                        <button>Subscribe</button>
                    </div>
                    <p style="font-size:.75rem; color:rgba(255,255,255,.35); margin-top:8px;">No spam. Unsubscribe anytime. We respect your privacy.</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes\footer.php'; ?>

    <script>
        function filterProducts(btn, cat) {
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            document.querySelectorAll('.product-item').forEach(item => {
                if (cat === 'all' || item.dataset.cat === cat) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>

</html>