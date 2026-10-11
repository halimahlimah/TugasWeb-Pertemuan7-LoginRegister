<?php
require_once __DIR__ . '/functions.php';

$email = $_SESSION['user_email'] ?? null;

if ($email) {
    $users = getUsers();
    $index = findUserIndexByEmail($email);

    if ($index >= 0) {
        $users[$index]['remember_token_hash'] = null;
        saveUsers($users);
    }
}

clearRememberCookie();

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
}

session_destroy();

header('Location: login.php');
exit;
