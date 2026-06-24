<?php
/**
 * =======
 * Warung Tiga Saudara - Panduan Belanja view
 * Author ID: 11240044
 * =======
 */
?>
<div class="guide-container fade-in">
    
    <!-- Top Header -->
    <div class="guide-header">
        <span class="guide-header__badge">BANTUAN PELANGGAN</span>
        <h1 class="guide-header__title">Panduan Belanja</h1>
        <p class="guide-header__desc">Mulai belanja kebutuhan dapur Anda dengan 3 langkah praktis dan aman. Nikmati kemudahan bertransaksi di Warung Tiga Saudara.</p>
    </div>

    <!-- Step Progress Layout Cards (3 columns) -->
    <div class="steps-grid">
        <!-- Step 1 -->
        <div class="step-card">
            <div class="step-card__icon-wrapper step-card__icon-wrapper--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <span class="step-card__num">1</span>
            <h3 class="step-card__title">Pilih Produk</h3>
            <p class="step-card__desc">Cari produk berkualitas mulai dari sembako, rempah-rempah, hingga camilan favorit keluarga melalui katalog kami yang lengkap.</p>
        </div>

        <!-- Step 2 -->
        <div class="step-card">
            <div class="step-card__icon-wrapper step-card__icon-wrapper--yellow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                </svg>
            </div>
            <span class="step-card__num">2</span>
            <h3 class="step-card__title">Isi Keranjang</h3>
            <p class="step-card__desc">Masukkan semua belanjaan Anda ke dalam keranjang. Atur jumlah pesanan dengan mudah sebelum melanjutkan ke tahap berikutnya.</p>
        </div>

        <!-- Step 3 -->
        <div class="step-card">
            <div class="step-card__icon-wrapper step-card__icon-wrapper--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="3" width="15" height="13" rx="2" ry="2"/>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                    <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
            </div>
            <span class="step-card__num">3</span>
            <h3 class="step-card__title">Pembayaran &amp; Kirim</h3>
            <p class="step-card__desc">Selesaikan transaksi dengan berbagai metode pembayaran aman. Pesanan akan langsung kami kemas dan kirim ke rumah Anda.</p>
        </div>
    </div>

    <!-- Split Section: Quality Card + Accordion FAQ Card -->
    <div class="guide-details-grid">
        <!-- Quality Card (Background Image) -->
        <div class="quality-card" style="background-image: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.9)), url('images/1.webp');">
            <div class="quality-card__content">
                <h2 class="quality-card__title">Kualitas Terjamin</h2>
                <p class="quality-card__desc">Produk kami dipilih langsung dari produsen lokal untuk memastikan kesegaran setiap hari.</p>
            </div>
        </div>

        <!-- FAQ Accordion Card -->
        <div class="faq-card">
            <h3 class="faq-card__title">Butuh Bantuan Lebih?</h3>
            
            <div class="faq-accordion">
                <!-- FAQ 1 -->
                <details class="faq-item" open>
                    <summary class="faq-item__summary">
                        <span>Berapa lama pengiriman barang?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="faq-item__arrow">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </summary>
                    <p class="faq-item__content">
                        Pengiriman pesanan dilakukan pada hari yang sama (Same-day) jika Anda memesan sebelum pukul 15:00 WIB. Untuk pesanan setelah jam tersebut, barang akan dikirim keesokan paginya.
                    </p>
                </details>

                <!-- FAQ 2 -->
                <details class="faq-item">
                    <summary class="faq-item__summary">
                        <span>Metode pembayaran apa saja yang tersedia?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="faq-item__arrow">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </summary>
                    <p class="faq-item__content">
                        Kami mendukung pembayaran via Transfer Bank (Virtual Account), E-Wallet (OVO, GoPay, Dana, LinkAja), dan Cash on Delivery (COD/Bayar di Tempat) saat kurir kami mengantarkan belanjaan Anda.
                    </p>
                </details>

                <!-- FAQ 3 -->
                <details class="faq-item">
                    <summary class="faq-item__summary">
                        <span>Bagaimana jika barang tidak sesuai?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="faq-item__arrow">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </summary>
                    <p class="faq-item__content">
                        Kami menjamin garansi kepuasan pelanggan. Jika barang yang diterima rusak, tidak segar, atau salah kirim, silakan laporkan ke admin kami via kontak WhatsApp dalam 1x24 jam untuk pengembalian dana atau penggantian barang baru secara gratis.
                    </p>
                </details>
            </div>
        </div>
    </div>

    <!-- Call to Action Banner -->
    <div class="guide-cta">
        <div class="guide-cta__text">
            <h2 class="guide-cta__title">Siap untuk Berbelanja?</h2>
            <p class="guide-cta__desc">Gabung bersama ribuan tetangga yang telah mempercayayakan kebutuhan dapur mereka kepada kami.</p>
        </div>
        <div class="guide-cta__actions">
            <a href="index.php?page=pembayaran" class="guide-cta__btn guide-cta__btn--white">Daftar Sekarang</a>
            <a href="index.php?page=promo" class="guide-cta__btn guide-cta__btn--outline">Lihat Promo</a>
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
                    <li><a href="index.php?page=sembako">Beranda</a></li>
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
