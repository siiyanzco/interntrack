<?php
// Koneksi database + fungsi bantu InternTrack
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DB_HOST = 'localhost';
$DB_NAME = 'interntrack';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    die('Koneksi database gagal: ' . htmlspecialchars($ex->getMessage()));
}

// Escape output (cegah XSS)
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Wajib login & cek role. $base = path relatif ke root aplikasi (mis. '../')
function wajib_login(array $roles, $base) {
    if (empty($_SESSION['user'])) {
        header('Location: ' . $base . 'auth/login.php');
        exit;
    }
    if (!in_array($_SESSION['user']['role'], $roles)) {
        header('Location: ' . $base . 'index.php');
        exit;
    }
}

// Pesan sekali tampil
function set_flash($pesan, $tipe = 'sukses') {
    $_SESSION['flash'] = ['pesan' => $pesan, 'tipe' => $tipe];
}
function tampil_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert ' . e($f['tipe']) . '">' . e($f['pesan']) . '</div>';
    }
}

// ALGORITMA 3: Penentuan status PKL berdasarkan persentase kehadiran
function status_kehadiran($persen) {
    if ($persen >= 90) return 'Sangat Baik';
    if ($persen >= 75) return 'Baik';
    if ($persen >= 60) return 'Perlu Perhatian';
    return 'Perlu Pembinaan';
}
function kelas_status($status) {
    switch ($status) {
        case 'Sangat Baik': return 'hijau';
        case 'Baik': return 'biru';
        case 'Perlu Perhatian': return 'kuning';
        case 'Perlu Pembinaan': return 'merah';
        default: return 'abu';
    }
}

// Hitung kehadiran 1 siswa: total hari, hadir, persentase
function hitung_kehadiran(PDO $pdo, $siswa_id) {
    $st = $pdo->prepare("SELECT COUNT(*) AS total, COALESCE(SUM(status='Hadir'),0) AS hadir FROM absensi WHERE siswa_id = ?");
    $st->execute([$siswa_id]);
    $r = $st->fetch();
    $total = (int)$r['total'];
    $hadir = (int)$r['hadir'];
    $persen = $total > 0 ? round($hadir / $total * 100, 1) : 0;
    return ['total' => $total, 'hadir' => $hadir, 'persen' => $persen,
            'status' => $total > 0 ? status_kehadiran($persen) : 'Belum ada data'];
}

// Rekap kehadiran semua siswa (dipakai dashboard, absensi, laporan)
function rekap_kehadiran(PDO $pdo) {
    $sql = "SELECT s.id, s.nis, s.nama, s.kelas,
                   COUNT(a.id) AS total,
                   COALESCE(SUM(a.status='Hadir'),0) AS hadir,
                   COALESCE(SUM(a.status='Izin'),0) AS izin,
                   COALESCE(SUM(a.status='Sakit'),0) AS sakit,
                   COALESCE(SUM(a.status='Alpa'),0) AS alpa
            FROM siswa s LEFT JOIN absensi a ON a.siswa_id = s.id
            GROUP BY s.id, s.nis, s.nama, s.kelas
            ORDER BY s.nama";
    $rows = $pdo->query($sql)->fetchAll();
    foreach ($rows as &$r) {
        $r['persen'] = $r['total'] > 0 ? round($r['hadir'] / $r['total'] * 100, 1) : 0;
        $r['status'] = $r['total'] > 0 ? status_kehadiran($r['persen']) : 'Belum ada data';
    }
    return $rows;
}

// ID siswa dari user yang login (untuk role siswa)
function siswa_login(PDO $pdo) {
    $st = $pdo->prepare("SELECT * FROM siswa WHERE user_id = ?");
    $st->execute([$_SESSION['user']['id']]);
    return $st->fetch();
}

function tgl($d) {
    return $d ? date('d-m-Y', strtotime($d)) : '-';
}
