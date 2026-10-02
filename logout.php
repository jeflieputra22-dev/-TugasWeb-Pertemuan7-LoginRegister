<?php
require __DIR__ . '/functions.php';

// Cabut token "ingat saya" milik user ini (jika ada)
if (isset($_SESSION['user_id'])) {
    $users = read_users();
    foreach ($users as &$u) {
        if ((int) $u['id'] === (int) $_SESSION['user_id']) {
            unset($u['remember_token']);
        }
    }
    unset($u);
    write_users($users);
}
clear_remember_cookie();

// Hapus semua data session
$_SESSION = [];

// Hapus session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain']);
}

// Destroy session
session_destroy();

// Redirect ke login
header('Location: login.php?msg=logged_out');
exit;
