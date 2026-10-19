<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['admin', 'pembimbing'], $base);

$aksi = $_GET['aksi'] ?? 'list';
$errors = [];
$data = ['siswa_id' => '', 'perusahaan_id' => '', 'pembimbing_id' => '', 'tanggal_mulai' => '', 'tanggal_selesai' => '', 'status' => 'Belum Mulai'];
$status_list = ['Belum Mulai', 'Aktif', 'Selesai'];

if ($aksi === 'hapus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare("DELETE FROM penempatan WHERE id = ?")->execute([(int)($_POST['id'] ?? 0)]);
    set_flash('Data penempatan berhasil dihapus.');
    header('Location: penempatan.php');
    exit;
}

// Ubah status cepat (Mengubah status PKL)
if ($aksi === 'status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $sv = $_POST['status'] ?? '';
    if (in_array($sv, $status_list)) {
        $pdo->prepare("UPDATE penempatan SET status = ? WHERE id = ?")->execute([$sv, (int)($_POST['id'] ?? 0)]);
        set_flash('Status PKL berhasil diubah.');
    }
    header('Location: penempatan.php');
    exit;
}

if (in_array($aksi, ['tambah', 'edit']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    foreach ($data as $k => $v) $data[$k] = trim($_POST[$k] ?? '');
    if (!$data['siswa_id']) $errors[] = 'Siswa wajib dipilih.';
    if (!$data['perusahaan_id']) $errors[] = 'Perusahaan wajib dipilih.';
    if (!$data['pembimbing_id']) $errors[] = 'Pembimbing wajib dipilih.';
    if ($data['tanggal_mulai'] === '') $errors[] = 'Tanggal mulai wajib diisi.';
    if ($data['tanggal_selesai'] === '') $errors[] = 'Tanggal selesai wajib diisi.';
    if ($data['tanggal_mulai'] && $data['tanggal_selesai'] && $data['tanggal_selesai'] < $data['tanggal_mulai'])
        $errors[] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
    if (!in_array($data['status'], $status_list)) $errors[] = 'Status tidak valid.';
    if ($data['siswa_id']) {
        $st = $pdo->prepare("SELECT COUNT(*) FROM penempatan WHERE siswa_id = ? AND id <> ?");
        $st->execute([$data['siswa_id'], $id]);
        if ($st->fetchColumn() > 0) $errors[] = 'Siswa tersebut sudah memiliki penempatan.';
    }
    if (!$errors) {
        if ($aksi === 'tambah') {
            $pdo->prepare("INSERT INTO penempatan (siswa_id,perusahaan_id,pembimbing_id,tanggal_mulai,tanggal_selesai,status) VALUES (?,?,?,?,?,?)")
                ->execute(array_values($data));
            set_flash('Penempatan berhasil ditambahkan.');
        } else {
            $pdo->prepare("UPDATE penempatan SET siswa_id=?, perusahaan_id=?, pembimbing_id=?, tanggal_mulai=?, tanggal_selesai=?, status=? WHERE id=?")
                ->execute([$data['siswa_id'], $data['perusahaan_id'], $data['pembimbing_id'], $data['tanggal_mulai'], $data['tanggal_selesai'], $data['status'], $id]);
            set_flash('Penempatan berhasil diperbarui.');
        }
        header('Location: penempatan.php');
        exit;
    }
}

if ($aksi === 'edit' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $st = $pdo->prepare("SELECT * FROM penempatan WHERE id = ?");
    $st->execute([(int)($_GET['id'] ?? 0)]);
    $data = $st->fetch();
    if (!$data) { header('Location: penempatan.php'); exit; }
}

$judul = 'Penempatan PKL';
include __DIR__ . '/../config/header.php';

if ($aksi === 'tambah' || $aksi === 'edit'):
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    $l_siswa = $pdo->query("SELECT id, nis, nama FROM siswa ORDER BY nama")->fetchAll();
    $l_per = $pdo->query("SELECT id, nama_perusahaan FROM perusahaan ORDER BY nama_perusahaan")->fetchAll();
    $l_pemb = $pdo->query("SELECT id, nama FROM pembimbing ORDER BY nama")->fetchAll();
?>
<div class="card">
    <h3><?= $aksi === 'tambah' ? 'Tambah' : 'Edit' ?> Penempatan</h3>
    <?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" action="penempatan.php?aksi=<?= e($aksi) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <label>Siswa</label>
        <select name="siswa_id"><option value="">-- Pilih Siswa --</option>
        <?php foreach ($l_siswa as $x): ?><option value="<?= $x['id'] ?>" <?= $data['siswa_id'] == $x['id'] ? 'selected' : '' ?>><?= e($x['nis'] . ' - ' . $x['nama']) ?></option><?php endforeach; ?>
        </select>
        <label>Perusahaan</label>
        <select name="perusahaan_id"><option value="">-- Pilih Perusahaan --</option>
        <?php foreach ($l_per as $x): ?><option value="<?= $x['id'] ?>" <?= $data['perusahaan_id'] == $x['id'] ? 'selected' : '' ?>><?= e($x['nama_perusahaan']) ?></option><?php endforeach; ?>
        </select>
        <label>Pembimbing</label>
        <select name="pembimbing_id"><option value="">-- Pilih Pembimbing --</option>
        <?php foreach ($l_pemb as $x): ?><option value="<?= $x['id'] ?>" <?= $data['pembimbing_id'] == $x['id'] ? 'selected' : '' ?>><?= e($x['nama']) ?></option><?php endforeach; ?>
        </select>
        <label>Tanggal Mulai</label><input type="date" name="tanggal_mulai" value="<?= e($data['tanggal_mulai']) ?>">
        <label>Tanggal Selesai</label><input type="date" name="tanggal_selesai" value="<?= e($data['tanggal_selesai']) ?>">
        <label>Status</label>
        <select name="status"><?php foreach ($status_list as $sv): ?><option <?= $data['status'] === $sv ? 'selected' : '' ?>><?= $sv ?></option><?php endforeach; ?></select>
        <p><button class="btn" type="submit">Simpan</button> <a class="btn abu" href="penempatan.php">Batal</a></p>
    </form>
</div>
<?php
else:
    $f_status = $_GET['status'] ?? '';
    $f_per = (int)($_GET['perusahaan'] ?? 0);
    $f_pemb = (int)($_GET['pembimbing'] ?? 0);
    $q = trim($_GET['q'] ?? '');
    $where = []; $par = [];
    if ($q !== '') { $where[] = "(s.nama LIKE ? OR s.nis LIKE ?)"; $par[] = "%$q%"; $par[] = "%$q%"; }
    if ($f_status !== '') { $where[] = "p.status = ?"; $par[] = $f_status; }
    if ($f_per) { $where[] = "p.perusahaan_id = ?"; $par[] = $f_per; }
    if ($f_pemb) { $where[] = "p.pembimbing_id = ?"; $par[] = $f_pemb; }
    $sql = "SELECT p.*, s.nis, s.nama AS nama_siswa, pr.nama_perusahaan, pb.nama AS nama_pembimbing
            FROM penempatan p JOIN siswa s ON s.id = p.siswa_id
            JOIN perusahaan pr ON pr.id = p.perusahaan_id JOIN pembimbing pb ON pb.id = p.pembimbing_id"
         . ($where ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY s.nama";
    $st = $pdo->prepare($sql);
    $st->execute($par);
    $rows = $st->fetchAll();
    $l_per = $pdo->query("SELECT id, nama_perusahaan FROM perusahaan ORDER BY nama_perusahaan")->fetchAll();
    $l_pemb = $pdo->query("SELECT id, nama FROM pembimbing ORDER BY nama")->fetchAll();
?>
<div class="card">
    <form method="get" class="filter">
        <div><label>Cari siswa</label><input type="text" name="q" value="<?= e($q) ?>"></div>
        <div><label>Status</label><select name="status"><option value="">Semua</option>
            <?php foreach ($status_list as $sv): ?><option <?= $f_status === $sv ? 'selected' : '' ?>><?= $sv ?></option><?php endforeach; ?></select></div>
        <div><label>Perusahaan</label><select name="perusahaan"><option value="0">Semua</option>
            <?php foreach ($l_per as $x): ?><option value="<?= $x['id'] ?>" <?= $f_per == $x['id'] ? 'selected' : '' ?>><?= e($x['nama_perusahaan']) ?></option><?php endforeach; ?></select></div>
        <div><label>Pembimbing</label><select name="pembimbing"><option value="0">Semua</option>
            <?php foreach ($l_pemb as $x): ?><option value="<?= $x['id'] ?>" <?= $f_pemb == $x['id'] ? 'selected' : '' ?>><?= e($x['nama']) ?></option><?php endforeach; ?></select></div>
        <div style="flex:0"><button class="btn" type="submit">Filter</button></div>
        <div style="flex:0"><a class="btn abu" href="penempatan.php">Reset</a></div>
    </form>
    <a class="btn biru" href="penempatan.php?aksi=tambah">+ Tambah Penempatan</a>
</div>
<table>
    <tr><th>No</th><th>Siswa</th><th>Perusahaan</th><th>Pembimbing</th><th>Mulai</th><th>Selesai</th><th>Status</th><th>Aksi</th></tr>
    <?php if (!$rows): ?><tr><td colspan="8">Data tidak ditemukan.</td></tr><?php endif; ?>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($r['nis'] . ' - ' . $r['nama_siswa']) ?></td>
        <td><?= e($r['nama_perusahaan']) ?></td>
        <td><?= e($r['nama_pembimbing']) ?></td>
        <td><?= tgl($r['tanggal_mulai']) ?></td>
        <td><?= tgl($r['tanggal_selesai']) ?></td>
        <td>
            <form class="inline" method="post" action="penempatan.php?aksi=status">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <select name="status" style="width:auto" onchange="this.form.submit()">
                    <?php foreach ($status_list as $sv): ?><option <?= $r['status'] === $sv ? 'selected' : '' ?>><?= $sv ?></option><?php endforeach; ?>
                </select>
            </form>
        </td>
        <td>
            <a class="btn kecil kuning" href="penempatan.php?aksi=edit&id=<?= $r['id'] ?>">Edit</a>
            <form class="inline" method="post" action="penempatan.php?aksi=hapus" onsubmit="return confirm('Hapus penempatan ini?')">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn kecil merah" type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php include __DIR__ . '/../config/footer.php'; ?>
