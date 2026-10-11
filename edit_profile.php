<?php
require_once __DIR__ . '/functions.php';

tryRememberLogin();
requireLogin();

$user = currentUser();
$errors = [];
$name = $user['name'] ?? '';
$email = $user['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $newPassword = $_POST['password'] ?? '';

    if ($name === '' || mb_strlen($name) < 3) {
        $errors[] = 'Nama wajib diisi minimal 3 karakter.';
    }

    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    $existing = findUserByEmail($newEmail);
    if ($existing !== null && strcasecmp($existing['email'], $user['email']) !== 0) {
        $errors[] = 'Email sudah digunakan akun lain.';
    }

    if ($newPassword !== '' && strlen($newPassword) < 6) {
        $errors[] = 'Password baru minimal 6 karakter.';
    }

    if (!$errors) {
        $users = getUsers();
        $index = findUserIndexByEmail($user['email']);

        if ($index >= 0) {
            $users[$index]['name'] = $name;
            $users[$index]['email'] = strtolower($newEmail);

            if ($newPassword !== '') {
                $users[$index]['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            if (saveUsers($users)) {
                $_SESSION['user_email'] = strtolower($newEmail);
                flash('success', 'Profile berhasil diperbarui.');
                redirect('dashboard.php');
            }

            $errors[] = 'Profile gagal disimpan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Edit Profile</h1>

        <?php if ($errors): ?>
            <div class="alert error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <label for="name">Nama</label>
            <input id="name" type="text" name="name" value="<?= e($name) ?>" required>

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?= e($email) ?>" required>

            <label for="password">Password Baru <small>(kosongkan jika tidak diubah)</small></label>
            <input id="password" type="password" name="password">

            <button type="submit">Simpan Perubahan</button>
        </form>

        <p class="link-text"><a href="dashboard.php">← Kembali ke Dashboard</a></p>
    </div>
</div>
</body>
</html>
