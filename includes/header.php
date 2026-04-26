<?php $currentPage = $currentPage ?? ''; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Informasi Penyewaan Arena Velodrome Diponegoro Semarang - Booking arena olahraga bertaraf internasional.">
    <title><?= $pageTitle ?? 'Velodrome Diponegoro' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    maroon:  { 50:'#fef2f2',100:'#fee2e2',200:'#fecaca',300:'#fca5a5',400:'#f87171',500:'#dc2626',600:'#b91c1c',700:'#991b1b',800:'#7f1d1d',900:'#581c1c',950:'#3b0d0d' },
                    surface: { 50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a',950:'#020617' }
                },
                fontFamily: { sans: ['Inter','system-ui','-apple-system','sans-serif'] }
            }
        }
    }
    </script>
</head>
<body>

<nav id="main-nav" class="navbar">
    <div class="navbar-inner">
        <a href="<?= BASE_URL ?>/" class="navbar-brand">
            <div class="navbar-brand-icon">
                <i class="fa-solid fa-person-biking"></i>
            </div>
            <div class="navbar-brand-text">
                <span>Velodrome</span>
                <span>Diponegoro</span>
            </div>
        </a>

        <div class="nav-links">
            <?php
            $navLinks = [
                ['/',             'Home',    'home'],
                ['/artikel.php',  'Artikel', 'artikel'],
                ['/jadwal.php',   'Jadwal',  'jadwal'],
                ['/penyewa/booking.php', 'Booking', 'booking'],
                ['/kontak.php',   'Kontak',  'kontak'],
            ];
            foreach ($navLinks as [$href, $label, $key]):
            ?>
            <a href="<?= BASE_URL . $href ?>"
               class="nav-link <?= $currentPage === $key ? 'active' : '' ?>">
               <?= $label ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="nav-right">
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                <a href="<?= BASE_URL ?>/admin/" class="nav-btn-ghost">
                    <i class="fa-solid fa-gauge-high" style="margin-right:4px;font-size:11px"></i> Admin
                </a>
                <?php else: ?>
                <a href="<?= BASE_URL ?>/penyewa/" class="nav-btn-ghost">
                    <i class="fa-solid fa-user" style="margin-right:4px;font-size:11px"></i> Dashboard
                </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/auth/logout.php" class="nav-btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/auth/login.php" class="nav-btn-ghost">Masuk</a>
                <a href="<?= BASE_URL ?>/auth/register.php" class="nav-btn-primary">Daftar</a>
            <?php endif; ?>

            <button id="mobile-toggle" class="mobile-toggle">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="mobile-menu">
        <a href="<?= BASE_URL ?>/"                    class="<?= $currentPage === 'home'    ? 'active' : '' ?>">Home</a>
        <a href="<?= BASE_URL ?>/artikel.php"         class="<?= $currentPage === 'artikel' ? 'active' : '' ?>">Artikel</a>
        <a href="<?= BASE_URL ?>/jadwal.php"          class="<?= $currentPage === 'jadwal'  ? 'active' : '' ?>">Jadwal</a>
        <a href="<?= BASE_URL ?>/penyewa/booking.php" class="<?= $currentPage === 'booking' ? 'active' : '' ?>">Booking</a>
        <a href="<?= BASE_URL ?>/kontak.php"          class="<?= $currentPage === 'kontak'  ? 'active' : '' ?>">Kontak</a>
    </div>
</nav>
<div class="navbar-spacer h-20 w-full bg-transparent"></div>