<?php
require __DIR__ . '/functions.php';
redirect_if_logged_in();

// Pesan dari redirect (daftar whitelist, bukan menampilkan input mentah).
$messages = [
    'registered'     => ['success', 'Registrasi berhasil! Silakan login.'],
    'logged_out'     => ['success', 'Anda berhasil logout.'],
    'login_required' => ['error',   'Silakan login terlebih dahulu untuk mengakses halaman tersebut.'],
];
$flash = null;
if (isset($_GET['msg'], $messages[$_GET['msg']])) {
    [$type, $text] = $messages[$_GET['msg']];
    $flash = ['type' => $type, 'message' => $text];
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_valid()) {
        $errors[] = 'Sesi form tidak valid. Muat ulang halaman lalu coba lagi.';
    } elseif ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid!';
    } else {
        $user = find_user_by_email(read_users(), $email);

        if ($user && password_verify($password, $user['password'])) {
            // ✅ Login berhasil — set session
            login_user($user);

            if (!empty($_POST['remember'])) {
                set_remember_cookie((int) $user['id']);
            }

            header('Location: dashboard.php');
            exit;
        }
        // ❌ Pesan sengaja dibuat umum agar tidak membocorkan email yang terdaftar
        $errors[] = 'Email atau password salah!';
    }
}

render_header('Login');
?>
<h1>🔐 Login</h1>
<?php render_flash($flash); render_errors($errors); ?>

<form method="POST" action="" novalidate>
    <?= csrf_field() ?>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="nama@email.com" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" placeholder="Password" required>

    <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya (30 hari)</label>

    <button type="submit">Login</button>
</form>

<p class="muted">Belum punya akun? <a href="register.php">Daftar</a></p>
<?php render_footer(); ?>
