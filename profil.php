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

/* =========================
   DATA DEFAULT
========================= */
$dataProfil = [
    'nama_sekolah'         => 'Nama Sekolah',
    'alamat'               => '',
    'desa'                 => '',
    'kecamatan'            => '',
    'kabupaten'            => '',
    'provinsi'             => '',
    'sejarah'              => '',
    'visi'                 => '',
    'misi'                 => '',
    'tujuan'               => '',
    'nama_kepala_sekolah'  => '',
    'nip_kepala_sekolah'   => '',
    'foto_kepala_sekolah'  => '',
    'logo'                 => ''
];

/* =========================
   AMBIL DATA PROFIL
========================= */
$result = db_query(
    $koneksi,
    "SELECT * FROM profil LIMIT 1"
);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $dataProfil = array_merge($dataProfil, $row);
}

require_once "layouts/publik/kepala.php";
require_once "layouts/publik/navbar.php";
?>

<style>
    .profil-page {
        background: #f5f7fa;
        min-height: 100vh;
        padding-bottom: 60px;
    }

    .profil-hero {
        position: relative;
        background: linear-gradient(
            135deg,
            #164f63,
            #246b7e
        );
        color: white;
        padding: 70px 20px;
        text-align: center;
    }

    .profil-hero h1 {
        margin: 0 0 12px;
        font-size: 38px;
        font-weight: 700;
    }

    .profil-hero p {
        margin: 0;
        font-size: 17px;
        opacity: 0.9;
    }

    .profil-container {
        width: 90%;
        max-width: 1100px;
        margin: 40px auto 0;
    }

    .profil-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07);
    }

    .profil-card h2 {
        margin: 0 0 20px;
        color: #164f63;
        font-size: 24px;
        border-left: 5px solid #164f63;
        padding-left: 12px;
    }

    .profil-card p {
        color: #555;
        line-height: 1.8;
        margin: 0 0 12px;
    }

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
        padding: 12px 8px;
        vertical-align: top;
        line-height: 1.6;
    }

    .identitas-table td:first-child {
        width: 200px;
        font-weight: 600;
        color: #164f63;
    }

    .kepala-sekolah {
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .kepala-foto {
        width: 180px;
        height: 220px;
        border-radius: 12px;
        object-fit: cover;
        background: #eeeeee;
        flex-shrink: 0;
    }

    .kepala-info h3 {
        margin: 0 0 8px;
        font-size: 24px;
        color: #164f63;
    }

    .kepala-info p {
        margin: 5px 0;
    }

    .visi-misi {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }

    .visi-box,
    .misi-box {
        background: #f7fafb;
        border-radius: 12px;
        padding: 25px;
    }

    .visi-box h3,
    .misi-box h3 {
        margin: 0 0 12px;
        color: #164f63;
    }

    .misi-box ol,
    .misi-box ul {
        margin: 0;
        padding-left: 22px;
        color: #555;
        line-height: 1.8;
    }

    .logo-sekolah {
        width: 120px;
        height: 120px;
        object-fit: contain;
        display: block;
        margin: 0 auto 20px;
    }

    .lokasi-box {
        background: #f7fafb;
        padding: 20px;
        border-radius: 12px;
        color: #555;
        line-height: 1.8;
    }

    @media (max-width: 768px) {
        .profil-hero {
            padding: 55px 20px;
        }

        .profil-hero h1 {
            font-size: 30px;
        }

        .profil-container {
            width: 92%;
            margin-top: 25px;
        }

        .profil-card {
            padding: 22px;
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

    @media (max-width: 480px) {
        .profil-hero h1 {
            font-size: 26px;
        }

        .profil-hero p {
            font-size: 15px;
        }

        .profil-card h2 {
            font-size: 20px;
        }

        .identitas-table,
        .identitas-table tbody,
        .identitas-table tr,
        .identitas-table td {
            display: block;
            width: 100%;
        }

        .identitas-table td:first-child {
            padding-bottom: 3px;
        }

        .identitas-table td:last-child {
            padding-top: 3px;
            padding-bottom: 12px;
        }
    }
</style>

<div class="profil-page">

    <!-- HERO -->
    <section class="profil-hero">
        <?php if (!empty($dataProfil['logo'])): ?>
            <img
                src="<?= e($dataProfil['logo']); ?>"
                alt="Logo Sekolah"
                class="logo-sekolah"
            >
        <?php endif; ?>

        <h1>Profil Sekolah</h1>

        <p>
            <?= e($dataProfil['nama_sekolah']); ?>
        </p>
    </section>

    <div class="profil-container">

        <!-- IDENTITAS SEKOLAH -->
        <section class="profil-card">
            <h2>Identitas Sekolah</h2>

            <table class="identitas-table">
                <tr>
                    <td>Nama Sekolah</td>
                    <td><?= e($dataProfil['nama_sekolah']); ?></td>
                </tr>

                <tr>
                    <td>Alamat</td>
                    <td><?= e($dataProfil['alamat']); ?></td>
                </tr>

                <tr>
                    <td>Desa / Kelurahan</td>
                    <td><?= e($dataProfil['desa']); ?></td>
                </tr>

                <tr>
                    <td>Kecamatan</td>
                    <td><?= e($dataProfil['kecamatan']); ?></td>
                </tr>

                <tr>
                    <td>Kabupaten</td>
                    <td><?= e($dataProfil['kabupaten']); ?></td>
                </tr>

                <tr>
                    <td>Provinsi</td>
                    <td><?= e($dataProfil['provinsi']); ?></td>
                </tr>
            </table>
        </section>

        <!-- KEPALA SEKOLAH -->
        <section class="profil-card">
            <h2>Kepala Sekolah</h2>

            <div class="kepala-sekolah">

                <?php if (!empty($dataProfil['foto_kepala_sekolah'])): ?>

                    <img
                        src="<?= e($dataProfil['foto_kepala_sekolah']); ?>"
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

        <!-- SEJARAH -->
        <section class="profil-card">
            <h2>Sejarah Sekolah</h2>

            <p>
                <?= nl2br(e($dataProfil['sejarah'])); ?>
            </p>
        </section>

        <!-- VISI MISI -->
        <section class="profil-card">
            <h2>Visi dan Misi</h2>

            <div class="visi-misi">

                <div class="visi-box">
                    <h3>Visi</h3>

                    <p>
                        <?= nl2br(e($dataProfil['visi'])); ?>
                    </p>
                </div>

                <div class="misi-box">
                    <h3>Misi</h3>

                    <div>
                        <?= nl2br(e($dataProfil['misi'])); ?>
                    </div>
                </div>

            </div>
        </section>

        <!-- TUJUAN -->
        <section class="profil-card">
            <h2>Tujuan Sekolah</h2>

            <p>
                <?= nl2br(e($dataProfil['tujuan'])); ?>
            </p>
        </section>

        <!-- LOKASI -->
        <section class="profil-card">
            <h2>Lokasi Sekolah</h2>

            <div class="lokasi-box">

                <?php if (!empty($dataProfil['alamat'])): ?>
                    <?= e($dataProfil['alamat']); ?><br>
                <?php endif; ?>

                <?php if (!empty($dataProfil['desa'])): ?>
                    <?= e($dataProfil['desa']); ?>,
                <?php endif; ?>

                <?php if (!empty($dataProfil['kecamatan'])): ?>
                    <?= e($dataProfil['kecamatan']); ?>,
                <?php endif; ?>

                <?php if (!empty($dataProfil['kabupaten'])): ?>
                    <?= e($dataProfil['kabupaten']); ?>,
                <?php endif; ?>

                <?php if (!empty($dataProfil['provinsi'])): ?>
                    <?= e($dataProfil['provinsi']); ?>
                <?php endif; ?>

            </div>
        </section>

    </div>
</div>

<?php
require_once "layouts/publik/kaki.php";
?>
