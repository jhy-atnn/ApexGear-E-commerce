<?php
session_start();

require_once 'database/db_connect.php';
global $conn;

class AuthD
{
    private $label = 'auth-d';

    private function format($text)
    {
        return '[AUTH] ' . $text;
    }

    public function getLabel()
    {
        return $this->label;
    }
}

// If already logged in, redirect home
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// ── Handle AJAX requests ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {

    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';

    // ── REGISTER ──
    if ($action === 'register') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validations
        if (!$username || !$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            exit;
        }
        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
            exit;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            echo json_encode(['success' => false, 'message' => 'Password must contain at least one uppercase letter.']);
            exit;
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            echo json_encode(['success' => false, 'message' => 'Password must contain at least one special character.']);
            exit;
        }

        // Check existing users in DB
        $stmt = $conn->prepare("SELECT user_id, username, email FROM users_tbl WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            if (strtolower($row['username']) === strtolower($username)) {
                echo json_encode(['success' => false, 'message' => 'Username already taken.']);
                exit;
            }
            if (strtolower($row['email']) === strtolower($email)) {
                echo json_encode(['success' => false, 'message' => 'Email already registered.']);
                exit;
            }
        }

        // Generate 6-digit verification code
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store pending registration
        $_SESSION['pending_register'] = [
            'username' => $username,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'code'     => $code,
            'expires'  => time() + 600, // 10 min
        ];

        // In a real app you'd email $code. We expose it for demo:
        echo json_encode([
            'success'  => true,
            'message'  => 'Verification code sent to ' . htmlspecialchars($email),
            'demo_code' => $code  // REMOVE IN PRODUCTION
        ]);
        exit;
    }

    // ── VERIFY CODE ──
    if ($action === 'verify') {
        $code = trim($_POST['code'] ?? '');

        if (!isset($_SESSION['pending_register'])) {
            echo json_encode(['success' => false, 'message' => 'No pending registration. Please register first.']);
            exit;
        }
        $pending = $_SESSION['pending_register'];
        if (time() > $pending['expires']) {
            unset($_SESSION['pending_register']);
            echo json_encode(['success' => false, 'message' => 'Code expired. Please register again.']);
            exit;
        }
        if ($code !== $pending['code']) {
            echo json_encode(['success' => false, 'message' => 'Incorrect code. Please try again.']);
            exit;
        }

        // Create account in DB
        $stmt = $conn->prepare("INSERT INTO users_tbl (username, email, password_hash, role) VALUES (?, ?, ?, 'customer')");
        $stmt->bind_param("sss", $pending['username'], $pending['email'], $pending['password']);
        $stmt->execute();
        $user_id = $stmt->insert_id;

        $newUser = [
            'id'       => $user_id,
            'username' => $pending['username'],
            'email'    => $pending['email'],
            'role'     => 'customer',
            'avatar'   => strtoupper(substr($pending['username'], 0, 1)),
            'joined'   => date('F Y'),
        ];
        
        unset($_SESSION['pending_register']);

        // Log them in immediately
        $_SESSION['user'] = $newUser;

        setcookie('apex_logged_in', time(), time() + 60, '/'); // cookie expires in 60 seconds
        echo json_encode(['success' => true, 'message' => 'Account created! Welcome to ApeX Gear.']);
        exit;
    }

    // ── LOGIN ──
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$password) {
            echo json_encode(['success' => false, 'message' => 'Both fields are required.']);
            exit;
        }

        $stmt = $conn->prepare("SELECT user_id, username, email, password_hash, role, first_name, last_name, profile_picture, bio, gender, birthday, phone FROM users_tbl WHERE LOWER(username) = LOWER(?)");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Username not found.']);
            exit;
        }

        $row = $res->fetch_assoc();

        if (!password_verify($password, $row['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
            exit;
        }

        $_SESSION['user'] = [
            'id' => $row['user_id'],
            'username' => $row['username'],
            'email' => $row['email'],
            'role' => $row['role'],
            'avatar' => strtoupper(substr($row['username'], 0, 1)),
            'joined' => date('F Y'),
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'profile_picture' => $row['profile_picture'],
            'bio' => $row['bio'],
            'gender' => $row['gender'],
            'birthday' => $row['birthday'],
            'phone' => $row['phone']
        ];
        
        setcookie('apex_logged_in', time(), time() + 60, '/'); // cookie expires in 60 seconds
        echo json_encode(['success' => true, 'message' => 'Welcome back, ' . htmlspecialchars($row['username']) . '!']);
        exit;
    }

    // ── SOCIAL LOGIN (simulated) ──
    if ($action === 'social') {
        $provider = $_POST['provider'] ?? 'Google';
        $fakeName = $provider === 'facebook' ? 'FBUser_' . rand(100, 999) : 'GUser_' . rand(100, 999);
        $fakeEmail = strtolower($fakeName) . '@' . strtolower($provider) . '.com';

        // Insert into DB if simulating real creation
        $password_hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users_tbl (username, email, password_hash, role) VALUES (?, ?, ?, 'customer')");
        $stmt->bind_param("sss", $fakeName, $fakeEmail, $password_hash);
        $stmt->execute();
        $user_id = $stmt->insert_id;

        $newUser = [
            'id'       => $user_id,
            'username' => $fakeName,
            'email'    => $fakeEmail,
            'role'     => 'customer',
            'avatar'   => strtoupper(substr($fakeName, 0, 1)),
            'joined'   => date('F Y'),
            'provider' => $provider,
            'first_name' => null,
            'last_name' => null,
            'bio' => null,
            'gender' => null,
            'birthday' => null,
            'phone' => null,
            'profile_picture' => null
        ];
        $_SESSION['user'] = $newUser;

        setcookie('apex_logged_in', time(), time() + 60, '/'); // cookie expires in 60 seconds
        echo json_encode(['success' => true, 'message' => 'Signed in with ' . ucfirst($provider) . '!']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In / Register — ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/auth-styles-append.css" rel="stylesheet" />
</head>

<body class="auth-body">

    <!-- Background grid -->
    <div class="auth-bg-grid"></div>

    <!-- Back to site -->
    <a href="index.php" class="auth-back-link">
        <i class="fas fa-arrow-left me-2"></i> Back to ApeX Gear
    </a>

    <div class="auth-wrapper">
        <!-- Left panel – branding -->
        <div class="auth-panel-left d-none d-lg-flex">
            <div class="auth-brand-block">
                <div class="auth-logo">ApeX<span>Gear</span></div>

                <p class="auth-pitch">Join thousands of gamers and tech enthusiasts who trust ApeX Gear for the best in laptops, desktops, phones, and peripherals.</p>
                <div class="auth-perks">
                    <div class="auth-perk"><i class="fas fa-bolt"></i><span>Exclusive member deals</span></div>
                    <div class="auth-perk"><i class="fas fa-heart"></i><span>Save your favorites</span></div>
                    <div class="auth-perk"><i class="fas fa-truck"></i><span>Fast delivery tracking</span></div>
                    <div class="auth-perk"><i class="fas fa-shield-alt"></i><span>Secure &amp; private</span></div>
                </div>
            </div>
            <div class="auth-glow g1"></div>
            <div class="auth-glow g2"></div>
        </div>

        <!-- Right panel – forms -->
        <div class="auth-panel-right">
            <div class="auth-form-wrap">

                <!-- ── TABS ── -->
                <div class="auth-tabs">
                    <button class="auth-tab active" id="tabLogin" onclick="showTab('login')">Sign In</button>
                    <button class="auth-tab" id="tabRegister" onclick="showTab('register')">Register</button>
                    <div class="auth-tab-slider" id="tabSlider"></div>
                </div>

                <!-- ═══════════════════════════════
                 LOGIN FORM
            ═══════════════════════════════ -->
                <div id="formLogin" class="auth-form-panel active">
                    <h2 class="auth-form-title">Welcome Back</h2>
                    <p class="auth-form-sub">Sign in to your ApeX Gear account.</p>

                    <div id="loginAlert" class="auth-alert d-none"></div>

                    <div class="auth-field">
                        <label>Username</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-user"></i>
                            <input type="text" id="loginUsername" placeholder="Enter your username" autocomplete="username" required />
                        </div>
                    </div>
                    <div class="auth-field">
                        <label>Password</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="loginPassword" placeholder="Enter your password" autocomplete="current-password" required />
                            <button class="auth-eye" onclick="togglePw('loginPassword',this)" type="button" tabindex="-1">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button class="auth-submit-btn" id="loginBtn" onclick="doLogin()">
                        <span class="btn-label">Sign In</span>
                        <span class="btn-spinner d-none"><i class="fas fa-circle-notch fa-spin"></i></span>
                    </button>

                    <p class="auth-switch">No account yet? <a href="#" onclick="showTab('register'); return false;">Register</a></p>
                </div>

                <!-- ═══════════════════════════════
                 REGISTER FORM
            ═══════════════════════════════ -->
                <div id="formRegister" class="auth-form-panel">
                    <h2 class="auth-form-title">Create Account</h2>
                    <p class="auth-form-sub">Sign up and start shopping with ApeX Gear.</p>

                    <div id="registerAlert" class="auth-alert d-none"></div>

                    <div class="auth-field">
                        <label>Username</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-user"></i>
                            <input type="text" id="regUsername" placeholder="Choose a username" autocomplete="username" required />
                        </div>
                    </div>
                    <div class="auth-field">
                        <label>Email Address</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="regEmail" placeholder="your@email.com" autocomplete="email" required />
                        </div>
                        <div class="auth-hint">We'll send a 6-digit verification code here.</div>
                    </div>
                    <div class="auth-field">
                        <label>Password</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="regPassword" placeholder="Min. 8 chars, 1 uppercase, 1 special" autocomplete="new-password" required oninput="checkPwStrength(this.value)" />
                            <button class="auth-eye" onclick="togglePw('regPassword',this)" type="button" tabindex="-1">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                        <!-- Password strength bar -->
                        <div class="pw-strength-bar">
                            <div id="pwBar"></div>
                        </div>
                        <div class="pw-rules" id="pwRules">
                            <span id="pr-len"><i class="fas fa-circle"></i> 8+ characters</span>
                            <span id="pr-upper"><i class="fas fa-circle"></i> Uppercase</span>
                            <span id="pr-special"><i class="fas fa-circle"></i> Special character</span>
                        </div>
                    </div>

                    <button class="auth-submit-btn" id="registerBtn" onclick="doRegister()">
                        <span class="btn-label">Create Account</span>
                        <span class="btn-spinner d-none"><i class="fas fa-circle-notch fa-spin"></i></span>
                    </button>

                    <p class="auth-switch">Already have an account? <a href="#" onclick="showTab('login'); return false;">Sign In</a></p>
                </div>

                <!-- ═══════════════════════════════
                 VERIFICATION FORM
            ═══════════════════════════════ -->
                <div id="formVerify" class="auth-form-panel">
                    <div class="verify-icon"><i class="fas fa-envelope-open-text"></i></div>
                    <h2 class="auth-form-title">Check Your Email</h2>
                    <p class="auth-form-sub" id="verifySubtext">We sent a 6-digit code to your email.</p>

                    <div id="verifyAlert" class="auth-alert d-none"></div>

                    <!-- Demo code display -->
                    <div id="demoCodeBox" class="demo-code-box d-none">
                        <i class="fas fa-info-circle me-2"></i> Demo code: <strong id="demoCode"></strong>
                    </div>

                    <div class="otp-wrap">
                        <input class="otp-box" type="text" maxlength="1" inputmode="numeric" />
                        <input class="otp-box" type="text" maxlength="1" inputmode="numeric" />
                        <input class="otp-box" type="text" maxlength="1" inputmode="numeric" />
                        <input class="otp-box" type="text" maxlength="1" inputmode="numeric" />
                        <input class="otp-box" type="text" maxlength="1" inputmode="numeric" />
                        <input class="otp-box" type="text" maxlength="1" inputmode="numeric" />
                    </div>

                    <button class="auth-submit-btn" id="verifyBtn" onclick="doVerify()">
                        <span class="btn-label">Verify &amp; Create Account</span>
                        <span class="btn-spinner d-none"><i class="fas fa-circle-notch fa-spin"></i></span>
                    </button>

                    <p class="auth-switch mt-2">Wrong email? <a href="#" onclick="showTab('register'); return false;">Go back</a></p>
                </div>

            </div>
        </div>
    </div>

    <script>
        // ── Tab switching ──────────────────────────────────────────────────────────────
        function showTab(tab) {
            document.querySelectorAll('.auth-form-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));

            if (tab === 'login') {
                document.getElementById('formLogin').classList.add('active');
                document.getElementById('tabLogin').classList.add('active');
                document.getElementById('tabSlider').style.transform = 'translateX(0)';
            } else if (tab === 'register') {
                document.getElementById('formRegister').classList.add('active');
                document.getElementById('tabRegister').classList.add('active');
                document.getElementById('tabSlider').style.transform = 'translateX(100%)';
            } else if (tab === 'verify') {
                document.getElementById('formVerify').classList.add('active');
                // hide tabs when on verify step
            }
        }

        // ── Alert helper ──────────────────────────────────────────────────────────────
        function showAlert(id, msg, type = 'error') {
            const el = document.getElementById(id);
            el.className = 'auth-alert ' + (type === 'success' ? 'auth-alert-success' : 'auth-alert-error');
            el.textContent = msg;
            el.classList.remove('d-none');
        }

        function hideAlert(id) {
            document.getElementById(id).classList.add('d-none');
        }

        // ── Loading state ─────────────────────────────────────────────────────────────
        function setLoading(btnId, on) {
            const btn = document.getElementById(btnId);
            btn.querySelector('.btn-label').classList.toggle('d-none', on);
            btn.querySelector('.btn-spinner').classList.toggle('d-none', !on);
            btn.disabled = on;
        }

        // ── AJAX helper ───────────────────────────────────────────────────────────────
        async function apexPost(data) {
            data.ajax = 1;
            const fd = new FormData();
            for (const k in data) fd.append(k, data[k]);
            const res = await fetch('auth.php', {
                method: 'POST',
                body: fd
            });
            return res.json();
        }

        // ── LOGIN ─────────────────────────────────────────────────────────────────────
        async function doLogin() {
            const u = document.getElementById('loginUsername').value.trim();
            const p = document.getElementById('loginPassword').value;
            if (!u || !p) {
                showAlert('loginAlert', 'Please fill in both fields.');
                return;
            }
            hideAlert('loginAlert');
            setLoading('loginBtn', true);
            const r = await apexPost({
                action: 'login',
                username: u,
                password: p
            });
            setLoading('loginBtn', false);
            if (r.success) {
                showAlert('loginAlert', r.message, 'success');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 900);
            } else {
                showAlert('loginAlert', r.message);
            }
        }

        // ── REGISTER ──────────────────────────────────────────────────────────────────
        async function doRegister() {
            const u = document.getElementById('regUsername').value.trim();
            const e = document.getElementById('regEmail').value.trim();
            const p = document.getElementById('regPassword').value;
            if (!u || !e || !p) {
                showAlert('registerAlert', 'All fields are required.');
                return;
            }
            hideAlert('registerAlert');
            setLoading('registerBtn', true);
            const r = await apexPost({
                action: 'register',
                username: u,
                email: e,
                password: p
            });
            setLoading('registerBtn', false);
            if (r.success) {
                // Show verify panel
                document.getElementById('verifySubtext').textContent = 'We sent a 6-digit code to ' + e + '.';
                // Demo: show the code
                if (r.demo_code) {
                    document.getElementById('demoCodeBox').classList.remove('d-none');
                    document.getElementById('demoCode').textContent = r.demo_code;
                }
                showTab('verify');
                initOtp();
            } else {
                showAlert('registerAlert', r.message);
            }
        }

        // ── VERIFY ────────────────────────────────────────────────────────────────────
        async function doVerify() {
            const boxes = document.querySelectorAll('.otp-box');
            const code = Array.from(boxes).map(b => b.value).join('');
            if (code.length < 6) {
                showAlert('verifyAlert', 'Please enter the full 6-digit code.');
                return;
            }
            hideAlert('verifyAlert');
            setLoading('verifyBtn', true);
            const r = await apexPost({
                action: 'verify',
                code
            });
            setLoading('verifyBtn', false);
            if (r.success) {
                showAlert('verifyAlert', r.message, 'success');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 900);
            } else {
                showAlert('verifyAlert', r.message);
            }
        }

        // ── SOCIAL ────────────────────────────────────────────────────────────────────
        async function doSocial(provider) {
            const r = await apexPost({
                action: 'social',
                provider
            });
            if (r.success) {
                window.location.href = 'index.php';
            }
        }

        // ── OTP boxes auto-advance ────────────────────────────────────────────────────
        function initOtp() {
            const boxes = document.querySelectorAll('.otp-box');
            boxes.forEach((box, i) => {
                box.value = '';
                box.addEventListener('input', () => {
                    box.value = box.value.replace(/\D/g, '').slice(0, 1);
                    if (box.value && i < boxes.length - 1) boxes[i + 1].focus();
                });
                box.addEventListener('keydown', e => {
                    if (e.key === 'Backspace' && !box.value && i > 0) boxes[i - 1].focus();
                });
            });
            boxes[0].focus();
        }

        // ── Password strength ─────────────────────────────────────────────────────────
        function checkPwStrength(val) {
            const len = val.length >= 8;
            const upper = /[A-Z]/.test(val);
            const special = /[^a-zA-Z0-9]/.test(val);
            const score = [len, upper, special].filter(Boolean).length;

            const bar = document.getElementById('pwBar');
            bar.style.width = (score / 3 * 100) + '%';
            bar.className = score === 1 ? 'weak' : score === 2 ? 'medium' : 'strong';

            toggle('pr-len', len, 'met');
            toggle('pr-upper', upper, 'met');
            toggle('pr-special', special, 'met');

            function toggle(id, ok, cls) {
                const el = document.getElementById(id);
                el.classList.toggle(cls, ok);
            }
        }

        // ── Toggle password visibility ────────────────────────────────────────────────
        function togglePw(inputId, btn) {
            const inp = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.className = 'far fa-eye-slash';
            } else {
                inp.type = 'password';
                icon.className = 'far fa-eye';
            }
        }

        // ── Enter key submit ──────────────────────────────────────────────────────────
        document.addEventListener('keydown', e => {
            if (e.key !== 'Enter') return;
            const login = document.getElementById('formLogin');
            const reg = document.getElementById('formRegister');
            const verify = document.getElementById('formVerify');
            if (login.classList.contains('active')) doLogin();
            else if (reg.classList.contains('active')) doRegister();
            else if (verify.classList.contains('active')) doVerify();
        });
    </script>
</body>

</html>