<?php
require_once __DIR__ . '/includes/storage.php';
?>
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
                    <h1 class="display-6 fw-bold">Shipping Policy</h1>
                    <p class="lead text-muted">Know how we ship your order, how long it takes, and what to expect when your package is on the way.</p>
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
                            <h2 class="h4 mb-3">Delivery Times</h2>
                            <p>Most orders are processed within 1-2 business days. Delivery generally takes 2-5 business days within Metro Manila and 3-8 business days for provincial addresses.</p>
                            <h2 class="h5 mt-4">Shipping Fees</h2>
                            <ul>
                                <li>Free shipping for orders above ₱5,000 within Metro Manila.</li>
                                <li>Flat rate shipping applies for orders below ₱5,000 and to provincial destinations.</li>
                                <li>Cash on Delivery orders may have an additional handling fee.</li>
                            </ul>
                            <h2 class="h5 mt-4">Tracking Your Order</h2>
                            <p>Once your order is shipped, you will receive a tracking number via email or SMS. Use that tracking number with the courier partner to monitor delivery progress.</p>
                            <h2 class="h5 mt-4">Delivery Restrictions</h2>
                            <p>We currently deliver to most areas in the Philippines. Delivery to some remote or highland locations may take longer and may require additional courier coordination.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title">Before you buy</h5>
                            <p class="text-muted">Please make sure your shipping details are complete and accurate to avoid delays.</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-apex-blue"></i>Full name</li>
                                <li class="mb-2"><i class="fas fa-road me-2 text-apex-blue"></i>Street address</li>
                                <li class="mb-2"><i class="fas fa-city me-2 text-apex-blue"></i>City / Municipality</li>
                                <li><i class="fas fa-mail-bulk me-2 text-apex-blue"></i>Postal / ZIP code</li>
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
