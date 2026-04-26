<?php $currentPage = $currentPage ?? ''; ?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= $pageTitle ?? 'Admin - Velodrome' ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{maroon:{50:'#fef2f2',100:'#fee2e2',200:'#fecaca',300:'#fca5a5',400:'#f87171',500:'#dc2626',600:'#b91c1c',700:'#991b1b',800:'#7f1d1d',900:'#581c1c',950:'#3b0d0d'},surface:{50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a'}},fontFamily:{sans:['Inter','system-ui','sans-serif']}}}}</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{font-family:'Inter',system-ui,sans-serif}body{background:#f8fafc}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:9px}
.sidebar-link{transition:all .15s ease}
.sidebar-link:hover{background:rgba(153,27,27,.06);color:#991b1b}
.sidebar-link.active{background:rgba(153,27,27,.08);color:#991b1b;border-right:3px solid #991b1b}
@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}.fade-in{animation:fadeIn .35s ease-out both}
.badge{padding:2px 10px;border-radius:9999px;font-size:.7rem;font-weight:500}
.badge-pending{background:#fef3c7;color:#92400e}.badge-confirmed{background:#d1fae5;color:#065f46}
.badge-rejected{background:#fee2e2;color:#991b1b}.badge-completed{background:#dbeafe;color:#1e40af}
</style>
</head>
<body class="antialiased text-surface-700">
<div class="flex min-h-screen">
<!-- Sidebar -->
<aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-30 w-60 bg-white border-r border-surface-100 flex flex-col transform transition-transform duration-200 lg:translate-x-0 -translate-x-full">
    <div class="h-16 flex items-center px-5 border-b border-surface-50">
        <a href="<?= BASE_URL ?>/admin/" class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-maroon-700 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-person-biking text-white text-xs"></i>
            </div>
            <div class="leading-tight">
                <span class="text-xs font-bold text-surface-900">Velodrome</span>
                <span class="block text-[9px] text-surface-400 uppercase tracking-widest">Admin Panel</span>
            </div>
        </a>
    </div>
    <nav class="flex-1 py-5 px-3 space-y-0.5 overflow-y-auto">
        <p class="px-3 mb-2 text-[9px] font-semibold text-surface-400 uppercase tracking-[.15em]">Menu Utama</p>
        <a href="<?= BASE_URL ?>/admin/" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] <?= $currentPage==='dashboard'?'active':'text-surface-600'?>"><i class="fa-solid fa-chart-pie w-4 text-center text-[11px]"></i>Dashboard</a>
        <a href="<?= BASE_URL ?>/admin/arena.php" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] <?= $currentPage==='arena'?'active':'text-surface-600'?>"><i class="fa-solid fa-building w-4 text-center text-[11px]"></i>Kelola Arena</a>
        <a href="<?= BASE_URL ?>/admin/tarif.php" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] <?= $currentPage==='tarif'?'active':'text-surface-600'?>"><i class="fa-solid fa-tags w-4 text-center text-[11px]"></i>Kelola Tarif</a>
        <a href="<?= BASE_URL ?>/admin/artikel.php" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] <?= $currentPage==='artikel'?'active':'text-surface-600'?>"><i class="fa-solid fa-newspaper w-4 text-center text-[11px]"></i>Kelola Artikel</a>
        <p class="px-3 mt-5 mb-2 text-[9px] font-semibold text-surface-400 uppercase tracking-[.15em]">Transaksi</p>
        <a href="<?= BASE_URL ?>/admin/booking.php" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] <?= $currentPage==='booking'?'active':'text-surface-600'?>"><i class="fa-solid fa-clipboard-check w-4 text-center text-[11px]"></i>Verifikasi Booking</a>
        <a href="<?= BASE_URL ?>/admin/laporan.php" class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] <?= $currentPage==='laporan'?'active':'text-surface-600'?>"><i class="fa-solid fa-chart-bar w-4 text-center text-[11px]"></i>Laporan</a>
    </nav>
    <div class="p-4 border-t border-surface-50">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-maroon-50 rounded-full flex items-center justify-center"><i class="fa-solid fa-user-shield text-maroon-700 text-[10px]"></i></div>
            <div class="flex-1 min-w-0"><p class="text-xs font-medium text-surface-900 truncate"><?= sanitize($_SESSION['nama_lengkap']??'Admin')?></p><p class="text-[10px] text-surface-400">Administrator</p></div>
            <a href="<?= BASE_URL ?>/auth/logout.php" title="Logout" class="text-surface-400 hover:text-red-500 transition-colors"><i class="fa-solid fa-right-from-bracket text-xs"></i></a>
        </div>
    </div>
</aside>
<!-- Main -->
<div class="flex-1 lg:ml-60">
<header class="h-14 bg-white/80 backdrop-blur border-b border-surface-100 flex items-center justify-between px-5 sticky top-0 z-20">
    <button id="admin-sidebar-toggle" class="lg:hidden text-surface-500 hover:text-surface-700"><i class="fa-solid fa-bars"></i></button>
    <div class="hidden sm:flex items-center gap-1.5 text-xs text-surface-400"><i class="fa-solid fa-circle text-[5px] text-emerald-500 animate-pulse"></i>Online</div>
    <div class="flex items-center gap-3">
        <a href="<?= BASE_URL ?>/" class="text-xs text-surface-400 hover:text-surface-600 transition-colors"><i class="fa-solid fa-arrow-up-right-from-square mr-1"></i>Lihat Website</a>
        <span class="text-[10px] text-surface-300"><?= date('d M Y')?></span>
    </div>
</header>
<main class="p-5 fade-in">
