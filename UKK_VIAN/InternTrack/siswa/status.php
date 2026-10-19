<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['siswa'], $base);
$judul = 'Status PKL';
$s = siswa_login($pdo);
$k = hitung_kehadiran($pdo, $s['id']);
$st = $pdo->prepare("SELECT status FROM penempatan WHERE siswa_id = ?");
$st->execute([$s['id']]);
$status_pen = $st->fetchColumn() ?: 'Belum ditempatkan';
include __DIR__ . '/../config/header.php';
?>
<div class="card">
    <table class="detail">
        <tr><td>Status Penempatan</td><td><?= e($status_pen) ?></td></tr>
        <tr><td>Total Hari</td><td><?= $k['total'] ?></td></tr>
        <tr><td>Hadir</td><td><?= $k['hadir'] ?></td></tr>
        <tr><td>Persentase Kehadiran</td><td><?= $k['hadir'] ?> / <?= $k['total'] ?> x 100 = <b><?= $k['persen'] ?>%</b></td></tr>
        <tr><td>Status Perkembangan PKL</td><td><span class="badge <?= kelas_status($k['status']) ?>"><?= e($k['status']) ?></span></td></tr>
    </table>
    <p style="font-size:13px;color:#555">Aturan: &ge;90% Sangat Baik | 75-89% Baik | 60-74% Perlu Perhatian | &lt;60% Perlu Pembinaan</p>
</div>
<?php include __DIR__ . '/../config/footer.php'; ?>
