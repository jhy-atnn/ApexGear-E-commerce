<?php $currentPage = 'order-tracking'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order Tracking | ApeX Gear</title>
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
    $currentPage = 'order-tracking';
    include 'includes\navbar.php';
    ?>

    <!-- PAGE HERO -->
    <section class="support-hero">
        <div class="container" style="position:relative; z-index:1;">
            <div class="support-hero-eyebrow">Tracking</div>
            <h1 class="support-hero-title">Order <em>Tracking</em></h1>
            <p class="support-hero-sub">Enter your order number and email to see exactly where your gear is.</p>
        </div>
    </section>

    <section class="support-section">
        <div class="container" style="max-width:760px;">
            <div class="text-center mb-2 fade-up">
                <h2 class="sec-title">Track Your <span>Order</span></h2>
            </div>
            <div class="sec-divider mx-auto fade-up"></div>
            <div class="track-box fade-up">
                <form method="POST" action="actions/track_order_action.php" class="track-form">
                    <label for="order_id">Order Number</label>
                    <input type="text" id="order_id" name="order_id" class="form-control" placeholder="e.g. APX-2026-018245" required>
                    <label for="track_email">Email Address</label>
                    <input type="email" id="track_email" name="email" class="form-control" placeholder="you@example.com" required>
                    <button type="submit" class="btn-apex w-100 mt-3"><i class="fas fa-location-arrow me-2"></i>Track Order</button>
                </form>
            </div>
            <div class="track-help fade-up">
                <p>Your order number is in your confirmation email. Can't find it? <a href="contact.php">Contact support</a> and we'll locate your order for you.</p>
            </div>
            <div class="track-stages fade-up">
                <div class="track-stage"><i class="fas fa-receipt"></i><span>Order Placed</span></div>
                <div class="track-stage"><i class="fas fa-box"></i><span>Packed</span></div>
                <div class="track-stage"><i class="fas fa-truck"></i><span>Shipped</span></div>
                <div class="track-stage"><i class="fas fa-house-circle-check"></i><span>Delivered</span></div>
            </div>
        </div>
    </section>

    <?php include 'includes\footer.php'; ?>
</body>

</html>
