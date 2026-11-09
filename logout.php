<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    // Log aktivitas logout
    log_activity($_SESSION['user_id'], 'logout', 'User berhasil logout');
    
    // Hapus semua session
    session_unset();
    session_destroy();
}

// Redirect ke halaman login
header("Location: index.php");
exit();
?>