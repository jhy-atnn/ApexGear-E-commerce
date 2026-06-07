<!-- ── Footer ── -->
<footer>
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-3">
                <a href="index.php" class="footer-logo-brand text-decoration-none">
                    <img src="assets/images/ApeX Logo.png" alt="ApeX Gear Logo" class="footer-logo-img">
                    <div class="footer-brand-text">
                        <div class="footer-brand">ApeX<span style="margin-left:2px">Gear</span></div>
                    </div>
                </a>
                <p style="font-size:.85rem; line-height:1.7; color:rgba(255,255,255,.45); margin-top:-28px">Your one-stop shop for laptops, desktops, cellphones, and premium accessories. Quality gear. Unbeatable prices.</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="footer-heading">Useful Links</div>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="product.php">Products</a></li>
                    <li><a href="deals.php">Deals</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="footer-heading">Support</div>
                <ul>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Shipping Policy</a></li>
                    <li><a href="#">Returns</a></li>
                    <li><a href="#">Order Tracking</a></li>
                    <li><a href="#">Warranty</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="footer-heading">Contact Us</div>
                <ul>
                    <li style="color:rgba(255,255,255,.45); font-size:.85rem;"><i class="fas fa-map-marker-alt me-2" style="color:var(--apex-accent);"></i>123 Tech Ave, Silicon City, PH</li>
                    <li><a href="tel:+1234567890"><i class="fas fa-phone-alt me-2" style="color:var(--apex-accent);"></i>+1 (234) 567-890</a></li>
                    <li><a href="mailto:support@apexgear.com"><i class="fas fa-envelope me-2" style="color:var(--apex-accent);"></i>support@apexgear.com</a></li>
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
    <div class="footer-bottom">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <p>© 2026 ApeX Gear. All rights reserved.</p>
            <p style="margin:0;">Terms &nbsp;|&nbsp; Privacy &nbsp;|&nbsp; Cookies</p>
        </div>
    </div>
</footer>

<a href="#" id="btt"><i class="fas fa-arrow-up"></i></a>

<!-- JavaScript Files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/navbar.js"></script>
<script src="assets/js/main.js"></script>

<!-- ── Cart Offcanvas ── -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel" style="width: 400px; border-left: none; box-shadow: -4px 0 24px rgba(0,0,0,0.1);">
    <div class="offcanvas-header border-bottom pb-3 pt-4 px-4">
        <h6 class="offcanvas-title fw-bold text-dark d-flex align-items-center" id="cartOffcanvasLabel">
            <i class="fas fa-shopping-bag me-2" style="color: var(--apex-blue);"></i> Cart Summary
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
                foreach ($_SESSION['cart'] as $id => $item):
                    $subtotal += ($item['price'] * $item['qty']);
                ?>
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        <div style="width: 50px; height: 50px; border-radius: 6px; background: var(--apex-grey); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink: 0;">
                            <?php
                            if (strpos($item['image'], '<svg') !== false) {
                                echo $item['image'];
                            } else {
                                echo '<img src="' . htmlspecialchars($item['image']) . '" style="max-width: 100%; object-fit: contain;">';
                            }
                            ?>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: .85rem;"><?php echo htmlspecialchars($item['name']); ?></h6>
                            <div class="text-muted small mt-1">
                                <?php echo $item['qty']; ?> x ₱<?php echo number_format($item['price'], 2); ?>
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
                <div class="d-flex flex-column gap-2">
                    <a href="checkout.php" class="btn-apex w-100 text-center py-2" style="border-radius: 50px;">Secure Checkout</a>
                    <a href="cart.php" class="btn btn-light w-100 text-center py-2 fw-bold text-apex-blue" style="border-radius: 50px; border: 1px solid var(--apex-border);">View Full Cart</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Favorites Offcanvas ── -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="favoritesOffcanvas" aria-labelledby="favoritesOffcanvasLabel" style="width: 400px; border-left: none; box-shadow: -4px 0 24px rgba(0,0,0,0.1);">
    <div class="offcanvas-header border-bottom pb-3 pt-4 px-4">
        <h6 class="offcanvas-title fw-bold text-dark d-flex align-items-center" id="favoritesOffcanvasLabel">
            <i class="fas fa-heart me-2" style="color: #ff3b5c;"></i> Your Favorites
        </h6>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4 d-flex flex-column">
        <?php if (!isset($_SESSION['favorites']) || empty($_SESSION['favorites'])): ?>
            <div class="m-auto text-center w-100">
                <div class="mb-4">
                    <div style="width: 80px; height: 80px; background: var(--apex-grey); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <i class="far fa-heart fa-2x text-muted opacity-50"></i>
                    </div>
                </div>
                <h6 class="fw-bold mb-2">No favorites yet</h6>
                <p class="text-muted small mb-4">Tap the heart on any product to save it for later.</p>
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2" style="background: var(--apex-blue); border-color: var(--apex-blue); font-weight: 600; font-size: .9rem;" data-bs-dismiss="offcanvas">Explore Products</button>
            </div>
        <?php else: ?>
            <div class="cart-items flex-grow-1 overflow-auto pe-2">
                <?php foreach ($_SESSION['favorites'] as $id => $item): ?>
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        <div style="width: 50px; height: 50px; border-radius: 6px; background: var(--apex-grey); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink: 0;">
                            <?php
                            if (strpos($item['image'], '<svg') !== false) {
                                echo $item['image'];
                            } else {
                                echo '<img src="' . htmlspecialchars($item['image']) . '" style="max-width: 100%; object-fit: contain;">';
                            }
                            ?>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: .85rem;">
                                <a href="product.php?id=<?php echo $id; ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($item['name']); ?></a>
                            </h6>
                            <div class="fw-bold text-apex-blue small mt-1">
                                ₱<?php echo number_format($item['price'], 2); ?>
                            </div>
                        </div>
                        <form method="POST" action="actions/favorites_action.php" class="ms-auto m-0">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                            <button type="submit" class="btn btn-sm text-danger border-0 bg-transparent" title="Remove from favorites">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── User Profile Modal ── -->
