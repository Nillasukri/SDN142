<?php
require_once "config/koneksi.php";


function tanggalIndonesia($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }

    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $timestamp = strtotime($tanggal);

    if (!$timestamp) {
        return $tanggal;
    }

    return date('d', $timestamp) . ' ' .
        $bulan[(int) date('m', $timestamp)] . ' ' .
        date('Y', $timestamp);
}


/*
|--------------------------------------------------------------------------
| HALAMAN AKTIF
|--------------------------------------------------------------------------
*/

$halamanAktif = basename($_SERVER['PHP_SELF']);


/*
|--------------------------------------------------------------------------
| DATA PROFIL SEKOLAH
|--------------------------------------------------------------------------
*/

$dataProfil = [
    "nama_sekolah" => NAMA_SEKOLAH_KAPITAL,
    "alamat" => ALAMAT_SEKOLAH,
    "desa" => DESA_SEKOLAH,
    "kecamatan" => KECAMATAN_SEKOLAH,
    "kabupaten" => KABUPATEN_SEKOLAH,
    "provinsi" => PROVINSI_SEKOLAH,
    "logo" => ""
];

$queryProfil = db_query(
    $koneksi,
    "SELECT * FROM profil LIMIT 1"
);

if ($queryProfil) {

    $profil = db_fetch_assoc($queryProfil);

    if ($profil) {

        $dataProfil = array_merge(
            $dataProfil,
            $profil
        );
    }
}


/*
|--------------------------------------------------------------------------
| DATA DOKUMEN
|--------------------------------------------------------------------------
*/

$queryDokumen = db_query(
    $koneksi,
    "SELECT * FROM dokumen
     ORDER BY tanggal DESC, id DESC"
);

if (!$queryDokumen) {

    die(
        "Terjadi kesalahan saat mengambil data dokumen: "
        . db_error($koneksi)
    );
}

$dokumenList = [];

while ($row = db_fetch_assoc($queryDokumen)) {

    $dokumenList[] = $row;
}


/*
|--------------------------------------------------------------------------
| JUMLAH DOKUMEN
|--------------------------------------------------------------------------
*/

$jumlahDokumen = count($dokumenList);


/*
|--------------------------------------------------------------------------
| KATEGORI DOKUMEN
|--------------------------------------------------------------------------
*/

$kategoriList = [];

foreach ($dokumenList as $dokumen) {

    $kategori = trim(
        $dokumen["kategori"] ?? ''
    );

    if (
        $kategori !== '' &&
        !in_array($kategori, $kategoriList)
    ) {

        $kategoriList[] = $kategori;
    }
}

sort($kategoriList);

?>

<?php

/* =========================================================
   PENGATURAN TAMPILAN HALAMAN
   Bagian atas, menu, dan bagian bawah halaman diambil dari
   folder layouts/ supaya tidak ditulis berulang.
========================================================== */

$judul         = "Dokumen - " . ($dataProfil["nama_sekolah"] ?? "");

$css           = "assets/css/publik/dokumen.css";

$halamanAktif  = basename(__FILE__);

$deskripsi     = "Dokumen dan berkas informasi " . ($dataProfil["nama_sekolah"] ?? "");

$font_pusat    = true;      /* halaman ini memakai font Poppins */

require_once "layouts/publik/kepala.php";
require_once "layouts/publik/navbar.php";
?>


<!-- =========================================================
     HERO
========================================================= -->

<section class="hero">

    <div class="hero-box">

        <div class="hero-content">

            <div class="hero-label">

                <i class="fa-solid fa-folder-open"></i>

                Pusat Dokumen Sekolah

            </div>


            <h1>

                Dokumen Sekolah

            </h1>


            <p>

                Temukan dan unduh berbagai dokumen,
                informasi administrasi, formulir,
                jadwal, surat, serta berkas penting
                lainnya dari

                <?= e(
                    $dataProfil["nama_sekolah"]
                ); ?>.

            </p>

        </div>

    </div>

</section>


<!-- =========================================================
     CONTENT
========================================================= -->

