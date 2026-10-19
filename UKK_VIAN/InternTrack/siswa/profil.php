<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['siswa'], $base);
$judul = 'Profil Saya';
$s = siswa_login($pdo);
include __DIR__ . '/../config/header.php';
?>
<div class="card">
    <table class="detail">
        <tr><td>NIS</td><td><?= e($s['nis']) ?></td></tr>
        <tr><td>Nama</td><td><?= e($s['nama']) ?></td></tr>
        <tr><td>Kelas</td><td><?= e($s['kelas']) ?></td></tr>
        <tr><td>Jurusan</td><td><?= e($s['jurusan']) ?></td></tr>
        <tr><td>No. HP</td><td><?= e($s['no_hp']) ?></td></tr>
        <tr><td>Username</td><td><?= e($_SESSION['user']['username']) ?></td></tr>
    </table>
</div>
<?php include __DIR__ . '/../config/footer.php'; ?>
