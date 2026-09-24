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
    header("Location: galeri.php");
    exit;
}


/* =========================
   AMBIL DATA GALERI
========================= */

$sql = "
    SELECT *
    FROM galeri
    WHERE id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    header("Location: galeri.php?status=hapus_gagal");
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =========================
   CEK DATA
========================= */

if (!$data) {
    header("Location: galeri.php?status=hapus_gagal");
    exit;
}


/* =========================
   DATA FILE
========================= */

$nama_foto = trim($data['foto'] ?? '');

$file_path = "../uploads/galeri/" . $nama_foto;


/* =========================
   HAPUS DATA DATABASE
========================= */

$sql_delete = "
    DELETE FROM galeri
    WHERE id = ?
";

$stmt_delete = mysqli_prepare(
    $koneksi,
    $sql_delete
);

if (!$stmt_delete) {
    header("Location: galeri.php?status=hapus_gagal");
    exit;
}


mysqli_stmt_bind_param(
    $stmt_delete,
    "i",
    $id
);


if (mysqli_stmt_execute($stmt_delete)) {

    mysqli_stmt_close($stmt_delete);


    /* =========================
       HAPUS FILE FISIK
    ========================= */

    if (
        $nama_foto !== '' &&
        file_exists($file_path) &&
        is_file($file_path)
    ) {

        unlink($file_path);
    }


    /* =========================
       KEMBALI KE GALERI
    ========================= */

    header(
        "Location: galeri.php?status=hapus_sukses"
    );

    exit;

} else {

    mysqli_stmt_close($stmt_delete);

    header(
        "Location: galeri.php?status=hapus_gagal"
    );

    exit;
}
