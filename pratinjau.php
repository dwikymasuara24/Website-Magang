<?php
$page_title = 'Detail Survey';
include 'includes/header.php';

$survey_data = null;
$error = '';
$id = 0;

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Ambil Data untuk satu record (termasuk nama surveyor)
    $stmt = $conn->prepare("
        SELECT sk.*, u.nama_lengkap as nama_surveyor, sk.foto_ktp, sk.foto_dokumentasi, sk.foto_bukti 
        FROM survey_kunjungan sk 
        LEFT JOIN users u ON sk.surveyor_id = u.id 
        WHERE sk.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $survey_data = $result->fetch_assoc();
        // Log aktivitas melihat detail
        log_activity($_SESSION['user_id'], 'view_survey_detail', "Melihat detail survey ID: $id (" . $survey_data['kode_survey'] . ")");
    } else {
        $error = 'Data survey tidak ditemukan.';
    }
    $stmt->close();
} else {
    $error = 'ID Survey tidak valid.';
}
?>

<div class="page-header">
    <h1>Detail Survey Kunjungan</h1>
    <p class="breadcrumb">Home / Data Survey / Detail</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <div style="margin-top: 20px;">
        <a href="survey.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Data Survey</a>
    </div>
<?php else: 
    $data = $survey_data;
    $nilai_rupiah = format_rupiah($data['nilai_transaksi']);

    // Tentukan kelas CSS untuk Status
    $status_class = '';
    switch($data['status_survey']) {
        case 'Approved': $status_class = 'badge-success'; break;
        case 'Submitted': $status_class = 'badge-info'; break;
        case 'Draft': $status_class = 'badge-secondary'; break;
        case 'Rejected': $status_class = 'badge-danger'; break;
    }

    // Tentukan kelas CSS untuk Hasil Kunjungan
    $hasil_class = '';
    switch($data['hasil_kunjungan']) {
        case 'Berhasil Ditemui': $hasil_class = 'badge-success'; break;
        case 'Tidak Ada di Tempat': $hasil_class = 'badge-warning'; break;
        case 'Menolak': $hasil_class = 'badge-danger'; break;
        default: $hasil_class = 'badge-info';
    }
?>

<div class="card">
    <div class="card-header">
        <h3>Detail Kode Survey: <?php echo htmlspecialchars($data['kode_survey']); ?></h3>
    </div>
    <div class="card-body">
        <style>
            .detail-table td { padding: 10px; border-bottom: 1px solid #eee; }
            .detail-table .label { font-weight: 600; width: 30%; background: #f8f9fa; }
            .image-preview-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 15px; }
            .image-preview-grid img { width: 100%; height: 200px; border-radius: 8px; border: 1px solid #ddd; object-fit: cover; }
        </style>

        <table class="detail-table" style="width: 100%; border-collapse: collapse;">
            <tr><td class="label">Kode Survey</td><td><?php echo htmlspecialchars($data['kode_survey']); ?></td></tr>
            <tr><td class="label">Tanggal Kunjungan</td><td><?php echo date('d F Y', strtotime($data['tanggal_kunjungan'])); ?></td></tr>
            <tr><td class="label">Nama Nasabah</td><td><?php echo htmlspecialchars($data['nama_nasabah']); ?></td></tr>
            <tr><td class="label">NIK Nasabah</td><td><?php echo htmlspecialchars($data['nik_nasabah']); ?></td></tr>
            <tr><td class="label">Alamat</td><td><?php echo nl2br(htmlspecialchars($data['alamat_nasabah'])); ?></td></tr>
            <tr><td class="label">No. Telepon</td><td><?php echo htmlspecialchars($data['no_telepon'] ?: '-'); ?></td></tr>
            <tr><td class="label">Jenis Produk</td><td><?php echo htmlspecialchars($data['jenis_produk']); ?></td></tr>
            <tr><td class="label">Nilai Transaksi</td><td><?php echo $nilai_rupiah; ?></td></tr>
            <tr><td class="label">Tujuan Kunjungan</td><td><?php echo htmlspecialchars($data['tujuan_kunjungan']); ?></td></tr>
            <tr>
                <td class="label">Hasil Kunjungan</td>
                <td><span class="badge <?php echo $hasil_class; ?>"><?php echo htmlspecialchars($data['hasil_kunjungan']); ?></span></td>
            </tr>
            <tr>
                <td class="label">Status Survey</td>
                <td><span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($data['status_survey']); ?></span></td>
            </tr>
            <tr><td class="label">Catatan Surveyor</td><td><?php echo nl2br(htmlspecialchars($data['catatan_surveyor'] ?: '-')); ?></td></tr>
            <tr><td class="label">Surveyor</td><td><?php echo htmlspecialchars($data['nama_surveyor']); ?></td></tr>
            <tr><td class="label">Tanggal Input</td><td><?php echo date('d/m/Y H:i', strtotime($data['created_at'])); ?></td></tr>
        </table>

        <h4>Dokumentasi Foto</h4>
        <div class="image-preview-grid">
            <div>
                <h5>Foto KTP</h5>
                <?php 
                $ktp_path = $data['foto_ktp'];
                if (!empty($ktp_path) && file_exists($ktp_path)): ?>
                    <a href="<?php echo htmlspecialchars($ktp_path); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($ktp_path); ?>" alt="Foto KTP">
                    </a>
                <?php else: ?>
                    <p style="color: #999;">Tidak ada foto KTP.</p>
                <?php endif; ?>
            </div>
            <div>
                <h5>Foto Dokumentasi Lain</h5>
                <?php 
                $dok_path = $data['foto_dokumentasi'];
                if (!empty($dok_path) && file_exists($dok_path)): ?>
                    <a href="<?php echo htmlspecialchars($dok_path); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($dok_path); ?>" alt="Foto Dokumentasi">
                    </a>
                <?php else: ?>
                    <p style="color: #999;">Tidak ada foto dokumentasi.</p>
                <?php endif; ?>
            </div>
             <div>
                <h5>Foto Bukti</h5> 
                <?php 
                $bukti_path = $data['foto_bukti'];
                if (!empty($bukti_path) && file_exists($bukti_path)): ?>
                    <a href="<?php echo htmlspecialchars($bukti_path); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($bukti_path); ?>" alt="Foto Bukti">
                    </a>
                <?php else: ?>
                    <p style="color: #999;">Tidak ada foto bukti.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-footer" style="padding: 15px 25px; border-top: 1px solid #eee;">
        <a href="survey.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Data Survey</a>
        
        <a href="exports/export_detail_pdf.php?id=<?php echo $id; ?>" 
           class="btn btn-danger" target="_blank" style="margin-left: 10px;">
            <i class="fas fa-file-pdf"></i> Export Detail PDF
        </a>
        
        <a href="edit_nasabah.php?id=<?php echo $id; ?>" class="btn btn-warning" style="margin-left: 10px;"><i class="fas fa-edit"></i> Edit Data</a>
    </div>
</div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>