<?php
session_start();
require_once '../config/database.php';
check_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID Survey tidak valid.");
}

$id = intval($_GET['id']);

// Ambil Data untuk satu record (termasuk nama surveyor)
$stmt = $conn->prepare("
    SELECT sk.*, u.nama_lengkap as nama_surveyor 
    FROM survey_kunjungan sk 
    LEFT JOIN users u ON sk.surveyor_id = u.id 
    WHERE sk.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    die("Data survey tidak ditemukan.");
}

$data = $result->fetch_assoc();
$stmt->close();

// Log aktivitas
log_activity($_SESSION['user_id'], 'export_pdf_detail', 'Export detail survey ' . $data['kode_survey'] . ' ke PDF');

$nilai_rupiah = format_rupiah($data['nilai_transaksi']);

// Tentukan kelas CSS untuk Status
$status_class = '';
$status_color = '';
switch($data['status_survey']) {
    case 'Approved': 
        $status_class = 'badge-success'; 
        $status_color = '#d4edda';
        break;
    case 'Submitted': 
        $status_class = 'badge-info'; 
        $status_color = '#d1ecf1';
        break;
    case 'Draft': 
        $status_class = 'badge-secondary'; 
        $status_color = '#e2e3e5';
        break;
    case 'Rejected': 
        $status_class = 'badge-danger'; 
        $status_color = '#f8d7da';
        break;
}

