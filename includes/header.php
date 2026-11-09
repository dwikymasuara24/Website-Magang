<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';
check_login();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?> - Sistem Survey Pegadaian</title>
    <link rel="stylesheet" href="aseets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Menggunakan Georgia/Serif sebagai pengganti font korporat yang khas Pegadaian */
        .sidebar, .sidebar h2, .sidebar p, .nav-menu {
            font-family: 'Georgia', serif;
        }
    </style>
</head>
<body id="body">
    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="p.png" alt="Logo Pegadaian">
                <h2>PEGADAIAN MIKRO</h2>
                <p>Survey System</p>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="survey.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'survey.php' ? 'active' : ''; ?>">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Data Survey</span>
                    </a>
                </li>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <li class="nav-item">
                    <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Manajemen User</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
            
            <div style="position: absolute; bottom: 20px; left: 20px; right: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.2); display: none;">
            </div>
        </aside>
        
        <main class="main-content">
            <div class="topbar">
                <button class="sidebar-toggle btn btn-sm" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div style="margin-left: 20px;">
                    <h2 style="color: #28a745; font-size: 20px;">Sistem Survey Kunjungan</h2>
                </div>
                <div class="user-info">
                    <div>
                        <div style="font-weight: 600; color: #333;"><?php echo $_SESSION['nama_lengkap']; ?></div>
                        <div style="font-size: 12px; color: #666;"><?php echo ucfirst($_SESSION['role']); ?></div>
                    </div>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>
                    </div>
                </div>
            </div>
            
            <div class="content-area">
<script>
    // FUNGSI JAVASCRIPT UNTUK TOGGLE SIDEBAR
    function toggleSidebar() {
        document.body.classList.toggle('sidebar-collapsed');
    }
    
    // Auto-collapse sidebar on load for desktop (lebar > 768px)
    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth > 768) {
            document.body.classList.add('sidebar-collapsed');
        }
    });

    // Reset toggle pada perubahan ukuran layar
    window.onresize = function() {
        const body = document.body;
        if (window.innerWidth <= 768) {
             // Pastikan sidebar terlihat di mobile secara default
             body.classList.remove('sidebar-collapsed');
        } else {
             // Ciutkan sidebar di desktop secara default
             body.classList.add('sidebar-collapsed');
        }
    }
</script>