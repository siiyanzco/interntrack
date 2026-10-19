<?php
// ALGORITMA 1: Login pengguna
require __DIR__ . '/../config/koneksi.php';

if (!empty($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $st = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $st->execute([$username]);
        $u = $st->fetch();
        if ($u && password_verify($password, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => $u['id'], 'username' => $u['username'], 'nama' => $u['nama'], 'role' => $u['role']];
            header('Location: ../index.php');
            exit;
        }
        $error = 'Username atau Password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - InternTrack</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="login-box">
    <h1>InternTrack</h1>
    <p class="sub">Sistem Monitoring PKL Siswa</p>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" required autofocus>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <button type="submit" class="btn">Login</button>
    </form>
    <p style="font-size:12px;color:#777;margin-top:16px">
         admin / admin123 &nbsp;|&nbsp; pembimbing1 / password &nbsp;|&nbsp; siswa: NIS 2026001 / password
    </p>
</div>
</body>
</html>
