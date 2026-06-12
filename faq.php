<?php
require_once __DIR__ . '/includes/storage.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FAQ | ApeX Gear</title>
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
                    <h1 class="display-6 fw-bold">Frequently Asked Questions</h1>
                    <p class="lead text-muted">Find quick answers about ordering, shipping, payment, and returns at ApeX Gear.</p>
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
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    How do I place an order?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="faqOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Browse our catalog, add items to your cart, and proceed to checkout. You can pay with credit card, debit card, GCash, Maya, or Cash on Delivery where available.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Can I change my delivery address after ordering?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="faqTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    If your order has not yet shipped, please contact our support team immediately with your order number. Once the order is dispatched, we may not be able to update the address.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="faqThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We accept major credit cards, debit cards, GCash, Maya, and Cash on Delivery for eligible orders. Payment options are shown at checkout.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    How can I track my order?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="faqFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    After shipping, we send a tracking number by email or SMS. You can also visit the Order Tracking page for status updates.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title">Need more help?</h5>
                            <p class="text-muted">Our team is ready to assist with orders, payments, shipping, and returns.</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="fas fa-phone-alt me-2 text-apex-blue"></i>+1 (234) 567-890</li>
                                <li class="mb-2"><i class="fas fa-envelope me-2 text-apex-blue"></i>support@apexgear.com</li>
                                <li><i class="fas fa-clock me-2 text-apex-blue"></i>Mon–Sat 9AM–9PM</li>
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
