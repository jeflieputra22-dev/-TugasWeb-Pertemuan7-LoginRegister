<?php
require __DIR__ . '/functions.php';
redirect_if_logged_in();

$errors = [];
$nama = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim(preg_replace('/\s+/', ' ', (string) ($_POST['nama'] ?? '')));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['confirm_password'] ?? '');

    // --- Validasi ---
    if (!csrf_valid()) {
        $errors[] = 'Sesi form tidak valid. Muat ulang halaman lalu coba lagi.';
    }
    if ($nama === '' || $email === '' || $password === '') {
        $errors[] = 'Semua field wajib diisi!';
    }
    if ($nama !== '' && str_length($nama) > 100) {
        $errors[] = 'Nama maksimal 100 karakter.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid!';
    }
    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter!';
    }
    if ($password !== $confirm) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }

    // --- Cek duplikasi email & simpan ---
    if (!$errors) {
        $users = read_users();

        if (find_user_by_email($users, $email)) {
            $errors[] = 'Email sudah terdaftar!';
        } else {
            $users[] = [
                'id'         => next_user_id($users),
                'nama'       => $nama, // disanitasi dengan e() saat ditampilkan
                'email'      => $email,
                'password'   => password_hash($password, PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if (write_users($users)) {
                header('Location: login.php?msg=registered');
                exit;
            }
            $errors[] = 'Gagal menyimpan data. Periksa izin tulis file users.json.';
        }
    }
}

render_header('Registrasi');
?>
<h1>📝 Registrasi</h1>
<?php render_errors($errors); ?>

<form method="POST" action="" novalidate>
    <?= csrf_field() ?>

    <label for="nama">Nama Lengkap</label>
    <input type="text" id="nama" name="nama" value="<?= e($nama) ?>" placeholder="Nama Lengkap" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="nama@email.com" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" placeholder="Password (min 6 karakter)" required>

    <label for="confirm_password">Konfirmasi Password</label>
    <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>

    <button type="submit">Daftar</button>
</form>

<p class="muted">Sudah punya akun? <a href="login.php">Login</a></p>
<?php render_footer(); ?>
