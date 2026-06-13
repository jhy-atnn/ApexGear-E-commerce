<?php $currentPage = 'warranty'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Warranty | ApeX Gear</title>
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
    $currentPage = 'warranty';
    include 'includes\navbar.php';
    ?>

    <!-- PAGE HERO -->
    <section class="support-hero">
        <div class="container" style="position:relative; z-index:1;">
            <div class="support-hero-eyebrow">Protection</div>
            <h1 class="support-hero-title">Warranty <em>Coverage</em></h1>
            <p class="support-hero-sub">Every product we carry is backed by a warranty and a team that knows tech inside-out.</p>
        </div>
    </section>

    <section class="support-section">
        <div class="container" style="max-width:900px;">
            <div class="text-center mb-2 fade-up">
                <h2 class="sec-title">Warranty <span>Coverage</span></h2>
            </div>
            <div class="sec-divider mx-auto fade-up"></div>
            <p class="support-intro fade-up">We stand behind every product we sell — with real warranties and honest support.</p>
            <div class="row g-4 fade-up">
                <div class="col-md-6">
                    <div class="why-card" style="height:100%;">
                        <i class="fas fa-shield-alt"></i>
                        <div class="why-card-title">Manufacturer Warranty</div>
                        <p>Brand-new items carry the full manufacturer warranty, honored through our authorized partner network.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="why-card" style="height:100%;">
                        <i class="fas fa-certificate"></i>
                        <div class="why-card-title">ApeX Certified Warranty</div>
                        <p>Certified pre-owned units include a limited ApeX warranty covering functional defects.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="why-card" style="height:100%;">
                        <i class="fas fa-screwdriver-wrench"></i>
                        <div class="why-card-title">Repair &amp; Service</div>
                        <p>Our in-house tech team handles diagnostics, repairs, and warranty claims directly.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="why-card" style="height:100%;">
                        <i class="fas fa-rotate"></i>
                        <div class="why-card-title">Replacement Units</div>
                        <p>Where repair isn't possible within the warranty period, we provide a replacement or store credit.</p>
                    </div>
                </div></div><div class="support-prose fade-up"><h3>Coverage Periods</h3><p>Warranty periods vary by product and brand — typically 12 months for new laptops and desktops, and 3–6 months for certified pre-owned units. The exact term is listed on each product page.</p><h3>What's Covered</h3><p>Manufacturing defects, hardware failures under normal use, and dead-on-arrival units. Coverage excludes physical/liquid damage, unauthorized modifications, and normal wear.</p><h3>How to Claim</h3><p>Contact support with your order number and a description of the issue. Our tech team will guide you through diagnostics and arrange repair, replacement, or refund as applicable.</p></div>
        </div>
    </section>

    <?php include 'includes\footer.php'; ?>
</body>

</html>
