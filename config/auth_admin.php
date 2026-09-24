<?php

/* =====================================================================
   PENANDA LOGIN ADMIN (COOKIE)              config/auth_admin.php
   ---------------------------------------------------------------------
   MASALAHNYA:
   Cara biasa menyimpan status "sudah login" adalah session PHP. Itu
   aman di XAMPP, tetapi di Vercel session mudah hilang (server yang
   menjalankan PHP berganti-ganti), akibatnya admin tiba-tiba ter-logout
   sendiri padahal belum menekan tombol Keluar.

   SOLUSINYA (sama seperti yang dipakai proyek kampungberu):
   Ketika admin berhasil login, kita membuat satu token acak 64 huruf.
     - Yang disimpan di database hanya SANDINYA (SHA-256), bukan tokennya.
     - Token aslinya dikirim ke browser lewat cookie bernama `sesi_admin`.
     - Masa berlaku 7 hari, dan setiap login lama dibersihkan.
   Saat halaman admin dibuka, kalau session kosong, token di cookie
   diperiksa ke tabel `sesi_admin`; kalau cocok, admin dianggap masih
   login dan $_SESSION diisi kembali (jadi kode halaman tidak berubah).

   Di mode lokal (XAMPP) semua ini TIDAK dipakai — session seperti biasa,
   jadi perilaku website di komputer Bapak tidak berubah sama sekali.
   ===================================================================== */


/* =====================================================================
   1. SETELAN DASAR
===================================================================== */

/** Nama cookie penanda login. */
function auth_nama_cookie(): string
{
    return 'sesi_admin';
}

/** Lama login diingat: 7 hari (dalam detik). */
function auth_umur(): int
{
    return 7 * 24 * 60 * 60;
}

/** Apakah cookie dipakai? (hanya di mode online) */
function auth_aktif(): bool
{
    return function_exists('db_lapisan') && db_lapisan() === 'supabase';
}

/** Apakah website dibuka lewat HTTPS? (di Vercel selalu HTTPS) */
function auth_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }

    if (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') {
        return true;
    }

    if ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443') {
        return true;
    }

    return false;
}

/** Sandi token yang disimpan di database. */
function auth_token_hash(string $token): string
{
    return hash('sha256', $token);
}


/* =====================================================================
   2. COOKIE
===================================================================== */

function auth_tulis_cookie(string $token, int $umur): void
{
    $setelan = [
        'expires'  => time() + $umur,
        'path'     => '/',
        'secure'   => auth_https(),      // hanya dikirim lewat HTTPS
        'httponly' => true,              // tidak bisa dibaca JavaScript
        'samesite' => 'Lax',
    ];

    setcookie(auth_nama_cookie(), $token, $setelan);

    $_COOKIE[auth_nama_cookie()] = $token;
}

function auth_hapus_cookie(): void
{
    $setelan = [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => auth_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];

    setcookie(auth_nama_cookie(), '', $setelan);

    unset($_COOKIE[auth_nama_cookie()]);
}


/* =====================================================================
   3. MEMBUAT, MEMBACA, DAN MENGHAPUS PENANDA LOGIN
===================================================================== */

/**
 * Dipanggil setelah username & password benar.
 *
 * Selalu mengisi $_SESSION (seperti cara lama). Di mode online,
 * sekaligus membuat token + cookie supaya login bertahan lama.
 */
function auth_buat_sesi($koneksi, int $id_admin, string $nama = '', string $username = ''): bool
{
    $_SESSION['admin_id']       = $id_admin;
    $_SESSION['admin_nama']     = $nama;
    $_SESSION['admin_username'] = $username;

    if (!auth_aktif()) {
        return true;                    // mode lokal: cukup session
    }

    /* Buang penanda login yang sudah kedaluwarsa (sekalian bersih-bersih) */
    auth_bersihkan($koneksi);

    $token      = bin2hex(random_bytes(32));         // 64 huruf acak
    $token_hash = auth_token_hash($token);           // sandinya untuk database
    $kadaluarsa = date('Y-m-d H:i:s', time() + auth_umur());

    $stmt = db_prepare(
        $koneksi,
        "INSERT INTO sesi_admin (token_hash, id_admin, kedaluwarsa_at)
         VALUES (?, ?, ?)"
    );

    if (!$stmt) {
        return false;
    }

    db_stmt_bind_param($stmt, 'sis', $token_hash, $id_admin, $kadaluarsa);

    $berhasil = db_stmt_execute($stmt);

    db_stmt_close($stmt);

    if (!$berhasil) {
        return false;                   // login tetap jalan lewat session
    }

    auth_tulis_cookie($token, auth_umur());

    return true;
}

