<?php
require_once __DIR__ . '/functions.php';

tryRememberLogin();

if (!empty($_SESSION['user_email'])) {
    redirect('dashboard.php');
}

redirect('login.php');
