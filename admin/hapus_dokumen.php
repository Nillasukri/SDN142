<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once "../config/koneksi.php";

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
        file_exists($file_path) &&
        is_file($file_path)
    ) {

        unlink($file_path);
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
