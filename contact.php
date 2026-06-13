<?php $currentPage = 'contact'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Us | ApeX Gear</title>
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
    $currentPage = 'contact';
    include 'includes\navbar.php';
    ?>

    <!-- PAGE HERO -->
    <section class="support-hero">
        <div class="container" style="position:relative; z-index:1;">
            <div class="support-hero-eyebrow">Get in Touch</div>
            <h1 class="support-hero-title">Contact <em>Us</em></h1>
            <p class="support-hero-sub">Questions, feedback, or need help picking gear? Our team is here 24/7.</p>
        </div>
    </section>

    <section class="support-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5 fade-up">
                    <div class="contact-info-card">
                        <h3 class="contact-info-title">Get in Touch</h3>
                        <p class="contact-info-sub">Our tech-savvy team is available 24/7. Reach us through any of the channels below.</p>
                        <div class="contact-line"><i class="fas fa-location-dot"></i><div><strong>Store</strong><span>Dasmariñas, Cavite, Philippines</span></div></div>
                        <div class="contact-line"><i class="fas fa-phone"></i><div><strong>Phone</strong><span>+63 912 345 6789</span></div></div>
                        <div class="contact-line"><i class="fas fa-envelope"></i><div><strong>Email</strong><span>support@apexgear.ph</span></div></div>
                        <div class="contact-line"><i class="fas fa-clock"></i><div><strong>Hours</strong><span>Mon–Sat, 9:00 AM – 8:00 PM</span></div></div>
                        <div class="contact-socials">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-discord"></i></a>
                            <a href="#"><i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 fade-up">
                    <div class="contact-form-card">
                        <h3 class="contact-form-title">Send Us a Message</h3>
                        <form method="POST" action="actions/contact_action.php">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="support-label" for="c_name">Full Name</label>
                                    <input type="text" id="c_name" name="name" class="form-control" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="support-label" for="c_email">Email Address</label>
                                    <input type="email" id="c_email" name="email" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="support-label" for="c_subject">Subject</label>
                                    <input type="text" id="c_subject" name="subject" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="support-label" for="c_msg">Message</label>
                                    <textarea id="c_msg" name="message" class="form-control" rows="5" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn-apex"><i class="fas fa-paper-plane me-2"></i>Send Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes\footer.php'; ?>
</body>

</html>
