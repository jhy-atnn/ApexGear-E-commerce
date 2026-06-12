<?php
require_once __DIR__ . '/includes/storage.php';
?>
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
                    <h1 class="display-6 fw-bold">Product Warranty</h1>
                    <p class="lead text-muted">Understand the warranty coverage for your purchase and how to file a claim if needed.</p>
                </div>
                <div class="col-lg-4 text-lg-end d-flex flex-column align-items-lg-end gap-3">
                    <a href="index.php" class="btn btn-outline-light btn-lg">Back to Home</a>
                    <a href="store.php" class="btn btn-light btn-sm">Browse Products</a>
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
                            <h2 class="h4 mb-3">Warranty Overview</h2>
                            <p>Most products purchased from ApeX Gear come with a 6-month limited warranty covering manufacturing defects and hardware failures.</p>
                            <h2 class="h5 mt-4">What is covered?</h2>
                            <ul>
                                <li>Defective components or parts.</li>
                                <li>Non-functioning electronics not caused by physical damage.</li>
                                <li>Failure due to improper assembly if sold as pre-owned or refurbished with warranty.</li>
                            </ul>
                            <h2 class="h5 mt-4">What is not covered?</h2>
                            <ul>
                                <li>Damage caused by drops, spills, or misuse.</li>
                                <li>Cosmetic wear and tear.</li>
                                <li>Issues caused by third-party repairs or unauthorized modifications.</li>
                            </ul>
                            <h2 class="h5 mt-4">How to claim warranty</h2>
                            <p>Contact support with your order number, product details, and description of the issue. We will provide steps for diagnostics, repair, or replacement.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title">Keep these ready</h5>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="fas fa-receipt me-2 text-apex-blue"></i>Order invoice</li>
                                <li class="mb-2"><i class="fas fa-barcode me-2 text-apex-blue"></i>Product serial or model number</li>
                                <li class="mb-2"><i class="fas fa-calendar-check me-2 text-apex-blue"></i>Date of purchase</li>
                                <li><i class="fas fa-comments me-2 text-apex-blue"></i>Problem description</li>
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
