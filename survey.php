<?php
$page_title = 'Data Survey';
include 'includes/header.php';

$success = '';
$error = '';

// Success message after redirect
if (isset($_GET['success'])) {
    $success = clean_input($_GET['success']);
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {

    $id = intval($_GET['id']);

    // Validasi ID
    if ($id > 0) {

        // 1. Ambil data file untuk dihapus
        $stmt_select = $conn->prepare("SELECT foto_ktp, foto_dokumentasi, foto_bukti FROM survey_kunjungan WHERE id = ?");
        $stmt_select->bind_param("i", $id);
        $stmt_select->execute();
        $file_paths = $stmt_select->get_result()->fetch_assoc();
        $stmt_select->close();

        // ==============================================================
        // [PERBAIKAN DELETE] Hapus Log Aktivitas yang terhubung
        // Ini penting untuk menghindari Foreign Key Constraint Error
        $stmt_log_delete = $conn->prepare("DELETE FROM activity_logs WHERE description LIKE ?");
        
        if ($stmt_log_delete !== false) {
             // Cari log yang mencatat ID survey ini
             $log_desc_pattern = "%ID: " . $id . "%"; 
             $stmt_log_delete->bind_param("s", $log_desc_pattern);
             $stmt_log_delete->execute(); 
             $stmt_log_delete->close();
        }
        // ==============================================================

        // 2. Hapus record utama
        $stmt_delete = $conn->prepare("DELETE FROM survey_kunjungan WHERE id = ?");
        
        // Cek apakah prepare DELETE berhasil
        if ($stmt_delete === false) {
            $error = 'Gagal menyiapkan query DELETE: ' . $conn->error;
        } else {
            $stmt_delete->bind_param("i", $id);

            if ($stmt_delete->execute()) {

                // 3. Hapus file jika ada
                if ($file_paths) {

                    $files = [
                        $file_paths['foto_ktp'],
                        $file_paths['foto_dokumentasi'],
                        $file_paths['foto_bukti']
                    ];

                    foreach ($files as $file) {
                        if (!empty($file)) {
                            // Asumsi path relatif dari file saat ini (babi/)
                            $real_path = __DIR__ . '/' . $file;
                            if (file_exists($real_path)) {
                                @unlink($real_path); // @ untuk menekan error jika gagal hapus
                            }
                        }
                    }
                }

                log_activity($_SESSION['user_id'], 'delete_survey', "Menghapus survey ID: $id");
                $success = 'Data survey berhasil dihapus!';
            } else {
                // Menangkap error Foreign Key yang mungkin masih terjadi
                $error = 'Gagal menghapus data! Detail Error: ' . $conn->error;
            }
            $stmt_delete->close();
        }
    }
}

// Filter & Search
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
// Menggunakan 'jenis_produk' dari URL untuk filter produk
$filter_produk = isset($_GET['jenis_produk']) ? clean_input($_GET['jenis_produk']) : ''; 
$filter_hasil = isset($_GET['hasil']) ? clean_input($_GET['hasil']) : '';

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

if (!empty($filter_hasil)) {
    $where_clauses[] = "hasil_kunjungan = '$filter_hasil'";
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total_query = $conn->query("SELECT COUNT(*) as total FROM survey_kunjungan $where_sql");
$total_records = $total_query->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Get Data
$query = "SELECT sk.*, u.nama_lengkap as nama_surveyor 
          FROM survey_kunjungan sk 
          LEFT JOIN users u ON sk.surveyor_id = u.id 
          $where_sql 
          ORDER BY sk.created_at DESC 
          LIMIT $limit OFFSET $offset";
$result = $conn->query($query);
?>

<div class="page-header">
    <h1>Data Survey Kunjungan</h1>
    <p class="breadcrumb">Home / Data Survey</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php if (strpos($error, 'Foreign Key') !== false || strpos($error, 'a foreign key constraint fails') !== false): ?>
        <div class="alert alert-info">
            <strong>Penting:</strong> Jika Anda masih melihat error Foreign Key setelah perbaikan ini, kemungkinan ada tabel lain selain `activity_logs` yang juga terhubung ke tabel `survey_kunjungan` di database Anda.
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="GET" action="survey.php" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">

            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label>Cari Data</label>
                <input type="text" name="search" class="form-control"
                    placeholder="Cari nama, kode, NIK..."
                    value="<?php echo $search; ?>">
            </div>

            <div class="form-group" style="min-width: 150px; margin-bottom: 0;">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="Draft" <?php echo $filter_status == 'Draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="Submitted" <?php echo $filter_status == 'Submitted' ? 'selected' : ''; ?>>Submitted</option>
                    <option value="Approved" <?php echo $filter_status == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Rejected" <?php echo $filter_status == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>

            <div class="form-group" style="min-width: 150px; margin-bottom: 0;">
                <label>Produk</label>
                <select name="jenis_produk" class="form-control">
                    <option value="">Semua Produk</option>
                    <option value="Kupedes" <?php echo $filter_produk == 'Kupedes' ? 'selected' : ''; ?>>Kupedes</option>
                    <option value="Mulia Multimate" <?php echo $filter_produk == 'Mulia Multimate' ? 'selected' : ''; ?>>Mulia Multimate</option>
                    <option value="Kredit Usaha Rakyat" <?php echo $filter_produk == 'Kredit Usaha Rakyat' ? 'selected' : ''; ?>>Kredit Usaha Rakyat</option>
                    <option value="Arrum Ekspress Loan" <?php echo $filter_produk == 'Arrum Ekspress Loan' ? 'selected' : ''; ?>>Arrum Ekspress Loan</option>
                    <option value="Amanah" <?php echo $filter_produk == 'Amanah' ? 'selected' : ''; ?>>Amanah</option>
                    <option value="Krasida" <?php echo $filter_produk == 'Krasida' ? 'selected' : ''; ?>>Krasida</option>
                </select>
            </div>

            <div class="form-group" style="min-width: 150px; margin-bottom: 0;">
                <label>Hasil Kunjungan</label>
                <select name="hasil" class="form-control">
                    <option value="">Semua Hasil</option>
                    <option value="Berhasil Ditemui" <?php echo $filter_hasil == 'Berhasil Ditemui' ? 'selected' : ''; ?>>Berhasil Ditemui</option>
                    <option value="Tidak Ada di Tempat" <?php echo $filter_hasil == 'Tidak Ada di Tempat' ? 'selected' : ''; ?>>Tidak Ada di Tempat</option>
                    <option value="Pindah Alamat" <?php echo $filter_hasil == 'Pindah Alamat' ? 'selected' : ''; ?>>Pindah Alamat</option>
                    <option value="Menolak" <?php echo $filter_hasil == 'Menolak' ? 'selected' : ''; ?>>Menolak</option>
                    <option value="Proses Lanjut" <?php echo $filter_hasil == 'Proses Lanjut' ? 'selected' : ''; ?>>Proses Lanjut</option>
                    <option value="Lainnya" <?php echo $filter_hasil == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">
                <i class="fas fa-search"></i> Cari
            </button>

            <a href="survey.php" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Reset
            </a>

        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Daftar Survey (<?php echo number_format($total_records); ?> data)</h3>
        <div style="display: flex; gap: 10px;">
            <a href="tambah_nasabah.php" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Tambah Data
            </a>
            <a href="exports/export_excel.php?<?php echo http_build_query(['search'=>$search,'status'=>$filter_status,'produk'=>$filter_produk,'hasil'=>$filter_hasil]); ?>" 
               class="btn btn-success btn-sm">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="exports/export_pdf.php?<?php echo http_build_query(['search'=>$search,'status'=>$filter_status,'produk'=>$filter_produk,'hasil'=>$filter_hasil]); ?>" 
               class="btn btn-danger btn-sm" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Survey</th>
                        <th>Tanggal</th>
                        <th>Nama Nasabah</th>
                        <th>NIK</th>
                        <th>Produk</th>
                        <th>Nilai</th>
                        <th>Hasil</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($result->num_rows > 0): ?>

                    <?php $no = $offset + 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo $row['kode_survey']; ?></strong></td>
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_kunjungan'])); ?></td>
                        <td><?php echo $row['nama_nasabah']; ?></td>
                        <td><?php echo $row['nik_nasabah']; ?></td>

                        <td>
                            <a href="survey.php?jenis_produk=<?php echo urlencode($row['jenis_produk']); ?>" style="text-decoration: none; font-weight: 600; color: #333;">
                                <?php echo $row['jenis_produk']; ?>
                            </a>
                        </td>

                        <td><?php echo format_rupiah($row['nilai_transaksi']); ?></td>

                        <td>
                            <?php
                                switch($row['hasil_kunjungan']) {
                                    case 'Berhasil Ditemui': $badge = 'badge-success'; break;
                                    case 'Tidak Ada di Tempat': $badge = 'badge-warning'; break;
                                    case 'Menolak': $badge = 'badge-danger'; break;
                                    default: $badge = 'badge-info';
                                }
                            ?>
                            <a href="survey.php?hasil=<?php echo urlencode($row['hasil_kunjungan']); ?>" class="badge <?php echo $badge; ?>" style="text-decoration: none;">
                                <?php echo $row['hasil_kunjungan']; ?>
                            </a>
                        </td>

                        <td>
                            <?php
                                switch($row['status_survey']) {
                                    case 'Approved': $status_badge = 'badge-success'; break;
                                    case 'Submitted': $status_badge = 'badge-info'; break;
                                    case 'Draft': $status_badge = 'badge-secondary'; break;
                                    case 'Rejected': $status_badge = 'badge-danger'; break;
                                }
                            ?>
                            <a href="survey.php?status=<?php echo urlencode($row['status_survey']); ?>" class="badge <?php echo $status_badge; ?>" style="text-decoration: none;">
                                <?php echo $row['status_survey']; ?>
                            </a>
                        </td>

                        <td>
                            <div class="action-buttons">
                                <a href="pratinjau.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm" title="Lihat Detail (Pratinjau)">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit_nasabah.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm" title="Edit Data (Update Nasabah)">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center; color: #999;">
                            Tidak ada data survey
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

        </div>

        <?php if ($total_pages > 1): ?>
        <div style="margin-top: 20px; display: flex; justify-content: center; gap: 5px;">

            <?php if ($page > 1): ?>
                <a class="btn btn-secondary btn-sm"
                   href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>&status=<?php echo $filter_status; ?>&jenis_produk=<?php echo $filter_produk; ?>&hasil=<?php echo $filter_hasil; ?>">Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a class="btn <?php echo $i == $page ? 'btn-success' : 'btn-secondary'; ?> btn-sm"
                   href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&status=<?php echo $filter_status; ?>&jenis_produk=<?php echo $filter_produk; ?>&hasil=<?php echo $filter_hasil; ?>">
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a class="btn btn-secondary btn-sm"
                   href="?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>&status=<?php echo $filter_status; ?>&jenis_produk=<?php echo $filter_produk; ?>&hasil=<?php echo $filter_hasil; ?>">Next</a>
            <?php endif; ?>

        </div>
        <?php endif; ?>

    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function confirmDelete(id) {
    if (confirm("Yakin ingin menghapus data ini? Semua file dan log terkait juga akan dihapus.")) {
        window.location.href = "survey.php?action=delete&id=" + id;
    }
}
</script>