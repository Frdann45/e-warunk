<?php
/**
 * ============================================================
 * Warung Tiga Saudara - Bantuan & Panduan Admin (Page Component)
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-07-22
 * Description : Operational Guide & Owner Contact for Admin Panel.
 *               1. Kelola Produk & Promo
 *               2. Mengelola Pesanan & Transaksi
 *               3. Kontak Owner & Support
 * ============================================================
 */
?>
<style>
    .help-container {
        padding: 24px 32px;
        max-width: 1300px;
        margin: 0 auto;
    }
    .help-header {
        margin-bottom: 28px;
    }
    .help-header__title {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0C1E43;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 6px;
    }
    .help-header__title svg {
        width: 28px;
        height: 28px;
        color: #0052CC;
    }
    .help-header__subtitle {
        font-size: 0.92rem;
        color: #4B5F83;
    }
    .help-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }
    .help-card {
        background: #FFFFFF;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
        padding: 24px;
        display: flex;
        flex-direction: column;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .help-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 82, 204, 0.08);
        border-color: #CBD5E1;
    }
    .help-card__header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid #F1F5F9;
    }
    .help-card__icon-wrapper {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #EFF6FF;
        color: #0052CC;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .help-card__icon-wrapper--green {
        background: #DCFCE7;
        color: #16A34A;
    }
    .help-card__icon-wrapper svg {
        width: 24px;
        height: 24px;
    }
    .help-card__title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0C1E43;
    }
    .help-card__badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 12px;
        background: #E0E7FF;
        color: #3730A3;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .help-card__badge--green {
        background: #DCFCE7;
        color: #15803D;
    }
    .help-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .help-list__item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 0.88rem;
        color: #334155;
        line-height: 1.55;
    }
    .help-list__bullet {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #0052CC;
        margin-top: 7px;
        flex-shrink: 0;
    }
    .help-list__bullet--green {
        background: #16A34A;
    }
    .help-list__content strong {
        color: #0F172A;
    }
    .contact-card__actions {
        margin-top: auto;
        padding-top: 16px;
    }
    .btn-wa-owner {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        background: #25D366;
        color: #FFFFFF;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 10px 16px;
        border-radius: 8px;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
        transition: all 0.2s ease;
    }
    .btn-wa-owner:hover {
        background: #1EBE57;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(37, 211, 102, 0.35);
        color: #FFFFFF;
    }
    .btn-wa-owner svg {
        width: 18px;
        height: 18px;
        fill: currentColor;
    }
    .troubleshoot-box {
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        border-left: 5px solid #0052CC;
        border-radius: 12px;
        padding: 24px;
        margin-top: 12px;
    }
    .troubleshoot-box__header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }
    .troubleshoot-box__header svg {
        width: 24px;
        height: 24px;
        color: #0052CC;
    }
    .troubleshoot-box__title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1E3A8A;
    }
    .troubleshoot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin-top: 14px;
    }
    .troubleshoot-item {
        background: #FFFFFF;
        padding: 14px 16px;
        border-radius: 8px;
        border: 1px solid #DBEAFE;
    }
    .troubleshoot-item__title {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1E40AF;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .troubleshoot-item__desc {
        font-size: 0.82rem;
        color: #475569;
        line-height: 1.45;
    }
</style>

<div class="help-container">
    <!-- Header Section -->
    <div class="help-header">
        <h1 class="help-header__title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            Panduan Operasional Admin Panel
        </h1>
        <p class="help-header__subtitle">
            Petunjuk penggunaan lengkap untuk mengelola produk, promo, pesanan transaksi, dan kontak langsung ke Owner toko.
        </p>
    </div>

    <!-- 3 Guide Cards Grid -->
    <div class="help-grid">

        <!-- Card 1: Kelola Produk & Promo -->
        <div class="help-card">
            <div class="help-card__header">
                <div class="help-card__icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                </div>
                <div>
                    <h2 class="help-card__title">1. Kelola Produk &amp; Promo</h2>
                    <span class="help-card__badge">Katalog &amp; Diskon</span>
                </div>
            </div>
            <ul class="help-list">
                <li class="help-list__item">
                    <span class="help-list__bullet"></span>
                    <div class="help-list__content">
                        <strong>Tambah &amp; Edit Produk:</strong> Masuk ke menu <em>Tambah Produk</em> untuk memasukkan nama barang, memilih kategori (Sembako, Rempah, Camilan, Minuman, Perawatan, Kesehatan), menentukan harga jual, serta stok awal.
                    </div>
                </li>
                <li class="help-list__item">
                    <span class="help-list__bullet"></span>
                    <div class="help-list__content">
                        <strong>Pengaturan Promo Diskon:</strong> Gunakan menu <em>Buat Promo</em> untuk menetapkan harga promo (potongan harga) dan memberikan badge menarik seperti <code>PALING LARIS</code> atau <code>PROMO</code>.
                    </div>
                </li>
                <li class="help-list__item">
                    <span class="help-list__bullet"></span>
                    <div class="help-list__content">
                        <strong>Update Stok Otomatis:</strong> Stok barang dalam database secara otomatis terpotong saat pesanan diselesaikan oleh pelanggan.
                    </div>
                </li>
            </ul>
        </div>

        <!-- Card 2: Mengelola Pesanan & Transaksi -->
        <div class="help-card">
            <div class="help-card__header">
                <div class="help-card__icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="3" width="22" height="13" rx="2" ry="2"/>
                        <line x1="1" y1="20" x2="23" y2="20"/>
                    </svg>
                </div>
                <div>
                    <h2 class="help-card__title">2. Mengelola Transaksi</h2>
                    <span class="help-card__badge">Pesanan &amp; Pengiriman</span>
                </div>
            </div>
            <ul class="help-list">
                <li class="help-list__item">
                    <span class="help-list__bullet"></span>
                    <div class="help-list__content">
                        <strong>Pesanan Masuk:</strong> Pantau pesanan belanjaan baru dari pelanggan di menu <em>Pesanan Masuk</em>. Periksa rincian item, alamat tujuan, dan total pembayaran.
                    </div>
                </li>
                <li class="help-list__item">
                    <span class="help-list__bullet"></span>
                    <div class="help-list__content">
                        <strong>Proses &amp; Update Status:</strong> Pada menu <em>Proses Pengiriman</em>, ubah status pesanan secara bertahap (misal: <em>Diproses</em> ➔ <em>Dikirim</em> ➔ <em>Selesai</em>).
                    </div>
                </li>
                <li class="help-list__item">
                    <span class="help-list__bullet"></span>
                    <div class="help-list__content">
                        <strong>Riwayat &amp; Ekspor Laporan:</strong> Buka menu <em>Semua Transaksi</em> untuk melihat histori lengkap dan unduh rekapitulasi data penjualan ke format Excel (.xls).
                    </div>
                </li>
            </ul>
        </div>

        <!-- Card 3: Kontak Owner toko -->
        <div class="help-card">
            <div class="help-card__header">
                <div class="help-card__icon-wrapper help-card__icon-wrapper--green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012.18 1h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.91 8.09a16 16 0 006 6l1.45-1.45a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14.92z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="help-card__title">3. Hubungi Owner Toko</h2>
                    <span class="help-card__badge help-card__badge--green">Kontak Langsung</span>
                </div>
            </div>
            <ul class="help-list">
                <li class="help-list__item">
                    <span class="help-list__bullet help-list__bullet--green"></span>
                    <div class="help-list__content">
                        <strong>Pemilik / Owner:</strong> Bpk. Owner Warung Tiga Saudara
                    </div>
                </li>
                <li class="help-list__item">
                    <span class="help-list__bullet help-list__bullet--green"></span>
                    <div class="help-list__content">
                        <strong>No. Telepon / WhatsApp:</strong> 0812-3456-7890
                    </div>
                </li>
                <li class="help-list__item">
                    <span class="help-list__bullet help-list__bullet--green"></span>
                    <div class="help-list__content">
                        <strong>Jam Operasional:</strong> Setiap Hari (08.00 - 21.00 WIB)
                    </div>
                </li>
            </ul>
            <div class="contact-card__actions">
                <a href="https://wa.me/6281234567890?text=Halo%20Owner%20Warung%20Tiga%20Saudara,%20saya%20admin%20perlu%20bantuan%20terkait%20operasional%20toko..." 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="btn-wa-owner">
                    <svg viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    Hubungi Owner via WhatsApp
                </a>
            </div>
        </div>

    </div><!-- /.help-grid -->

    <!-- Quick Operating Tips -->
    <div class="troubleshoot-box">
        <div class="troubleshoot-box__header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <h3 class="troubleshoot-box__title">Tips Operasional Harian Admin</h3>
        </div>
        <p style="font-size: 0.88rem; color: #334155; line-height: 1.5; margin-bottom: 12px;">
            Ikuti 3 alur pengoperasian rutin ini setiap hari untuk menjaga kelancaran transaksi Warung Tiga Saudara:
        </p>
        <div class="troubleshoot-grid">
            <div class="troubleshoot-item">
                <div class="troubleshoot-item__title">
                    1. Cek Pesanan Masuk
                </div>
                <div class="troubleshoot-item__desc">
                    Periksa menu <em>Pesanan Masuk</em> setiap pagi dan sore hari untuk memastikan tidak ada pesanan baru yang tertunda.
                </div>
            </div>
            <div class="troubleshoot-item">
                <div class="troubleshoot-item__title">
                    2. Update Status Pengiriman
                </div>
                <div class="troubleshoot-item__desc">
                    Segera perbarui status ke <em>Dikirim</em> saat kurir berangkat dan ubah ke <em>Selesai</em> begitu pesanan diterima pembeli.
                </div>
            </div>
            <div class="troubleshoot-item">
                <div class="troubleshoot-item__title">
                    3. Perbarui Stok &amp; Harga Promo
                </div>
                <div class="troubleshoot-item__desc">
                    Pastikan stok barang di menu <em>Tambah Produk</em> selalu terisi sesuai fisik barang di toko agar tidak terjadi pembatalan.
                </div>
            </div>
        </div>
    </div>
</div>
