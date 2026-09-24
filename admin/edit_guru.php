<?php

session_start();



/* =========================
   CEK LOGIN ADMIN
========================= */
/* -------------------------------------------------------------
   WAJIB LOGIN
   Di mode lokal (XAMPP) cukup session PHP seperti biasa.
   Di mode online (Vercel) penanda login juga dibaca dari cookie
   `sesi_admin`, supaya admin tidak ter-logout sendiri.
   Penjelasan lengkap ada di config/auth_admin.php.
------------------------------------------------------------- */
require_once "../config/koneksi.php";
require_once "../config/auth_admin.php";

auth_paksa_login($koneksi, "login.php");


/* =========================
/* =========================
   DATA ADMIN
========================= */
$admin_nama = $_SESSION['admin_nama'] ?? 'Administrator';
$admin_username = $_SESSION['admin_username'] ?? 'admin';

/* =========================
   CEK ID GURU
========================= */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: guru.php?status=id_tidak_valid");
    exit;
}

$id = (int) $_GET['id'];

/* =========================
   AMBIL DATA GURU
========================= */
$sql = "SELECT id, nama, jabatan, foto FROM guru WHERE id = ?";

$stmt = db_prepare($koneksi, $sql);

if (!$stmt) {
    die("Query database gagal diproses.");
}

db_stmt_bind_param($stmt, "i", $id);
db_stmt_execute($stmt);

$result = db_stmt_get_result($stmt);
$guru = db_fetch_assoc($result);

db_stmt_close($stmt);

/* =========================
   CEK DATA
========================= */
if (!$guru) {
    header("Location: guru.php?status=data_tidak_ditemukan");
    exit;
}

/* =========================
   DATA LAMA
========================= */
$nama_lama = $guru['nama'];
$jabatan_lama = $guru['jabatan'];
$foto_lama = $guru['foto'];

/* =========================
   VARIABEL ERROR
========================= */
$error = '';

