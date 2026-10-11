<?php
require_once __DIR__ . '/functions.php';

if (!empty($_SESSION['user_email'])) {
    redirect('dashboard.php');
}

$errors = [];
$oldName = '';
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldName = trim($_POST['name'] ?? '');
    $oldEmail = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($oldName === '' || mb_strlen($oldName) < 3) {
        $errors[] = 'Nama wajib diisi minimal 3 karakter.';
    }

    if (!filter_var($oldEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }

    if (findUserByEmail($oldEmail) !== null) {
        $errors[] = 'Email sudah terdaftar. Gunakan email lain.';
    }

    if (!$errors) {
        $users = getUsers();
        $users[] = [
            'id' => bin2hex(random_bytes(8)),
            'name' => $oldName,
            'email' => strtolower($oldEmail),
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'remember_token_hash' => null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if (saveUsers($users)) {
            flash('success', 'Registrasi berhasil. Silakan login.');
            redirect('login.php');
        }

        $errors[] = 'Data gagal disimpan. Coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Tugas Pertemuan 7</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Daftar Akun</h1>
        <p class="subtitle">Sistem Login/Register PHP Native + JSON</p>

        <?php if ($errors): ?>
            <div class="alert error">
                <strong>Periksa data:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <label for="name">Nama</label>
            <input id="name" type="text" name="name" value="<?= e($oldName) ?>" required>

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?= e($oldEmail) ?>" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <label for="confirm_password">Konfirmasi Password</label>
            <input id="confirm_password" type="password" name="confirm_password" required>

            <button type="submit">Register</button>
        </form>

        <p class="link-text">Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
</div>
</body>
</html>
