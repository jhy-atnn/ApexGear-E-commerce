<?php
require_once __DIR__ . '/includes/storage.php';
?>
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
                    <h1 class="display-6 fw-bold">Privacy Policy</h1>
                    <p class="lead text-muted">Learn how ApeX Gear collects, uses, and protects your personal information.</p>
                </div>
                <div class="col-lg-4 text-lg-end d-flex flex-column align-items-lg-end gap-3">
                    <a href="index.php" class="btn btn-outline-light btn-lg">Back to Home</a>
                    <a href="contact.php" class="btn btn-light btn-sm">Contact Support</a>
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
                            <h2 class="h4 mb-3">Information We Collect</h2>
                            <p>We collect the details you provide when you create an account, place an order, or communicate with our support team. This can include your name, email, phone number, shipping address, and order history.</p>
                            <h2 class="h5 mt-4">How We Use Your Information</h2>
                            <ul>
                                <li>Process and deliver your orders.</li>
                                <li>Provide customer service and order updates.</li>
                                <li>Send you promotional offers only when you opt-in.</li>
                            </ul>
                            <h2 class="h5 mt-4">How We Protect Your Data</h2>
                            <p>We take reasonable measures to protect your information from unauthorized access, but please remember that no online system is completely secure.</p>
                            <h2 class="h5 mt-4">Sharing with Third Parties</h2>
                            <p>We only share your information with shipping partners, payment processors, and service providers needed to fulfill your order. We do not sell your personal data.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title">Your privacy rights</h5>
                            <p class="text-muted">You can request access to your information, ask for corrections, or contact us about your privacy concerns.</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="fas fa-user-shield me-2 text-apex-blue"></i>Data access</li>
                                <li class="mb-2"><i class="fas fa-edit me-2 text-apex-blue"></i>Update your details</li>
                                <li class="mb-2"><i class="fas fa-trash-alt me-2 text-apex-blue"></i>Request removal</li>
                                <li><i class="fas fa-envelope me-2 text-apex-blue"></i>Contact support@apexgear.com</li>
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
