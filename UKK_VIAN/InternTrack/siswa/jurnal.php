<?php
// ALGORITMA 2: Input jurnal kegiatan
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['siswa'], $base);
$judul = 'Jurnal Kegiatan';
$s = siswa_login($pdo);
$errors = [];
$data = ['tanggal' => date('Y-m-d'), 'kegiatan' => '', 'hasil' => '', 'kendala' => ''];

if (isset($_GET['hapus']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare("DELETE FROM jurnal WHERE id = ? AND siswa_id = ?")->execute([(int)($_POST['id'] ?? 0), $s['id']]);
    set_flash('Jurnal dihapus.');
    header('Location: jurnal.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['hapus'])) {
    foreach ($data as $k => $v) $data[$k] = trim($_POST[$k] ?? '');
    if ($data['tanggal'] === '') $errors[] = 'Tanggal wajib diisi.';
    if ($data['kegiatan'] === '') $errors[] = 'Kegiatan wajib diisi.';
    if (!$errors) {
        $pdo->prepare("INSERT INTO jurnal (siswa_id,tanggal,kegiatan,hasil,kendala) VALUES (?,?,?,?,?)")
            ->execute([$s['id'], $data['tanggal'], $data['kegiatan'], $data['hasil'], $data['kendala']]);
        set_flash('Jurnal kegiatan tersimpan.');
        header('Location: jurnal.php');
        exit;
    }
}

$st = $pdo->prepare("SELECT * FROM jurnal WHERE siswa_id = ? ORDER BY tanggal DESC, id DESC");
$st->execute([$s['id']]);
$rows = $st->fetchAll();
include __DIR__ . '/../config/header.php';
?>
<div class="card">
    <h3>Isi Jurnal</h3>
    <?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post">
        <label>Tanggal</label><input type="date" name="tanggal" value="<?= e($data['tanggal']) ?>">
        <label>Kegiatan</label><textarea name="kegiatan"><?= e($data['kegiatan']) ?></textarea>
        <label>Hasil</label><textarea name="hasil"><?= e($data['hasil']) ?></textarea>
        <label>Kendala</label><textarea name="kendala"><?= e($data['kendala']) ?></textarea>
        <p><button class="btn" type="submit">Simpan Jurnal</button></p>
    </form>
</div>
<table>
    <tr><th>No</th><th>Tanggal</th><th>Kegiatan</th><th>Hasil</th><th>Kendala</th><th>Aksi</th></tr>
    <?php if (!$rows): ?><tr><td colspan="6">Belum ada jurnal.</td></tr><?php endif; ?>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td><td><?= tgl($r['tanggal']) ?></td><td><?= e($r['kegiatan']) ?></td><td><?= e($r['hasil']) ?></td><td><?= e($r['kendala']) ?></td>
        <td>
            <form class="inline" method="post" action="jurnal.php?hapus=1" onsubmit="return confirm('Hapus jurnal ini?')">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn kecil merah" type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../config/footer.php'; ?>
