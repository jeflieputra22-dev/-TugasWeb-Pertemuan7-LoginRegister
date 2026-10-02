<?php
require __DIR__ . '/functions.php';
require_login(); // 🔒 PROTEKSI: redirect ke login.php jika belum login

$user = find_user_by_id(read_users(), (int) $_SESSION['user_id']);
$flash = get_flash();

render_header('Dashboard');
?>
<h1>🏠 Dashboard</h1>
<?php render_flash($flash); ?>

<p>Selamat datang, <strong><?= e($_SESSION['username']) ?></strong>!</p>

<table class="info">
    <tr><th>ID</th><td><?= e($_SESSION['user_id']) ?></td></tr>
    <tr><th>Email</th><td><?= e($_SESSION['email']) ?></td></tr>
    <tr><th>Terdaftar sejak</th><td><?= e($user['created_at'] ?? '-') ?></td></tr>
</table>

<p class="actions">
    <a class="btn" href="profile.php">✏️ Edit Profil</a>
    <a class="btn btn-danger" href="logout.php">🚪 Logout</a>
</p>
<?php render_footer(); ?>
