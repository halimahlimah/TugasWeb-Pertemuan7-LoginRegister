<?php
require_once __DIR__ . '/functions.php';

tryRememberLogin();
requireLogin();

$user = currentUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tugas Pertemuan 7</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="card dashboard">
        <h1>Dashboard</h1>

        <?php if ($flash): ?>
            <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <div class="profile-box">
            <h2>Selamat datang, <?= e($user['name'] ?? '') ?>!</h2>
            <p><strong>Nama:</strong> <?= e($user['name'] ?? '') ?></p>
            <p><strong>Email:</strong> <?= e($user['email'] ?? '') ?></p>
        </div>

        <div class="actions">
            <a class="button secondary" href="edit_profile.php">Edit Profile</a>
            <a class="button danger" href="logout.php">Logout</a>
        </div>
    </div>
</div>
</body>
</html>
