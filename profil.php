<?php

/* =====================================================
   PROFIL SEKOLAH - HALAMAN PENGUNJUNG
===================================================== */

session_start();

require_once "config/koneksi.php";


/* =====================================================
   FUNGSI ESCAPE
===================================================== */

if (!function_exists('e')) {

    function e($text)
    {
        return htmlspecialchars(
            $text ?? '',
            ENT_QUOTES,
            'UTF-8'
        );
    }

}


/* =====================================================
   HALAMAN AKTIF
===================================================== */

$halamanAktif = 'profil.php';


/* =====================================================
   DATA PROFIL SEKOLAH
===================================================== */

$dataProfil = [

    "nama_sekolah" =>
        "UPT SD NEGERI 142 INPRES LASSANG II",

    "alamat" =>
        "Desa Kampung Beru, Kec. Polombangkeng Timur, Kab. Takalar",

    "desa" =>
        "Kampung Beru",

    "kecamatan" =>
        "Polombangkeng Timur",

    "kabupaten" =>
        "Takalar",

    "provinsi" =>
        "Sulawesi Selatan",

    "sejarah" => "",

    "visi" => "",

    "misi" => "",

    "tujuan" => "",

    "nama_kepala_sekolah" =>
        "",

    "nip_kepala_sekolah" =>
        "",

    "foto_kepala_sekolah" =>
        "",

    "logo" =>
        ""

];


/* =====================================================
   AMBIL DATA DARI DATABASE
===================================================== */

$queryProfil = mysqli_query(
    $koneksi,
    "SELECT * FROM profil LIMIT 1"
);


if (
    $queryProfil &&
    mysqli_num_rows($queryProfil) > 0
) {

    $profilDatabase =
        mysqli_fetch_assoc($queryProfil);


    foreach (
        $dataProfil as $key => $nilaiDefault
    ) {

        if (
            array_key_exists(
                $key,
                $profilDatabase
            )
        ) {

            $dataProfil[$key] =
                $profilDatabase[$key];

        }

    }

}


/* =====================================================
   HEADER / NAVBAR
===================================================== */

require_once "layouts/publik/kepala.php";

require_once "layouts/publik/navbar.php";

?>

<!-- =====================================================
     HALAMAN PROFIL
===================================================== -->

