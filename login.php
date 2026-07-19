<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Login Page (RBAC)
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-25
 * Description : Role-Based Access Control login page.
 *               - Both Admin & User redirect to index.php
 * ============================================================
 */

session_start();

// If already logged in, redirect based on role
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/admin.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

// ── Database Connection ─────────────────────────────────────
require_once __DIR__ . '/config/db_connect.php';

// ── Handle Login & Register POST ────────────────────────────
$errorMessage    = '';
$registerError   = '';
$registerSuccess = '';
$activeTab       = 'login'; // 'login' or 'register'

// ── Read cart redirect flash message ────────────────────────
$loginRequiredMessage = '';
if (isset($_SESSION['login_required_message'])) {
    $loginRequiredMessage = $_SESSION['login_required_message'];
    unset($_SESSION['login_required_message']);
}

// ── Read register success flash ──────────────────────────────
if (isset($_SESSION['register_success'])) {
    $registerSuccess = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
    $activeTab = 'login';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ════════════════════════════════════════
    // HANDLE LOGIN
    // ════════════════════════════════════════
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $activeTab = 'login';
        $email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        if ($email === '' || $password === '') {
            $errorMessage = 'Mohon lengkapi semua field.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1');
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name']    = $user['name'];
                    $_SESSION['email']   = $user['email'];
                    $_SESSION['role']    = $user['role'];

                    if ($user['role'] === 'admin') {
                        header('Location: admin/admin.php');
                    } else {
                        header('Location: index.php');
                    }
                    exit;
                } else {
                    $errorMessage = 'Email atau password salah. Silakan coba lagi.';
                }
            } catch (PDOException $e) {
                error_log('Login Error: ' . $e->getMessage());
                $errorMessage = 'Terjadi kesalahan sistem. Silakan coba lagi nanti.';
            }
        }
    }

    // ════════════════════════════════════════
    // HANDLE REGISTER
    // ════════════════════════════════════════
    elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
        $activeTab   = 'register';
        $regName     = isset($_POST['reg_name'])     ? trim($_POST['reg_name'])     : '';
        $regEmail    = isset($_POST['reg_email'])    ? trim($_POST['reg_email'])    : '';
        $regPassword = isset($_POST['reg_password']) ? $_POST['reg_password']       : '';
        $regConfirm  = isset($_POST['reg_confirm'])  ? $_POST['reg_confirm']        : '';

        // Validate
        if ($regName === '' || $regEmail === '' || $regPassword === '' || $regConfirm === '') {
            $registerError = 'Mohon lengkapi semua field pendaftaran.';
        } elseif (!filter_var($regEmail, FILTER_VALIDATE_EMAIL)) {
            $registerError = 'Format email tidak valid.';
        } elseif (strlen($regPassword) < 6) {
            $registerError = 'Password minimal 6 karakter.';
        } elseif ($regPassword !== $regConfirm) {
            $registerError = 'Konfirmasi password tidak cocok.';
        } else {
            try {
                // Check if email already registered
                $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
                $check->execute([':email' => $regEmail]);

                if ($check->fetch()) {
                    $registerError = 'Email ini sudah terdaftar. Silakan gunakan email lain atau masuk.';
                } else {
                    // Insert new user
                    $hashedPassword = password_hash($regPassword, PASSWORD_BCRYPT);
                    $insert = $pdo->prepare(
                        'INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)'
                    );
                    $insert->execute([
                        ':name'     => $regName,
                        ':email'    => $regEmail,
                        ':password' => $hashedPassword,
                        ':role'     => 'user',
                    ]);

                    // Success — redirect back to login tab with flash
                    $_SESSION['register_success'] = 'Akun berhasil dibuat! Silakan masuk dengan email dan password Anda.';
                    header('Location: login.php');
                    exit;
                }
            } catch (PDOException $e) {
                error_log('Register Error: ' . $e->getMessage());
                $registerError = 'Terjadi kesalahan sistem. Silakan coba lagi nanti.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login ke E-WARUNG — Warung Tiga Saudara">
    <title>Masuk — E-WARUNG Warung Tiga Saudara</title>
    <link rel="icon" type="image/png" href="images/logo.png">

    <!-- Google Fonts (same as main app) -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ── CSS Reset & Variables ─────────────────────────── */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary:        #0B2D72;
            --primary-dark:   #0C1E43;
            --primary-light:  #0AC4E0;
            --primary-hover:  #0992C2;
            --accent-red:     #0AC4E0;
            --bg:             #FFFFFF;
            --bg-card:        #FFFFFF;
            --text-primary:   #0C1E43;
            --text-secondary: #4B5F83;
            --text-light:     #8FA0B0;
            --border:         #D1D9E6;
            --border-light:   #E2E8EE;
            --radius-md:      10px;
            --radius-lg:      16px;
            --radius-xl:      20px;
            --shadow-lg:      0 8px 30px rgba(0, 0, 0, 0.10);
            --transition:     0.3s ease;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--bg);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* ── Login Container ──────────────────────────────── */
        .login-container {
            display: flex;
            width: 100%;
            max-width: 960px;
            min-height: 560px;
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--border-light);
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Left Panel: Branding ─────────────────────────── */
        .login-brand {
            flex: 0 0 45%;
            background: linear-gradient(145deg, var(--primary-dark) 0%, var(--primary) 50%, var(--primary-light) 100%);
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative circles */
        .login-brand::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            border: 40px solid rgba(255, 255, 255, 0.04);
            pointer-events: none;
        }

        .login-brand::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: -60px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            pointer-events: none;
        }

        .login-brand__logo {
            width: 72px;
            height: 72px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 28px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .login-brand__logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .login-brand__logo svg {
            width: 36px;
            height: 36px;
        }

        .login-brand__title {
            font-size: 1.8rem;
            font-weight: 800;
            text-align: center;
            line-height: 1.25;
            margin-bottom: 12px;
            z-index: 1;
        }

        .login-brand__subtitle {
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.75);
            text-align: center;
            line-height: 1.6;
            max-width: 280px;
            z-index: 1;
        }

        .login-brand__features {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 36px;
            z-index: 1;
        }

        .login-brand__feature {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .login-brand__feature-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .login-brand__feature-icon svg {
            width: 16px;
            height: 16px;
        }

        /* ── Right Panel: Form ────────────────────────────── */
        .login-form-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 44px;
        }

        .login-form-panel__header {
            margin-bottom: 28px;
        }

        .login-form-panel__title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 6px;
        }

        .login-form-panel__desc {
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        /* ── Error Alert ──────────────────────────────────── */
        .login-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: rgba(184, 56, 44, 0.06);
            border: 1px solid rgba(184, 56, 44, 0.25);
            border-radius: var(--radius-md);
            color: var(--accent-red);
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 20px;
            animation: shakeX 0.4s ease;
        }

        @keyframes shakeX {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        .login-alert__icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* ── Info Alert (cart redirect) ───────────────────── */
        .login-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: rgba(11, 45, 114, 0.06);
            border: 1px solid rgba(11, 45, 114, 0.2);
            border-radius: var(--radius-md);
            color: var(--primary-dark);
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 20px;
            animation: fadeInInfo 0.4s ease;
        }

        @keyframes fadeInInfo {
            from { opacity: 0; transform: translateY(-4px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-info__icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* ── Form ─────────────────────────────────────────── */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .login-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .login-field__label {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .login-field__input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .login-field__icon {
            position: absolute;
            left: 14px;
            width: 18px;
            height: 18px;
            color: var(--text-light);
            pointer-events: none;
            transition: color var(--transition);    
        }

        .login-field__input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            background: #F8F6F4;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            font-family: inherit;
            color: var(--text-primary);
            outline: none;
            transition: all var(--transition);
        }

        .login-field__input::placeholder {
            color: var(--text-light);
        }

        .login-field__input:focus {
            border-color: var(--primary-light);
            background: #FFFFFF;
            box-shadow: 0 0 0 3px rgba(11, 45, 114, 0.08);
        }

        .login-field__input:focus + .login-field__icon,
        .login-field__input:focus ~ .login-field__icon {
            color: var(--primary);
        }

        .login-field__toggle-pw {
            position: absolute;
            right: 12px;
            width: 20px;
            height: 20px;
            background: transparent;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color var(--transition);
        }

        .login-field__toggle-pw:hover {
            color: var(--primary);
        }

        /* ── Extras Row ───────────────────────────────────── */
        .login-extras {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.78rem;
            color: var(--text-secondary);
        }

        .login-extras__remember {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-weight: 500;
        }

        .login-extras__remember input[type="checkbox"] {
            accent-color: var(--primary);
            width: 14px;
            height: 14px;
        }

        .login-extras__forgot {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .login-extras__forgot:hover {
            text-decoration: underline;
        }

        /* ── Submit Button ────────────────────────────────── */
        .login-submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #FFFFFF;
            font-size: 0.9rem;
            font-weight: 700;
            font-family: inherit;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition);
            box-shadow: 0 4px 16px rgba(11, 45, 114, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 4px;
        }

        .login-submit-btn:hover {
            background: linear-gradient(135deg, var(--primary-hover), var(--primary-dark));
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(11, 45, 114, 0.35);
        }

        .login-submit-btn:active {
            transform: translateY(0);
        }

        .login-submit-btn__icon {
            width: 18px;
            height: 18px;
        }

        /* ── Footer ───────────────────────────────────────── */
        .login-footer {
            text-align: center;
            font-size: 0.78rem;
            color: var(--text-light);
            margin-top: 22px;
        }

        .login-footer a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        /* ── Tab Toggle ───────────────────────────────────── */
        .auth-tabs {
            display: flex;
            background: #F0EBE4;
            border-radius: var(--radius-md);
            padding: 4px;
            margin-bottom: 28px;
            gap: 4px;
        }

        .auth-tab-btn {
            flex: 1;
            padding: 9px 10px;
            border: none;
            background: transparent;
            border-radius: calc(var(--radius-md) - 2px);
            font-family: inherit;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.22s ease;
        }

        .auth-tab-btn.auth-tab-btn--active {
            background: var(--bg-card);
            color: var(--primary-dark);
            box-shadow: 0 2px 8px rgba(11, 45, 114, 0.12);
        }

        /* ── Panel Sections (login/register) ──────────────── */
        .auth-panel {
            display: none;
            animation: panelFadeIn 0.28s ease;
        }
        .auth-panel.auth-panel--active {
            display: block;
        }

        @keyframes panelFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Success Alert ────────────────────────────────── */
        .login-success {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: rgba(22, 163, 74, 0.07);
            border: 1px solid rgba(22, 163, 74, 0.3);
            border-radius: var(--radius-md);
            color: #15803d;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 20px;
            animation: fadeInInfo 0.4s ease;
        }
        .login-success svg { width: 18px; height: 18px; flex-shrink: 0; }

        /* ── Register error same style as login-alert ─────── */
        .register-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: rgba(184, 56, 44, 0.06);
            border: 1px solid rgba(184, 56, 44, 0.25);
            border-radius: var(--radius-md);
            color: var(--accent-red);
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 20px;
            animation: shakeX 0.4s ease;
        }
        .register-alert svg { width: 18px; height: 18px; flex-shrink: 0; }

        /* ── Divider ──────────────────────────────────────── */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 18px 0;
            color: var(--text-light);
            font-size: 0.72rem;
            letter-spacing: 0.05em;
        }
        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Credentials Hint (dev only) ──────────────────── */
        .login-dev-hint {
            margin-top: 24px;
            padding: 12px 14px;
            background: #FEF9E7;
            border: 1px solid #F9E79F;
            border-radius: var(--radius-md);
            font-size: 0.72rem;
            color: #7D6608;
            line-height: 1.6;
        }

        .login-dev-hint strong {
            display: block;
            margin-bottom: 4px;
            font-size: 0.75rem;
        }

        .login-dev-hint code {
            font-family: 'Courier New', monospace;
            background: rgba(125, 102, 8, 0.08);
            padding: 1px 5px;
            border-radius: 3px;
        }

        /* ── Responsive ───────────────────────────────────── */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 440px;
                min-height: auto;
            }

            .login-brand {
                flex: none;
                padding: 32px 28px;
            }

            .login-brand__features {
                display: none;
            }

            .login-brand__title {
                font-size: 1.4rem;
            }

            .login-form-panel {
                padding: 32px 28px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 12px;
            }

            .login-container {
                border-radius: var(--radius-lg);
            }

            .login-form-panel {
                padding: 24px 20px;
            }
        }
    </style>
