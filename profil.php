<?php
session_start();

require_once "config/koneksi.php";

if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

$halamanAktif = 'profil.php';

$dataProfil = [
    'nama_sekolah'        => 'Nama Sekolah',
    'alamat'              => '',
    'desa'                => '',
    'kecamatan'           => '',
    'kabupaten'           => '',
    'provinsi'            => '',
    'sejarah'             => '',
    'visi'                => '',
    'misi'                => '',
    'tujuan'              => '',
    'nama_kepala_sekolah' => '',
    'nip_kepala_sekolah'  => '',
    'foto_kepala_sekolah' => '',
    'logo'                => ''
];

$result = db_query(
    $koneksi,
    "SELECT * FROM profil LIMIT 1"
);

if ($result && db_num_rows($result) > 0) {
    $row = db_fetch_assoc($result);

    if (is_array($row)) {
        $dataProfil = array_merge($dataProfil, $row);
    }
}


/*
|--------------------------------------------------------------------------
| Fungsi untuk menampilkan teks biasa
|--------------------------------------------------------------------------
*/

function tampilTeks($teks)
{
    if (empty($teks)) {
        return '';
    }

    return nl2br(e($teks));
}


/*
|--------------------------------------------------------------------------
| Fungsi untuk merapikan daftar bernomor
|--------------------------------------------------------------------------
|
| Contoh data:
|
| 1. Meningkatkan mutu pendidikan
| 2. Mengembangkan karakter siswa
| 3. Meningkatkan prestasi
|
| Akan ditampilkan menjadi daftar bernomor yang rapi.
|
*/

function tampilDaftar($teks)
{
    if (empty($teks)) {
        return '';
    }

    $teks = trim($teks);

    /*
    | Normalisasi line break
    */
    $teks = str_replace(["\r\n", "\r"], "\n", $teks);

    /*
    | Pecah berdasarkan baris
    */
    $baris = preg_split('/\n+/', $teks);

    $hasil = [];
    $adaNomor = false;

    foreach ($baris as $item) {

        $item = trim($item);

        if ($item === '') {
            continue;
        }

        /*
        | Deteksi nomor:
        | 1. isi
        | 2. isi
        | 3) isi
        */
        if (preg_match('/^\s*(\d+)[\.\)]\s*(.+)$/', $item, $cocok)) {

            $adaNomor = true;

            $hasil[] = [
                'nomor' => $cocok[1],
                'isi'   => $cocok[2]
            ];

        } else {

            $hasil[] = [
                'nomor' => '',
                'isi'   => $item
            ];
        }
    }


    /*
    | Jika tidak ada nomor,
    | tampilkan sebagai paragraf biasa.
    */
    if (!$adaNomor) {

        return nl2br(e($teks));
    }


    /*
    | Jika ada nomor,
    | tampilkan menggunakan daftar custom.
    */
    $output = '<div class="daftar-rapi">';

    foreach ($hasil as $item) {

        if ($item['nomor'] !== '') {

            $output .= '
                <div class="daftar-item">
                    <span class="daftar-nomor">'
                    . e($item['nomor']) .
                    '.</span>
                    <span class="daftar-isi">'
                    . e($item['isi']) .
                    '</span>
                </div>
            ';

        } else {

            $output .= '
                <div class="daftar-paragraf">'
                . e($item['isi']) .
                '</div>
            ';
        }
    }

    $output .= '</div>';

    return $output;
}


require_once "layouts/publik/kepala.php";
require_once "layouts/publik/navbar.php";
?>

<style>

/* =========================================================
   HALAMAN PROFIL
========================================================= */

.profil-page {
    background: #f5f7fa;
    min-height: 100vh;
    padding-bottom: 45px;
}


/* =========================================================
   HERO
========================================================= */

.profil-hero {
    position: relative;
    background: linear-gradient(
        135deg,
        #164f63,
        #246b7e
    );

    color: #ffffff;
    padding: 50px 20px;
    text-align: center;
}

.profil-hero h1 {
    margin: 0 0 8px;
    font-size: 30px;
    font-weight: 700;
}

.profil-hero p {
    margin: 0;
    font-size: 15px;
    opacity: 0.9;
}

.logo-sekolah {
    width: 90px;
    height: 90px;
    object-fit: contain;
    display: block;
    margin: 0 auto 15px;
}


/* =========================================================
   CONTAINER
========================================================= */

.profil-container {
    width: 90%;
    max-width: 1050px;
    margin: 30px auto 0;
}


/* =========================================================
   CARD
========================================================= */

.profil-card {
    background: #ffffff;
    border-radius: 13px;
    padding: 24px;
    margin-bottom: 18px;

    box-shadow:
        0 3px 14px rgba(0, 0, 0, 0.06);
}


/* =========================================================
   JUDUL CARD
========================================================= */

.profil-card h2 {
    margin: 0 0 16px;

    color: #164f63;

    font-size: 20px;
    font-weight: 700;

    border-left: 4px solid #164f63;

    padding-left: 10px;
}


