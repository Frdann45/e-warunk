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
<style>
/* ── Outer Shell ──────────────────────────────────────────────── */
.main-footer {
    background-color: #1A1A1A;
    border-top: 1px solid #2D2D2D;
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont,
                 'Segoe UI', sans-serif;
    color: #CCCCCC;
    font-size: 0.875rem;
    line-height: 1.65;
}

/* ── Grid Container ───────────────────────────────────────────── */
.main-footer__grid {
    max-width: 1200px;
    margin: 0 auto;
    padding: 52px 24px 36px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 40px 32px;
}

/* ── Column Headings ──────────────────────────────────────────── */
.main-footer__col h4 {
    font-size: 0.8125rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #FFFFFF;
    margin: 0 0 14px;
    padding-bottom: 8px;
    border-bottom: 2px solid #8B5E3C;
    display: inline-block;
}

/* Second h4 inside same column (Layanan Pengiriman & Hubungi Kami) */
.main-footer__col h4.footer-sub-heading {
    margin-top: 24px;
}

/* ── Link Lists ───────────────────────────────────────────────── */
.main-footer__links {
    list-style: none;
    margin: 0;
    padding: 0;
}

.main-footer__links li {
    margin-bottom: 8px;
}

.main-footer__links a {
    text-decoration: none;
    color: #A8A8A8;
    font-weight: 400;
    transition: color 0.2s ease, padding-left 0.2s ease;
    display: inline-block;
}

.main-footer__links a:hover {
    color: #E8C7A8;
    padding-left: 4px;
}

/* ── Payment Badge Row ────────────────────────────────────────── */
.footer-payment-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    margin-top: 2px;
}

.footer-payment-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    background: #FFFFFF;
    border: 1px solid #D9D0C8;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 600;
    color: #3A3330;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    white-space: nowrap;
}

/* Payment-specific accent colours */
.footer-payment-badge--cod     { border-color: #2D9B4E; color: #2D9B4E; }
.footer-payment-badge--bca     { border-color: #005BAA; color: #005BAA; }
.footer-payment-badge--mandiri { border-color: #003D7A; color: #003D7A; }

.footer-payment-badge svg {
    flex-shrink: 0;
}

/* ── Delivery Row ─────────────────────────────────────────────── */
.footer-delivery-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    margin-top: 2px;
}

.footer-delivery-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #2D2D2D;
    border: 1px solid #4A2710;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #E8C7A8;
}

/* ── Social Icons ─────────────────────────────────────────────── */
.footer-social-row {
    display: flex;
    gap: 10px;
    margin-top: 2px;
}

.footer-social-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 1px 4px rgba(0,0,0,0.12);
}

.footer-social-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.18);
}

.footer-social-btn--fb { background: #1877F2; }
.footer-social-btn--tw { background: #1DA1F2; }
.footer-social-btn--ig {
    background: radial-gradient(circle at 30% 107%,
        #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
}

/* ── Contact List ─────────────────────────────────────────────── */
.footer-contact-list {
    list-style: none;
    margin: 4px 0 0;
    padding: 0;
}

.footer-contact-list li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 10px;
    color: #A8A8A8;
    font-size: 0.845rem;
}

.footer-contact-list li svg {
    flex-shrink: 0;
    margin-top: 2px;
    color: #8B5E3C;
}

.footer-contact-list a {
    text-decoration: none;
    color: inherit;
    transition: color 0.2s ease;
}

.footer-contact-list a:hover {
    color: #E8C7A8;
}

/* ── Divider ──────────────────────────────────────────────────── */
.main-footer__divider {
    max-width: 1200px;
    margin: 0 auto;
    border: none;
    border-top: 1px solid #2D2D2D;
}

/* ── Copyright Bar ────────────────────────────────────────────── */
.main-footer__bottom {
    max-width: 1200px;
    margin: 0 auto;
    padding: 18px 24px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.main-footer__copyright {
    font-size: 0.8rem;
    color: #888888;
    text-align: center;
    flex: 1;
    margin: 0;
}

.main-footer__copyright strong {
    color: #E8C7A8;
    font-weight: 600;
}

.main-footer__author-tag {
    font-size: 0.72rem;
    color: #555555;
    letter-spacing: 0.04em;
}

/* ── Responsive: Tablet (≤ 900px → 2 columns) ────────────────── */
@media (max-width: 900px) {
    .main-footer__grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 36px 28px;
    }
}

/* ── Responsive: Mobile (≤ 768px → 1 column, stacked) ────────── */
@media (max-width: 768px) {
    .main-footer__grid {
        grid-template-columns: 1fr;
        padding: 36px 20px 24px;
        gap: 28px;
    }

    .main-footer__col h4 {
        font-size: 0.875rem;
    }

    .main-footer__bottom {
        flex-direction: column;
        text-align: center;
        padding: 16px 20px 20px;
        gap: 6px;
    }

    .main-footer__author-tag {
        order: 1;
    }

    .main-footer__copyright {
        order: 2;
    }
}
</style>

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
                    <a href="index.php?page=panduan" id="footer-link-panduan">
                        Panduan Belanja
                    </a>
                </li>
                <li>
                    <a href="index.php?page=promo" id="footer-link-promo">
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
                    <a href="index.php?page=tentang" id="footer-link-tentang">
                        Tentang Kami
                    </a>
                </li>
                <li>
                    <a href="index.php?page=kontak" id="footer-link-kontak">
                        Kontak
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
        <span class="main-footer__author-tag" aria-hidden="true">ID: 11240044</span>
        <p class="main-footer__copyright">
            &copy; 2026 <strong>Warung Tiga Saudara</strong>. All rights reserved.
        </p>
        <span class="main-footer__author-tag" aria-hidden="true">e-warung&nbsp;v1.0</span>
    </div><!-- /.main-footer__bottom -->

</footer><!-- /.main-footer -->
