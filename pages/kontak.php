<?php
/**
 * =======
 * Warung Tiga Saudara - Kontak view
 * Author ID: 11240044
 * =======
 */

$messageSent = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subjek = isset($_POST['subjek']) ? trim($_POST['subjek']) : '';
    $pesan = isset($_POST['pesan']) ? trim($_POST['pesan']) : '';

    if ($nama === '' || $email === '' || $pesan === '') {
        $errorMessage = 'Mohon lengkapi semua field wajib (Nama, Email, dan Pesan).';
    } else {
        // Save message or simulate success
        $messageSent = true;
    }
}
?>
<div class="contact-container fade-in">
    
    <!-- Top Header -->
    <div class="contact-header">
        <h1 class="contact-header__title">Hubungi Kami</h1>
        <p class="contact-header__desc">Punya pertanyaan atau ingin memesan langsung? Tim kami siap melayani Anda dengan ramah dan cepat.</p>
    </div>

    <?php if ($messageSent): ?>
        <div class="contact-success-alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="alert-icon">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <div>
                <strong>Pesan Terkirim!</strong> Terima kasih telah menghubungi kami. Kami akan merespon pesan Anda secepatnya.
            </div>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
        <div class="contact-error-alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="alert-icon">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <div>
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Grid -->
    <div class="contact-grid">
        <!-- Left Column: Form -->
        <div class="contact-card contact-form-card">
            <div class="contact-card__header-row">
                <span class="contact-card__header-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </span>
                <h3 class="contact-card__form-title">Kirim Pesan</h3>
            </div>
            
            <form action="index.php?page=kontak" method="POST" class="contact-form">
                <input type="hidden" name="action" value="send_message">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nama">NAMA LENGKAP</label>
                        <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama Anda" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">ALAMAT EMAIL</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="subjek">SUBJEK PESAN</label>
                    <div class="select-wrapper-contact">
                        <select id="subjek" name="subjek" class="form-control-select">
                            <option value="Pertanyaan Stok Barang">Pertanyaan Stok Barang</option>
                            <option value="Kritik &amp; Saran">Kritik &amp; Saran</option>
                            <option value="Kerjasama Kemitraan">Kerjasama Kemitraan</option>
                        </select>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-arrow-contact">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="pesan">PESAN ANDA</label>
                    <textarea id="pesan" name="pesan" class="form-control-textarea" placeholder="Tuliskan pesan Anda di sini..." required></textarea>
                </div>

                <button type="submit" class="contact-form__submit-btn">
                    Kirim Sekarang
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="submit-btn-icon">
                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Right Column: Info Widgets -->
        <div class="contact-info-stack">
            <!-- WhatsApp Card -->
            <div class="info-card info-card--whatsapp">
                <div class="info-card__content">
                    <span class="info-card__icon-container">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                        </svg>
                    </span>
                    <h3 class="info-card__title">WhatsApp Kami</h3>
                    <p class="info-card__desc">Butuh respon cepat? Chat admin kami langsung di WhatsApp.</p>
                    <a href="https://wa.me/6281234567890" target="_blank" class="info-card__btn-wa">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="wa-btn-icon">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0.7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        +62 812-3456-7890
                    </a>
                </div>
                <!-- Watermark chat icon -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="info-card__watermark">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>

            <!-- Hours Card -->
            <div class="info-card">
                <div class="info-card__header-row">
                    <span class="info-card__small-icon info-card__small-icon--red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </span>
                    <h4 class="info-card__sub-title">Jam Operasional</h4>
                </div>
                <ul class="info-card__list-hours">
                    <li>
                        <span>Senin - Jumat</span>
                        <strong>07:00 - 21:00</strong>
                    </li>
                    <li>
                        <span>Sabtu</span>
                        <strong>07:00 - 20:00</strong>
                    </li>
                    <li>
                        <span>Minggu</span>
                        <strong class="text-danger">Tutup</strong>
                    </li>
                </ul>
            </div>

            <!-- Address Card -->
            <div class="info-card">
                <div class="info-card__header-row">
                    <span class="info-card__small-icon info-card__small-icon--red">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                    </span>
                    <h4 class="info-card__sub-title">Alamat Toko</h4>
                </div>
                <p class="info-card__address-text">Jl. Bungursari, kelurahan Bungursari, kecamatan Bungursari, kota Tasikmalaya</p>
                
                <!-- SVG map rendering -->
                <div class="info-card__map-wrapper">
                    <img src="images/map-location.svg" alt="Lokasi Toko" class="info-card__map-img">
                    <div class="info-card__map-controls">
                        <button class="map-ctrl-btn" title="Perbesar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                        </button>
                        <button class="map-ctrl-btn" title="Perkecil">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Social Media Section -->
    <div class="contact-socials-section">
        <h3 class="contact-socials-section__title">Ikuti Kami di Media Sosial</h3>
        <div class="contact-socials-section__pills">
            <a href="#" class="social-pill">
                <span class="social-pill__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                    </svg>
                </span>
                Instagram
            </a>
            <a href="#" class="social-pill">
                <span class="social-pill__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                    </svg>
                </span>
                Facebook
            </a>
            <a href="#" class="social-pill">
                <span class="social-pill__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5 4.9-7.22 8.5-12.33 11.23"/>
                    </svg>
                </span>
                Website
            </a>
        </div>
    </div>

    <!-- Small Footer Copyright -->
    <footer class="contact-footer">
        <p>&copy; 2024 Warung Tiga Saudara. Dikelola dengan Cinta oleh Tiga Saudara.</p>
    </footer>

</div>
