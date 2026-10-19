<?php
require __DIR__ . '/../config/koneksi.php';
$base = '../';
wajib_login(['admin', 'pembimbing'], $base);

$aksi = $_GET['aksi'] ?? 'list';
$errors = [];
$data = ['nis' => '', 'nama' => '', 'kelas' => '', 'jurusan' => '', 'no_hp' => ''];

// ---------- HAPUS ----------
if ($aksi === 'hapus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $pdo->prepare("SELECT user_id FROM siswa WHERE id = ?");
    $st->execute([(int)($_POST['id'] ?? 0)]);
    $uid = $st->fetchColumn();
    if ($uid) {
        // hapus user -> siswa & data turunannya ikut terhapus (ON DELETE CASCADE)
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
        set_flash('Data siswa berhasil dihapus.');
    }
    header('Location: siswa.php');
    exit;
}

// ---------- SIMPAN (tambah / edit) ----------
if (in_array($aksi, ['tambah', 'edit']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    foreach ($data as $k => $v) {
        $data[$k] = trim($_POST[$k] ?? '');
    }
    $password = $_POST['password'] ?? '';

    // Validasi
    if ($data['nis'] === '')  $errors[] = 'NIS tidak boleh kosong.';
    if ($data['nama'] === '') $errors[] = 'Nama tidak boleh kosong.';
    if ($data['nis'] !== '') {
        $st = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE nis = ? AND id <> ?");
        $st->execute([$data['nis'], $id]);
        if ($st->fetchColumn() > 0) $errors[] = 'NIS sudah terdaftar (tidak boleh duplikat).';
        $st = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id NOT IN (SELECT user_id FROM siswa WHERE id = ?)");
        $st->execute([$data['nis'], $id]);
        if ($st->fetchColumn() > 0) $errors[] = 'NIS dipakai sebagai username lain.';
    }

    if (!$errors) {
        if ($aksi === 'tambah') {
            $pdo->beginTransaction();
            $hash = password_hash($password !== '' ? $password : 'password', PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (username,password,nama,role) VALUES (?,?,?, 'siswa')")
                ->execute([$data['nis'], $hash, $data['nama']]);
            $uid = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO siswa (user_id,nis,nama,kelas,jurusan,no_hp) VALUES (?,?,?,?,?,?)")
                ->execute([$uid, $data['nis'], $data['nama'], $data['kelas'], $data['jurusan'], $data['no_hp']]);
            $pdo->commit();
            set_flash('Data siswa berhasil ditambahkan.');
        } else {
            $st = $pdo->prepare("SELECT user_id FROM siswa WHERE id = ?");
            $st->execute([$id]);
            $uid = $st->fetchColumn();
            $pdo->prepare("UPDATE siswa SET nis=?, nama=?, kelas=?, jurusan=?, no_hp=? WHERE id=?")
                ->execute([$data['nis'], $data['nama'], $data['kelas'], $data['jurusan'], $data['no_hp'], $id]);
            $pdo->prepare("UPDATE users SET username=?, nama=? WHERE id=?")
                ->execute([$data['nis'], $data['nama'], $uid]);
            if ($password !== '') {
                $pdo->prepare("UPDATE users SET password=? WHERE id=?")
                    ->execute([password_hash($password, PASSWORD_DEFAULT), $uid]);
            }
            set_flash('Data siswa berhasil diperbarui.');
        }
        header('Location: siswa.php');
        exit;
    }
}

// Ambil data untuk form edit
if ($aksi === 'edit' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $st = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
    $st->execute([(int)($_GET['id'] ?? 0)]);
    $data = $st->fetch();
    if (!$data) { header('Location: siswa.php'); exit; }
}

$judul = 'Data Siswa';
include __DIR__ . '/../config/header.php';

// ---------- FORM ----------
if ($aksi === 'tambah' || $aksi === 'edit'):
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
?>
<div class="card">
    <h3><?= $aksi === 'tambah' ? 'Tambah' : 'Edit' ?> Siswa</h3>
    <?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" action="siswa.php?aksi=<?= e($aksi) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <label>NIS</label>
        <input type="text" name="nis" value="<?= e($data['nis']) ?>">
        <label>Nama</label>
        <input type="text" name="nama" value="<?= e($data['nama']) ?>">
        <label>Kelas</label>
        <input type="text" name="kelas" value="<?= e($data['kelas']) ?>" placeholder="contoh: XII RPL 1">
        <label>Jurusan</label>
        <input type="text" name="jurusan" value="<?= e($data['jurusan']) ?>" placeholder="contoh: Rekayasa Perangkat Lunak">
        <label>No. HP</label>
        <input type="text" name="no_hp" value="<?= e($data['no_hp']) ?>">
        <label>Password login <?= $aksi === 'edit' ? '(kosongkan jika tidak diubah)' : '(kosong = "password")' ?></label>
        <input type="password" name="password">
        <p>Username siswa = NIS.</p>
        <button class="btn" type="submit">Simpan</button>
        <a class="btn abu" href="siswa.php">Batal</a>
    </form>
</div>
<?php
// ---------- DETAIL ----------
elseif ($aksi === 'detail'):
    $st = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
    $st->execute([(int)($_GET['id'] ?? 0)]);
    $s = $st->fetch();
    if (!$s) { echo '<div class="alert error">Data tidak ditemukan.</div>'; include __DIR__ . '/../config/footer.php'; exit; }
    $st = $pdo->prepare("SELECT p.*, pr.nama_perusahaan, pb.nama AS nama_pembimbing
                         FROM penempatan p JOIN perusahaan pr ON pr.id=p.perusahaan_id JOIN pembimbing pb ON pb.id=p.pembimbing_id
                         WHERE p.siswa_id = ?");
    $st->execute([$s['id']]);
    $pen = $st->fetch();
    $k = hitung_kehadiran($pdo, $s['id']);
    $st = $pdo->prepare("SELECT COUNT(*) FROM jurnal WHERE siswa_id = ?");
    $st->execute([$s['id']]);
    $jml_jurnal = $st->fetchColumn();
?>
<div class="card">
    <h3>Detail Siswa</h3>
    <table class="detail">
        <tr><td>NIS</td><td><?= e($s['nis']) ?></td></tr>
        <tr><td>Nama</td><td><?= e($s['nama']) ?></td></tr>
        <tr><td>Kelas</td><td><?= e($s['kelas']) ?></td></tr>
        <tr><td>Jurusan</td><td><?= e($s['jurusan']) ?></td></tr>
        <tr><td>No. HP</td><td><?= e($s['no_hp']) ?></td></tr>
        <tr><td>Perusahaan</td><td><?= $pen ? e($pen['nama_perusahaan']) : 'Belum ditempatkan' ?></td></tr>
        <tr><td>Pembimbing</td><td><?= $pen ? e($pen['nama_pembimbing']) : '-' ?></td></tr>
        <tr><td>Status Penempatan</td><td><?= $pen ? e($pen['status']) : '-' ?></td></tr>
        <tr><td>Kehadiran</td><td><?= $k['hadir'] ?> / <?= $k['total'] ?> hari (<?= $k['persen'] ?>%)
            <span class="badge <?= kelas_status($k['status']) ?>"><?= e($k['status']) ?></span></td></tr>
        <tr><td>Jumlah Jurnal</td><td><?= $jml_jurnal ?></td></tr>
    </table>
    <p><a class="btn abu" href="siswa.php">Kembali</a></p>
</div>
<?php
// ---------- LIST + SEARCH + FILTER ----------
else:
    $q = trim($_GET['q'] ?? '');
    $f_kelas = $_GET['kelas'] ?? '';
    $f_per = (int)($_GET['perusahaan'] ?? 0);
    $f_status = $_GET['status'] ?? '';
    $f_pemb = (int)($_GET['pembimbing'] ?? 0);

    $where = [];
    $par = [];
    if ($q !== '') { $where[] = "(s.nama LIKE ? OR s.nis LIKE ?)"; $par[] = "%$q%"; $par[] = "%$q%"; }
    if ($f_kelas !== '') { $where[] = "s.kelas = ?"; $par[] = $f_kelas; }
    if ($f_per) { $where[] = "p.perusahaan_id = ?"; $par[] = $f_per; }
    if ($f_status !== '') { $where[] = "p.status = ?"; $par[] = $f_status; }
    if ($f_pemb) { $where[] = "p.pembimbing_id = ?"; $par[] = $f_pemb; }

    $sql = "SELECT s.*, p.status AS status_pkl, pr.nama_perusahaan, pb.nama AS nama_pembimbing
            FROM siswa s
            LEFT JOIN penempatan p ON p.siswa_id = s.id
            LEFT JOIN perusahaan pr ON pr.id = p.perusahaan_id
            LEFT JOIN pembimbing pb ON pb.id = p.pembimbing_id"
         . ($where ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY s.nama";
    $st = $pdo->prepare($sql);
    $st->execute($par);
    $rows = $st->fetchAll();

    $list_kelas = $pdo->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas")->fetchAll(PDO::FETCH_COLUMN);
    $list_per = $pdo->query("SELECT id, nama_perusahaan FROM perusahaan ORDER BY nama_perusahaan")->fetchAll();
    $list_pemb = $pdo->query("SELECT id, nama FROM pembimbing ORDER BY nama")->fetchAll();
?>
<div class="card">
    <form method="get" class="filter">
        <div><label>Cari (nama / NIS)</label><input type="text" name="q" value="<?= e($q) ?>"></div>
        <div><label>Kelas</label>
            <select name="kelas"><option value="">Semua</option>
            <?php foreach ($list_kelas as $kl): ?><option value="<?= e($kl) ?>" <?= $f_kelas === $kl ? 'selected' : '' ?>><?= e($kl) ?></option><?php endforeach; ?>
            </select></div>
        <div><label>Perusahaan</label>
            <select name="perusahaan"><option value="0">Semua</option>
            <?php foreach ($list_per as $x): ?><option value="<?= $x['id'] ?>" <?= $f_per == $x['id'] ? 'selected' : '' ?>><?= e($x['nama_perusahaan']) ?></option><?php endforeach; ?>
            </select></div>
        <div><label>Status PKL</label>
            <select name="status"><option value="">Semua</option>
            <?php foreach (['Belum Mulai', 'Aktif', 'Selesai'] as $sv): ?><option <?= $f_status === $sv ? 'selected' : '' ?>><?= $sv ?></option><?php endforeach; ?>
            </select></div>
        <div><label>Pembimbing</label>
            <select name="pembimbing"><option value="0">Semua</option>
            <?php foreach ($list_pemb as $x): ?><option value="<?= $x['id'] ?>" <?= $f_pemb == $x['id'] ? 'selected' : '' ?>><?= e($x['nama']) ?></option><?php endforeach; ?>
            </select></div>
        <div style="flex:0"><button class="btn" type="submit">Cari / Filter</button></div>
        <div style="flex:0"><a class="btn abu" href="siswa.php">Reset</a></div>
    </form>
    <a class="btn biru" href="siswa.php?aksi=tambah">+ Tambah Siswa</a>
</div>
<table>
    <tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Perusahaan</th><th>Pembimbing</th><th>Status PKL</th><th>Aksi</th></tr>
    <?php if (!$rows): ?><tr><td colspan="8">Data tidak ditemukan.</td></tr><?php endif; ?>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($r['nis']) ?></td>
        <td><?= e($r['nama']) ?></td>
        <td><?= e($r['kelas']) ?></td>
        <td><?= e($r['nama_perusahaan'] ?? '-') ?></td>
        <td><?= e($r['nama_pembimbing'] ?? '-') ?></td>
        <td><?= e($r['status_pkl'] ?? '-') ?></td>
        <td>
            <a class="btn kecil biru" href="siswa.php?aksi=detail&id=<?= $r['id'] ?>">Detail</a>
            <a class="btn kecil kuning" href="siswa.php?aksi=edit&id=<?= $r['id'] ?>">Edit</a>
            <form class="inline" method="post" action="siswa.php?aksi=hapus" onsubmit="return confirm('Hapus siswa ini beserta jurnal/absensinya?')">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn kecil merah" type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php include __DIR__ . '/../config/footer.php'; ?>
