<?php
require_once __DIR__ . '/../config/koneksi.php';
if (isLoggedIn()) { header('Location: ' . BASE_URL . '/'); exit; }

$errors = [];
$old = ['username' => '', 'email' => '', 'nama_lengkap' => '', 'no_telepon' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'username'     => trim($_POST['username'] ?? ''),
        'email'        => trim($_POST['email'] ?? ''),
        'nama_lengkap' => trim($_POST['nama_lengkap'] ?? ''),
        'no_telepon'   => trim($_POST['no_telepon'] ?? ''),
    ];
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if (strlen($old['username']) < 4)                      $errors[] = 'Username minimal 4 karakter.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (!$old['nama_lengkap'])                             $errors[] = 'Nama lengkap wajib diisi.';
    if (strlen($password) < 6)                             $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $confirm)                            $errors[] = 'Konfirmasi password tidak cocok.';

    if (empty($errors)) {
        $dup = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $dup->execute([$old['username'], $old['email']]);
        if ($dup->fetch()) $errors[] = 'Username atau email sudah terdaftar.';
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username,password_hash,email,nama_lengkap,no_telepon,role) VALUES (?,?,?,?,?,'penyewa')")
            ->execute([$old['username'], $hash, $old['email'], $old['nama_lengkap'], $old['no_telepon']]);
        flash('auth', 'Registrasi berhasil. Silakan login.', 'success');
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Velodrome Diponegoro</title>

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
                            50:'#fdf0f0', 100:'#fadddd', 200:'#f7bbbb',
                            300:'#f09090', 400:'#e55e5e', 500:'#d63232',
                            600:'#c41a1a', 700:'#a31414', 800:'#861111',
                            900:'#6e1010', 950:'#3f0808'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --maroon-bright: #c41a1a;
            --maroon-glow:   rgba(196, 26, 26, 0.35);
            --gold-accent:   #c9a14a;
            --dark-base:     #0d0909;
            --glass-bg:      rgba(255,255,255,0.04);
            --glass-border:  rgba(255,255,255,0.09);
        }

        html, body { height: 100%; }

        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background: var(--dark-base);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ---------------------------------------------------------------- */
        /* Animated background                                               */
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
            width: 580px; height: 580px;
            background: radial-gradient(circle, rgba(180,20,20,0.52) 0%, transparent 70%);
            top: -18%; left: -8%;
            animation-delay: 0s;
        }
        .bg-orb-2 {
            width: 380px; height: 380px;
            background: radial-gradient(circle, rgba(110,10,10,0.38) 0%, transparent 70%);
            bottom: -12%; right: 32%;
            animation-delay: 4s;
            animation-duration: 15s;
        }
        .bg-orb-3 {
            width: 260px; height: 260px;
            background: radial-gradient(circle, rgba(201,161,74,0.16) 0%, transparent 70%);
            top: 25%; right: -4%;
            animation-delay: 2s;
            animation-duration: 18s;
        }
        @keyframes orbFloat {
            0%   { opacity: 0; transform: scale(0.9) translate(0, 0); }
            20%  { opacity: 1; }
            50%  { transform: scale(1.08) translate(16px, -20px); }
            80%  { opacity: 1; }
            100% { opacity: 0; transform: scale(0.9) translate(0, 0); }
        }

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
            width: 40%;
            display: none;
            flex-direction: column;
            justify-content: center;
            padding: 64px 52px;
            border-right: 1px solid rgba(255,255,255,0.06);
            animation: panelReveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @media (min-width: 1024px) { .left-panel { display: flex; } }

        @keyframes panelReveal {
            from { opacity: 0; transform: translateX(-24px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .arc-decor {
            position: absolute;
            top: -80px; right: -120px;
            width: 480px; height: 480px;
            border-radius: 50%;
            border: 1px solid rgba(196,26,26,0.11);
            pointer-events: none;
        }
        .arc-decor::before {
            content: '';
            position: absolute;
            inset: 50px;
            border-radius: 50%;
            border: 1px solid rgba(201,161,74,0.07);
        }
        .arc-decor::after {
            content: '';
            position: absolute;
            inset: 110px;
            border-radius: 50%;
            border: 1px solid rgba(196,26,26,0.05);
        }

        .brand-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, rgba(196,26,26,0.3), rgba(120,10,10,0.5));
            border: 1px solid rgba(196,26,26,0.35);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
            animation: fadeInUp 0.6s 0.3s both;
            box-shadow: 0 0 28px rgba(196,26,26,0.22), inset 0 1px 0 rgba(255,255,255,0.09);
        }
        .brand-icon i { color: #f8d0d0; font-size: 20px; }

        .left-heading {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 2.3rem;
            line-height: 1.12;
            color: #f5eaea;
            margin-bottom: 16px;
            animation: fadeInUp 0.6s 0.4s both;
            letter-spacing: -0.02em;
        }
        .left-heading em { font-style: italic; color: var(--gold-accent); }

        .left-desc {
            font-size: 13.5px;
            line-height: 1.75;
            color: rgba(245,220,220,0.48);
            max-width: 280px;
            margin-bottom: 40px;
            animation: fadeInUp 0.6s 0.5s both;
        }

        /* Stats row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 40px;
            animation: fadeInUp 0.6s 0.55s both;
        }
        .stat-block {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            padding: 14px 10px;
            text-align: center;
            transition: background 0.3s ease, border-color 0.3s ease;
        }
        .stat-block:hover {
            background: rgba(196,26,26,0.09);
            border-color: rgba(196,26,26,0.25);
        }
        .stat-value {
            font-family: 'DM Serif Display', serif;
            font-size: 1.55rem;
            color: #f5eaea;
            line-height: 1;
            margin-bottom: 4px;
        }
        .stat-label { font-size: 10.5px; color: rgba(245,220,220,0.35); letter-spacing: 0.05em; }

        .feature-list { display: flex; flex-direction: column; gap: 12px; }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeInUp 0.5s ease both;
        }
        .feature-item:nth-child(1) { animation-delay: 0.60s; }
        .feature-item:nth-child(2) { animation-delay: 0.68s; }

        .feature-dot {
            width: 30px; height: 30px;
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
            background: rgba(196,26,26,0.14);
            border-color: rgba(196,26,26,0.28);
        }
        .feature-dot i { color: var(--gold-accent); font-size: 10px; }
        .feature-text { font-size: 12.5px; color: rgba(245,220,220,0.48); }

        .left-bottom-line {
            position: absolute;
            bottom: 48px; left: 52px; right: 52px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(196,26,26,0.25), rgba(201,161,74,0.18), transparent);
            animation: fadeIn 1s 0.8s both;
        }

        /* ---------------------------------------------------------------- */
        /* Right panel                                                       */
        /* ---------------------------------------------------------------- */
        .right-panel {
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 36px 24px;
            overflow-y: auto;
        }

        .form-card {
            width: 100%;
            max-width: 460px;
            padding: 8px 0;
        }

        .mobile-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
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

        .form-heading {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 1.85rem;
            color: #f5eaea;
            letter-spacing: -0.02em;
            line-height: 1.1;
            animation: fadeInUp 0.55s 0.18s both;
        }
        .gold-line {
            width: 28px; height: 2px;
            background: linear-gradient(90deg, var(--gold-accent), transparent);
            margin: 12px 0 18px;
            border-radius: 2px;
            animation: fadeIn 0.5s 0.26s both;
        }
        .form-subheading {
            font-size: 12.5px;
            color: rgba(245,220,220,0.38);
            margin-bottom: 24px;
            animation: fadeInUp 0.55s 0.24s both;
        }

        /* Error block */
        .alert-error {
            padding: 12px 15px;
            border-radius: 13px;
            margin-bottom: 20px;
            font-size: 12.5px;
            background: rgba(180,20,20,0.18);
            border: 1px solid rgba(196,26,26,0.35);
            color: #f5b0b0;
            animation: fadeInUp 0.4s ease both;
        }
        .alert-error-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 12px;
        }
        .alert-error-header i { font-size: 11px; }
        .alert-error ul { padding-left: 18px; display: flex; flex-direction: column; gap: 3px; }
        .alert-error li { list-style: disc; color: rgba(245,176,176,0.8); }

        /* Form grid */
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
            animation: fadeInUp 0.5s ease both;
        }
        .form-grid-2:nth-of-type(1) { animation-delay: 0.32s; }
        .form-grid-2:nth-of-type(2) { animation-delay: 0.40s; }
        .form-grid-2:nth-of-type(3) { animation-delay: 0.48s; }

        .form-group { display: flex; flex-direction: column; }

        .form-label {
            display: block;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: rgba(245,220,220,0.35);
            margin-bottom: 7px;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(245,220,220,0.2);
            font-size: 10px;
            pointer-events: none;
            transition: color 0.25s ease;
            z-index: 1;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px 10px 38px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            color: #f0dede;
            outline: none;
            transition:
                background 0.25s ease,
                border-color 0.25s ease,
                box-shadow 0.3s ease;
            -webkit-appearance: none;
        }
        .form-input::placeholder { color: rgba(245,220,220,0.18); }
        .form-input:hover {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.12);
        }
        .form-input:focus {
            background: rgba(196,26,26,0.07);
            border-color: rgba(196,26,26,0.5);
            box-shadow: 0 0 0 3px rgba(196,26,26,0.1), 0 0 18px rgba(196,26,26,0.07);
        }
        .input-wrap:focus-within .input-icon { color: rgba(196,26,26,0.75); }

        .pw-toggle {
            position: absolute;
            right: 0; top: 0; bottom: 0;
            width: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(245,220,220,0.2);
            font-size: 10px;
            transition: color 0.2s ease;
        }
        .pw-toggle:hover { color: rgba(245,220,220,0.5); }

        /* Strength meter */
        .strength-bar {
            display: flex;
            gap: 4px;
            margin-top: 7px;
            height: 3px;
        }
        .strength-seg {
            flex: 1;
            border-radius: 2px;
            background: rgba(255,255,255,0.07);
            transition: background 0.35s ease;
        }
        .strength-seg.active-weak   { background: #dc2626; }
        .strength-seg.active-medium { background: #d97706; }
        .strength-seg.active-strong { background: #16a34a; }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: linear-gradient(135deg, #c41a1a 0%, #8b0f0f 60%, #6e0a0a 100%);
            border: 1px solid rgba(196,26,26,0.5);
            border-radius: 13px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
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
                box-shadow 0.3s ease;
            animation: fadeInUp 0.5s 0.56s both;
            box-shadow:
                0 4px 24px rgba(196,26,26,0.26),
                0 1px 0 rgba(255,255,255,0.07) inset;
        }
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0; left: -80%;
            width: 60%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.09), transparent);
            transform: skewX(-20deg);
            transition: left 0.5s ease;
        }
        .btn-submit:hover::before { left: 130%; }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow:
                0 8px 30px rgba(196,26,26,0.4),
                0 2px 0 rgba(255,255,255,0.07) inset;
        }
        .btn-submit:active { transform: translateY(0); box-shadow: 0 3px 14px rgba(196,26,26,0.22); }

        /* Terms note */
        .terms-note {
            font-size: 11px;
            color: rgba(245,220,220,0.22);
            text-align: center;
            margin-top: 12px;
            line-height: 1.6;
            animation: fadeIn 0.6s 0.6s both;
        }

        .form-footer {
            text-align: center;
            margin-top: 18px;
            font-size: 12.5px;
            color: rgba(245,220,220,0.3);
            animation: fadeIn 0.6s 0.65s both;
        }
        .form-footer a {
            color: var(--gold-accent);
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .form-footer a:hover { color: #e0b85a; }

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
            Bergabunglah<br>dengan<br><em>Velodrome</em>
        </h2>
        <p class="left-desc">
            Daftar gratis untuk mulai menyewa fasilitas arena olahraga bertaraf internasional secara online.
        </p>
        <div class="stats-row">
            <div class="stat-block">
                <div class="stat-value">2</div>
                <div class="stat-label">Arena</div>
            </div>
            <div class="stat-block">
                <div class="stat-value">24/7</div>
                <div class="stat-label">Online</div>
            </div>
            <div class="stat-block">
                <div class="stat-value">UCI</div>
                <div class="stat-label">Standar</div>
            </div>
        </div>
        <div class="feature-list">
            <div class="feature-item">
                <div class="feature-dot"><i class="fa-solid fa-bolt-lightning"></i></div>
                <span class="feature-text">Pendaftaran cepat, langsung aktif</span>
            </div>
            <div class="feature-item">
                <div class="feature-dot"><i class="fa-solid fa-shield-halved"></i></div>
                <span class="feature-text">Data Anda terlindungi sepenuhnya</span>
            </div>
        </div>
        <div class="left-bottom-line"></div>
    </div>

    <!-- Right panel -->
    <div class="right-panel">
        <div class="form-card">

            <div class="mobile-brand">
                <div class="mobile-brand-icon">
                    <i class="fa-solid fa-person-biking"></i>
                </div>
                <span class="mobile-brand-name">Velodrome Diponegoro</span>
            </div>

            <h1 class="form-heading">Buat Akun Baru</h1>
            <div class="gold-line"></div>
            <p class="form-subheading">Isi data berikut untuk mendaftar</p>

            <?php if ($errors): ?>
            <div class="alert-error">
                <div class="alert-error-header">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    Terdapat kesalahan pada formulir
                </div>
                <ul>
                    <?php foreach ($errors as $e): ?>
                    <li><?= sanitize($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST">
                <!-- Row 1: Username + Email -->
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fa-solid fa-at"></i></span>
                            <input type="text"
                                   name="username"
                                   value="<?= sanitize($old['username']) ?>"
                                   class="form-input"
                                   placeholder="min. 4 karakter"
                                   required
                                   autofocus
                                   autocomplete="username">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email"
                                   name="email"
                                   value="<?= sanitize($old['email']) ?>"
                                   class="form-input"
                                   placeholder="nama@email.com"
                                   required
                                   autocomplete="email">
                        </div>
                    </div>
                </div>

                <!-- Row 2: Nama + Telepon -->
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                            <input type="text"
                                   name="nama_lengkap"
                                   value="<?= sanitize($old['nama_lengkap']) ?>"
                                   class="form-input"
                                   placeholder="Nama Anda"
                                   required
                                   autocomplete="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Telepon</label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fa-solid fa-phone"></i></span>
                            <input type="text"
                                   name="no_telepon"
                                   value="<?= sanitize($old['no_telepon']) ?>"
                                   class="form-input"
                                   placeholder="08xxxxxxxxxx"
                                   autocomplete="tel">
                        </div>
                    </div>
                </div>

                <!-- Row 3: Password + Konfirmasi -->
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input type="password"
                                   name="password"
                                   id="pw"
                                   class="form-input"
                                   placeholder="Min. 6 karakter"
                                   required
                                   autocomplete="new-password">
                            <button type="button" class="pw-toggle" id="pwToggle1" title="Tampilkan password">
                                <i class="fa-solid fa-eye" id="pwToggleIcon1"></i>
                            </button>
                        </div>
                        <div class="strength-bar" id="strengthBar">
                            <div class="strength-seg" id="seg1"></div>
                            <div class="strength-seg" id="seg2"></div>
                            <div class="strength-seg" id="seg3"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fa-solid fa-lock-open"></i></span>
                            <input type="password"
                                   name="password_confirm"
                                   id="pw2"
                                   class="form-input"
                                   placeholder="Ulangi password"
                                   required
                                   autocomplete="new-password">
                            <button type="button" class="pw-toggle" id="pwToggle2" title="Tampilkan password">
                                <i class="fa-solid fa-eye" id="pwToggleIcon2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-user-plus" style="font-size:11px;"></i>
                    Daftar Sekarang
                </button>
            </form>

            <p class="terms-note">
                Dengan mendaftar, Anda menyetujui syarat dan ketentuan penggunaan layanan arena.
            </p>

            <p class="form-footer">
                Sudah punya akun?
                <a href="<?= BASE_URL ?>/auth/login.php">Masuk di sini</a>
            </p>
        </div>
    </div>

    <script>
        // Password visibility toggle
        (function () {
            function makeToggle(inputId, btnId, iconId) {
                var input = document.getElementById(inputId);
                var btn   = document.getElementById(btnId);
                var icon  = document.getElementById(iconId);
                if (!btn) return;
                btn.addEventListener('click', function () {
                    var isPass = input.type === 'password';
                    input.type = isPass ? 'text' : 'password';
                    icon.className = isPass ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                });
            }
            makeToggle('pw',  'pwToggle1', 'pwToggleIcon1');
            makeToggle('pw2', 'pwToggle2', 'pwToggleIcon2');
        })();

        // Password strength meter
        (function () {
            var pw   = document.getElementById('pw');
            var segs = [
                document.getElementById('seg1'),
                document.getElementById('seg2'),
                document.getElementById('seg3')
            ];
            if (!pw) return;

            function getStrength(val) {
                if (val.length === 0) return 0;
                if (val.length < 6)   return 1;
                var score = 0;
                if (val.length >= 8)                   score++;
                if (/[A-Z]/.test(val))                 score++;
                if (/[0-9]/.test(val))                 score++;
                if (/[^A-Za-z0-9]/.test(val))          score++;
                if (score <= 1) return 1;
                if (score <= 3) return 2;
                return 3;
            }

            var classes = ['active-weak', 'active-medium', 'active-strong'];

            pw.addEventListener('input', function () {
                var level = getStrength(pw.value);
                segs.forEach(function (seg, i) {
                    seg.className = 'strength-seg';
                    if (i < level) {
                        seg.classList.add(classes[level - 1]);
                    }
                });
            });
        })();
    </script>
</body>
</html>