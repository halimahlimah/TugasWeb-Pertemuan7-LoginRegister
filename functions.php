<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header("Location: $url");
    exit;
}

function getUsers(): array
{
    $json = @file_get_contents(USERS_FILE);
    if ($json === false || trim($json) === '') {
        return [];
    }

    $users = json_decode($json, true);
    return is_array($users) ? $users : [];
}

function saveUsers(array $users): bool
{
    $json = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }

    return file_put_contents(USERS_FILE, $json, LOCK_EX) !== false;
}

function findUserByEmail(string $email): ?array
{
    foreach (getUsers() as $user) {
        if (strcasecmp($user['email'] ?? '', $email) === 0) {
            return $user;
        }
    }

    return null;
}

function findUserIndexByEmail(string $email): int
{
    foreach (getUsers() as $index => $user) {
        if (strcasecmp($user['email'] ?? '', $email) === 0) {
            return (int) $index;
        }
    }

    return -1;
}

function requireLogin(): void
{
    if (empty($_SESSION['user_email'])) {
        redirect('login.php');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

function createRememberToken(string $email): string
{
    return hash_hmac('sha256', $email . '|' . bin2hex(random_bytes(32)), APP_KEY);
}

function setRememberCookie(string $email, string $token): void
{
    $value = base64_encode(json_encode([
        'email' => $email,
        'token' => $token
    ]));

    setcookie('remember_me', $value, [
        'expires' => time() + (60 * 60 * 24 * 30),
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function clearRememberCookie(): void
{
    setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function tryRememberLogin(): void
{
    if (!empty($_SESSION['user_email']) || empty($_COOKIE['remember_me'])) {
        return;
    }

    $decoded = base64_decode($_COOKIE['remember_me'], true);
    if ($decoded === false) {
        clearRememberCookie();
        return;
    }

    $data = json_decode($decoded, true);
    if (!is_array($data) || empty($data['email']) || empty($data['token'])) {
        clearRememberCookie();
        return;
    }

    $users = getUsers();
    foreach ($users as $user) {
        if (
            strcasecmp($user['email'] ?? '', $data['email']) === 0 &&
            !empty($user['remember_token_hash']) &&
            hash_equals($user['remember_token_hash'], hash('sha256', $data['token']))
        ) {
            session_regenerate_id(true);
            $_SESSION['user_email'] = $user['email'];
            return;
        }
    }

    clearRememberCookie();
}

function currentUser(): ?array
{
    if (empty($_SESSION['user_email'])) {
        return null;
    }

    return findUserByEmail($_SESSION['user_email']);
}
