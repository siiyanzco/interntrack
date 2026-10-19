<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['admin', 'pembimbing'], $base);
$judul = 'Laporan';
include __DIR__ . '/../config/header.php';
?>
<div class="menu-grid">
    <a href="siswa.php">Laporan Data Siswa PKL</a>
    <a href="kehadiran.php">Laporan Kehadiran</a>
    <a href="monitoring.php">Laporan Monitoring</a>
</div>
<?php include __DIR__ . '/../config/footer.php'; ?>