/* =========================
   PROSES UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
     * PENTING:
     * Gunakan data POST jika tersedia.
     * Kalau kosong karena alasan tertentu,
     * data lama tetap dipertahankan.
     */
    $nama = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');

    /* =========================
       VALIDASI
    ========================= */

    if ($nama === '') {
        $error = "Nama guru/staf wajib diisi.";
    } elseif ($jabatan === '') {
        $error = "Jabatan wajib diisi.";
    }

    /* =========================
       DATA FOTO
    ========================= */

    $foto_baru = $foto_lama;
    $file_foto_baru = '';
    $foto_diubah = false;

    /* =========================
       PROSES UPLOAD FOTO BARU
    ========================= */

    if (
        $error === '' &&
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $file = $_FILES['foto'];

        if ($file['error'] !== UPLOAD_ERR_OK) {

            $error = "Foto gagal diupload. Silakan coba lagi.";

        } else {

            $allowed_ext = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];

            $allowed_mime = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            $nama_asli = $file['name'];
            $tmp_file = $file['tmp_name'];
            $ukuran = $file['size'];

            $ext = strtolower(
                pathinfo($nama_asli, PATHINFO_EXTENSION)
            );

            /* =========================
               CEK EKSTENSI
            ========================= */

            if (!in_array($ext, $allowed_ext)) {

                $error =
                    "Format foto harus JPG, JPEG, PNG, atau WEBP.";

            }

            /* =========================
               CEK UKURAN
            ========================= */

            elseif ($ukuran > 2 * 1024 * 1024) {

                $error =
                    "Ukuran foto maksimal 2 MB.";

            }

            /* =========================
               CEK MIME
            ========================= */

            else {

                $finfo = finfo_open(FILEINFO_MIME_TYPE);

                $mime = finfo_file(
                    $finfo,
                    $tmp_file
                );

                finfo_close($finfo);

                if (!in_array($mime, $allowed_mime)) {

                    $error =
                        "File yang diupload bukan gambar yang valid.";
                }
            }

            /* =========================
               SIMPAN FOTO BARU
            ========================= */

            if ($error === '') {

                $folder_upload = "../uploads/guru/";

                if (!unggah_ada_folder($folder_upload)) {

                    unggah_mkdir(
                        $folder_upload,
                        0755,
                        true
                    );
                }

                $file_foto_baru =
                    'guru_' .
                    time() .
                    '_' .
                    bin2hex(random_bytes(5)) .
                    '.' .
                    $ext;

                $tujuan_file =
                    $folder_upload .
                    $file_foto_baru;

                if (
                    unggah_simpan(
                        $tmp_file,
                        $tujuan_file
                    )
                ) {

                    $foto_baru = $file_foto_baru;
                    $foto_diubah = true;

                } else {

                    $error =
                        "Foto baru gagal disimpan ke server.";

                    $file_foto_baru = '';
                }
            }
        }
    }

    /* =========================
       UPDATE DATABASE
    ========================= */

    if ($error === '') {

        /*
         * Selalu update nama, jabatan,
         * dan foto.
         *
         * Jika tidak ada foto baru,
         * $foto_baru tetap berisi foto lama.
         */
        $sql_update = "
            UPDATE guru
            SET
                nama = ?,
                jabatan = ?,
                foto = ?
            WHERE id = ?
        ";

        $stmt_update = db_prepare(
            $koneksi,
            $sql_update
        );

        if ($stmt_update) {

            db_stmt_bind_param(
                $stmt_update,
                "sssi",
                $nama,
                $jabatan,
                $foto_baru,
                $id
            );

            if (
                db_stmt_execute(
                    $stmt_update
                )
            ) {

                db_stmt_close(
                    $stmt_update
                );

                /* =========================
                   HAPUS FOTO LAMA
                   HANYA JIKA FOTO DIGANTI
                ========================= */

                if (
                    $foto_diubah &&
                    !empty($foto_lama)
                ) {

                    $file_lama =
                        "../uploads/guru/" .
                        $foto_lama;

                    if (
                        unggah_ada(
                            $file_lama
                        )
                    ) {

                        unggah_hapus($file_lama);
                    }
                }

                /* =========================
                   SELESAI
                ========================= */

                header(
                    "Location: guru.php?status=edit_sukses"
                );

                exit;

            } else {

                db_stmt_close(
                    $stmt_update
                );

                /*
                 * Jika database gagal,
                 * hapus foto baru agar tidak
                 * menjadi file sampah.
                 */
                if (
                    $file_foto_baru !== ''
                ) {

                    $file_gagal =
                        "../uploads/guru/" .
                        $file_foto_baru;

                    if (
                        unggah_ada(
                            $file_gagal
                        )
                    ) {

                        unggah_hapus($file_gagal);
                    }
                }

                $error =
                    "Data guru gagal diperbarui.";
            }

        } else {

            /*
             * Jika prepare gagal,
             * hapus foto baru.
             */
            if (
                $file_foto_baru !== ''
            ) {

                $file_gagal =
                    "../uploads/guru/" .
                    $file_foto_baru;

                if (
                    unggah_ada(
                        $file_gagal
                    )
                ) {

                    unggah_hapus($file_gagal);
                }
            }

            $error =
                "Query database tidak dapat diproses.";
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

$judul         = "Edit Guru & Staf | Admin Sekolah";

$css           = "../assets/css/admin/edit_guru.css";

$menu_aktif    = "guru";

$judul_halaman = "Edit Guru & Staf";

$subjudul      = "Kelola data tenaga pendidik dan kependidikan";

require_once "../layouts/admin/kepala.php";
require_once "../layouts/admin/sidebar.php";
require_once "../layouts/admin/topbar.php";
?>

    <!-- =========================
         SIDEBAR


    <div
        class="overlay"
        id="overlay"
    ></div>


    <!-- =========================
         MAIN

            <!-- BREADCRUMB -->

            <div class="breadcrumb">

                <a href="guru.php">
                    Guru & Staf
                </a>

                <span>
                    ›
                </span>

                <span>
                    Edit Data
                </span>

            </div>


            <!-- PAGE HEADER -->

            <div class="page-header">

                <div>

                    <h1>
                        Edit Guru & Staf
                    </h1>

                    <p>
                        Perbarui data guru atau tenaga kependidikan
                        yang sudah tersimpan.
                    </p>

                </div>


                <a
                    href="guru.php"
                    class="back-btn"
                >
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>

            </div>


            <!-- FORM CARD -->

            <div class="form-card">

                <div class="form-title">

                    <div class="form-title-icon">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>

                    <div>

                        <h2>
                            Form Edit Data Guru & Staf
                        </h2>

                        <p>
                            Periksa kembali data sebelum menyimpan perubahan.
                        </p>

                    </div>

                </div>


                <?php if ($error !== ''): ?>

                    <div class="alert alert-error">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?= e($error) ?>
                    </div>

                <?php endif; ?>


                <form
                    action=""
                    method="POST"
                    enctype="multipart/form-data"
                >

                    <div class="form-grid">


                        <!-- NAMA -->

                        <div class="form-group">

                            <label
                                for="nama"
                                class="form-label"
                            >

                                Nama Lengkap

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                name="nama"
                                id="nama"
                                class="form-control"
                                placeholder="Contoh: Andi Ahmad, S.Pd."
                                value="<?= e(
                                    $_POST['nama']
                                    ?? $nama_lama
                                ) ?>"
                                required
                                autocomplete="off"
                            >

                            <span class="form-help">
                                Nama lama tetap ditampilkan dan bisa langsung diedit.
                            </span>

                        </div>


                        <!-- JABATAN -->

                        <div class="form-group">

                            <label
                                for="jabatan"
                                class="form-label"
                            >

                                Jabatan

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                name="jabatan"
                                id="jabatan"
                                class="form-control"
                                placeholder="Contoh: Guru Kelas"
                                value="<?= e(
                                    $_POST['jabatan']
                                    ?? $jabatan_lama
                                ) ?>"
                                required
                                autocomplete="off"
                            >

                            <span class="form-help">
                                Jabatan lama tetap dipertahankan jika tidak diubah.
                            </span>

                        </div>


                        <!-- FOTO -->

                        <div class="form-group full">

                            <label class="form-label">

                                Foto Guru / Staf

                                <span
                                    style="font-weight:normal;color:#8d9096;"
                                >
                                    (Opsional)
                                </span>

                            </label>


                            <!-- FOTO LAMA -->

                            <div class="current-photo">

                                <span class="current-photo-title">
                                    Foto saat ini
                                </span>

                                <?php if (!empty($foto_lama)): ?>

                                    <img
                                        src="../uploads/guru/<?= e($foto_lama) ?>"
                                        alt="Foto <?= e($nama_lama) ?>"
                                    >

                                <?php else: ?>

                                    <div class="no-photo">
                                        Belum ada foto guru
                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- UPLOAD FOTO BARU -->

                            <label
                                for="foto"
                                class="upload-box"
                                id="uploadBox"
                            >

                                <div class="upload-icon">
                                    <i class="fa-solid fa-camera"></i>
                                </div>

                                <strong>
                                    Klik untuk mengganti foto
                                </strong>

                                <span>
                                    JPG, JPEG, PNG atau WEBP • Maksimal 2 MB
                                </span>

                            </label>


                            <input
                                type="file"
                                name="foto"
                                id="foto"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            >


                            <div
                                class="preview-wrapper"
                                id="previewWrapper"
                            >

                                <img
                                    src=""
                                    id="previewImage"
                                    alt="Preview foto baru"
                                >

                                <span
                                    class="preview-name"
                                    id="previewName"
                                ></span>

                            </div>


                            <span class="form-help">
                                Jika tidak memilih foto baru, foto lama akan tetap digunakan.
                            </span>

                        </div>


                    </div>


                    <!-- ACTION -->

                    <div class="form-actions">

                        <a
                            href="guru.php"
                            class="btn btn-cancel"
                        >
                            Batal
                        </a>


                        <button
                            type="submit"
                            class="btn btn-save"
                        >
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                        </button>

                    </div>

                </form>

            </div>


            <!-- INFO -->

            <div class="info-box">

                <div class="info-icon">
                    <i class="fa-solid fa-lightbulb"></i>
                </div>

                <div>

                    <strong>
                        Informasi
                    </strong>

                    <p>
                        Nama dan jabatan yang sudah tersimpan akan otomatis
                        muncul pada form edit. Jika foto baru tidak dipilih,
                        sistem akan mempertahankan foto lama.
                    </p>

                </div>

            </div>


