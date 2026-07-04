<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Xendit Webhook Callback
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-07-03
 * Description : Secure callback handler for incoming Xendit webhook notifications.
 *               Verifies x-callback-token header and updates order status to
 *               'Sedang Dikemas' (coded as 'Diproses' in DB schema) upon successful payment.
 * ============================================================
 */

require_once __DIR__ . '/config_xendit.php';

// ── 1. Secure Webhook Validation ────────────────────────────
// Extract Xendit Callback Token header
$headers       = getallheaders();
$callbackToken = isset($headers['x-callback-token']) ? trim($headers['x-callback-token']) : '';

// Verify incoming token matches the one defined in config_xendit.php
if ($callbackToken !== XENDIT_WEBHOOK_TOKEN) {
    http_response_code(403);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Unauthorized token verification failed.'
    ]);
    exit;
}

// ── 2. Read and Decode Payload ──────────────────────────────
$rawInput = file_get_contents('php://input');
$payload  = json_decode($rawInput, true);

if (!$payload) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid JSON input data.'
    ]);
    exit;
}

$externalId = isset($payload['external_id']) ? $payload['external_id'] : '';
$status     = isset($payload['status']) ? strtoupper($payload['status']) : '';

if (empty($externalId) || empty($status)) {
    http_response_code(422);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Missing required payload parameters (external_id or status).'
    ]);
    exit;
}

// ── 3. DB Status Update (Paid or Settled) ────────────────────
if ($status === 'PAID' || $status === 'SETTLED') {
    
    // MySQLi connection details matching db_connect.php
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'e_warung';

    // Establish connection via MySQLi (as explicitly requested)
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    if ($mysqli->connect_errno) {
        error_log('Xendit Webhook MySQLi Connection Failed: ' . $mysqli->connect_error);
        http_response_code(500);
        exit;
    }

    /**
     * Note on table scheme mapping:
     * - User requested: UPDATE status_pesanan = 'Sedang Dikemas' WHERE id_transaksi = external_id
     * - Database scheme: UPDATE orders SET status = 'Diproses' WHERE order_code = external_id
     * 
     * To ensure absolute compatibility, we execute the update on the active schema
     * columns (status and order_code), while commenting the hypothetical code block.
     */

    // Active project database query
    $updateQuery = "UPDATE orders SET status = 'Diproses' WHERE order_code = ?";
    
    // Alternative code query matching user instructions (commented for reference):
    // $updateQuery = "UPDATE orders SET status_pesanan = 'Sedang Dikemas' WHERE id_transaksi = ?";

    $stmt = $mysqli->prepare($updateQuery);
    if ($stmt) {
        $stmt->bind_param('s', $externalId);
        $stmt->execute();
        
        if ($mysqli->affected_rows > 0) {
            error_log("Order {$externalId} successfully marked PAID/SETTLED via Xendit webhook.");
        } else {
            error_log("Order {$externalId} was not updated (possibly already updated or not found).");
        }
        $stmt->close();
    } else {
        error_log('Xendit Webhook MySQLi statement preparation failed: ' . $mysqli->error);
    }

    $mysqli->close();
}

// Respond with 200 OK to acknowledge receipt of webhook event
http_response_code(200);
echo json_encode([
    'status'  => 'success',
    'message' => 'Callback processed successfully.'
]);
exit;
