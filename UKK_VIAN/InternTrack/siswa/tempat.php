<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['siswa'], $base);
$judul = 'Tempat PKL';
$s = siswa_login($pdo);
$st = $pdo->prepare("SELECT p.*, pr.nama_perusahaan, pr.alamat, pr.bidang, pr.kontak, pb.nama AS nama_pembimbing, pb.no_hp AS hp_pembimbing
                     FROM penempatan p JOIN perusahaan pr ON pr.id = p.perusahaan_id JOIN pembimbing pb ON pb.id = p.pembimbing_id
                     WHERE p.siswa_id = ?");
$st->execute([$s['id']]);
$p = $st->fetch();
include __DIR__ . '/../config/header.php';
?>
<div class="card">
<?php if (!$p): ?>
    <p>Anda belum ditempatkan di perusahaan. Hubungi admin/pembimbing.</p>
<?php else: ?>
    <table class="detail">
        <tr><td>Perusahaan</td><td><?= e($p['nama_perusahaan']) ?></td></tr>
        <tr><td>Alamat</td><td><?= e($p['alamat']) ?></td></tr>
        <tr><td>Bidang</td><td><?= e($p['bidang']) ?></td></tr>
        <tr><td>Kontak</td><td><?= e($p['kontak']) ?></td></tr>
        <tr><td>Pembimbing</td><td><?= e($p['nama_pembimbing']) ?> (<?= e($p['hp_pembimbing']) ?>)</td></tr>
        <tr><td>Tanggal Mulai</td><td><?= tgl($p['tanggal_mulai']) ?></td></tr>
        <tr><td>Tanggal Selesai</td><td><?= tgl($p['tanggal_selesai']) ?></td></tr>
        <tr><td>Status</td><td><?= e($p['status']) ?></td></tr>
    </table>
<?php endif; ?>
</div>
<?php include __DIR__ . '/../config/footer.php'; ?>
