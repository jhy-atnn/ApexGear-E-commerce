<?php session_start();
$currentPage = 'terms'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Terms and Conditions | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="icon" href="assets\images\ApeX Logo.png" type="image/png">
    <link href="assets/css/auth-styles-append.css" rel="stylesheet" />
    <link href="assets/css/support-pages.css" rel="stylesheet" />
</head>

<body>
    <?php include_once __DIR__ . '/includes\cookie_notif.php'; ?>

    <!-- INJECT NAVBAR -->
    <?php
    $currentPage = 'terms';
    include 'includes\navbar.php';
    ?>

    <!-- PAGE HERO -->
    <section class="support-hero">
        <div class="container" style="position:relative; z-index:1;">
            <div class="support-hero-eyebrow">Legal</div>
            <h1 class="support-hero-title">Terms &amp; <em>Conditions</em></h1>
            <p class="support-hero-sub">The rules and guidelines for using ApeX Gear and purchasing our products.</p>
        </div>
    </section>

    <section class="support-section">
        <div class="container" style="max-width:900px;">
            <div class="text-center mb-2 fade-up">
                <h2 class="sec-title">Terms &amp; <span>Conditions</span></h2>
            </div>
            <div class="sec-divider mx-auto fade-up"></div>
            <p class="support-intro fade-up">Please read these terms carefully before using ApeX Gear.</p>
            <div class="support-prose fade-up">
                <h3>1. Acceptance of Terms</h3>
                <p>By accessing or using the ApeX Gear website, you agree to be bound by these Terms &amp; Conditions and all applicable laws of the Republic of the Philippines.</p>
                <h3>2. Products &amp; Pricing</h3>
                <p>We strive for accuracy in product descriptions, specs, and pricing. Errors may occur; we reserve the right to correct them and to cancel orders affected by such errors.</p>
                <h3>3. Orders &amp; Payment</h3>
                <p>All orders are subject to acceptance and availability. Payment must be completed through our approved channels before dispatch, unless Cash on Delivery is selected.</p>
                <h3>4. Shipping &amp; Delivery</h3>
                <p>Delivery timelines are estimates and not guarantees. Risk of loss passes to you upon delivery to your specified address.</p>
                <h3>5. Returns &amp; Warranty</h3>
                <p>Returns and warranty claims are governed by our Returns and Warranty policies. Please review them for eligibility and procedures.</p>
                <h3>6. Intellectual Property</h3>
                <p>All content on this site — logos, text, images, and code — is the property of ApeX Gear and may not be reproduced without written permission.</p>
                <h3>7. Limitation of Liability</h3>
                <p>ApeX Gear is not liable for indirect or consequential damages arising from the use of our products or website, to the fullest extent permitted by law.</p>
                <h3>8. Changes to Terms</h3>
                <p>We may update these Terms at any time. Continued use of the site after changes constitutes acceptance of the revised Terms.</p>
                <h3>9. Contact</h3>
                <p>For questions about these Terms, reach us through the <a href="contact.php">Contact page</a>.</p>
            </div>
        </div>
    </section>

    <?php include 'includes\footer.php'; ?>
</body>

</html>