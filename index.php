<?php

/* =====================================================
   SESSION
===================================================== */

session_start();

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
        1 => "Januari",
        2 => "Februari",
        3 => "Maret",
        4 => "April",
        5 => "Mei",
        6 => "Juni",
        7 => "Juli",
        8 => "Agustus",
        9 => "September",
        10 => "Oktober",
        11 => "November",
        12 => "Desember"
    ];

    $pecah = explode("-", $tanggal);

    if (count($pecah) != 3) {
        return "-";
    }

    return $pecah[2] . " " .
           $bulan[(int)$pecah[1]] . " " .
           $pecah[0];
}


/* =====================================================
   LOGIN ADMIN DARI HALAMAN AWAL
===================================================== */

$pesanLogin = "";

if (isset($_POST["login_admin"])) {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        $pesanLogin = "Username dan password wajib diisi.";

    } else {

        $stmt = db_prepare(
            $koneksi,
            "SELECT id, username, password, nama
             FROM admin
             WHERE username = ?
             LIMIT 1"
        );

        db_stmt_bind_param(
            $stmt,
            "s",
            $username
        );

        db_stmt_execute($stmt);

        $hasil = db_stmt_get_result($stmt);

        $admin = db_fetch_assoc($hasil);

        if ($admin) {

            /*
             * Mendukung password_hash()
             * dan password plaintext untuk akun
             * prototype lama.
             */

            $passwordBenar = false;

            if (
                password_verify(
                    $password,
                    $admin["password"]
                )
            ) {

                $passwordBenar = true;

            } elseif (
                hash_equals(
                    (string)$admin["password"],
                    (string)$password
                )
            ) {

                $passwordBenar = true;
            }


            if ($passwordBenar) {

                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_username"] = $admin["username"];
                $_SESSION["admin_nama"] = $admin["nama"];

                header("Location: admin/dashboard.php");
                exit;

            } else {

                $pesanLogin = "Username atau password salah.";
            }

        } else {

            $pesanLogin = "Username atau password salah.";
        }

        db_stmt_close($stmt);
    }
}


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
   DATA BANNER
===================================================== */

$banner = null;


$queryBanner = db_query(
    $koneksi,
    "SELECT * FROM banner
     WHERE status = 'aktif'
     ORDER BY id DESC
     LIMIT 1"
);


if ($queryBanner) {

    $banner = db_fetch_assoc($queryBanner);
}


/* =====================================================
   DATA INFORMASI
===================================================== */

$queryInformasi = db_query(
    $koneksi,
    "SELECT * FROM informasi
     ORDER BY tanggal DESC, id DESC
     LIMIT 3"
);


/* =====================================================
   DATA GURU
===================================================== */

$queryGuru = db_query(
    $koneksi,
    "SELECT * FROM guru
     ORDER BY id ASC"
);

?>

<?php

/* =========================================================
   PENGATURAN TAMPILAN HALAMAN
   Bagian atas, menu, dan bagian bawah halaman diambil dari
   folder layouts/ supaya tidak ditulis berulang.
========================================================== */

$judul         = $dataProfil["nama_sekolah"] ?? "";

$css           = "assets/css/publik/index.css";

$halamanAktif  = basename(__FILE__);

$font_pusat    = true;      /* halaman ini memakai font Poppins */

require_once "layouts/publik/kepala.php";
require_once "layouts/publik/navbar.php";
?>


<!-- =====================================================
     HERO / BANNER
===================================================== -->

<section class="hero">


    <?php if ($banner && !empty($banner["foto"])): ?>

        <div
            class="hero-image"
            style="
                background-image:
                url('uploads/<?= e($banner["foto"]); ?>');
            "
        ></div>

    <?php else: ?>

        <div class="hero-image"></div>

    <?php endif; ?>


    <div class="container">

        <div class="hero-content">


            <div class="label">

                <i class="fa-solid fa-graduation-cap"></i>

                Sekolah Dasar Negeri

            </div>


            <?php if ($banner): ?>

                <h2>
                    <?= e($banner["judul"]); ?>
                </h2>


                <?php if (!empty($banner["deskripsi"])): ?>

                    <p>
                        <?= nl2br(e($banner["deskripsi"])); ?>
                    </p>

                <?php endif; ?>


                <?php if (!empty($banner["tombol_link"])): ?>

                    <a
                        href="<?= e($banner["tombol_link"]); ?>"
                        class="button button-yellow"
                    >

                        <?= !empty($banner["tombol_text"])
                            ? e($banner["tombol_text"])
                            : "Selengkapnya"; ?>

                    </a>

                <?php endif; ?>


                <a
                    href="profil.php"
                    class="button button-white"
                >

                    Profil Sekolah

                </a>


            <?php else: ?>

                <h2>

                    Selamat Datang di
                    <?= e($dataProfil["nama_sekolah"]); ?>

                </h2>


                <p>

                    Website resmi sekolah sebagai pusat
                    informasi, layanan sekolah, dan
                    komunikasi dengan masyarakat.

                </p>


                <a
                    href="profil.php"
                    class="button button-yellow"
                >

                    Profil Sekolah

                </a>


                <a
                    href="informasi.php"
                    class="button button-white"
                >

                    Informasi Sekolah

                </a>

            <?php endif; ?>


        </div>

    </div>

