<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pgd_survey');

// Membuat koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Fungsi untuk mencegah SQL Injection
function clean_input($data)
{
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

// Fungsi untuk log aktivitas
function log_activity($user_id, $activity_type, $description)
{
    global $conn;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity_type, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $activity_type, $description, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Fungsi untuk cek session dan keamanan
function check_login()
{
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
        header("Location: index.php");
        exit();
    }
}

// Fungsi untuk generate kode survey
function generate_kode_survey()
{
    global $conn;
    $year = date('Y');
    $query = "SELECT kode_survey FROM survey_kunjungan WHERE kode_survey LIKE 'SRV-$year-%' ORDER BY id DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_code = $row['kode_survey'];
        $last_number = intval(substr($last_code, -3));
        $new_number = $last_number + 1;
    } else {
        $new_number = 1;
    }

    return 'SRV-' . $year . '-' . str_pad($new_number, 3, '0', STR_PAD_LEFT);
}

// Fungsi untuk format rupiah
function format_rupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Timezone
date_default_timezone_set('Asia/Jakarta');

// [TAMBAHAN] Fungsi untuk redirect berdasarkan role (diperlukan oleh kode Login/Register baru)
function redirect_by_role()
{
    // Arahkan semua user yang berhasil login ke dashboard.php
    return 'dashboard.php'; 
}