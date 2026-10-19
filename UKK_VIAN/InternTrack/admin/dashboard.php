<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['admin', 'pembimbing'], $base);
$judul = 'Dashboard';

// Semua angka diambil dari database
$total_siswa  = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
$perusahaan   = $pdo->query("SELECT COUNT(*) FROM perusahaan")->fetchColumn();
$pembimbing   = $pdo->query("SELECT COUNT(*) FROM pembimbing")->fetchColumn();
$pkl_aktif    = $pdo->query("SELECT COUNT(*) FROM penempatan WHERE status = 'Aktif'")->fetchColumn();
$pkl_selesai  = $pdo->query("SELECT COUNT(*) FROM penempatan WHERE status = 'Selesai'")->fetchColumn();
$jurnal_hari  = $pdo->query("SELECT COUNT(*) FROM jurnal WHERE tanggal = CURDATE()")->fetchColumn();

$perlu = 0;
foreach (rekap_kehadiran($pdo) as $r) {
    if ($r['total'] > 0 && $r['persen'] < 75) {
        $perlu++;
    }
}
include __DIR__ . '/../config/header.php';
?>
<div class="stats">
    <div class="stat"><div class="angka"><?= $total_siswa ?></div>Total Siswa PKL</div>
    <div class="stat"><div class="angka"><?= $perusahaan ?></div>Perusahaan</div>
    <div class="stat"><div class="angka"><?= $pembimbing ?></div>Pembimbing</div>
    <div class="stat"><div class="angka"><?= $pkl_aktif ?></div>PKL Aktif</div>
    <div class="stat"><div class="angka"><?= $pkl_selesai ?></div>PKL Selesai</div>
</div>
<div class="stats">
    <div class="stat"><div class="angka"><?= $jurnal_hari ?></div>Jurnal Hari Ini</div>
    <div class="stat warn"><div class="angka"><?= $perlu ?></div>Siswa Perlu Perhatian (kehadiran &lt; 75%)</div>
</div>
<?php include __DIR__ . '/../config/footer.php'; ?>