<main class="profil-page">


    <!-- =================================================
         JUDUL HALAMAN
    ================================================== -->

    <section class="profil-hero">

        <div class="container">

            <div class="profil-hero-content">

                <span class="profil-label">

                    <i class="fa-solid fa-school"></i>

                    Profil Sekolah

                </span>


                <h1>

                    <?= e(
                        $dataProfil["nama_sekolah"]
                    ); ?>

                </h1>


                <p>

                    Mengenal lebih dekat
                    identitas, sejarah,
                    visi, misi, dan tujuan sekolah.

                </p>

            </div>

        </div>

    </section>



    <!-- =================================================
         IDENTITAS SEKOLAH
    ================================================== -->

    <section class="profil-section">

        <div class="container">


            <div class="section-title">

                <span>

                    Identitas

                </span>


                <h2>

                    Identitas Sekolah

                </h2>


                <p>

                    Informasi dasar mengenai
                    sekolah.

                </p>

            </div>



            <div class="profil-identitas-grid">


                <!-- NAMA SEKOLAH -->

                <div class="profil-card">

                    <div class="profil-icon">

                        <i
                            class="fa-solid fa-school"
                        ></i>

                    </div>


                    <div>

                        <span>
                            Nama Sekolah
                        </span>

                        <h3>

                            <?= e(
                                $dataProfil[
                                    "nama_sekolah"
                                ]
                            ); ?>

                        </h3>

                    </div>

                </div>



                <!-- ALAMAT -->

                <div class="profil-card">

                    <div class="profil-icon">

                        <i
                            class="fa-solid fa-location-dot"
                        ></i>

                    </div>


                    <div>

                        <span>
                            Alamat
                        </span>

                        <h3>

                            <?= e(
                                $dataProfil[
                                    "alamat"
                                ]
                            ); ?>

                        </h3>

                    </div>

                </div>



                <!-- DESA -->

                <div class="profil-card">

                    <div class="profil-icon">

                        <i
                            class="fa-solid fa-map-location-dot"
                        ></i>

                    </div>


                    <div>

                        <span>
                            Desa / Kelurahan
                        </span>

                        <h3>

                            <?= e(
                                $dataProfil[
                                    "desa"
                                ]
                            ); ?>

                        </h3>

                    </div>

                </div>



                <!-- KECAMATAN -->

                <div class="profil-card">

                    <div class="profil-icon">

                        <i
                            class="fa-solid fa-map"
                        ></i>

                    </div>


                    <div>

                        <span>
                            Kecamatan
                        </span>

                        <h3>

                            <?= e(
                                $dataProfil[
                                    "kecamatan"
                                ]
                            ); ?>

                        </h3>

                    </div>

                </div>



                <!-- KABUPATEN -->

                <div class="profil-card">

                    <div class="profil-icon">

                        <i
                            class="fa-solid fa-location-crosshairs"
                        ></i>

                    </div>


                    <div>

                        <span>
                            Kabupaten
                        </span>

                        <h3>

                            <?= e(
                                $dataProfil[
                                    "kabupaten"
                                ]
                            ); ?>

                        </h3>

                    </div>

                </div>



                <!-- PROVINSI -->

                <div class="profil-card">

                    <div class="profil-icon">

                        <i
                            class="fa-solid fa-earth-asia"
                        ></i>

                    </div>


                    <div>

                        <span>
                            Provinsi
                        </span>

                        <h3>

                            <?= e(
                                $dataProfil[
                                    "provinsi"
                                ]
                            ); ?>

                        </h3>

                    </div>

                </div>


            </div>

        </div>

    </section>



    <!-- =================================================
         KEPALA SEKOLAH
    ================================================== -->

    <section class="profil-section kepala-section">

        <div class="container">


            <div class="section-title">

                <span>

                    Pimpinan

                </span>


                <h2>

                    Kepala Sekolah

                </h2>

            </div>



            <div class="kepala-profil">


                <div class="kepala-profil-foto">


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
                            alt="Foto Kepala Sekolah"
                        >

                    <?php else: ?>

                        <div
                            class="kepala-profil-placeholder"
                        >

                            <i
                                class="fa-solid fa-user-tie"
                            ></i>

                        </div>

                    <?php endif; ?>


                </div>



                <div class="kepala-profil-info">


                    <span>

                        Kepala Sekolah

                    </span>


                    <h2>

                        <?= e(
                            $dataProfil[
                                "nama_kepala_sekolah"
                            ]
                        ); ?>

                    </h2>


                    <?php if (
                        !empty(
                            $dataProfil[
                                "nip_kepala_sekolah"
                            ]
                        )
                    ): ?>

                        <p>

                            <strong>NIP:</strong>

                            <?= e(
                                $dataProfil[
                                    "nip_kepala_sekolah"
                                ]
                            ); ?>

                        </p>

                    <?php endif; ?>


                    <p>

                        <?= e(
                            $dataProfil[
                                "nama_sekolah"
                            ]
                        ); ?>

                    </p>


                </div>

            </div>

        </div>

    </section>



    <!-- =================================================
         SEJARAH SEKOLAH
    ================================================== -->

    <section class="profil-section">

        <div class="container">


            <div class="section-title">

                <span>

                    Tentang Sekolah

                </span>


                <h2>

                    Sejarah Sekolah

                </h2>

            </div>



            <div class="profil-text-card">


                <?php if (
                    trim(
                        $dataProfil["sejarah"]
                    ) !== ''
                ): ?>

                    <p>

                        <?= nl2br(
                            e(
                                $dataProfil[
                                    "sejarah"
                                ]
                            )
                        ); ?>

                    </p>

                <?php else: ?>

                    <div class="profil-empty">

                        <i
                            class="fa-solid fa-book-open"
                        ></i>


                        <p>

                            Informasi sejarah sekolah
                            belum tersedia.

                        </p>

                    </div>

                <?php endif; ?>


            </div>

        </div>

    </section>



    <!-- =================================================
         VISI DAN MISI
    ================================================== -->

    <section class="profil-section visi-misi-section">

        <div class="container">


            <div class="section-title">

                <span>

                    Arah Sekolah

                </span>


                <h2>

                    Visi dan Misi

                </h2>

            </div>



            <div class="visi-misi-grid">


                <!-- VISI -->

                <div class="visi-card">


                    <div class="visi-icon">

                        <i
                            class="fa-solid fa-eye"
                        ></i>

                    </div>


                    <h2>

                        Visi Sekolah

                    </h2>


                    <?php if (
                        trim(
                            $dataProfil["visi"]
                        ) !== ''
                    ): ?>

                        <p>

                            <?= nl2br(
                                e(
                                    $dataProfil[
                                        "visi"
                                    ]
                                )
                            ); ?>

                        </p>

                    <?php else: ?>

                        <p class="text-empty">

                            Visi sekolah belum
                            tersedia.

                        </p>

                    <?php endif; ?>


                </div>



                <!-- MISI -->

                <div class="misi-card">


                    <div class="misi-icon">

                        <i
                            class="fa-solid fa-bullseye"
                        ></i>

                    </div>


                    <h2>

                        Misi Sekolah

                    </h2>


                    <?php if (
                        trim(
                            $dataProfil["misi"]
                        ) !== ''
                    ): ?>

                        <p>

                            <?= nl2br(
                                e(
                                    $dataProfil[
                                        "misi"
                                    ]
                                )
                            ); ?>

                        </p>

                    <?php else: ?>

                        <p class="text-empty">

                            Misi sekolah belum
                            tersedia.

                        </p>

                    <?php endif; ?>


                </div>


            </div>

        </div>

    </section>



    <!-- =================================================
         TUJUAN SEKOLAH
    ================================================== -->

    <section class="profil-section">

        <div class="container">


            <div class="section-title">

                <span>

                    Pendidikan

                </span>


                <h2>

                    Tujuan Sekolah

                </h2>

            </div>



            <div class="profil-text-card tujuan-card">


                <?php if (
                    trim(
                        $dataProfil["tujuan"]
                    ) !== ''
                ): ?>

                    <div class="tujuan-icon">

                        <i
                            class="fa-solid fa-graduation-cap"
                        ></i>

                    </div>


                    <p>

                        <?= nl2br(
                            e(
                                $dataProfil[
                                    "tujuan"
                                ]
                            )
                        ); ?>

                    </p>

                <?php else: ?>

                    <div class="profil-empty">

                        <i
                            class="fa-solid fa-graduation-cap"
                        ></i>


                        <p>

                            Informasi tujuan sekolah
                            belum tersedia.

                        </p>

                    </div>

                <?php endif; ?>


            </div>

        </div>

    </section>



    <!-- =================================================
         ALAMAT
    ================================================== -->

    <section class="profil-section alamat-section">

        <div class="container">


            <div class="alamat-card">


                <div class="alamat-icon">

                    <i
                        class="fa-solid fa-location-dot"
                    ></i>

                </div>


                <div>

                    <span>

                        Lokasi Sekolah

                    </span>


                    <h2>

                        <?= e(
                            $dataProfil[
                                "nama_sekolah"
                            ]
                        ); ?>

                    </h2>


                    <p>

                        <?= e(
                            $dataProfil[
                                "alamat"
                            ]
                        ); ?>

                    </p>


                    <p>

                        Desa
                        <?= e(
                            $dataProfil[
                                "desa"
                            ]
                        ); ?>

                        • Kecamatan
                        <?= e(
                            $dataProfil[
                                "kecamatan"
                            ]
                        ); ?>

                        •
                        <?= e(
                            $dataProfil[
                                "kabupaten"
                            ]
                        ); ?>

                        •
                        <?= e(
                            $dataProfil[
                                "provinsi"
                            ]
                        ); ?>

                    </p>

                </div>

            </div>

        </div>

    </section>


