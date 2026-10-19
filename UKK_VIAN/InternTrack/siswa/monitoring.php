<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['siswa'], $base);
$judul = 'Hasil Monitoring';
$s = siswa_login($pdo);
$st = $pdo->prepare("SELECT m.*, pb.nama AS nama_pembimbing FROM monitoring m LEFT JOIN pembimbing pb ON pb.id = m.pembimbing_id WHERE m.siswa_id = ? ORDER BY m.tanggal DESC");
$st->execute([$s['id']]);
$rows = $st->fetchAll();
include __DIR__ . '/../config/header.php';
?>
<table>
    <tr><th>No</th><th>Tanggal</th><th>Pembimbing</th><th>Catatan</th><th>Nilai</th><th>Rekomendasi</th></tr>
    <?php if (!$rows): ?><tr><td colspan="6">Belum ada hasil monitoring.</td></tr><?php endif; ?>
    <?php foreach ($rows as $i => $r): ?>
    <tr><td><?= $i + 1 ?></td><td><?= tgl($r['tanggal']) ?></td><td><?= e($r['nama_pembimbing'] ?? '-') ?></td>
        <td><?= e($r['catatan']) ?></td><td><?= $r['nilai'] ?></td><td><?= e($r['rekomendasi']) ?></td></tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../config/footer.php'; ?>
