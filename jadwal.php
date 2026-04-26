<?php
$pageTitle = 'Jadwal Arena - Velodrome Diponegoro';
$currentPage = 'jadwal';
require_once __DIR__ . '/config/koneksi.php';

$arenas = $pdo->query("SELECT * FROM arena ORDER BY id")->fetchAll();
$selectedArena = (int)($_GET['arena_id'] ?? ($arenas[0]['id'] ?? 0));

// Current month navigation
$month = (int)($_GET['month'] ?? date('m'));
$year  = (int)($_GET['year'] ?? date('Y'));
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$startDow = (int)date('N', $firstDay); // 1=Monday

$bulanNama = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

// Get booked dates for this month
$stmt = $pdo->prepare("
    SELECT tanggal, jenis,
           GROUP_CONCAT(CONCAT(TIME_FORMAT(waktu_mulai,'%H:%i'),'-',TIME_FORMAT(waktu_selesai,'%H:%i')) SEPARATOR ', ') as slots
    FROM jadwal
    WHERE arena_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
    GROUP BY tanggal, jenis
");
$stmt->execute([$selectedArena, $month, $year]);
$bookedData = [];
foreach ($stmt->fetchAll() as $row) {
    $bookedData[$row['tanggal']][] = $row;
}

$prevM = $month - 1; $prevY = $year; if ($prevM < 1) { $prevM = 12; $prevY--; }
$nextM = $month + 1; $nextY = $year; if ($nextM > 12) { $nextM = 1; $nextY++; }

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <p class="text-xs font-semibold text-maroon-700 uppercase tracking-widest mb-2">Ketersediaan</p>
        <h1 class="text-3xl font-bold text-surface-900">Jadwal Arena</h1>
        <p class="text-surface-500 mt-2 text-sm">Lihat ketersediaan arena berdasarkan bulan. Tanggal yang ditandai sudah memiliki jadwal.</p>
    </div>

    <!-- Arena Tabs -->
    <div class="flex gap-2 mb-6 flex-wrap">
        <?php foreach ($arenas as $ar): ?>
        <a href="?arena_id=<?= $ar['id'] ?>&month=<?= $month ?>&year=<?= $year ?>"
           class="px-4 py-2 rounded-xl text-sm font-medium transition-colors <?= $selectedArena == $ar['id'] ? 'bg-maroon-700 text-white' : 'bg-white text-surface-600 border border-surface-200 hover:bg-surface-50' ?>">
            <?= sanitize($ar['nama_arena']) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Legend -->
    <div class="bg-white rounded-2xl border border-surface-100 p-5 mb-6 flex flex-wrap gap-6 text-sm">
        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-surface-100 border border-surface-200"></span> Tersedia</span>
        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-400"></span> Terisi Sebagian (Per Jam)</span>
        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-maroon-600"></span> Full / Event</span>
    </div>

    <!-- Calendar -->
    <div class="bg-white rounded-2xl border border-surface-100 overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-surface-100">
            <a href="?arena_id=<?= $selectedArena ?>&month=<?= $prevM ?>&year=<?= $prevY ?>" class="w-9 h-9 flex items-center justify-center rounded-lg bg-surface-50 hover:bg-surface-100 text-surface-600 transition-colors">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </a>
            <h2 class="text-lg font-bold text-surface-900"><?= $bulanNama[$month] ?> <?= $year ?></h2>
            <a href="?arena_id=<?= $selectedArena ?>&month=<?= $nextM ?>&year=<?= $nextY ?>" class="w-9 h-9 flex items-center justify-center rounded-lg bg-surface-50 hover:bg-surface-100 text-surface-600 transition-colors">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </a>
        </div>

        <!-- Days Header -->
        <div class="grid grid-cols-7 border-b border-surface-50">
            <?php foreach (['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $d): ?>
            <div class="py-2.5 text-center text-[11px] font-semibold text-surface-400 uppercase"><?= $d ?></div>
            <?php endforeach; ?>
        </div>

        <!-- Calendar Grid -->
        <div class="grid grid-cols-7">
            <?php
            // Empty cells before first day
            for ($i = 1; $i < $startDow; $i++):
            ?>
            <div class="min-h-[80px] border-b border-r border-surface-50 bg-surface-25"></div>
            <?php endfor; ?>

            <?php for ($day = 1; $day <= $daysInMonth; $day++):
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $isToday = $dateStr === date('Y-m-d');
                $isPast  = $dateStr < date('Y-m-d');
                $entries = $bookedData[$dateStr] ?? [];
                $hasEvent = false;
                $hasPerJam = false;
                foreach ($entries as $e) {
                    if ($e['jenis'] === 'event') $hasEvent = true;
                    if ($e['jenis'] === 'per_jam') $hasPerJam = true;
                }
            ?>
            <div class="min-h-[80px] border-b border-r border-surface-50 p-1.5 <?= $isPast ? 'bg-surface-50/50' : '' ?> <?= $isToday ? 'bg-maroon-50/40' : '' ?>">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium <?= $isToday ? 'text-maroon-700 font-bold' : ($isPast ? 'text-surface-300' : 'text-surface-700') ?>"><?= $day ?></span>
                    <?php if ($hasEvent): ?>
                    <span class="w-2 h-2 rounded-full bg-maroon-600"></span>
                    <?php elseif ($hasPerJam): ?>
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <?php endif; ?>
                </div>
                <?php if ($hasEvent): ?>
                <span class="block text-[9px] text-maroon-700 font-medium bg-maroon-50 px-1.5 py-0.5 rounded">Event</span>
                <?php elseif ($hasPerJam): ?>
                <?php foreach ($entries as $e): ?>
                <span class="block text-[9px] text-amber-700 truncate" title="<?= sanitize($e['slots']) ?>"><?= sanitize($e['slots']) ?></span>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php
                // Close row
                $cellIndex = $startDow - 1 + $day;
            endfor;

            // Fill remaining cells
            $remaining = 7 - ($cellIndex % 7);
            if ($remaining < 7):
                for ($i = 0; $i < $remaining; $i++):
            ?>
            <div class="min-h-[80px] border-b border-r border-surface-50 bg-surface-25"></div>
            <?php endfor; endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
