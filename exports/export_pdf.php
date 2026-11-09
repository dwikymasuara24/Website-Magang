<?php
session_start();
require_once '../config/database.php';
check_login();

$filename = "Laporan_Survey_" . date('Y-m-d') . ".pdf";
header("Content-Type: text/html; charset=UTF-8"); 
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Get filters
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$filter_produk = isset($_GET['produk']) ? clean_input($_GET['produk']) : '';

$where_clauses = [];
if (!empty($search)) {
    $where_clauses[] = "(nama_nasabah LIKE '%$search%' OR kode_survey LIKE '%$search%' OR nik_nasabah LIKE '%$search%')";
}
if (!empty($filter_status)) {
    $where_clauses[] = "status_survey = '$filter_status'";
}
if (!empty($filter_produk)) {
    $where_clauses[] = "jenis_produk = '$filter_produk'";
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get Data
$query = "SELECT sk.*, u.nama_lengkap as nama_surveyor 
          FROM survey_kunjungan sk 
          LEFT JOIN users u ON sk.surveyor_id = u.id 
          $where_sql 
          ORDER BY sk.created_at DESC";
$result = $conn->query($query);

// Log aktivitas
log_activity($_SESSION['user_id'], 'export_pdf', 'Export data survey ke PDF');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Survey</title>
    <style>
        @page {
            size: A4 landscape; /* Landscape untuk laporan ringkasan */
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px; /* Font lebih kecil */
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px solid #28a745;
            padding-bottom: 10px;
        }
        .header h2 { margin: 0; color: #28a745; font-size: 18px; }
        .header p { margin: 5px 0 0; font-size: 11px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
            page-break-inside: auto;
        }
        table thead {
            display: table-header-group;
            background-color: #f1f1f1;
            font-weight: bold;
        }
        table th, table td { 
            border: 1px solid #ccc; 
            padding: 4px 6px; /* Padding lebih kecil */
            text-align: left;
            vertical-align: top;
        }
        table th { background-color: #28a745; color: white; text-align: center; }
        table tr { page-break-inside: avoid; page-break-after: auto; }
        .badge {
            padding: 2px 5px;
            border-radius: 8px;
            font-size: 8px;
            font-weight: 600;
            display: inline-block;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        
        /* [KODE BARU STYLE] */
        .signature-section {
            margin-top: 50px;
            padding-top: 20px;
            page-break-before: always; /* Memastikan tanda tangan di halaman baru jika tabel terlalu panjang */
        }
        .signature-section table td {
            border: none;
            padding: 5px;
        }
        /* [KODE BARU STYLE END] */
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN DATA SURVEY KUNJUNGAN</h2>
        <p>Periode: <?php echo date('d F Y'); ?></p>
        <p>Dicetak oleh: <?php echo $_SESSION['nama_lengkap']; ?> pada <?php echo date('d F Y H:i:s'); ?></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Survey</th>
                <th>Tgl. Kunjung</th>
                <th>Nama Nasabah</th>
                <th>Produk</th>
                <th>Nilai Transaksi</th>
                <th>Hasil Kunjungan</th>
                <th>Status</th>
                <th>Surveyor</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if ($result->num_rows > 0): 
                while ($row = $result->fetch_assoc()): 
                    // Tentukan kelas badge untuk status dan hasil
                    $status_class = '';
                    switch($row['status_survey']) {
                        case 'Approved': $status_class = 'badge-success'; break;
                        case 'Submitted': $status_class = 'badge-info'; break;
                        case 'Draft': $status_class = 'badge-secondary'; break;
                        case 'Rejected': $status_class = 'badge-danger'; break;
                    }
                    $hasil_class = '';
                    switch($row['hasil_kunjungan']) {
                        case 'Berhasil Ditemui': $hasil_class = 'badge-success'; break;
                        case 'Tidak Ada di Tempat': $hasil_class = 'badge-warning'; break;
                        case 'Menolak': $hasil_class = 'badge-danger'; break;
                        default: $hasil_class = 'badge-info';
                    }
            ?>
            <tr>
                <td style="text-align: center;"><?php echo $no++; ?></td>
                <td><?php echo $row['kode_survey']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['tanggal_kunjungan'])); ?></td>
                <td><?php echo $row['nama_nasabah']; ?></td>
                <td><?php echo $row['jenis_produk']; ?></td>
                <td style="text-align: right;"><?php echo format_rupiah($row['nilai_transaksi']); ?></td>
                <td><span class="badge <?php echo $hasil_class; ?>"><?php echo $row['hasil_kunjungan']; ?></span></td>
                <td><span class="badge <?php echo $status_class; ?>"><?php echo $row['status_survey']; ?></span></td>
                <td><?php echo $row['nama_surveyor'] ?? '-'; ?></td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr>
                <td colspan="9" style="text-align: center;">Tidak ada data survey yang ditemukan.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="signature-section">
        <h3 style="text-align: center; margin-bottom: 25px; font-size: 14px;">Pengesahan Laporan</h3>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 33%; text-align: center;">
                    <p style="font-size: 11px; margin-bottom: 5px;">Disetujui Oleh,</p>
                    <p style="font-weight: bold; margin-bottom: 60px;">Pimpinan</p>
                    <p style="border-top: 1px solid #333; display: inline-block; padding: 0 10px; margin: 0;">(...............................................)</p>
                    <p style="font-size: 9px; margin-top: 5px;">Nama & Jabatan</p>
                </td>
                <td style="width: 33%; text-align: center;">
                    <p style="font-size: 11px; margin-bottom: 5px;">Dibuat Oleh,</p>
                    <p style="font-weight: bold; margin-bottom: 60px;">Surveyor</p>
                    <p style="border-top: 1px solid #333; display: inline-block; padding: 0 10px; margin: 0;">(<?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Nama Pelapor'); ?>)</p>
                    <p style="font-size: 9px; margin-top: 5px;">Nama Lengkap</p>
                </td>
                <td style="width: 34%; text-align: center;">
                    <p style="font-size: 11px; margin-bottom: 5px;">Diketahui Oleh,</p>
                    <p style="font-weight: bold; margin-bottom: 60px;">Nasabah</p>
                    <p style="border-top: 1px solid #333; display: inline-block; padding: 0 10px; margin: 0;">(...............................................)</p>
                    <p style="font-size: 9px; margin-top: 5px;">Nama & Tanda Tangan</p>
                </td>
            </tr>
        </table>
    </div>
    </body>
</html>