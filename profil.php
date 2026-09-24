<?php

require_once "config/koneksi.php";


/* =====================================================
   HALAMAN AKTIF
===================================================== */

$halamanAktif = basename($_SERVER['PHP_SELF']);


/* =====================================================
   DATA DEFAULT
===================================================== */

$dataProfil = [

    "nama_sekolah" =>
        NAMA_SEKOLAH_KAPITAL,

    "alamat" =>
        ALAMAT_SEKOLAH,

    "desa" =>
        DESA_SEKOLAH,

    "kecamatan" =>
        KECAMATAN_SEKOLAH,

    "kabupaten" =>
        KABUPATEN_SEKOLAH,

    "provinsi" =>
        PROVINSI_SEKOLAH,

    "sejarah" =>
        NAMA_SEKOLAH_PANJANG . " merupakan salah satu satuan pendidikan dasar yang berada di wilayah Kabupaten Takalar, Sulawesi Selatan. Sekolah hadir sebagai bagian dari upaya memberikan layanan pendidikan dasar yang berkualitas bagi masyarakat sekitar.",

    "visi" =>
        "Terwujudnya peserta didik yang beriman, berkarakter, berprestasi, mandiri, dan peduli terhadap lingkungan.",

    "misi" =>
        "Menyelenggarakan pembelajaran yang aktif, kreatif, efektif, dan menyenangkan; membangun karakter peserta didik melalui keteladanan dan pembiasaan positif; meningkatkan prestasi akademik dan nonakademik; serta menciptakan lingkungan sekolah yang aman, nyaman, bersih, dan sehat.",

    "tujuan" =>
        "Membentuk peserta didik yang memiliki karakter baik, memiliki kemampuan dasar yang kuat, mampu mengembangkan potensi diri, memiliki semangat belajar, serta mampu berinteraksi secara positif dengan lingkungan.",

    "nama_kepala_sekolah" =>
        "Kepala Sekolah",

    "foto_kepala_sekolah" =>
        "",

    "logo" =>
        ""

];


/* =====================================================
   AMBIL DATA PROFIL
===================================================== */

$queryProfil = db_query(
    $koneksi,
    "SELECT *
     FROM profil
     LIMIT 1"
);


if (!$queryProfil) {

    die(
        "Terjadi kesalahan saat mengambil data profil: " .
        db_error($koneksi)
    );

}


$profil = db_fetch_assoc($queryProfil);


if ($profil) {

    $dataProfil = array_merge(
        $dataProfil,
        $profil
    );

}


/* =====================================================
   PISAHKAN DATA MISI
===================================================== */

$misiList = [];

if (!empty($dataProfil["misi"])) {

    $misiList = preg_split(
        "/\r\n|\r|\n/",
        $dataProfil["misi"]
    );

}


/* =====================================================
   ALAMAT
===================================================== */

$alamatLengkap = $dataProfil["alamat"];

if (empty($alamatLengkap)) {

    $alamatLengkap =
        "Desa " .
        $dataProfil["desa"] .
        ", Kec. " .
        $dataProfil["kecamatan"] .
        ", Kab. " .
        $dataProfil["kabupaten"] .
        ", " .
        $dataProfil["provinsi"];

}

?>

<?php

/* =========================================================
   PENGATURAN TAMPILAN HALAMAN
   Bagian atas, menu, dan bagian bawah halaman diambil dari
   folder layouts/ supaya tidak ditulis berulang.
========================================================== */

$judul         = "Profil | " . ($dataProfil["nama_sekolah"] ?? "");

$css           = "assets/css/publik/profil.css";

$halamanAktif  = basename(__FILE__);

$deskripsi     = "Profil " . ($dataProfil["nama_sekolah"] ?? "");

$font_pusat    = true;      /* halaman ini memakai font Poppins */

require_once "layouts/publik/kepala.php";
require_once "layouts/publik/navbar.php";
?>


<!-- =====================================================
     HERO PROFIL
===================================================== -->

