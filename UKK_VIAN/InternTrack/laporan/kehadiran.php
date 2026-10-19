<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['admin', 'pembimbing'], $base);
$judul = 'Laporan Kehadiran';
$rows = rekap_kehadiran($pdo);
include __DIR__ . '/../config/header.php';
?>
<p class="no-print"><button class="btn" onclick="window.print()">Cetak</button> <a class="btn abu" href="index.php">Kembali</a></p>
<p>Tanggal cetak: <?= date('d-m-Y') ?></p>
<table>
    <tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Total Hari</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpa</th><th>Persentase</th><th>Status</th></tr>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td><td><?= e($r['nis']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['kelas']) ?></td>
        <td><?= $r['total'] ?></td><td><?= $r['hadir'] ?></td><td><?= $r['izin'] ?></td><td><?= $r['sakit'] ?></td><td><?= $r['alpa'] ?></td>
        <td><?= $r['total'] > 0 ? $r['persen'] . '%' : '-' ?></td><td><?= e($r['status']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../config/footer.php'; ?>
