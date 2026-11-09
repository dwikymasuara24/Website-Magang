<?php
$page_title = 'Dashboard';
include 'includes/header.php';

// Statistik
$total_survey = $conn->query("SELECT COUNT(*) as total FROM survey_kunjungan")->fetch_assoc()['total'];
$survey_hari_ini = $conn->query("SELECT COUNT(*) as total FROM survey_kunjungan WHERE DATE(tanggal_kunjungan) = CURDATE()")->fetch_assoc()['total'];
$survey_bulan_ini = $conn->query("SELECT COUNT(*) as total FROM survey_kunjungan WHERE MONTH(tanggal_kunjungan) = MONTH(CURDATE()) AND YEAR(tanggal_kunjungan) = YEAR(CURDATE())")->fetch_assoc()['total'];
$survey_berhasil = $conn->query("SELECT COUNT(*) as total FROM survey_kunjungan WHERE hasil_kunjungan = 'Berhasil Ditemui'")->fetch_assoc()['total'];

// Survey terbaru
$survey_terbaru = $conn->query("
    SELECT sk.*, u.nama_lengkap as nama_surveyor 
    FROM survey_kunjungan sk 
    LEFT JOIN users u ON sk.surveyor_id = u.id 
    ORDER BY sk.created_at DESC 
    LIMIT 5
");

// Data per status
$status_stats = $conn->query("
    SELECT status_survey, COUNT(*) as jumlah 
    FROM survey_kunjungan 
    GROUP BY status_survey
");

// Data per jenis produk
$produk_stats = $conn->query("
    SELECT jenis_produk, COUNT(*) as jumlah 
    FROM survey_kunjungan 
    GROUP BY jenis_produk 
    ORDER BY jumlah DESC 
    LIMIT 5
");
?>

<div class="page-header">
    <h1>Dashboard</h1>
    <p class="breadcrumb">Home / Dashboard</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-clipboard-check"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($total_survey); ?></h3>
            <p>Total Survey</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($survey_hari_ini); ?></h3>
            <p>Survey Hari Ini</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($survey_bulan_ini); ?></h3>
            <p>Survey Bulan Ini</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($survey_berhasil); ?></h3>
            <p>Survey Berhasil</p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
    <div class="card">
        <div class="card-header">
            <h3>Status Survey</h3>
        </div>
        <div class="card-body">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th style="text-align: right;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $status_stats->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php
                            $badge_class = '';
                            switch($row['status_survey']) {
                                case 'Approved': $badge_class = 'badge-success'; break;
                                case 'Submitted': $badge_class = 'badge-info'; break;
                                case 'Draft': $badge_class = 'badge-secondary'; break;
                                case 'Rejected': $badge_class = 'badge-danger'; break;
                            }
                            ?>
                            <a href="survey.php?status=<?php echo urlencode($row['status_survey']); ?>" 
                               class="badge <?php echo $badge_class; ?>" style="text-decoration: none;">
                                <?php echo $row['status_survey']; ?>
                            </a>
                        </td>
                        <td style="text-align: right; font-weight: 600;">
                            <?php echo $row['jumlah']; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>Produk Populer</h3>
        </div>
        <div class="card-body">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Jenis Produk</th>
                        <th style="text-align: right;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $produk_stats->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <a href="survey.php?produk=<?php echo urlencode($row['jenis_produk']); ?>" style="text-decoration: none; font-weight: 600; color: #333;">
                                <?php echo $row['jenis_produk']; ?>
                            </a>
                        </td>
                        <td style="text-align: right; font-weight: 600;">
                            <?php echo $row['jumlah']; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Survey Terbaru</h3>
        <a href="survey.php" class="btn btn-success btn-sm">Lihat Semua</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Kode Survey</th>
                        <th>Tanggal</th>
                        <th>Nama Nasabah</th>
                        <th>Produk</th>
                        <th>Hasil</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($survey_terbaru->num_rows > 0): ?>
                        <?php while ($row = $survey_terbaru->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['kode_survey']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_kunjungan'])); ?></td>
                            <td><?php echo $row['nama_nasabah']; ?></td>
                            <td><?php echo $row['jenis_produk']; ?></td>
                            <td>
                                <?php
                                $badge_class = '';
                                switch($row['hasil_kunjungan']) {
                                    case 'Berhasil Ditemui': $badge_class = 'badge-success'; break;
                                    case 'Tidak Ada di Tempat': $badge_class = 'badge-warning'; break;
                                    case 'Menolak': $badge_class = 'badge-danger'; break;
                                    default: $badge_class = 'badge-info';
                                }
                                ?>
                                <a href="survey.php?hasil=<?php echo urlencode($row['hasil_kunjungan']); ?>" 
                                   class="badge <?php echo $badge_class; ?>" style="text-decoration: none;">
                                    <?php echo $row['hasil_kunjungan']; ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                $status_class = '';
                                switch($row['status_survey']) {
                                    case 'Approved': $status_class = 'badge-success'; break;
                                    case 'Submitted': $status_class = 'badge-info'; break;
                                    case 'Draft': $status_class = 'badge-secondary'; break;
                                    case 'Rejected': $status_class = 'badge-danger'; break;
                                }
                                ?>
                                <a href="survey.php?status=<?php echo urlencode($row['status_survey']); ?>" 
                                   class="badge <?php echo $status_class; ?>" style="text-decoration: none;">
                                    <?php echo $row['status_survey']; ?>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #999;">
                                Belum ada data survey
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>