<div id="userProfileModal" class="profile-modal-container">
    <div class="profile-modal-content">
        <div class="profile-modal-header">
            <button type="button" class="btn-close-modal"><i class="fas fa-arrow-left"></i></button>
            <h5 class="profile-modal-title">Edit Profile</h5>
        </div>
        <div class="profile-modal-body">
            <div id="profileAlert" class="alert d-none"></div>
            <form id="userProfileForm" enctype="multipart/form-data">

                <!-- Avatar -->
                <div class="profile-avatar-section">
                    <div class="profile-avatar-large position-relative">
                        <?php
                            $avatarUrl = isset($_SESSION['user']['profile_picture']) && !empty($_SESSION['user']['profile_picture'])
                                         ? $_SESSION['user']['profile_picture']
                                         : 'https://via.placeholder.com/400';
                        ?>
                        <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="User Avatar" id="userModalAvatar">
                        <label for="userProfilePicture" class="btn btn-sm btn-primary position-absolute" title="Change photo">
                            <i class="fas fa-pen"></i>
                        </label>
                        <input type="file" id="userProfilePicture" name="profile_picture" class="d-none" accept="image/*">
                    </div>
                    <div class="text-muted small">Upload a JPG, PNG, or WEBP profile picture.</div>
                </div>

                <!-- Personal Info -->
                <div class="profile-section-label"><i class="fas fa-user" style="color:rgba(0,194,255,.5);font-size:.7rem;"></i> Personal Info</div>
                <div class="row">
                    <div class="col-6 form-group">
                        <label for="userFirstName">First Name</label>
                        <input type="text" id="userFirstName" name="first_name" class="form-control" placeholder="First name" value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['first_name'] ?? '') : ''; ?>">
                    </div>
                    <div class="col-6 form-group">
                        <label for="userLastName">Last Name</label>
                        <input type="text" id="userLastName" name="last_name" class="form-control" placeholder="Last name" value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['last_name'] ?? '') : ''; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="userBio">Bio</label>
                    <textarea id="userBio" name="bio" class="form-control" rows="3" placeholder="Tell us a little about yourself…"><?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['bio'] ?? '') : ''; ?></textarea>
                </div>

                <!-- Contact & Details -->
                <div class="profile-section-label"><i class="fas fa-id-card" style="color:rgba(0,194,255,.5);font-size:.7rem;"></i> Contact &amp; Details</div>
                <div class="form-group">
                    <label for="userEmail">Email</label>
                    <input type="email" id="userEmail" name="email" class="form-control" value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['email'] ?? '') : ''; ?>" placeholder="your@email.com">
                </div>
                <div class="form-group">
                    <label for="userContact">Contact Number</label>
                    <input
                        type="text"
                        id="userContact"
                        name="phone"
                        class="form-control"
                        inputmode="numeric"
                        maxlength="11"
                        placeholder="11-digit number"
                        value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['phone'] ?? '') : ''; ?>"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);">
                </div>
                <div class="form-group">
                    <label for="userGender">Gender</label>
                    <?php $uGender = isset($_SESSION['user']) ? $_SESSION['user']['gender'] : ''; ?>
                    <select id="userGender" name="gender" class="form-control">
                        <option value="" <?php echo $uGender == '' ? 'selected' : ''; ?>>Select</option>
                        <option value="Male" <?php echo $uGender == 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $uGender == 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo $uGender == 'Other' ? 'selected' : ''; ?>>Other</option>
                        <option value="Prefer not to say" <?php echo $uGender == 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="userBirthdate">Birthdate</label>
                    <div class="input-group">
                        <input type="date" id="userBirthdate" name="birthday" class="form-control datepicker-input" value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['birthday'] ?? '') : ''; ?>" />
                    </div>
                </div>

            </form>
        </div>
        <div class="profile-modal-footer">
            <button type="button" class="btn btn-secondary btn-close-modal-alt">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveProfile()">
                <i class="fas fa-check" style="margin-right:6px;font-size:.75rem;"></i>Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Profile Dropdown Logic -->
