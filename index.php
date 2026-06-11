<?php
session_start();

require_once 'classes/Inventory.php';
$inventoryManager = new Inventory();
$products = $inventoryManager->getAllProducts();

$featuredProducts = array_values($products);
usort($featuredProducts, function ($a, $b) {
    $scoreA = (int)($a['rating'] ?? 0) + (int)($a['sales'] ?? 0);
    $scoreB = (int)($b['rating'] ?? 0) + (int)($b['sales'] ?? 0);

    if ($scoreA === $scoreB) {
        return (int)($b['sales'] ?? 0) <=> (int)($a['sales'] ?? 0);
    }

    return $scoreB <=> $scoreA;
});
$featuredProducts = array_slice($featuredProducts, 0, 10);
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
    <?php include_once __DIR__ . '/includes\cookie_notif.php'; ?>

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
                                <div class="hero-eyebrow">Gaming Equipments</div>
                                <h1 class="hero-title">
                                    Keyboards<br>&amp; Mouse<br><em>Built to Win</em>
                                </h1>
                                <p class="hero-sub">Precision, speed, and feel — every keystroke counts. Dominate with ApeX Gear.</p>
                                <div class="d-flex gap-3 flex-wrap">
                                    <a href="#featured" class="btn-apex">Shop Equipments</a>
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
                <div class="brand-logo-wrap" aria-hidden="true"><img src="assets/images/lenovo.png" alt="" class="brand-img"></div>
                <div class="brand-logo-wrap" aria-hidden="true"><img src="assets/images/asus.png" alt="" class="brand-img"></div>
                <div class="brand-logo-wrap" aria-hidden="true"><img src="assets/images/dell.png" alt="" class="brand-img"></div>
                <div class="brand-logo-wrap" aria-hidden="true"><img src="assets/images/hp.png" alt="" class="brand-img"></div>
                <div class="brand-logo-wrap" aria-hidden="true"><img src="assets/images/apple.png" alt="" class="brand-img"></div>
                <div class="brand-logo-wrap" aria-hidden="true"><img src="assets/images/samsung.png" alt="" class="brand-img"></div>
                <div class="brand-logo-wrap" aria-hidden="true"><img src="assets/images/razer.png" alt="" class="brand-img"></div>
                <div class="brand-logo-wrap" aria-hidden="true"><img src="assets/images/logitech.png" alt="" class="brand-img"></div>
                <div class="brand-logo-wrap" aria-hidden="true"><img src="assets/images/sony.png" alt="" class="brand-img"></div>
                <div class="brand-logo-wrap" aria-hidden="true"><img src="assets/images/nvidia.png" alt="" class="brand-img"></div>
                <div class="brand-logo-wrap" aria-hidden="true"><img src="assets/images/corsair.png" alt="" class="brand-img"></div>
                <div class="brand-logo-wrap" aria-hidden="true"><img src="assets/images/intel.png" alt="" class="brand-img"></div>
            </div>
        </div>
    </section>

    <section class="cat-section">
        <div class="container">
            <div class="text-center mb-2 fade-up">
                <h2 class="sec-title">Browse by <span>Category</span></h2>
            </div>
            <div class="sec-divider mx-auto fade-up"></div>
            <div class="category-carousel fade-up">
                <button class="cat-arrow cat-arrow-left" type="button" aria-label="Previous categories">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="category-slider" aria-label="Browse categories">
                    <div class="cat-slide">
                        <a href="store.php" class="cat-card">
                            <img src="assets/images/products/legion5pro.png" alt="Gaming laptop" class="cat-img">
                            <div class="cat-card-info">
                                <div class="cat-name">Laptops</div>
                                <span class="btn-shop">Shop Now</span>
                            </div>
                        </a>
                    </div>
                    <div class="cat-slide">
                        <a href="store.php" class="cat-card">
                            <img src="assets/images/products/xpstower.png" alt="Desktop computer" class="cat-img">
                            <div class="cat-card-info">
                                <div class="cat-name">Desktops</div>
                                <span class="btn-shop">Shop Now</span>
                            </div>
                        </a>
                    </div>
                    <div class="cat-slide">
                        <a href="store.php" class="cat-card">
                            <img src="assets/images/products/iphone17pro.png" alt="Cellphone" class="cat-img">
                            <div class="cat-card-info">
                                <div class="cat-name">Cellphones</div>
                                <span class="btn-shop">Shop Now</span>
                            </div>
                        </a>
                    </div>
                    <div class="cat-slide">
                        <a href="store.php" class="cat-card">
                            <img src="assets/images/products/sonywh.png" alt="Headphones" class="cat-img">
                            <div class="cat-card-info">
                                <div class="cat-name">Headphones</div>
                                <span class="btn-shop">Shop Now</span>
                            </div>
                        </a>
                    </div>
                    <div class="cat-slide">
                        <a href="store.php" class="cat-card">
                            <img src="assets/images/products/s24ultra.png" alt="Tablet device" class="cat-img">
                            <div class="cat-card-info">
                                <div class="cat-name">Tablet</div>
                                <span class="btn-shop">Shop Now</span>
                            </div>
                        </a>
                    </div>
                    <div class="cat-slide">
                        <a href="store.php" class="cat-card">
                            <img src="assets/images/intel.png" alt="CPU" class="cat-img cat-img-contain">
                            <div class="cat-card-info">
                                <div class="cat-name">CPU</div>
                                <span class="btn-shop">Shop Now</span>
                            </div>
                        </a>
                    </div>
                    <div class="cat-slide">
                        <a href="store.php" class="cat-card">
                            <img src="assets/images/products/nvidiartx4070.png" alt="Graphics card" class="cat-img">
                            <div class="cat-card-info">
                                <div class="cat-name">GPU</div>
                                <span class="btn-shop">Shop Now</span>
                            </div>
                        </a>
                    </div>
                    <div class="cat-slide">
                        <a href="store.php" class="cat-card">
                            <img src="assets/images/products/blackwidow.png" alt="Gaming accessories" class="cat-img">
                            <div class="cat-card-info">
                                <div class="cat-name">Accessories</div>
                                <span class="btn-shop">Shop Now</span>
                            </div>
                        </a>
                    </div>
                </div>
                <button class="cat-arrow cat-arrow-right" type="button" aria-label="Next categories">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>

    <section class="featured-section" id="featured">
        <div class="container">
            <div class="text-center mb-2 fade-up">
                <h2 class="sec-title">Featured <span>Products</span></h2>
            </div>
            <div class="sec-divider mx-auto fade-up"></div>

            <div class="featured-carousel fade-up">
                <button class="product-arrow product-arrow-left" type="button" aria-label="Previous featured products">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="featured-product-slider" id="productGrid" aria-label="Top featured products">
                    <?php foreach ($featuredProducts as $product) {
                        $cat       = isset($product['category']) ? $product['category'] : 'laptop';
                        $badgeType = isset($product['badge_type']) ? $product['badge_type'] : null;
                        $badge     = isset($product['badge']) ? $product['badge'] : null;
                        $brand     = isset($product['brand']) ? $product['brand'] : 'ApeX';
                        $rating    = isset($product['rating']) ? $product['rating'] : '12';
                        $sales     = isset($product['sales']) ? (int)$product['sales'] : 0;
                        $oldPrice  = isset($product['old_price']) ? $product['old_price'] : null;
                    ?>
                        <div class="featured-product-slide product-item" data-cat="<?php echo htmlspecialchars($cat); ?>">
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
                                        <span class="product-sales"><i class="fas fa-bag-shopping"></i> <?php echo number_format($sales); ?> sold</span>
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

                                        <form method="POST" action="actions/favorites_action.php" class="m-0 position-relative" style="display: inline-block; z-index: 2;">
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
                <button class="product-arrow product-arrow-right" type="button" aria-label="Next featured products">
                    <i class="fas fa-chevron-right"></i>
                </button>
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

        document.querySelectorAll('.category-carousel').forEach(carousel => {
            const slider = carousel.querySelector('.category-slider');
            const prev = carousel.querySelector('.cat-arrow-left');
            const next = carousel.querySelector('.cat-arrow-right');

            function slideCategories(direction) {
                const firstCard = slider.querySelector('.cat-slide');
                const gap = parseFloat(getComputedStyle(slider).columnGap) || 0;
                const distance = firstCard ? firstCard.offsetWidth + gap : slider.clientWidth * .75;

                slider.scrollBy({
                    left: direction * distance,
                    behavior: 'smooth'
                });
            }

            prev.addEventListener('click', () => slideCategories(-1));
            next.addEventListener('click', () => slideCategories(1));
        });

        document.querySelectorAll('.featured-carousel').forEach(carousel => {
            const slider = carousel.querySelector('.featured-product-slider');
            const prev = carousel.querySelector('.product-arrow-left');
            const next = carousel.querySelector('.product-arrow-right');

            function slideFeatured(direction) {
                const firstCard = slider.querySelector('.featured-product-slide');
                const gap = parseFloat(getComputedStyle(slider).columnGap) || 0;
                const distance = firstCard ? firstCard.offsetWidth + gap : slider.clientWidth * .75;

                slider.scrollBy({
                    left: direction * distance,
                    behavior: 'smooth'
                });
            }

            prev.addEventListener('click', () => slideFeatured(-1));
            next.addEventListener('click', () => slideFeatured(1));
        });

        document.querySelectorAll('.nav-search-wrap').forEach(searchWrap => {
            const toggle = searchWrap.querySelector('.nav-search-toggle');
            const input = searchWrap.querySelector('.nav-product-search');
            const results = Array.from(searchWrap.querySelectorAll('.nav-search-result'));
            const empty = searchWrap.querySelector('.nav-search-empty');

            function closeSearch() {
                searchWrap.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
            }

            function openSearch() {
                searchWrap.classList.add('open');
                toggle.setAttribute('aria-expanded', 'true');
                setTimeout(() => input?.focus(), 80);
            }

            function filterSearch() {
                const query = (input?.value || '').trim().toLowerCase();
                let visibleCount = 0;

                results.forEach(result => {
                    const isMatch = result.dataset.searchText.includes(query);
                    result.classList.toggle('is-hidden', !isMatch);
                    if (isMatch) visibleCount++;
                });

                empty?.classList.toggle('show', visibleCount === 0);
            }

            toggle.addEventListener('click', event => {
                event.stopPropagation();
                searchWrap.classList.contains('open') ? closeSearch() : openSearch();
            });

            searchWrap.addEventListener('click', event => {
                event.stopPropagation();
            });

            input?.addEventListener('input', filterSearch);

            document.addEventListener('click', closeSearch);
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') closeSearch();
            });
        });
    </script>
</body>

</html>