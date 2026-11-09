<?php
$page_title = 'Tambah Survey Baru';
include 'includes/header.php';

$success = '';
$error = '';
$upload_dir = 'uploads/'; // Pastikan direktori ini ada dan dapat ditulis

// Handle Form Submit (CREATE SURVEY)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // --- Data Sanitization and Retrieval from POST ---
    $kode_survey = clean_input($_POST['kode_survey']);
    $tanggal_kunjungan = clean_input($_POST['tanggal_kunjungan']);
    $nama_nasabah = clean_input($_POST['nama_nasabah']);
    $nik_nasabah = clean_input($_POST['nik_nasabah']);
    $alamat_nasabah = clean_input($_POST['alamat_nasabah']);
    $no_telepon = clean_input($_POST['no_telepon']);
    $jenis_produk = clean_input($_POST['jenis_produk']);
    $nilai_transaksi = str_replace('.', '', clean_input($_POST['nilai_transaksi'])); 
    $tujuan_kunjungan = clean_input($_POST['tujuan_kunjungan']);
    $hasil_kunjungan = clean_input($_POST['hasil_kunjungan']);
    $catatan_surveyor = clean_input($_POST['catatan_surveyor']);
    $status_survey = clean_input($_POST['status_survey']);
    $surveyor_id = $_SESSION['user_id'];
    
    $foto_ktp_path = '';
    $foto_dokumentasi_path = '';
    $foto_bukti_path = ''; // [PERBAIKAN] Inisialisasi path Foto Bukti
    $has_upload_error = false;
    
    // --- FILE UPLOAD LOGIC ---
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // 1. Upload FOTO KTP
    if (isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto_ktp']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['foto_ktp']['name'], PATHINFO_EXTENSION));
        // Batasi ekstensi dan ukuran file di sini juga untuk keamanan server
        if (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $error = "Format file Foto KTP tidak didukung. Gunakan JPG, JPEG, PNG, GIF, atau WEBP.";
            $has_upload_error = true;
        } elseif ($_FILES['foto_ktp']['size'] > 5 * 1024 * 1024) {
             $error = "Ukuran file Foto KTP terlalu besar (Maks. 5MB).";
             $has_upload_error = true;
        } else {
            $new_filename = $kode_survey . '_ktp_' . time() . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $destination)) {
                $foto_ktp_path = $destination;
            } else {
                $error = 'Gagal mengunggah Foto KTP.';
                $has_upload_error = true;
            }
        }
    }

    // 2. Upload FOTO DOKUMENTASI
    if (!$has_upload_error && isset($_FILES['foto_dokumentasi']) && $_FILES['foto_dokumentasi']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto_dokumentasi']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['foto_dokumentasi']['name'], PATHINFO_EXTENSION));
        // Batasi ekstensi dan ukuran file di sini juga
         if (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $error = "Format file Foto Dokumentasi tidak didukung. Gunakan JPG, JPEG, PNG, GIF, atau WEBP.";
            $has_upload_error = true;
        } elseif ($_FILES['foto_dokumentasi']['size'] > 5 * 1024 * 1024) {
             $error = "Ukuran file Foto Dokumentasi terlalu besar (Maks. 5MB).";
             $has_upload_error = true;
        } else {
            $new_filename = $kode_survey . '_dok_' . time() . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $destination)) {
                $foto_dokumentasi_path = $destination;
            } else {
                $error = 'Gagal mengunggah Foto Dokumentasi.';
                $has_upload_error = true;
            }
        }
    }

    // [PERBAIKAN] 3. Upload FOTO BUKTI
    if (!$has_upload_error && isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto_bukti']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['foto_bukti']['name'], PATHINFO_EXTENSION));
        // Batasi ekstensi dan ukuran file di sini juga
        if (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $error = "Format file Foto Bukti tidak didukung. Gunakan JPG, JPEG, PNG, GIF, atau WEBP.";
            $has_upload_error = true;
        } elseif ($_FILES['foto_bukti']['size'] > 5 * 1024 * 1024) {
             $error = "Ukuran file Foto Bukti terlalu besar (Maks. 5MB).";
             $has_upload_error = true;
        } else {
            $new_filename = $kode_survey . '_bukti_' . time() . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $destination)) {
                $foto_bukti_path = $destination;
            } else {
                $error = 'Gagal mengunggah Foto Bukti.';
                $has_upload_error = true;
            }
        }
    }
    // --- END FILE UPLOAD LOGIC ---

    if (!$has_upload_error) {
        // KODE TAMBAH NASABAH BARU (CREATE SURVEY)
        $stmt = $conn->prepare("INSERT INTO survey_kunjungan 
            (kode_survey, tanggal_kunjungan, nama_nasabah, nik_nasabah, alamat_nasabah, 
            no_telepon, jenis_produk, nilai_transaksi, tujuan_kunjungan, hasil_kunjungan, 
            catatan_surveyor, status_survey, surveyor_id, foto_ktp, foto_dokumentasi, foto_bukti) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssdssssisss", // [PERBAIKAN] Menambah 1 's' untuk foto_bukti_path
            $kode_survey, $tanggal_kunjungan, $nama_nasabah, $nik_nasabah, 
            $alamat_nasabah, $no_telepon, $jenis_produk, $nilai_transaksi, 
            $tujuan_kunjungan, $hasil_kunjungan, $catatan_surveyor, $status_survey, $surveyor_id, 
            $foto_ktp_path, $foto_dokumentasi_path, $foto_bukti_path); // [PERBAIKAN] Menambah foto_bukti_path
        
        if ($stmt->execute()) {
            log_activity($_SESSION['user_id'], 'create_survey', "Menambah survey: $kode_survey");
            header("Location: survey.php?success=" . urlencode('Nasabah baru dan data survey berhasil ditambahkan!'));
            exit();
        } else {
            $error = 'Gagal menambahkan data! ' . $conn->error;
        }
        $stmt->close();
    }
}

