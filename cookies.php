<?php session_start(); $currentPage = 'cookies'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cookie Policy | ApeX Gear</title>
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
    $currentPage = 'cookies';
    include 'includes\navbar.php';
    ?>

    <!-- PAGE HERO -->
    <section class="support-hero">
        <div class="container" style="position:relative; z-index:1;">
            <div class="support-hero-eyebrow">Legal</div>
            <h1 class="support-hero-title">Cookie <em>Policy</em></h1>
            <p class="support-hero-sub">How and why we use cookies to keep your ApeX Gear experience smooth and personalized.</p>
        </div>
    </section>

    <section class="support-section">
        <div class="container" style="max-width:900px;">
            <div class="text-center mb-2 fade-up">
                <h2 class="sec-title">Cookie <span>Policy</span></h2>
            </div>
            <div class="sec-divider mx-auto fade-up"></div>
            <p class="support-intro fade-up">How and why ApeX Gear uses cookies to improve your experience.</p>
            <div class="support-prose fade-up"><h3>What Are Cookies?</h3><p>Cookies are small text files stored on your device that help websites remember your preferences, cart contents, and login session.</p><h3>Essential Cookies</h3><p>These are required for core functionality — your shopping cart, secure checkout, and login session. The site cannot function properly without them.</p><h3>Preference Cookies</h3><p>These remember your settings, such as recently viewed products and display preferences, to personalize your visit.</p><h3>Analytics Cookies</h3><p>We use anonymized analytics to understand how visitors use the site so we can improve performance and content.</p><h3>Managing Cookies</h3><p>You can accept or decline non-essential cookies through the cookie notice shown on your first visit, or by adjusting your browser settings at any time.</p><h3>Updates</h3><p>We may update this Cookie Policy periodically. Any changes will be reflected on this page with a revised date.</p><h3>Contact</h3><p>For questions about our use of cookies, please <a href="contact.php">contact our team</a>.</p></div>
        </div>
    </section>

    <?php include 'includes\footer.php'; ?>
</body>

</html>