</head>
<body>

<div class="login-container">

    <!-- ═══════════════════════════════════════════════════════
         LEFT PANEL: Branding & Illustration
         ═══════════════════════════════════════════════════════ -->
    <div class="login-brand">
        <div class="login-brand__logo">
            <img src="assets/images/logo.png" alt="E-WARUNG Logo" onerror="this.style.display='none'; this.parentElement.innerHTML='<svg viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; stroke-width=&quot;2&quot;><path d=&quot;M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z&quot;/><line x1=&quot;3&quot; y1=&quot;6&quot; x2=&quot;21&quot; y2=&quot;6&quot;/><path d=&quot;M16 10a4 4 0 01-8 0&quot;/></svg>';">
        </div>
        <h1 class="login-brand__title">Selamat Datang<br>di E-WARUNG</h1>
        <p class="login-brand__subtitle">Warung Tiga Saudara — Pusat belanja serba ada untuk kebutuhan sehari-hari Anda.</p>

        <div class="login-brand__features">
            <div class="login-brand__feature">
                <span class="login-brand__feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </span>
                Belanja mudah, harga terjangkau
            </div>
            <div class="login-brand__feature">
                <span class="login-brand__feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                    </svg>
                </span>
                Keranjang & pembayaran aman
            </div>
            <div class="login-brand__feature">
                <span class="login-brand__feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>
                    </svg>
                </span>
                Promo eksklusif setiap bulan
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         RIGHT PANEL: Login Form
         ═══════════════════════════════════════════════════════ -->
    <div class="login-form-panel" id="login-form-panel">

        <!-- ══ Page Title ══════════════════════════════════ -->
        <div class="login-form-panel__header">
            <h2 class="login-form-panel__title" id="auth-panel-title">Akun E-WARUNG</h2>
            <p class="login-form-panel__desc">Masuk atau buat akun baru untuk mulai berbelanja.</p>
        </div>

        <!-- ══ Tab Toggle: Masuk / Daftar ══════════════════ -->
        <div class="auth-tabs" id="auth-tabs" role="tablist">
            <button type="button"
                    class="auth-tab-btn <?= $activeTab === 'login' ? 'auth-tab-btn--active' : '' ?>"
                    id="tab-btn-login"
                    role="tab"
                    aria-selected="<?= $activeTab === 'login' ? 'true' : 'false' ?>"
                    aria-controls="panel-login"
                    onclick="switchTab('login')">
                Masuk
            </button>
            <button type="button"
                    class="auth-tab-btn <?= $activeTab === 'register' ? 'auth-tab-btn--active' : '' ?>"
                    id="tab-btn-register"
                    role="tab"
                    aria-selected="<?= $activeTab === 'register' ? 'true' : 'false' ?>"
                    aria-controls="panel-register"
                    onclick="switchTab('register')">
                Daftar
            </button>
        </div>

        <!-- ══════════════════════════════════════════════════
             PANEL: MASUK (LOGIN)
             ══════════════════════════════════════════════════ -->
        <div class="auth-panel <?= $activeTab === 'login' ? 'auth-panel--active' : '' ?>"
             id="panel-login" role="tabpanel" aria-labelledby="tab-btn-login">

            <?php if ($registerSuccess !== ''): ?>
                <div class="login-success" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <?= htmlspecialchars($registerSuccess) ?>
                </div>
            <?php endif; ?>

            <?php if ($loginRequiredMessage !== ''): ?>
                <div class="login-info">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="login-info__icon">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                    </svg>
                    <?= htmlspecialchars($loginRequiredMessage) ?>
                </div>
            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>
                <div class="login-alert" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="login-alert__icon">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="login-form" id="loginPageForm">
                <input type="hidden" name="action" value="login">

                <!-- Email -->
                <div class="login-field">
                    <label class="login-field__label" for="login-email-field">ALAMAT EMAIL</label>
                    <div class="login-field__input-wrapper">
                        <input
                            type="email"
                            id="login-email-field"
                            name="email"
                            class="login-field__input"
                            placeholder="nama@email.com"
                            value="<?= (isset($_POST['action']) && $_POST['action'] === 'login' && isset($_POST['email'])) ? htmlspecialchars($_POST['email']) : '' ?>"
                            required
                            autocomplete="email"
                        >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="login-field__icon">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                </div>

                <!-- Password -->
                <div class="login-field">
                    <label class="login-field__label" for="login-password-field">PASSWORD</label>
                    <div class="login-field__input-wrapper">
                        <input
                            type="password"
                            id="login-password-field"
                            name="password"
                            class="login-field__input"
                            placeholder="Masukkan password Anda"
                            required
                            autocomplete="current-password"
                        >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="login-field__icon">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <button type="button" class="login-field__toggle-pw" id="togglePassword" title="Tampilkan password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="eyeIcon">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Extras Row -->
                <div class="login-extras">
                    <label class="login-extras__remember">
                        <input type="checkbox" name="remember"> Ingat saya
                    </label>
                    <a href="#" class="login-extras__forgot">Lupa Password?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="login-submit-btn" id="login-submit-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="login-submit-btn__icon">
                        <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M13.8 12H3"/>
                    </svg>
                    Masuk Sekarang
                </button>
            </form>

            <div class="auth-divider">ATAU</div>
            <p style="text-align:center; font-size:.82rem; color:var(--text-secondary);">
                Belum punya akun?
                <a href="#" onclick="switchTab('register'); return false;"
                   style="color:var(--primary); font-weight:700; text-decoration:none;">
                   Daftar Sekarang
                </a>
            </p>

        </div><!-- /panel-login -->

        <!-- ══════════════════════════════════════════════════
             PANEL: DAFTAR (REGISTER)
             ══════════════════════════════════════════════════ -->
        <div class="auth-panel <?= $activeTab === 'register' ? 'auth-panel--active' : '' ?>"
             id="panel-register" role="tabpanel" aria-labelledby="tab-btn-register">

            <?php if ($registerError !== ''): ?>
                <div class="register-alert" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <?= htmlspecialchars($registerError) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="login-form" id="registerForm">
                <input type="hidden" name="action" value="register">

                <!-- Nama Lengkap -->
                <div class="login-field">
                    <label class="login-field__label" for="reg-name-field">NAMA LENGKAP</label>
                    <div class="login-field__input-wrapper">
                        <input
                            type="text"
                            id="reg-name-field"
                            name="reg_name"
                            class="login-field__input"
                            placeholder="Nama lengkap Anda"
                            value="<?= (isset($_POST['action']) && $_POST['action'] === 'register' && isset($_POST['reg_name'])) ? htmlspecialchars($_POST['reg_name']) : '' ?>"
                            required
                            autocomplete="name"
                        >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="login-field__icon">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                </div>

                <!-- Email -->
                <div class="login-field">
                    <label class="login-field__label" for="reg-email-field">ALAMAT EMAIL</label>
                    <div class="login-field__input-wrapper">
                        <input
                            type="email"
                            id="reg-email-field"
                            name="reg_email"
                            class="login-field__input"
                            placeholder="nama@email.com"
                            value="<?= (isset($_POST['action']) && $_POST['action'] === 'register' && isset($_POST['reg_email'])) ? htmlspecialchars($_POST['reg_email']) : '' ?>"
                            required
                            autocomplete="email"
                        >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="login-field__icon">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                </div>

                <!-- Password -->
                <div class="login-field">
                    <label class="login-field__label" for="reg-password-field">PASSWORD <span style="font-size:.65rem; text-transform:none; letter-spacing:0; font-weight:400; color:var(--text-light);">(min. 6 karakter)</span></label>
                    <div class="login-field__input-wrapper">
                        <input
                            type="password"
                            id="reg-password-field"
                            name="reg_password"
                            class="login-field__input"
                            placeholder="Buat password Anda"
                            required
                            autocomplete="new-password"
                        >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="login-field__icon">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <button type="button" class="login-field__toggle-pw" id="toggleRegPassword" title="Tampilkan password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="eyeIconReg">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Konfirmasi Password -->
                <div class="login-field">
                    <label class="login-field__label" for="reg-confirm-field">KONFIRMASI PASSWORD</label>
                    <div class="login-field__input-wrapper">
                        <input
                            type="password"
                            id="reg-confirm-field"
                            name="reg_confirm"
                            class="login-field__input"
                            placeholder="Ulangi password Anda"
                            required
                            autocomplete="new-password"
                        >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="login-field__icon">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                </div>

                <!-- Terms note -->
                <p style="font-size:.72rem; color:var(--text-light); line-height:1.5; margin-top:-4px;">
                    Dengan mendaftar, Anda menyetujui
                    <a href="#" style="color:var(--primary); font-weight:600;">Syarat &amp; Ketentuan</a>
                    dan <a href="#" style="color:var(--primary); font-weight:600;">Kebijakan Privasi</a>
                    Warung Tiga Saudara.
                </p>

                <!-- Submit -->
                <button type="submit" class="login-submit-btn" id="register-submit-btn"
                        style="background: linear-gradient(135deg, #B8382C, #8B2220);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="login-submit-btn__icon">
                        <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <line x1="19" y1="8" x2="19" y2="14"/>
                        <line x1="22" y1="11" x2="16" y2="11"/>
                    </svg>
                    Buat Akun Sekarang
                </button>
            </form>

            <div class="auth-divider">ATAU</div>
            <p style="text-align:center; font-size:.82rem; color:var(--text-secondary);">
                Sudah punya akun?
                <a href="#" onclick="switchTab('login'); return false;"
                   style="color:var(--primary); font-weight:700; text-decoration:none;">
                   Masuk di Sini
                </a>
            </p>

        </div><!-- /panel-register -->

        <!-- Footer -->
        <div class="login-footer" style="margin-top:20px;">
            &copy; 2026 Warung Tiga Saudara.
        </div>
    </div>