<!-- =====================================================
     JAVASCRIPT KHUSUS HALAMAN INI
===================================================== -->

<script>

        /* =========================
           PREVIEW FOTO BARU
        ========================= */

        const fotoInput =
            document.getElementById('foto');

        const previewWrapper =
            document.getElementById('previewWrapper');

        const previewImage =
            document.getElementById('previewImage');

        const previewName =
            document.getElementById('previewName');


        if (fotoInput) {

            fotoInput?.addEventListener(
                'change',
                function () {

                    const file =
                        this.files[0];


                    if (!file) {

                        previewWrapper.classList.remove(
                            'show'
                        );

                        previewImage.src = '';

                        previewName.textContent = '';

                        return;
                    }


                    /* =========================
                       VALIDASI UKURAN
                    ========================= */

                    if (
                        file.size >
                        2 * 1024 * 1024
                    ) {

                        alert(
                            'Ukuran foto maksimal 2 MB.'
                        );

                        this.value = '';

                        previewWrapper.classList.remove(
                            'show'
                        );

                        return;
                    }


                    /* =========================
                       VALIDASI TIPE
                    ========================= */

                    const allowedTypes = [
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ];


                    if (
                        !allowedTypes.includes(
                            file.type
                        )
                    ) {

                        alert(
                            'Format foto harus JPG, JPEG, PNG, atau WEBP.'
                        );

                        this.value = '';

                        previewWrapper.classList.remove(
                            'show'
                        );

                        return;
                    }


                    /* =========================
                       PREVIEW
                    ========================= */

                    const reader =
                        new FileReader();


                    reader.onload =
                        function (event) {

                            previewImage.src =
                                event.target.result;

                            previewName.textContent =
                                file.name +
                                ' • ' +
                                (
                                    file.size / 1024
                                ).toFixed(0) +
                                ' KB';

                            previewWrapper.classList.add(
                                'show'
                            );

                        };


                    reader.readAsDataURL(file);

                }
            );

        }

</script>

<?php require_once "../layouts/admin/kaki.php"; ?>
