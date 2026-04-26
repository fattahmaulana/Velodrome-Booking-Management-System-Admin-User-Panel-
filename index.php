<?php
$pageTitle = 'Velodrome Diponegoro — Arena Olahraga Bertaraf Internasional';
$currentPage = 'home';
require_once __DIR__ . '/config/koneksi.php';

$arenas = $pdo->query("
    SELECT a.*, 
           (SELECT MIN(t.harga) FROM tarif t WHERE t.arena_id = a.id) as harga_mulai
    FROM arena a ORDER BY a.id
")->fetchAll();

$artikelTerbaru = $pdo->query("SELECT * FROM artikel ORDER BY created_at DESC LIMIT 3")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero relative w-full min-h-[100dvh] -mt-20 pt-28 flex flex-col justify-center overflow-hidden bg-surface-950 pb-16">
    
    <div class="hidden md:block absolute inset-y-0 right-0 w-[55%] z-0 pointer-events-none">
        <script type="module" src="https://unpkg.com/@splinetool/viewer@1.12.88/build/spline-viewer.js"></script>
        <spline-viewer url="https://prod.spline.design/HzIPSoYV3nLhuMXq/scene.splinecode" style="display: block; width: 100%; height: 100%;"></spline-viewer>
    </div>

    <div class="hidden md:block absolute inset-0 z-[5] bg-gradient-to-r from-surface-950 via-surface-950/80 to-transparent pointer-events-none"></div>
    <div class="md:hidden absolute inset-0 z-[5] bg-surface-950 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-surface-800 to-surface-950 pointer-events-none"></div>

    <div class="hero-content relative z-10 container mx-auto px-6 md:px-4 flex-grow flex items-center">
        <div class="max-w-2xl mt-12 md:mt-0">
            <div class="hero-badge reveal bg-surface-900/60 backdrop-blur-md border border-surface-700/50 text-white px-4 py-2 rounded-full inline-flex items-center gap-2 text-sm mb-6 shadow-lg">
                <i class="fa-solid fa-circle text-green-400 text-[10px] animate-pulse"></i> Booking Online Tersedia
            </div>
            
            <h1 class="reveal reveal-d1 text-5xl sm:text-6xl md:text-7xl font-extrabold text-white leading-tight mb-6 drop-shadow-lg">
                Arena Velodrome<br>
                <span class="text-maroon-400">Diponegoro</span>
            </h1>
            
            <p class="hero-desc reveal reveal-d2 text-surface-300 text-lg sm:text-xl mb-10 leading-relaxed max-w-xl drop-shadow-md">
                Fasilitas olahraga bertaraf internasional di Semarang. Velodrome Track standar UCI dan Lapangan Sepakbola rumput sintetis FIFA untuk latihan, kompetisi, hingga event besar.
            </p>
            
            <div class="hero-actions reveal reveal-d3 flex flex-wrap gap-4">
                <a href="#arena" class="btn px-8 py-3.5 bg-maroon-600 text-white rounded-full font-bold hover:bg-maroon-700 transition-all hover:-translate-y-1 shadow-[0_0_20px_rgba(220,38,38,0.4)]">
                    <i class="fa-solid fa-calendar-check mr-2"></i> Booking Sekarang
                </a>
                <a href="<?= BASE_URL ?>/jadwal.php" class="btn px-8 py-3.5 bg-surface-800/50 backdrop-blur-sm border border-surface-600 text-white rounded-full font-bold hover:bg-surface-700 transition-all">
                    <i class="fa-regular fa-calendar mr-2"></i> Lihat Jadwal
                </a>
            </div>
        </div>
    </div>

    <div class="hero-stats absolute bottom-0 left-0 w-full z-10 bg-surface-950/90 backdrop-blur-md border-t border-surface-800/50">
        <div class="container mx-auto px-4">
            <div class="py-5 grid grid-cols-2 md:grid-cols-4 gap-4 text-center divide-x divide-surface-800/50">
                <div><p class="text-xl sm:text-2xl font-bold text-white mb-1">250m</p><p class="text-surface-400 text-xs sm:text-sm font-medium">Lintasan UCI</p></div>
                <div><p class="text-xl sm:text-2xl font-bold text-white mb-1">3.000</p><p class="text-surface-400 text-xs sm:text-sm font-medium">Kapasitas Penonton</p></div>
                <div><p class="text-xl sm:text-2xl font-bold text-white mb-1">FIFA</p><p class="text-surface-400 text-xs sm:text-sm font-medium">Standar Lapangan</p></div>
                <div><p class="text-xl sm:text-2xl font-bold text-white mb-1">24/7</p><p class="text-surface-400 text-xs sm:text-sm font-medium">Booking Online</p></div>
            </div>
        </div>
    </div>
</section>

<section class="section py-20 bg-surface-50" id="tentang">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <p class="text-maroon-600 font-bold tracking-widest text-sm uppercase mb-3 reveal">Tentang Kami</p>
            <h2 class="text-3xl md:text-4xl font-extrabold text-surface-900 mb-6 reveal reveal-d1">Pusat Olahraga Terdepan di Jawa Tengah</h2>
            <p class="text-surface-600 text-lg leading-relaxed reveal reveal-d2">Velodrome Diponegoro menyediakan dua arena utama yang bisa disewa terpisah, didukung fasilitas lengkap dan modern untuk berbagai kebutuhan olahraga.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php
            $features = [
                ['fa-solid fa-medal', 'Standar Internasional', 'Lintasan velodrome UCI dan lapangan rumput sintetis FIFA untuk hasil terbaik.'],
                ['fa-solid fa-shield-halved', 'Aman & Terpercaya', 'Sistem booking online transparan dengan verifikasi pembayaran oleh admin.'],
                ['fa-solid fa-bolt', 'Proses Cepat', 'Cek ketersediaan, booking, dan konfirmasi dalam hitungan menit.'],
            ];
            foreach ($features as $i => [$icon, $title, $desc]):
            ?>
            <div class="bg-white p-8 rounded-2xl border border-surface-200 hover:border-maroon-200 hover:shadow-xl transition-all duration-300 reveal reveal-d<?= $i + 1 ?> group">
                <div class="w-14 h-14 bg-surface-100 text-maroon-600 rounded-xl flex items-center justify-center text-2xl mb-6 group-hover:bg-maroon-600 group-hover:text-white transition-colors">
                    <i class="<?= $icon ?>"></i>
                </div>
                <h3 class="text-xl font-bold text-surface-900 mb-3"><?= $title ?></h3>
                <p class="text-surface-600 leading-relaxed"><?= $desc ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="arena" class="section py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="mb-12">
            <p class="text-maroon-600 font-bold tracking-widest text-sm uppercase mb-3 reveal">Pilih Arena</p>
            <h2 class="text-3xl md:text-4xl font-extrabold text-surface-900 mb-4 reveal reveal-d1">Arena yang Tersedia</h2>
            <p class="text-surface-600 max-w-2xl text-lg reveal reveal-d2">Pilih arena sesuai kebutuhan kegiatan Anda. Setiap arena memiliki karakter dan fasilitas berbeda.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($arenas as $i => $a):
                $covers = [
                    'https://images.unsplash.com/photo-1534318542-7e47f2c05e5c?w=600&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=600&h=400&fit=crop',
                ];
                $coverImg = !empty($a['foto_cover'])
                ? BASE_URL .'/assets/uploads/' . $a['foto_cover'] : ($covers[$i] ?? $covers[0]);
                ?>
            <div class="bg-white rounded-3xl overflow-hidden border border-surface-200 hover:shadow-2xl hover:shadow-maroon-900/5 transition-all duration-300 group flex flex-col reveal reveal-d<?= min($i + 1, 3) ?>">
                <div class="relative h-60 overflow-hidden">
                    <img src="<?= $coverImg ?>" alt="<?= sanitize($a['nama_arena']) ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
                    <div class="absolute inset-0 bg-gradient-to-t from-surface-900/90 via-surface-900/20 to-transparent"></div>
                    <span class="absolute bottom-4 left-4 bg-white/20 backdrop-blur-md text-white text-xs font-bold px-3 py-1.5 rounded-full border border-white/20">
                        <i class="fa-solid fa-users mr-1"></i> Max <?= number_format($a['kapasitas_max']) ?> orang
                    </span>
                </div>
                <div class="p-6 flex-grow flex flex-col">
                    <h3 class="text-2xl font-bold text-surface-900 mb-2"><?= sanitize($a['nama_arena']) ?></h3>
                    <p class="text-sm text-surface-500 mb-4 flex items-center gap-2 font-medium">
                        <i class="fa-solid fa-location-dot text-maroon-500"></i>
                        <?= sanitize($a['lokasi']) ?>
                    </p>
                    <p class="text-surface-600 text-sm mb-6 line-clamp-3 flex-grow"><?= sanitize(substr($a['deskripsi'], 0, 150)) ?>...</p>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-surface-100">
                        <div>
                            <p class="text-xs text-surface-400 font-semibold uppercase tracking-wide">Mulai dari</p>
                            <p class="text-lg font-extrabold text-maroon-600"><?= $a['harga_mulai'] ? formatRupiah($a['harga_mulai']) : '-' ?><span class="text-xs font-normal text-surface-500">/jam</span></p>
                        </div>
                        <a href="<?= BASE_URL ?>/penyewa/booking.php?arena_id=<?= $a['id'] ?>" class="px-5 py-2.5 bg-maroon-50 text-maroon-700 hover:bg-maroon-600 hover:text-white rounded-xl text-sm font-bold transition-colors">
                            Pesan <i class="fa-solid fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="bg-gradient-to-br from-surface-900 to-surface-950 text-white rounded-3xl p-8 flex flex-col justify-between shadow-xl reveal">
                <div>
                    <div class="w-14 h-14 bg-maroon-500/20 text-maroon-400 rounded-2xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-solid fa-calendar-star"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Sewa untuk Event</h3>
                    <p class="text-surface-300 text-sm mb-6 leading-relaxed">Sewa arena selama 3 hari penuh termasuk persiapan dan pembongkaran untuk turnamen atau kompetisi besar.</p>
                    <ul class="space-y-3 text-sm text-surface-200 font-medium">
                        <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-maroon-400"></i> Blokir arena 3 hari penuh</li>
                        <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-maroon-400"></i> Termasuk H-1 dan H+1</li>
                        <li class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-maroon-400"></i> Fasilitas lengkap tersedia</li>
                    </ul>
                </div>
                <a href="<?= BASE_URL ?>/penyewa/booking.php" class="mt-8 px-6 py-3.5 bg-white text-surface-900 rounded-xl text-sm font-bold hover:bg-maroon-50 transition-colors text-center shadow-lg">
                    Ajukan Event <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($artikelTerbaru)): ?>