/**
 * Mencoba masuk memakai cookie `sesi_admin`.
 *
 * @return bool true kalau cookie-nya sah (dan $_SESSION sudah diisi).
 */
function auth_admin_dari_cookie($koneksi): bool
{
    if (!auth_aktif()) {
        return false;
    }

    $token = (string) ($_COOKIE[auth_nama_cookie()] ?? '');

    if (!preg_match('/^[0-9a-f]{64}$/', $token)) {
        return false;
    }

    $stmt = db_prepare(
        $koneksi,
        "SELECT s.id_admin, s.kedaluwarsa_at, a.username, a.nama
           FROM sesi_admin s
           JOIN admin a ON a.id = s.id_admin
          WHERE s.token_hash = ?
          LIMIT 1"
    );

    if (!$stmt) {
        return false;
    }

    $token_hash = auth_token_hash($token);

    db_stmt_bind_param($stmt, 's', $token_hash);

    db_stmt_execute($stmt);

    $hasil = db_stmt_get_result($stmt);

    $baris = $hasil ? db_fetch_assoc($hasil) : null;

    db_stmt_close($stmt);

    if (!$baris) {
        return false;
    }

    /* Sudah lewat masa berlaku? Hapus, lalu minta login lagi. */
    $kadaluarsa = strtotime((string) $baris['kedaluwarsa_at']);

    if ($kadaluarsa !== false && $kadaluarsa < time()) {
        auth_hapus_penanda($koneksi, $token);

        return false;
    }

    $_SESSION['admin_id']       = (int) $baris['id_admin'];
    $_SESSION['admin_nama']     = (string) ($baris['nama'] ?? '');
    $_SESSION['admin_username'] = (string) ($baris['username'] ?? '');

    return true;
}

/**
 * Menghapus satu penanda login dari tabel (dipakai saat kedaluwarsa
 * atau saat admin keluar).
 */
function auth_hapus_penanda($koneksi, string $token): void
{
    if (!auth_aktif() || $koneksi === null) {
        return;
    }

    $stmt = db_prepare($koneksi, 'DELETE FROM sesi_admin WHERE token_hash = ?');

    if (!$stmt) {
        return;
    }

    $token_hash = auth_token_hash($token);

    db_stmt_bind_param($stmt, 's', $token_hash);

    db_stmt_execute($stmt);

    db_stmt_close($stmt);
}

/**
 * Wajib login. Dipanggil di awal setiap halaman admin.
 *
 * Cara kerjanya:
 *   1. Kalau $_SESSION sudah berisi admin_id -> lanjut.
 *   2. Kalau tidak, coba baca cookie `sesi_admin` (mode online).
 *   3. Kalau dua-duanya tidak ada -> alihkan ke halaman login.
 */
function auth_paksa_login($koneksi = null, string $halaman_login = 'login.php'): void
{
    if (isset($_SESSION['admin_id'])) {
        return;
    }

    if ($koneksi !== null && auth_admin_dari_cookie($koneksi)) {
        return;
    }

    header('Location: ' . $halaman_login);
    exit;
}

/**
 * Keluar: hapus penanda login (cookie + baris di tabel).
 * Pembersihan $_SESSION dilakukan oleh admin/logout.php.
 */
function auth_hapus_sesi($koneksi = null): void
{
    $token = (string) ($_COOKIE[auth_nama_cookie()] ?? '');

    if ($token !== '' && preg_match('/^[0-9a-f]{64}$/', $token) && $koneksi !== null) {
        auth_hapus_penanda($koneksi, $token);
    }

    auth_hapus_cookie();
}

/**
 * Membersihkan penanda login yang sudah kedaluwarsa.
 */
function auth_bersihkan($koneksi): void
{
    if (!auth_aktif()) {
        return;
    }

    /* Kalau gagal (misalnya tabel belum ada), tidak apa-apa —
       yang penting login tetap bisa dipakai. */
    db_query($koneksi, 'DELETE FROM sesi_admin WHERE kedaluwarsa_at < now()');
}