// Tentukan kelas CSS untuk Hasil Kunjungan
$hasil_class = '';
$hasil_color = '';
switch($data['hasil_kunjungan']) {
    case 'Berhasil Ditemui': 
        $hasil_class = 'badge-success'; 
        $hasil_color = '#d4edda';
        break;
    case 'Tidak Ada di Tempat': 
        $hasil_class = 'badge-warning'; 
        $hasil_color = '#fff3cd';
        break;
    case 'Menolak': 
        $hasil_class = 'badge-danger'; 
        $hasil_color = '#f8d7da';
        break;
    default: 
        $hasil_class = 'badge-info';
        $hasil_color = '#d1ecf1';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Survey - <?php echo htmlspecialchars($data['kode_survey']); ?></title>
    <style>
        @page {
            size: A4 portrait;
            /* Margin sekecil mungkin agar muat 1 halaman */
            margin: 8mm; 
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px; 
            color: #333;
            line-height: 1.5; 
            background: #fff;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto; 
            padding: 10px; 
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px; 
            border-bottom: 4px solid #28a745;
            padding-bottom: 10px; 
        }
        
        .header .logo {
            font-size: 32px;
            color: #28a745;
            margin-bottom: 5px;
        }
        
        .header h2 {
            margin: 10px 0 5px;
            color: #28a745;
            font-size: 20px;
            font-weight: bold;
        }
        
        .header h3 {
            margin: 5px 0;
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }
        
        .header .meta {
            font-size: 11px;
            color: #666;
            margin-top: 10px;
        }
        
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .detail-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .detail-table td { 
            padding: 8px 10px; 
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        .detail-table .label { 
            font-weight: 600; 
            width: 35%; 
            background: #e9ecef;
            color: #495057;
        }
        
        .detail-table .value {
            color: #212529;
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            text-align: center;
        }
        
        .section-title {
            margin-top: 15px; 
            margin-bottom: 8px; 
            color: #28a745;
            border-bottom: 2px solid #28a745;
            padding-bottom: 5px; 
            font-size: 14px; 
            font-weight: bold;
        }
        
        .photo-grid {
            display: table;
            width: 100%;
            margin-top: 8px;
            border-spacing: 5px; 
        }
        
        .photo-item {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            text-align: center;
        }
        
        .photo-item h4 {
            font-size: 12px;
            margin-bottom: 3px; 
            color: #495057;
            font-weight: 600;
        }
        
        .photo-box {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 3px; 
            background: #f8f9fa;
            min-height: 120px; 
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .photo-box img {
            max-width: 100%;
            max-height: 120px; 
            height: auto;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .photo-box .no-photo {
            color: #999;
            font-size: 10px;
            font-style: italic;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px; 
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px; 
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            transition: all 0.3s;
            z-index: 1000;
        }

        .signature-section {
             margin-top: 15px; 
             margin-bottom: 10px;
             page-break-inside: avoid !important; 
        }
        
        /* [PERBAIKAN TANDA TANGAN] */
        .signature-space {
            /* Menambah tinggi ruang tanda tangan tanpa garis */
            height: 40px; 
            display: block; 
            margin-bottom: 3px; 
        }

        .signature-section .printed-name {
            /* Warna hitam untuk teks cetak */
            color: #333; 
            font-weight: 500;
        }
        
        /* Print styles */
        @media print {
            .no-print { 
                display: none !important; 
            }
            
            body {
                margin: 0;
                padding: 0;
                font-size: 10.5px; 
            }
            
            .container {
                max-width: 100%;
                padding: 0mm; 
                margin: 0 auto;
            }
            
            /* Memastikan semua blok penting tidak terpisah */
            .detail-table, .photo-grid, .signature-section table, .signature-section {
                page-break-inside: avoid !important;
            }

            .signature-section {
                 margin-top: 10px !important;
                 margin-bottom: 5px !important;
            }

            .signature-section .signature-space {
                 /* Mengurangi tinggi spasi di mode print sedikit agar muat */
                 height: 125px; 
            }
        }
    </style>
</head>
<body>
    
    <button onclick="window.print()" class="btn-print no-print">
        <i class="fas fa-print"></i> Cetak / Save PDF
    </button>
    
    <div class="no-print" style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; padding: 15px; margin: 20px; text-align: center;">
        <p style="margin: 0; color: #856404; font-weight: 600;">
            💡 <strong>Cara Download PDF:</strong> Klik tombol "Cetak / Save PDF" di atas, 
            lalu pilih <strong>"Save as PDF"</strong> atau <strong>"Microsoft Print to PDF"</strong> sebagai printer tujuan.
        </p>
    </div>
    
    <div class="container">
        <div class="header">
            <div class="logo">🏦</div>
            <h2>DATA SURVEY KUNJUNGAN</h2>
            <h3><?php echo htmlspecialchars($data['kode_survey']); ?></h3>
            <div style="font-size: 14px; font-weight: 600; margin: 10px 0;">PT PEGADAIAN (PERSERO)</div>
            <div class="meta">
                Dicetak oleh: <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong> 
                pada <strong><?php echo date('d F Y, H:i:s'); ?> WIB</strong>
            </div>
        </div>
        
        <h4 class="section-title">📋 Informasi Survey</h4>
        <table class="detail-table">
            <tr>
                <td class="label">Kode Survey</td>
                <td class="value"><strong><?php echo htmlspecialchars($data['kode_survey']); ?></strong></td>
            </tr>
            <tr>
                <td class="label">Tanggal Kunjungan</td>
                <td class="value"><?php echo date('d F Y', strtotime($data['tanggal_kunjungan'])); ?></td>
            </tr>
            <tr>
                <td class="label">Surveyor</td>
                <td class="value"><?php echo htmlspecialchars($data['nama_surveyor']); ?></td>
            </tr>
            <tr>
                <td class="label">Tanggal Input Data</td>
                <td class="value"><?php echo date('d F Y, H:i', strtotime($data['created_at'])); ?> WIB</td>
            </tr>
        </table>
        
        <h4 class="section-title">👤 Data Nasabah</h4>
        <table class="detail-table">
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="value"><strong><?php echo htmlspecialchars($data['nama_nasabah']); ?></strong></td>
            </tr>
            <tr>
                <td class="label">NIK</td>
                <td class="value"><?php echo htmlspecialchars($data['nik_nasabah']); ?></td>
            </tr>
            <tr>
                <td class="label">Alamat Lengkap</td>
                <td class="value"><?php echo nl2br(htmlspecialchars($data['alamat_nasabah'])); ?></td>
            </tr>
            <tr>
                <td class="label">No. Telepon</td>
                <td class="value"><?php echo htmlspecialchars($data['no_telepon'] ?: '-'); ?></td>
            </tr>
        </table>
        
        <h4 class="section-title">💰 Data Transaksi</h4>
        <table class="detail-table">
            <tr>
                <td class="label">Jenis Produk</td>
                <td class="value"><strong><?php echo htmlspecialchars($data['jenis_produk']); ?></strong></td>
            </tr>
            <tr>
                <td class="label">Nilai Transaksi</td>
                <td class="value"><strong style="color: #28a745; font-size: 14px;"><?php echo $nilai_rupiah; ?></strong></td>
            </tr>
            <tr>
                <td class="label">Tujuan Kunjungan</td>
                <td class="value"><?php echo htmlspecialchars($data['tujuan_kunjungan']); ?></td>
            </tr>
        </table>
        
        <h4 class="section-title">✅ Hasil Kunjungan</h4>
        <table class="detail-table">
            <tr>
                <td class="label">Hasil Kunjungan</td>
                <td class="value">
                    <span class="badge <?php echo $hasil_class; ?>">
                        <?php echo htmlspecialchars($data['hasil_kunjungan']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label">Status Survey</td>
                <td class="value">
                    <span class="badge <?php echo $status_class; ?>">
                        <?php echo htmlspecialchars($data['status_survey']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label">Catatan Surveyor</td>
                <td class="value"><?php echo nl2br(htmlspecialchars($data['catatan_surveyor'] ?: 'Tidak ada catatan tambahan')); ?></td>
            </tr>
        </table>

        <h4 class="section-title">📷 Dokumentasi Foto</h4>
        <div class="photo-grid">
            <div class="photo-item">
                <h4>Foto KTP</h4>
                <div class="photo-box">
                    <?php 
                    $ktp_path = $data['foto_ktp'] ?? '';
                    if (!empty($ktp_path) && file_exists('../' . $ktp_path)): 
                    ?>
                        <img src="../<?php echo htmlspecialchars($ktp_path); ?>" alt="Foto KTP">
                    <?php else: ?>
                        <div class="no-photo">📄 Tidak ada foto KTP</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="photo-item">
                <h4>Foto Dokumentasi</h4>
                <div class="photo-box">
                    <?php 
                    $dok_path = $data['foto_dokumentasi'] ?? '';
                    if (!empty($dok_path) && file_exists('../' . $dok_path)): 
                    ?>
                        <img src="../<?php echo htmlspecialchars($dok_path); ?>" alt="Foto Dokumentasi">
                    <?php else: ?>
                        <div class="no-photo">📄 Tidak ada foto dokumentasi</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="photo-item">
                <h4>Foto Bukti</h4>
                <div class="photo-box">
                    <?php 
                    $bukti_path = $data['foto_bukti'] ?? '';
                    if (!empty($bukti_path) && file_exists('../' . $bukti_path)): 
                    ?>
                        <img src="../<?php echo htmlspecialchars($bukti_path); ?>" alt="Foto Bukti">
                    <?php else: ?>
                        <div class="no-photo">📄 Tidak ada foto bukti</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="signature-section">
            <table style="width: 100%; border-collapse: collapse; text-align: center; margin: 0 auto; ">
                <tr>
                    <td style="width: 33%;">
                        <p style="font-size: 11px; margin-bottom: 5px;">Disetujui Oleh,</p>
                        <p style="font-weight: bold; margin-bottom: 5px;">Pimpinan</p> 
                        <div class="signature-space"></div> <p class="printed-name" style="font-size: 11px; margin: 0;">(...............................................)</p> 
                        <p style="font-size: 9px; margin-top: 5px;">Nama & Jabatan</p>
                    </td>
                    <td style="width: 33%;">
                        <p style="font-size: 11px; margin-bottom: 5px;">Dibuat Oleh,</p>
                        <p style="font-weight: bold; margin-bottom: 5px;">Surveyor</p>
                        <div class="signature-space"></div> <p class="printed-name" style="font-size: 11px; margin: 0;">
                            (<?php echo htmlspecialchars($data['nama_surveyor'] ?? 'Nama Surveyor'); ?>)
                        </p>
                        <p style="font-size: 9px; margin-top: 5px;">Nama Lengkap</p>
                    </td>
                    <td style="width: 34%;">
                        <p style="font-size: 11px; margin-bottom: 5px;">Disaksikan Oleh,</p>
                        <p style="font-weight: bold; margin-bottom: 5px;">Nasabah</p>
                        <div class="signature-space"></div> <p class="printed-name" style="font-size: 11px; margin: 0;">
                            (<?php echo htmlspecialchars($data['nama_nasabah'] ?? 'Nama Nasabah'); ?>)
                        </p>
                        <p style="font-size: 9px; margin-top: 5px;">Nama Lengkap</p>
                    </td>
                </tr>
            </table>
        </div>
        <div class="footer">
    
</body>
</html>