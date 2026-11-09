<?php
session_start();
require_once '../config/database.php';
check_login();

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

// Set headers untuk download Excel
$filename = "Data_Survey_" . date('Y-m-d_His') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Log aktivitas
log_activity($_SESSION['user_id'], 'export_excel', 'Export data survey ke Excel');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            padding: 10px;
            border: 1px solid #000;
        }
        td {
            padding: 8px;
            border: 1px solid #000;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN DATA SURVEY KUNJUNGAN NASABAH</h2>
        <h3>PT PEGADAIAN (PERSERO)</h3>
        <p>Tanggal Export: <?php echo date('d F Y H:i:s'); ?></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Survey</th>
                <th>Tanggal Kunjungan</th>
                <th>Nama Nasabah</th>
                <th>NIK Nasabah</th>
                <th>Alamat</th>
                <th>No. Telepon</th>
                <th>Jenis Produk</th>
                <th>Nilai Transaksi</th>
                <th>Tujuan Kunjungan</th>
                <th>Hasil Kunjungan</th>
                <th>Status Survey</th>
                <th>Catatan Surveyor</th>
                <th>Nama Surveyor</th>
                <th>Tanggal Input</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()): 
            ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo $row['kode_survey']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['tanggal_kunjungan'])); ?></td>
                <td><?php echo $row['nama_nasabah']; ?></td>
                <td style="mso-number-format:'\@';"><?php echo $row['nik_nasabah']; ?></td>
                <td><?php echo $row['alamat_nasabah']; ?></td>
                <td style="mso-number-format:'\@';"><?php echo $row['no_telepon']; ?></td>
                <td><?php echo $row['jenis_produk']; ?></td>
                <td style="mso-number-format:'\#\,\#\#0';"><?php echo $row['nilai_transaksi']; ?></td>
                <td><?php echo $row['tujuan_kunjungan']; ?></td>
                <td><?php echo $row['hasil_kunjungan']; ?></td>
                <td><?php echo $row['status_survey']; ?></td>
                <td><?php echo $row['catatan_surveyor']; ?></td>
                <td><?php echo $row['nama_surveyor']; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
            </tr>
            <?php 
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="15" style="text-align: center;">Tidak ada data</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <br><br>
    <p><strong>Keterangan:</strong></p>
    <p>Total Data: <?php echo $result->num_rows; ?> record</p>
    <p>Dicetak oleh: <?php echo $_SESSION['nama_lengkap']; ?></p>
</body>
</html>