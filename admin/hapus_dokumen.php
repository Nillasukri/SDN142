<?php

session_start();

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


/* =========================
   CEK ID
========================= */

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: dokumen.php");
    exit;
}


/* =========================
   AMBIL DATA DOKUMEN
========================= */

$sql = "
    SELECT *
    FROM dokumen
    WHERE id = ?
    LIMIT 1
";

$stmt = db_prepare($koneksi, $sql);

if (!$stmt) {
    header("Location: dokumen.php");
    exit;
}

db_stmt_bind_param(
    $stmt,
    "i",
    $id
);

db_stmt_execute($stmt);

$result = db_stmt_get_result($stmt);

$data = db_fetch_assoc($result);

db_stmt_close($stmt);


/* =========================
   CEK DATA
========================= */

if (!$data) {
    header("Location: dokumen.php");
    exit;
}


/* =========================
   DATA FILE
========================= */

$nama_file = $data['nama_file'] ?? '';

$file_path = "../uploads/dokumen/" . $nama_file;


/* =========================
   HAPUS DATA DATABASE
========================= */

$sql_delete = "
    DELETE FROM dokumen
    WHERE id = ?
";

$stmt_delete = db_prepare(
    $koneksi,
    $sql_delete
);

if (!$stmt_delete) {
    header("Location: dokumen.php");
    exit;
}


db_stmt_bind_param(
    $stmt_delete,
    "i",
    $id
);


if (db_stmt_execute($stmt_delete)) {

    db_stmt_close($stmt_delete);


    /* =========================
       HAPUS FILE FISIK
    ========================= */

    if (
        $nama_file !== '' &&
        unggah_ada($file_path) &&
        unggah_ada($file_path)
    ) {

        unggah_hapus($file_path);
    }


    /* =========================
       KEMBALI KE DOKUMEN
    ========================= */

    header(
        "Location: dokumen.php?status=hapus_sukses"
    );

    exit;

} else {

    db_stmt_close($stmt_delete);

    header(
        "Location: dokumen.php?status=hapus_gagal"
    );

    exit;
}
