<?php session_start();
$currentPage = 'returns'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Returns | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link rel="icon" href="assets\images\ApeX Logo.png" type="image/png">
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/auth-styles-append.css" rel="stylesheet" />
    <link href="assets/css/support-pages.css" rel="stylesheet" />
</head>

<body>
    <?php include_once __DIR__ . '/includes\cookie_notif.php'; ?>

    <!-- INJECT NAVBAR -->
    <?php
    $currentPage = 'returns';
    include 'includes\navbar.php';
    ?>

    <!-- PAGE HERO -->
    <section class="support-hero">
        <div class="container" style="position:relative; z-index:1;">
            <div class="support-hero-eyebrow">Returns</div>
            <h1 class="support-hero-title">Easy <em>Returns</em></h1>
            <p class="support-hero-sub">Our hassle-free 30-day return policy means you can shop with total confidence.</p>
        </div>
    </section>

    <section class="support-section">
        <div class="container" style="max-width:900px;">
            <div class="text-center mb-2 fade-up">
                <h2 class="sec-title">Returns &amp; <span>Refunds</span></h2>
            </div>
            <div class="sec-divider mx-auto fade-up"></div>
            <p class="support-intro fade-up">Changed your mind? No problem. Our hassle-free policy has you covered.</p>
            <div class="support-steps fade-up">
                <div class="support-step">
                    <div class="support-step-num">1</div>
                    <div>
                        <div class="support-step-title">Request a Return</div>
                        <p>Contact support within the return window with your order number and reason for return.</p>
                    </div>
                </div>
                <div class="support-step">
                    <div class="support-step-num">2</div>
                    <div>
                        <div class="support-step-title">Pack the Item</div>
                        <p>Repack the product in its original packaging with all accessories and freebies included.</p>
                    </div>
                </div>
                <div class="support-step">
                    <div class="support-step-num">3</div>
                    <div>
                        <div class="support-step-title">Ship It Back</div>
                        <p>We arrange pickup or provide a drop-off point through our courier partners.</p>
                    </div>
                </div>
                <div class="support-step">
                    <div class="support-step-num">4</div>
                    <div>
                        <div class="support-step-title">Get Your Refund</div>
                        <p>Once inspected and approved, your refund is processed to your original payment method.</p>
                    </div>
                </div>
            </div>
            <div class="support-prose fade-up">
                <h3>Return Window</h3>
                <p>Eligible products may be returned within <strong>30 days</strong> for brand-new items and <strong>7 days</strong> for certified pre-owned units, provided they are in resalable condition.</p>
                <h3>Non-Returnable Items</h3>
                <p>Software, digital licenses, and items damaged through misuse are not eligible for return. Opened consumables may be subject to inspection.</p>
                <h3>Refund Timeline</h3>
                <p>Approved refunds are processed within 5–7 business days. Card refunds may take an additional billing cycle to reflect.</p>
            </div>
        </div>
    </section>

    <?php include 'includes\footer.php'; ?>
</body>

</html>