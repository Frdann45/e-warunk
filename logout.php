<?php
/**
 * ============================================================
 * E-WARUNG (Warung Tiga Saudara) - Logout Handler
 * ============================================================
 * Author ID   : 11240044
 * Created     : 2026-06-25
 * Description : Destroys all session data and redirects the
 *               user back to the login page.
 * ============================================================
 */

session_start();
session_unset();
session_destroy();

header('Location: login.php');
exit;
