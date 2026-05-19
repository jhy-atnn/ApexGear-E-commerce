<?php
session_start();

class AboutBrand
{
    protected $brandName = 'ApeX Gear';

    public function getBrand()
    {
        return $this->brandName;
    }
}

class AboutTeam extends AboutBrand
{
    public function getMessage()
    {
        return 'We build technology with heart.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>About Us — ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/auth-styles-append.css" rel="stylesheet" />
</head>
<body>
    <?php include_once __DIR__ . '/cookie_notif.php'; ?>

    <!-- INJECT NAVBAR -->
    <?php 
    $currentPage = 'about';
    include 'includes/navbar.php'; 
    ?>

    <section class="about-hero">
        <div class="container" style="position:relative; z-index:1;">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="about-hero-eyebrow">Who We Are</div>
                    <h1 class="about-hero-title">
                        Built by Gamers,<br>
                        <em>Driven by</em><br>
                        Passion.
                    </h1>
                    <p class="about-hero-sub">ApeX Gear is the Philippines' growing destination for premium tech — laptops, desktops, phones, and peripherals. We started as a small stall and we're scaling up to serve every Filipino tech enthusiast.</p>
                    <div class="hero-stat-wrap">
                        <div class="hero-stat">
                            <div class="hero-stat-num">3K+</div>
                            <div class="hero-stat-label">Happy Customers</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-num">500+</div>
                            <div class="hero-stat-label">Products Listed</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-num">4</div>
                            <div class="hero-stat-label">Years Growing</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-num">99%</div>
                            <div class="hero-stat-label">Satisfaction Rate</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-hero-img">
                        <img src="assets/images/valojet.png" alt="Tech store setup with gaming peripherals" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="story-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 fade-up">
                    <div class="story-img-wrap">
                        <img src="https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=800&q=80" alt="ApeX Gear store interior" />
                        <div class="story-img-badge">
                            <div class="num">2022</div>
                            <div class="lbl">Founded</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 fade-up">
                    <div class="sec-eyebrow">Our Story</div>
                    <h2 class="sec-title mb-1">From a Small <span>Stall</span><br>to Something Bigger</h2>
                    <div class="sec-divider left"></div>
                    <div class="story-text">
                        <p>ApeX Gear started in 2022 from a humble bazaar stall in Dasmariñas, Cavite — just a few laptops, some second-hand peripherals, and a dream to give Filipino gamers and students access to quality tech at fair prices.</p>
                        <p>What we discovered was a massive gap: most Filipinos couldn't find <strong>trusted, curated</strong> tech equipment in one place. So we built it. Starting with pre-loved units and a handful of brand-new items, we slowly earned the trust of our community.</p>
                        <p>Today, ApeX Gear is a <strong>growing tech equipment store in the Philippines</strong>, with an expanding online catalog covering laptops, desktops, cellphones, headphones, GPUs, CPUs, and accessories — all hand-picked by our in-house tech team.</p>
                        <p>We're not just sellers. We're enthusiasts who test, review, and <strong>believe in every product we carry</strong>. That's the ApeX promise.</p>
                    </div>
                    <div class="d-flex gap-3 flex-wrap mt-3">
                        <a href="store.php" class="btn-apex">Shop Our Catalog</a>
                        <a href="#team" class="btn-apex-outline">Meet the Team</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mv-section">
        <div class="container" style="position:relative; z-index:1;">
            <div class="text-center mb-2 fade-up">
                <div class="sec-eyebrow">What Drives Us</div>
                <h2 class="sec-title on-dark mb-1">Mission &amp; <span>Vision</span></h2>
            </div>
            <div class="sec-divider fade-up"></div>
            <div class="row g-4">
                <div class="col-md-6 fade-up">
                    <div class="mv-card">
                        <div class="mv-icon"><i class="fas fa-bullseye"></i></div>
                        <div class="mv-card-title">Our Mission</div>
                        <p>To make premium tech accessible to every Filipino — students, gamers, creators, and professionals alike. We bridge the gap between world-class equipment and local affordability, offering transparent pricing, honest reviews, and reliable after-sales support.</p>
                    </div>
                </div>
                <div class="col-md-6 fade-up">
                    <div class="mv-card">
                        <div class="mv-icon"><i class="fas fa-eye"></i></div>
                        <div class="mv-card-title">Our Vision</div>
                        <p>To become the Philippines' most trusted tech marketplace — a brand synonymous with quality, integrity, and community. We envision a future where no Filipino has to compromise on their gear just because of price, and where ApeX Gear is the first name they think of.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 fade-up">
                    <div class="mv-card">
                        <div class="mv-icon"><i class="fas fa-handshake"></i></div>
                        <div class="mv-card-title">Our Commitment</div>
                        <p>Every product we carry is tested and vetted. Every customer query is answered. We stand behind what we sell — with warranties, honest specs, and a team that knows tech inside-out.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 fade-up">
                    <div class="mv-card">
                        <div class="mv-icon"><i class="fas fa-users"></i></div>
                        <div class="mv-card-title">Our Community</div>
                        <p>ApeX is built on community. From our Discord group to our in-store tech workshops, we invest in the Filipino tech culture — sharing knowledge, fostering upgrades, and leveling everyone up together.</p>
                    </div>
                </div>
                <div class="col-md-12 col-lg-4 fade-up">
                    <div class="mv-card">
                        <div class="mv-icon"><i class="fas fa-leaf"></i></div>
                        <div class="mv-card-title">Sustainability</div>
                        <p>We promote responsible tech consumption by offering certified pre-owned products and refurbished units — keeping quality gear in circulation and reducing e-waste one device at a time.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="values-section">
        <div class="container">
            <div class="text-center mb-2 fade-up">
                <div class="sec-eyebrow">What We Stand For</div>
                <h2 class="sec-title mb-1">Our Core <span>Values</span></h2>
            </div>
            <div class="sec-divider fade-up"></div>
            <div class="row g-3">
                <div class="col-sm-6 col-lg-4 fade-up">
                    <div class="value-item">
                        <div class="value-icon"><i class="fas fa-shield-alt"></i></div>
                        <div>
                            <div class="value-title">Integrity</div>
                            <p class="value-desc">We never inflate specs. What you see is what you get — accurate descriptions, real photos, and honest pricing. Always.</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4 fade-up">
                    <div class="value-item">
                        <div class="value-icon"><i class="fas fa-bolt"></i></div>
                        <div>
                            <div class="value-title">Performance</div>
                            <p class="value-desc">We only stock gear that performs. Every product goes through our internal quality check before it hits the shelf.</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4 fade-up">
                    <div class="value-item">
                        <div class="value-icon"><i class="fas fa-heart"></i></div>
                        <div>
                            <div class="value-title">Passion</div>
                            <p class="value-desc">This isn't just a business. We're gamers, builders, and tech lovers — we live and breathe the products we sell.</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4 fade-up">
                    <div class="value-item">
                        <div class="value-icon"><i class="fas fa-tags"></i></div>
                        <div>
                            <div class="value-title">Affordability</div>
                            <p class="value-desc">Quality tech shouldn't be exclusive. We work hard to offer the best possible prices without sacrificing standards.</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4 fade-up">
                    <div class="value-item">
                        <div class="value-icon"><i class="fas fa-headset"></i></div>
                        <div>
                            <div class="value-title">After-Sales Support</div>
                            <p class="value-desc">Our job doesn't end at checkout. We're here for questions, issues, and upgrades — long after your purchase.</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4 fade-up">
                    <div class="value-item">
                        <div class="value-icon"><i class="fas fa-rocket"></i></div>
                        <div>
                            <div class="value-title">Innovation</div>
                            <p class="value-desc">We stay ahead of the curve — tracking the latest releases, chips, and trends so you always have access to what's next.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════
         TEAM / OWNERS & DEVELOPERS
    ══════════════════════════════ -->
    <section class="team-section" id="team">
        <div class="container">
            <div class="text-center mb-2 fade-up">
                <div class="sec-eyebrow">The People Behind ApeX</div>
                <h2 class="sec-title mb-1">Owners &amp; <span>Developers</span></h2>
            </div>
            <div class="sec-divider fade-up"></div>

            <p class="team-subheading fade-up">— Web &amp; Systems Developers —</p>
            <div class="row g-4">
                <div class="col-sm-6 col-lg-4 fade-up">
                    <div class="team-card">
                        <div class="team-img-wrap">
                            <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=400&q=80" alt="Lead Dev" />
                            <div class="team-img-overlay"></div>
                            <span class="team-role-badge">Lead Dev</span>
                        </div>
                        <div class="team-body">
                            <div class="team-name">Jhody Atinon</div>
                            <div class="team-position">Lead Full-Stack Developer</div>
                            <p class="team-bio">Architected the entire ApeX platform — from the backend inventory system to the storefront you're browsing right now. PHP, JS, and everything in between.</p>
                            <div class="team-socials">
                                <a href="#"><i class="fab fa-github"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4 fade-up">
                    <div class="team-card">
                        <div class="team-img-wrap">
                            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&q=80" alt="UI Designer" />
                            <div class="team-img-overlay"></div>
                            <span class="team-role-badge">Designer</span>
                        </div>
                        <div class="team-body">
                            <div class="team-name">Justine Luige Malaiba</div>
                            <div class="team-position">UI/UX Designer &amp; Front-End Dev</div>
                            <p class="team-bio">Responsible for every pixel of the ApeX aesthetic. Carla merges design instinct with front-end code — ensuring the site is as beautiful as it is fast.</p>
                            <div class="team-socials">
                                <a href="#"><i class="fab fa-behance"></i></a>
                                <a href="#"><i class="fab fa-dribbble"></i></a>
                                <a href="#"><i class="fab fa-github"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4 fade-up">
                    <div class="team-card">
                        <div class="team-img-wrap">
                            <img src="https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?w=400&q=80" alt="Backend Dev" />
                            <div class="team-img-overlay"></div>
                            <span class="team-role-badge">Dev</span>
                        </div>
                        <div class="team-body">
                            <div class="team-name">Sebastian Luis Ebora</div>
                            <div class="team-position">Back-End Developer &amp; DB Admin</div>
                            <p class="team-bio">Maintains the database, API integrations, and server infrastructure behind ApeX. If the site is fast and the data is clean, thank Renz.</p>
                            <div class="team-socials">
                                <a href="#"><i class="fab fa-github"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════
         MILESTONES / TIMELINE
    ══════════════════════════════ -->
    <section class="timeline-section">
        <div class="container">
            <div class="row g-5 align-items-start">
                <div class="col-lg-4 fade-up">
                    <div class="sec-eyebrow">Our Journey</div>
                    <h2 class="sec-title mb-1">ApeX <span>Milestones</span></h2>
                    <div class="sec-divider left"></div>
                    <p style="color:var(--apex-muted); font-size:.9rem; line-height:1.8;">Every big brand starts somewhere. Here's a look at how ApeX Gear grew from a weekend bazaar into a full-blown Philippine tech store.</p>
                    <div class="mt-4">
                        <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=600&q=80" alt="ApeX team at work" class="timeline-img" />
                    </div>
                </div>
                <div class="col-lg-8 fade-up">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-year">2022 — Q1</div>
                            <div class="timeline-event">The Bazaar Begins</div>
                            <p class="timeline-desc">ApeX Gear launches as a weekend stall in Dasmariñas, Cavite. First products: 10 refurbished laptops and a handful of gaming mice.</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-year">2022 — Q3</div>
                            <div class="timeline-event">First 100 Customers</div>
                            <p class="timeline-desc">Word-of-mouth spreads. We hit our first 100 customers and expand our catalog to include cellphones and desktops.</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-year">2023 — Q1</div>
                            <div class="timeline-event">Official Online Store Launched</div>
                            <p class="timeline-desc">ApeXGear.com goes live. Our first e-commerce platform enables nationwide shipping and 24/7 browsing for customers across the Philippines.</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-year">2023 — Q4</div>
                            <div class="timeline-event">1,000+ Orders Milestone</div>
                            <p class="timeline-desc">We cross 1,000 total orders. New categories added: GPUs, CPUs, and premium audio peripherals from brands like Razer and Sony.</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-year">2024 — Q2</div>
                            <div class="timeline-event">Physical Storefront Opens</div>
                            <p class="timeline-desc">ApeX Gear opens its first physical store, bringing a hands-on tech experience to Dasmariñas shoppers — try before you buy.</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-year">2025 — Present</div>
                            <div class="timeline-event">Expanding Nationwide</div>
                            <p class="timeline-desc">With 3,000+ satisfied customers and 500+ products, ApeX Gear is scaling — new brand partnerships, faster shipping, and more. The best is yet to come.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════
         WHY CHOOSE US
    ══════════════════════════════ -->
    <section class="about-why-section">
        <div class="container" style="position:relative; z-index:1;">
            <div class="text-center mb-2 fade-up">
                <div class="sec-eyebrow">The ApeX Advantage</div>
                <h2 class="sec-title on-dark mb-1">Why Choose <span>ApeX Gear?</span></h2>
            </div>
            <div class="sec-divider fade-up"></div>
            <div class="row g-4">
                <div class="col-6 col-md-4 col-lg-2 fade-up">
                    <div class="about-why-card">
                        <div class="about-why-icon"><i class="fas fa-check-double"></i></div>
                        <div class="about-why-title">Verified Quality</div>
                        <p class="about-why-desc">Every item passes our internal QC before listing.</p>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 fade-up">
                    <div class="about-why-card">
                        <div class="about-why-icon"><i class="fas fa-shipping-fast"></i></div>
                        <div class="about-why-title">Fast Shipping</div>
                        <p class="about-why-desc">Nationwide delivery via trusted couriers. Same-day dispatch for in-stock items.</p>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 fade-up">
                    <div class="about-why-card">
                        <div class="about-why-icon"><i class="fas fa-undo-alt"></i></div>
                        <div class="about-why-title">Easy Returns</div>
                        <p class="about-why-desc">7-day return policy on all eligible products. No hassle, no questions.</p>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 fade-up">
                    <div class="about-why-card">
                        <div class="about-why-icon"><i class="fas fa-lock"></i></div>
                        <div class="about-why-title">Secure Payment</div>
                        <p class="about-why-desc">GCash, PayMaya, cards, COD — all secured with 256-bit SSL.</p>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 fade-up">
                    <div class="about-why-card">
                        <div class="about-why-icon"><i class="fas fa-tools"></i></div>
                        <div class="about-why-title">Tech Support</div>
                        <p class="about-why-desc">Our in-house tech team is always available for setup help and advice.</p>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 fade-up">
                    <div class="about-why-card">
                        <div class="about-why-icon"><i class="fas fa-percent"></i></div>
                        <div class="about-why-title">Best Prices</div>
                        <p class="about-why-desc">We price-match and offer exclusive deals for loyal ApeX customers.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════
         CTA BANNER
    ══════════════════════════════ -->
    <section class="about-cta-section">
        <div class="container" style="position:relative; z-index:1;">
            <div class="row align-items-center g-4">
                <div class="col-lg-7 fade-up">
                    <h2 class="about-cta-title">Ready to <span>Level Up</span><br>Your Setup?</h2>
                    <p class="about-cta-sub">Browse hundreds of curated tech products — laptops, desktops, phones, peripherals, and more. Delivered anywhere in the Philippines.</p>
                </div>
                <div class="col-lg-5 fade-up text-lg-end">
                    <div class="d-flex gap-3 flex-wrap justify-content-lg-end">
                        <a href="store.php" class="btn-apex" style="padding:14px 36px; font-size:1rem;">Shop Now</a>
                        <a href="#" class="btn-apex-outline" style="padding:13px 34px; font-size:1rem;">View Deals</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Newsletter ── -->
    <section class="newsletter-section">
        <div class="container" style="position:relative; z-index:1;">
            <div class="row align-items-center g-4">
                <div class="col-lg-6 fade-up">
                    <h2 class="newsletter-title">Join the <span>ApeX</span> Inner Circle</h2>
                    <p class="newsletter-sub">Get exclusive deals, early access to new arrivals, and tech tips delivered straight to your inbox.</p>
                </div>
                <div class="col-lg-6 fade-up">
                    <div class="newsletter-form">
                        <input type="email" placeholder="Enter your email address…" />
                        <button>Subscribe</button>
                    </div>
                    <p style="font-size:.75rem; color:rgba(255,255,255,.35); margin-top:8px;">No spam. Unsubscribe anytime. We respect your privacy.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- INJECT FOOTER, MODALS, AND SCRIPTS -->
    <?php include 'includes/footer.php'; ?>

</body>
</html>