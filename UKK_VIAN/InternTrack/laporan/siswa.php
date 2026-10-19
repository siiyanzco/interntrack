<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['admin', 'pembimbing'], $base);
$judul = 'Laporan Data Siswa PKL';
$rows = $pdo->query("SELECT s.nis, s.nama, s.kelas, s.jurusan, s.no_hp, pr.nama_perusahaan, pb.nama AS nama_pembimbing,
                            p.tanggal_mulai, p.tanggal_selesai, p.status
                     FROM siswa s LEFT JOIN penempatan p ON p.siswa_id = s.id
                     LEFT JOIN perusahaan pr ON pr.id = p.perusahaan_id LEFT JOIN pembimbing pb ON pb.id = p.pembimbing_id
                     ORDER BY s.nama")->fetchAll();
include __DIR__ . '/../config/header.php';
?>
<p class="no-print"><button class="btn" onclick="window.print()">Cetak</button> <a class="btn abu" href="index.php">Kembali</a></p>
<p>Tanggal cetak: <?= date('d-m-Y') ?></p>
<table>
    <tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Jurusan</th><th>No. HP</th><th>Perusahaan</th><th>Pembimbing</th><th>Periode</th><th>Status</th></tr>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td><td><?= e($r['nis']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['kelas']) ?></td>
        <td><?= e($r['jurusan']) ?></td><td><?= e($r['no_hp']) ?></td>
        <td><?= e($r['nama_perusahaan'] ?? '-') ?></td><td><?= e($r['nama_pembimbing'] ?? '-') ?></td>
        <td><?= $r['tanggal_mulai'] ? tgl($r['tanggal_mulai']) . ' s/d ' . tgl($r['tanggal_selesai']) : '-' ?></td>
        <td><?= e($r['status'] ?? 'Belum ditempatkan') ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../config/footer.php'; ?>
