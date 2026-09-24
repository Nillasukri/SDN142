<?php

// Mulai session
session_start();

// Cek apakah admin sudah login
/* -------------------------------------------------------------
   WAJIB LOGIN
   Di mode lokal (XAMPP) cukup session PHP seperti biasa.
   Di mode online (Vercel) penanda login juga dibaca dari cookie
   `sesi_admin`, supaya admin tidak ter-logout sendiri.
   Penjelasan lengkap ada di config/auth_admin.php.
------------------------------------------------------------- */
require_once "../config/koneksi.php";
require_once "../config/auth_admin.php";

auth_paksa_login($koneksi, "login.php");

// Koneksi database

// Cek apakah ID tersedia
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: guru.php?status=id_tidak_valid");
    exit;
}

$id = (int) $_GET['id'];

// ======================================================
// Ambil data guru terlebih dahulu
// ======================================================

$query = "SELECT foto FROM guru WHERE id = ?";
$stmt = db_prepare($koneksi, $query);

if (!$stmt) {
    header("Location: guru.php?status=gagal");
    exit;
}

db_stmt_bind_param($stmt, "i", $id);
db_stmt_execute($stmt);

$result = db_stmt_get_result($stmt);
$data = db_fetch_assoc($result);

db_stmt_close($stmt);

// Jika data guru tidak ditemukan
if (!$data) {
    header("Location: guru.php?status=data_tidak_ditemukan");
    exit;
}

// ======================================================
// Hapus data guru dari database
// ======================================================

$query_hapus = "DELETE FROM guru WHERE id = ?";
$stmt_hapus = db_prepare($koneksi, $query_hapus);

if (!$stmt_hapus) {
    header("Location: guru.php?status=gagal");
    exit;
}

db_stmt_bind_param($stmt_hapus, "i", $id);

if (db_stmt_execute($stmt_hapus)) {

    // ==================================================
    // Hapus foto guru jika ada
    // ==================================================

    if (!empty($data['foto'])) {

        $file_foto = "../uploads/guru/" . $data['foto'];

        if (unggah_ada($file_foto)) {
            unggah_hapus($file_foto);
        }
    }

    db_stmt_close($stmt_hapus);

    // Kembali ke halaman guru
    header("Location: guru.php?status=hapus_sukses");
    exit;

} else {

    db_stmt_close($stmt_hapus);

    // Jika gagal menghapus
    header("Location: guru.php?status=gagal");
    exit;
}

?>