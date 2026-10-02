# TugasWeb-Pertemuan7-LoginRegister

Tugas Rutin 7 — Pemrograman Web (3KOM40115), FMIPA UNIMED, Semester Ganjil 2026/2027.
Sistem login/register sederhana dengan **PHP Native** dan **file JSON** sebagai penyimpanan data.

## Cara Menjalankan
1. Pastikan PHP 8.x terpasang (Laragon / XAMPP).
2. Salin folder ini ke `htdocs` (XAMPP) atau `www` (Laragon), lalu buka `http://localhost/TugasWeb-Pertemuan7-LoginRegister/register.php`.
3. Alternatif tanpa Apache: jalankan `php -S localhost:8000` di dalam folder ini, lalu buka `http://localhost:8000/register.php`.
4. Pastikan file `users.json` dapat ditulis oleh web server.

## Akun Contoh (`users.json`)
| Email | Password |
|---|---|
| adid@unimed.ac.id | password123 |
| budi@example.com | rahasia456 |

## Struktur File
| File | Fungsi |
|---|---|
| `register.php` | Form registrasi + validasi + simpan ke JSON |
| `login.php` | Proses login dengan session (+ Remember Me) |
| `dashboard.php` | Halaman terproteksi (redirect jika belum login) |
| `logout.php` | Hapus session & cookie, `session_destroy()` |
| `profile.php` | **Bonus:** edit profil & ganti password |
| `functions.php` | Fungsi bersama (JSON, session, CSRF, layout) |
| `style.css` | **Bonus:** tampilan CSS |
| `users.json` | Penyimpanan data (berisi contoh data) |

## Checklist Requirements
| # | Requirement | Implementasi |
|---|---|---|
| 1 | Form registrasi dengan validasi | `register.php` (nama, email, password, konfirmasi password) |
| 2 | Validasi email `filter_var()` | `FILTER_VALIDATE_EMAIL` di register, login, profile |
| 3 | Password di-hash `password_hash()` | `PASSWORD_DEFAULT`; login memakai `password_verify()` |
| 4 | Data disimpan di file JSON | `users.json` (dengan `LOCK_EX` saat menulis) |
| 5 | Cek duplikasi email | `find_user_by_email()` (tidak peka huruf besar/kecil) |
| 6 | Login dengan session | `$_SESSION['user_id' / 'username' / 'email']` + `session_regenerate_id()` |
| 7 | Dashboard terproteksi | `require_login()` → redirect ke `login.php` |
| 8 | Logout | `session_destroy()` + hapus session cookie |
| 9 | Sanitasi `htmlspecialchars()` | Fungsi `e()` dipakai pada setiap output data user |
| 10 | Pesan error & sukses jelas | Daftar error per field + flash message |

**Bonus:** Remember Me (cookie `HttpOnly`, token di-hash SHA-256 di JSON), halaman edit profil, CSS rapi, serta proteksi CSRF.

## Catatan Desain
- Input disimpan apa adanya (setelah `trim`) dan di-escape dengan `htmlspecialchars()` **saat ditampilkan**. Ini menghindari *double encoding* (mis. `&amp;amp;`) dan tetap mencegah XSS.
- Pesan login gagal sengaja umum ("Email atau password salah!") agar tidak membocorkan email yang terdaftar.
- File JSON hanya untuk pembelajaran; untuk produksi gunakan database (Pertemuan 9).
