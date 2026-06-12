<?php
require_once __DIR__ . '/includes/storage.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Returns | ApeX Gear</title>
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
                    <h1 class="display-6 fw-bold">Return Policy</h1>
                    <p class="lead text-muted">Learn how to return an item, what conditions apply, and how to get a refund or replacement.</p>
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
                            <h2 class="h4 mb-3">Return Window</h2>
                            <p>Items can be returned within 7 days from delivery, provided they are unused, in original condition, and accompanied by the original packaging.</p>
                            <h2 class="h5 mt-4">How to Request a Return</h2>
                            <ol>
                                <li>Contact support by email or phone with your order number and reason for return.</li>
                                <li>Wait for return approval and shipping instructions from our support team.</li>
                                <li>Ship the item back using a trusted courier and keep the tracking number.</li>
                            </ol>
                            <h2 class="h5 mt-4">Refunds and Replacements</h2>
                            <p>Once we confirm receipt and inspect the item, we process refunds within 5 business days. Replacements are available where stock permits.</p>
                            <h2 class="h5 mt-4">Non-Returnable Items</h2>
                            <p>Opened software, damaged items from misuse, and products missing original accessories may not qualify for return.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title">Return tips</h5>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="fas fa-box-open me-2 text-apex-blue"></i>Keep original packaging.</li>
                                <li class="mb-2"><i class="fas fa-receipt me-2 text-apex-blue"></i>Include your order invoice.</li>
                                <li class="mb-2"><i class="fas fa-shield-alt me-2 text-apex-blue"></i>Use a trackable courier service.</li>
                                <li><i class="fas fa-headset me-2 text-apex-blue"></i>Contact support before shipping.</li>
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
