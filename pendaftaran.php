<?php


/* =====================================================
   SESSION
===================================================== */

session_start();

require_once "config/koneksi.php";


/* =====================================================
/* =====================================================
   DATA PROFIL SEKOLAH
===================================================== */

$dataProfil = [
    "nama_sekolah" => NAMA_SEKOLAH_KAPITAL,
    "alamat" => ALAMAT_SEKOLAH,
    "nama_kepala_sekolah" => "Kepala Sekolah",
    "foto_kepala_sekolah" => "",
    "logo" => ""
];


$queryProfil = db_query(
    $koneksi,
    "SELECT * FROM profil LIMIT 1"
);


if (!$queryProfil) {

    die(
        "Terjadi kesalahan pada tabel profil: " .
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
   HALAMAN AKTIF
===================================================== */

$halamanAktif = basename($_SERVER["PHP_SELF"]);

?>

<?php

/* =========================================================
   PENGATURAN TAMPILAN HALAMAN
   Bagian atas, menu, dan bagian bawah halaman diambil dari
   folder layouts/ supaya tidak ditulis berulang.
========================================================== */

$judul         = "Pendaftaran - " . ($dataProfil["nama_sekolah"] ?? "");

$css           = "assets/css/publik/pendaftaran.css";

$halamanAktif  = basename(__FILE__);

$font_pusat    = true;      /* halaman ini memakai font Poppins */

require_once "layouts/publik/kepala.php";
require_once "layouts/publik/navbar.php";
?>


<!-- =====================================================
     HERO PENDAFTARAN
===================================================== -->

<section class="hero-pendaftaran">

    <div class="container">

        <div class="hero-pendaftaran-content">

            <div class="hero-label">

                <i class="fa-solid fa-user-plus"></i>

                Penerimaan Peserta Didik

            </div>


            <h2>
                Informasi Pendaftaran
            </h2>


            <p>

                Informasi mengenai persyaratan,
                prosedur, dan layanan pendaftaran
                peserta didik baru di
                <?= e($dataProfil["nama_sekolah"]); ?>.

            </p>

        </div>

    </div>

</section>


<!-- =====================================================
     INFORMASI SINGKAT
===================================================== -->

<section>

    <div class="container">


        <div class="section-title">

            <span>
                Pendaftaran
            </span>


            <h2>
                Informasi Penting
            </h2>


            <p>
                Hal-hal yang perlu diketahui sebelum
                melakukan pendaftaran.
            </p>

        </div>


        <div class="quick-grid">


            <div class="quick-card">

                <div class="quick-icon">

                    <i class="fa-solid fa-calendar-days"></i>

                </div>


                <h3>
                    Jadwal Pendaftaran
                </h3>


                <p>
                    Silakan mengikuti jadwal pendaftaran
                    yang diumumkan oleh pihak sekolah.
                </p>

            </div>


            <div class="quick-card">

                <div class="quick-icon">

                    <i class="fa-solid fa-file-lines"></i>

                </div>


                <h3>
                    Persyaratan
                </h3>


                <p>
                    Siapkan seluruh dokumen persyaratan
                    sebelum melakukan pendaftaran.
                </p>

            </div>


            <div class="quick-card">

                <div class="quick-icon">

                    <i class="fa-solid fa-headset"></i>

                </div>


                <h3>
                    Bantuan
                </h3>


                <p>
                    Hubungi pihak sekolah jika membutuhkan
                    informasi atau bantuan pendaftaran.
                </p>

            </div>


        </div>

    </div>

</section>


<!-- =====================================================
     PERSYARATAN DAN PROSEDUR
===================================================== -->

<section style="background:#f8fcfe;">

    <div class="container">


        <div class="section-title">

            <span>
                Panduan
            </span>


            <h2>
                Persyaratan & Prosedur
            </h2>


            <p>
                Ikuti informasi berikut sebelum melakukan
                proses pendaftaran.
            </p>

        </div>


        <div class="content-grid">


            <!-- PERSYARATAN -->

            <div class="content-box">

                <h3>

                    <i
                        class="fa-solid fa-clipboard-check"
                        style="color:#70c5e5;margin-right:8px;"
                    ></i>

                    Persyaratan Pendaftaran

                </h3>


                <ul class="requirements">


                    <li>

                        <i class="fa-solid fa-check"></i>

                        <span>
                            Mengisi formulir pendaftaran
                            peserta didik.
                        </span>

                    </li>


                    <li>

                        <i class="fa-solid fa-check"></i>

                        <span>
                            Menyerahkan dokumen atau berkas
                            persyaratan yang diminta sekolah.
                        </span>

                    </li>


                    <li>

                        <i class="fa-solid fa-check"></i>

                        <span>
                            Membawa dokumen asli apabila
                            diperlukan untuk proses verifikasi.
                        </span>

                    </li>


                    <li>

                        <i class="fa-solid fa-check"></i>

                        <span>
                            Mengikuti proses verifikasi data
                            sesuai ketentuan sekolah.
                        </span>

                    </li>


                    <li>

                        <i class="fa-solid fa-check"></i>

                        <span>
                            Mematuhi seluruh ketentuan
                            pendaftaran yang berlaku.
                        </span>

                    </li>


                </ul>

            </div>


            <!-- LANGKAH -->

            <div class="content-box">

                <h3>

                    <i
                        class="fa-solid fa-list-ol"
                        style="color:#70c5e5;margin-right:8px;"
                    ></i>

                    Langkah Pendaftaran

                </h3>


                <div class="steps">


                    <div class="step">

                        <div class="step-number">
                            1
                        </div>


                        <div class="step-content">

                            <h4>
                                Persiapkan Berkas
                            </h4>


                            <p>
                                Siapkan seluruh dokumen
                                yang diperlukan.
                            </p>

                        </div>

                    </div>


                    <div class="step">

                        <div class="step-number">
                            2
                        </div>


                        <div class="step-content">

                            <h4>
                                Isi Formulir
                            </h4>


                            <p>
                                Lengkapi formulir pendaftaran
                                dengan data yang benar.
                            </p>

                        </div>

                    </div>


                    <div class="step">

                        <div class="step-number">
                            3
                        </div>


                        <div class="step-content">

                            <h4>
                                Verifikasi
                            </h4>


                            <p>
                                Pihak sekolah melakukan
                                pemeriksaan data dan berkas.
                            </p>

                        </div>

                    </div>


                    <div class="step">

                        <div class="step-number">
                            4
                        </div>


                        <div class="step-content">

                            <h4>
                                Selesai
                            </h4>


                            <p>
                                Ikuti informasi lanjutan dari
                                pihak sekolah.
                            </p>

                        </div>

                    </div>


                </div>

            </div>


        </div>


        <!-- DOWNLOAD FORM -->

        <div class="download-box">

            <h3>

                <i
                    class="fa-solid fa-download"
                    style="margin-right:7px;"
                ></i>

                Formulir Pendaftaran

            </h3>


            <p>

                Jika sekolah menyediakan formulir
                pendaftaran dalam bentuk dokumen,
                formulir dapat diunduh melalui halaman
                Dokumen.

            </p>


            <a
                href="dokumen.php"
                class="button button-yellow"
            >

                <i class="fa-solid fa-file-arrow-down"></i>

                Lihat Dokumen

            </a>

        </div>


        <!-- KONTAK -->

        <div class="contact-box">

            <h3>

                <i
                    class="fa-solid fa-circle-info"
                    style="margin-right:7px;"
                ></i>

                Informasi & Bantuan

            </h3>


            <div class="contact-item">

                <i class="fa-solid fa-school"></i>

                <span>

                    <?= e($dataProfil["nama_sekolah"]); ?>

                </span>

            </div>


            <div class="contact-item">

                <i class="fa-solid fa-location-dot"></i>

                <span>

                    <?= e($dataProfil["alamat"]); ?>

                </span>

            </div>


            <div class="contact-item">

                <i class="fa-solid fa-phone"></i>

                <span>

                    Silakan menghubungi pihak sekolah
                    secara langsung untuk informasi
                    pendaftaran terbaru.

                </span>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     FOOTER
===================================================== -->

<?php require_once "layouts/publik/kaki.php"; ?>
