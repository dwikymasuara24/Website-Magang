<?php
$page_title = 'Edit Survey';
include 'includes/header.php';

$success = '';
$error = '';
$survey_data = null;
$id = 0;
$upload_dir = 'uploads/'; // Direktori penyimpanan file

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // 1. Fetch existing data
    $stmt = $conn->prepare("SELECT * FROM survey_kunjungan WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $survey_data = $result->fetch_assoc();
    } else {
        $error = 'Data survey tidak ditemukan.';
    }
    $stmt->close();
} else {
    $error = 'ID Survey tidak ditemukan.';
}

// Fungsi Pembantu Upload (duplikat dari tambah_nasabah, dipertahankan di sini untuk kejelasan)
function handle_update_upload($file_key, $kode_survey, $type, $current_path) {
    global $upload_dir, $error;
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == UPLOAD_ERR_OK) {
        // Hapus file lama jika ada unggahan baru
        if (!empty($current_path) && file_exists($current_path)) {
            // Cek apakah file lama benar-benar di dalam direktori upload sebelum dihapus
            if (strpos($current_path, $upload_dir) !== false) {
                 unlink($current_path);
            }
        }

        $file_tmp = $_FILES[$file_key]['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));
        // Batasi ekstensi (Menambah 'webp' seperti di file lain)
        if (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $error = "Format file $type tidak didukung. Gunakan JPG, JPEG, PNG, GIF, atau WEBP.";
            return false;
        }
        // Batasi ukuran (misal 5MB)
        if ($_FILES[$file_key]['size'] > 5 * 1024 * 1024) {
             $error = "Ukuran file $type terlalu besar (Maks. 5MB).";
            return false;
        }
        
        $new_filename = $kode_survey . '_' . $type . '_' . time() . '.' . $file_ext;
        $destination = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file_tmp, $destination)) {
            return $destination;
        } else {
            $error = "Gagal mengunggah file $type.";
            return false;
        }
    }
    // Jika tidak ada upload baru, kembalikan path yang lama
    return $current_path;
}


