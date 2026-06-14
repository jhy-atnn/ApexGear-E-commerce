<?php session_start(); $currentPage = 'shipping'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shipping Policy | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/auth-styles-append.css" rel="stylesheet" />
    <link href="assets/css/support-pages.css" rel="stylesheet" />
</head>

<body>
    <?php include_once __DIR__ . '/includes\cookie_notif.php'; ?>

    <!-- INJECT NAVBAR -->
    <?php
    $currentPage = 'shipping';
    include 'includes\navbar.php';
    ?>

    <!-- PAGE HERO -->
    <section class="support-hero">
        <div class="container" style="position:relative; z-index:1;">
            <div class="support-hero-eyebrow">Delivery</div>
            <h1 class="support-hero-title">Shipping <em>Policy</em></h1>
            <p class="support-hero-sub">Lightning-fast nationwide shipping. Most orders arrive within 1–3 business days.</p>
        </div>
    </section>

    <section class="support-section">
        <div class="container" style="max-width:900px;">
            <div class="text-center mb-2 fade-up">
                <h2 class="sec-title">Shipping <span>Policy</span></h2>
            </div>
            <div class="sec-divider mx-auto fade-up"></div>
            <p class="support-intro fade-up">Fast, reliable, nationwide delivery via our trusted courier partners.</p>
            <div class="row g-4 fade-up">
                <div class="col-md-6">
                    <div class="why-card" style="height:100%;">
                        <i class="fas fa-shipping-fast"></i>
                        <div class="why-card-title">Nationwide Delivery</div>
                        <p>We ship anywhere in the Philippines through trusted courier partners with full tracking.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="why-card" style="height:100%;">
                        <i class="fas fa-bolt"></i>
                        <div class="why-card-title">Same-Day Dispatch</div>
                        <p>In-stock orders placed before our daily cut-off are dispatched the same business day.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="why-card" style="height:100%;">
                        <i class="fas fa-clock"></i>
                        <div class="why-card-title">1–3 Business Days</div>
                        <p>Most metro orders arrive within 1–3 business days. Provincial areas may take slightly longer.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="why-card" style="height:100%;">
                        <i class="fas fa-box-open"></i>
                        <div class="why-card-title">Secure Packaging</div>
                        <p>Every item is packed with protective materials to ensure it arrives in perfect condition.</p>
                    </div>
                </div></div><div class="support-rate-table fade-up"><h3>Shipping Rates &amp; Timelines</h3><table class="table"><thead><tr><th>Region</th><th>Estimated Time</th><th>Fee</th></tr></thead><tbody><tr><td>Metro Manila</td><td>1–2 business days</td><td>₱120</td></tr><tr><td>Luzon</td><td>2–4 business days</td><td>₱160</td></tr><tr><td>Visayas</td><td>3–5 business days</td><td>₱190</td></tr><tr><td>Mindanao</td><td>3–6 business days</td><td>₱210</td></tr><tr><td>Orders over ₱5,000</td><td>—</td><td><strong style="color:var(--apex-blue)">FREE</strong></td></tr></tbody></table></div>
        </div>
    </section>

    <?php include 'includes\footer.php'; ?>
</body>

</html>
