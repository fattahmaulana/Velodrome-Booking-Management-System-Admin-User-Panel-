<?php
$pageTitle   = 'Kontak & Informasi - Velodrome Diponegoro';
$currentPage = 'kontak';
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400;1,9..144,600&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap');

/* ==========================================================================
   Tokens
   ========================================================================== */
:root {
    --red:      #991b1b;
    --red-pale: #fff1f1;
    --ink:      #0c0f14;
    --ink-2:    #374151;
    --ink-3:    #6b7280;
    --cream:    #faf9f7;
    --white:    #ffffff;
    --line:     #e5e7eb;
    --r:        16px;
    --r-lg:     22px;
}
.kp * { box-sizing: border-box; }
.kp   { font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--ink); }

/* ==========================================================================
   Keyframes
   ========================================================================== */
@keyframes kUp     { from { opacity:0; transform:translateY(22px); } to { opacity:1; transform:none; } }
@keyframes kIn     { from { opacity:0; } to { opacity:1; } }
@keyframes kPulse  { 0%,100%{ transform:scale(1); opacity:.5; } 50%{ transform:scale(1.6); opacity:0; } }
@keyframes kShim   { from{ left:-70%; } to{ left:130%; } }

/* ==========================================================================
   HERO & MESH GRADIENT
   ========================================================================== */
.kp-hero {
    position: relative;
    background-color: #0c0f14;
}

/* Layer 1: Ambient Mesh Gradient dengan Blur dan Animasi */
.kp-hero::before {
    content: '';
    position: absolute;
    inset: -20%;
    background:
        radial-gradient(circle at 80% 20%, rgba(153, 27, 27, 0.45) 0%, transparent 45%),
        radial-gradient(circle at 10% 80%, rgba(30, 41, 59, 0.8) 0%, transparent 50%),
        radial-gradient(circle at 50% 60%, rgba(220, 38, 38, 0.15) 0%, transparent 60%);
    filter: blur(80px);
    z-index: 0;
    pointer-events: none;
    animation: meshShift 15s ease-in-out infinite alternate;
}

/* Layer 2: Fine Grid dengan efek fade di tepi (Vignette) */
.kp-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
    background-size: 48px 48px;
    -webkit-mask-image: radial-gradient(circle at 50% 50%, black 30%, transparent 90%);
    mask-image: radial-gradient(circle at 50% 50%, black 30%, transparent 90%);
    pointer-events: none;
    z-index: 1;
}

/* Animasi Pergerakan Mesh Gradient */
@keyframes meshShift {
    0% { transform: scale(1) translate(0, 0); }
    33% { transform: scale(1.05) translate(3%, 2%); }
    66% { transform: scale(1.02) translate(-2%, 4%); }
    100% { transform: scale(1) translate(-1%, -1%); }
}

/* Penyesuaian konten hero agar berada di atas efek background */
.kp-hero-inner {
    position: relative;
    z-index: 2;
    max-width: 860px;
    margin: 0 auto;
    padding: 0 32px;
    width: 100%;
}

/* Location pill */
.kp-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.11);
    border-radius: 999px;
    padding: 5px 15px;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: rgba(255,255,255,.5);
    margin-bottom: 22px;
    animation: kIn .5s .05s both;
}
.kp-pill i { color: #fca5a5; font-size: 9px; }

/* Main heading */
.kp-hero-h {
    font-family: 'Fraunces', Georgia, serif;
    font-size: clamp(3rem, 7.5vw, 5.4rem);
    font-weight: 700;
    line-height: 1.0;
    letter-spacing: -.035em;
    color: #f9fafb;
    margin-bottom: 14px;
    animation: kUp .6s .1s both;
}
.kp-hero-h em { font-style: italic; font-weight: 400; color: #fca5a5; }

/* Gold rule */
.kp-rule {
    width: 44px; height: 2px;
    background: linear-gradient(90deg, #fca5a5, transparent);
    border-radius: 2px;
    margin-bottom: 18px;
    animation: kIn .5s .25s both;
}

.kp-hero-p {
    font-size: 15.5px;
    line-height: 1.75;
    color: rgba(249,250,251,.42);
    max-width: 430px;
    margin-bottom: 28px;
    animation: kUp .6s .18s both;
}

/* CTA row */
.kp-cta { display: flex; flex-wrap: wrap; gap: 11px; animation: kUp .6s .3s both; }
.kp-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 22px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: transform .2s ease, box-shadow .2s ease;
}
.kp-btn:hover { transform: translateY(-2px); }
.kp-btn-wa {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff;
    box-shadow: 0 4px 16px rgba(34,197,94,.32);
}
.kp-btn-wa:hover { box-shadow: 0 8px 26px rgba(34,197,94,.45); }
.kp-btn-ghost {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.13);
    color: rgba(255,255,255,.65);
}
.kp-btn-ghost:hover { background: rgba(255,255,255,.11); }

/* ==========================================================================
   STATS STRIP
   ========================================================================== */
.kp-stats-wrap {
    max-width: 860px;
    margin: 48px auto 0;
    padding: 0 24px;
}
.kp-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    background: var(--line);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    overflow: hidden;
}
@media (max-width: 520px) {
    .kp-stats { grid-template-columns: 1fr; }
}
.kp-stat {
    background: var(--white);
    padding: 26px 16px;
    text-align: center;
    transition: background .2s;
}
.kp-stat:hover { background: var(--red-pale); }
.kp-stat-v {
    font-family: 'Fraunces', serif;
    font-size: 2.3rem;
    font-weight: 700;
    color: var(--red);
    line-height: 1;
    letter-spacing: -.03em;
    margin-bottom: 5px;
}
.kp-stat-l { font-size: 11.5px; color: var(--ink-3); font-weight: 500; }

