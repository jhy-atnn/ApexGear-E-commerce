<?php
session_start();
// Pointing to the database folder from the main root
require_once __DIR__ . '/database/db_connect.php';

// Redirect into the admin folder if already logged in
if (isset($_SESSION['admin'])) {
    header('Location: admin/apex26admin.php');
    exit;
}

// ── Handle AJAX login ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        echo json_encode(['success' => false, 'message' => 'Both fields are required.']);
        exit;
    }

    $db   = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT * FROM admin_users_tbl WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
        exit;
    }

    $admin = $result->fetch_assoc();

    if ($password !== $admin['password']) {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
        exit;
    }

    $_SESSION['admin'] = [
        'id'       => $admin['admin_id'],
        'username' => $admin['username'],
        'avatar'   => strtoupper(substr($admin['username'], 0, 1)),
    ];

    echo json_encode(['success' => true, 'message' => 'Access granted.']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login — ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/auth-styles-append.css" rel="stylesheet" />
    <style>
        /* ── Admin-specific overrides ── */
        .admin-shield {
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, #0b2fa8, #0a2260);
            border: 1px solid rgba(0, 194, 255, .25);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #44d8ff;
            box-shadow: 0 8px 24px rgba(0, 194, 255, .15);
            flex-shrink: 0;
        }

        .admin-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: rgba(255, 59, 92, .12);
            border: 1px solid rgba(255, 59, 92, .28);
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #ff6b6b;
            margin-bottom: 26px;
        }

        .admin-badge-pill i {
            font-size: .65rem;
        }

        .auth-panel-left .admin-left-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            gap: 0;
        }

        .admin-divider {
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, #00c2ff, rgba(0, 194, 255, 0));
            border-radius: 2px;
            margin: 20px 0 24px;
        }

        .admin-feature {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
        }

        .admin-feature-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            border-radius: 9px;
            background: rgba(0, 194, 255, .1);
            border: 1px solid rgba(0, 194, 255, .18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            color: #44d8ff;
            margin-top: 2px;
        }

        .admin-feature-text strong {
            display: block;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: .95rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: rgba(255, 255, 255, .85);
            margin-bottom: 2px;
        }

        .admin-feature-text span {
            font-size: .8rem;
            color: rgba(255, 255, 255, .42);
            line-height: 1.5;
        }

        .admin-restricted-note {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: rgba(255, 59, 92, .07);
            border: 1px solid rgba(255, 59, 92, .18);
            border-radius: 8px;
            font-size: .75rem;
            color: rgba(255, 100, 100, .7);
            margin-top: auto;
        }

        .admin-restricted-note i {
            font-size: .7rem;
            flex-shrink: 0;
        }

        /* Single-panel centering — no tabs needed */
        .auth-form-wrap .admin-login-wrap {
            padding-top: 10px;
        }

        .auth-right-logo .admin-shield-sm {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, #0b2fa8, #0a2260);
            border: 1px solid rgba(0, 194, 255, .2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            color: #44d8ff;
        }
    </style>
</head>

<body class="auth-body">

    <!-- Back to site -->
    <a href="index.php" class="auth-back-link">
        <i class="fas fa-arrow-left me-2"></i> Back to ApeX Gear
    </a>

    <div class="auth-wrapper">

        <!-- ── Left panel – admin branding ── -->
        <div class="auth-panel-left d-none d-lg-flex">
            <div class="admin-left-content">

                <div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="admin-shield">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div>
                            <div class="auth-logo" style="font-size:1.7rem; margin-bottom:2px;">ApeX<span>Gear</span></div>
                            <div class="auth-tagline" style="margin-bottom:0;">Administration</div>
                        </div>
                    </div>

                    <div class="admin-divider"></div>

                    <h1 class="auth-hero-title" style="font-size: clamp(2.2rem,3.5vw,3.2rem); margin-bottom:12px;">
                        Admin<br><span>Control Panel</span>
                    </h1>
                    <p class="auth-pitch" style="margin-bottom:28px;">
                        Secure access to the ApeX Gear back-office. Manage inventory, orders, users, and platform settings from one place.
                    </p>
                </div>

                <div>
                    <div class="admin-feature">
                        <div class="admin-feature-icon"><i class="fas fa-boxes-stacked"></i></div>
                        <div class="admin-feature-text">
                            <strong>Inventory Management</strong>
                            <span>Add, edit, and remove products in real time.</span>
                        </div>
                    </div>
                    <div class="admin-feature">
                        <div class="admin-feature-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="admin-feature-text">
                            <strong>Sales & Analytics</strong>
                            <span>Track orders, revenue, and customer activity.</span>
                        </div>
                    </div>
                    <div class="admin-feature">
                        <div class="admin-feature-icon"><i class="fas fa-users-gear"></i></div>
                        <div class="admin-feature-text">
                            <strong>User Management</strong>
                            <span>Review accounts, roles, and access levels.</span>
                        </div>
                    </div>
                </div>

                <div class="admin-restricted-note" style="margin-top: 0px;">
                    <i class="fas fa-lock"></i>
                    Restricted area — authorized personnel only. Unauthorized access is prohibited.
                </div>
            </div>

            <div class="auth-glow g1"></div>
            <div class="auth-glow g2"></div>
        </div>

        <!-- ── Right panel – login form ── -->
        <div class="auth-panel-right">
            <div class="auth-form-wrap">

                <!-- Logo -->
                <div class="auth-right-logo">
                    <div class="admin-shield-sm"><i class="fas fa-shield-halved"></i></div>
                    <div class="auth-right-logo-text">ApeX<span>Gear</span></div>
                    <span class="auth-right-logo-badge" style="border-color: rgba(255,59,92,.3); color: #ff6b6b;">Admin</span>
                </div>

                <!-- Login form (no tabs — admin page is login-only) -->
                <div class="admin-login-wrap">
                    <div class="admin-badge-pill">
                        <i class="fas fa-lock"></i> Restricted Access
                    </div>

                    <h2 class="auth-form-title">Admin Sign-In</h2>
                    <p class="auth-form-sub">Enter your administrator credentials to continue.</p>

                    <div id="adminAlert" class="auth-alert d-none"></div>

                    <div class="auth-field">
                        <label>Username</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-user-shield"></i>
                            <input type="text" id="adminUsername" placeholder="Admin username" autocomplete="username" required />
                        </div>
                    </div>

                    <div class="auth-field">
                        <label>Password</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="adminPassword" placeholder="Admin password" autocomplete="current-password" required />
                            <button class="auth-eye" onclick="togglePw('adminPassword', this)" type="button" tabindex="-1">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button class="auth-submit-btn" id="adminLoginBtn" onclick="doAdminLogin()"
                        style="background: linear-gradient(135deg, #0e3580 0%, #0a2260 60%, #071840 100%);">
                        <span class="btn-label"><i class="fas fa-shield-halved me-2"></i>Sign In to Admin Panel</span>
                        <span class="btn-spinner d-none"><i class="fas fa-circle-notch fa-spin"></i></span>
                    </button>

                </div>

            </div>
        </div>
    </div>

    <script>
        // ── Alert helper ──────────────────────────────────────────────────────
        function showAlert(msg, type = 'error') {
            const el = document.getElementById('adminAlert');
            el.className = 'auth-alert ' + (type === 'success' ? 'auth-alert-success' : 'auth-alert-error');
            el.textContent = msg;
            el.classList.remove('d-none');
        }

        function hideAlert() {
            document.getElementById('adminAlert').classList.add('d-none');
        }

        // ── Loading state ─────────────────────────────────────────────────────
        function setLoading(on) {
            const btn = document.getElementById('adminLoginBtn');
            btn.querySelector('.btn-label').classList.toggle('d-none', on);
            btn.querySelector('.btn-spinner').classList.toggle('d-none', !on);
            btn.disabled = on;
        }

        // ── Login ─────────────────────────────────────────────────────────────
        async function doAdminLogin() {
                const u = document.getElementById('adminUsername').value.trim();
                const p = document.getElementById('adminPassword').value;

                if (!u || !p) {
                    showAlert('Both fields are required.');
                    return;
                }

                hideAlert();
                setLoading(true);

                const fd = new FormData();
                fd.append('ajax', '1');
                fd.append('username', u);
                fd.append('password', p);

                try {
                    const res = await fetch('admingear.php', {
                        method: 'POST',
                        body: fd
                    });
                    const data = await res.json();

                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => {
                            // Redirect INTO the admin folder
                            window.location.href = 'admin/apex26admin.php';
                        }, 800);
                    } else {
                        showAlert(data.message);
                    }
                } catch (err) {
                    showAlert('Connection error. Please try again.');
                    setLoading(false);
                }
            }

                // ── Toggle password visibility ────────────────────────────────────────
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

                // ── Enter key ─────────────────────────────────────────────────────────
                document.addEventListener('keydown', e => {
                    if (e.key === 'Enter') doAdminLogin();
                });
    </script>
</body>

</html>