<section class="profile-hero">


    <div class="hero-decoration decoration-one"></div>

    <div class="hero-decoration decoration-two"></div>


    <div class="profile-hero-container">


        <div class="hero-icon">

            <i class="fa-solid fa-school"></i>

        </div>


        <h1>

            Profil Sekolah

        </h1>


        <p>

            Mengenal lebih dekat

            <?= e(
                $dataProfil["nama_sekolah"]
            ); ?>

            melalui sejarah, identitas,
            visi, misi, dan tujuan sekolah.

        </p>


    </div>


</section>


<!-- =====================================================
     IDENTITAS SEKOLAH
===================================================== -->

<section class="identity-section">

    <div class="container">


        <div class="section-title">

            <span>
                Tentang Sekolah
            </span>


            <h2>
                Identitas Sekolah
            </h2>


            <p>
                Informasi umum mengenai sekolah kami.
            </p>

        </div>


        <div class="identity-card">


            <!-- LOGO -->

            <div class="school-logo">


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
                        onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fa-solid fa-school\'></i>';"
                    >


                <?php else: ?>


                    <i class="fa-solid fa-school"></i>


                <?php endif; ?>


            </div>


            <!-- CONTENT -->

            <div class="identity-content">


                <h3>

                    <?= e(
                        $dataProfil["nama_sekolah"]
                    ); ?>

                </h3>


                <div class="identity-address">

                    <span class="location-icon">

                        <i class="fa-solid fa-location-dot"></i>

                    </span>


                    <span>

                        <?= e(
                            $alamatLengkap
                        ); ?>

                    </span>

                </div>


                <div class="identity-grid">


                    <div class="identity-item">

                        <span>
                            Desa
                        </span>

                        <strong>

                            <?= e(
                                $dataProfil["desa"]
                            ); ?>

                        </strong>

                    </div>


                    <div class="identity-item">

                        <span>
                            Kecamatan
                        </span>

                        <strong>

                            <?= e(
                                $dataProfil["kecamatan"]
                            ); ?>

                        </strong>

                    </div>


                    <div class="identity-item">

                        <span>
                            Kabupaten
                        </span>

                        <strong>

                            <?= e(
                                $dataProfil["kabupaten"]
                            ); ?>

                        </strong>

                    </div>


                    <div class="identity-item">

                        <span>
                            Provinsi
                        </span>

                        <strong>

                            <?= e(
                                $dataProfil["provinsi"]
                            ); ?>

                        </strong>

                    </div>


                    <div class="identity-item">

                        <span>
                            Jenjang
                        </span>

                        <strong>
                            Sekolah Dasar
                        </strong>

                    </div>


                    <div class="identity-item">

                        <span>
                            Status
                        </span>

                        <strong>
                            Negeri
                        </strong>

                    </div>


                </div>


            </div>


        </div>


    </div>

</section>


<!-- =====================================================
     KEPALA SEKOLAH
===================================================== -->

<section class="principal-section">

    <div class="container">


        <div class="section-title">

            <span>
                Pimpinan Sekolah
            </span>


            <h2>
                Kepala Sekolah
            </h2>


            <p>
                Pimpinan yang mendukung kemajuan
                dan perkembangan sekolah.
            </p>

        </div>


        <div class="principal-card">


            <div class="principal-photo">


                <?php if (
                    !empty(
                        $dataProfil[
                            "foto_kepala_sekolah"
                        ]
                    )
                ): ?>


                    <img
                        src="uploads/<?= e(
                            $dataProfil[
                                "foto_kepala_sekolah"
                            ]
                        ); ?>"
                        alt="<?= e(
                            $dataProfil[
                                "nama_kepala_sekolah"
                            ]
                        ); ?>"
                    >


                <?php else: ?>


                    <i class="fa-solid fa-user-tie"></i>


                <?php endif; ?>


            </div>


            <div class="principal-content">


                <span class="principal-label">

                    Kepala Sekolah

                </span>


                <h3>

                    <?= e(
                        $dataProfil[
                            "nama_kepala_sekolah"
                        ]
                    ); ?>

                </h3>


                <p>

                    Kepala sekolah bersama seluruh
                    tenaga pendidik dan kependidikan
                    berkomitmen memberikan layanan
                    pendidikan yang terbaik bagi
                    peserta didik.

                </p>


            </div>


        </div>


    </div>

