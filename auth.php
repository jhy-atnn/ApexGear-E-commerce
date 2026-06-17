<?php
session_start();
require_once __DIR__ . '\database\db_connect.php';
require_once __DIR__ . '\includes\otp_mailer.php';

// If already logged in, redirect home
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// ── Handle AJAX requests ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {

    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    // Instantiate the OOP Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // ── REGISTER ──
    if ($action === 'register') {
        $username   = trim($_POST['username'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';
        $lastName   = trim($_POST['last_name'] ?? '');
        $firstName  = trim($_POST['first_name'] ?? '');
        $middleName = trim($_POST['middle_name'] ?? '');
        $gender     = trim($_POST['gender'] ?? '');

        // Validations
        if (!$username || !$email || !$password || !$lastName || !$firstName || !$gender) {
            echo json_encode(['success' => false, 'message' => 'All required fields are required.']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            exit;
        }
        if (!preg_match('/^[A-Za-z]+$/', $username) || !preg_match('/^[A-Za-z]+$/', $firstName) || !preg_match('/^[A-Za-z]+$/', $lastName) || ($middleName !== '' && !preg_match('/^[A-Za-z]+$/', $middleName))) {
            echo json_encode(['success' => false, 'message' => 'Please only use Letters for username and name fields.']);
            exit;
        }
        if (strlen($username) > 50 || strlen($email) > 100) {
            echo json_encode(['success' => false, 'message' => 'Username or email is too long.']);
            exit;
        }
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
            echo json_encode(['success' => false, 'message' => 'Password must have 8+ characters, an uppercase letter, and a special character.']);
            exit;
        }

        // Check existing username in Database
        $stmt = $conn->prepare("SELECT user_id FROM users_tbl WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username already taken.']);
            exit;
        }

        // Check existing email in Database
        $stmt = $conn->prepare("SELECT user_id FROM users_tbl WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already registered.']);
            exit;
        }

        $code = (string) random_int(100000, 999999);
        $token = bin2hex(random_bytes(32));
        $otpHash = password_hash($code, PASSWORD_DEFAULT);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $conn->query("DELETE FROM pending_registrations_tbl WHERE expires_at < NOW()");
        $stmt = $conn->prepare(
            "INSERT INTO pending_registrations_tbl
                (token, first_name, last_name, m_name, gender, username, email, password_hash, otp_hash, expires_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))
             ON DUPLICATE KEY UPDATE
                token = VALUES(token), first_name = VALUES(first_name), last_name = VALUES(last_name),
                m_name = VALUES(m_name), gender = VALUES(gender), username = VALUES(username),
                password_hash = VALUES(password_hash), otp_hash = VALUES(otp_hash),
                expires_at = VALUES(expires_at), attempts = 0, last_sent_at = NOW()"
        );
        $stmt->bind_param(
            "sssssssss",
            $token,
            $firstName,
            $lastName,
            $middleName,
            $gender,
            $username,
            $email,
            $passwordHash,
            $otpHash
        );

        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Could not start email verification. Please try again.']);
            exit;
        }

        try {
            sendRegistrationOtpEmail($email, $firstName, $code);
        } catch (Throwable $e) {
            $delete = $conn->prepare("DELETE FROM pending_registrations_tbl WHERE token = ?");
            $delete->bind_param("s", $token);
            $delete->execute();
            error_log('ApeX OTP email error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }

        $_SESSION['pending_registration_token'] = $token;
        echo json_encode(['success' => true, 'message' => 'Verification code sent to ' . $email]);
        exit;
    }

    // ── VERIFY CODE ──
    if ($action === 'verify') {
        $code = trim($_POST['code'] ?? '');

        if (!preg_match('/^\d{6}$/', $code) || empty($_SESSION['pending_registration_token'])) {
            echo json_encode(['success' => false, 'message' => 'No pending registration. Please register first.']);
            exit;
        }

        $token = $_SESSION['pending_registration_token'];
        $stmt = $conn->prepare("SELECT * FROM pending_registrations_tbl WHERE token = ? LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $pending = $stmt->get_result()->fetch_assoc();

        if (!$pending) {
            unset($_SESSION['pending_registration_token']);
            echo json_encode(['success' => false, 'message' => 'This verification request is no longer available. Please register again.']);
            exit;
        }
        if (strtotime($pending['expires_at']) < time()) {
            echo json_encode(['success' => false, 'message' => 'The code has expired. Request a new code below.', 'expired' => true]);
            exit;
        }
        if ((int) $pending['attempts'] >= 5) {
            echo json_encode(['success' => false, 'message' => 'Too many incorrect attempts. Request a new code.', 'expired' => true]);
            exit;
        }
        if (!password_verify($code, $pending['otp_hash'])) {
            $attempt = $conn->prepare("UPDATE pending_registrations_tbl SET attempts = attempts + 1 WHERE pending_id = ?");
            $attempt->bind_param("i", $pending['pending_id']);
            $attempt->execute();
            echo json_encode(['success' => false, 'message' => 'Incorrect code. Please try again.']);
            exit;
        }

        $conn->begin_transaction();
        $stmt = $conn->prepare("INSERT INTO users_tbl (first_name, last_name, m_name, gender, username, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssss",
            $pending['first_name'],
            $pending['last_name'],
            $pending['middle_name'],
            $pending['gender'],
            $pending['username'],
            $pending['email'],
            $pending['password_hash']
        );

        try {
            $stmt->execute();
            $user_id = $stmt->insert_id;

            // Initialize an empty profile in the dependent table
            $profStmt = $conn->prepare("INSERT INTO users_profiles_tbl (user_id) VALUES (?)");
            $profStmt->bind_param("i", $user_id);
            $profStmt->execute();

            $delete = $conn->prepare("DELETE FROM pending_registrations_tbl WHERE pending_id = ?");
            $delete->bind_param("i", $pending['pending_id']);
            $delete->execute();
            $conn->commit();
            unset($_SESSION['pending_registration_token']);

            // Log them in immediately
            $_SESSION['user'] = [
                'id' => $user_id,
                'username' => $pending['username'],
                'email' => $pending['email'],
                'role' => 'customer',
                'avatar' => strtoupper(substr($pending['username'], 0, 1)),
                'first_name' => $pending['first_name'],
                'last_name' => $pending['last_name']
            ];

            $_SESSION['apex_welcome_toast'] = [
                'message' => 'Welcome, ' . $pending['username'] . '!'
            ];

            setcookie('apex_logged_in', (string)time(), time() + 60, '/');
            echo json_encode(['success' => true, 'message' => 'Account created! Welcome to ApeX Gear.']);
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('ApeX registration error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Could not create the account. The username or email may already be in use.']);
        }
        exit;
    }

    if ($action === 'resend_otp') {
        if (empty($_SESSION['pending_registration_token'])) {
            echo json_encode(['success' => false, 'message' => 'No pending registration. Please register again.']);
            exit;
        }

        $token = $_SESSION['pending_registration_token'];
        $stmt = $conn->prepare("SELECT * FROM pending_registrations_tbl WHERE token = ? LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $pending = $stmt->get_result()->fetch_assoc();

        if (!$pending) {
            unset($_SESSION['pending_registration_token']);
            echo json_encode(['success' => false, 'message' => 'Please register again to request a new code.']);
            exit;
        }
        if (strtotime($pending['last_sent_at']) > time() - 60) {
            echo json_encode(['success' => false, 'message' => 'Please wait 60 seconds before requesting another code.']);
            exit;
        }

        $code = (string) random_int(100000, 999999);
        $otpHash = password_hash($code, PASSWORD_DEFAULT);

        try {
            sendRegistrationOtpEmail($pending['email'], $pending['first_name'], $code);
            $update = $conn->prepare(
                "UPDATE pending_registrations_tbl
                 SET otp_hash = ?, expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE), attempts = 0, last_sent_at = NOW()
                 WHERE pending_id = ?"
            );
            $update->bind_param("si", $otpHash, $pending['pending_id']);
            $update->execute();
            echo json_encode(['success' => true, 'message' => 'A new verification code was sent.']);
        } catch (Throwable $e) {
            error_log('ApeX OTP resend error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$password) {
            echo json_encode(['success' => false, 'message' => 'Both fields are required.']);
            exit;
        }
        // Updated query to fetch EVERYTHING from the profile table
        $query = "SELECT u.*, p.bio, p.street_address, p.city, p.zip_code, p.phone_number, p.image_path, p.birthday 
                  FROM users_tbl u 
                  LEFT JOIN users_profiles_tbl p ON u.user_id = p.user_id 
                  WHERE u.username = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Username not found.']);
            exit;
        }

        $found = $result->fetch_assoc();

        $passwordInfo = password_get_info($found['password']);
        $isLegacyPlaintext = $passwordInfo['algo'] === null || $passwordInfo['algo'] === 0;
        $passwordValid = password_verify($password, $found['password'])
            || ($isLegacyPlaintext && hash_equals($found['password'], $password));
        if (!$passwordValid) {
            echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
            exit;
        }

        // Updated session array to store the newly fetched data
        $_SESSION['user'] = [
            'id' => $found['user_id'],
            'username' => $found['username'],
            'email' => $found['email'],
            'role' => 'customer',
            'avatar' => strtoupper(substr($found['username'], 0, 1)),
            'first_name' => $found['first_name'],
            'last_name' => $found['last_name'],
            'gender' => $found['gender'],
            'bio' => $found['bio'] ?? null,
            'phone' => $found['phone_number'] ?? null,
            'street_address' => $found['street_address'] ?? null,
            'city' => $found['city'] ?? null,
            'postal_code' => $found['zip_code'] ?? null,
            'profile_picture' => $found['image_path'] ?? null,
            'birthday' => $found['birthday'] ?? null
        ];

        $_SESSION['apex_welcome_toast'] = [
            'message' => 'Welcome back, ' . $found['username'] . '!'
        ];

        // ── SYNC FAVORITES ──
        // Fetch user's saved favorites from the database into the session
        $_SESSION['favorites'] = [];
        $favStmt = $conn->prepare("
            SELECT f.product_id, p.name, p.price 
            FROM favorites_tbl f 
            JOIN products_tbl p ON f.product_id = p.product_id 
            WHERE f.user_id = ? AND p.archived_at IS NULL
        ");
        $favStmt->bind_param("i", $found['user_id']);
        $favStmt->execute();
        $favRes = $favStmt->get_result();

        while ($favRow = $favRes->fetch_assoc()) {
            $_SESSION['favorites'][$favRow['product_id']] = [
                'name' => $favRow['name'],
                'price' => $favRow['price']
            ];
        }

        $_SESSION['apex_welcome_toast'] = [
            'message' => 'Welcome back, ' . $fakeName . '!'
        ];

        setcookie('apex_logged_in', (string)time(), time() + 60, '/');
        echo json_encode(['success' => true, 'message' => 'Welcome back, ' . htmlspecialchars($found['username']) . '!']);
        exit;
    }



    // ── SOCIAL LOGIN (Simulated DB entry) ──
    if ($action === 'social') {
        $provider = $_POST['provider'] ?? 'Google';
        $fakeName = $provider === 'facebook' ? 'FBUser_' . rand(100, 999) : 'GUser_' . rand(100, 999);
        $fakeEmail = strtolower($fakeName) . '@' . strtolower($provider) . '.com';
        $fakePassword = 'social_login_dummy';

        // Insert mock social user
        $stmt = $conn->prepare("INSERT INTO users_tbl (first_name, last_name, username, email, password) VALUES ('Social', 'User', ?, ?, ?)");
        $stmt->bind_param("sss", $fakeName, $fakeEmail, $fakePassword);
        $stmt->execute();
        $user_id = $stmt->insert_id;

        $_SESSION['user'] = [
            'id' => $user_id,
            'username' => $fakeName,
            'email' => $fakeEmail,
            'role' => 'customer',
            'avatar' => strtoupper(substr($fakeName, 0, 1)),
            'provider' => $provider
        ];


        setcookie('apex_logged_in', (string)time(), time() + 60, '/');
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
        <!-- Left panel – branding + trusted brand carousel -->
        <div class="auth-panel-left d-none d-lg-flex">
            <div class="auth-brand-block">
                <div class="auth-logo-wrap" style="display:flex;align-items:center;gap:2px;">
                    <img src="assets/images/ApeX Logo.png" alt="ApeX Logo" style="height:70px;width:auto;">
                    <div class="auth-logo">ApeX<span>Gear</span></div>
                </div>
                <div class="auth-tagline">Trusted Tech Marketplace</div>

                <h1 class="auth-hero-title">Your Ultimate<br><span>Gear Finder</span></h1>
                <p class="auth-pitch">Login or create an account to unlock member deals, track orders, save favorites, and shop from the tech brands you trust.</p>

                <div class="auth-mini-badges">
                    <span><i class="fas fa-bolt"></i> Member Deals</span>
                    <span><i class="fas fa-shield-halved"></i> Secure Checkout</span>
                </div>
            </div>

            <div class="auth-logo-showcase" aria-label="Trusted brands carousel">
                <div class="auth-logo-row auth-logo-row-top">
                    <div class="auth-brand-tile"><img src="assets/images/lenovo.png" alt="Lenovo"></div>
                    <div class="auth-brand-tile"><img src="assets/images/asus.png" alt="Asus"></div>
                    <div class="auth-brand-tile"><img src="assets/images/dell.png" alt="Dell"></div>
                    <div class="auth-brand-tile"><img src="assets/images/hp.png" alt="HP"></div>
                    <div class="auth-brand-tile"><img src="assets/images/apple.png" alt="Apple"></div>
                    <div class="auth-brand-tile"><img src="assets/images/samsung.png" alt="Samsung"></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/lenovo.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/asus.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/dell.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/hp.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/apple.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/samsung.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/lenovo.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/asus.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/dell.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/hp.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/apple.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/samsung.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/lenovo.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/asus.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/dell.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/hp.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/apple.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/samsung.png" alt=""></div>
                </div>
                <div class="auth-logo-row auth-logo-row-bottom">
                    <div class="auth-brand-tile"><img src="assets/images/razer.png" alt="Razer"></div>
                    <div class="auth-brand-tile"><img src="assets/images/logitech.png" alt="Logitech"></div>
                    <div class="auth-brand-tile"><img src="assets/images/sony.png" alt="Sony"></div>
                    <div class="auth-brand-tile"><img src="assets/images/nvidia.png" alt="NVIDIA"></div>
                    <div class="auth-brand-tile"><img src="assets/images/corsair.png" alt="Corsair"></div>
                    <div class="auth-brand-tile"><img src="assets/images/intel.png" alt="Intel"></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/razer.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/logitech.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/sony.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/nvidia.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/corsair.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/intel.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/razer.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/logitech.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/sony.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/nvidia.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/corsair.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/intel.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/razer.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/logitech.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/sony.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/nvidia.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/corsair.png" alt=""></div>
                    <div class="auth-brand-tile" aria-hidden="true"><img src="assets/images/intel.png" alt=""></div>
                </div>
            </div>

            <div class="auth-glow g1"></div>
            <div class="auth-glow g2"></div>
        </div>

        <!-- Right panel – forms -->
        <div class="auth-panel-right">
            <div class="auth-form-wrap">

                <!-- ── RIGHT PANEL LOGO ── -->
                <div class="auth-right-logo" style="display:flex;align-items:center;gap:3px;">
                    <img src="assets/images/ApeX Logo.png" alt="ApeX Logo" style="height:55px;width:auto;margin-bottom:-3px;">
                    <div class="auth-right-logo-text">ApeX<span>Gear</span></div>
                </div>

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

                    <!-- Row 1: Username + Email -->
                    <div class="auth-field-row">
                        <div class="auth-field">
                            <label>Username</label>
                            <div class="auth-input-wrap">
                                <i class="fas fa-user"></i>
                                <input type="text" id="regUsername" placeholder="Username" autocomplete="username" required />
                            </div>
                        </div>
                        <div class="auth-field">
                            <label>Email</label>
                            <div class="auth-input-wrap">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="regEmail" placeholder="your@email.com" autocomplete="email" required />
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: First Name + Middle Name + Last Name -->
                    <div class="auth-field-row auth-field-row-3">
                        <div class="auth-field">
                            <label>First Name</label>
                            <div class="auth-input-wrap">
                                <input type="text" id="regFname" placeholder="First" autocomplete="given-name" required oninput="filterLettersOnly(this)" />
                            </div>
                        </div>
                        <div class="auth-field">
                            <label>Middle <span class="auth-opt">(opt.)</span></label>
                            <div class="auth-input-wrap">
                                <input type="text" id="regMname" placeholder="M.I." autocomplete="additional-name" oninput="filterLettersOnly(this)" />
                            </div>
                        </div>
                        <div class="auth-field">
                            <label>Last Name</label>
                            <div class="auth-input-wrap">
                                <input type="text" id="regLname" placeholder="Last" autocomplete="family-name" required oninput="filterLettersOnly(this)" />
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Gender custom dropdown -->
                    <div class="auth-field">
                        <label>Gender</label>
                        <div class="auth-custom-select" id="genderDropdown">
                            <button type="button" class="auth-custom-select__trigger" onclick="toggleGenderDropdown()" id="genderTrigger">
                                <span class="auth-custom-select__icon"><i class="fas fa-venus-mars"></i></span>
                                <span class="auth-custom-select__value" id="genderDisplay">Select gender</span>
                                <span class="auth-custom-select__arrow"><i class="fas fa-chevron-down"></i></span>
                            </button>
                            <ul class="auth-custom-select__menu" id="genderMenu" role="listbox">
                                <li class="auth-custom-select__option" data-value="Male" onclick="selectGenderOption(this)"><i class="fas fa-mars"></i> Male</li>
                                <li class="auth-custom-select__option" data-value="Female" onclick="selectGenderOption(this)"><i class="fas fa-venus"></i> Female</li>
                                <li class="auth-custom-select__option" data-value="Others" onclick="selectGenderOption(this)"><i class="fas fa-circle-dot"></i> Others</li>
                                <li class="auth-custom-select__option" data-value="Prefer not to say" onclick="selectGenderOption(this)"><i class="fas fa-user-secret"></i> Prefer not to say</li>
                            </ul>
                        </div>
                        <input type="hidden" id="regGender" />
                    </div>

                    <!-- Row 4: Password -->
                    <div class="auth-field">
                        <label>Password</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="regPassword" placeholder="Min. 8 chars, 1 uppercase, 1 special" autocomplete="new-password" required oninput="checkPwStrength(this.value)" />
                            <button class="auth-eye" onclick="togglePw('regPassword',this)" type="button" tabindex="-1">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                        <div class="pw-strength-bar">
                            <div id="pwBar"></div>
                        </div>
                        <div class="pw-rules" id="pwRules">
                            <span id="pr-len"><i class="fas fa-circle"></i> 8+ chars</span>
                            <span id="pr-upper"><i class="fas fa-circle"></i> Uppercase</span>
                            <span id="pr-special"><i class="fas fa-circle"></i> Special char</span>
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

                    <p class="otp-expiry" style="margin-top: 20px;">Code expires in <strong id="otpTimer">10:00</strong></p>
                    <p class="auth-switch mt-2" style="margin-top: 20px;">Didn't receive it? <a href="#" id="resendOtpLink" onclick="resendOtp(); return false;">Send a new code</a></p>
                    <p class="auth-switch mt-2" style="margin-top: 20px;">Wrong email? <a href="#" onclick="showTab('register'); return false;">Go back</a></p>
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

        function filterLettersOnly(el) {
            const original = el.value;
            const filtered = original.replace(/[^A-Za-z]/g, '');
            if (original !== filtered) {
                el.value = filtered;
                const alertId = el.id === 'loginUsername' ? 'loginAlert' : 'registerAlert';
                showAlert(alertId, 'Please only use Letters');
            } else {
                const alertId = el.id === 'loginUsername' ? 'loginAlert' : 'registerAlert';
                hideAlert(alertId);
            }
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
            const l = document.getElementById('regLname').value.trim();
            const f = document.getElementById('regFname').value.trim();
            const m = document.getElementById('regMname').value.trim();
            const g = document.getElementById('regGender').value;
            const e = document.getElementById('regEmail').value.trim();
            const p = document.getElementById('regPassword').value;
            const lettersOnly = /^[A-Za-z]+$/;
            if (!u || !l || !f || !g || !e || !p) {
                showAlert('registerAlert', 'All required fields are required.');
                return;
            }
            if (!lettersOnly.test(f) || !lettersOnly.test(l) || (m && !lettersOnly.test(m))) {
                showAlert('registerAlert', 'Please only use Letters');
                return;
            }
            hideAlert('registerAlert');
            setLoading('registerBtn', true);
            const r = await apexPost({
                action: 'register',
                username: u,
                email: e,
                password: p,
                last_name: l,
                first_name: f,
                middle_name: m,
                gender: g
            });
            setLoading('registerBtn', false);
            if (r.success) {
                document.getElementById('verifySubtext').textContent = 'We sent a 6-digit code to ' + e + '.';
                showTab('verify');
                initOtp();
                startOtpTimer();
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

        async function resendOtp() {
            const link = document.getElementById('resendOtpLink');
            link.classList.add('disabled');
            hideAlert('verifyAlert');
            const r = await apexPost({ action: 'resend_otp' });
            link.classList.remove('disabled');

            if (r.success) {
                showAlert('verifyAlert', r.message, 'success');
                initOtp();
                startOtpTimer();
            } else {
                showAlert('verifyAlert', r.message);
            }
        }

        let otpTimerInterval;
        function startOtpTimer() {
            clearInterval(otpTimerInterval);
            let remaining = 600;
            const timer = document.getElementById('otpTimer');

            const render = () => {
                const minutes = Math.floor(remaining / 60);
                const seconds = String(remaining % 60).padStart(2, '0');
                timer.textContent = minutes + ':' + seconds;
                if (remaining <= 0) {
                    clearInterval(otpTimerInterval);
                    timer.textContent = 'Expired';
                }
                remaining--;
            };

            render();
            otpTimerInterval = setInterval(render, 1000);
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
                box.oninput = () => {
                    box.value = box.value.replace(/\D/g, '').slice(0, 1);
                    if (box.value && i < boxes.length - 1) boxes[i + 1].focus();
                };
                box.onkeydown = e => {
                    if (e.key === 'Backspace' && !box.value && i > 0) boxes[i - 1].focus();
                };
                box.onpaste = e => {
                    const digits = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                    if (!digits) return;
                    e.preventDefault();
                    digits.split('').forEach((digit, index) => {
                        if (boxes[index]) boxes[index].value = digit;
                    });
                    boxes[Math.min(digits.length, boxes.length) - 1].focus();
                };
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

        // ── Custom gender dropdown ────────────────────────────────────────────────
        function toggleGenderDropdown() {
            const dd = document.getElementById('genderDropdown');
            dd.classList.toggle('open');
        }

        function selectGenderOption(li) {
            const value = li.dataset.value;
            document.getElementById('regGender').value = value;
            document.getElementById('genderDisplay').textContent = value;
            document.getElementById('genderDisplay').classList.add('selected');
            document.getElementById('genderDropdown').classList.remove('open');
            // mark active option
            document.querySelectorAll('.auth-custom-select__option').forEach(o => o.classList.remove('active'));
            li.classList.add('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', e => {
            const dd = document.getElementById('genderDropdown');
            if (dd && !dd.contains(e.target)) dd.classList.remove('open');
        });

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
