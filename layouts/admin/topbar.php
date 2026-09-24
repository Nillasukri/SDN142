<?php
/* =====================================================================
   TOPBAR ADMIN                    layouts/admin/topbar.php
   ---------------------------------------------------------------------
   Bagian atas halaman: tombol menu (tampil di layar HP), judul halaman,
   dan profil admin yang sedang masuk.
   File ini juga membuka <main class="main"> dan <section class="content">,
   yang nanti ditutup oleh layouts/admin/kaki.php

   Halaman wajib mengisi $judul_halaman dan $subjudul (lihat kepala.php).
   ===================================================================== */

/* nama admin di halaman ada yang bernama $nama_admin / $admin_nama */
$nama_admin     = $nama_admin     ?? $admin_nama ?? 'Administrator';
$username_admin = $username_admin ?? $admin_user ?? 'admin';

$judul_halaman = $judul_halaman ?? '';
$subjudul      = $subjudul      ?? '';
?>
<!-- =========================================================
     MAIN
========================================================= -->

<main class="main">


    <!-- =====================================================
         TOPBAR
    ====================================================== -->

    <header class="topbar">


        <div class="topbar-left">


            <button
                type="button"
                class="mobile-menu-btn"
                id="mobileMenuBtn"
                aria-label="Buka menu">

                <i class="fa-solid fa-bars"></i>

            </button>


            <div class="topbar-title">

                <h1>
                    <?= e($judul_halaman) ?>
                </h1>
<?php if ($subjudul !== ''): ?>

                <p>
                    <?= e($subjudul) ?>
                </p>
<?php endif; ?>

            </div>


        </div>


        <!-- ADMIN PROFILE -->
        <div class="admin-profile">


            <div class="admin-info">

                <strong>
                    <?= e($nama_admin) ?>
                </strong>

                <span>
                    @<?= e($username_admin) ?>
                </span>

            </div>


            <div class="admin-avatar">

                <?= e(strtoupper(substr($nama_admin, 0, 1))) ?>

            </div>


        </div>


    </header>


    <!-- =====================================================
         CONTENT
    ====================================================== -->

    <section class="content">