// Default values for form (used if not POSTing)
$default_kode = generate_kode_survey();
$default_tanggal = date('Y-m-d');
?>

<div class="page-header">
    <h1><?php echo $page_title; ?></h1>
    <p class="breadcrumb">Home / Data Survey / Tambah Baru</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Input Data Nasabah dan Kunjungan</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="tambah_nasabah.php" id="surveyForm" enctype="multipart/form-data">
            <input type="hidden" name="id" value="0">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Kode Survey *</label>
                    <input type="text" name="kode_survey" id="kode_survey" 
                           class="form-control" value="<?php echo $default_kode; ?>" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Kunjungan *</label>
                    <input type="date" name="tanggal_kunjungan" id="tanggal_kunjungan" 
                           class="form-control" value="<?php echo $default_tanggal; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Nasabah *</label>
                    <input type="text" name="nama_nasabah" id="nama_nasabah" 
                           class="form-control" placeholder="Nama lengkap nasabah" required
                           value="<?php echo isset($_POST['nama_nasabah']) ? htmlspecialchars($_POST['nama_nasabah']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>NIK Nasabah *</label>
                    <input type="text" name="nik_nasabah" id="nik_nasabah" 
                           class="form-control" placeholder="16 digit NIK" maxlength="16" required
                           value="<?php echo isset($_POST['nik_nasabah']) ? htmlspecialchars($_POST['nik_nasabah']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Alamat Nasabah *</label>
                <textarea name="alamat_nasabah" id="alamat_nasabah" 
                          class="form-control" placeholder="Alamat lengkap" required><?php echo isset($_POST['alamat_nasabah']) ? htmlspecialchars($_POST['alamat_nasabah']) : ''; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" name="no_telepon" id="no_telepon" 
                           class="form-control" placeholder="08xxxxxxxxxx"
                           value="<?php echo isset($_POST['no_telepon']) ? htmlspecialchars($_POST['no_telepon']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Jenis Produk *</label>
                    <select name="jenis_produk" id="jenis_produk" class="form-control" required>
                        <option value="">Semua Produk</option>
                        <option value="Kupedes" <?php echo (isset($_POST['jenis_produk']) && $_POST['jenis_produk'] == 'Kupedes') ? 'selected' : ''; ?>>Kupedes</option>
                        <option value="Mulia Multimate" <?php echo (isset($_POST['jenis_produk']) && $_POST['jenis_produk'] == 'Mulia Multimate') ? 'selected' : ''; ?>>Mulia Multimate</option>
                        <option value="Kredit Usaha Rakyat" <?php echo (isset($_POST['jenis_produk']) && $_POST['jenis_produk'] == 'Kredit Usaha Rakyat') ? 'selected' : ''; ?>>Kredit Usaha Rakyat</option>
                        <option value="Arrum Ekspress Loan" <?php echo (isset($_POST['jenis_produk']) && $_POST['jenis_produk'] == 'Arrum Ekspress Loan') ? 'selected' : ''; ?>>Arrum Ekspress Loan</option>
                        <option value="Amanah" <?php echo (isset($_POST['jenis_produk']) && $_POST['jenis_produk'] == 'Amanah') ? 'selected' : ''; ?>>Amanah</option>
                        <option value="Krasida" <?php echo (isset($_POST['jenis_produk']) && $_POST['jenis_produk'] == 'Krasida') ? 'selected' : ''; ?>>Krasida</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Nilai Transaksi (Rp)</label>
                    <input type="text" name="nilai_transaksi" id="nilai_transaksi" 
                           class="form-control rupiah-input" placeholder="0"
                           value="<?php echo isset($_POST['nilai_transaksi']) ? htmlspecialchars($_POST['nilai_transaksi']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Tujuan Kunjungan *</label>
                    <select name="tujuan_kunjungan" id="tujuan_kunjungan" class="form-control" required>
                        <option value="">Pilih Tujuan</option>
                        <option value="Survey Awal" <?php echo (isset($_POST['tujuan_kunjungan']) && $_POST['tujuan_kunjungan'] == 'Survey Awal') ? 'selected' : ''; ?>>Survey Awal</option>
                        <option value="Follow Up" <?php echo (isset($_POST['tujuan_kunjungan']) && $_POST['tujuan_kunjungan'] == 'Follow Up') ? 'selected' : ''; ?>>Follow Up</option>
                        <option value="Penagihan" <?php echo (isset($_POST['tujuan_kunjungan']) && $_POST['tujuan_kunjungan'] == 'Penagihan') ? 'selected' : ''; ?>>Penagihan</option>
                        <option value="Verifikasi Data" <?php echo (isset($_POST['tujuan_kunjungan']) && $_POST['tujuan_kunjungan'] == 'Verifikasi Data') ? 'selected' : ''; ?>>Verifikasi Data</option>
                        <option value="Lainnya" <?php echo (isset($_POST['tujuan_kunjungan']) && $_POST['tujuan_kunjungan'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Foto KTP (Wajib)</label>
                    <input type="file" name="foto_ktp" class="form-control" accept="image/*">
                    <small class="text-muted">Max 5MB. Format: JPG, PNG, dll.</small>
                </div>
                <div class="form-group">
                    <label>Foto Dokumentasi</label>
                    <input type="file" name="foto_dokumentasi" class="form-control" accept="image/*">
                    <small class="text-muted">Max 5MB. Format: JPG, PNG, dll.</small>
                </div>
                <div class="form-group">
                    <label>Foto Bukti</label>
                    <input type="file" name="foto_bukti" class="form-control" accept="image/*">
                    <small class="text-muted">Max 5MB. Format: JPG, PNG, dll.</small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Hasil Kunjungan *</label>
                    <select name="hasil_kunjungan" id="hasil_kunjungan" class="form-control" required>
                        <option value="">Pilih Hasil</option>
                        <option value="Berhasil Ditemui" <?php echo (isset($_POST['hasil_kunjungan']) && $_POST['hasil_kunjungan'] == 'Berhasil Ditemui') ? 'selected' : ''; ?>>Berhasil Ditemui</option>
                        <option value="Tidak Ada di Tempat" <?php echo (isset($_POST['hasil_kunjungan']) && $_POST['hasil_kunjungan'] == 'Tidak Ada di Tempat') ? 'selected' : ''; ?>>Tidak Ada di Tempat</option>
                        <option value="Pindah Alamat" <?php echo (isset($_POST['hasil_kunjungan']) && $_POST['hasil_kunjungan'] == 'Pindah Alamat') ? 'selected' : ''; ?>>Pindah Alamat</option>
                        <option value="Menolak" <?php echo (isset($_POST['hasil_kunjungan']) && $_POST['hasil_kunjungan'] == 'Menolak') ? 'selected' : ''; ?>>Menolak</option>
                        <option value="Proses Lanjut" <?php echo (isset($_POST['hasil_kunjungan']) && $_POST['hasil_kunjungan'] == 'Proses Lanjut') ? 'selected' : ''; ?>>Proses Lanjut</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status Survey *</label>
                    <select name="status_survey" id="status_survey" class="form-control" required>
                        <option value="Draft" <?php echo (isset($_POST['status_survey']) && $_POST['status_survey'] == 'Draft') ? 'selected' : ''; ?>>Draft</option>
                        <option value="Submitted" <?php echo (isset($_POST['status_survey']) && $_POST['status_survey'] == 'Submitted') ? 'selected' : ''; ?>>Submitted</option>
                        <option value="Approved" <?php echo (isset($_POST['status_survey']) && $_POST['status_survey'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                        <option value="Rejected" <?php echo (isset($_POST['status_survey']) && $_POST['status_survey'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Catatan Surveyor</label>
                <textarea name="catatan_surveyor" id="catatan_surveyor" 
                          class="form-control" placeholder="Catatan tambahan"><?php echo isset($_POST['catatan_surveyor']) ? htmlspecialchars($_POST['catatan_surveyor']) : ''; ?></textarea>
            </div>
            
            <div class="modal-footer" style="padding: 0; margin-top: 20px; border-top: none; justify-content: space-between;">
                <a href="survey.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>