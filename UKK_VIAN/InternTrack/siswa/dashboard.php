<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['siswa'], $base);
$judul = 'Dashboard Siswa';
$s = siswa_login($pdo);
$k = hitung_kehadiran($pdo, $s['id']);
include __DIR__ . '/../config/header.php';
?>
<div class="card">
    <p>Selamat datang, <b><?= e($s['nama']) ?></b> (NIS <?= e($s['nis']) ?>).</p>
    <p>Kehadiran: <b><?= $k['persen'] ?>%</b> &mdash; <span class="badge <?= kelas_status($k['status']) ?>"><?= e($k['status']) ?></span></p>
</div>
<div class="menu-grid">
    <a href="profil.php">Profil</a>
    <a href="tempat.php">Tempat PKL</a>
    <a href="jurnal.php">Jurnal Kegiatan</a>
    <a href="absensi.php">Kehadiran</a>
    <a href="status.php">Status PKL</a>
    <a href="monitoring.php">Hasil Monitoring</a>
</div>
<?php include __DIR__ . '/../config/footer.php'; ?>
