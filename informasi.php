<?php

require_once "config/koneksi.php";


/* =====================================================
   FUNGSI
===================================================== */


function tanggalIndonesia($tanggal)
{
    if (!$tanggal) {
        return "-";
    }


    $bulan = [

        1  => "Januari",
        2  => "Februari",
        3  => "Maret",
        4  => "April",
        5  => "Mei",
        6  => "Juni",
        7  => "Juli",
        8  => "Agustus",
        9  => "September",
        10 => "Oktober",
        11 => "November",
        12 => "Desember"

    ];


    $pecah = explode(
        "-",
        $tanggal
    );


    if (count($pecah) != 3) {
        return "-";
    }


    return $pecah[2] . " " .
           $bulan[(int)$pecah[1]] . " " .
           $pecah[0];
}


/* =====================================================
   HALAMAN AKTIF
===================================================== */

$halamanAktif =
    basename($_SERVER['PHP_SELF']);


/* =====================================================
   DATA PROFIL SEKOLAH
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

    "logo" =>
        ""

];


$queryProfil = db_query(
    $koneksi,
    "SELECT *
     FROM profil
     LIMIT 1"
);


if (
    $queryProfil &&
    db_num_rows($queryProfil) > 0
) {

    $profilDatabase =
        db_fetch_assoc(
            $queryProfil
        );


    $dataProfil =
        array_merge(
            $dataProfil,
            $profilDatabase
        );
}


/* =====================================================
   AMBIL DATA INFORMASI
===================================================== */

$queryInformasi = db_query(
    $koneksi,
    "SELECT *
     FROM informasi
     ORDER BY tanggal DESC, id DESC"
);


if (!$queryInformasi) {

    die(

        "Terjadi kesalahan saat mengambil data informasi: " .

        db_error($koneksi)

    );

}

?>

<?php

/* =========================================================
   PENGATURAN TAMPILAN HALAMAN
   Bagian atas, menu, dan bagian bawah halaman diambil dari
   folder layouts/ supaya tidak ditulis berulang.
========================================================== */

$judul         = "Informasi | " . ($dataProfil["nama_sekolah"] ?? "");

$css           = "assets/css/publik/informasi.css";

$halamanAktif  = basename(__FILE__);

$deskripsi     = "Informasi dan berita " . ($dataProfil["nama_sekolah"] ?? "");

$font_pusat    = true;      /* halaman ini memakai font Poppins */

require_once "layouts/publik/kepala.php";
require_once "layouts/publik/navbar.php";
?>


<!-- =====================================================
     PAGE HEADER
===================================================== -->

<section class="page-header">


    <h1>

        Informasi & Berita Sekolah

    </h1>


    <p>

        Informasi terbaru mengenai kegiatan,
        pengumuman, jadwal, dan berbagai kegiatan

        <?= e(
            $dataProfil["nama_sekolah"]
        ); ?>.

    </p>


</section>


<!-- =====================================================
     CONTENT INFORMASI
===================================================== -->

<main class="informasi-container">


    <?php if (
        db_num_rows(
            $queryInformasi
        ) > 0
    ): ?>


        <div class="informasi-grid">


            <?php while (
                $info =
                    db_fetch_assoc(
                        $queryInformasi
                    )
            ): ?>


                <article class="card">


                    <!-- FOTO -->

                    <?php if (
                        !empty(
                            $info["foto"]
                        )
                    ): ?>


                        <img
                            src="uploads/informasi/<?= e(
                                $info["foto"]
                            ); ?>"
                            alt="<?= e(
                                $info["judul"]
                            ); ?>"
                            class="card-image"
                        >


                    <?php else: ?>


                        <div class="no-image">

                            <i
                                class="fa-solid fa-newspaper"
                            ></i>

                        </div>


                    <?php endif; ?>


                    <!-- CONTENT CARD -->

                    <div class="card-content">


                        <div class="tanggal">

                            <i
                                class="fa-regular fa-calendar"
                            ></i>

                            <?= tanggalIndonesia(
                                $info["tanggal"]
                            ); ?>

                        </div>


                        <h2>

                            <?= e(
                                $info["judul"]
                            ); ?>

                        </h2>


                        <div class="isi">

                            <?= nl2br(
                                e(
                                    $info["isi"]
                                )
                            ); ?>

                        </div>


                    </div>


                </article>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <!-- =================================================
             BELUM ADA INFORMASI
        ================================================= -->

        <div class="empty">


            <div class="empty-icon">

                <i
                    class="fa-solid fa-newspaper"
                ></i>

            </div>


            <h2>

                Belum Ada Informasi

            </h2>


            <p>

                Saat ini belum ada informasi
                atau berita yang dipublikasikan
                oleh sekolah.

            </p>


        </div>


    <?php endif; ?>


</main>


<!-- =====================================================
     FOOTER
===================================================== -->

<?php require_once "layouts/publik/kaki.php"; ?>


</body>

</html>