<section class="section py-20 bg-surface-50" id="artikel">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 md:mb-12 gap-4">
            <div>
                <p class="text-maroon-600 font-bold tracking-widest text-sm uppercase mb-3 reveal">Berita & Info</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-surface-900 reveal reveal-d1">Artikel Terbaru</h2>
            </div>
            <a href="<?= BASE_URL ?>/artikel.php" class="text-maroon-600 font-bold hover:text-maroon-800 transition-colors flex items-center gap-2 reveal md:pb-1">
                Lihat Semua Artikel <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 md:gap-8">
            <?php foreach ($artikelTerbaru as $i => $art): ?>
            <a href="<?= BASE_URL ?>/artikel.php?slug=<?= $art['slug'] ?>" class="bg-white rounded-3xl overflow-hidden border border-surface-200 hover:shadow-xl transition-all duration-300 group reveal reveal-d<?= min($i + 1, 3) ?> flex flex-col h-full">
                <div class="h-48 md:h-52 overflow-hidden bg-surface-100 relative shrink-0">
                    <?php if ($art['foto_thumbnail']): ?>
                    <img src="<?= BASE_URL ?>/assets/uploads/<?= $art['foto_thumbnail'] ?>" loading="lazy" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-surface-300 text-4xl"><i class="fa-solid fa-newspaper"></i></div>
                    <?php endif; ?>
                </div>
                <div class="p-5 md:p-6 flex flex-col flex-grow justify-between">
                    <div>
                        <p class="text-xs text-surface-400 font-semibold tracking-wide uppercase mb-3 flex items-center gap-2">
                            <i class="fa-regular fa-clock"></i> <?= formatTgl($art['created_at']) ?>
                        </p>
                        <h3 class="text-lg md:text-xl font-bold text-surface-900 group-hover:text-maroon-600 transition-colors line-clamp-3 leading-snug"><?= sanitize($art['judul']) ?></h3>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-32 relative overflow-hidden flex items-center justify-center bg-[#0a0a0a]">
    
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_100%_150%_at_50%_-20%,_#334155_0%,_#0f172a_50%,_#0a0a0a_100%)]"></div>
    
    <div class="absolute top-[-10%] left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-white opacity-[0.05] blur-[80px] rounded-full pointer-events-none"></div>
    
    <div class="container mx-auto px-4 relative z-10 text-center">
        <div class="max-w-3xl mx-auto reveal">
            <h2 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white mb-6 tracking-tight drop-shadow-md">Siap Booking Arena?</h2>
            <p class="text-surface-300 mb-12 text-lg md:text-xl leading-relaxed max-w-2xl mx-auto font-light drop-shadow-sm">Daftar gratis, pilih arena yang tersedia, dan pesan dalam hitungan menit. Pengalaman sewa arena tanpa ribet.</p>
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="<?= BASE_URL ?>/auth/register.php" class="w-full sm:w-auto px-10 py-4 bg-white text-surface-950 rounded-full font-bold hover:bg-surface-200 shadow-[0_0_30px_rgba(255,255,255,0.15)] transition-all hover:-translate-y-1">Mulai Daftar Sekarang</a>
                <a href="<?= BASE_URL ?>/jadwal.php" class="w-full sm:w-auto px-10 py-4 border border-surface-400/50 text-white rounded-full font-bold hover:bg-surface-800/50 hover:border-surface-300 backdrop-blur-sm transition-all">Lihat Jadwal Terkini</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>