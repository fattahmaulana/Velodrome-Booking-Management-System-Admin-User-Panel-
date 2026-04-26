<?php
$pageTitle = 'Artikel - Velodrome Diponegoro';
$currentPage = 'artikel';
require_once __DIR__ . '/config/koneksi.php';

// Single article view
if (isset($_GET['slug'])) {
    $stmt = $pdo->prepare("SELECT * FROM artikel WHERE slug = ?");
    $stmt->execute([trim($_GET['slug'])]);
    $article = $stmt->fetch();
    if (!$article) { header('Location: ' . BASE_URL . '/artikel.php'); exit; }
    $pageTitle = sanitize($article['judul']) . ' - Velodrome Diponegoro';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
        <a href="<?= BASE_URL ?>/artikel.php" class="text-sm text-maroon-700 font-medium hover:text-maroon-800 mb-6 inline-block"><i class="fa-solid fa-arrow-left mr-1.5 text-xs"></i>Kembali</a>
        <?php if ($article['foto_thumbnail']): ?>
        <img src="<?= BASE_URL ?>/assets/uploads/<?= $article['foto_thumbnail'] ?>" class="w-full h-64 object-cover rounded-2xl mb-6" alt="">
        <?php endif; ?>
        <p class="text-xs text-surface-400 mb-2"><?= formatTgl($article['created_at']) ?></p>
        <h1 class="text-3xl font-bold text-surface-900 mb-6 leading-tight"><?= sanitize($article['judul']) ?></h1>
        <div class="prose prose-sm text-surface-600 leading-relaxed whitespace-pre-line"><?= sanitize($article['konten']) ?></div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Article list
$articles = $pdo->query("SELECT * FROM artikel ORDER BY created_at DESC")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-10">
        <p class="text-xs font-semibold text-maroon-700 uppercase tracking-widest mb-2">Berita & Informasi</p>
        <h1 class="text-3xl font-bold text-surface-900">Artikel</h1>
    </div>

    <?php if (empty($articles)): ?>
    <div class="text-center py-16"><i class="fa-solid fa-newspaper text-4xl text-surface-200 mb-3"></i><p class="text-surface-400">Belum ada artikel.</p></div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($articles as $art): ?>
        <a href="<?= BASE_URL ?>/artikel.php?slug=<?= $art['slug'] ?>" class="group block">
            <div class="h-48 bg-surface-100 rounded-2xl mb-4 overflow-hidden">
                <?php if ($art['foto_thumbnail']): ?>
                <img src="<?= BASE_URL ?>/assets/uploads/<?= $art['foto_thumbnail'] ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" alt="">
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center"><i class="fa-solid fa-newspaper text-3xl text-surface-300"></i></div>
                <?php endif; ?>
            </div>
            <p class="text-xs text-surface-400 mb-1.5"><?= formatTgl($art['created_at']) ?></p>
            <h3 class="text-lg font-semibold text-surface-900 group-hover:text-maroon-700 transition-colors leading-snug mb-2"><?= sanitize($art['judul']) ?></h3>
            <p class="text-sm text-surface-500 line-clamp-2"><?= sanitize(substr($art['konten'], 0, 150)) ?>...</p>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
