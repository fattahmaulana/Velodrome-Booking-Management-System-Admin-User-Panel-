<?php
require_once __DIR__ . '/../config/koneksi.php';
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . (isAdmin() ? '/admin/' : '/penyewa/'));
    exit;
}

$error   = '';
$oldUser = '';
$flash   = getFlash('auth');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $oldUser  = $username;

    if (!$username || !$password) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role']         = $user['role'];
            $_SESSION['email']        = $user['email'];
            header('Location: ' . BASE_URL . ($user['role'] === 'admin' ? '/admin/' : '/penyewa/'));
            exit;
        }
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Velodrome Diponegoro</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        maroon: {
                            50:  '#fdf0f0', 100: '#fadddd', 200: '#f7bbbb',
                            300: '#f09090', 400: '#e55e5e', 500: '#d63232',
                            600: '#c41a1a', 700: '#a31414', 800: '#861111',
                            900: '#6e1010', 950: '#3f0808'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --maroon-deep:   #3f0808;
            --maroon-mid:    #7f1010;
            --maroon-bright: #c41a1a;
            --maroon-glow:   rgba(196, 26, 26, 0.35);
            --gold-accent:   #c9a14a;
            --gold-soft:     rgba(201, 161, 74, 0.12);
            --dark-base:     #0d0909;
            --dark-panel:    #160c0c;
            --glass-bg:      rgba(255,255,255,0.04);
            --glass-border:  rgba(255,255,255,0.09);
        }

        html, body { height: 100%; }

        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background: var(--dark-base);
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }

        /* ---------------------------------------------------------------- */
        /* Animated background canvas                                        */
        /* ---------------------------------------------------------------- */
        .bg-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0;
            animation: orbFloat 12s ease-in-out infinite;
        }
        .bg-orb-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(180,20,20,0.55) 0%, transparent 70%);
            top: -15%; left: -10%;
            animation-delay: 0s;
        }
        .bg-orb-2 {
            width: 420px; height: 420px;
            background: radial-gradient(circle, rgba(120,10,10,0.4) 0%, transparent 70%);
            bottom: -10%; right: 35%;
            animation-delay: 4s;
            animation-duration: 15s;
        }
        .bg-orb-3 {
            width: 280px; height: 280px;
            background: radial-gradient(circle, rgba(201,161,74,0.18) 0%, transparent 70%);
            top: 30%; right: -5%;
            animation-delay: 2s;
            animation-duration: 18s;
        }

        @keyframes orbFloat {
            0%   { opacity: 0; transform: scale(0.9) translate(0, 0); }
            20%  { opacity: 1; }
            50%  { transform: scale(1.08) translate(18px, -24px); }
            80%  { opacity: 1; }
            100% { opacity: 0; transform: scale(0.9) translate(0, 0); }
        }

        /* Fine grid texture */
        .bg-grid {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.022) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.022) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        /* ---------------------------------------------------------------- */
        /* Left panel                                                        */
        /* ---------------------------------------------------------------- */
        .left-panel {
            position: relative;
            z-index: 1;
            width: 44%;
            display: none;
            flex-direction: column;
            justify-content: center;
            padding: 64px 60px;
            border-right: 1px solid rgba(255,255,255,0.06);
            animation: panelReveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @media (min-width: 1024px) { .left-panel { display: flex; } }

        @keyframes panelReveal {
            from { opacity: 0; transform: translateX(-24px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* Decorative arc lines */
        .arc-decor {
            position: absolute;
            top: -80px; right: -120px;
            width: 520px; height: 520px;
            border-radius: 50%;
            border: 1px solid rgba(196,26,26,0.12);
            pointer-events: none;
        }
        .arc-decor::before {
            content: '';
            position: absolute;
            inset: 40px;
            border-radius: 50%;
            border: 1px solid rgba(201,161,74,0.08);
        }
        .arc-decor::after {
            content: '';
            position: absolute;
            inset: 90px;
            border-radius: 50%;
            border: 1px solid rgba(196,26,26,0.06);
        }

        .brand-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, rgba(196,26,26,0.3), rgba(120,10,10,0.5));
            border: 1px solid rgba(196,26,26,0.35);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 36px;
            animation: fadeInUp 0.6s 0.3s both;
            box-shadow: 0 0 28px rgba(196,26,26,0.25), inset 0 1px 0 rgba(255,255,255,0.1);
        }
        .brand-icon i { color: #f8d0d0; font-size: 20px; }

        .left-heading {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 2.6rem;
            line-height: 1.12;
            color: #f5eaea;
            margin-bottom: 18px;
            animation: fadeInUp 0.6s 0.4s both;
            letter-spacing: -0.02em;
        }
        .left-heading em {
            font-style: italic;
            color: var(--gold-accent);
        }

        .left-desc {
            font-size: 14px;
            line-height: 1.75;
            color: rgba(245,220,220,0.5);
            max-width: 300px;
            margin-bottom: 44px;
            animation: fadeInUp 0.6s 0.5s both;
        }

        .feature-list { display: flex; flex-direction: column; gap: 14px; }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 13px;
            animation: fadeInUp 0.5s ease both;
        }
        .feature-item:nth-child(1) { animation-delay: 0.58s; }
        .feature-item:nth-child(2) { animation-delay: 0.66s; }
        .feature-item:nth-child(3) { animation-delay: 0.74s; }

        .feature-dot {
            width: 32px; height: 32px;
            border-radius: 9px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background 0.3s ease, border-color 0.3s ease;
        }
        .feature-item:hover .feature-dot {
            background: rgba(196,26,26,0.15);
            border-color: rgba(196,26,26,0.3);
        }
        .feature-dot i { color: var(--gold-accent); font-size: 11px; }
        .feature-text { font-size: 13px; color: rgba(245,220,220,0.55); font-weight: 400; }

        /* Divider line bottom */
        .left-bottom-line {
            position: absolute;
            bottom: 48px; left: 60px;
            right: 60px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(196,26,26,0.25), rgba(201,161,74,0.2), transparent);
            animation: fadeIn 1s 0.8s both;
        }

        /* ---------------------------------------------------------------- */
        /* Right panel / form side                                           */
        /* ---------------------------------------------------------------- */
        .right-panel {
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 28px;
        }

        .form-card {
            width: 100%;
            max-width: 380px;
        }

        /* Mobile brand bar */
        .mobile-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 36px;
            animation: fadeInUp 0.5s 0.1s both;
        }
        @media (min-width: 1024px) { .mobile-brand { display: none; } }

        .mobile-brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, rgba(196,26,26,0.4), rgba(120,10,10,0.6));
            border: 1px solid rgba(196,26,26,0.4);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mobile-brand-icon i { color: #f8d0d0; font-size: 13px; }
        .mobile-brand-name { font-size: 14px; font-weight: 600; color: #f0dede; }

        /* Heading block */
        .form-heading {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 2rem;
            color: #f5eaea;
            letter-spacing: -0.02em;
            line-height: 1.1;
            animation: fadeInUp 0.55s 0.18s both;
        }
        .form-subheading {
            font-size: 13px;
            color: rgba(245,220,220,0.4);
            margin-top: 6px;
            margin-bottom: 28px;
            animation: fadeInUp 0.55s 0.24s both;
        }

        /* Alert notices */
        .alert {
            padding: 11px 14px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-size: 12.5px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeInUp 0.4s ease both;
        }
        .alert-error   { background: rgba(180,20,20,0.18); border: 1px solid rgba(196,26,26,0.35); color: #f5b0b0; }
        .alert-warning { background: rgba(180,130,20,0.14); border: 1px solid rgba(201,161,74,0.3); color: #f5dea0; }
        .alert-success { background: rgba(20,140,70,0.14); border: 1px solid rgba(30,180,90,0.25); color: #90f0b0; }
        .alert i { font-size: 11px; flex-shrink: 0; }

        /* Form group */
        .form-group { margin-bottom: 16px; animation: fadeInUp 0.5s ease both; }
        .form-group:nth-child(1) { animation-delay: 0.32s; }
        .form-group:nth-child(2) { animation-delay: 0.40s; }

        .form-label {
            display: block;
            font-size: 10.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: rgba(245,220,220,0.38);
            margin-bottom: 7px;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(245,220,220,0.22);
            font-size: 11px;
            pointer-events: none;
            transition: color 0.25s ease;
            z-index: 1;
        }

        .form-input {
            width: 100%;
            padding: 11px 14px 11px 42px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 13px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13.5px;
            color: #f0dede;
            outline: none;
            transition:
                background 0.25s ease,
                border-color 0.25s ease,
                box-shadow 0.3s ease;
            -webkit-appearance: none;
        }
        .form-input::placeholder { color: rgba(245,220,220,0.2); }

        .form-input:hover {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.13);
        }
        .form-input:focus {
            background: rgba(196,26,26,0.07);
            border-color: rgba(196,26,26,0.5);
            box-shadow: 0 0 0 3px rgba(196,26,26,0.1), 0 0 20px rgba(196,26,26,0.08);
        }
        .input-wrap:focus-within .input-icon { color: rgba(196,26,26,0.8); }

        .pw-toggle {
            position: absolute;
            right: 0; top: 0; bottom: 0;
            width: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(245,220,220,0.22);
            font-size: 11px;
            transition: color 0.2s ease;
        }
        .pw-toggle:hover { color: rgba(245,220,220,0.55); }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 12px;
            margin-top: 22px;
            background: linear-gradient(135deg, #c41a1a 0%, #8b0f0f 60%, #6e0a0a 100%);
            border: 1px solid rgba(196,26,26,0.5);
            border-radius: 13px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13.5px;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            letter-spacing: 0.02em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            position: relative;
            overflow: hidden;
            transition:
                transform 0.2s ease,
                box-shadow 0.3s ease,
                background 0.3s ease;
            animation: fadeInUp 0.5s 0.5s both;
            box-shadow:
                0 4px 24px rgba(196,26,26,0.28),
                0 1px 0 rgba(255,255,255,0.08) inset;
        }

        /* Shimmer sweep on hover */
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0; left: -80%;
            width: 60%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: skewX(-20deg);
            transition: left 0.5s ease;
        }
        .btn-submit:hover::before { left: 130%; }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow:
                0 8px 32px rgba(196,26,26,0.42),
                0 2px 0 rgba(255,255,255,0.08) inset;
            background: linear-gradient(135deg, #d42020 0%, #9b1212 60%, #7e0d0d 100%);
        }
        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 3px 14px rgba(196,26,26,0.25);
        }

        /* Footer links */
        .form-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 12.5px;
            color: rgba(245,220,220,0.3);
            animation: fadeIn 0.6s 0.6s both;
        }
        .form-footer a {
            color: var(--gold-accent);
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .form-footer a:hover { color: #e0b85a; }

        .form-demo {
            text-align: center;
            margin-top: 14px;
            font-size: 11px;
            color: rgba(245,220,220,0.18);
            animation: fadeIn 0.6s 0.7s both;
        }

        /* Gold accent separator */
        .gold-line {
            width: 32px; height: 2px;
            background: linear-gradient(90deg, var(--gold-accent), transparent);
            margin: 14px 0 22px;
            border-radius: 2px;
            animation: fadeIn 0.5s 0.28s both;
        }

        /* ---------------------------------------------------------------- */
        /* Shared keyframes                                                  */
        /* ---------------------------------------------------------------- */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* Autofill override */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-text-fill-color: #f0dede;
            -webkit-box-shadow: 0 0 0 100px rgba(90,10,10,0.35) inset;
            transition: background-color 9999s ease;
        }
    </style>
</head>

<body>
    <!-- Animated background -->
    <div class="bg-canvas">
        <div class="bg-orb bg-orb-1"></div>
        <div class="bg-orb bg-orb-2"></div>
        <div class="bg-orb bg-orb-3"></div>
    </div>
    <div class="bg-grid"></div>

    <!-- Left panel -->
    <div class="left-panel">
        <div class="arc-decor"></div>
        <div class="brand-icon">
            <i class="fa-solid fa-person-biking"></i>
        </div>
        <h2 class="left-heading">
            Arena<br>Velodrome<br><em>Diponegoro</em>
        </h2>
        <p class="left-desc">
            Platform penyewaan arena resmi. Pesan lintasan, kelola jadwal, dan pantau riwayat transaksi Anda dalam satu tempat.
        </p>
        <div class="feature-list">
            <div class="feature-item">
                <div class="feature-dot"><i class="fa-solid fa-bolt-lightning"></i></div>
                <span class="feature-text">Pemesanan cepat dan transparan</span>
            </div>
            <div class="feature-item">
                <div class="feature-dot"><i class="fa-solid fa-shield-halved"></i></div>
                <span class="feature-text">Pembayaran aman dan terverifikasi</span>
            </div>
            <div class="feature-item">
                <div class="feature-dot"><i class="fa-solid fa-chart-line"></i></div>
                <span class="feature-text">Pantau riwayat penyewaan real-time</span>
            </div>
        </div>
        <div class="left-bottom-line"></div>
    </div>

    <!-- Right panel -->
    <div class="right-panel">
        <div class="form-card">

            <!-- Mobile brand -->
            <div class="mobile-brand">
                <div class="mobile-brand-icon">
                    <i class="fa-solid fa-person-biking"></i>
                </div>
                <span class="mobile-brand-name">Velodrome Diponegoro</span>
            </div>

            <!-- Heading -->
            <h1 class="form-heading">Masuk</h1>
            <div class="gold-line"></div>
            <p class="form-subheading">Masuk ke akun Anda untuk melanjutkan</p>

            <!-- Flash notice -->
            <?php if ($flash): ?>
            <div data-flash class="alert <?= $flash['type'] === 'warning' ? 'alert-warning' : 'alert-success' ?>">
                <i class="fa-solid <?= $flash['type'] === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-check' ?>"></i>
                <?= sanitize($flash['message']) ?>
            </div>
            <?php endif; ?>

            <!-- Error notice -->
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= sanitize($error) ?>
            </div>
            <?php endif; ?>

            <!-- Login form -->
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                        <input
                            type="text"
                            name="username"
                            value="<?= sanitize($oldUser) ?>"
                            class="form-input"
                            placeholder="Username Anda"
                            required
                            autofocus
                            autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                        <input
                            type="password"
                            name="password"
                            id="pw"
                            class="form-input"
                            placeholder="Password Anda"
                            required
                            autocomplete="current-password">
                        <button type="button" class="pw-toggle" id="pwToggle" title="Tampilkan password">
                            <i class="fa-solid fa-eye" id="pwToggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-right-to-bracket" style="font-size:12px;"></i>
                    Masuk ke Akun
                </button>
            </form>

            <p class="form-footer">
                Belum punya akun?
                <a href="<?= BASE_URL ?>/auth/register.php">Daftar sekarang</a>
            </p>

        </div>
    </div>

    <script>
        // Password visibility toggle
        (function () {
            var input  = document.getElementById('pw');
            var toggle = document.getElementById('pwToggle');
            var icon   = document.getElementById('pwToggleIcon');
            if (!toggle) return;
            toggle.addEventListener('click', function () {
                var isPass = input.type === 'password';
                input.type = isPass ? 'text' : 'password';
                icon.className = isPass ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
            });
        })();

        // Auto-dismiss flash notices after 5 seconds
        (function () {
            var flash = document.querySelector('[data-flash]');
            if (!flash) return;
            setTimeout(function () {
                flash.style.transition = 'opacity 0.5s ease, transform 0.5s ease, max-height 0.5s ease, margin 0.5s ease, padding 0.5s ease';
                flash.style.opacity   = '0';
                flash.style.transform = 'translateY(-6px)';
                flash.style.maxHeight = '0';
                flash.style.margin    = '0';
                flash.style.padding   = '0';
                flash.style.overflow  = 'hidden';
            }, 5000);
        })();
    </script>
</body>
</html>