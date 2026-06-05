<?php
session_start();
require_once 'classes/Inventory.php';

// Fetch inventory
$inventoryManager = new Inventory();
$all_products = $inventoryManager->getAllProducts();

// Dynamically generate the sale items array from the inventory
$sale_items = [];
foreach ($all_products as $item) {
    // Only include items that have an old_price greater than their current price
    if (!empty($item['old_price']) && $item['old_price'] > $item['price']) {
        // Calculate the dynamic discount percentage
        $discount = round((($item['old_price'] - $item['price']) / $item['old_price']) * 100);
        $item['discount'] = $discount;
        $sale_items[] = $item;
    }
}

$bundles = [
    [
        'title'     => 'Ultimate Gaming Setup',
        'items'     => ['Legion 5 Pro Laptop', 'BlackWidow V4 Keyboard', 'G Pro X Mouse'],
        'price'     => 134995.00,
        'old_price' => 152989.00,
        'discount'  => 12,
        'icon'      => 'fa-gamepad',
    ],
    [
        'title'     => 'Creator Workstation Bundle',
        'items'     => ['XPS Tower i9', 'UltraGear 27" Monitor', 'WH-1000XM5 Headphones'],
        'price'     => 235000.00,
        'old_price' => 266994.00,
        'discount'  => 12,
        'icon'      => 'fa-desktop',
    ],
    [
        'title'     => 'Mobile Warrior Pack',
        'items'     => ['Galaxy S24 Ultra', 'ROG Zephyrus G14', 'G Pro X Mouse'],
        'price'     => 195000.00,
        'old_price' => 223980.00,
        'discount'  => 13,
        'icon'      => 'fa-mobile-alt',
    ],
];

$flash_deal_ids = [10, 11, 12, 13];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Deals &amp; Offers | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/deals.css">
    <link href="assets/css/auth-styles-append.css" rel="stylesheet" />
</head>