</section>


<!-- =====================================================
     SAMBUTAN KEPALA SEKOLAH
===================================================== -->

<section class="sambutan">

    <div class="container">


        <div class="section-title">

            <span>
                Kepala Sekolah
            </span>


            <h2>
                Sambutan Kepala Sekolah
            </h2>

        </div>


        <div class="sambutan-grid">


            <div class="kepala-foto">


                <?php if (
                    !empty(
                        $dataProfil["foto_kepala_sekolah"]
                    )
                ): ?>

                    <img
                        src="uploads/<?= e(
                            $dataProfil["foto_kepala_sekolah"]
                        ); ?>"
                        alt="Kepala Sekolah"
                    >

                <?php else: ?>

                    <div class="kepala-placeholder">

                        <i class="fa-solid fa-user-tie"></i>

                    </div>

                <?php endif; ?>


                <div class="kepala-nama">

                    <h3>

                        <?= e(
                            $dataProfil["nama_kepala_sekolah"]
                        ); ?>

                    </h3>


                    <p>
                        Kepala Sekolah
                    </p>

                </div>

            </div>


            <div class="sambutan-text">


                <h2>
                    Selamat Datang
                </h2>


                <p>

                    Selamat datang di website resmi

                    <strong>

                        <?= e(
                            $dataProfil["nama_sekolah"]
                        ); ?>

                    </strong>.

                </p>


                <p>

                    Website ini menjadi media informasi
                    sekolah dan sarana komunikasi antara
                    sekolah, peserta didik, orang tua,
                    dan masyarakat.

                </p>


                <div class="quote">

                    <i class="fa-solid fa-quote-left"></i>

                    Bersama membangun pendidikan
                    yang berkualitas, berkarakter,
                    dan berprestasi.

                </div>


                <br>


                <a
                    href="profil.php"
                    class="button button-yellow"
                >

                    Lihat Profil Sekolah

                </a>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     INFORMASI TERBARU
===================================================== -->

<section>

    <div class="container">


        <div class="section-title">

            <span>
                Informasi
            </span>


            <h2>
                Informasi Terbaru
            </h2>


            <p>
                Berita dan pengumuman terbaru dari sekolah.
            </p>

        </div>


        <div class="informasi-grid">


            <?php if (
                $queryInformasi &&
                db_num_rows($queryInformasi) > 0
            ): ?>


                <?php while (
                    $info = db_fetch_assoc(
                        $queryInformasi
                    )
                ): ?>


                    <article class="informasi-card">


                        <div class="informasi-foto">


                            <?php if (
                                !empty($info["foto"])
                            ): ?>

                                <img
                                    src="uploads/<?= e(
                                        $info["foto"]
                                    ); ?>"
                                    alt="<?= e(
                                        $info["judul"]
                                    ); ?>"
                                >

                            <?php else: ?>

                                <div class="no-foto">

                                    <i class="fa-solid fa-bullhorn"></i>

                                </div>

                            <?php endif; ?>


                        </div>


                        <div class="informasi-isi">


                            <div class="tanggal">

                                <i class="fa-regular fa-calendar"></i>

                                <?= tanggalIndonesia(
                                    $info["tanggal"]
                                ); ?>

                            </div>


                            <h3>

                                <?= e($info["judul"]); ?>

                            </h3>


                            <p>

                                <?= e($info["isi"]); ?>

                            </p>


                        </div>

                    </article>


                <?php endwhile; ?>


            <?php else: ?>


                <div
                    style="
                        grid-column: 1 / -1;
                        text-align: center;
                        padding: 40px;
                        color: #999;
                    "
                >

                    <i
                        class="fa-regular fa-newspaper"
                        style="
                            font-size: 40px;
                            margin-bottom: 10px;
                        "
                    ></i>


                    <p>
                        Belum ada informasi.
                    </p>

                </div>


            <?php endif; ?>


        </div>


        <div
            style="
                text-align: center;
                margin-top: 30px;
            "
        >

            <a
                href="informasi.php"
                class="button button-yellow"
            >

                Lihat Semua Informasi

            </a>

        </div>

    </div>

