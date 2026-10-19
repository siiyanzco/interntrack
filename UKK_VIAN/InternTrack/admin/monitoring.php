<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['admin', 'pembimbing'], $base);
$judul = 'Monitoring Pembimbing';
$errors = [];
$data = ['siswa_id' => '', 'tanggal' => date('Y-m-d'), 'catatan' => '', 'nilai' => '', 'rekomendasi' => ''];

// Jika yang login pembimbing, catat id pembimbingnya
$pemb_id = null;
if ($_SESSION['user']['role'] === 'pembimbing') {
    $st = $pdo->prepare("SELECT id FROM pembimbing WHERE user_id = ?");
    $st->execute([$_SESSION['user']['id']]);
    $pemb_id = $st->fetchColumn() ?: null;
}

if (isset($_GET['hapus']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare("DELETE FROM monitoring WHERE id = ?")->execute([(int)($_POST['id'] ?? 0)]);
    set_flash('Data monitoring dihapus.');
    header('Location: monitoring.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['hapus'])) {
    foreach ($data as $k => $v) $data[$k] = trim($_POST[$k] ?? '');
    if (!$data['siswa_id']) $errors[] = 'Siswa wajib dipilih.';
    if ($data['tanggal'] === '') $errors[] = 'Tanggal monitoring wajib diisi.';
    if ($data['catatan'] === '') $errors[] = 'Catatan wajib diisi.';
    if ($data['nilai'] === '' || !ctype_digit($data['nilai']) || (int)$data['nilai'] > 100) $errors[] = 'Nilai harus angka 0-100.';
    if (!$errors) {
        $pdo->prepare("INSERT INTO monitoring (siswa_id,pembimbing_id,tanggal,catatan,nilai,rekomendasi) VALUES (?,?,?,?,?,?)")
            ->execute([$data['siswa_id'], $pemb_id, $data['tanggal'], $data['catatan'], (int)$data['nilai'], $data['rekomendasi']]);
        set_flash('Data monitoring tersimpan.');
        header('Location: monitoring.php');
        exit;
    }
}

$l_siswa = $pdo->query("SELECT id, nis, nama FROM siswa ORDER BY nama")->fetchAll();
$rows = $pdo->query("SELECT m.*, s.nis, s.nama, pb.nama AS nama_pembimbing
                     FROM monitoring m JOIN siswa s ON s.id = m.siswa_id LEFT JOIN pembimbing pb ON pb.id = m.pembimbing_id
                     ORDER BY m.tanggal DESC, m.id DESC")->fetchAll();
include __DIR__ . '/../config/header.php';
?>
<div class="card">
    <h3>Input Monitoring</h3>
    <?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post">
        <label>Nama Siswa</label>
        <select name="siswa_id"><option value="">-- Pilih Siswa --</option>
        <?php foreach ($l_siswa as $x): ?><option value="<?= $x['id'] ?>" <?= $data['siswa_id'] == $x['id'] ? 'selected' : '' ?>><?= e($x['nis'] . ' - ' . $x['nama']) ?></option><?php endforeach; ?></select>
        <label>Tanggal Monitoring</label><input type="date" name="tanggal" value="<?= e($data['tanggal']) ?>">
        <label>Catatan</label><textarea name="catatan"><?= e($data['catatan']) ?></textarea>
        <label>Nilai (0-100)</label><input type="number" name="nilai" min="0" max="100" value="<?= e($data['nilai']) ?>">
        <label>Rekomendasi</label><textarea name="rekomendasi"><?= e($data['rekomendasi']) ?></textarea>
        <p><button class="btn" type="submit">Simpan</button></p>
    </form>
</div>
<table>
    <tr><th>No</th><th>Tanggal</th><th>Siswa</th><th>Pembimbing</th><th>Catatan</th><th>Nilai</th><th>Rekomendasi</th><th>Aksi</th></tr>
    <?php if (!$rows): ?><tr><td colspan="8">Belum ada data monitoring.</td></tr><?php endif; ?>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td><td><?= tgl($r['tanggal']) ?></td><td><?= e($r['nis'] . ' - ' . $r['nama']) ?></td>
        <td><?= e($r['nama_pembimbing'] ?? '-') ?></td><td><?= e($r['catatan']) ?></td><td><?= $r['nilai'] ?></td><td><?= e($r['rekomendasi']) ?></td>
        <td>
            <form class="inline" method="post" action="monitoring.php?hapus=1" onsubmit="return confirm('Hapus data monitoring ini?')">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn kecil merah" type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../config/footer.php'; ?>
