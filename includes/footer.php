<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Main Footer Component
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-07-08
 * Description : Alfagift-inspired 4-column site footer for the
 *               User Portal. Columns:
 *               1 – Layanan Pelanggan  (nav links)
 *               2 – Jelajahi e-warung  (nav links)
 *               3 – Metode Pembayaran + Layanan Pengiriman
 *               4 – Ikuti Kami (social) + Hubungi Kami (contact)
 *               Bottom: <hr> + copyright line.
 *
 * Usage       : <?php include 'footer.php'; ?>
 *               Place at the very end of every user-facing page,
 *               before the closing </body> tag.
 * ============================================================
 */
?>

<!-- ═══════════════════════════════════════════════════════════
     MAIN FOOTER — 4-Column Alfagift-Style
     ═══════════════════════════════════════════════════════════ -->
<!-- Inline styles migrated to style.css -->


<footer class="main-footer" id="main-footer" role="contentinfo">

    <!-- ── 4-Column Grid ──────────────────────────────────────── -->
    <div class="main-footer__grid">

        <!-- ╔══════════════════════════════════════════╗ -->
        <!-- ║  Column 1 — Layanan Pelanggan            ║ -->
        <!-- ╚══════════════════════════════════════════╝ -->
        <div class="main-footer__col" id="footer-col-layanan">
            <h4>Layanan Pelanggan</h4>
            <ul class="main-footer__links" aria-label="Layanan Pelanggan">
                <li>
                    <a href="<?= BASE_URL ?>index.php?page=panduan" id="footer-link-panduan">
                        Panduan Belanja
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>index.php?page=promo" id="footer-link-promo">
                        Promo Bulanan
                    </a>
                </li>
            </ul>
        </div><!-- /#footer-col-layanan -->

        <!-- ╔══════════════════════════════════════════╗ -->
        <!-- ║  Column 2 — Jelajahi e-warung            ║ -->
        <!-- ╚══════════════════════════════════════════╝ -->
        <div class="main-footer__col" id="footer-col-jelajahi">
            <h4>Jelajahi e-warung</h4>
            <ul class="main-footer__links" aria-label="Jelajahi e-warung">
                <li>
                    <a href="<?= BASE_URL ?>index.php?page=tentang" id="footer-link-tentang">
                        Tentang Kami
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>index.php?page=kontak" id="footer-link-kontak">
                        Hubungi Kami
                    </a>
                </li>
            </ul>
        </div><!-- /#footer-col-jelajahi -->

        <!-- ╔══════════════════════════════════════════╗ -->
        <!-- ║  Column 3 — Pembayaran & Pengiriman      ║ -->
        <!-- ╚══════════════════════════════════════════╝ -->
        <div class="main-footer__col" id="footer-col-payment">

            <!-- — Metode Pembayaran ——————————————————— -->
            <h4>Metode Pembayaran</h4>
            <div class="footer-payment-badges" role="list" aria-label="Metode pembayaran yang diterima">

                <!-- COD -->
                <span class="footer-payment-badge footer-payment-badge--cod"
                      role="listitem" title="Bayar di Tempat (Cash on Delivery)">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M2 7a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V7zm10 6a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                    COD
                </span>

                <!-- BCA -->
                <span class="footer-payment-badge footer-payment-badge--bca"
                      role="listitem" title="Transfer BCA">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M3 21h18v-2H3v2zm0-4h18v-2H3v2zm2-4h14V7H5v6zm7-10L2 7h20L12 3z"/>
                    </svg>
                    BCA
                </span>

                <!-- Mandiri -->
                <span class="footer-payment-badge footer-payment-badge--mandiri"
                      role="listitem" title="Transfer Mandiri">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M3 21h18v-2H3v2zm0-4h18v-2H3v2zm2-4h14V7H5v6zm7-10L2 7h20L12 3z"/>
                    </svg>
                    Mandiri
                </span>

            </div><!-- /.footer-payment-badges -->

            <!-- — Layanan Pengiriman ——————————————————— -->
            <h4 class="footer-sub-heading">Layanan Pengiriman</h4>
            <div class="footer-delivery-row" aria-label="Layanan pengiriman yang tersedia">

                <!-- Instan -->
                <span class="footer-delivery-badge" title="Pengiriman Instan">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M19 7c0-1.1-.9-2-2-2h-3L12 3H8C6.3 3 5 4.3 5 6v2H3c-1.1 0-2 .9-2 2v3h2c0 1.7 1.3 3 3 3s3-1.3 3-3h4c0 1.7 1.3 3 3 3s3-1.3 3-3h2v-3c0-1.1-.9-2-2-2h-1V7zM6 14c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm12 0c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z"/>
                    </svg>
                    Pengiriman Instan
                </span>

                <!-- Kurir Lokal -->
                <span class="footer-delivery-badge" title="Kurir Lokal Warung">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.7 1.3 3 3 3s3-1.3 3-3h6c0 1.7 1.3 3 3 3s3-1.3 3-3h2v-5l-3-4zM6 18.5c-.8 0-1.5-.7-1.5-1.5s.7-1.5 1.5-1.5 1.5.7 1.5 1.5-.7 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zM18 18.5c-.8 0-1.5-.7-1.5-1.5s.7-1.5 1.5-1.5 1.5.7 1.5 1.5-.7 1.5-1.5 1.5z"/>
                    </svg>
                    Kurir Lokal
                </span>

            </div><!-- /.footer-delivery-row -->

        </div><!-- /#footer-col-payment -->

        <!-- ╔══════════════════════════════════════════╗ -->
        <!-- ║  Column 4 — Sosial & Kontak              ║ -->
        <!-- ╚══════════════════════════════════════════╝ -->
        <div class="main-footer__col" id="footer-col-social">

            <!-- — Ikuti Kami ——————————————————————————— -->
            <h4>Ikuti Kami</h4>
            <div class="footer-social-row" aria-label="Media sosial e-warung">

                <!-- Facebook -->
                <a href="#"
                   class="footer-social-btn footer-social-btn--fb"
                   id="footer-social-facebook"
                   aria-label="Ikuti kami di Facebook"
                   title="Facebook e-warung"
                   target="_blank" rel="noopener noreferrer">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#FFFFFF" aria-hidden="true">
                        <path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.234 2.686.234v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
                    </svg>
                </a>

                <!-- Twitter / X -->
                <a href="#"
                   class="footer-social-btn footer-social-btn--tw"
                   id="footer-social-twitter"
                   aria-label="Ikuti kami di Twitter"
                   title="Twitter e-warung"
                   target="_blank" rel="noopener noreferrer">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="#FFFFFF" aria-hidden="true">
                        <path d="M23.954 4.569a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.691 8.094 4.066 6.13 1.64 3.161a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.061a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.937 4.937 0 004.604 3.417 9.868 9.868 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.054 0 13.999-7.496 13.999-13.986 0-.209 0-.42-.015-.63a9.936 9.936 0 002.46-2.548l-.047-.02z"/>
                    </svg>
                </a>

                <!-- Instagram -->
                <a href="#"
                   class="footer-social-btn footer-social-btn--ig"
                   id="footer-social-instagram"
                   aria-label="Ikuti kami di Instagram"
                   title="Instagram e-warung"
                   target="_blank" rel="noopener noreferrer">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="#FFFFFF" aria-hidden="true">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                    </svg>
                </a>

            </div><!-- /.footer-social-row -->

            <!-- — Hubungi Kami ————————————————————————— -->
            <h4 class="footer-sub-heading">Hubungi Kami</h4>
            <ul class="footer-contact-list" aria-label="Informasi kontak e-warung">

                <!-- Email -->
                <li>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <span>
                        <a href="mailto:cs@ewarung.com" id="footer-contact-email">
                            cs@ewarung.com
                        </a>
                    </span>
                </li>

                <!-- Phone / WhatsApp -->
                <li>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012.18 1h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.91 8.09a16 16 0 006 6l1.45-1.45a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14.92z"/>
                    </svg>
                    <span>
                        <a href="https://wa.me/6281200000000"
                           id="footer-contact-whatsapp"
                           target="_blank" rel="noopener noreferrer">
                            0812-XXXX-XXXX
                        </a>
                        <small style="display:block;font-size:0.72rem;color:#9A8D85;">
                            (WhatsApp tersedia)
                        </small>
                    </span>
                </li>

            </ul><!-- /.footer-contact-list -->

        </div><!-- /#footer-col-social -->

    </div><!-- /.main-footer__grid -->

    <!-- ── Divider ────────────────────────────────────────────── -->
    <hr class="main-footer__divider" aria-hidden="true">

    <!-- ── Copyright Bar ──────────────────────────────────────── -->
    <div class="main-footer__bottom">
        <p class="main-footer__copyright">
            &copy; 2026 <strong>Warung Tiga Saudara</strong>. All rights reserved.
        </p>
        <span class="main-footer__author-tag" aria-hidden="true">e-warung&nbsp;v2.0</span>
    </div><!-- /.main-footer__bottom -->

</footer><!-- /.main-footer -->
