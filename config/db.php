<?php

/* =====================================================================
   PEMILIH LAPISAN DATABASE                      config/db.php
   ---------------------------------------------------------------------
   Website ini bisa dijalankan dalam DUA keadaan:

     1. LOKAL (XAMPP)      -> memakai MySQL, seperti biasa
     2. SUPABASE (Vercel)  -> memakai PostgreSQL Supabase

   Supaya kode halaman TIDAK perlu ditulis dua kali, semua halaman
   memanggil fungsi berawalan `db_` (misalnya db_query, db_prepare,
   db_fetch_assoc). File ini yang memutuskan fungsi `db_` itu
   diambilkan dari file yang mana:

     - config/db_mysql.php     -> db_* diteruskan ke mysqli_* (XAMPP)
     - config/db_supabase.php  -> db_* diterjemahkan ke Supabase

   Jadi halaman cukup menulis sekali: db_query($koneksi, "SELECT ..."),
   dan itu jalan di dua tempat.
   ===================================================================== */


/* ---------------------------------------------------------------------
   Sudah pernah dimuat? Jangan dimuat dua kali.
--------------------------------------------------------------------- */

if (defined('DB_LAPISAN')) {
    return;
}


/* ---------------------------------------------------------------------
   1. Membaca berkas rahasia (kalau ada)

   config/rahasia.php isinya: url, service_key, anon_key, bucket.
   Berkas ini TIDAK diunggah ke GitHub (lihat .gitignore), jadi
   di komputer Bapak berkas itu bisa ada, di GitHub tidak.
--------------------------------------------------------------------- */

function db_rahasia(): array
{
    static $rahasia = null;

    if ($rahasia !== null) {
        return $rahasia;
    }

    $rahasia = [];
    $berkas  = __DIR__ . '/rahasia.php';

    if (is_file($berkas)) {
        $isi = require $berkas;
        if (is_array($isi)) {
            $rahasia = $isi;
        }
    }

    return $rahasia;
}


/* ---------------------------------------------------------------------
   2. Mengambil satu setelan

   Urutan pencarian:
     a. Environment Variable (dipakai di Vercel)
     b. config/rahasia.php (kalau menjalankan Supabase di komputer)
     c. nilai bawaan (kalau ada)
--------------------------------------------------------------------- */

function db_ambil_setelan(string $nama, string $bawaan = ''): string
{
    static $peta = [
        'SUPABASE_URL'         => 'url',
        'SUPABASE_SERVICE_KEY' => 'service_key',
        'SUPABASE_ANON_KEY'    => 'anon_key',
        'SUPABASE_BUCKET'      => 'bucket',
    ];

    $nilai = getenv($nama);

    if ($nilai === false || $nilai === '') {
        $nilai = $_SERVER[$nama] ?? ($_ENV[$nama] ?? '');
    }

    if (($nilai === false || $nilai === '') && isset($peta[$nama])) {
        $rahasia = db_rahasia();
        $nilai   = $rahasia[$peta[$nama]] ?? '';
    }

    if (!is_string($nilai)) {
        $nilai = '';
    }

    $nilai = trim($nilai);

    return $nilai !== '' ? $nilai : $bawaan;
}


/* ---------------------------------------------------------------------
   3. Menentukan mode yang sebenarnya dipakai

   Kalau alamat + kunci Supabase ada -> mode supabase.
   Kalau tidak                      -> mode lokal (MySQL).
--------------------------------------------------------------------- */

function db_mode_otomatis(): string
{
    $url   = db_ambil_setelan('SUPABASE_URL');
    $kunci = db_ambil_setelan('SUPABASE_SERVICE_KEY');

    if ($kunci === '') {
        $kunci = db_ambil_setelan('SUPABASE_ANON_KEY');
    }

    return ($url !== '' && $kunci !== '') ? 'supabase' : 'lokal';
}


/* ---------------------------------------------------------------------
   4. Memuat lapisan yang dipilih
--------------------------------------------------------------------- */

function db_muat_lapisan(string $mode = 'otomatis'): string
{
    if (defined('DB_LAPISAN')) {
        return DB_LAPISAN;
    }

    $mode = strtolower(trim($mode));

    if ($mode !== 'lokal' && $mode !== 'supabase') {
        $mode = db_mode_otomatis();          // 'otomatis' atau salah tulis
    }

    if ($mode === 'supabase') {
        require_once __DIR__ . '/db_supabase.php';
    } else {
        require_once __DIR__ . '/db_mysql.php';
    }

    define('DB_LAPISAN', $mode);

    return $mode;
}


/* ---------------------------------------------------------------------
   5. Menanyakan lapisan yang sedang dipakai
--------------------------------------------------------------------- */

function db_lapisan(): string
{
    return defined('DB_LAPISAN') ? DB_LAPISAN : 'lokal';
}
