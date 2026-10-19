<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['admin', 'pembimbing'], $base);
$judul = 'Laporan Monitoring';
$rows = $pdo->query("SELECT m.*, s.nis, s.nama, pb.nama AS nama_pembimbing
                     FROM monitoring m JOIN siswa s ON s.id = m.siswa_id LEFT JOIN pembimbing pb ON pb.id = m.pembimbing_id
                     ORDER BY s.nama, m.tanggal")->fetchAll();
include __DIR__ . '/../config/header.php';
?>
<p class="no-print"><button class="btn" onclick="window.print()">Cetak</button> <a class="btn abu" href="index.php">Kembali</a></p>
<p>Tanggal cetak: <?= date('d-m-Y') ?></p>
<table>
    <tr><th>No</th><th>Siswa</th><th>Tanggal</th><th>Pembimbing</th><th>Catatan</th><th>Nilai</th><th>Rekomendasi</th></tr>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td><td><?= e($r['nis'] . ' - ' . $r['nama']) ?></td><td><?= tgl($r['tanggal']) ?></td>
        <td><?= e($r['nama_pembimbing'] ?? '-') ?></td><td><?= e($r['catatan']) ?></td><td><?= $r['nilai'] ?></td><td><?= e($r['rekomendasi']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../config/footer.php'; ?>
