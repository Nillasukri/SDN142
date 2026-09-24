<?php
/* =====================================================================
   KEPALA HALAMAN PENGUNJUNG       layouts/publik/kepala.php
   ---------------------------------------------------------------------
   Dipakai semua halaman pengunjung (index, profil, informasi, galeri,
   dokumen, pendaftaran) supaya bagian pembuka tidak ditulis berulang.

   Halaman wajib mengisi:
       $judul          judul yang muncul di tab browser
       $css            file CSS halaman ini
   Opsional:
       $deskripsi      keterangan halaman untuk mesin pencari

   $halamanAktif dan $dataProfil disiapkan oleh halaman masing-masing.
   ===================================================================== */

$judul     = $judul     ?? NAMA_SEKOLAH;
$css       = $css       ?? '';
$deskripsi = $deskripsi ?? '';
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

    <!-- GOOGLE FONT -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- CSS BERSAMA (sidebar, topbar, menu, footer) -->
    <link rel="stylesheet" href="assets/css/publik/layout.css">

<?php if ($css !== ''): ?>

    <!-- CSS KHUSUS HALAMAN INI -->
    <link rel="stylesheet" href="<?= $css ?>">
<?php endif; ?>

</head>


<body>


<!-- =====================================================
     TOPBAR
===================================================== -->

<div class="topbar">

    <div class="container topbar-content">

        <div>

            <i class="fa-solid fa-location-dot"></i>

            <?= e($dataProfil["alamat"]); ?>

        </div>


        <div>

            Website Resmi Sekolah

        </div>

    </div>

</div>
