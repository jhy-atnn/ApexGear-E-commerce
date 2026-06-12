<?php
require_once __DIR__ . '/includes/storage.php';
?>
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
</head>

<body>
    <?php include_once __DIR__ . '/includes/cookie_notif.php'; ?>

    <?php
    $currentPage = '';
    include 'includes/navbar.php';
    ?>

    <section class="py-5" style="background: rgba(7, 10, 28, 0.95); color: white;">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-8">
                    <h1 class="display-6 fw-bold">Order Tracking</h1>
                    <p class="lead text-muted">Check the status of your purchase and follow the delivery progress from our warehouse to your doorstep.</p>
                </div>
                <div class="col-lg-4 text-lg-end d-flex flex-column align-items-lg-end gap-3">
                    <a href="index.php" class="btn btn-outline-light btn-lg">Back to Home</a>
                    
                </div>
            </div>
        </div>
    </section>

    <main class="py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h2 class="h4 mb-3">How to Track Your Order</h2>
                            <p>After your order is shipped, we send a tracking number to your email or phone. Use that number with the courier to view the latest status.</p>
                            <div class="timeline">
                                <div class="mb-4">
                                    <h3 class="h5">Order confirmed</h3>
                                    <p>We received your order and payment. Our team is preparing it for shipment.</p>
                                </div>
                                <div class="mb-4">
                                    <h3 class="h5">Order shipped</h3>
                                    <p>Your package is on the way, and tracking details have been shared by email/SMS.</p>
                                </div>
                                <div class="mb-4">
                                    <h3 class="h5">Out for delivery</h3>
                                    <p>The courier is delivering your package to your address. Please be available to receive it.</p>
                                </div>
                                <div class="mb-4">
                                    <h3 class="h5">Delivered</h3>
                                    <p>The order has been delivered. If you need help after delivery, contact support right away.</p>
                                </div>
                            </div>
                            <h2 class="h5 mt-4">Can’t find your tracking number?</h2>
                            <p>If you did not receive tracking details, contact support with your order number and we will resend the information.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title">Track your order</h5>
                            <p class="text-muted">Keep your order number and delivery address ready.</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="fas fa-hashtag me-2 text-apex-blue"></i>Order number</li>
                                <li class="mb-2"><i class="fas fa-user-check me-2 text-apex-blue"></i>Recipient name</li>
                                <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-apex-blue"></i>Shipping address</li>
                                <li><i class="fas fa-phone me-2 text-apex-blue"></i>Delivery contact</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
