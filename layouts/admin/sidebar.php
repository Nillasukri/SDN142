<?php
/* =====================================================================
   SIDEBAR ADMIN                   layouts/admin/sidebar.php
   ---------------------------------------------------------------------
   >>> MENU ADMIN ADA DI FILE INI <<<

   Mau menambah, mengubah, atau menghapus menu? Cukup ubah daftar
   $menu_admin di bawah ini, dan SEMUA halaman admin ikut berubah.
   Tidak perlu lagi mengedit 15 file satu per satu.

   Isi daftar menu: 'kunci' => [tulisan menu, file tujuan, ikon Font Awesome]
   Nama ikon bisa dilihat di https://fontawesome.com/icons
   ===================================================================== */

$menu_admin = [

    'dashboard' => ['Dashboard',      'dashboard.php', 'fa-chart-line'],
    'profil'    => ['Profil Sekolah', 'profil.php',    'fa-school'],
    'guru'      => ['Guru &amp; Staf', 'guru.php',      'fa-chalkboard-user'],
    'informasi' => ['Informasi',      'informasi.php', 'fa-bullhorn'],
    'dokumen'   => ['Dokumen',        'dokumen.php',   'fa-folder-open'],
    'galeri'    => ['Galeri',         'galeri.php',    'fa-images'],
    'keamanan'  => ['Keamanan Akun',  'keamanan.php',  'fa-lock'],

];

/* menu yang sedang dibuka (diisi oleh halaman yang memanggil file ini) */
$menu_aktif = $menu_aktif ?? '';

/* logo sekolah: pakai yang sudah disiapkan halaman; kalau halaman belum
   menyiapkannya, file ini yang mengambil dari database */
if (!isset($logo_sekolah)) {

    $logo_sekolah = '';

    if (isset($koneksi)) {

        $query_logo = db_query(
            $koneksi,
            "SELECT logo FROM profil ORDER BY id ASC LIMIT 1"
        );

        if ($query_logo) {
            $data_logo = db_fetch_assoc($query_logo);
            $logo_sekolah = $data_logo['logo'] ?? '';
        }
    }
}
?>
<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside
    class="sidebar"
    id="sidebar">

    <!-- BRAND -->
    <div class="sidebar-header">

        <div class="brand">

            <div class="brand-logo">

                <?php if (
                    !empty($logo_sekolah) &&
                    file_exists("../uploads/" . basename($logo_sekolah))
                ): ?>

                    <img
                        src="../uploads/<?= e(basename($logo_sekolah)) ?>"
                        alt="Logo Sekolah">

                <?php else: ?>

                    <i class="fa-solid fa-school"></i>

                <?php endif; ?>

            </div>

            <div class="brand-text">

                <h2>
                    Admin Sekolah
                </h2>

                <span>
                    <?= NAMA_SEKOLAH ?>
                </span>

            </div>

        </div>

    </div>


    <div class="menu-title">
        Menu Utama
    </div>


    <nav class="menu">

        <?php foreach ($menu_admin as $kunci_menu => $item_menu): ?>

        <a
            href="<?= $item_menu[1] ?>"<?= $kunci_menu === $menu_aktif ? ' class="active"' : '' ?>>

            <span class="menu-icon">
                <i class="fa-solid <?= $item_menu[2] ?>"></i>
            </span>

            <span>
                <?= $item_menu[0] ?>
            </span>

        </a>

        <?php endforeach; ?>

    </nav>


    <!-- KELUAR -->
    <div class="sidebar-bottom">

        <a
            href="logout.php"
            class="logout">

            <span class="menu-icon">
                <i class="fa-solid fa-right-from-bracket"></i>
            </span>

            <span>
                Keluar
            </span>

        </a>

    </div>


</aside>