/* =========================================================
   TEKS UMUM
========================================================= */

.profil-card p {
    color: #555555;

    font-size: 14px;

    line-height: 1.65;

    margin: 0 0 8px;
}


/* =========================================================
   IDENTITAS SEKOLAH
========================================================= */

.identitas-table {
    width: 100%;
    border-collapse: collapse;
}

.identitas-table tr {
    border-bottom: 1px solid #eeeeee;
}

.identitas-table tr:last-child {
    border-bottom: none;
}

.identitas-table td {
    padding: 9px 7px;

    vertical-align: top;

    font-size: 13.5px;

    line-height: 1.5;
}

.identitas-table td:first-child {
    width: 190px;

    font-weight: 600;

    color: #164f63;
}


/* =========================================================
   KEPALA SEKOLAH
========================================================= */

.kepala-sekolah {
    display: flex;

    align-items: center;

    gap: 22px;
}

.kepala-foto {
    width: 150px;
    height: 190px;

    border-radius: 10px;

    object-fit: cover;

    background: #eeeeee;

    flex-shrink: 0;
}

.kepala-info h3 {
    margin: 0 0 7px;

    font-size: 20px;

    color: #164f63;
}

.kepala-info p {
    margin: 4px 0;

    font-size: 13.5px;
}


/* =========================================================
   SEJARAH
========================================================= */

.sejarah-text {
    color: #555555;

    font-size: 13.5px;

    line-height: 1.7;

    text-align: justify;

    margin: 0;

    white-space: normal;
}


/* =========================================================
   VISI & MISI
========================================================= */

.visi-misi {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 15px;
}

.visi-box,
.misi-box {
    background: #f7fafb;

    border-radius: 10px;

    padding: 17px 18px;

    border: 1px solid #e6eef1;
}

.visi-box h3,
.misi-box h3 {
    margin: 0 0 9px;

    color: #164f63;

    font-size: 17px;

    font-weight: 700;
}


/* =========================================================
   ISI VISI
========================================================= */

.visi-box p {
    margin: 0;

    color: #555555;

    font-size: 13.5px;

    line-height: 1.65;

    text-align: justify;
}


/* =========================================================
   DAFTAR MISI & TUJUAN
========================================================= */

.daftar-rapi {
    width: 100%;
}

.daftar-item {
    display: flex;

    align-items: flex-start;

    gap: 8px;

    margin-bottom: 7px;

    color: #555555;

    font-size: 13.5px;

    line-height: 1.6;

    text-align: justify;
}

.daftar-nomor {
    flex: 0 0 20px;

    color: #164f63;

    font-weight: 600;

    text-align: right;
}

.daftar-isi {
    flex: 1;
}

.daftar-paragraf {
    color: #555555;

    font-size: 13.5px;

    line-height: 1.65;

    margin-bottom: 7px;

    text-align: justify;
}


/* =========================================================
   TUJUAN
========================================================= */

.tujuan-box {
    background: #f7fafb;

    border-radius: 10px;

    padding: 17px 18px;

    border: 1px solid #e6eef1;
}


/* =========================================================
   LOKASI
========================================================= */

.lokasi-box {
    background: #f7fafb;

    padding: 16px 18px;

    border-radius: 10px;

    color: #555555;

    font-size: 13.5px;

    line-height: 1.7;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 768px) {

    .profil-hero {
        padding: 45px 18px;
    }

    .profil-hero h1 {
        font-size: 27px;
    }

    .profil-hero p {
        font-size: 14px;
    }

    .profil-container {
        width: 92%;

        margin-top: 22px;
    }

    .profil-card {
        padding: 20px;
    }

    .profil-card h2 {
        font-size: 19px;
    }

    .identitas-table td:first-child {
        width: 140px;
    }

    .kepala-sekolah {
        flex-direction: column;

        text-align: center;
    }

    .visi-misi {
        grid-template-columns: 1fr;
    }

}


/* =========================================================
   HP
========================================================= */

@media (max-width: 480px) {

    .profil-hero {
        padding: 40px 15px;
    }

    .profil-hero h1 {
        font-size: 24px;
    }

    .profil-hero p {
        font-size: 13.5px;
    }

    .logo-sekolah {
        width: 75px;
        height: 75px;
    }

    .profil-container {
        width: 94%;

        margin-top: 18px;
    }

    .profil-card {
        padding: 17px;

        border-radius: 11px;

        margin-bottom: 15px;
    }

    .profil-card h2 {
        font-size: 18px;

        margin-bottom: 13px;

        padding-left: 8px;

        border-left-width: 3px;
    }

    .profil-card p {
        font-size: 13px;

        line-height: 1.6;
    }

    .identitas-table,
    .identitas-table tbody,
    .identitas-table tr,
    .identitas-table td {
        display: block;

        width: 100%;
    }

    .identitas-table td:first-child {
        width: 100%;

        padding-bottom: 2px;

        font-size: 13px;
    }

    .identitas-table td:last-child {
        padding-top: 2px;

        padding-bottom: 9px;

        font-size: 13px;
    }

    .kepala-foto {
        width: 135px;

        height: 175px;
    }

    .kepala-info h3 {
        font-size: 18px;
    }

    .kepala-info p {
        font-size: 13px;
    }

    .sejarah-text {
        font-size: 13px;

        line-height: 1.6;
    }

    .visi-box,
    .misi-box,
    .tujuan-box {
        padding: 15px;
    }

    .visi-box h3,
    .misi-box h3 {
        font-size: 16px;
    }

    .visi-box p,
    .daftar-item,
    .daftar-paragraf {
        font-size: 13px;

        line-height: 1.6;
    }

    .lokasi-box {
        font-size: 13px;

        line-height: 1.6;
    }

}

