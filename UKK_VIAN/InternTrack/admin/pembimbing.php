<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['admin', 'pembimbing'], $base);

$aksi = $_GET['aksi'] ?? 'list';
$errors = [];
$data = ['nip' => '', 'nama' => '', 'no_hp' => '', 'username' => ''];

if ($aksi === 'hapus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $pdo->prepare("SELECT user_id FROM pembimbing WHERE id = ?");
    $st->execute([(int)($_POST['id'] ?? 0)]);
    $uid = $st->fetchColumn();
    if ($uid) {
        try {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
            set_flash('Data pembimbing berhasil dihapus.');
        } catch (PDOException $ex) {
            set_flash('Pembimbing tidak bisa dihapus karena masih dipakai data penempatan.', 'error');
        }
    }
    header('Location: pembimbing.php');
    exit;
}

if (in_array($aksi, ['tambah', 'edit']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    foreach ($data as $k => $v) $data[$k] = trim($_POST[$k] ?? '');
    $password = $_POST['password'] ?? '';

    if ($data['nip'] === '') $errors[] = 'NIP wajib diisi.';
    if ($data['nama'] === '') $errors[] = 'Nama wajib diisi.';
    if ($data['username'] === '') $errors[] = 'Username wajib diisi.';
    if (!$errors) {
        $st = $pdo->prepare("SELECT COUNT(*) FROM pembimbing WHERE nip = ? AND id <> ?");
        $st->execute([$data['nip'], $id]);
        if ($st->fetchColumn() > 0) $errors[] = 'NIP sudah terdaftar.';
        $st = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id NOT IN (SELECT user_id FROM pembimbing WHERE id = ?)");
        $st->execute([$data['username'], $id]);
        if ($st->fetchColumn() > 0) $errors[] = 'Username sudah dipakai.';
    }
    if (!$errors) {
        if ($aksi === 'tambah') {
            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO users (username,password,nama,role) VALUES (?,?,?, 'pembimbing')")
                ->execute([$data['username'], password_hash($password !== '' ? $password : 'password', PASSWORD_DEFAULT), $data['nama']]);
            $uid = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO pembimbing (user_id,nip,nama,no_hp) VALUES (?,?,?,?)")
                ->execute([$uid, $data['nip'], $data['nama'], $data['no_hp']]);
            $pdo->commit();
            set_flash('Pembimbing berhasil ditambahkan.');
        } else {
            $st = $pdo->prepare("SELECT user_id FROM pembimbing WHERE id = ?");
            $st->execute([$id]);
            $uid = $st->fetchColumn();
            $pdo->prepare("UPDATE pembimbing SET nip=?, nama=?, no_hp=? WHERE id=?")
                ->execute([$data['nip'], $data['nama'], $data['no_hp'], $id]);
            $pdo->prepare("UPDATE users SET username=?, nama=? WHERE id=?")->execute([$data['username'], $data['nama'], $uid]);
            if ($password !== '') {
                $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($password, PASSWORD_DEFAULT), $uid]);
            }
            set_flash('Pembimbing berhasil diperbarui.');
        }
        header('Location: pembimbing.php');
        exit;
    }
}

if ($aksi === 'edit' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $st = $pdo->prepare("SELECT p.*, u.username FROM pembimbing p JOIN users u ON u.id = p.user_id WHERE p.id = ?");
    $st->execute([(int)($_GET['id'] ?? 0)]);
    $data = $st->fetch();
    if (!$data) { header('Location: pembimbing.php'); exit; }
}

$judul = 'Data Guru Pembimbing';
include __DIR__ . '/../config/header.php';

if ($aksi === 'tambah' || $aksi === 'edit'):
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
?>
<div class="card">
    <h3><?= $aksi === 'tambah' ? 'Tambah' : 'Edit' ?> Pembimbing</h3>
    <?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" action="pembimbing.php?aksi=<?= e($aksi) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <label>NIP</label><input type="text" name="nip" value="<?= e($data['nip']) ?>">
        <label>Nama</label><input type="text" name="nama" value="<?= e($data['nama']) ?>">
        <label>No. HP</label><input type="text" name="no_hp" value="<?= e($data['no_hp']) ?>">
        <label>Username login</label><input type="text" name="username" value="<?= e($data['username']) ?>">
        <label>Password <?= $aksi === 'edit' ? '(kosongkan jika tidak diubah)' : '(kosong = "password")' ?></label>
        <input type="password" name="password">
        <p><button class="btn" type="submit">Simpan</button> <a class="btn abu" href="pembimbing.php">Batal</a></p>
    </form>
</div>
<?php
else:
    $q = trim($_GET['q'] ?? '');
    $st = $pdo->prepare("SELECT p.*, u.username, (SELECT COUNT(*) FROM penempatan x WHERE x.pembimbing_id = p.id) AS jml
                         FROM pembimbing p JOIN users u ON u.id = p.user_id
                         WHERE p.nama LIKE ? OR p.nip LIKE ? ORDER BY p.nama");
    $st->execute(["%$q%", "%$q%"]);
    $rows = $st->fetchAll();
?>
<div class="card">
    <form method="get" class="filter">
        <div><label>Cari (nama / NIP)</label><input type="text" name="q" value="<?= e($q) ?>"></div>
        <div style="flex:0"><button class="btn" type="submit">Cari</button></div>
        <div style="flex:0"><a class="btn abu" href="pembimbing.php">Reset</a></div>
    </form>
    <a class="btn biru" href="pembimbing.php?aksi=tambah">+ Tambah Pembimbing</a>
</div>
<table>
    <tr><th>No</th><th>NIP</th><th>Nama</th><th>No. HP</th><th>Username</th><th>Jml Siswa</th><th>Aksi</th></tr>
    <?php if (!$rows): ?><tr><td colspan="7">Data tidak ditemukan.</td></tr><?php endif; ?>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td><td><?= e($r['nip']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['no_hp']) ?></td>
        <td><?= e($r['username']) ?></td><td><?= $r['jml'] ?></td>
        <td>
            <a class="btn kecil kuning" href="pembimbing.php?aksi=edit&id=<?= $r['id'] ?>">Edit</a>
            <form class="inline" method="post" action="pembimbing.php?aksi=hapus" onsubmit="return confirm('Hapus pembimbing ini?')">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn kecil merah" type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php include __DIR__ . '/../config/footer.php'; ?>
