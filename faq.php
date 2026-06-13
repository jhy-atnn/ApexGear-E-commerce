<?php $currentPage = 'faq'; ?>
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
    <link href="assets/css/support-pages.css" rel="stylesheet" />
</head>

<body>
    <?php include_once __DIR__ . '/includes\cookie_notif.php'; ?>

    <!-- INJECT NAVBAR -->
    <?php
    $currentPage = 'faq';
    include 'includes\navbar.php';
    ?>

    <!-- PAGE HERO -->
    <section class="support-hero">
        <div class="container" style="position:relative; z-index:1;">
            <div class="support-hero-eyebrow">Help Center</div>
            <h1 class="support-hero-title">Frequently Asked <em>Questions</em></h1>
            <p class="support-hero-sub">Everything you need to know about ordering, payments, shipping, and support at ApeX Gear.</p>
        </div>
    </section>

    <section class="support-section">
        <div class="container">
            <div class="text-center mb-2 fade-up">
                <h2 class="sec-title">Frequently Asked <span>Questions</span></h2>
            </div>
            <div class="sec-divider mx-auto fade-up"></div>
            <div class="faq-wrap" style="max-width:820px; margin:0 auto;">
                    <div class="faq-item fade-up">
                        <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="false">
                            <span class="faq-cat">Ordering</span>
                            <span class="faq-q-text">How do I place an order?</span>
                            <i class="fas fa-chevron-down faq-chevron"></i>
                        </button>
                        <div class="faq-a"><p>Browse our catalog, click <strong>View Details</strong> on any in-stock product, then add it to your cart and proceed to checkout. You can check out as a guest or log in for faster future orders.</p></div>
                    </div>
                    <div class="faq-item fade-up">
                        <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="false">
                            <span class="faq-cat">Ordering</span>
                            <span class="faq-q-text">Can I modify or cancel my order after placing it?</span>
                            <i class="fas fa-chevron-down faq-chevron"></i>
                        </button>
                        <div class="faq-a"><p>Yes — as long as the order has not yet been dispatched. Contact our support team immediately and we will update or cancel it for you.</p></div>
                    </div>
                    <div class="faq-item fade-up">
                        <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="false">
                            <span class="faq-cat">Payments</span>
                            <span class="faq-q-text">What payment methods do you accept?</span>
                            <i class="fas fa-chevron-down faq-chevron"></i>
                        </button>
                        <div class="faq-a"><p>We accept GCash, PayMaya, major credit/debit cards, and Cash on Delivery (COD). All online payments are secured with 256-bit SSL encryption.</p></div>
                    </div>
                    <div class="faq-item fade-up">
                        <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="false">
                            <span class="faq-cat">Payments</span>
                            <span class="faq-q-text">Is Cash on Delivery available nationwide?</span>
                            <i class="fas fa-chevron-down faq-chevron"></i>
                        </button>
                        <div class="faq-a"><p>COD is available in most serviceable areas across the Philippines. Availability is confirmed at checkout based on your delivery address.</p></div>
                    </div>
                    <div class="faq-item fade-up">
                        <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="false">
                            <span class="faq-cat">Shipping</span>
                            <span class="faq-q-text">How long does delivery take?</span>
                            <i class="fas fa-chevron-down faq-chevron"></i>
                        </button>
                        <div class="faq-a"><p>Most in-stock orders arrive within 1–3 business days via our trusted courier partners. Same-day dispatch applies to in-stock items ordered before cut-off.</p></div>
                    </div>
                    <div class="faq-item fade-up">
                        <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="false">
                            <span class="faq-cat">Products</span>
                            <span class="faq-q-text">Are your products authentic?</span>
                            <i class="fas fa-chevron-down faq-chevron"></i>
                        </button>
                        <div class="faq-a"><p>100%. Every item is genuine, sourced directly from authorized distributors and brand partners, and passes our internal quality check before listing.</p></div>
                    </div>
                    <div class="faq-item fade-up">
                        <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="false">
                            <span class="faq-cat">Products</span>
                            <span class="faq-q-text">Do you sell pre-owned units?</span>
                            <i class="fas fa-chevron-down faq-chevron"></i>
                        </button>
                        <div class="faq-a"><p>Yes. We offer certified pre-owned and refurbished units, clearly labeled as such, each inspected and vetted by our in-house tech team.</p></div>
                    </div>
                    <div class="faq-item fade-up">
                        <button class="faq-q" type="button" onclick="toggleFaq(this)" aria-expanded="false">
                            <span class="faq-cat">Support</span>
                            <span class="faq-q-text">How can I reach customer support?</span>
                            <i class="fas fa-chevron-down faq-chevron"></i>
                        </button>
                        <div class="faq-a"><p>Our tech-savvy team is available 24/7 via live chat, email, or phone. We are happy to help you pick the right gear or resolve any issue.</p></div>
                    </div>
            </div>
            <div class="support-cta-box fade-up">
                <div class="support-cta-text">
                    <h3>Still have questions?</h3>
                    <p>Our support team is here 24/7 to help with anything we missed.</p>
                </div>
                <a href="contact.php" class="btn-apex">Contact Support</a>
            </div>
        </div>
    </section>

    <script>
        function toggleFaq(btn) {
            const item = btn.closest('.faq-item');
            const isOpen = item.classList.contains('open');
            document.querySelectorAll('.faq-item.open').forEach(el => {
                el.classList.remove('open');
                el.querySelector('.faq-q').setAttribute('aria-expanded', 'false');
            });
            if (!isOpen) {
                item.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
            }
        }
    </script>

    <?php include 'includes\footer.php'; ?>
</body>

</html>
