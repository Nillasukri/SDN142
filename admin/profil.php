<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once "../config/koneksi.php";


/* =========================================================
   DATA ADMIN
========================================================= */

$nama_admin = $_SESSION['admin_nama'] ?? 'Administrator';
$username_admin = $_SESSION['admin_username'] ?? 'admin';


/* =========================================================
   AMBIL DATA PROFIL
========================================================= */

$profil = null;

$query_profil = db_query(
    $koneksi,
    "SELECT * FROM profil ORDER BY id ASC LIMIT 1"
);

if ($query_profil && db_num_rows($query_profil) > 0) {
    $profil = db_fetch_assoc($query_profil);
}


/* =========================================================
   DEFAULT DATA
========================================================= */

if (!$profil) {

    $profil = [
        'id' => '',
        'nama_sekolah' => NAMA_SEKOLAH_KAPITAL,
        'alamat' => '',
        'desa' => '',
        'kecamatan' => '',
        'kabupaten' => '',
        'provinsi' => PROVINSI_SEKOLAH,
        'sejarah' => '',
        'visi' => '',
        'misi' => '',
        'tujuan' => '',
        'nama_kepala_sekolah' => '',
        'nip_kepala_sekolah' => '',
        'foto_kepala_sekolah' => '',
        'logo' => ''
    ];
}


/* =========================================================
   PESAN
========================================================= */

$error = '';
$success = '';

if (isset($_GET['status'])) {

    if ($_GET['status'] === 'sukses') {
        $success = 'Profil sekolah berhasil diperbarui.';
    }

    if ($_GET['status'] === 'foto_sukses') {
        $success = 'Foto kepala sekolah berhasil diperbarui.';
    }

    if ($_GET['status'] === 'logo_sukses') {
        $success = 'Logo sekolah berhasil diperbarui.';
    }
}


/* =========================================================
   SIMPAN / UPDATE PROFIL
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['simpan_profil'])
) {

    $nama_sekolah = trim($_POST['nama_sekolah'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $desa = trim($_POST['desa'] ?? '');
    $kecamatan = trim($_POST['kecamatan'] ?? '');
    $kabupaten = trim($_POST['kabupaten'] ?? '');
    $provinsi = trim($_POST['provinsi'] ?? '');

    $sejarah = trim($_POST['sejarah'] ?? '');
    $visi = trim($_POST['visi'] ?? '');
    $misi = trim($_POST['misi'] ?? '');
    $tujuan = trim($_POST['tujuan'] ?? '');

    $nama_kepala_sekolah = trim(
        $_POST['nama_kepala_sekolah'] ?? ''
    );

    $nip_kepala_sekolah = trim(
        $_POST['nip_kepala_sekolah'] ?? ''
    );


    if ($nama_sekolah === '') {

        $error = 'Nama sekolah wajib diisi.';
    }


    if (
        $nama_kepala_sekolah === '' &&
        $error === ''
    ) {

        $error = 'Nama kepala sekolah wajib diisi.';
    }


    if ($error === '') {


        /* =============================================
           INSERT
        ============================================= */

        if (empty($profil['id'])) {


            $stmt = db_prepare(
                $koneksi,
                "INSERT INTO profil (
                    nama_sekolah,
                    alamat,
                    desa,
                    kecamatan,
                    kabupaten,
                    provinsi,
                    sejarah,
                    visi,
                    misi,
                    tujuan,
                    nama_kepala_sekolah,
                    nip_kepala_sekolah
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );


            if ($stmt) {

                db_stmt_bind_param(
                    $stmt,
                    "ssssssssssss",
                    $nama_sekolah,
                    $alamat,
                    $desa,
                    $kecamatan,
                    $kabupaten,
                    $provinsi,
                    $sejarah,
                    $visi,
                    $misi,
                    $tujuan,
                    $nama_kepala_sekolah,
                    $nip_kepala_sekolah
                );


                if (db_stmt_execute($stmt)) {

                    db_stmt_close($stmt);

                    header(
                        "Location: profil.php?status=sukses"
                    );

                    exit;

                } else {

                    $error =
                        'Profil gagal disimpan ke database.';

                    db_stmt_close($stmt);
                }


            } else {

                $error =
                    'Query database gagal dibuat.';
            }


        }


        /* =============================================
           UPDATE
        ============================================= */

        else {

            $id_profil = (int) $profil['id'];


            $stmt = db_prepare(
                $koneksi,
                "UPDATE profil SET
                    nama_sekolah = ?,
                    alamat = ?,
                    desa = ?,
                    kecamatan = ?,
                    kabupaten = ?,
                    provinsi = ?,
                    sejarah = ?,
                    visi = ?,
                    misi = ?,
                    tujuan = ?,
                    nama_kepala_sekolah = ?,
                    nip_kepala_sekolah = ?
                 WHERE id = ?"
            );


            if ($stmt) {

                db_stmt_bind_param(
                    $stmt,
                    "ssssssssssssi",
                    $nama_sekolah,
                    $alamat,
                    $desa,
                    $kecamatan,
                    $kabupaten,
                    $provinsi,
                    $sejarah,
                    $visi,
                    $misi,
                    $tujuan,
                    $nama_kepala_sekolah,
                    $nip_kepala_sekolah,
                    $id_profil
                );


                if (db_stmt_execute($stmt)) {

                    db_stmt_close($stmt);

                    header(
                        "Location: profil.php?status=sukses"
                    );

                    exit;

                } else {

                    $error =
                        'Profil gagal diperbarui.';

                    db_stmt_close($stmt);
                }


            } else {

                $error =
                    'Query update database gagal dibuat.';
            }
        }
    }


    /* =====================================================
       PERTAHANKAN INPUT JIKA ERROR
    ===================================================== */

    $profil['nama_sekolah'] =
        $nama_sekolah;

    $profil['alamat'] =
        $alamat;

    $profil['desa'] =
        $desa;

    $profil['kecamatan'] =
        $kecamatan;

    $profil['kabupaten'] =
        $kabupaten;

    $profil['provinsi'] =
        $provinsi;

    $profil['sejarah'] =
        $sejarah;

    $profil['visi'] =
        $visi;

    $profil['misi'] =
        $misi;

    $profil['tujuan'] =
        $tujuan;

    $profil['nama_kepala_sekolah'] =
        $nama_kepala_sekolah;

    $profil['nip_kepala_sekolah'] =
        $nip_kepala_sekolah;
}


