<?php
/**
 * =======
 * Warung Tiga Saudara - Tentang Kami view
 * Author ID: 11240044
 * =======
 */
?>
<div class="about-container fade-in">
    
    <!-- Hero Section -->
    <div class="about-hero">
        <div class="about-hero__content">
            <span class="about-hero__badge">ESTABLISHED 2015</span>
            <h1 class="about-hero__title">Dedikasi Tiga Saudara<br>Untuk Kebutuhan Anda.</h1>
            <p class="about-hero__desc">
                Berawal dari kios kecil di sudut kota, kini kami tumbuh menjadi pusat kebutuhan harian yang 
                mengedepankan kualitas dan keramahan lokal.
            </p>
        </div>
        <div class="about-hero__image-wrapper">
            <img src="<?= BASE_URL ?>assets/images/warung.webp" alt="Supermarket Staff" class="about-hero__image">
        </div>
    </div>

    <!-- Metrics Stats Grid -->
    <div class="metrics-grid">
        <div class="metric-card">
            <h3 class="metric-card__value">25+</h3>
            <span class="metric-card__label">TAHUN MELAYANI</span>
        </div>
        <div class="metric-card">
            <h3 class="metric-card__value">1000+</h3>
            <span class="metric-card__label">PRODUK PILIHAN</span>
        </div>
        <div class="metric-card">
            <h3 class="metric-card__value">50k+</h3>
            <span class="metric-card__label">PELANGGAN SETIA</span>
        </div>
        <div class="metric-card">
            <h3 class="metric-card__value">24/7</h3>
            <span class="metric-card__label">LAYANAN PESANAN</span>
        </div>
    </div>

    <!-- Sejarah & Visi Misi Section -->
    <div class="about-details-grid">
        <!-- Sejarah Kami -->
        <div class="history-block">
            <div class="history-block__header">
                <span class="history-block__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9M3 20h9M12 4v16"/>
                    </svg>
                </span>
                <h2 class="history-block__title">Sejarah Kami</h2>
            </div>
            <p class="history-block__text">
                Warung Tiga Saudara didirikan pada tahun 1998 oleh tiga bersaudara yang memiliki mimpi untuk menyediakan bahan pokok berkualitas dengan harga terjangkau bagi tetangga sekitar. Dengan modal kejujuran dan pelayanan dari hati, apa yang bermula dari gerobak sederhana kini telah bertransformasi menjadi supermarket lingkungan yang modern namun tetap menjaga nilai-nilai kehangatan keluarga.
            </p>
            <blockquote class="history-block__quote">
                "Kepercayaan pelanggan adalah modal utama kami. Kami tidak hanya menjual produk, kami membangun hubungan yang berkelanjutan." - Pendiri
            </blockquote>
        </div>

        <!-- Visi & Misi Cards -->
        <div class="vision-mission-block">
            <!-- Visi -->
            <div class="vision-card">
                <div class="vision-card__header">
                    <span class="vision-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </span>
                    <h3 class="vision-card__title">Visi Kami</h3>
                </div>
                <p class="vision-card__text">
                    Menjadi mitra terpercaya bagi setiap rumah tangga di Indonesia dalam memenuhi kebutuhan harian dengan kualitas terbaik dan teknologi yang memudahkan.
                </p>
            </div>

            <!-- Misi -->
            <div class="mission-card">
                <div class="mission-card__header">
                    <span class="mission-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </span>
                    <h3 class="mission-card__title">Misi Kami</h3>
                </div>
                <ul class="mission-card__list">
                    <li>Menyediakan produk segar langsung dari petani.</li>
                    <li>Memberikan pelayanan ramah dan profesional.</li>
                    <li>Berinovasi dalam sistem belanja digital.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Suasana Toko Section -->
    <div class="store-atmosphere">
        <h2 class="store-atmosphere__title">Suasana Toko</h2>
        <p class="store-atmosphere__desc">Kami merancang ruang belanja yang nyaman, bersih, dan modern untuk pengalaman terbaik Anda.</p>
        
        <div class="atmosphere-grid">
            <div class="atmosphere-grid__large">
                <img src="<?= BASE_URL ?>assets/images/1.webp" alt="Interior Utama" class="atmosphere-grid__img">
                <div class="atmosphere-grid__label">
                    <h4>Interior Utama</h4>
                    <p>Modern &amp; Nyaman</p>
                </div>
            </div>
            <div class="atmosphere-grid__small-stack">
                <div class="atmosphere-grid__small-item">
                    <img src="<?= BASE_URL ?>assets/images/3.webp" alt="Rak Rempah" class="atmosphere-grid__img">
                </div>
                <div class="atmosphere-grid__small-item">
                    <img src="<?= BASE_URL ?>assets/images/2.webp" alt="Transaksi Kasir" class="atmosphere-grid__img">
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom CTA Banner -->
    <div class="about-cta">
        <div class="about-cta__circles"></div>
        <h2 class="about-cta__title">Mulai Belanja Dengan Kami Hari Ini</h2>
        <p class="about-cta__desc">Nikmati kemudahan berbelanja produk berkualitas tinggi dengan harga yang tetap bersahabat bagi kantong keluarga.</p>
        <div class="about-cta__actions">
            <a href="index.php?page=sembako" class="about-cta__btn about-cta__btn--white">Pesan Sekarang</a>
            <a href="index.php?page=kontak" class="about-cta__btn about-cta__btn--outline">Hubungi Kami</a>
        </div>
    </div>

    <!-- Customized Footer -->
    <footer class="about-footer">
        <div class="about-footer__top">
            <div class="about-footer__brand-col">
                <h3 class="about-footer__logo">Warung Tiga Saudara</h3>
                <p class="about-footer__desc">Warung tetangga modern yang menyediakan kebutuhan harian berkualitas dengan harga kompetitif dan layanan sepenuh hati.</p>
                <div class="about-footer__socials">
                    <a href="#" class="social-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                        </svg>
                    </a>
                    <a href="#" class="social-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </a>
                    <a href="#" class="social-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <div class="about-footer__links-col">
                <h4 class="about-footer__col-title">Tautan Cepat</h4>
                <ul class="about-footer__links-list">
                    <li><a href="index.php?page=beranda">Beranda</a></li>
                    <li><a href="index.php?page=tentang">Tentang Kami</a></li>
                    <li><a href="index.php?page=promo">Promo Bulanan</a></li>
                    <li><a href="index.php?page=panduan">Panduan Belanja</a></li>
                </ul>
            </div>
            
            <div class="about-footer__links-col">
                <h4 class="about-footer__col-title">Layanan</h4>
                <ul class="about-footer__links-list">
                    <li><a href="#">Pengiriman Cepat</a></li>
                    <li><a href="#">Member Card</a></li>
                    <li><a href="#">Catering</a></li>
                    <li><a href="#">Mitra Toko</a></li>
                </ul>
            </div>
        </div>
        <div class="about-footer__bottom">
            <p>&copy; 2024 Warung Tiga Saudara. Dikelola dengan Cinta oleh Tiga Saudara.</p>
        </div>
    </footer>
    
</div>