<script>
    <?php if (isset($_SESSION['user'])): ?>
        (function() {
            const panel = document.createElement('div');
            panel.id = 'profilePanel';
            panel.className = 'profile-panel';
            panel.innerHTML = `
                <div class="pp-header">
                    <div class="pp-avatar"><?php echo !empty($_SESSION['user']['profile_picture']) ? '<img src="' . htmlspecialchars($_SESSION['user']['profile_picture']) . '" alt="Profile" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">' : htmlspecialchars($_SESSION['user']['avatar']); ?></div>
                    <div>
                        <div class="pp-username"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></div>
                        <div class="pp-email"><?php echo htmlspecialchars($_SESSION['user']['email']); ?></div>
                    </div>
                </div>
                <div class="pp-divider"></div>
                <a href="javascript:void(0)" class="pp-link"><i class="fas fa-user"></i> My Profile</a>
                <a href="javascript:void(0)" class="pp-link"><i class="fas fa-box"></i> My Orders</a>
                <a href="#favoritesOffcanvas" data-bs-toggle="offcanvas" class="pp-link"><i class="fas fa-heart"></i> Favorites</a>
                <a href="#cartOffcanvas" data-bs-toggle="offcanvas" class="pp-link"><i class="fas fa-shopping-cart"></i> My Cart</a>
                <a href="javascript:void(0)" class="pp-link"><i class="fas fa-cog"></i> Settings</a>
                <div class="pp-divider"></div>
                <a href="logout.php" class="pp-link pp-logout"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
            `;
            document.body.appendChild(panel);
        })();

        function toggleProfilePanel(e) {
            e.stopPropagation();
            const panel = document.getElementById('profilePanel');
            const btn = document.getElementById('profileToggle');
            const rect = btn.getBoundingClientRect();
            const isOpen = panel.classList.contains('open');

            if (isOpen) {
                panel.classList.remove('open');
                return;
            }

            panel.style.top = (rect.bottom + window.scrollY + 12) + 'px';
            panel.style.left = 'auto';
            panel.style.right = (document.documentElement.clientWidth - rect.right) + 'px';
            panel.classList.add('open');
        }

        document.addEventListener('click', function(e) {
            const panel = document.getElementById('profilePanel');
            const btn = document.getElementById('profileToggle');
            if (panel && btn && !btn.contains(e.target) && !panel.contains(e.target)) {
                panel.classList.remove('open');
            }
        });

        window.addEventListener('scroll', function() {
            const panel = document.getElementById('profilePanel');
            if (panel) panel.classList.remove('open');
        });

        // User Profile Modal Logic
        const profileModal = document.getElementById('userProfileModal');
        const birthInput = document.getElementById('userBirthdate');
        if (birthInput) {
            birthInput.addEventListener('click', function() {
                if (typeof birthInput.showPicker === 'function') {
                    try {
                        birthInput.showPicker();
                    } catch (err) {}
                }
            });

            birthInput.addEventListener('keydown', function(e) {
                if (e.key !== 'Tab') e.preventDefault();
            });
        }

        const myProfileLink = document.querySelector('.pp-link[href="javascript:void(0)"]'); // Simplified selector
        const closeModalBtn = document.querySelector('.btn-close-modal');
        const closeModalAltBtn = document.querySelector('.btn-close-modal-alt');

        myProfileLink.addEventListener('click', (e) => {
            e.preventDefault();
            profileModal.classList.add('open');
            document.getElementById('profilePanel').classList.remove('open');
        });

        closeModalBtn.addEventListener('click', () => {
            profileModal.classList.remove('open');
        });

        if (closeModalAltBtn) {
            closeModalAltBtn.addEventListener('click', () => {
                profileModal.classList.remove('open');
            });
        }

        profileModal.addEventListener('click', (e) => {
            if (e.target === profileModal) {
                profileModal.classList.remove('open');
            }
        });

        document.getElementById('userProfilePicture').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const avatar = document.getElementById('userModalAvatar');
            if (file && avatar) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatar.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        async function saveProfile() {
            const form = document.getElementById('userProfileForm');
            const formData = new FormData(form);
            const alertBox = document.getElementById('profileAlert');

            try {
                const response = await fetch('actions/profile_action.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    alertBox.className = 'alert alert-success';
                    alertBox.textContent = result.message;
                    alertBox.classList.remove('d-none');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alertBox.className = 'alert alert-danger';
                    alertBox.textContent = result.message;
                    alertBox.classList.remove('d-none');
                }
            } catch (err) {
                alertBox.className = 'alert alert-danger';
                alertBox.textContent = 'An error occurred while saving.';
                alertBox.classList.remove('d-none');
            }
        }
    <?php endif; ?>
</script>
