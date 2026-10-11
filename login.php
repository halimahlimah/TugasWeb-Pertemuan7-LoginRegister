<?php
require_once __DIR__ . '/functions.php';

tryRememberLogin();

if (!empty($_SESSION['user_email'])) {
    redirect('dashboard.php');
}

$errors = [];
$email = '';
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if ($password === '') {
        $errors[] = 'Password wajib diisi.';
    }

    if (!$errors) {
        $user = findUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Email atau password salah.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_email'] = $user['email'];

            if ($remember) {
                $token = createRememberToken($user['email']);
                $users = getUsers();
                $index = findUserIndexByEmail($user['email']);

                if ($index >= 0) {
                    $users[$index]['remember_token_hash'] = hash('sha256', $token);
                    saveUsers($users);
                    setRememberCookie($user['email'], $token);
                }
            }

            flash('success', 'Login berhasil. Selamat datang!');
            redirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tugas Pertemuan 7</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Login</h1>
        <p class="subtitle">Silakan masuk ke akun Anda</p>

        <?php if ($flash): ?>
            <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

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
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?= e($email) ?>" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <label class="checkbox">
                <input type="checkbox" name="remember">
                <span>Remember Me</span>
            </label>

            <button type="submit">Login</button>
        </form>

        <p class="link-text">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
    </div>
</div>
</body>
</html>
