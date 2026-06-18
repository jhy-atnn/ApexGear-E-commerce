<style>
.footer-bottom-links {
  display: flex;
  align-items: center;
  gap: 24px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.footer-bottom-links a {
  font-size: 12px;
  line-height: 1.5;
  color: var(--gray-500);
  text-decoration: none;
  transition: color 0.35s ease;
}

.footer-bottom-links a:hover {
  color: var(--apex-accent);
  text-decoration: none;
}

/* Responsive: Tablet */
@media (max-width: 768px) {
  .footer-bottom-links {
    gap: 16px;
    justify-content: center;
    margin-top: 12px;
  }

  .footer-bottom-links a {
    font-size: 11px;
  }

  .footer-bottom .container {
    flex-direction: column;
    text-align: center;
    gap: 8px;
  }
}

/* Responsive: Mobile */
@media (max-width: 480px) {
  .footer-bottom-links {
    gap: 12px;
    justify-content: center;
  }

  .footer-bottom-links a {
    font-size: 11px;
  }
}
</style>

<!-- ── Footer ── -->
<footer>
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-3">
                <a href="#" class="footer-logo-brand text-decoration-none" data-scroll-top>
                    <img src="assets/images/ApeX Logo.png" alt="ApeX Gear Logo" class="footer-logo-img">
                    <div class="footer-brand-text">
                        <div class="footer-brand">ApeX<span style="margin-left:2px">Gear</span></div>
                    </div>
                </a>
               <p style="font-size:.85rem; line-height:1.7; color:rgba(255,255,255,.45); margin-top:-28px">
    Your one-stop shop for laptops, desktops, cellphones, and premium accessories. Quality gear. Unbeatable prices.
</p>
<div class="footer-social">
    <a href="https://www.facebook.com/" target="_blank"><i class="fab fa-facebook-f"></i></a>
    <a href="https://x.com/" target="_blank"><i class="fab fa-twitter"></i></a>
    <a href="https://www.instagram.com/" target="_blank"><i class="fab fa-instagram"></i></a>
    <a href="https://www.tiktok.com/" target="_blank"><i class="fab fa-tiktok"></i></a>
</div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="footer-heading">Useful Links</div>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="product.php">Products</a></li>
                    <li><a href="deals.php">Deals</a></li>
                    <li><a href="#">News</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="footer-heading">Support</div>
                <ul>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="shipping.php">Shipping Policy</a></li>
                    <li><a href="returns.php">Returns</a></li>
                    <li><a href="order-tracking.php">Order Tracking</a></li>
                    <li><a href="warranty.php">Warranty</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="footer-heading">Contact Us</div>
                <ul>
                    <li style="color:rgba(255,255,255,.45); font-size:.85rem;"><i class="fas fa-map-marker-alt me-2" style="color:var(--apex-accent);"></i>Ayala Avenue, Makati City, PH</li>
                    <li><a href="tel:+1234567890"><i class="fas fa-phone-alt me-2" style="color:var(--apex-accent);"></i>+63 912 255 3546</a></li>
                    <li><a href="mailto:support@apexgear.com"><i class="fas fa-envelope me-2" style="color:var(--apex-accent);"></i>apex26gear@gmail.com</a></li>
                    <li style="color:rgba(255,255,255,.45); font-size:.85rem;"><i class="fas fa-clock me-2" style="color:var(--apex-accent);"></i>Mon–Sat: 9AM – 9PM</li>
                </ul>
                <div class="footer-heading mt-4">Accept Payment</div>
                <div class="payment-icons d-flex gap-2 flex-wrap mt-2">
                    <div class="pay-icon d-flex align-items-center justify-content-center px-2" title="Visa"><i class="fab fa-cc-visa fs-2"></i></div>
                    <div class="pay-icon d-flex align-items-center justify-content-center px-2" title="Mastercard"><i class="fab fa-cc-mastercard fs-2"></i></div>
                    <div class="pay-icon d-flex align-items-center justify-content-center px-2 gap-1" title="GCash"><i class="fas fa-mobile-screen"></i><span style="font-size: 0.75rem; font-weight: bold;">GCash</span></div>
                    <div class="pay-icon d-flex align-items-center justify-content-center px-2 gap-1" title="Maya"><i class="fas fa-wallet"></i><span style="font-size: 0.75rem; font-weight: bold;">Maya</span></div>
                    <div class="pay-icon d-flex align-items-center justify-content-center px-2 gap-1" title="Cash on Delivery"><i class="fas fa-hand-holding-usd"></i><span style="font-size: 0.75rem; font-weight: bold;">COD</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer bottom -->
    <div class="footer-bottom">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <p>© 2026 ApeX Gear. All rights reserved.</p>
          <nav class="footer-bottom-links" style="display:flex; gap:16px; flex-wrap:wrap;">
            <a href="privacy.php">Privacy Policy</a>
            <a href="terms.php">Terms and Conditions</a>
            <a href="cookies.php">Cookie Policy</a>
          </nav>
        </div>
    </div>
</footer>

<!-- JavaScript Files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/navbar.js"></script>
<script src="assets/js/main.js"></script>
