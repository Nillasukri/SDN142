<?php
/* =====================================================================
   KEPALA HALAMAN ADMIN            layouts/admin/kepala.php
   ---------------------------------------------------------------------
   Dipakai SEMUA halaman di folder admin/ supaya bagian pembuka halaman
   (DOCTYPE, <head>, <body>, lapisan gelap) tidak ditulis berulang kali.

   Halaman yang memakai file ini WAJIB mengisi variabel ini dulu:
       $judul          judul yang muncul di tab browser
       $css            file CSS halaman ini
       $menu_aktif     menu yang disorot di sidebar (lihat sidebar.php)
       $judul_halaman  judul besar di bagian atas halaman
       $subjudul       tulisan kecil di bawah judul (boleh kosong)

   Boleh juga mengisi (kalau perlu saja):
       $deskripsi      keterangan halaman untuk mesin pencari
       $font_pusat     true  = halaman ini memakai font Poppins
   ===================================================================== */

$judul      = $judul      ?? 'Admin | ' . NAMA_SEKOLAH;
$css        = $css        ?? '';
$deskripsi  = $deskripsi  ?? '';
$font_pusat = $font_pusat ?? false;
?>
<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>
    </title>
<?php if ($deskripsi !== ''): ?>

    <meta
        name="description"
        content="<?= htmlspecialchars($deskripsi, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?php if ($font_pusat): ?>

    <!-- GOOGLE FONT -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
<?php endif; ?>
    <!-- CSS BERSAMA (sidebar, topbar, menu, footer) -->
    <link rel="stylesheet" href="../assets/css/admin/layout.css">

<?php if ($css !== ''): ?>

    <!-- CSS KHUSUS HALAMAN INI -->
    <link rel="stylesheet" href="<?= $css ?>">
<?php endif; ?>

</head>


<body>


<!-- =========================================================
     OVERLAY
========================================================= -->

<div
    class="overlay"
    id="overlay">
</div>