/* =========================================================
   UPLOAD FOTO KEPALA SEKOLAH
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['upload_foto_kepala'])
) {


    if (empty($profil['id'])) {

        $error =
            'Simpan data profil terlebih dahulu sebelum mengupload foto.';


    } elseif (
        !isset($_FILES['foto_kepala_sekolah']) ||
        $_FILES['foto_kepala_sekolah']['error'] ===
        UPLOAD_ERR_NO_FILE
    ) {

        $error =
            'Silakan pilih foto kepala sekolah.';


    } else {


        $file =
            $_FILES['foto_kepala_sekolah'];

        if (
            $file['error'] !==
            UPLOAD_ERR_OK
        ) {

            $error =
                'Terjadi kesalahan saat mengupload foto.';


        } elseif (
            $file['size'] >
            5 * 1024 * 1024
        ) {

            $error =
                'Ukuran foto terlalu besar. Maksimal 5 MB.';


        } else {


            $allowed_mime = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];


            $finfo =
                finfo_open(FILEINFO_MIME_TYPE);

            $mime =
                finfo_file(
                    $finfo,
                    $file['tmp_name']
                );

            finfo_close($finfo);


            if (
                !in_array(
                    $mime,
                    $allowed_mime,
                    true
                )
            ) {

                $error =
                    'Format foto tidak diperbolehkan. Gunakan JPG, PNG, atau WEBP.';


            } elseif (
                @getimagesize(
                    $file['tmp_name']
                ) === false
            ) {

                $error =
                    'File yang dipilih bukan gambar yang valid.';


            } else {


                $folder =
                    "../uploads/";


                if (!is_dir($folder)) {

                    mkdir(
                        $folder,
                        0777,
                        true
                    );
                }


                $extension_map = [

                    'image/jpeg' =>
                        'jpg',

                    'image/png' =>
                        'png',

                    'image/webp' =>
                        'webp'
                ];


                $extension =
                    $extension_map[$mime];


                $nama_file =
                    'kepala_sekolah_' .
                    date('Ymd_His') .
                    '_' .
                    bin2hex(
                        random_bytes(4)
                    ) .
                    '.' .
                    $extension;


                $target =
                    $folder .
                    $nama_file;


                if (
                    move_uploaded_file(
                        $file['tmp_name'],
                        $target
                    )
                ) {


                    $foto_lama =
                        $profil[
                            'foto_kepala_sekolah'
                        ] ?? '';


                    $id_profil =
                        (int) $profil['id'];


                    $stmt =
                        db_prepare(
                            $koneksi,
                            "UPDATE profil
                             SET foto_kepala_sekolah = ?
                             WHERE id = ?"
                        );


                    if ($stmt) {


                        db_stmt_bind_param(
                            $stmt,
                            "si",
                            $nama_file,
                            $id_profil
                        );


                        if (
                            db_stmt_execute(
                                $stmt
                            )
                        ) {


                            db_stmt_close(
                                $stmt
                            );


                            if (
                                !empty(
                                    $foto_lama
                                ) &&
                                file_exists(
                                    "../uploads/" .
                                    $foto_lama
                                )
                            ) {

                                unlink(
                                    "../uploads/" .
                                    $foto_lama
                                );
                            }


                            header(
                                "Location: profil.php?status=foto_sukses"
                            );

                            exit;


                        } else {


                            db_stmt_close(
                                $stmt
                            );


                            if (
                                file_exists(
                                    $target
                                )
                            ) {

                                unlink($target);
                            }


                            $error =
                                'Foto berhasil diupload tetapi gagal disimpan ke database.';
                        }


                    } else {


                        if (
                            file_exists(
                                $target
                            )
                        ) {

                            unlink($target);
                        }


                        $error =
                            'Query database gagal dibuat.';
                    }


                } else {

                    $error =
                        'Foto gagal dipindahkan ke folder upload.';
                }
            }
        }
    }
}


/* =========================================================
   UPLOAD LOGO
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['upload_logo'])
) {


    if (empty($profil['id'])) {

        $error =
            'Simpan data profil terlebih dahulu sebelum mengupload logo.';


    } elseif (
        !isset($_FILES['logo']) ||
        $_FILES['logo']['error'] ===
        UPLOAD_ERR_NO_FILE
    ) {

        $error =
            'Silakan pilih logo sekolah.';


    } else {


        $file =
            $_FILES['logo'];


        if (
            $file['error'] !==
            UPLOAD_ERR_OK
        ) {

            $error =
                'Terjadi kesalahan saat mengupload logo.';


        } elseif (
            $file['size'] >
            3 * 1024 * 1024
        ) {

            $error =
                'Ukuran logo terlalu besar. Maksimal 3 MB.';


        } else {


            $allowed_mime = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];


            $finfo =
                finfo_open(
                    FILEINFO_MIME_TYPE
                );


            $mime =
                finfo_file(
                    $finfo,
                    $file['tmp_name']
                );


            finfo_close($finfo);


            if (
                !in_array(
                    $mime,
                    $allowed_mime,
                    true
                )
            ) {

                $error =
                    'Format logo tidak diperbolehkan. Gunakan JPG, PNG, atau WEBP.';


            } elseif (
                @getimagesize(
                    $file['tmp_name']
                ) === false
            ) {

                $error =
                    'File logo bukan gambar yang valid.';


            } else {


                $folder =
                    "../uploads/";


                if (!is_dir($folder)) {

                    mkdir(
                        $folder,
                        0777,
                        true
                    );
                }


                $extension_map = [

                    'image/jpeg' =>
                        'jpg',

                    'image/png' =>
                        'png',

                    'image/webp' =>
                        'webp'
                ];


                $extension =
                    $extension_map[$mime];


                $nama_file =
                    'logo_sekolah_' .
                    date('Ymd_His') .
                    '_' .
                    bin2hex(
                        random_bytes(4)
                    ) .
                    '.' .
                    $extension;


                $target =
                    $folder .
                    $nama_file;


                if (
                    move_uploaded_file(
                        $file['tmp_name'],
                        $target
                    )
                ) {


                    $logo_lama =
                        $profil['logo'] ?? '';


                    $id_profil =
                        (int) $profil['id'];


                    $stmt =
                        db_prepare(
                            $koneksi,
                            "UPDATE profil
                             SET logo = ?
                             WHERE id = ?"
                        );


                    if ($stmt) {


                        db_stmt_bind_param(
                            $stmt,
                            "si",
                            $nama_file,
                            $id_profil
                        );


                        if (
                            db_stmt_execute(
                                $stmt
                            )
                        ) {


                            db_stmt_close(
                                $stmt
                            );


                            if (
                                !empty(
                                    $logo_lama
                                ) &&
                                file_exists(
                                    "../uploads/" .
                                    $logo_lama
                                )
                            ) {

                                unlink(
                                    "../uploads/" .
                                    $logo_lama
                                );
                            }


                            header(
                                "Location: profil.php?status=logo_sukses"
                            );

                            exit;


                        } else {


                            db_stmt_close(
                                $stmt
                            );


                            if (
                                file_exists(
                                    $target
                                )
                            ) {

                                unlink($target);
                            }


                            $error =
                                'Logo berhasil diupload tetapi gagal disimpan ke database.';
                        }


                    } else {


                        if (
                            file_exists(
                                $target
                            )
                        ) {

                            unlink($target);
                        }


                        $error =
                            'Query database gagal dibuat.';
                    }


                } else {

                    $error =
                        'Logo gagal dipindahkan ke folder upload.';
                }
            }
        }
    }
}

?>

<?php

/* =========================================================
   PENGATURAN TAMPILAN HALAMAN
   Bagian atas, menu, dan bagian bawah halaman diambil dari
   folder layouts/ supaya tidak ditulis berulang.
========================================================== */