</main>


<!-- =====================================================
     CSS KHUSUS HALAMAN PROFIL
===================================================== -->

<style>

    /* =================================================
       DASAR
    ================================================= */

    .profil-page {

        background: #ffffff;

        color: #333;

    }


    .profil-page * {

        box-sizing: border-box;

    }


    .profil-page .container {

        width: 90%;

        max-width: 1200px;

        margin: auto;

    }



    /* =================================================
       HERO PROFIL
    ================================================= */

    .profil-hero {

        position: relative;

        padding: 95px 0 80px;

        background:
            linear-gradient(
                135deg,
                #164f63,
                #1e718c
            );

        color: #ffffff;

        overflow: hidden;

    }


    .profil-hero::before {

        content: "";

        position: absolute;

        width: 300px;

        height: 300px;

        border-radius: 50%;

        background:
            rgba(255,255,255,.07);

        top: -130px;

        right: -80px;

    }


    .profil-hero::after {

        content: "";

        position: absolute;

        width: 220px;

        height: 220px;

        border-radius: 50%;

        background:
            rgba(255,255,255,.05);

        bottom: -100px;

        left: -70px;

    }


    .profil-hero-content {

        position: relative;

        z-index: 2;

        max-width: 850px;

    }


    .profil-label {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        padding: 8px 15px;

        margin-bottom: 18px;

        border-radius: 30px;

        background:
            rgba(255,255,255,.14);

        border:
            1px solid
            rgba(255,255,255,.18);

        font-size: 13px;

    }


    .profil-hero h1 {

        margin: 0 0 18px;

        font-size: 42px;

        line-height: 1.2;

        font-weight: 700;

    }


    .profil-hero p {

        margin: 0;

        max-width: 700px;

        font-size: 16px;

        line-height: 1.8;

        color:
            rgba(255,255,255,.88);

    }



    /* =================================================
       SECTION
    ================================================= */

    .profil-section {

        padding: 75px 0;

    }


    .profil-section:nth-of-type(even) {

        background: #f8fcfe;

    }


    .section-title {

        text-align: center;

        margin-bottom: 45px;

    }


    .section-title span {

        display: block;

        color: #70c5e5;

        font-size: 13px;

        font-weight: 600;

        text-transform: uppercase;

        letter-spacing: 1.5px;

        margin-bottom: 7px;

    }


    .section-title h2 {

        margin: 0;

        color: #164f63;

        font-size: 30px;

        font-weight: 700;

    }


    .section-title p {

        color: #777;

        font-size: 14px;

        margin-top: 10px;

    }



    /* =================================================
       IDENTITAS
    ================================================= */

    .profil-identitas-grid {

        display: grid;

        grid-template-columns:
            repeat(2, 1fr);

        gap: 20px;

    }


    .profil-card {

        display: flex;

        align-items: flex-start;

        gap: 18px;

        padding: 24px;

        background: #ffffff;

        border:
            1px solid
            #e8eef0;

        border-radius: 16px;

        box-shadow:
            0 8px 25px
            rgba(0,0,0,.05);

        transition:
            .25s ease;

    }


    .profil-card:hover {

        transform:
            translateY(-4px);

        box-shadow:
            0 12px 30px
            rgba(0,0,0,.08);

    }


    .profil-icon {

        width: 48px;

        height: 48px;

        flex-shrink: 0;

        border-radius: 13px;

        display: flex;

        align-items: center;

        justify-content: center;

        background: #e7f7fd;

        color: #164f63;

        font-size: 19px;

    }


    .profil-card span {

        display: block;

        margin-bottom: 6px;

        color: #888;

        font-size: 12px;

    }


    .profil-card h3 {

        margin: 0;

        color: #164f63;

        font-size: 16px;

        line-height: 1.6;

        font-weight: 600;

    }



    /* =================================================
       KEPALA SEKOLAH
    ================================================= */

    .kepala-section {

        background: #f8fcfe;

    }


    .kepala-profil {

        display: grid;

        grid-template-columns:
            300px 1fr;

        gap: 60px;

        align-items: center;

        max-width: 950px;

        margin: auto;

    }


    .kepala-profil-foto {

        text-align: center;

    }


    .kepala-profil-foto img {

        width: 270px;

        height: 340px;

        object-fit: cover;

        border-radius: 20px;

        box-shadow:
            0 15px 35px
            rgba(0,0,0,.12);

    }


    .kepala-profil-placeholder {

        width: 270px;

        height: 340px;

        margin: auto;

        border-radius: 20px;

        display: flex;

        align-items: center;

        justify-content: center;

        background: #e7f7fd;

        color: #70c5e5;

        font-size: 75px;

    }


    .kepala-profil-info span {

        color: #70c5e5;

        font-size: 13px;

        font-weight: 600;

        text-transform: uppercase;

        letter-spacing: 1px;

    }


    .kepala-profil-info h2 {

        margin:
            10px 0 15px;

        color: #164f63;

        font-size: 30px;

    }


    .kepala-profil-info p {

        color: #666;

        font-size: 15px;

        line-height: 1.8;

        margin-bottom: 8px;

    }



    /* =================================================
       TEXT CARD
    ================================================= */

    .profil-text-card {

        max-width: 950px;

        margin: auto;

        padding: 32px;

        background: #ffffff;

        border:
            1px solid
            #e8eef0;

        border-radius: 18px;

        box-shadow:
            0 8px 25px
            rgba(0,0,0,.05);

    }


    .profil-text-card p {

        margin: 0;

        color: #666;

        font-size: 15px;

        line-height: 1.9;

    }


    .profil-empty {

        text-align: center;

        padding: 20px;

        color: #999;

    }


    .profil-empty i {

        display: block;

        margin-bottom: 12px;

        color: #70c5e5;

        font-size: 35px;

    }


    .profil-empty p {

        color: #999;

    }



    /* =================================================
       VISI MISI
    ================================================= */

    .visi-misi-grid {

        display: grid;

        grid-template-columns:
            repeat(2, 1fr);

        gap: 25px;

        max-width: 1000px;

        margin: auto;

    }


    .visi-card,
    .misi-card {

        padding: 32px;

        border-radius: 18px;

        background: #ffffff;

        border:
            1px solid
            #e8eef0;

        box-shadow:
            0 8px 25px
            rgba(0,0,0,.05);

    }


    .visi-icon,
    .misi-icon {

        width: 55px;

        height: 55px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 15px;

        margin-bottom: 20px;

        background: #e7f7fd;

        color: #164f63;

        font-size: 22px;

    }


    .visi-card h2,
    .misi-card h2 {

        margin: 0 0 15px;

        color: #164f63;

        font-size: 22px;

    }


    .visi-card p,
    .misi-card p {

        margin: 0;

        color: #666;

        font-size: 15px;

        line-height: 1.9;

    }


    .text-empty {

        color: #999 !important;

    }



    /* =================================================
       TUJUAN
    ================================================= */

    .tujuan-card {

        display: flex;

        align-items: flex-start;

        gap: 20px;

    }


    .tujuan-icon {

        width: 55px;

        height: 55px;

        flex-shrink: 0;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 15px;

        background: #fff5c9;

        color: #c9a51c;

        font-size: 22px;

    }



    /* =================================================
       ALAMAT
    ================================================= */

    .alamat-section {

        background: #f8fcfe;

    }


    .alamat-card {

        display: flex;

        align-items: flex-start;

        gap: 20px;

        max-width: 950px;

        margin: auto;

        padding: 30px;

        border-radius: 18px;

        background: #ffffff;

        border:
            1px solid
            #e8eef0;

        box-shadow:
            0 8px 25px
            rgba(0,0,0,.05);

    }


    .alamat-icon {

        width: 55px;

        height: 55px;

        flex-shrink: 0;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 15px;

        background: #e7f7fd;

        color: #164f63;

        font-size: 22px;

    }


    .alamat-card span {

        color: #70c5e5;

        font-size: 12px;

        font-weight: 600;

        text-transform: uppercase;

        letter-spacing: 1px;

    }


    .alamat-card h2 {

        margin:
            7px 0 10px;

        color: #164f63;

        font-size: 21px;

    }


    .alamat-card p {

        margin:
            4px 0;

        color: #666;

        font-size: 14px;

        line-height: 1.7;

    }



    /* =================================================
       RESPONSIVE HP
    ================================================= */

    @media (max-width: 768px) {


        .profil-hero {

            padding:
                65px 0 55px;

        }


        .profil-hero h1 {

            font-size: 28px;

        }


        .profil-hero p {

            font-size: 14px;

        }


        .profil-section {

            padding:
                55px 0;

        }


        .section-title {

            margin-bottom: 30px;

        }


        .section-title h2 {

            font-size: 25px;

        }


        .profil-identitas-grid {

            grid-template-columns: 1fr;

        }


        .kepala-profil {

            grid-template-columns: 1fr;

            gap: 30px;

            text-align: center;

        }


        .kepala-profil-info h2 {

            font-size: 25px;

        }


        .visi-misi-grid {

            grid-template-columns: 1fr;

        }


        .profil-text-card {

            padding: 23px 20px;

        }


        .tujuan-card {

            flex-direction: column;

        }


        .alamat-card {

            flex-direction: column;

            padding: 24px 20px;

        }

    }


    @media (max-width: 480px) {


        .profil-page .container {

            width: 92%;

        }


        .profil-hero h1 {

            font-size: 24px;

        }


        .profil-label {

            font-size: 11px;

        }


        .profil-card {

            padding: 18px;

        }


        .profil-card h3 {

            font-size: 14px;

        }


        .kepala-profil-foto img,
        .kepala-profil-placeholder {

            width: 220px;

            height: 280px;

        }


        .visi-card,
        .misi-card {

            padding: 24px 20px;

        }

    }

</style>


<?php

/* =====================================================
   FOOTER
===================================================== */

require_once "layouts/publik/kaki.php";

?>
