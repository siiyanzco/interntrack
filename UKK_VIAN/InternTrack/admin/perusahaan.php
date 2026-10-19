<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['admin', 'pembimbing'], $base);

$aksi = $_GET['aksi'] ?? 'list';
$errors = [];
$data = ['nama_perusahaan' => '', 'alamat' => '', 'bidang' => '', 'kontak' => ''];

if ($aksi === 'hapus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->prepare("DELETE FROM perusahaan WHERE id = ?")->execute([(int)($_POST['id'] ?? 0)]);
        set_flash('Data perusahaan berhasil dihapus.');
    } catch (PDOException $ex) {
        set_flash('Perusahaan tidak bisa dihapus karena masih dipakai data penempatan.', 'error');
    }
    header('Location: perusahaan.php');
    exit;
}

if (in_array($aksi, ['tambah', 'edit']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    foreach ($data as $k => $v) $data[$k] = trim($_POST[$k] ?? '');
    if ($data['nama_perusahaan'] === '') $errors[] = 'Nama perusahaan wajib diisi.';
    if ($data['alamat'] === '') $errors[] = 'Alamat wajib diisi.';
    if ($data['bidang'] === '') $errors[] = 'Bidang wajib diisi.';
    if (!$errors) {
        if ($aksi === 'tambah') {
            $pdo->prepare("INSERT INTO perusahaan (nama_perusahaan,alamat,bidang,kontak) VALUES (?,?,?,?)")
                ->execute(array_values($data));
            set_flash('Perusahaan berhasil ditambahkan.');
        } else {
            $pdo->prepare("UPDATE perusahaan SET nama_perusahaan=?, alamat=?, bidang=?, kontak=? WHERE id=?")
                ->execute([$data['nama_perusahaan'], $data['alamat'], $data['bidang'], $data['kontak'], $id]);
            set_flash('Perusahaan berhasil diperbarui.');
        }
        header('Location: perusahaan.php');
        exit;
    }
}

if ($aksi === 'edit' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $st = $pdo->prepare("SELECT * FROM perusahaan WHERE id = ?");
    $st->execute([(int)($_GET['id'] ?? 0)]);
    $data = $st->fetch();
    if (!$data) { header('Location: perusahaan.php'); exit; }
}

$judul = 'Data Perusahaan';
include __DIR__ . '/../config/header.php';

if ($aksi === 'tambah' || $aksi === 'edit'):
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
?>
<div class="card">
    <h3><?= $aksi === 'tambah' ? 'Tambah' : 'Edit' ?> Perusahaan</h3>
    <?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" action="perusahaan.php?aksi=<?= e($aksi) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <label>Nama Perusahaan</label><input type="text" name="nama_perusahaan" value="<?= e($data['nama_perusahaan']) ?>">
        <label>Alamat</label><input type="text" name="alamat" value="<?= e($data['alamat']) ?>">
        <label>Bidang</label><input type="text" name="bidang" value="<?= e($data['bidang']) ?>">
        <label>Kontak</label><input type="text" name="kontak" value="<?= e($data['kontak']) ?>">
        <p><button class="btn" type="submit">Simpan</button> <a class="btn abu" href="perusahaan.php">Batal</a></p>
    </form>
</div>
<?php
elseif ($aksi === 'detail'):
    $st = $pdo->prepare("SELECT * FROM perusahaan WHERE id = ?");
    $st->execute([(int)($_GET['id'] ?? 0)]);
    $p = $st->fetch();
    if (!$p) { echo '<div class="alert error">Data tidak ditemukan.</div>'; include __DIR__ . '/../config/footer.php'; exit; }
    $st = $pdo->prepare("SELECT s.nis, s.nama, pe.status FROM penempatan pe JOIN siswa s ON s.id = pe.siswa_id WHERE pe.perusahaan_id = ?");
    $st->execute([$p['id']]);
    $siswa = $st->fetchAll();
?>
<div class="card">
    <h3>Detail Perusahaan</h3>
    <table class="detail">
        <tr><td>Nama Perusahaan</td><td><?= e($p['nama_perusahaan']) ?></td></tr>
        <tr><td>Alamat</td><td><?= e($p['alamat']) ?></td></tr>
        <tr><td>Bidang</td><td><?= e($p['bidang']) ?></td></tr>
        <tr><td>Kontak</td><td><?= e($p['kontak']) ?></td></tr>
    </table>
    <h4>Siswa yang PKL di sini</h4>
    <table>
        <tr><th>NIS</th><th>Nama</th><th>Status</th></tr>
        <?php if (!$siswa): ?><tr><td colspan="3">Belum ada siswa.</td></tr><?php endif; ?>
        <?php foreach ($siswa as $s): ?><tr><td><?= e($s['nis']) ?></td><td><?= e($s['nama']) ?></td><td><?= e($s['status']) ?></td></tr><?php endforeach; ?>
    </table>
    <p><a class="btn abu" href="perusahaan.php">Kembali</a></p>
</div>
<?php
else:
    $q = trim($_GET['q'] ?? '');
    $st = $pdo->prepare("SELECT * FROM perusahaan WHERE nama_perusahaan LIKE ? OR bidang LIKE ? ORDER BY nama_perusahaan");
    $st->execute(["%$q%", "%$q%"]);
    $rows = $st->fetchAll();
?>
<div class="card">
    <form method="get" class="filter">
        <div><label>Cari (nama / bidang)</label><input type="text" name="q" value="<?= e($q) ?>"></div>
        <div style="flex:0"><button class="btn" type="submit">Cari</button></div>
        <div style="flex:0"><a class="btn abu" href="perusahaan.php">Reset</a></div>
    </form>
    <a class="btn biru" href="perusahaan.php?aksi=tambah">+ Tambah Perusahaan</a>
</div>
<table>
    <tr><th>No</th><th>Nama Perusahaan</th><th>Alamat</th><th>Bidang</th><th>Kontak</th><th>Aksi</th></tr>
    <?php if (!$rows): ?><tr><td colspan="6">Data tidak ditemukan.</td></tr><?php endif; ?>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td><td><?= e($r['nama_perusahaan']) ?></td><td><?= e($r['alamat']) ?></td>
        <td><?= e($r['bidang']) ?></td><td><?= e($r['kontak']) ?></td>
        <td>
            <a class="btn kecil biru" href="perusahaan.php?aksi=detail&id=<?= $r['id'] ?>">Detail</a>
            <a class="btn kecil kuning" href="perusahaan.php?aksi=edit&id=<?= $r['id'] ?>">Edit</a>
            <form class="inline" method="post" action="perusahaan.php?aksi=hapus" onsubmit="return confirm('Hapus perusahaan ini?')">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn kecil merah" type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php include __DIR__ . '/../config/footer.php'; ?>
