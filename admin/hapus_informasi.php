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
    header("Location: informasi.php");
    exit;
}


/* =========================
   AMBIL DATA INFORMASI
========================= */

$sql = "
    SELECT *
    FROM informasi
    WHERE id = ?
    LIMIT 1
";

$stmt = db_prepare($koneksi, $sql);

if (!$stmt) {
    header("Location: informasi.php?status=hapus_gagal");
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
    header("Location: informasi.php?status=hapus_gagal");
    exit;
}


/* =========================
   DATA FILE
=========================

   Catatan penting soal folder:

   Foto informasi disimpan di  -> uploads/informasi/
   (lihat bagian penyimpanan file di tambah_informasi.php
    dan edit_informasi.php)

   Sisi pembaca di admin/informasi.php sudah diperbaiki
   (Tahap 4) sehingga ikut membaca uploads/informasi/,
   jadi foto informasi kini tampil di halaman admin.

   File fisik karena itu dihapus dari folder
   uploads/informasi/.

========================= */

$nama_foto = trim($data['foto'] ?? '');

$file_path = "../uploads/informasi/" . $nama_foto;


/* =========================
   HAPUS DATA DATABASE
========================= */

$sql_delete = "
    DELETE FROM informasi
    WHERE id = ?
";

$stmt_delete = db_prepare(
    $koneksi,
    $sql_delete
);

if (!$stmt_delete) {
    header("Location: informasi.php?status=hapus_gagal");
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
        $nama_foto !== '' &&
        file_exists($file_path) &&
        is_file($file_path)
    ) {

        unlink($file_path);
    }


    /* =========================
       KEMBALI KE INFORMASI
    ========================= */

    header(
        "Location: informasi.php?status=hapus_sukses"
    );

    exit;

} else {

    db_stmt_close($stmt_delete);

    header(
        "Location: informasi.php?status=hapus_gagal"
    );

    exit;
}