// 2. Handle Form Submit (UPDATE SURVEY)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id > 0) {
    // Gunakan data yang sudah ada sebagai fallback
    $current_data = $survey_data; 

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
    
    $foto_ktp_path = $current_data['foto_ktp'];
    $foto_dokumentasi_path = $current_data['foto_dokumentasi'];
    $foto_bukti_path = $current_data['foto_bukti']; // Inisialisasi path Foto Bukti
    $has_upload_error = false;

    // --- FILE UPLOAD LOGIC ---
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // 1. Upload FOTO KTP
    $new_ktp_path = handle_update_upload('foto_ktp', $kode_survey, 'ktp', $current_data['foto_ktp']);
    if ($new_ktp_path === false) {
        $has_upload_error = true;
    } else {
        $foto_ktp_path = $new_ktp_path;
    }

    // 2. Upload FOTO DOKUMENTASI
    if (!$has_upload_error) {
        $new_dok_path = handle_update_upload('foto_dokumentasi', $kode_survey, 'dokumentasi', $current_data['foto_dokumentasi']);
        if ($new_dok_path === false) {
            $has_upload_error = true;
        } else {
            $foto_dokumentasi_path = $new_dok_path;
        }
    }
    
    // 3. Upload FOTO BUKTI
    if (!$has_upload_error) {
        $new_bukti_path = handle_update_upload('foto_bukti', $kode_survey, 'bukti', $current_data['foto_bukti']);
        if ($new_bukti_path === false) {
            $has_upload_error = true;
        } else {
            $foto_bukti_path = $new_bukti_path;
        }
    }
    // --- END FILE UPLOAD LOGIC ---

    if (!$has_upload_error) {
        // KODE EDIT (UPDATE NASABAH/SURVEY)
        $stmt = $conn->prepare("UPDATE survey_kunjungan SET 
            kode_survey=?, tanggal_kunjungan=?, nama_nasabah=?, nik_nasabah=?, 
            alamat_nasabah=?, no_telepon=?, jenis_produk=?, nilai_transaksi=?, 
            tujuan_kunjungan=?, hasil_kunjungan=?, catatan_surveyor=?, status_survey=?,
            foto_ktp=?, foto_dokumentasi=?, foto_bukti=?
            WHERE id=?"); 
        
        // Cek apakah prepare berhasil
        if ($stmt === false) {
             $error = 'Gagal menyiapkan query SQL (Kemungkinan kolom foto_bukti tidak ada di database): ' . $conn->error;
        } else {
            // Tipe data yang benar: 14 string (s), 1 double (d), 1 integer (i)
            // Ini adalah baris 177 yang sudah diperbaiki agar string parameternya benar
            $stmt->bind_param("sssssssdsssssssi", 
                $kode_survey, $tanggal_kunjungan, $nama_nasabah, $nik_nasabah, 
                $alamat_nasabah, $no_telepon, $jenis_produk, $nilai_transaksi, 
                $tujuan_kunjungan, $hasil_kunjungan, $catatan_surveyor, $status_survey, 
                $foto_ktp_path, $foto_dokumentasi_path, $foto_bukti_path, $id); 
            
            if ($stmt->execute()) {
                log_activity($_SESSION['user_id'], 'update_survey', "Mengupdate survey: $kode_survey (ID: $id)");
                // Redirect to survey.php with success
                header("Location: survey.php?success=" . urlencode('Data survey (Nasabah) berhasil diupdate!'));
                exit();
            } else {
                $error = 'Gagal mengupdate data! ' . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Fallback to POST data if submission failed, otherwise use fetched data
$data = $survey_data;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kode_survey'])) {
    // Merge POST data with fetched data to retain file paths if upload failed
    $data = array_merge($survey_data, $_POST);
    // Set correct file paths after failed POST
    $data['foto_ktp'] = $foto_ktp_path; 
    $data['foto_dokumentasi'] = $foto_dokumentasi_path;
    $data['foto_bukti'] = $foto_bukti_path; // Set correct file path for foto_bukti
}


// Display error if data not found, and stop
if (!$survey_data && $id > 0) {
    echo '<div class="page-header"><h1>Edit Survey Gagal</h1><p class="breadcrumb">Home / Data Survey / Edit Gagal</p></div>';
    echo '<div class="alert alert-danger">' . $error . '</div>';
    include 'includes/footer.php';
    exit();
}

// If data exists, show form
if ($survey_data):
    // Format nilai_transaksi for display in input (without 'Rp ')
    $nilai_display = number_format($data['nilai_transaksi'], 0, ',', '.');
?>

<div class="page-header">
    <h1><?php echo $page_title; ?>: <?php echo htmlspecialchars($data['kode_survey']); ?></h1>
    <p class="breadcrumb">Home / Data Survey / Edit Data</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Form Edit Data Nasabah dan Kunjungan</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="edit_nasabah.php?id=<?php echo $id; ?>" id="surveyForm" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Kode Survey *</label>
                    <input type="text" name="kode_survey" id="kode_survey" 
                           class="form-control" value="<?php echo htmlspecialchars($data['kode_survey']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Kunjungan *</label>
                    <input type="date" name="tanggal_kunjungan" id="tanggal_kunjungan" 
                           class="form-control" value="<?php echo htmlspecialchars($data['tanggal_kunjungan']); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Nasabah *</label>
                    <input type="text" name="nama_nasabah" id="nama_nasabah" 
                           class="form-control" placeholder="Nama lengkap nasabah" required
                           value="<?php echo htmlspecialchars($data['nama_nasabah']); ?>">
                </div>
                <div class="form-group">
                    <label>NIK Nasabah *</label>
                    <input type="text" name="nik_nasabah" id="nik_nasabah" 
                           class="form-control" placeholder="16 digit NIK" maxlength="16" required
                           value="<?php echo htmlspecialchars($data['nik_nasabah']); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Alamat Nasabah *</label>
                <textarea name="alamat_nasabah" id="alamat_nasabah" 
                          class="form-control" placeholder="Alamat lengkap" required><?php echo htmlspecialchars($data['alamat_nasabah']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" name="no_telepon" id="no_telepon" 
                           class="form-control" placeholder="08xxxxxxxxxx"
                           value="<?php echo htmlspecialchars($data['no_telepon']); ?>">
                </div>
                <div class="form-group">
                    <label>Jenis Produk *</label>
                    <select name="jenis_produk" id="jenis_produk" class="form-control" required>
                        <option value="">Semua Produk</option>
                        <option value="Kupedes" <?php echo ($data['jenis_produk'] == 'Kupedes') ? 'selected' : ''; ?>>Kupedes</option>
                        <option value="Mulia Multimate" <?php echo ($data['jenis_produk'] == 'Mulia Multimate') ? 'selected' : ''; ?>>Mulia Multimate</option>
                        <option value="Kredit Usaha Rakyat" <?php echo ($data['jenis_produk'] == 'Kredit Usaha Rakyat') ? 'selected' : ''; ?>>Kredit Usaha Rakyat</option>
                        <option value="Arrum Ekspress Loan" <?php echo ($data['jenis_produk'] == 'Arrum Ekspress Loan') ? 'selected' : ''; ?>>Arrum Ekspress Loan</option>
                        <option value="Amanah" <?php echo ($data['jenis_produk'] == 'Amanah') ? 'selected' : ''; ?>>Amanah</option>
                        <option value="Krasida" <?php echo ($data['jenis_produk'] == 'Krasida') ? 'selected' : ''; ?>>Krasida</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Nilai Transaksi (Rp)</label>
                    <input type="text" name="nilai_transaksi" id="nilai_transaksi" 
                           class="form-control rupiah-input" placeholder="0"
                           value="<?php echo $nilai_display; ?>">
                </div>
                <div class="form-group">
                    <label>Tujuan Kunjungan *</label>
                    <select name="tujuan_kunjungan" id="tujuan_kunjungan" class="form-control" required>
                        <option value="">Pilih Tujuan</option>
                        <option value="Survey Awal" <?php echo ($data['tujuan_kunjungan'] == 'Survey Awal') ? 'selected' : ''; ?>>Survey Awal</option>
                        <option value="Follow Up" <?php echo ($data['tujuan_kunjungan'] == 'Follow Up') ? 'selected' : ''; ?>>Follow Up</option>
                        <option value="Penagihan" <?php echo ($data['tujuan_kunjungan'] == 'Penagihan') ? 'selected' : ''; ?>>Penagihan</option>
                        <option value="Verifikasi Data" <?php echo ($data['tujuan_kunjungan'] == 'Verifikasi Data') ? 'selected' : ''; ?>>Verifikasi Data</option>
                        <option value="Lainnya" <?php echo ($data['tujuan_kunjungan'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Foto KTP (Opsional)</label>
                    <?php if (!empty($data['foto_ktp'])): ?>
                        <p>File saat ini: <a href="<?php echo htmlspecialchars($data['foto_ktp']); ?>" target="_blank">Lihat Foto</a></p>
                    <?php endif; ?>
                    <input type="file" name="foto_ktp" class="form-control" accept="image/*">
                    <small class="text-muted">Upload file baru untuk mengganti file lama. Maks. 5MB.</small>
                </div>
                <div class="form-group">
                    <label>Foto Dokumentasi</label>
                    <?php if (!empty($data['foto_dokumentasi'])): ?>
                        <p>File saat ini: <a href="<?php echo htmlspecialchars($data['foto_dokumentasi']); ?>" target="_blank">Lihat Foto</a></p>
                    <?php endif; ?>
                    <input type="file" name="foto_dokumentasi" class="form-control" accept="image/*">
                    <small class="text-muted">Upload file baru untuk mengganti file lama. Maks. 5MB.</small>
                </div>
                <div class="form-group">
                    <label>Foto Bukti</label>
                    <?php if (!empty($data['foto_bukti'])): ?>
                        <p>File saat ini: <a href="<?php echo htmlspecialchars($data['foto_bukti']); ?>" target="_blank">Lihat Foto</a></p>
                    <?php endif; ?>
                    <input type="file" name="foto_bukti" class="form-control" accept="image/*">
                    <small class="text-muted">Upload file baru untuk mengganti file lama. Maks. 5MB.</small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Hasil Kunjungan *</label>
                    <select name="hasil_kunjungan" id="hasil_kunjungan" class="form-control" required>
                        <option value="">Pilih Hasil</option>
                        <option value="Berhasil Ditemui" <?php echo ($data['hasil_kunjungan'] == 'Berhasil Ditemui') ? 'selected' : ''; ?>>Berhasil Ditemui</option>
                        <option value="Tidak Ada di Tempat" <?php echo ($data['hasil_kunjungan'] == 'Tidak Ada di Tempat') ? 'selected' : ''; ?>>Tidak Ada di Tempat</option>
                        <option value="Pindah Alamat" <?php echo ($data['hasil_kunjungan'] == 'Pindah Alamat') ? 'selected' : ''; ?>>Pindah Alamat</option>
                        <option value="Menolak" <?php echo ($data['hasil_kunjungan'] == 'Menolak') ? 'selected' : ''; ?>>Menolak</option>
                        <option value="Proses Lanjut" <?php echo ($data['hasil_kunjungan'] == 'Proses Lanjut') ? 'selected' : ''; ?>>Proses Lanjut</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status Survey *</label>
                    <select name="status_survey" id="status_survey" class="form-control" required>
                        <option value="Draft" <?php echo ($data['status_survey'] == 'Draft') ? 'selected' : ''; ?>>Draft</option>
                        <option value="Submitted" <?php echo ($data['status_survey'] == 'Submitted') ? 'selected' : ''; ?>>Submitted</option>
                        <option value="Approved" <?php echo ($data['status_survey'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                        <option value="Rejected" <?php echo ($data['status_survey'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Catatan Surveyor</label>
                <textarea name="catatan_surveyor" id="catatan_surveyor" 
                          class="form-control" placeholder="Catatan tambahan"><?php echo htmlspecialchars($data['catatan_surveyor']); ?></textarea>
            </div>
            
            <div class="modal-footer" style="padding: 0; margin-top: 20px; border-top: none; justify-content: space-between;">
                <a href="survey.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                <button type="submit" class="btn btn-warning"><i class="fas fa-edit"></i> Update Data</button>
            </div>
        </form>
    </div>
</div>

<?php 
endif; // End if ($survey_data)
include 'includes/footer.php'; 
?>