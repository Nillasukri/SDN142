<?php

/* =========================================================
   LOGOUT (KELUAR DARI DASHBOARD)          admin/logout.php
   ---------------------------------------------------------
   Membersihkan dua hal:

     1. Session PHP (cara lama) — seperti sebelumnya.
     2. Penanda login di cookie `sesi_admin` + barisnya di tabel
        `sesi_admin` (Tahap 7). Tanpa langkah ini, di mode online
        admin bisa "kembali masuk" sendiri walau sudah keluar,
        karena cookie-nya masih dianggap sah oleh sistem.
========================================================= */

session_start();


/* =========================================================
   1. HAPUS SESSION PHP
========================================================= */

$_SESSION = [];


$params = session_get_cookie_params();

setcookie(
    session_name(),
    '',
    time() - 42000,
    $params["path"],
    $params["domain"],
    $params["secure"],
    $params["httponly"]
);


session_destroy();


/* =========================================================
   2. HAPUS PENANDA LOGIN (COOKIE + TABEL sesi_admin)

   Di mode lokal langkah ini tidak mengubah apa pun.
   Kalau koneksi database bermasalah, cookie tetap dihapus
   supaya admin benar-benar keluar.
========================================================= */

require_once "../config/koneksi.php";
require_once "../config/auth_admin.php";

auth_hapus_sesi($koneksi);


/* =========================================================
   3. KEMBALI KE HALAMAN LOGIN
========================================================= */

header("Location: login.php");
exit;

?>
