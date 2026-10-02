<?php
/**
 * functions.php — fungsi bersama untuk seluruh halaman.
 * Tugas Rutin 7 — Pemrograman Web (PHP Native + JSON sebagai penyimpanan).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const USERS_FILE     = __DIR__ . '/users.json';
const REMEMBER_COOKIE = 'remember_me';
const REMEMBER_DAYS  = 30;

/* ---------- Sanitasi output ---------- */

// Selalu gunakan e() saat menampilkan data user ke HTML (cegah XSS).
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Panjang string; tidak bergantung pada ekstensi mbstring.
function str_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

/* ---------- Penyimpanan JSON ---------- */

function read_users(): array
{
    if (!file_exists(USERS_FILE)) {
        return [];
    }
    $users = json_decode(file_get_contents(USERS_FILE), true);
    return is_array($users) ? $users : [];
}

function write_users(array $users): bool
{
    $json = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents(USERS_FILE, $json, LOCK_EX) !== false;
}

function find_user_by_email(array $users, string $email): ?array
{
    foreach ($users as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            return $user;
        }
    }
    return null;
}

function find_user_by_id(array $users, int $id): ?array
{
    foreach ($users as $user) {
        if ((int) $user['id'] === $id) {
            return $user;
        }
    }
    return null;
}

function next_user_id(array $users): int
{
    return $users ? max(array_column($users, 'id')) + 1 : 1;
}

/* ---------- Flash message ---------- */

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent) && hash_equals(csrf_token(), $sent);
}

/* ---------- Autentikasi ---------- */

function login_user(array $user): void
{
    session_regenerate_id(true); // cegah session fixation
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['nama'];
    $_SESSION['email']    = $user['email'];
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

// Proteksi halaman: redirect ke login jika belum login.
function require_login(): void
{
    try_auto_login();
    if (!is_logged_in()) {
        header('Location: login.php?msg=login_required');
        exit;
    }
}

// Jika sudah login, tidak perlu melihat halaman login/register lagi.
function redirect_if_logged_in(): void
{
    try_auto_login();
    if (is_logged_in()) {
        header('Location: dashboard.php');
        exit;
    }
}

/* ---------- Remember Me (bonus, cookies) ---------- */

function cookie_options(int $expires): array
{
    return [
        'expires'  => $expires,
        'path'     => '/',
        'httponly' => true,   // tidak bisa dibaca JavaScript
        'samesite' => 'Lax',
    ];
}

// Simpan hash token di users.json; token asli hanya ada di cookie user.
function set_remember_cookie(int $userId): void
{
    $users = read_users();
    $token = bin2hex(random_bytes(32));

    foreach ($users as &$u) {
        if ((int) $u['id'] === $userId) {
            $u['remember_token'] = hash('sha256', $token);
        }
    }
    unset($u);
    write_users($users);

    setcookie(REMEMBER_COOKIE, $userId . ':' . $token, cookie_options(time() + 86400 * REMEMBER_DAYS));
}

function clear_remember_cookie(): void
{
    setcookie(REMEMBER_COOKIE, '', cookie_options(time() - 3600));
    unset($_COOKIE[REMEMBER_COOKIE]);
}

function try_auto_login(): void
{
    if (is_logged_in() || empty($_COOKIE[REMEMBER_COOKIE])) {
        return;
    }

    $parts = explode(':', (string) $_COOKIE[REMEMBER_COOKIE], 2);
    if (count($parts) !== 2 || !ctype_digit($parts[0])) {
        clear_remember_cookie();
        return;
    }

    $user = find_user_by_id(read_users(), (int) $parts[0]);
    if ($user && !empty($user['remember_token'])
        && hash_equals($user['remember_token'], hash('sha256', $parts[1]))) {
        login_user($user);
    } else {
        clear_remember_cookie();
    }
}

/* ---------- Layout ---------- */

function render_header(string $title): void
{
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
       . '<title>' . e($title) . ' — Login/Register PHP</title>'
       . '<link rel="stylesheet" href="style.css"></head><body><main class="card">';
}

function render_footer(): void
{
    echo '</main></body></html>';
}

function render_flash(?array $flash): void
{
    if ($flash) {
        echo '<div class="alert alert-' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
    }
}

function render_errors(array $errors): void
{
    if ($errors) {
        echo '<div class="alert alert-error"><ul>';
        foreach ($errors as $err) {
            echo '<li>' . e($err) . '</li>';
        }
        echo '</ul></div>';
    }
}
