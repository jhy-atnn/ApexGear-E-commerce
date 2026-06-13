<?php $currentPage = 'privacy'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Privacy Policy | ApeX Gear</title>
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
    $currentPage = 'privacy';
    include 'includes\navbar.php';
    ?>

    <!-- PAGE HERO -->
    <section class="support-hero">
        <div class="container" style="position:relative; z-index:1;">
            <div class="support-hero-eyebrow">Legal</div>
            <h1 class="support-hero-title">Privacy <em>Policy</em></h1>
            <p class="support-hero-sub">Your trust matters. Learn how ApeX Gear collects, uses, and safeguards your information.</p>
        </div>
    </section>

    <section class="support-section">
        <div class="container" style="max-width:900px;">
            <div class="text-center mb-2 fade-up">
                <h2 class="sec-title">Privacy <span>Policy</span></h2>
            </div>
            <div class="sec-divider mx-auto fade-up"></div>
            <p class="support-intro fade-up">Your trust matters. Here is how we collect, use, and protect your data.</p>
            <div class="support-prose fade-up"><h3>Information We Collect</h3><p>We collect information you provide at checkout and registration — name, contact details, shipping address, and payment information — as well as basic usage data to improve your experience.</p><h3>How We Use Your Data</h3><p>Your data is used to process orders, deliver products, provide support, and (with your consent) send updates and promotions. We never sell your personal information.</p><h3>Payment Security</h3><p>All online payments are processed over 256-bit SSL encryption through trusted payment gateways. We do not store full card numbers on our servers.</p><h3>Cookies</h3><p>We use cookies to remember your cart, preferences, and login session. You can manage cookie preferences through the notice shown on your first visit.</p><h3>Data Sharing</h3><p>We share data only with logistics and payment partners strictly as needed to fulfill your order, and as required by Philippine law.</p><h3>Your Rights</h3><p>Under the Data Privacy Act of 2012, you may access, correct, or request deletion of your personal data at any time by contacting our support team.</p><h3>Contact</h3><p>Questions about this policy? Reach us through the <a href="contact.php">Contact page</a> and our team will respond promptly.</p></div>
        </div>
    </section>

    <?php include 'includes\footer.php'; ?>
</body>

</html>