</div>

<!-- Auth Tab Switcher + Password Toggle Scripts -->
<script>
    /* ── Tab Switch ────────────────────────────────────────── */
    function switchTab(tab) {
        var panels  = document.querySelectorAll('.auth-panel');
        var buttons = document.querySelectorAll('.auth-tab-btn');

        panels.forEach(function (p) { p.classList.remove('auth-panel--active'); });
        buttons.forEach(function (b) {
            b.classList.remove('auth-tab-btn--active');
            b.setAttribute('aria-selected', 'false');
        });

        var targetPanel = document.getElementById('panel-' + tab);
        var targetBtn   = document.getElementById('tab-btn-' + tab);

        if (targetPanel) targetPanel.classList.add('auth-panel--active');
        if (targetBtn) {
            targetBtn.classList.add('auth-tab-btn--active');
            targetBtn.setAttribute('aria-selected', 'true');
        }
    }

    /* ── Password Toggle — Login ───────────────────────────── */
    document.getElementById('togglePassword').addEventListener('click', function () {
        var pwd  = document.getElementById('login-password-field');
        var icon = document.getElementById('eyeIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            pwd.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    });

    /* ── Password Toggle — Register ────────────────────────── */
    document.getElementById('toggleRegPassword').addEventListener('click', function () {
        var pwd  = document.getElementById('reg-password-field');
        var icon = document.getElementById('eyeIconReg');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            pwd.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    });

    /* ── Client-side password match check ─────────────────── */
    (function () {
        var form     = document.getElementById('registerForm');
        var pwField  = document.getElementById('reg-password-field');
        var cfmField = document.getElementById('reg-confirm-field');
        if (!form) return;

        cfmField.addEventListener('input', function () {
            if (this.value && this.value !== pwField.value) {
                this.style.borderColor = '#B8382C';
            } else {
                this.style.borderColor = '';
            }
        });

        form.addEventListener('submit', function (e) {
            if (pwField.value !== cfmField.value) {
                e.preventDefault();
                cfmField.style.borderColor = '#B8382C';
                cfmField.focus();
            }
        });
    }());
</script>

</body>
</html>

<?php
/*
 * ============================================================
 * SESSION PROTECTION SNIPPETS
 * ============================================================
 * Paste the appropriate snippet at the VERY TOP of each
 * protected PHP file (before any HTML output).
 * ============================================================
 *
 *
 * ── SNIPPET A: Admin-Only Pages ────────────────────────────
 * Use on: admin_only_page.php, manage_stock.php, edit_prices.php, etc.
 * ────────────────────────────────────────────────────────────
 *
 * <?php
 * session_start();
 * if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
 *     header('Location: login.php');
 *     exit;
 * }
 * ?>
 *
 *
 * ── SNIPPET B: Authenticated User Pages ────────────────────
 * Use on: checkout.php, profile.php, order_history.php, etc.
 * ────────────────────────────────────────────────────────────
 *
 * <?php
 * session_start();
 * if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'user'])) {
 *     header('Location: login.php');
 *     exit;
 * }
 * ?>
 *
 *
 * ── SNIPPET C: Logout Handler ──────────────────────────────
 * Create a file called logout.php with:
 * ────────────────────────────────────────────────────────────
 *
 * <?php
 * session_start();
 * session_unset();
 * session_destroy();
 * header('Location: login.php');
 * exit;
 * ?>
 *
 * ============================================================
 */
?>