/* ==========================================================================
   BODY
   ========================================================================== */
.kp-body {
    max-width: 1060px;
    margin: 0 auto;
    padding: 60px 24px 88px;
}
.kp-eye   { font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--red); margin-bottom: 7px; }
.kp-sec-h { font-family: 'Fraunces', serif; font-size: clamp(1.5rem, 3vw, 2.1rem); font-weight: 700; color: var(--ink); letter-spacing: -.025em; margin-bottom: 32px; line-height: 1.15; }

/* Card shell */
.kp-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(12,15,20,.05);
    transition: box-shadow .28s ease, transform .28s ease, border-color .28s ease;
    animation: kUp .5s ease both;
}
.kp-card:hover { box-shadow: 0 10px 36px rgba(12,15,20,.1); transform: translateY(-3px); border-color: #d1d5db; }

.kp-ch { padding: 17px 20px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; gap: 11px; }
.kp-ch-ico { width: 36px; height: 36px; border-radius: 10px; background: var(--red-pale); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.kp-ch-ico i { color: var(--red); font-size: 13px; }
.kp-ch-t  { font-size: 13.5px; font-weight: 700; color: var(--ink); letter-spacing: -.01em; }
.kp-ch-s  { font-size: 11px; color: var(--ink-3); margin-top: 1px; }
.kp-cb    { padding: 18px 20px 22px; }

/* Duo grid */
.kp-duo {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
    align-items: stretch;
}
@media (max-width: 768px) { .kp-duo { grid-template-columns: 1fr; } }

/* Map iframe */
.kp-map { display: block; width: 100%; height: 100%; min-height: 320px; border: 0; }
@media (max-width: 520px) { .kp-map { min-height: 250px; } }

/* Contact rows */
.kp-row { display: flex; align-items: flex-start; gap: 12px; padding: 13px 0; border-bottom: 1px solid #f3f4f6; transition: padding-left .18s ease; }
.kp-row:last-of-type { border-bottom: none; padding-bottom: 0; }
.kp-row:first-child  { padding-top: 0; }
.kp-row:hover        { padding-left: 4px; }
.kp-ri { width: 32px; height: 32px; border-radius: 9px; background: var(--red-pale); display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; transition: background .2s; }
.kp-row:hover .kp-ri { background: #fecaca; }
.kp-ri i { color: var(--red); font-size: 11px; }
.kp-rl  { font-size: 9.5px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--ink-3); margin-bottom: 2px; }
.kp-rv  { font-size: 13px; font-weight: 500; color: var(--ink); line-height: 1.5; }
.kp-rv a { color: var(--ink); text-decoration: none; transition: color .2s; }
.kp-rv a:hover { color: var(--red); }

/* WhatsApp button */
.kp-wa {
    margin-top: 16px;
    display: flex; align-items: center; gap: 12px;
    padding: 13px 16px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    border-radius: 13px;
    text-decoration: none; color: #fff;
    position: relative; overflow: hidden;
    transition: transform .2s ease, box-shadow .25s ease;
    box-shadow: 0 4px 16px rgba(22,163,74,.28), inset 0 1px 0 rgba(255,255,255,.14);
}
.kp-wa::before {
    content: '';
    position: absolute; top: 0; left: -70%;
    width: 55%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent);
    transform: skewX(-20deg);
    animation: kShim 3.5s 1.5s ease infinite;
}
.kp-wa:hover { transform: translateY(-2px); box-shadow: 0 8px 26px rgba(22,163,74,.38); }
.kp-wa-ic { width: 40px; height: 40px; background: rgba(255,255,255,.18); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.kp-wa-ic i { font-size: 19px; }
.kp-wa-lbl { font-size: 13.5px; font-weight: 700; margin-bottom: 1px; }
.kp-wa-sub { font-size: 10.5px; opacity: .72; }

/* Bottom grid */
.kp-bot {
    display: grid;
    grid-template-columns: 1fr 1.7fr;
    gap: 20px;
    align-items: start;
}
@media (max-width: 768px) { .kp-bot { grid-template-columns: 1fr; } }

/* Hours */
.kp-ht { width: 100%; border-collapse: collapse; }
.kp-ht tr { border-bottom: 1px solid #f3f4f6; }
.kp-ht tr:last-child { border-bottom: none; }
.kp-ht td { padding: 11px 0; font-size: 12.5px; color: var(--ink-2); vertical-align: middle; }
.kp-ht td:last-child { text-align: right; font-weight: 600; white-space: nowrap; }
.kp-b { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 9.5px; font-weight: 700; margin-left: 5px; }
.kp-b-o { background: #dcfce7; color: #15803d; }
.kp-b-c { background: #fee2e2; color: #b91c1c; }
.kp-note { margin-top: 16px; padding: 12px 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 11px; display: flex; gap: 9px; align-items: flex-start; }
.kp-note i { color: #d97706; font-size: 12px; margin-top: 1px; flex-shrink: 0; }
.kp-note p { font-size: 11.5px; color: #78350f; line-height: 1.6; margin: 0; }

/* Facility grid */
.kp-tags {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 9px;
}
@media (max-width: 480px) {
    .kp-tags { grid-template-columns: 1fr 1fr; }
}
.kp-tag {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 10px 13px;
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 11px;
    font-size: 12px;
    font-weight: 500;
    color: var(--ink-2);
    transition: background .18s, border-color .18s, transform .2s, color .18s;
    animation: kUp .4s ease both;
}
.kp-tag:hover { background: var(--red-pale); border-color: #fecaca; color: var(--red); transform: translateY(-2px); }
.kp-tag-i { width: 28px; height: 28px; border-radius: 7px; background: var(--red-pale); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background .18s; }
.kp-tag:hover .kp-tag-i { background: #fecaca; }
.kp-tag-i i { color: var(--red); font-size: 10px; }

/* Floating WA */
.kp-float {
    position: fixed; bottom: 26px; right: 26px; z-index: 200;
    width: 54px; height: 54px; border-radius: 50%;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff; display: flex; align-items: center; justify-content: center;
    text-decoration: none;
    box-shadow: 0 6px 20px rgba(22,163,74,.4);
    transition: transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .25s ease;
    animation: kUp .7s 1s cubic-bezier(.34,1.56,.64,1) both;
}
.kp-float:hover { transform: scale(1.1); box-shadow: 0 10px 30px rgba(22,163,74,.5); }
.kp-float i { font-size: 22px; }
.kp-ring { position: absolute; inset: -5px; border-radius: 50%; border: 2px solid rgba(34,197,94,.4); animation: kPulse 2.2s ease-out infinite; }
.kp-tip {
    position: absolute; right: 63px;
    background: var(--ink); color: #fff;
    font-size: 11.5px; font-weight: 600;
    padding: 5px 11px; border-radius: 7px;
    white-space: nowrap; opacity: 0; pointer-events: none;
    transform: translateX(6px); transition: opacity .2s, transform .2s;
}
.kp-tip::after { content: ''; position: absolute; top: 50%; right: -5px; transform: translateY(-50%); border: 5px solid transparent; border-left-color: var(--ink); border-right: 0; }
.kp-float:hover .kp-tip { opacity: 1; transform: translateX(0); }

/* Stagger tags */
.kp-tag:nth-child(1){animation-delay:.04s}.kp-tag:nth-child(2){animation-delay:.08s}
.kp-tag:nth-child(3){animation-delay:.12s}.kp-tag:nth-child(4){animation-delay:.16s}
.kp-tag:nth-child(5){animation-delay:.20s}.kp-tag:nth-child(6){animation-delay:.24s}
.kp-tag:nth-child(7){animation-delay:.28s}.kp-tag:nth-child(8){animation-delay:.32s}
.kp-tag:nth-child(9){animation-delay:.36s}.kp-tag:nth-child(10){animation-delay:.40s}
.kp-tag:nth-child(11){animation-delay:.44s}.kp-tag:nth-child(12){animation-delay:.48s}
</style>

<div class="kp">

<section class="kp-hero hero relative w-full min-h-[100dvh] -mt-20 pt-28 flex flex-col justify-center overflow-hidden bg-surface-950 pb-16">
    <div class="kp-hero-inner">

        <div class="kp-pill">
            <i class="fa-solid fa-location-dot"></i>
            Semarang, Jawa Tengah
        </div>

        <h1 class="kp-hero-h">
            Informasi<br>& <em>Kontak</em>
        </h1>

        <div class="kp-rule"></div>

        <p class="kp-hero-p">
            Temukan kami, hubungi langsung, atau kunjungi arena bertaraf internasional di jantung kota Semarang.
        </p>

        <div class="kp-cta">
            <a href="https://wa.me/6287744495511?text=Halo%2C%20saya%20ingin%20bertanya%20mengenai%20penyewaan%20arena%20Velodrome%20Diponegoro."
               target="_blank" rel="noopener" class="kp-btn kp-btn-wa">
                <i class="fa-brands fa-whatsapp"></i> Chat WhatsApp
            </a>
            <a href="#lokasi" class="kp-btn kp-btn-ghost">
                <i class="fa-solid fa-map-pin"></i> Lihat Lokasi
            </a>
        </div>

    </div>
</section>

<div class="kp-stats-wrap">
    <div class="kp-stats">
        <div class="kp-stat"><div class="kp-stat-v">2</div><div class="kp-stat-l">Arena Tersedia</div></div>
        <div class="kp-stat"><div class="kp-stat-v">24/7</div><div class="kp-stat-l">Booking Online</div></div>
        <div class="kp-stat"><div class="kp-stat-v">UCI</div><div class="kp-stat-l">Standar Internasional</div></div>
    </div>
</div>

<div class="kp-body">

    <p class="kp-eye">Hubungi Kami</p>
    <h2 class="kp-sec-h">Siap membantu Anda</h2>

    <div class="kp-duo" id="lokasi">

        <div class="kp-card" style="display:flex;flex-direction:column;animation-delay:.06s">
            <div class="kp-ch">
                <div class="kp-ch-ico"><i class="fa-solid fa-map"></i></div>
                <div>
                    <div class="kp-ch-t">Lokasi Arena</div>
                    <div class="kp-ch-s">Kompleks Velodrome Diponegoro, Semarang</div>
                </div>
            </div>
            <div style="flex:1;min-height:320px;">
                <iframe
                    class="kp-map"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63360.269317362065!2d110.35957135171353!3d-7.007300494296287!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e708d4fe15b8d21%3A0x22c8b7831646fc78!2sStadion%20Diponegoro!5e0!3m2!1sid!2sid!4v1777175367501!5m2!1sid!2sid"
                    allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Lokasi Velodrome Diponegoro Semarang">
                </iframe>
            </div>
        </div>

        <div class="kp-card" style="animation-delay:.14s">
            <div class="kp-ch">
                <div class="kp-ch-ico"><i class="fa-solid fa-address-card"></i></div>
                <div>
                    <div class="kp-ch-t">Informasi Kontak</div>
                    <div class="kp-ch-s">Hubungi kami kapan saja</div>
                </div>
            </div>
            <div class="kp-cb">
                <div class="kp-row">
                    <div class="kp-ri"><i class="fa-solid fa-location-dot"></i></div>
                    <div><div class="kp-rl">Alamat</div><div class="kp-rv">Jl. Diponegoro, Semarang,<br>Jawa Tengah, Indonesia</div></div>
                </div>
                <div class="kp-row">
                    <div class="kp-ri"><i class="fa-brands fa-whatsapp"></i></div>
                    <div><div class="kp-rl">WhatsApp</div><div class="kp-rv"><a href="https://wa.me/6287744495511" target="_blank" rel="noopener">+62 877-4449-5511</a></div></div>
                </div>
                <div class="kp-row">
                    <div class="kp-ri"><i class="fa-solid fa-envelope"></i></div>
                    <div><div class="kp-rl">Email</div><div class="kp-rv"><a href="mailto:info@velodrome-diponegoro.id">info@velodrome-diponegoro.id</a></div></div>
                </div>
                <div class="kp-row">
                    <div class="kp-ri"><i class="fa-solid fa-globe"></i></div>
                    <div><div class="kp-rl">Website</div><div class="kp-rv"><a href="<?= BASE_URL ?>/">velodrome-diponegoro.id</a></div></div>
                </div>

                <a href="https://wa.me/6287744495511?text=Halo%2C%20saya%20ingin%20bertanya%20mengenai%20penyewaan%20arena%20Velodrome%20Diponegoro."
                   target="_blank" rel="noopener" class="kp-wa">
                    <div class="kp-wa-ic"><i class="fa-brands fa-whatsapp"></i></div>
                    <div>
                        <div class="kp-wa-lbl">Hubungi via WhatsApp</div>
                        <div class="kp-wa-sub">Respons cepat pada jam kerja</div>
                    </div>
                    <i class="fa-solid fa-arrow-right" style="margin-left:auto;font-size:11px;opacity:.65;"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="kp-bot">

        <div class="kp-card" style="animation-delay:.18s">
            <div class="kp-ch">
                <div class="kp-ch-ico"><i class="fa-solid fa-clock"></i></div>
                <div>
                    <div class="kp-ch-t">Jam Operasional</div>
                    <div class="kp-ch-s">Waktu layanan dan kunjungan</div>
                </div>
            </div>
            <div class="kp-cb">
                <table class="kp-ht">
                    <tr><td>Senin – Jumat</td><td>07.00 – 22.00 <span class="kp-b kp-b-o">Buka</span></td></tr>
                    <tr><td>Sabtu</td><td>06.00 – 22.00 <span class="kp-b kp-b-o">Buka</span></td></tr>
                    <tr><td>Minggu</td><td>06.00 – 20.00 <span class="kp-b kp-b-o">Buka</span></td></tr>
                    <tr><td>Hari Libur</td><td><span class="kp-b kp-b-c">Tutup</span></td></tr>
                </table>
                <div class="kp-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>Konfirmasi booking dilakukan pada hari kerja. Unggah bukti pembayaran sebelum waktu sewa dimulai.</p>
                </div>
            </div>
        </div>

        <div class="kp-card" style="animation-delay:.24s">
            <div class="kp-ch">
                <div class="kp-ch-ico"><i class="fa-solid fa-star"></i></div>
                <div>
                    <div class="kp-ch-t">Fasilitas Arena</div>
                    <div class="kp-ch-s">Tersedia untuk seluruh penyewa</div>
                </div>
            </div>
            <div class="kp-cb">
                <div class="kp-tags">
                    <?php foreach ([
                        ['fa-bicycle',       'Lintasan Oval 250m'],
                        ['fa-users',         'Tribun 3.000 Kursi'],
                        ['fa-futbol',        'Lapangan FIFA'],
                        ['fa-lightbulb',     'Lighting LED'],
                        ['fa-display',       'Scoring Digital'],
                        ['fa-car',           'Area Parkir'],
                        ['fa-door-open',     'Ruang Ganti'],
                        ['fa-droplet',       'Kamar Mandi'],
                        ['fa-shield-halved', 'Keamanan 24 Jam'],
                        ['fa-wifi',          'Akses WiFi'],
                        ['fa-kit-medical',   'P3K'],
                        ['fa-camera',        'CCTV'],
                    ] as [$ic, $lb]): ?>
                    <div class="kp-tag">
                        <div class="kp-tag-i"><i class="fa-solid <?= $ic ?>"></i></div>
                        <?= $lb ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
</div>
</div>

<a href="https://wa.me/6287744495511?text=Halo%2C%20saya%20ingin%20bertanya%20mengenai%20penyewaan%20arena%20Velodrome%20Diponegoro."
   target="_blank" rel="noopener" class="kp-float" title="Chat WhatsApp">
    <div class="kp-ring"></div>
    <i class="fa-brands fa-whatsapp"></i>
    <span class="kp-tip">Chat WhatsApp</span>
</a>

<script>
(function () {
    if (!('IntersectionObserver' in window)) return;
    var els = document.querySelectorAll('.kp-card, .kp-tag, .kp-stat');
    var io  = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            e.target.style.opacity   = '1';
            e.target.style.transform = 'translateY(0)';
            io.unobserve(e.target);
        });
    }, { threshold: 0.08 });
    els.forEach(function (el) {
        if (el.getBoundingClientRect().top > window.innerHeight) {
            el.style.opacity    = '0';
            el.style.transform  = 'translateY(18px)';
            el.style.transition = 'opacity .5s ease, transform .5s ease';
            io.observe(el);
        }
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>