<body>
    <?php include_once __DIR__ . '/includes\cookie_notif.php'; ?>

    <!-- INJECT NAVBAR -->
    <?php
    $currentPage = 'deals';
    include 'includes/navbar.php';
    ?>

    <!-- ── DEALS HERO ── -->
    <section class="deals-hero">
        <div class="container deals-hero-content">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="deals-eyebrow"><i class="fas fa-bolt me-2"></i>Limited Time Offers</div>
                    <h1 class="deals-hero-title">
                        Hot <em>Deals</em><br>&amp; Flash<br>Sales
                    </h1>
                    <p class="deals-hero-sub">Massive discounts on top-tier laptops, desktops, phones, and peripherals. Grab them before they're gone.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#flash-deals" class="btn-apex">View Flash Deals</a>
                        <a href="#sale-items" class="btn-apex-outline">All Sale Items</a>
                    </div>
                </div>
                <div class="col-lg-5 mt-5 mt-lg-0 text-center text-lg-end">
                    <div class="mb-3" style="color:rgba(255,255,255,.55); font-family:'Barlow Condensed',sans-serif; font-size:.8rem; letter-spacing:.2em; text-transform:uppercase;">Sale Ends In</div>
                    <div class="countdown-wrap justify-content-center justify-content-lg-end" id="heroCountdown">
                        <div class="countdown-unit"><span class="countdown-num" id="hcDays">00</span><span class="countdown-lbl">Days</span></div>
                        <div class="countdown-unit"><span class="countdown-num" id="hcHrs">00</span><span class="countdown-lbl">Hours</span></div>
                        <div class="countdown-unit"><span class="countdown-num" id="hcMins">00</span><span class="countdown-lbl">Mins</span></div>
                        <div class="countdown-unit"><span class="countdown-num" id="hcSecs">00</span><span class="countdown-lbl">Secs</span></div>
                    </div>
                    <!-- Big % graphic -->
                    <div style="font-family:'Barlow Condensed',sans-serif; font-size:7rem; font-weight:900; line-height:1; color:rgba(0,194,255,.18); margin-top:10px; user-select:none;">
                        UP TO<br><span style="font-size:9rem; color:rgba(0,194,255,.28);">25%</span><br>OFF
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── FLASH DEALS ── -->
    <section class="sale-section" id="flash-deals">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-2 fade-up">
                <div>
                    <h2 class="sec-title">Flash <span>Deals</span></h2>
                </div>
                <a href="store.php" style="color: var(--apex-blue); font-weight:600; font-size:.88rem; text-decoration:none;">
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
                            <p style="color:rgba(255,255,255,.6); font-size:.88rem; margin-bottom:24px;">Massive discounts on keyboards, mice, headsets, and more. Don't miss out.</p>
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
                            <a href="#sale-items" class="btn-apex">Shop Flash Deals</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="row g-3">
                        <!-- Dynamically load the specific Flash Deals -->
                        <?php foreach ($flash_deal_ids as $fid):
                            if (isset($all_products[$fid])):
                                $f_item = $all_products[$fid];
                                $f_discount = round((($f_item['old_price'] - $f_item['price']) / $f_item['old_price']) * 100);
                        ?>
                                <div class="col-sm-6">
                                    <div class="product-card h-100 position-relative" style="border-color:#ff3b5c;">
                                        <div class="product-badge sale">–<?php echo $f_discount; ?>%</div>
                                        <div class="product-img" style="height:150px; display: flex; align-items: center; justify-content: center;">
                                            <?php if (strpos($f_item['image'], '<svg') !== false): ?>
                                                <?php echo $f_item['image']; ?>
                                            <?php else: ?>
                                                <img src="<?php echo htmlspecialchars($f_item['image']); ?>" alt="<?php echo htmlspecialchars($f_item['name']); ?>" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-body">
                                            <div class="product-brand"><?php echo htmlspecialchars($f_item['brand']); ?></div>
                                            <div class="product-name"><?php echo htmlspecialchars($f_item['name']); ?></div>
                                            <div class="product-price" style="font-size:1.2rem;">₱<?php echo number_format($f_item['price'], 2); ?> <span class="old">₱<?php echo number_format($f_item['old_price'], 2); ?></span></div>
                                            <a href="product.php?id=<?php echo $f_item['id']; ?>" class="btn-shop d-block text-center mt-2 stretched-link">View Deal</a>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── PROMO CODE BANNER ── -->
    <section class="promo-banner">
        <div class="container promo-banner-content">
            <div class="row align-items-center gy-4">
                <div class="col-lg-7">
                    <div class="section-label"><i class="fas fa-tag me-1"></i> Exclusive Code</div>
                    <div class="promo-title">Extra <span>10% Off</span><br>Your Entire Order</div>
                    <p class="promo-sub">Use the code below at checkout. Valid on all sale items. One use per account. Limited time only.</p>
                    <div class="promo-code-box">
                        <div class="promo-code" id="promoCode">APEX10</div>
                        <button class="promo-copy-btn" onclick="copyCode()"><i class="fas fa-copy me-1"></i> Copy</button>
                    </div>
                    <div id="copiedMsg" style="display:none; color:var(--apex-accent); font-size:.82rem; margin-top:8px; font-weight:600;"><i class="fas fa-check me-1"></i> Copied to clipboard!</div>
                </div>
                <div class="col-lg-5 text-center">
                    <div style="font-family:'Barlow Condensed',sans-serif; font-size:6rem; font-weight:900; line-height:.9; color:rgba(255,255,255,.08); user-select:none;">PROMO<br>CODE</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── ALL SALE ITEMS (Dynamically loaded from Inventory) ── -->
    <section class="sale-section" id="sale-items">
        <div class="container">
            <div class="row align-items-end mb-5">
                <div class="col">
                    <div class="section-label"><i class="fas fa-tags me-1"></i> On Sale Now</div>
                    <h2 class="section-title">All <span>Sale</span> Items</h2>
                </div>
                <div class="col-auto">
                    <a href="store.php" class="btn-apex" style="padding:9px 22px; font-size:.82rem;">Shop Full Store</a>
                </div>
            </div>
            <div class="row g-4">
                <?php if (empty($sale_items)): ?>
                    <div class="col-12 text-center py-5">
                        <h4 class="text-muted">No items are currently on sale. Check back later!</h4>
                    </div>
                <?php else: ?>
                    <?php foreach ($sale_items as $item): ?>
                        <div class="col-sm-6 col-lg-4">
                            <div class="sale-card">
                                <div class="sale-discount-badge">-<?php echo $item['discount']; ?>%</div>
                                <div class="sale-img-wrap">
                                    <?php if (strpos($item['image'], '<svg') !== false): ?>
                                        <?php echo $item['image']; ?>
                                    <?php else: ?>
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="max-height: 150px; max-width: 100%; object-fit: contain;">
                                    <?php endif; ?>
                                </div>
                                <div class="sale-body">
                                    <div class="sale-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                                    <div class="sale-name" style="min-height: 48px;"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="sale-rating">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                        <span>(<?php echo isset($item['rating']) ? $item['rating'] : 0; ?>)</span>
                                    </div>
                                    <div class="sale-price">₱<?php echo number_format($item['price'], 2); ?></div>
                                    <div class="sale-old">₱<?php echo number_format($item['old_price'], 2); ?></div>
                                    <a href="product.php?id=<?php echo $item['id']; ?>" class="btn-sale">View Deal</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ── BUNDLE DEALS ── -->
    <section class="bundle-section" id="bundles">
        <div class="container">
            <div class="row align-items-end mb-5">
                <div class="col">
                    <div class="section-label"><i class="fas fa-layer-group me-1"></i> Bundle Deals</div>
                    <h2 class="section-title">Save More, <span>Bundle Up</span></h2>
                </div>
            </div>
            <div class="row g-4">
                <?php foreach ($bundles as $bundle): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="bundle-card">
                            <div class="bundle-icon"><i class="fas <?php echo $bundle['icon']; ?>"></i></div>
                            <div class="bundle-title"><?php echo htmlspecialchars($bundle['title']); ?></div>
                            <ul class="bundle-items">
                                <?php foreach ($bundle['items'] as $bItem): ?>
                                    <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($bItem); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="bundle-savings">You save <?php echo $bundle['discount']; ?>% — ₱<?php echo number_format($bundle['old_price'] - $bundle['price'], 2); ?></div>
                            <div class="bundle-price">₱<?php echo number_format($bundle['price'], 2); ?></div>
                            <div class="bundle-old">₱<?php echo number_format($bundle['old_price'], 2); ?></div>
                            <a href="store.php" class="btn-bundle"><i class="fas fa-box me-1"></i> Get Bundle</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ── NEWSLETTER SIGNUP ── -->
    <section class="deals-newsletter">
        <div class="container">
            <div class="newsletter-inner">
                <div class="row align-items-center gy-4">
                    <div class="col-lg-6">
                        <div class="section-label"><i class="fas fa-envelope me-1"></i> Stay in the Loop</div>
                        <div class="newsletter-title">Get <span>Deals</span> First</div>
                        <p class="newsletter-sub">Subscribe and be the first to know about flash sales, exclusive promos, and new arrivals. No spam — just gear.</p>
                    </div>
                    <div class="col-lg-6">
                        <div class="newsletter-form">
                            <input type="email" class="newsletter-input" placeholder="Your email address…" />
                            <a href="#" class="btn-apex" style="white-space:nowrap; padding:11px 28px;">Subscribe</a>
                        </div>
                        <p style="font-size:.75rem; color:var(--apex-muted); margin-top:10px;"><i class="fas fa-lock me-1"></i> We respect your privacy. Unsubscribe anytime.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
        // ── HERO COUNTDOWN (3 days from page load) ──
        (function() {
            const end = Date.now() + (3 * 24 * 60 * 60 * 1000);

            function tick() {
                const diff = Math.max(0, end - Date.now());
                const d = Math.floor(diff / 86400000);
                const h = Math.floor((diff % 86400000) / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                const pad = n => String(n).padStart(2, '0');
                document.getElementById('hcDays').textContent = pad(d);
                document.getElementById('hcHrs').textContent = pad(h);
                document.getElementById('hcMins').textContent = pad(m);
                document.getElementById('hcSecs').textContent = pad(s);
            }
            tick();
            setInterval(tick, 1000);
        })();

        // ── FLASH DEALS BANNER COUNTDOWN (8 hrs from page load) ──
        (function() {
            const end = Date.now() + (8 * 60 * 60 * 1000);

            function tick() {
                const diff = Math.max(0, end - Date.now());
                const h = Math.floor(diff / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                const pad = n => String(n).padStart(2, '0');
                const cdH = document.getElementById('cd-h');
                const cdM = document.getElementById('cd-m');
                const cdS = document.getElementById('cd-s');
                if (cdH) cdH.textContent = pad(h);
                if (cdM) cdM.textContent = pad(m);
                if (cdS) cdS.textContent = pad(s);
            }
            tick();
            setInterval(tick, 1000);
        })();

        // ── PROMO CODE COPY ──
        function copyCode() {
            const code = document.getElementById('promoCode').textContent.trim();
            navigator.clipboard.writeText(code).then(function() {
                const msg = document.getElementById('copiedMsg');
                msg.style.display = 'block';
                setTimeout(function() {
                    msg.style.display = 'none';
                }, 3000);
            });
        }
    </script>
</body>

</html>