<?php
session_start();

$cart_items = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart_items) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: store.php");
    exit();
}

if (empty($cart_items) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    header("Location: store.php");
    exit();
}

$order_successful = false;
$receipt_number = '';
$receipt_data = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $receipt_number = 'APX-' . strtoupper(uniqid());
    
    // Store receipt data
    $receipt_data = array(
        'firstName' => isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '',
        'lastName' => isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '',
        'email' => isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '',
        'phone' => isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '',
        'address' => isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '',
        'city' => isset($_POST['city']) ? htmlspecialchars($_POST['city']) : '',
        'zipcode' => isset($_POST['zipcode']) ? htmlspecialchars($_POST['zipcode']) : '',
        'paymentMethod' => isset($_POST['payment_method']) ? htmlspecialchars($_POST['payment_method']) : '',
        'orderDate' => date('F d, Y \a\t h:i A'),
        'items' => $cart_items
    );
    
    // Store payment-specific details based on method
    $paymentMethod = isset($_POST['payment_method']) ? htmlspecialchars($_POST['payment_method']) : '';
    if ($paymentMethod === 'card') {
        $receipt_data['cardName'] = isset($_POST['card_name']) ? htmlspecialchars($_POST['card_name']) : '';
        $receipt_data['cardLast4'] = '****' . substr(preg_replace('/\s+/', '', $_POST['card_number']), -4);
    } elseif ($paymentMethod === 'gcash') {
        $receipt_data['gcashName'] = isset($_POST['gcash_name']) ? htmlspecialchars($_POST['gcash_name']) : '';
        $receipt_data['gcashMobile'] = isset($_POST['gcash_mobile']) ? htmlspecialchars($_POST['gcash_mobile']) : '';
    } elseif ($paymentMethod === 'paypal') {
        $receipt_data['paypalEmail'] = isset($_POST['paypal_email']) ? htmlspecialchars($_POST['paypal_email']) : '';
    } elseif ($paymentMethod === 'maya') {
        $receipt_data['mayaName'] = isset($_POST['maya_name']) ? htmlspecialchars($_POST['maya_name']) : '';
        $receipt_data['mayaMobile'] = isset($_POST['maya_mobile']) ? htmlspecialchars($_POST['maya_mobile']) : '';
    }
    
    // Calculate item count and totals BEFORE storing in receipt
    $subtotal = 0;
    $item_count = 0;
    foreach ($cart_items as $item) {
        $subtotal += ($item['price'] * $item['qty']);
        $item_count += $item['qty'];
    }
    $tax = $subtotal * 0.08;
    $grand_total = $subtotal + $tax;
    
    // Store item count and totals
    $receipt_data['itemCount'] = $item_count;
    $receipt_data['subtotal'] = $subtotal;
    $receipt_data['tax'] = $tax;
    $receipt_data['grandTotal'] = $grand_total;
    $receipt_data['receiptNumber'] = $receipt_number;
    
    // Store in session for potential future use
    $_SESSION['last_receipt'] = $receipt_data;
    
    unset($_SESSION['cart']);
    $order_successful = true;
} else {
    // Calculate totals for display (only when NOT processing an order)
    $subtotal = 0;
    $item_count = 0;
    foreach ($cart_items as $item) {
        $subtotal += ($item['price'] * $item['qty']);
        $item_count += $item['qty'];
    }
    $tax = $subtotal * 0.08;
    $grand_total = $subtotal + $tax;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .form-control.is-invalid {
            border-color: #dc3545 !important;
            background-image: none;
        }
        .form-control.is-invalid:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        
        @media print {
            body {
                background: white;
            }
            nav, footer, .d-none, .offcanvas {
                display: none !important;
            }
            .inner-page {
                padding: 0;
            }
            .container {
                max-width: 100%;
                padding: 20px;
            }
            .apex-card {
                box-shadow: none;
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }
            .bg-light {
                background-color: white !important;
                border: 1px solid #ddd !important;
            }
            .alert {
                border: 1px solid #ddd !important;
                background: white !important;
                color: #333 !important;
            }
            table {
                page-break-inside: avoid;
            }
            #downloadPdfBtn, [onclick*="print"] {
                display: none !important;
            }
            .btn-apex, .btn {
                display: none !important;
            }
            h1, h2, h3, h4, h5, h6, p, span, td, th {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
<?php include_once __DIR__ . '/includes\cookie_notif.php'; ?>

    <nav class="main-nav navbar navbar-expand-lg">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="brand me-4">
                <img src="assets/images/ApeX Logo.png" alt="ApeX Gear Logo" class="brand-logo-img">
                <div class="brand-text" style="color: white; margin-left:-10px">ApeX</div><div class="brand-text" style="color: #00c2ff; margin-left:-10px">Gear</div>
            </a>

            <div class="text-white small fw-bold">
                <i class="fas fa-lock text-apex-accent"></i> Secure Checkout
            </div>
        </div>
    </nav>

    <section class="inner-page">
        <div class="container">

            <?php if ($order_successful): ?>
                <div class="row justify-content-center mt-4">
                    <div class="col-md-8 text-center">
                        <div class="apex-card border-top border-5" style="border-top-color: #00d68f !important;">
                            <div class="mb-4">
                                <i class="fas fa-check-circle" style="font-size: 4rem; color: #00d68f;"></i>
                            </div>
                            <h1 class="fw-bold mb-3" style="font-family: 'Barlow Condensed', sans-serif; text-transform: uppercase;">Payment Successful</h1>
                            <p class="text-muted lead mb-4">Thank you for your order! Your high-performance gear is being prepped for shipment.</p>

                            <!-- Receipt Card -->
                            <div class="bg-light rounded-4 p-5 mb-5 text-start w-100 border" style="border: 2px solid var(--apex-border);">
                                <!-- Receipt Header -->
                                <div class="text-center mb-4 pb-3 border-bottom">
                                    <h3 class="fw-bold mb-2" style="font-family: 'Barlow Condensed', sans-serif; letter-spacing: 0.05em;">ONLINE RECEIPT</h3>
                                    <p class="text-muted small mb-0">Payment Completed Successfully</p>
                                    <p class="text-muted small mb-1">Order Number</p>
                                    <h4 class="fw-bold text-apex-blue font-monospace"><?php echo $receipt_number; ?></h4>
                                    <p class="text-muted small mt-2 mb-0"><?php echo $receipt_data['orderDate']; ?></p>
                                </div>

                                <!-- Shipping Details Section -->
                                <div class="mb-4 pb-3 border-bottom">
                                    <h6 class="fw-bold text-uppercase mb-3" style="font-family: 'Barlow Condensed', sans-serif; font-size: 0.9rem; letter-spacing: 0.05em; color: var(--apex-blue);">
                                        <i class="fas fa-map-marker-alt me-2"></i>Shipping Details
                                    </h6>
                                    <div class="ms-4">
                                        <p class="mb-2 text-dark fw-bold"><?php echo $receipt_data['firstName'] . ' ' . $receipt_data['lastName']; ?></p>
                                        <p class="mb-1 text-muted small"><?php echo $receipt_data['address']; ?></p>
                                        <p class="mb-1 text-muted small"><?php echo $receipt_data['city']; ?>, <?php echo $receipt_data['zipcode']; ?></p>
                                        <p class="mb-1 text-muted small"><i class="fas fa-phone me-2" style="color: var(--apex-blue);"></i><?php echo $receipt_data['phone']; ?></p>
                                        <p class="mb-0 text-muted small"><i class="fas fa-envelope me-2" style="color: var(--apex-blue);"></i><?php echo $receipt_data['email']; ?></p>
                                    </div>
                                </div>

                                <!-- Payment Method Section -->
                                <div class="mb-4 pb-3 border-bottom">
                                    <h6 class="fw-bold text-uppercase mb-3" style="font-family: 'Barlow Condensed', sans-serif; font-size: 0.9rem; letter-spacing: 0.05em; color: var(--apex-blue);">
                                        <i class="fas fa-credit-card me-2"></i>Payment Method
                                    </h6>
                                    <div class="ms-4">
                                        <p class="mb-0 text-dark fw-bold text-capitalize">
                                            <?php 
                                                $methods = array(
                                                    'card' => 'Credit/Debit Card',
                                                    'cod' => 'Cash on Delivery',
                                                    'gcash' => 'GCash',
                                                    'paypal' => 'PayPal',
                                                    'maya' => 'Maya'
                                                );
                                                echo isset($methods[$receipt_data['paymentMethod']]) ? $methods[$receipt_data['paymentMethod']] : ucfirst($receipt_data['paymentMethod']);
                                            ?>
                                        </p>
                                        <?php if ($receipt_data['paymentMethod'] === 'card' && isset($receipt_data['cardLast4'])): ?>
                                            <p class="mb-0 text-muted small mt-1">Card: <?php echo $receipt_data['cardLast4']; ?></p>
                                        <?php elseif ($receipt_data['paymentMethod'] === 'gcash' && isset($receipt_data['gcashMobile'])): ?>
                                            <p class="mb-0 text-muted small mt-1"><?php echo $receipt_data['gcashName']; ?></p>
                                            <p class="mb-0 text-muted small"><?php echo $receipt_data['gcashMobile']; ?></p>
                                        <?php elseif ($receipt_data['paymentMethod'] === 'paypal' && isset($receipt_data['paypalEmail'])): ?>
                                            <p class="mb-0 text-muted small mt-1"><?php echo $receipt_data['paypalEmail']; ?></p>
                                        <?php elseif ($receipt_data['paymentMethod'] === 'maya' && isset($receipt_data['mayaMobile'])): ?>
                                            <p class="mb-0 text-muted small mt-1"><?php echo $receipt_data['mayaName']; ?></p>
                                            <p class="mb-0 text-muted small"><?php echo $receipt_data['mayaMobile']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Order Items Section -->
                                <div class="mb-4 pb-3 border-bottom">
                                    <h6 class="fw-bold text-uppercase mb-3" style="font-family: 'Barlow Condensed', sans-serif; font-size: 0.9rem; letter-spacing: 0.05em; color: var(--apex-blue);">
                                        <i class="fas fa-shopping-bag me-2"></i>Order Items (<?php echo $receipt_data['itemCount']; ?> <?php echo $receipt_data['itemCount'] == 1 ? 'Item' : 'Items'; ?>)
                                    </h6>
                                    <div class="ms-4">
                                        <table class="w-100" style="font-size: 0.9rem;">
                                            <thead>
                                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                                    <th class="text-start pb-2 text-muted fw-bold">Item</th>
                                                    <th class="text-center pb-2 text-muted fw-bold">Qty</th>
                                                    <th class="text-end pb-2 text-muted fw-bold">Price</th>
                                                    <th class="text-end pb-2 text-muted fw-bold">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($receipt_data['items'] as $item): ?>
                                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                                        <td class="py-2 text-dark"><?php echo htmlspecialchars($item['name']); ?></td>
                                                        <td class="text-center py-2 text-dark"><?php echo $item['qty']; ?></td>
                                                        <td class="text-end py-2 text-dark">₱<?php echo number_format($item['price'], 2); ?></td>
                                                        <td class="text-end py-2 fw-bold text-dark">₱<?php echo number_format($item['price'] * $item['qty'], 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <div class="small text-muted mt-3">
                                            <p class="mb-0">Total Quantity: <strong class="text-dark"><?php echo $receipt_data['itemCount']; ?></strong> items</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Amount Section -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Subtotal</span>
                                        <span class="text-dark">₱<?php echo number_format($receipt_data['subtotal'], 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Tax (8%)</span>
                                        <span class="text-dark">₱<?php echo number_format($receipt_data['tax'], 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Shipping</span>
                                        <span class="text-success fw-bold">FREE</span>
                                    </div>
                                    <div style="border-top: 2px solid var(--apex-border); padding-top: 1rem;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold fs-5 text-dark">Total Amount Due</span>
                                            <span class="fw-bold fs-4 text-apex-blue">₱<?php echo number_format($receipt_data['grandTotal'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="alert alert-success alert-dismissible fade show mt-4 mb-0" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Order Status:</strong> Processing &bull; Paid
                                </div>
                            </div>

                            <!-- Download and Print Buttons -->
                            <div class="d-flex gap-3 justify-content-center mb-4">
                                <button type="button" class="btn" id="downloadPdfBtn" style="background: var(--apex-blue); color: white; border: none; padding: 10px 25px; border-radius: 50px; font-weight: 600; cursor: pointer;">
                                    <i class="fas fa-download me-2"></i>Download PDF
                                </button>
                                <button type="button" class="btn" onclick="window.print();" style="background: #6c757d; color: white; border: none; padding: 10px 25px; border-radius: 50px; font-weight: 600; cursor: pointer;">
                                    <i class="fas fa-print me-2"></i>Print Receipt
                                </button>
                            </div>

                            <a href="store.php" class="btn-apex" style="background: var(--apex-dark); color: white;">Return to Store</a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <h2 class="sec-title mb-4">Complete <span>Your Order</span></h2>

                <form method="POST" action="checkout.php" class="row g-5">

                    <div class="col-lg-8">
                        <div class="apex-card mb-4">
                            <h5 class="fw-bold mb-4 border-bottom pb-3 text-uppercase" style="font-family: 'Barlow Condensed', sans-serif; font-size: 1.4rem;">Shipping Details</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">First Name</label>
                                    <input type="text" class="form-control bg-light" name="first_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Last Name</label>
                                    <input type="text" class="form-control bg-light" name="last_name" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                                    <input type="email" class="form-control bg-light" name="email" required placeholder="receipts@example.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Phone Number</label>
                                    <input type="tel" class="form-control bg-light" name="phone" required placeholder="+63 (XXX) XXX-XXXX">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Street Address</label>
                                    <input type="text" class="form-control bg-light" name="address" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">City</label>
                                    <input type="text" class="form-control bg-light" name="city" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">ZIP / Postal Code</label>
                                    <input type="text" class="form-control bg-light" name="zipcode" required>
                                </div>
                            </div>
                        </div>

                        <div class="apex-card">
                            <h5 class="fw-bold mb-4 border-bottom pb-3 text-uppercase" style="font-family: 'Barlow Condensed', sans-serif; font-size: 1.4rem;">Payment Method</h5>

                            <div class="d-flex gap-2 mb-4" style="flex-wrap: nowrap; overflow-x: auto;">
                                <div class="payment-method border rounded px-3 py-2 bg-light fw-bold text-apex-blue" data-method="card" style="cursor:pointer; border-color: var(--apex-blue) !important; flex: 1; min-width: 140px; text-align: center; transition: all 0.3s ease; white-space: nowrap;">
                                    <i class="fas fa-credit-card me-2"></i> Credit/Debit
                                </div>

                                <div class="payment-method border rounded px-3 py-2 bg-light fw-bold text-apex-blue" data-method="cod" style="cursor:pointer; border-color: var(--apex-blue) !important; flex: 1; min-width: 120px; text-align: center; transition: all 0.3s ease; white-space: nowrap;">
                                    <i class="fas fa-money-bill-wave me-2"></i> COD
                                </div>

                                <div class="payment-method border rounded px-3 py-2 bg-light fw-bold text-apex-blue" data-method="gcash" style="cursor:pointer; border-color: var(--apex-blue) !important; flex: 1; min-width: 120px; text-align: center; transition: all 0.3s ease; white-space: nowrap;">
                                    <i class="fas fa-mobile-alt me-2"></i> GCash
                                </div>

                                <div class="payment-method border rounded px-3 py-2 bg-light fw-bold text-apex-blue" data-method="paypal" style="cursor:pointer; border-color: var(--apex-blue) !important; flex: 1; min-width: 120px; text-align: center; transition: all 0.3s ease; white-space: nowrap;">
                                    <i class="fab fa-paypal me-2"></i> PayPal
                                </div>

                                <div class="payment-method border rounded px-3 py-2 bg-light fw-bold text-apex-blue" data-method="maya" style="cursor:pointer; border-color: var(--apex-blue) !important; flex: 1; min-width: 100px; text-align: center; transition: all 0.3s ease; white-space: nowrap;">
                                    <i class="fas fa-wallet me-2"></i> Maya
                                </div>
                            </div>

                            <input type="hidden" id="selected_payment_method" name="payment_method" value="">

                            <!-- Credit/Debit Card Fields -->
                            <div id="card-fields" class="payment-fields row g-3" style="display: none;">
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Name on Card</label>
                                    <input type="text" class="form-control bg-light payment-required" name="card_name" placeholder="John Doe">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Card Number</label>
                                    <input type="text" class="form-control bg-light payment-required" name="card_number" placeholder="0000 0000 0000 0000" pattern="\d{16}" title="16 digit card number" maxlength="19">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Expiration Date</label>
                                    <input type="text" class="form-control bg-light payment-required" name="card_expiry" placeholder="MM/YY" maxlength="5">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">CVV</label>
                                    <input type="password" class="form-control bg-light payment-required" name="card_cvv" placeholder="123" maxlength="3">
                                </div>
                            </div>

                            <!-- Cash on Delivery Fields -->
                            <div id="cod-fields" class="payment-fields row g-3" style="display: none;">
                                <div class="col-12">
                                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Cash on Delivery</strong>
                                        <p class="mb-0 small mt-2">You will pay ₱<?php echo number_format($grand_total, 2); ?> when the package arrives at your address.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- G Cash Fields -->
                            <div id="gcash-fields" class="payment-fields row g-3" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Account Name</label>
                                    <input type="text" class="form-control bg-light payment-required" name="gcash_name" placeholder="Name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Mobile Number</label>
                                    <input type="tel" class="form-control bg-light payment-required" name="gcash_mobile" placeholder="09171234567">
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-info" role="alert">
                                        <small><i class="fas fa-check-circle me-2"></i>Payment reference will be sent to your G Cash account.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- PayPal Fields -->
                            <div id="paypal-fields" class="payment-fields row g-3" style="display: none;">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">PayPal Email</label>
                                    <input type="email" class="form-control bg-light payment-required" name="paypal_email" placeholder="your@paypal.com">
                                </div>
                                  <div class="col-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">PayPal Number</label>
                                    <input type="email" class="form-control bg-light payment-required" name="paypal_Number" placeholder="your@paypal.com">
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-info" role="alert">
                                        <small><i class="fas fa-external-link-alt me-2"></i>You will be redirected to PayPal to complete the payment.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Maya Fields -->
                            <div id="maya-fields" class="payment-fields row g-3" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Account Name</label>
                                    <input type="text" class="form-control bg-light payment-required" name="maya_name" placeholder="Name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Mobile Number</label>
                                    <input type="tel" class="form-control bg-light payment-required" name="maya_mobile" placeholder="09171234567">
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-info" role="alert">
                                        <small><i class="fas fa-check-circle me-2"></i>Payment instructions will be sent to your registered account.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="apex-card sticky-top" style="top: 100px;">
                            <h5 class="fw-bold mb-4 text-uppercase" style="font-family: 'Barlow Condensed', sans-serif; font-size: 1.4rem;">In Your Cart</h5>

                            <div class="mb-4 border-bottom pb-3">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                                        <span class="text-muted text-truncate me-2" style="max-width: 200px;">
                                            <span class="text-apex-blue fw-bold"><?php echo $item['qty']; ?>x</span> <?php echo htmlspecialchars($item['name']); ?>
                                        </span>
                                        <span class="fw-bold text-dark">₱<?php echo number_format($item['price'] * $item['qty'], 2); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-bold">₱<?php echo number_format($subtotal, 2); ?></span>
                            </div>

                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Tax</span>
                                <span class="fw-bold">₱<?php echo number_format($tax, 2); ?></span>
                            </div>

                            <div class="d-flex justify-content-between mb-4">
                                <span class="text-muted">Shipping</span>
                                <span class="fw-bold text-success">FREE</span>
                            </div>

                            <hr class="mb-4">

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="fw-bold fs-5 text-dark">Total</span>
                                <span class="fw-bold fs-4 text-apex-blue">₱<?php echo number_format($grand_total, 2); ?></span>
                            </div>

                            <input type="hidden" name="place_order" value="1">
                            <button type="submit" class="btn-apex w-100 py-3 text-center">Pay ₱<?php echo number_format($grand_total, 2); ?></button>

                            <div class="text-center mt-3">
                                <small class="text-muted">By confirming your order, you agree to our Terms of Service.</small>
                            </div>
                        </div>
                    </div>

                </form>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <div class="footer-bottom mt-0 border-0 pt-4 pb-4 bg-apex-dark text-center">
            <p>© 2026 ApeX Gear. All rights reserved. | High-Performance Tech</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/navbar.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
        // PDF Download functionality
        document.addEventListener('DOMContentLoaded', function() {
            const downloadBtn = document.getElementById('downloadPdfBtn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function() {
                    // Find the receipt container
                    const receiptElement = document.querySelector('.bg-light.rounded-4.p-5');
                    
                    if (receiptElement) {
                        // Clone the receipt for PDF export
                        const element = receiptElement.cloneNode(true);
                        
                        // Remove buttons div from clone
                        const buttonsToRemove = element.querySelectorAll('.d-flex.gap-3.justify-content-center');
                        buttonsToRemove.forEach(btn => btn.remove());
                        
                        const opt = {
                            margin: 10,
                            filename: 'APX_Gear_Receipt_<?php echo isset($receipt_number) ? htmlspecialchars($receipt_number) : 'receipt'; ?>.pdf',
                            image: { type: 'jpeg', quality: 0.98 },
                            html2canvas: { scale: 2, useCORS: true },
                            jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
                        };
                        
                        html2pdf().set(opt).from(element).save();
                    } else {
                        alert('Receipt not found. Please try again.');
                    }
                });
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethods = document.querySelectorAll('.payment-method');
            const selectedMethodInput = document.getElementById('selected_payment_method');
            const form = document.querySelector('form');

            // Initialize - set first payment method as default
            if (paymentMethods.length > 0) {
                paymentMethods[0].click();
            }

            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    const selectedMethod = this.getAttribute('data-method');
                    
                    // Update the hidden input
                    selectedMethodInput.value = selectedMethod;

                    // Remove active state from all methods
                    paymentMethods.forEach(m => {
                        m.style.background = 'var(--apex-light)';
                        m.style.borderColor = 'var(--apex-blue)';
                        m.style.color = 'var(--apex-blue)';
                        m.style.boxShadow = 'none';
                    });

                    // Set active state for clicked method
                    this.style.background = 'var(--apex-blue)';
                    this.style.borderColor = 'var(--apex-blue)';
                    this.style.color = 'white';
                    this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';

                    // Hide all payment field sections
                    const allFields = document.querySelectorAll('.payment-fields');
                    allFields.forEach(field => {
                        field.style.display = 'none';
                    });

                    // Show the selected payment method's fields
                    const fieldsToShow = document.getElementById(selectedMethod + '-fields');
                    if (fieldsToShow) {
                        fieldsToShow.style.display = '';
                        // Trigger animation
                        setTimeout(() => {
                            fieldsToShow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }, 100);
                    }
                });
            });

            // Form submission validation
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Validate payment method is selected
                    if (!selectedMethodInput.value) {
                        alert('Please select a payment method.');
                        return false;
                    }

                    // Check all required payment fields for the selected method
                    const selectedMethod = selectedMethodInput.value;
                    const paymentFields = document.querySelectorAll(`#${selectedMethod}-fields .payment-required`);
                    let allFilled = true;
                    let emptyFieldName = '';

                    paymentFields.forEach(field => {
                        if (!field.value.trim()) {
                            allFilled = false;
                            emptyFieldName = field.previousElementSibling?.textContent || field.name;
                            field.classList.add('is-invalid');
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });

                    if (!allFilled) {
                        alert(`Please fill in all required fields for ${selectedMethod.toUpperCase()}. Missing: ${emptyFieldName}`);
                        return false;
                    }

                    // If all validation passes, submit the form
                    form.submit();
                });

                // Add real-time validation for payment fields
                const paymentRequiredFields = document.querySelectorAll('.payment-required');
                paymentRequiredFields.forEach(field => {
                    field.addEventListener('input', function() {
                        if (this.value.trim()) {
                            this.classList.remove('is-invalid');
                        }
                    });
                });
            }
        });
    </script>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel" style="width: 400px; border-left: none; box-shadow: -4px 0 24px rgba(0,0,0,0.1);">

        <div class="offcanvas-header border-bottom pb-3 pt-4 px-4">
            <h6 class="offcanvas-title fw-bold text-dark d-flex align-items-center" id="cartOffcanvasLabel">
                <i class="fas fa-shopping-bag me-2" style="color: var(--apex-blue);"></i> Your Cart
            </h6>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body p-4 d-flex flex-column">
            <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
                <div class="m-auto text-center w-100">
                    <div class="mb-4">
                        <div style="width: 80px; height: 80px; background: var(--apex-grey); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <i class="fas fa-shopping-bag fa-2x text-muted opacity-50"></i>
                        </div>
                    </div>
                    <h6 class="fw-bold mb-2">Your cart is empty</h6>
                    <p class="text-muted small mb-4">Looks like you haven't added any gadgets yet.</p>
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2" style="background: var(--apex-blue); border-color: var(--apex-blue); font-weight: 600; font-size: .9rem;" data-bs-dismiss="offcanvas">Continue Shopping</button>
                </div>
            <?php else: ?>
                <div class="cart-items flex-grow-1 overflow-auto pe-2">
                    <?php
                    $subtotal = 0;
                    foreach ($cart_items as $id => $item):
                        $subtotal += ($item['price'] * $item['qty']);
                    ?>
                        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                            <div style="width: 60px; height: 60px; border-radius: 8px; background: var(--apex-grey); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink: 0;">
                                <?php
                                if (strpos($item['image'], '<svg') !== false) {
                                    echo $item['image'];
                                } else {
                                    echo '<img src="' . htmlspecialchars($item['image']) . '" style="max-width: 100%; object-fit: contain;">';
                                }
                                ?>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <h6 class="mb-1 fw-bold text-dark text-truncate" style="font-size: .9rem;"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Qty: <?php echo $item['qty']; ?></span>
                                    <span class="fw-bold text-apex-blue">₱<?php echo number_format($item['price'] * $item['qty'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-footer pt-3 mt-auto border-top">
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold text-muted text-uppercase small" style="letter-spacing: 0.05em;">Subtotal</span>
                        <span class="fw-bold text-dark fs-5">₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn-apex w-100 text-center py-3" style="border-radius: 50px;">Proceed to Checkout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>