$judul         = "Profil Sekolah | Admin";

$css           = "../assets/css/admin/profil.css";

$menu_aktif    = "profil";

$judul_halaman = "Profil Sekolah";

$subjudul      = "Kelola profil sekolah";

require_once "../layouts/admin/kepala.php";
require_once "../layouts/admin/sidebar.php";
require_once "../layouts/admin/topbar.php";
?>


<!-- =========================================================
     OVERLAY
========================================================= -->

<div
    class="overlay"
    id="overlay">
</div>


<!-- =========================================================
     SIDEBAR
     SAMA DENGAN DASHBOARD


<!-- =========================================================
     MAIN


        <div class="page-header">

            <h2>
                Profil Sekolah
            </h2>

            <p>
                Kelola identitas sekolah, data kepala sekolah,
                sejarah, visi, misi, tujuan, foto, dan logo sekolah.
            </p>

        </div>


        <!-- =================================================
             SUCCESS
        ================================================== -->

        <?php if ($success !== ''): ?>

            <div class="alert alert-success">

                <i class="fa-solid fa-circle-check"></i> <?= e($success) ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             ERROR
        ================================================== -->

        <?php if ($error !== ''): ?>

            <div class="alert alert-error">

                <i class="fa-solid fa-triangle-exclamation"></i> <?= e($error) ?>

            </div>

        <?php endif; ?>


        <div class="dashboard-grid">


            <!-- =================================================
                 KOLOM KIRI
            ================================================== -->

            <div>


                <div class="card">


                    <div class="card-header">

                        <h2>
                            Identitas Sekolah
                        </h2>

                        <p>
                            Lengkapi informasi dasar sekolah
                            yang akan ditampilkan pada website.
                        </p>

                    </div>


                    <form
                        method="POST"
                        action="">


                        <!-- NAMA SEKOLAH -->

                        <div class="form-group">

                            <label for="nama_sekolah">

                                Nama Sekolah

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                name="nama_sekolah"
                                id="nama_sekolah"
                                class="form-control"
                                value="<?= e($profil['nama_sekolah']) ?>"
                                placeholder="Nama sekolah"
                                required>

                        </div>


                        <!-- ALAMAT -->

                        <div class="form-group">

                            <label for="alamat">
                                Alamat Lengkap
                            </label>


                            <textarea
                                name="alamat"
                                id="alamat"
                                class="form-control"
                                placeholder="Alamat lengkap sekolah"><?= e($profil['alamat']) ?></textarea>

                        </div>


                        <!-- DESA + KECAMATAN -->

                        <div class="two-column">


                            <div class="form-group">

                                <label for="desa">
                                    Desa / Kelurahan
                                </label>


                                <input
                                    type="text"
                                    name="desa"
                                    id="desa"
                                    class="form-control"
                                    value="<?= e($profil['desa']) ?>"
                                    placeholder="Contoh: Kampung Beru">

                            </div>


                            <div class="form-group">

                                <label for="kecamatan">
                                    Kecamatan
                                </label>


                                <input
                                    type="text"
                                    name="kecamatan"
                                    id="kecamatan"
                                    class="form-control"
                                    value="<?= e($profil['kecamatan']) ?>"
                                    placeholder="Contoh: Polombangkeng Timur">

                            </div>


                        </div>


                        <!-- KABUPATEN + PROVINSI -->

                        <div class="two-column">


                            <div class="form-group">

                                <label for="kabupaten">
                                    Kabupaten
                                </label>


                                <input
                                    type="text"
                                    name="kabupaten"
                                    id="kabupaten"
                                    class="form-control"
                                    value="<?= e($profil['kabupaten']) ?>"
                                    placeholder="Contoh: Takalar">

                            </div>


                            <div class="form-group">

                                <label for="provinsi">
                                    Provinsi
                                </label>


                                <input
                                    type="text"
                                    name="provinsi"
                                    id="provinsi"
                                    class="form-control"
                                    value="<?= e($profil['provinsi']) ?>"
                                    placeholder="Contoh: Sulawesi Selatan">

                            </div>


                        </div>


                        <!-- =================================================
                             KEPALA SEKOLAH
                        ================================================== -->

                        <div class="section-divider">

                            <h2>
                                Kepala Sekolah
                            </h2>

                            <p>
                                Data kepala sekolah yang akan
                                ditampilkan pada halaman profil.
                            </p>

                        </div>


                        <div class="two-column">


                            <div class="form-group">

                                <label for="nama_kepala_sekolah">

                                    Nama Kepala Sekolah

                                    <span class="required">
                                        *
                                    </span>

                                </label>


                                <input
                                    type="text"
                                    name="nama_kepala_sekolah"
                                    id="nama_kepala_sekolah"
                                    class="form-control"
                                    value="<?= e($profil['nama_kepala_sekolah']) ?>"
                                    placeholder="Nama kepala sekolah"
                                    required>

                            </div>


                            <div class="form-group">

                                <label for="nip_kepala_sekolah">

                                    NIP Kepala Sekolah

                                </label>


                                <input
                                    type="text"
                                    name="nip_kepala_sekolah"
                                    id="nip_kepala_sekolah"
                                    class="form-control"
                                    value="<?= e($profil['nip_kepala_sekolah']) ?>"
                                    placeholder="NIP kepala sekolah">


                                <div class="form-help">

                                    Masukkan NIP tanpa tulisan
                                    "NIP:".

                                </div>

                            </div>


                        </div>


                        <!-- SEJARAH -->

                        <div class="form-group">

                            <label for="sejarah">
                                Sejarah Sekolah
                            </label>


                            <textarea
                                name="sejarah"
                                id="sejarah"
                                class="form-control"
                                placeholder="Tuliskan sejarah sekolah..."><?= e($profil['sejarah']) ?></textarea>

                        </div>


                        <!-- VISI -->

                        <div class="form-group">

                            <label for="visi">
                                Visi Sekolah
                            </label>


                            <textarea
                                name="visi"
                                id="visi"
                                class="form-control"
                                placeholder="Tuliskan visi sekolah..."><?= e($profil['visi']) ?></textarea>

                        </div>


                        <!-- MISI -->

                        <div class="form-group">

                            <label for="misi">
                                Misi Sekolah
                            </label>


                            <textarea
                                name="misi"
                                id="misi"
                                class="form-control"
                                placeholder="Tuliskan misi sekolah..."><?= e($profil['misi']) ?></textarea>


                            <div class="form-help">

                                Jika memiliki beberapa poin misi,
                                tuliskan setiap poin pada baris baru.

                            </div>

                        </div>


                        <!-- TUJUAN -->

                        <div class="form-group">

                            <label for="tujuan">
                                Tujuan Sekolah
                            </label>


                            <textarea
                                name="tujuan"
                                id="tujuan"
                                class="form-control"
                                placeholder="Tuliskan tujuan sekolah..."><?= e($profil['tujuan']) ?></textarea>

                        </div>


                        <!-- SIMPAN -->

                        <div class="form-actions">

                            <button
                                type="submit"
                                name="simpan_profil"
                                class="btn-save">

                                Simpan Profil Sekolah

                            </button>

                        </div>


                    </form>


                </div>


            </div>


            <!-- =================================================
                 KOLOM KANAN
            ================================================== -->

            <div>


                <!-- =================================================
                     FOTO KEPALA SEKOLAH
                ================================================== -->

                <div class="card">


                    <div class="card-header">

                        <h2>
                            Foto Kepala Sekolah
                        </h2>

                        <p>
                            Upload foto kepala sekolah
                            untuk halaman profil.
                        </p>

                    </div>


                    <div class="profile-photo-box">


                        <?php if (
                            !empty(
                                $profil[
                                    'foto_kepala_sekolah'
                                ]
                            )
                        ): ?>


                            <img
                                src="../uploads/<?= e($profil['foto_kepala_sekolah']) ?>"
                                alt="Foto Kepala Sekolah"
                                class="photo-preview">


                        <?php else: ?>


                            <div class="photo-placeholder">

                                <div class="photo-placeholder-icon">
                                    <i class="fa-solid fa-user"></i>
                                </div>

                                <span>
                                    Belum ada foto
                                </span>

                            </div>


                        <?php endif; ?>


                        <?php if (!empty($profil['id'])): ?>


                            <form
                                method="POST"
                                enctype="multipart/form-data">


                                <input
                                    type="file"
                                    name="foto_kepala_sekolah"
                                    class="upload-input"
                                    accept="image/jpeg,image/png,image/webp"
                                    required>


                                <div class="form-help">

                                    JPG, PNG, WEBP.
                                    Maksimal 5 MB.

                                </div>


                                <button
                                    type="submit"
                                    name="upload_foto_kepala"
                                    class="upload-btn">

                                    Upload Foto

                                </button>


                            </form>


                        <?php else: ?>


                            <div class="form-help">

                                Simpan profil terlebih dahulu
                                sebelum mengupload foto.

                            </div>


                        <?php endif; ?>


                    </div>


                </div>


                <!-- =================================================
                     LOGO SEKOLAH
                ================================================== -->

                <div class="card">


                    <div class="card-header">

                        <h2>
                            Logo Sekolah
                        </h2>

                        <p>
                            Logo sekolah untuk digunakan
                            pada website.
                        </p>

                    </div>


                    <div class="profile-photo-box">


                        <?php if (!empty($profil['logo'])): ?>


                            <img
                                src="../uploads/<?= e($profil['logo']) ?>"
                                alt="Logo Sekolah"
                                class="logo-preview">


                        <?php else: ?>


                            <div class="logo-placeholder">

                                <div>
                                    <i class="fa-solid fa-school"></i>
                                </div>

                                <span>
                                    Belum ada logo
                                </span>

                            </div>


                        <?php endif; ?>


                        <?php if (!empty($profil['id'])): ?>


                            <form
                                method="POST"
                                enctype="multipart/form-data">


                                <input
                                    type="file"
                                    name="logo"
                                    class="upload-input"
                                    accept="image/jpeg,image/png,image/webp"
                                    required>


                                <div class="form-help">

                                    JPG, PNG, WEBP.
                                    Maksimal 3 MB.

                                </div>


                                <button
                                    type="submit"
                                    name="upload_logo"
                                    class="upload-btn">

                                    <i class="fa-solid fa-upload"></i> Upload Logo

                                </button>


                            </form>


                        <?php else: ?>


                            <div class="form-help">

                                Simpan profil terlebih dahulu
                                sebelum mengupload logo.

                            </div>


                        <?php endif; ?>


                    </div>


                </div>


            </div>


        </div>


<?php require_once "../layouts/admin/kaki.php"; ?>


</body>

</html>