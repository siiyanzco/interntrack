<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['admin', 'pembimbing'], $base);
$judul = 'Aktivitas / Jurnal Siswa';

$f_siswa = (int)($_GET['siswa'] ?? 0);
$q = trim($_GET['q'] ?? '');
$tanggal = $_GET['tanggal'] ?? '';

$where = []; $par = [];
if ($f_siswa) { $where[] = "j.siswa_id = ?"; $par[] = $f_siswa; }
if ($q !== '') { $where[] = "(j.kegiatan LIKE ? OR s.nama LIKE ?)"; $par[] = "%$q%"; $par[] = "%$q%"; }
if ($tanggal !== '') { $where[] = "j.tanggal = ?"; $par[] = $tanggal; }
$sql = "SELECT j.*, s.nis, s.nama FROM jurnal j JOIN siswa s ON s.id = j.siswa_id"
     . ($where ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY j.tanggal DESC, s.nama";
$st = $pdo->prepare($sql);
$st->execute($par);
$rows = $st->fetchAll();
$l_siswa = $pdo->query("SELECT id, nis, nama FROM siswa ORDER BY nama")->fetchAll();

include __DIR__ . '/../config/header.php';
?>
<div class="card">
    <form method="get" class="filter">
        <div><label>Siswa</label><select name="siswa"><option value="0">Semua</option>
            <?php foreach ($l_siswa as $x): ?><option value="<?= $x['id'] ?>" <?= $f_siswa == $x['id'] ? 'selected' : '' ?>><?= e($x['nis'] . ' - ' . $x['nama']) ?></option><?php endforeach; ?></select></div>
        <div><label>Tanggal</label><input type="date" name="tanggal" value="<?= e($tanggal) ?>"></div>
        <div><label>Cari kegiatan / nama</label><input type="text" name="q" value="<?= e($q) ?>"></div>
        <div style="flex:0"><button class="btn" type="submit">Filter</button></div>
        <div style="flex:0"><a class="btn abu" href="jurnal.php">Reset</a></div>
    </form>
</div>
<table>
    <tr><th>No</th><th>Tanggal</th><th>Siswa</th><th>Kegiatan</th><th>Hasil</th><th>Kendala</th></tr>
    <?php if (!$rows): ?><tr><td colspan="6">Belum ada jurnal.</td></tr><?php endif; ?>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td><td><?= tgl($r['tanggal']) ?></td><td><?= e($r['nis'] . ' - ' . $r['nama']) ?></td>
        <td><?= e($r['kegiatan']) ?></td><td><?= e($r['hasil']) ?></td><td><?= e($r['kendala']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../config/footer.php'; ?>
