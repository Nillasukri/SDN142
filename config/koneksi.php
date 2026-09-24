<?php

/* =====================================================================
   KONEKSI DATABASE                            config/koneksi.php
   ---------------------------------------------------------------------
   Ini satu-satunya berkas yang boleh tahu soal database.
   Semua halaman cukup memuat berkas ini, lalu memakai $koneksi.

   ADA DUA MODE:

     lokal     -> MySQL di komputer sendiri (XAMPP), data di folder
                  htdocs, foto di folder uploads/.
     supabase  -> PostgreSQL milik Supabase (untuk website online di
                  Vercel), foto di Supabase Storage.

   Mode 'otomatis' artinya: kalau ditemukan alamat + kunci Supabase
   (dari Environment Variable Vercel ATAU dari config/rahasia.php),
   maka mode supabase dipakai; kalau tidak ada, ya MySQL lokal.
   Jadi komputer Bapak tetap seperti biasa, tidak ada yang berubah.
   ===================================================================== */

/* Data sekolah (config/sekolah.php) dan fungsi bantuan
   (config/bantuan.php) ikut dimuat di sini, supaya semua halaman
   bisa langsung memakainya tanpa menulis require lagi. */
require_once __DIR__ . '/sekolah.php';
require_once __DIR__ . '/bantuan.php';

/* Pemilih lapisan database (config/db.php) */
require_once __DIR__ . '/db.php';

/* --- Pengaturan mode: 'otomatis', 'lokal', atau 'supabase' --- */
$mode = 'otomatis';

db_muat_lapisan($mode);


if (db_lapisan() === 'supabase') {

    /* --------------------------------------------------------------
       MODE ONLINE (Supabase)
       Alamat & kunci dibaca dari Environment Variable di Vercel:
         SUPABASE_URL, SUPABASE_SERVICE_KEY, SUPABASE_BUCKET
       (atau dari config/rahasia.php kalau dijalankan di komputer)
    -------------------------------------------------------------- */
    $koneksi = db_connect();

    if (!$koneksi) {
        die("Koneksi ke Supabase gagal: " . db_connect_error());
    }

    /* Uji cepat: pastikan Supabase menjawab dan fungsi app_query ada.
       Kalau gagal, pesannya dibuat jelas supaya mudah diperbaiki. */
    if (db_query($koneksi, "select 1 as uji") === false) {
        die(
            "<h2>Website belum bisa terhubung ke database Supabase</h2>"
            . "<p><b>Pesan dari Supabase:</b> " . e(db_error($koneksi)) . "</p>"
            . "<p>Yang perlu diperiksa:</p>"
            . "<ol>"
            . "<li>Sudah menjalankan <code>database/schema-supabase.sql</code> di SQL Editor Supabase?</li>"
            . "<li>Environment Variable <code>SUPABASE_URL</code> dan <code>SUPABASE_SERVICE_KEY</code> "
            . "sudah diisi dengan benar (kunci <b>service_role</b>, bukan anon)?</li>"
            . "<li>Alamat project masih benar (tidak salah ketik/kedaluwarsa)?</li>"
            . "</ol>"
        );
    }

} else {

    /* --------------------------------------------------------------
       MODE LOKAL (XAMPP) — ini yang biasa Bapak pakai
       Sesuaikan kalau setelan MySQL di komputer berbeda.
    -------------------------------------------------------------- */
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "db_sekolah";
    $port = 3306;

    $koneksi = db_connect($host, $user, $pass, $db, $port);

    if (!$koneksi) {
        die("Koneksi database gagal: " . db_connect_error());
    }

    db_set_charset($koneksi, "utf8mb4");
}
