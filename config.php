<?php
declare(strict_types=1);

session_start();

const USERS_FILE = __DIR__ . '/data/users.json';

/*
 * Ganti dengan string acak yang panjang jika project dipakai sungguhan.
 * Jangan dibagikan ke orang lain.
 */
const APP_KEY = 'tugas-web-pertemuan-7-ubah-secret-ini-2026';

if (!file_exists(USERS_FILE)) {
    file_put_contents(USERS_FILE, json_encode([], JSON_PRETTY_PRINT));
}
