<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Xendit Checkout Form
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-07-03
 * Description : Frontend checkout form containing hidden inputs
 *               and a prominent submit button to pay via Xendit.
 * ============================================================
 */

// If direct access, redirect back to home page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Variables should be initialized by pembayaran.php or matching controller
$viewOrderId       = isset($orderCode) ? htmlspecialchars($orderCode) : '';
$viewTotalAmount   = isset($totalBill) ? (float) $totalBill : 0.0;
$viewCustomerEmail = isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '';
$viewCustomerName  = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : '';

// Render UI only if we have a valid order
if ($viewTotalAmount > 0 && !empty($viewOrderId)):
?>
<!-- ═══════════════════════════════════════════════════════════
     XENDIT CHECKOUT INTEGRATION UI CARD
     ═══════════════════════════════════════════════════════════ -->
<div class="xendit-checkout-card" id="xendit-checkout-section" style="margin-top: 24px; padding: 24px; background: #FFF5F2; border: 1px solid #FFD8CC; border-radius: 12px;">
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 50%; background: #FFEBE6; display: flex; align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" fill="none" stroke="#EE4D2D" stroke-width="2" style="width: 24px; height: 24px;">
                <rect x="2" y="5" width="20" height="14" rx="2" ry="2"/>
                <line x1="2" y1="10" x2="22" y2="10"/>
            </svg>
        </div>
        <div>
            <h3 style="font-size: 1rem; font-weight: 700; color: #4A2710; margin: 0;">Pembayaran Gateway Aman (Xendit)</h3>
            <p style="font-size: 0.8rem; color: #7F5A44; margin: 4px 0 0 0;">Pembayaran otomatis diverifikasi secara real-time via Invoice Sandbox.</p>
        </div>
    </div>

    <!-- Hidden checkout details submitted to processor -->
    <form action="checkout_proses.php" method="POST" id="xendit-checkout-form">
        <input type="hidden" name="order_id" value="<?= $viewOrderId ?>">
        <input type="hidden" name="total_amount" value="<?= $viewTotalAmount ?>">
        <input type="hidden" name="customer_email" value="<?= $viewCustomerEmail ?>">
        <input type="hidden" name="customer_name" value="<?= $viewCustomerName ?>">

        <!-- Prominent Pay Button -->
        <button type="submit" class="btn-xendit-pay" id="btn-submit-xendit" style="
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, #EE4D2D 0%, #FF6633 100%);
            color: #FFFFFF;
            font-size: 0.92rem;
            font-weight: 700;
            font-family: inherit;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(238, 77, 45, 0.3);
            transition: all 0.25s ease;
        ">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 20px; height: 20px;" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span>Bayar Sekarang dengan Xendit</span>
        </button>
    </form>
</div>

<!-- Inline hover effect to avoid styling issues -->
<script>
    (function() {
        var btn = document.getElementById('btn-submit-xendit');
        if (btn) {
            btn.addEventListener('mouseenter', function() {
                btn.style.transform = 'translateY(-1px)';
                btn.style.boxShadow = '0 6px 20px rgba(238, 77, 45, 0.4)';
            });
            btn.addEventListener('mouseleave', function() {
                btn.style.transform = 'translateY(0)';
                btn.style.boxShadow = '0 4px 14px rgba(238, 77, 45, 0.3)';
            });
        }
    })();
</script>
<?php endif; ?>