</style>


<div class="profil-page">


    <!-- =====================================================
         HERO
    ====================================================== -->

    <section class="profil-hero">

        <?php if (!empty($dataProfil['logo'])): ?>

            <img
                src="<?= e('uploads/' . ltrim($dataProfil['logo'], '/')); ?>"
                alt="Logo Sekolah"
                class="logo-sekolah"
            >

        <?php endif; ?>


        <h1>
            Profil Sekolah
        </h1>


        <p>
            <?= e($dataProfil['nama_sekolah']); ?>
        </p>

    </section>



    <div class="profil-container">


        <!-- =================================================
             IDENTITAS SEKOLAH
        ================================================== -->

        <section class="profil-card">

            <h2>
                Identitas Sekolah
            </h2>


            <table class="identitas-table">

                <tr>

                    <td>
                        Nama Sekolah
                    </td>

                    <td>
                        <?= e($dataProfil['nama_sekolah']); ?>
                    </td>

                </tr>


                <tr>

                    <td>
                        Alamat
                    </td>

                    <td>
                        <?= e($dataProfil['alamat']); ?>
                    </td>

                </tr>


                <tr>

                    <td>
                        Desa / Kelurahan
                    </td>

                    <td>
                        <?= e($dataProfil['desa']); ?>
                    </td>

                </tr>


                <tr>

                    <td>
                        Kecamatan
                    </td>

                    <td>
                        <?= e($dataProfil['kecamatan']); ?>
                    </td>

                </tr>


                <tr>

                    <td>
                        Kabupaten
                    </td>

                    <td>
                        <?= e($dataProfil['kabupaten']); ?>
                    </td>

                </tr>


                <tr>

                    <td>
                        Provinsi
                    </td>

                    <td>
                        <?= e($dataProfil['provinsi']); ?>
                    </td>

                </tr>

            </table>

        </section>



        <!-- =================================================
             KEPALA SEKOLAH
        ================================================== -->

        <section class="profil-card">

            <h2>
                Kepala Sekolah
            </h2>


            <div class="kepala-sekolah">


                <?php if (!empty($dataProfil['foto_kepala_sekolah'])): ?>

                    <img
                        src="<?= e('uploads/' . ltrim($dataProfil['foto_kepala_sekolah'], '/')); ?>"
                        alt="Foto Kepala Sekolah"
                        class="kepala-foto"
                    >

                <?php else: ?>

                    <div class="kepala-foto"></div>

                <?php endif; ?>


                <div class="kepala-info">

                    <h3>
                        <?= e($dataProfil['nama_kepala_sekolah']); ?>
                    </h3>


                    <?php if (!empty($dataProfil['nip_kepala_sekolah'])): ?>

                        <p>

                            <strong>NIP:</strong>

                            <?= e($dataProfil['nip_kepala_sekolah']); ?>

                        </p>

                    <?php endif; ?>

                </div>

            </div>

        </section>



        <!-- =================================================
             SEJARAH SEKOLAH
        ================================================== -->

        <section class="profil-card">

            <h2>
                Sejarah Sekolah
            </h2>


            <div class="sejarah-text">

                <?= tampilTeks($dataProfil['sejarah']); ?>

            </div>

        </section>



        <!-- =================================================
             VISI DAN MISI
        ================================================== -->

        <section class="profil-card">

            <h2>
                Visi dan Misi
            </h2>


            <div class="visi-misi">


                <!-- =========================
                     VISI
                ========================== -->

                <div class="visi-box">

                    <h3>
                        Visi
                    </h3>


                    <p>
                        <?= tampilTeks($dataProfil['visi']); ?>
                    </p>

                </div>



                <!-- =========================
                     MISI
                ========================== -->

                <div class="misi-box">

                    <h3>
                        Misi
                    </h3>


                    <?= tampilDaftar($dataProfil['misi']); ?>

                </div>


            </div>

        </section>



        <!-- =================================================
             TUJUAN SEKOLAH
        ================================================== -->

        <section class="profil-card">

            <h2>
                Tujuan Sekolah
            </h2>


            <div class="tujuan-box">

                <?= tampilDaftar($dataProfil['tujuan']); ?>

            </div>

        </section>





    </div>

</div>



<?php
require_once "layouts/publik/kaki.php";
?>
