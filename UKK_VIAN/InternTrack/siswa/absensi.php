<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['siswa'], $base);
$judul = 'Kehadiran Saya';
$s = siswa_login($pdo);
$status_list = ['Hadir', 'Izin', 'Sakit', 'Alpa'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = trim($_POST['tanggal'] ?? '');
    $status = $_POST['status'] ?? '';
    if ($tanggal === '') $errors[] = 'Tanggal wajib diisi.';
    if (!in_array($status, $status_list)) $errors[] = 'Status wajib dipilih.';
    if (!$errors) {
        $pdo->prepare("INSERT INTO absensi (siswa_id,tanggal,status) VALUES (?,?,?) ON DUPLICATE KEY UPDATE status = VALUES(status)")
            ->execute([$s['id'], $tanggal, $status]);
        set_flash('Kehadiran tersimpan.');
        header('Location: absensi.php');
        exit;
    }
}

$st = $pdo->prepare("SELECT * FROM absensi WHERE siswa_id = ? ORDER BY tanggal DESC");
$st->execute([$s['id']]);
$rows = $st->fetchAll();
$k = hitung_kehadiran($pdo, $s['id']);
include __DIR__ . '/../config/header.php';
?>
<div class="card">
    <h3>Isi Kehadiran</h3>
    <?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post">
        <label>Tanggal</label><input type="date" name="tanggal" value="<?= date('Y-m-d') ?>">
        <label>Status</label>
        <select name="status"><option value="">-- Pilih Status --</option>
        <?php foreach ($status_list as $sv): ?><option><?= $sv ?></option><?php endforeach; ?></select>
        <p><button class="btn" type="submit">Simpan</button></p>
    </form>
</div>
<div class="card">
    Rekap: Total hari <b><?= $k['total'] ?></b>, Hadir <b><?= $k['hadir'] ?></b>,
    Persentase <b><?= $k['persen'] ?>%</b>
    <span class="badge <?= kelas_status($k['status']) ?>"><?= e($k['status']) ?></span>
</div>
<table>
    <tr><th>No</th><th>Tanggal</th><th>Status</th></tr>
    <?php if (!$rows): ?><tr><td colspan="3">Belum ada data.</td></tr><?php endif; ?>
    <?php foreach ($rows as $i => $r): ?>
    <tr><td><?= $i + 1 ?></td><td><?= tgl($r['tanggal']) ?></td><td><?= e($r['status']) ?></td></tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../config/footer.php'; ?>
