<?php
/** Bonus: halaman edit profil (nama, email, dan ganti password opsional). */
require __DIR__ . '/functions.php';
require_login();

$users = read_users();
$me = find_user_by_id($users, (int) $_SESSION['user_id']);
if (!$me) { // data user hilang dari JSON → paksa logout
    header('Location: logout.php');
    exit;
}

$errors = [];
$nama  = $me['nama'];
$email = $me['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama        = trim(preg_replace('/\s+/', ' ', (string) ($_POST['nama'] ?? '')));
    $email       = trim((string) ($_POST['email'] ?? ''));
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $current     = (string) ($_POST['current_password'] ?? '');

    if (!csrf_valid()) {
        $errors[] = 'Sesi form tidak valid. Muat ulang halaman lalu coba lagi.';
    }
    if ($nama === '' || $email === '') {
        $errors[] = 'Nama dan email wajib diisi!';
    }
    if ($nama !== '' && str_length($nama) > 100) {
        $errors[] = 'Nama maksimal 100 karakter.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid!';
    }
    if ($newPassword !== '' && strlen($newPassword) < 6) {
        $errors[] = 'Password baru minimal 6 karakter!';
    }
    // Ganti password atau ganti email butuh konfirmasi password saat ini
    $needsConfirm = $newPassword !== '' || strcasecmp($email, $me['email']) !== 0;
    if (!$errors && $needsConfirm && !password_verify($current, $me['password'])) {
        $errors[] = 'Password saat ini salah (wajib diisi untuk mengganti email/password).';
    }
    if (!$errors) {
        $other = find_user_by_email($users, $email);
        if ($other && (int) $other['id'] !== (int) $me['id']) {
            $errors[] = 'Email sudah dipakai akun lain!';
        }
    }

    if (!$errors) {
        foreach ($users as &$u) {
            if ((int) $u['id'] === (int) $me['id']) {
                $u['nama']  = $nama;
                $u['email'] = $email;
                if ($newPassword !== '') {
                    $u['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                    unset($u['remember_token']); // cabut semua perangkat "ingat saya"
                }
            }
        }
        unset($u);

        if (write_users($users)) {
            $_SESSION['username'] = $nama;
            $_SESSION['email']    = $email;
            set_flash('success', 'Profil berhasil diperbarui.');
            header('Location: dashboard.php');
            exit;
        }
        $errors[] = 'Gagal menyimpan data. Periksa izin tulis file users.json.';
    }
}

render_header('Edit Profil');
?>
<h1>✏️ Edit Profil</h1>
<?php render_errors($errors); ?>

<form method="POST" action="" novalidate>
    <?= csrf_field() ?>

    <label for="nama">Nama Lengkap</label>
    <input type="text" id="nama" name="nama" value="<?= e($nama) ?>" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= e($email) ?>" required>

    <label for="new_password">Password Baru <span class="muted">(kosongkan jika tidak diganti)</span></label>
    <input type="password" id="new_password" name="new_password" placeholder="Min 6 karakter">

    <label for="current_password">Password Saat Ini <span class="muted">(wajib jika ganti email/password)</span></label>
    <input type="password" id="current_password" name="current_password">

    <button type="submit">Simpan Perubahan</button>
</form>

<p class="muted"><a href="dashboard.php">← Kembali ke Dashboard</a></p>
<?php render_footer(); ?>
