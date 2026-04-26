-- ============================================================
-- SISTEM INFORMASI PENYEWAAN ARENA VELODROME DIPONEGORO
-- Database: velodrome_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS velodrome_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE velodrome_db;

-- 1. USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(20) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    role ENUM('admin','penyewa') NOT NULL DEFAULT 'penyewa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. ARENA
CREATE TABLE arena (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_arena VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    fasilitas TEXT,
    kapasitas_max INT NOT NULL DEFAULT 0,
    foto_cover VARCHAR(255) DEFAULT NULL,
    lokasi VARCHAR(200) DEFAULT 'Jl. Diponegoro, Semarang'
) ENGINE=InnoDB;

-- 3. ARTIKEL
CREATE TABLE artikel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    konten TEXT NOT NULL,
    foto_thumbnail VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. TARIF
CREATE TABLE tarif (
    id INT AUTO_INCREMENT PRIMARY KEY,
    arena_id INT NOT NULL,
    jenis_sewa ENUM('per_jam','event') NOT NULL,
    harga DECIMAL(12,2) NOT NULL,
    keterangan VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (arena_id) REFERENCES arena(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. BOOKING
CREATE TABLE booking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    penyewa_id INT NOT NULL,
    arena_id INT NOT NULL,
    tarif_id INT NOT NULL,
    kode_booking VARCHAR(20) NOT NULL UNIQUE,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    waktu_mulai TIME DEFAULT NULL,
    waktu_selesai TIME DEFAULT NULL,
    total_biaya DECIMAL(12,2) NOT NULL,
    status ENUM('pending','confirmed','rejected','completed') NOT NULL DEFAULT 'pending',
    file_bukti_bayar VARCHAR(255) DEFAULT NULL,
    catatan TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penyewa_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (arena_id) REFERENCES arena(id) ON DELETE CASCADE,
    FOREIGN KEY (tarif_id) REFERENCES tarif(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. JADWAL (Blokir)
CREATE TABLE jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    arena_id INT NOT NULL,
    booking_id INT DEFAULT NULL,
    tanggal DATE NOT NULL,
    waktu_mulai TIME DEFAULT NULL,
    waktu_selesai TIME DEFAULT NULL,
    jenis ENUM('per_jam','event') NOT NULL DEFAULT 'per_jam',
    status ENUM('booked','blocked') NOT NULL DEFAULT 'booked',
    FOREIGN KEY (arena_id) REFERENCES arena(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin (password: admin123)
INSERT INTO users (username, password_hash, email, nama_lengkap, role) VALUES
('admin', '$2y$10$placeholder_will_be_updated', 'admin@velodrome.id', 'Administrator Velodrome', 'admin');

-- Arena
INSERT INTO arena (nama_arena, deskripsi, fasilitas, kapasitas_max, foto_cover, lokasi) VALUES
(
  'Velodrome Track',
  'Arena balap sepeda bertaraf internasional dengan lintasan oval 250 meter yang memenuhi standar UCI. Cocok untuk latihan rutin, kompetisi balap sepeda, dan event olahraga berskala besar.',
  'Lintasan oval 250m standar UCI, Tribun penonton 3.000 kursi, Ruang ganti & locker, Scoring system digital, Area parkir luas, Lighting LED standar kompetisi',
  3000,
  NULL,
  'Kompleks Velodrome Diponegoro, Semarang'
),
(
  'Lapangan Sepakbola',
  'Lapangan sepakbola rumput sintetis berstandar FIFA yang terletak di dalam kompleks Velodrome Diponegoro. Ideal untuk pertandingan persahabatan, latihan klub, dan turnamen futsal/sepakbola.',
  'Rumput sintetis standar FIFA, Gawang standar, Bench pemain cadangan, Lampu sorot malam hari, Area pemanasan, Ruang ganti terpisah',
  500,
  NULL,
  'Kompleks Velodrome Diponegoro, Semarang'
);

-- Tarif
INSERT INTO tarif (arena_id, jenis_sewa, harga, keterangan) VALUES
(1, 'per_jam', 750000.00, 'Sewa per jam lintasan velodrome untuk latihan / komunitas'),
(1, 'event',  15000000.00, 'Sewa event velodrome 3 hari penuh (termasuk persiapan & bongkar)'),
(2, 'per_jam', 500000.00,  'Sewa per jam lapangan sepakbola'),
(2, 'event',  8000000.00,  'Sewa event lapangan sepakbola 3 hari penuh');

-- Artikel
INSERT INTO artikel (judul, slug, konten, foto_thumbnail) VALUES
(
  'Velodrome Diponegoro Resmi Dibuka untuk Publik',
  'velodrome-diponegoro-resmi-dibuka',
  'Arena Velodrome Diponegoro kini secara resmi membuka layanan penyewaan untuk masyarakat umum. Fasilitas bertaraf internasional ini dapat digunakan untuk berbagai kegiatan olahraga, mulai dari latihan rutin hingga kompetisi berskala nasional.\n\nDengan lintasan oval 250 meter yang memenuhi standar UCI dan lapangan sepakbola rumput sintetis berstandar FIFA, Velodrome Diponegoro siap menjadi pusat olahraga terdepan di Jawa Tengah.\n\nProses penyewaan kini bisa dilakukan secara online melalui website resmi, memudahkan penyewa untuk memeriksa ketersediaan jadwal, melakukan pemesanan, dan mengunggah bukti pembayaran dengan cepat dan transparan.',
  NULL
),
(
  'Panduan Lengkap Penyewaan Arena Velodrome',
  'panduan-lengkap-penyewaan',
  'Berikut panduan lengkap untuk menyewa arena di Velodrome Diponegoro:\n\n1. Daftar atau Login ke akun Anda.\n2. Pilih arena yang ingin disewa (Velodrome Track atau Lapangan Sepakbola).\n3. Pilih jenis sewa: per jam untuk latihan singkat, atau event untuk acara 3 hari penuh.\n4. Sistem akan mengecek ketersediaan otomatis.\n5. Lakukan pembayaran dan upload bukti transfer.\n6. Tunggu konfirmasi dari admin.\n7. Datang sesuai jadwal dan nikmati fasilitas kami!',
  NULL
),
(
  'Turnamen Sepeda Nasional Segera Digelar',
  'turnamen-sepeda-nasional-2026',
  'Kabar gembira bagi pecinta olahraga balap sepeda! Velodrome Diponegoro akan menjadi tuan rumah Turnamen Sepeda Nasional 2026 yang akan diikuti oleh atlet-atlet terbaik dari seluruh Indonesia.\n\nTurnamen ini direncanakan akan berlangsung selama 3 hari penuh dengan berbagai kategori perlombaan. Fasilitas lighting LED dan scoring system digital akan memastikan kompetisi berjalan profesional.',
  NULL
);
