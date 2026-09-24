<?php
/* =====================================================================
   NAVBAR HALAMAN PENGUNJUNG        layouts/publik/navbar.php
   ---------------------------------------------------------------------
   MENU PENGUNJUNG ADA DI FILE INI. Urutan menu, tulisan, dan ikonnya
   cukup diubah di sini, dan semua halaman pengunjung ikut berubah.

   Menu yang sedang terbuka ditentukan oleh $halamanAktif, yang diisi
   otomatis oleh halaman (lihat cara pakainya di halaman masing-masing).
   ===================================================================== */

$halamanAktif = $halamanAktif ?? '';
?>
<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">

    <div class="container navbar-content">


        <!-- =================================================
             LOGO + NAMA SEKOLAH
        ================================================= -->

        <a
            href="index.php"
            class="logo-area"
        >


            <?php if (
                !empty(
                    $dataProfil["logo"]
                )
            ): ?>


                <img
                    src="uploads/<?= e(
                        $dataProfil["logo"]
                    ); ?>"
                    alt="Logo Sekolah"
                    class="logo"
                >


            <?php else: ?>


                <div class="logo-default">

                    <i class="fa-solid fa-school"></i>

                </div>


            <?php endif; ?>


            <div class="school-name">


                <h1>

                    <?= e(
                        $dataProfil[
                            "nama_sekolah"
                        ]
                    ); ?>

                </h1>


                <p>

                    Desa <?= DESA_SEKOLAH ?> • <?= KABUPATEN_SEKOLAH ?>

                </p>


            </div>


        </a>



        <!-- =================================================
             BUTTON MENU HP
        ================================================= -->

        <button
            type="button"
            class="menu-button"
            id="menuButton"
        >

            <i class="fa-solid fa-bars"></i>

        </button>



        <!-- =================================================
             MENU NAVIGASI
        ================================================= -->

        <ul
            class="menu"
            id="menu"
        >


            <!-- BERANDA -->

            <li>

                <a
                    href="index.php"
                    class="<?= $halamanAktif == 'index.php'
                        ? 'active'
                        : ''; ?>"
                >

                    Beranda

                </a>

            </li>



            <!-- PROFIL -->

            <li>

                <a
                    href="profil.php"
                    class="<?= $halamanAktif == 'profil.php'
                        ? 'active'
                        : ''; ?>"
                >

                    Profil

                </a>

            </li>



            <!-- INFORMASI -->

            <li>

                <a
                    href="informasi.php"
                    class="<?= $halamanAktif == 'informasi.php'
                        ? 'active'
                        : ''; ?>"
                >

                    Informasi

                </a>

            </li>



            <!-- PENDAFTARAN -->

            <li>

                <a
                    href="pendaftaran.php"
                    class="<?= $halamanAktif == 'pendaftaran.php'
                        ? 'active'
                        : ''; ?>"
                >

                    Pendaftaran

                </a>

            </li>



            <!-- DOKUMEN -->

            <li>

                <a
                    href="dokumen.php"
                    class="<?= $halamanAktif == 'dokumen.php'
                        ? 'active'
                        : ''; ?>"
                >

                    Dokumen

                </a>

            </li>



            <!-- GALERI -->

            <li>

                <a
                    href="galeri.php"
                    class="<?= $halamanAktif == 'galeri.php'
                        ? 'active'
                        : ''; ?>"
                >

                    Galeri

                </a>

            </li>



            <!-- LOGIN ADMIN -->

            <li>

                <a
                    href="admin/login.php"
                    class="btn-login-admin"
                >

                    <i class="fa-solid fa-lock"></i>

                    Login Admin

                </a>

            </li>


        </ul>


    </div>

</nav>
