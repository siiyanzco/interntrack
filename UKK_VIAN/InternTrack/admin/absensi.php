<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['admin', 'pembimbing'], $base);
$judul = 'Kehadiran Siswa';
$status_list = ['Hadir', 'Izin', 'Sakit', 'Alpa'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $tanggal = trim($_POST['tanggal'] ?? '');
    $status = $_POST['status'] ?? '';
    if (!$siswa_id) $errors[] = 'Siswa wajib dipilih.';
    if ($tanggal === '') $errors[] = 'Tanggal wajib diisi.';
    if (!in_array($status, $status_list)) $errors[] = 'Status wajib dipilih.';
    if (!$errors) {
        // jika sudah ada absen di tanggal yg sama, status diperbarui
        $pdo->prepare("INSERT INTO absensi (siswa_id,tanggal,status) VALUES (?,?,?) ON DUPLICATE KEY UPDATE status = VALUES(status)")
            ->execute([$siswa_id, $tanggal, $status]);
        set_flash('Data absensi tersimpan.');
        header('Location: absensi.php');
        exit;
    }
}

$f_siswa = (int)($_GET['siswa'] ?? 0);
$where = []; $par = [];
if ($f_siswa) { $where[] = "a.siswa_id = ?"; $par[] = $f_siswa; }
$st = $pdo->prepare("SELECT a.*, s.nis, s.nama FROM absensi a JOIN siswa s ON s.id = a.siswa_id"
    . ($where ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY a.tanggal DESC, s.nama LIMIT 100");
$st->execute($par);
$rows = $st->fetchAll();
$l_siswa = $pdo->query("SELECT id, nis, nama FROM siswa ORDER BY nama")->fetchAll();
$rekap = array_filter(rekap_kehadiran($pdo), function ($r) { return $r['total'] > 0; });

include __DIR__ . '/../config/header.php';
?>
<div class="card">
    <h3>Input Absensi</h3>
    <?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post">
        <label>Siswa</label>
        <select name="siswa_id"><option value="">-- Pilih Siswa --</option>
        <?php foreach ($l_siswa as $x): ?><option value="<?= $x['id'] ?>"><?= e($x['nis'] . ' - ' . $x['nama']) ?></option><?php endforeach; ?></select>
        <label>Tanggal</label><input type="date" name="tanggal" value="<?= date('Y-m-d') ?>">
        <label>Status</label>
        <select name="status"><option value="">-- Pilih Status --</option>
        <?php foreach ($status_list as $sv): ?><option><?= $sv ?></option><?php endforeach; ?></select>
        <p><button class="btn" type="submit">Simpan</button></p>
    </form>
</div>

<h3>Rekap Kehadiran &amp; Status PKL</h3>
<table>
    <tr><th>Siswa</th><th>Total Hari</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpa</th><th>Persentase</th><th>Status</th></tr>
    <?php foreach ($rekap as $r): ?>
    <tr>
        <td><?= e($r['nis'] . ' - ' . $r['nama']) ?></td><td><?= $r['total'] ?></td><td><?= $r['hadir'] ?></td>
        <td><?= $r['izin'] ?></td><td><?= $r['sakit'] ?></td><td><?= $r['alpa'] ?></td>
        <td><?= $r['persen'] ?>%</td>
        <td><span class="badge <?= kelas_status($r['status']) ?>"><?= e($r['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
</table>

<h3>Riwayat Absensi</h3>
<form method="get" class="filter">
    <div><label>Filter siswa</label><select name="siswa"><option value="0">Semua</option>
        <?php foreach ($l_siswa as $x): ?><option value="<?= $x['id'] ?>" <?= $f_siswa == $x['id'] ? 'selected' : '' ?>><?= e($x['nis'] . ' - ' . $x['nama']) ?></option><?php endforeach; ?></select></div>
    <div style="flex:0"><button class="btn" type="submit">Filter</button></div>
</form>
<table>
    <tr><th>No</th><th>Tanggal</th><th>Siswa</th><th>Status</th></tr>
    <?php if (!$rows): ?><tr><td colspan="4">Belum ada data.</td></tr><?php endif; ?>
    <?php foreach ($rows as $i => $r): ?>
    <tr><td><?= $i + 1 ?></td><td><?= tgl($r['tanggal']) ?></td><td><?= e($r['nis'] . ' - ' . $r['nama']) ?></td><td><?= e($r['status']) ?></td></tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../config/footer.php'; ?>
