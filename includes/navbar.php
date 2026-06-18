<?php
require_once __DIR__ . '/auth_timeout.php';
apex_enforce_login_timeout();

if (!isset($inventoryManager)) {
    require_once __DIR__ . '/../classes/Inventory.php';
    /** @var Inventory $inventoryManager */
    $inventoryManager = new Inventory();
}
$welcomeToast = null;
if (!empty($_SESSION['apex_welcome_toast']) && is_array($_SESSION['apex_welcome_toast'])) {
    $welcomeToast = $_SESSION['apex_welcome_toast'];
    unset($_SESSION['apex_welcome_toast']);
}
?>

<!-- ── Topbar ── -->
<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-phone-alt me-1"></i>
            <a href="tel:+1234567890">+1 (234) 567-890</a>
            <span class="mx-2">|</span>
            <i class="fas fa-envelope me-1"></i>
            <a href="mailto:support@apexgear.com">support@apexgear.com</a>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span>Free shipping on orders over ₱5,000 </span>
        </div>
    </div>
</div>

<!-- ── Navbar ── -->
<nav id="mainNav" class="main-nav navbar navbar-expand-lg">
    <div class="container">
        <a href="index.php" class="brand me-4">
            <img src="assets/images/ApeX Logo.png" alt="ApeX Gear Logo" class="brand-logo-img">
            <div class="brand-text" style="color: white; margin-left:-10px">ApeX</div>
            <div class="brand-text" style="color: #00c2ff; margin-left:-10px">Gear</div>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
            data-bs-target="#mainMenu" style="color:#fff; font-size:1.3rem;">
            <i class="fas fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="mainMenu">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'home' ? 'active' : '' ?>" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'store' ? 'active' : '' ?>" href="store.php">Products</a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'deals' ? 'active' : '' ?>" href="deals.php">Deals</a></li>
                <li class="nav-item"><a class="nav-link <?= $currentPage === 'about' ? 'active' : '' ?>" href="about.php">About Us</a></li>
            </ul>
            <div class="nav-icons d-flex align-items-center">
                <div class="nav-search-wrap">
                    <div class="nav-search-field">
                        <button class="nav-search-toggle" type="button" aria-label="Search products" aria-expanded="false">
                            <i class="fas fa-search"></i>
                        </button>
                        <input type="search" class="nav-product-search" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" placeholder="Search products..." autocomplete="off">
                    </div>
                    <div class="nav-search-panel" aria-label="Search products">
                        <!-- AJAX results will be injected here -->
                    </div>
                </div>

                <a href="#favoritesOffcanvas" data-bs-toggle="offcanvas" role="button" aria-controls="favoritesOffcanvas" style="position:relative; cursor:pointer; color: rgba(255, 255, 255, .75);">
                    <i class="fas fa-heart"></i>
                    <span class="cart-badge" style="background: #ff3b5c; color: white;"><?php echo isset($_SESSION['favorites']) ? count($_SESSION['favorites']) : 0; ?></span>
                </a>

                <a href="#cartOffcanvas" data-bs-toggle="offcanvas" role="button" aria-controls="cartOffcanvas" style="position:relative; cursor:pointer;">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-badge">
                        <?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0; ?>
                    </span>
                </a>

                <?php if (isset($_SESSION['user'])): ?>
                    <a href="javascript:void(0)" class="order-status-link ms-3" aria-label="Order Status" style="position:relative; cursor:pointer; color: rgba(255, 255, 255, .75);" title="Order Status">
                        <i class="fas fa-truck"></i>
                    </a>
                    <div class="profile-btn-wrap ms-3">
                        <button class="profile-btn" id="profileToggle" onclick="toggleProfilePanel(event)">
                            <span class="profile-avatar"><?php echo !empty($_SESSION['user']['profile_picture']) ? '<img src="' . htmlspecialchars($_SESSION['user']['profile_picture']) . '" alt="Profile" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">' : htmlspecialchars($_SESSION['user']['avatar'] ?? 'A'); ?></span>
                            <span class="profile-name d-none d-lg-inline"><?php echo htmlspecialchars($_SESSION['user']['username'] ?? 'User'); ?></span>
                            <i class="fas fa-chevron-down ms-1" style="font-size:.65rem; opacity:.6;"></i>
                        </button>
                    </div>
                <?php else: ?>
                    <a href="auth.php" class="btn-apex ms-3" style="padding:8px 20px; font-size:.82rem;">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if ($welcomeToast): ?>
    <div class="apex-floating-toast" id="apexWelcomeToast" role="status" aria-live="polite">
        <div class="apex-floating-toast__icon"><i class="fas fa-bolt"></i></div>
        <div class="apex-floating-toast__body">
            <strong><?php echo htmlspecialchars($welcomeToast['message'] ?? 'Welcome to ApeX Gear!'); ?></strong>
            <span>Good to see you on ApeX Gear.</span>
        </div>
        <button type="button" class="apex-floating-toast__close" aria-label="Close notification" onclick="dismissApexWelcomeToast()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <script>
        function dismissApexWelcomeToast() {
            const toast = document.getElementById('apexWelcomeToast');
            if (!toast) return;
            toast.classList.add('is-hiding');
            setTimeout(() => toast.remove(), 280);
        }

        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(dismissApexWelcomeToast, 5000);
        });
    </script>
<?php endif; ?>

<!-- Modals for all visitors -->
<?php include_once __DIR__ . '/cart_modal.php'; ?>
<?php include_once __DIR__ . '/favorites_modal.php'; ?>

<!-- Modals EXCLUSIVELY for logged-in users -->
<?php if (isset($_SESSION['user'])): ?>
    <?php include_once __DIR__ . '/order_status.php'; ?>
    <?php include_once __DIR__ . '/user_profile_modal.php'; ?>
<?php endif; ?>