</section>


<!-- =====================================================
     SEJARAH
===================================================== -->

<section class="history-section">

    <div class="container">


        <div class="section-title">

            <span>
                Perjalanan Sekolah
            </span>


            <h2>
                Sejarah Sekolah
            </h2>


            <p>
                Mengenal perjalanan dan perkembangan
                sekolah dari waktu ke waktu.
            </p>

        </div>


        <div class="content-card">

            <p>

                <?= nl2br(
                    e(
                        $dataProfil["sejarah"]
                    )
                ); ?>

            </p>

        </div>


    </div>

</section>


<!-- =====================================================
     VISI MISI
===================================================== -->

<section class="vision-mission-section">

    <div class="container">


        <div class="section-title">

            <span>
                Arah Pendidikan
            </span>


            <h2>
                Visi & Misi
            </h2>


            <p>
                Landasan dan arah dalam menyelenggarakan
                pendidikan di sekolah.
            </p>

        </div>


        <div class="vision-mission-grid">


            <!-- VISI -->

            <div class="vm-card">


                <div class="vm-icon">

                    <i class="fa-solid fa-eye"></i>

                </div>


                <h3>
                    Visi
                </h3>


                <p>

                    <?= nl2br(
                        e(
                            $dataProfil["visi"]
                        )
                    ); ?>

                </p>


            </div>


            <!-- MISI -->

            <div class="vm-card mission">


                <div class="vm-icon">

                    <i class="fa-solid fa-rocket"></i>

                </div>


                <h3>
                    Misi
                </h3>


                <?php if (
                    count($misiList) > 1
                ): ?>


                    <ul class="mission-list">


                        <?php foreach (
                            $misiList
                            as $misi
                        ): ?>


                            <?php if (
                                trim($misi) != ""
                            ): ?>


                                <li>

                                    <span
                                        class="mission-check"
                                    >
                                        <i class="fa-solid fa-check"></i>
                                    </span>


                                    <span>

                                        <?= e(
                                            trim($misi)
                                        ); ?>

                                    </span>

                                </li>


                            <?php endif; ?>


                        <?php endforeach; ?>


                    </ul>


                <?php else: ?>


                    <p>

                        <?= nl2br(
                            e(
                                $dataProfil["misi"]
                            )
                        ); ?>

                    </p>


                <?php endif; ?>


            </div>


        </div>


    </div>

</section>


<!-- =====================================================
     TUJUAN
===================================================== -->

<section class="goals-section">

    <div class="container">


        <div class="section-title">

            <span>
                Sasaran Pendidikan
            </span>


            <h2>
                Tujuan Sekolah
            </h2>


            <p>
                Tujuan yang ingin dicapai dalam
                penyelenggaraan pendidikan.
            </p>

        </div>


        <div class="goals-card">


            <div class="goals-header">


                <div class="goals-icon">

                    <i class="fa-solid fa-bullseye"></i>

                </div>


                <h2>
                    Tujuan Pendidikan
                </h2>


            </div>


            <p>

                <?= nl2br(
                    e(
                        $dataProfil["tujuan"]
                    )
                ); ?>

            </p>


        </div>


    </div>

</section>


<!-- =====================================================
     ALAMAT
===================================================== -->

<section class="address-section">

    <div class="container">


        <div class="section-title">

            <span>
                Lokasi
            </span>


            <h2>
                Alamat Sekolah
            </h2>

        </div>


        <div class="address-card">


            <div class="address-icon">

                <i class="fa-solid fa-location-dot"></i>

            </div>


            <div>


                <h3>

                    <?= e(
                        $dataProfil["nama_sekolah"]
                    ); ?>

                </h3>


                <p>

                    <?= e(
                        $alamatLengkap
                    ); ?>

                </p>


            </div>


        </div>


    </div>

</section>


<!-- =====================================================
     FOOTER
===================================================== -->

<?php require_once "layouts/publik/kaki.php"; ?>