</section>


<!-- =====================================================
     GURU DAN TENAGA KEPENDIDIKAN
===================================================== -->

<section class="guru">

    <div class="container">


        <div class="section-title">

            <span>
                Tenaga Pendidik
            </span>


            <h2>
                Guru & Tenaga Kependidikan
            </h2>


            <p>
                Guru dan tenaga kependidikan sekolah.
            </p>

        </div>


        <!-- ==================================================
             KARTU GURU & TENAGA KEPENDIDIKAN

             Kartu bisa digeser ke samping dengan:
             1. tombol panah di sisi kiri / kanan, atau
             2. di-swipe / diseret langsung (mouse atau jari)
        ================================================== -->

        <div class="geser-wrap">


            <!-- TOMBOL GESER (KIRI) -->

            <button
                type="button"
                class="geser-btn geser-kiri"
                data-geser="#guru-slider"
                data-arah="-1"
                aria-label="Geser guru ke kiri"
            >
                <i class="fa-solid fa-chevron-left"></i>
            </button>


            <!-- KOTAK KARTU (yang digeser) -->

            <div
                class="guru-slider"
                id="guru-slider"
            >


                <?php if (
                    $queryGuru &&
                    db_num_rows($queryGuru) > 0
                ): ?>


                    <?php while (
                        $guru = db_fetch_assoc(
                            $queryGuru
                        )
                    ): ?>


                        <div class="guru-card">


                            <div class="guru-foto">


                                <?php if (
                                    !empty($guru["foto"])
                                ): ?>

                                    <!-- PERBAIKAN PATH FOTO GURU -->

                                    <img
                                        src="uploads/guru/<?= e(
                                            $guru["foto"]
                                        ); ?>"
                                        alt="<?= e(
                                            $guru["nama"]
                                        ); ?>"
                                    >

                                <?php else: ?>

                                    <div class="guru-placeholder">

                                        <i class="fa-solid fa-user"></i>

                                    </div>

                                <?php endif; ?>


                            </div>


                            <div class="guru-info">


                                <h3>

                                    <?= e($guru["nama"]); ?>

                                </h3>


                                <p>

                                    <?= !empty($guru["jabatan"])
                                        ? e($guru["jabatan"])
                                        : "Tenaga Kependidikan";
                                    ?>

                                </p>


                            </div>


                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div
                        style="
                            width: 100%;
                            text-align: center;
                            padding: 40px;
                            color: #999;
                        "
                    >

                        <i
                            class="fa-solid fa-users"
                            style="
                                font-size: 40px;
                                margin-bottom: 10px;
                            "
                        ></i>


                        <p>
                            Data guru belum tersedia.
                        </p>

                    </div>


                <?php endif; ?>



            </div>


            <!-- TOMBOL GESER (KANAN) -->

            <button
                type="button"
                class="geser-btn geser-kanan"
                data-geser="#guru-slider"
                data-arah="1"
                aria-label="Geser guru ke kanan"
            >
                <i class="fa-solid fa-chevron-right"></i>
            </button>


        </div>

        <div class="swipe-info">

            <i class="fa-solid fa-arrows-left-right"></i>

            Geser ke kiri atau kanan, atau klik tombol panah
            di sisi kiri / kanan untuk melihat guru lainnya.

        </div>


    </div>

</section>


<!-- =====================================================
     PENDAFTARAN
===================================================== -->

<section class="pendaftaran">

    <div class="container">


        <div class="pendaftaran-box">


            <div>

                <h2>
                    Penerimaan Peserta Didik
                </h2>


                <p>

                    Lihat informasi persyaratan dan
                    prosedur pendaftaran peserta didik.

                </p>

            </div>


            <a
                href="pendaftaran.php"
                class="button button-yellow"
            >

                <i class="fa-solid fa-user-plus"></i>

                Informasi Pendaftaran

            </a>


        </div>

    </div>

</section>


<!-- =====================================================
     FOOTER
===================================================== -->

<?php require_once "layouts/publik/kaki.php"; ?>


<!-- TOMBOL GESER KARTU GURU -->

<script src="assets/js/geser-guru.js"></script>
