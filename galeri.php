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
| DATA PROFIL
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
| DATA GALERI
|--------------------------------------------------------------------------
*/

$queryGaleri = db_query(
    $koneksi,
    "SELECT * FROM galeri
     ORDER BY created_at DESC, id DESC"
);

if (!$queryGaleri) {

    die(
        "Terjadi kesalahan saat mengambil data galeri: "
        . db_error($koneksi)
    );
}

$jumlahGaleri = db_num_rows($queryGaleri);

?>

<?php

/* =========================================================
   PENGATURAN TAMPILAN HALAMAN
   Bagian atas, menu, dan bagian bawah halaman diambil dari
   folder layouts/ supaya tidak ditulis berulang.
========================================================== */

$judul         = "Galeri - " . ($dataProfil["nama_sekolah"] ?? "");

$css           = "assets/css/publik/galeri.css";

$halamanAktif  = basename(__FILE__);

$deskripsi     = "Galeri kegiatan dan dokumentasi " . ($dataProfil["nama_sekolah"] ?? "");

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

                <i class="fa-solid fa-camera"></i>

                Dokumentasi Sekolah

            </div>


            <h1>

                Galeri Sekolah

            </h1>


            <p>

                Lihat berbagai kegiatan, aktivitas
                pembelajaran, prestasi, dan momen
                berharga keluarga besar

                <?= e(
                    $dataProfil["nama_sekolah"]
                ); ?>.

            </p>

        </div>

    </div>

</section>


<!-- =========================================================
     CONTENT GALERI
========================================================= -->

<main class="content-container">


    <div class="section-heading">


        <div>

            <h2 class="section-title">

                Dokumentasi Kegiatan

            </h2>


            <p class="section-subtitle">

                Kumpulan foto kegiatan dan momen sekolah.

            </p>

        </div>


        <div class="gallery-count">

            <?= $jumlahGaleri ?>

            Foto

        </div>

    </div>


    <?php if ($jumlahGaleri > 0): ?>


        <div class="gallery-grid">


            <?php while (
                $galeri = db_fetch_assoc(
                    $queryGaleri
                )
            ): ?>


                <?php

                $judul =
                    !empty($galeri["judul"])
                    ? $galeri["judul"]
                    : "Dokumentasi Sekolah";


                $keterangan =
                    !empty($galeri["keterangan"])
                    ? $galeri["keterangan"]
                    : "Dokumentasi kegiatan sekolah.";


                /*
                |--------------------------------------------------------------------------
                | LOKASI FOTO GALERI
                |--------------------------------------------------------------------------
                | Foto dari admin disimpan di:
                | uploads/galeri/
                |--------------------------------------------------------------------------
                */

                $foto =
                    !empty($galeri["foto"])
                    ? "uploads/galeri/" .
                      basename($galeri["foto"])
                    : "";

                ?>


                <article class="gallery-card">


                    <div
                        class="gallery-image"
                        <?php if (!empty($foto)): ?>

                            onclick="bukaModal(
                                '<?= e($foto) ?>',
                                '<?= e($judul) ?>'
                            )"

                        <?php endif; ?>
                    >


                        <?php if (!empty($foto)): ?>


                            <img
                                src="<?= e($foto) ?>"
                                alt="<?= e($judul) ?>"
                                loading="lazy"
                            >


                            <div class="image-overlay">


                                <div class="zoom-button">

                                    <i class="fa-solid fa-magnifying-glass-plus"></i>

                                </div>


                            </div>


                        <?php else: ?>


                            <div
                                style="
                                    width:100%;
                                    height:100%;
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    font-size:45px;
                                    color:#70b9d5;
                                "
                            >

                                <i class="fa-solid fa-image"></i>

                            </div>


                        <?php endif; ?>


                    </div>


                    <div class="gallery-content">


                        <h3 class="gallery-title">

                            <?= e($judul) ?>

                        </h3>


                        <p class="gallery-description">

                            <?= nl2br(
                                e($keterangan)
                            ) ?>

                        </p>


                        <?php if (
                            !empty(
                                $galeri["created_at"]
                            )
                        ): ?>


                            <div class="gallery-date">

                                <i class="fa-regular fa-calendar"></i>

                                <?= tanggalIndonesia(
                                    date(
                                        'Y-m-d',
                                        strtotime(
                                            $galeri["created_at"]
                                        )
                                    )
                                ) ?>

                            </div>


                        <?php endif; ?>


                    </div>


                </article>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <div class="empty-state">


            <div class="empty-icon">

                <i class="fa-solid fa-images"></i>

            </div>


            <h3>

                Belum Ada Foto

            </h3>


            <p>

                Dokumentasi kegiatan sekolah belum tersedia.

                <br>

                Silakan tambahkan foto melalui halaman admin.

            </p>


        </div>


    <?php endif; ?>


</main>


<!-- =========================================================
     MODAL FOTO
========================================================= -->

<div
    class="modal"
    id="modalGaleri"
    onclick="tutupModal(event)"
>


    <div class="modal-content">


        <button
            type="button"
            class="modal-close"
            onclick="tutupModal()"
            aria-label="Tutup"
        >

            <i class="fa-solid fa-xmark"></i>

        </button>


        <img
            src=""
            alt=""
            class="modal-image"
            id="modalImage"
        >


        <div
            class="modal-caption"
            id="modalCaption"
        ></div>


    </div>

</div>


<!-- =========================================================
     FOOTER
========================================================= -->

<!-- =====================================================
     JAVASCRIPT KHUSUS HALAMAN INI
===================================================== -->

<script>


/*
|--------------------------------------------------------------------------
| BUKA MODAL FOTO
|--------------------------------------------------------------------------
*/

function bukaModal(
    foto,
    judul
) {

    const modal =
        document.getElementById(
            "modalGaleri"
        );


    const image =
        document.getElementById(
            "modalImage"
        );


    const caption =
        document.getElementById(
            "modalCaption"
        );


    if (!foto) {

        return;

    }


    image.src = foto;

    image.alt = judul;

    caption.textContent = judul;


    modal.classList.add(
        "show"
    );


    document.body.style.overflow =
        "hidden";
}


/*
|--------------------------------------------------------------------------
| TUTUP MODAL
|--------------------------------------------------------------------------
*/

function tutupModal(event)
{

    const modal =
        document.getElementById(
            "modalGaleri"
        );


    /*
    |--------------------------------------------------------------------------
    | Jika klik gambar, modal tidak ditutup.
    |--------------------------------------------------------------------------
    */

    if (
        event &&
        event.target &&
        event.target.id ===
            "modalImage"
    ) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Jika klik bagian dalam modal,
    | jangan tutup kecuali tombol close.
    |--------------------------------------------------------------------------
    */

    if (
        event &&
        event.target &&
        event.target.closest(
            ".modal-content"
        ) &&
        !event.target.closest(
            ".modal-close"
        )
    ) {

        return;

    }


    modal.classList.remove(
        "show"
    );


    document.body.style.overflow =
        "";


    document.getElementById(
        "modalImage"
    ).src = "";

}


/*
|--------------------------------------------------------------------------
| TUTUP MODAL DENGAN ESC
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "keydown",
    function (event) {

        if (
            event.key === "Escape"
        ) {

            const modal =
                document.getElementById(
                    "modalGaleri"
                );


            if (
                modal.classList.contains(
                    "show"
                )
            ) {

                modal.classList.remove(
                    "show"
                );


                document.body.style.overflow =
                    "";


                document.getElementById(
                    "modalImage"
                ).src = "";

            }

        }

    }
);

</script>

<?php require_once "layouts/publik/kaki.php"; ?>