<main class="content-container">

    <div class="section-heading">

        <div>

            <h2 class="section-title">

                Daftar Dokumen

            </h2>


            <p class="section-subtitle">

                Dokumen yang dapat diakses dan diunduh
                oleh orang tua dan peserta didik.

            </p>

        </div>


        <div class="document-count">

            <?= $jumlahDokumen ?>

            Dokumen

        </div>

    </div>


    <!-- =====================================================
         FILTER KATEGORI
    ====================================================== -->

    <?php if ($jumlahDokumen > 0): ?>

        <div class="filter-wrapper">

            <button
                type="button"
                class="filter-button active"
                onclick="filterDokumen('semua', this)"
            >

                Semua

            </button>


            <?php foreach ($kategoriList as $kategori): ?>

                <button
                    type="button"
                    class="filter-button"
                    onclick="filterDokumen(
                        '<?= e($kategori) ?>',
                        this
                    )"
                >

                    <?= e($kategori) ?>

                </button>

            <?php endforeach; ?>

        </div>


        <!-- =================================================
             DOCUMENT GRID
        ================================================== -->

        <div
            class="document-grid"
            id="documentGrid"
        >

            <?php foreach ($dokumenList as $dokumen): ?>

                <?php

                $judul =
                    !empty($dokumen["judul"])
                    ? $dokumen["judul"]
                    : "Dokumen Sekolah";


                $kategori =
                    !empty($dokumen["kategori"])
                    ? $dokumen["kategori"]
                    : "Dokumen";


                $deskripsi =
                    !empty($dokumen["deskripsi"])
                    ? $dokumen["deskripsi"]
                    : "Dokumen resmi sekolah.";


                $namaFile =
                    $dokumen["nama_file"] ?? "";


                $filePath =
                    "uploads/dokumen/" .
                    $namaFile;

                ?>


                <article
                    class="document-card"
                    data-category="<?= e($kategori) ?>"
                >

                    <div class="document-icon">

                        <i class="fa-solid fa-file-lines"></i>

                    </div>


                    <div class="document-info">

                        <span class="document-category">

                            <?= e($kategori) ?>

                        </span>


                        <h3 class="document-title">

                            <?= e($judul) ?>

                        </h3>


                        <p class="document-description">

                            <?= nl2br(
                                e($deskripsi)
                            ) ?>

                        </p>


                        <?php if (!empty($dokumen["tanggal"])): ?>

                            <div class="document-date">

                                <i class="fa-regular fa-calendar"></i>

                                <?= tanggalIndonesia(
                                    $dokumen["tanggal"]
                                ) ?>

                            </div>

                        <?php endif; ?>


                        <?php if (!empty($namaFile)): ?>

                            <a
                                href="<?= e($filePath) ?>"
                                class="document-action"
                                download
                                target="_blank"
                            >

                                <i class="fa-solid fa-download"></i>

                                Download Dokumen

                            </a>

                        <?php endif; ?>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>


        <!-- =================================================
             INFO BOX
        ================================================== -->

        <div class="info-box">

            <div class="info-icon">

                <i class="fa-solid fa-lightbulb"></i>

            </div>


            <div>

                <strong>

                    Informasi Download

                </strong>


                <p>

                    Klik tombol
                    <b>Download Dokumen</b>
                    untuk membuka atau mengunduh
                    berkas yang tersedia.
                    Jika dokumen tidak dapat dibuka,
                    silakan hubungi pihak sekolah.

                </p>

            </div>

        </div>


    <?php else: ?>


        <!-- =================================================
             EMPTY STATE
        ================================================== -->

        <div class="empty-state">

            <div class="empty-icon">

                <i class="fa-solid fa-folder-open"></i>

            </div>


            <h3>

                Belum Ada Dokumen

            </h3>


            <p>

                Saat ini belum ada dokumen yang
                tersedia untuk diunduh.

                <br>

                Silakan kembali lagi nanti.

            </p>

        </div>

    <?php endif; ?>

</main>


<!-- =========================================================
     FOOTER
========================================================= -->

<!-- =====================================================
     JAVASCRIPT KHUSUS HALAMAN INI
===================================================== -->

<script>


/* =========================================================
   FILTER DOKUMEN
========================================================= */

function filterDokumen(
    kategori,
    tombol
) {

    const cards =
        document.querySelectorAll(
            ".document-card"
        );


    const buttons =
        document.querySelectorAll(
            ".filter-button"
        );


    /*
    |--------------------------------------------------------------------------
    | RESET TOMBOL
    |--------------------------------------------------------------------------
    */

    buttons.forEach(
        function (button) {

            button.classList.remove(
                "active"
            );

        }
    );


    tombol.classList.add(
        "active"
    );


    /*
    |--------------------------------------------------------------------------
    | FILTER DOKUMEN
    |--------------------------------------------------------------------------
    */

    cards.forEach(
        function (card) {

            const category =
                card.getAttribute(
                    "data-category"
                );


            if (
                kategori === "semua" ||
                category === kategori
            ) {

                card.style.display =
                    "flex";

            } else {

                card.style.display =
                    "none";

            }

        }
    );

}

</script>

<?php require_once "layouts/publik/kaki.php"; ?>


</body>

</html>