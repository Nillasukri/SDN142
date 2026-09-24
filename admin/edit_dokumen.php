<?php
session_start();

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



/* =========================================================
   DATA ADMIN
========================================================= */

$nama_admin = $_SESSION["admin_nama"] ?? "Administrator";
$username_admin = $_SESSION["admin_username"] ?? "admin";

/* =========================================================
   LOGO SEKOLAH
========================================================= */

$logo_sekolah = '';

$query_logo = db_query(
    $koneksi,
    "SELECT logo FROM profil ORDER BY id ASC LIMIT 1"
);

if ($query_logo) {

    $data_logo = db_fetch_assoc($query_logo);

    $logo_sekolah = $data_logo["logo"] ?? "";
}

/* =========================================================
   AMBIL ID DOKUMEN
========================================================= */

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    header("Location: dokumen.php?status=id_tidak_valid");
    exit;
}

/* =========================================================
   AMBIL DATA DOKUMEN
========================================================= */

$stmt = db_prepare(
    $koneksi,
    "SELECT id, judul, kategori, deskripsi, nama_file, tanggal
     FROM dokumen
     WHERE id = ?"
);

db_stmt_bind_param($stmt, "i", $id);
db_stmt_execute($stmt);

$result = db_stmt_get_result($stmt);
$dokumen = db_fetch_assoc($result);

db_stmt_close($stmt);

if (!$dokumen) {
    header("Location: dokumen.php?status=data_tidak_ditemukan");
    exit;
}

/* =========================================================
   DATA LAMA
========================================================= */

$judul_lama = $dokumen["judul"] ?? "";
$kategori_lama = $dokumen["kategori"] ?? "";
$deskripsi_lama = $dokumen["deskripsi"] ?? "";
$nama_file_lama = $dokumen["nama_file"] ?? "";
$tanggal_lama = $dokumen["tanggal"] ?? "";

/* =========================================================
   PROSES UPDATE
========================================================= */

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $judul = trim($_POST["judul"] ?? "");
    $kategori = trim($_POST["kategori"] ?? "");
    $deskripsi = trim($_POST["deskripsi"] ?? "");
    $tanggal = trim($_POST["tanggal"] ?? "");

    /* Jika validasi gagal, nilai POST tetap tampil */
    $judul_tampil = $judul;
    $kategori_tampil = $kategori;
    $deskripsi_tampil = $deskripsi;
    $tanggal_tampil = $tanggal;

    if ($judul === "") {
        $error = "Judul dokumen wajib diisi.";
    } elseif ($kategori === "") {
        $error = "Kategori dokumen wajib diisi.";
    } elseif ($tanggal === "") {
        $error = "Tanggal dokumen wajib diisi.";
    }

    /* =====================================================
       FILE BARU
    ===================================================== */

    $nama_file_baru = $nama_file_lama;
    $file_baru_diupload = false;
    $file_lama_yang_dihapus = "";

    if (
        $error === "" &&
        isset($_FILES["file"]) &&
        $_FILES["file"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES["file"]["error"] !== UPLOAD_ERR_OK) {

            $error = "File gagal diupload.";

        } else {

            $nama_asli = $_FILES["file"]["name"];
            $tmp_file = $_FILES["file"]["tmp_name"];
            $ukuran_file = $_FILES["file"]["size"];

            $ekstensi = strtolower(
                pathinfo($nama_asli, PATHINFO_EXTENSION)
            );

            $ekstensi_diizinkan = [
                "pdf",
                "doc",
                "docx",
                "xls",
                "xlsx",
                "ppt",
                "pptx",
                "jpg",
                "jpeg",
                "png",
                "webp",
                "zip",
                "rar"
            ];

            if (!in_array($ekstensi, $ekstensi_diizinkan, true)) {

                $error = "Format file tidak diizinkan.";

            } elseif ($ukuran_file > 10 * 1024 * 1024) {

                $error = "Ukuran file maksimal 10 MB.";

            } else {

                $folder_upload = "../uploads/dokumen/";

                if (!unggah_ada_folder($folder_upload)) {
                    unggah_mkdir($folder_upload, 0755, true);
                }

                $nama_file_baru =
                    uniqid("dok_", true) . "." . $ekstensi;

                $lokasi_file_baru =
                    $folder_upload . $nama_file_baru;

                if (
                    !unggah_simpan(
                        $tmp_file,
                        $lokasi_file_baru
                    )
                ) {

                    $error = "Gagal menyimpan file baru.";

                } else {

                    $file_baru_diupload = true;
                    $file_lama_yang_dihapus = $nama_file_lama;
                }
            }
        }
    }

    /* =====================================================
       UPDATE DATABASE
    ===================================================== */

    if ($error === "") {

        $stmt_update = db_prepare(
            $koneksi,
            "UPDATE dokumen
             SET judul = ?,
                 kategori = ?,
                 deskripsi = ?,
                 nama_file = ?,
                 tanggal = ?
             WHERE id = ?"
        );

        db_stmt_bind_param(
            $stmt_update,
            "sssssi",
            $judul,
            $kategori,
            $deskripsi,
            $nama_file_baru,
            $tanggal,
            $id
        );

        if (db_stmt_execute($stmt_update)) {

            db_stmt_close($stmt_update);

            /* =============================================
               HAPUS FILE LAMA SETELAH DATABASE BERHASIL
            ============================================= */

            if (
                $file_baru_diupload &&
                $file_lama_yang_dihapus !== ""
            ) {

                $file_lama =
                    "../uploads/dokumen/" .
                    basename($file_lama_yang_dihapus);

                if (unggah_ada($file_lama)) {
                    unggah_hapus($file_lama);
                }
            }

            header("Location: dokumen.php?status=edit_sukses");
            exit;

        } else {

            db_stmt_close($stmt_update);

            /* Jika update gagal, hapus file baru */
            if ($file_baru_diupload) {

                $file_baru =
                    "../uploads/dokumen/" .
                    basename($nama_file_baru);

                if (unggah_ada($file_baru)) {
                    unggah_hapus($file_baru);
                }
            }

            $error = "Gagal memperbarui dokumen.";
        }
    }

} else {

    $judul_tampil = $judul_lama;
    $kategori_tampil = $kategori_lama;
    $deskripsi_tampil = $deskripsi_lama;
    $tanggal_tampil = $tanggal_lama;
}

/* =========================================================
   FORMAT UKURAN FILE
========================================================= */

function ukuranFile($filename)
{
    $path =
        "../uploads/dokumen/" .
        basename($filename);

    if (!unggah_ada($path)) {
        return "-";
    }

    $size = unggah_ukuran($path);

    if ($size >= 1024 * 1024) {
        return number_format(
            $size / (1024 * 1024),
            2
        ) . " MB";
    }

    if ($size >= 1024) {
        return number_format(
            $size / 1024,
            2
        ) . " KB";
    }

    return $size . " B";
}

/* =========================================================
   ICON FILE
========================================================= */

function iconFile($filename)
{
    $ext = strtolower(
        pathinfo($filename, PATHINFO_EXTENSION)
    );

    switch ($ext) {

        case "pdf":
            return "fa-file-pdf";

        case "doc":
        case "docx":
            return "fa-file-word";

        case "xls":
        case "xlsx":
            return "fa-file-excel";

        case "ppt":
        case "pptx":
            return "fa-file-powerpoint";

        case "zip":
        case "rar":
            return "fa-file-zipper";

        case "jpg":
        case "jpeg":
        case "png":
        case "webp":
            return "fa-file-image";

        default:
            return "fa-file-lines";
    }
}

?>
<?php

/* =========================================================
   PENGATURAN TAMPILAN HALAMAN
   Bagian atas, menu, dan bagian bawah halaman diambil dari
   folder layouts/ supaya tidak ditulis berulang.
========================================================== */

$judul         = "Edit Dokumen - Admin";

$css           = "../assets/css/admin/edit_dokumen.css";

$menu_aktif    = "dokumen";

$judul_halaman = "Edit Dokumen";

$subjudul      = "Kelola dan perbarui dokumen sekolah";

require_once "../layouts/admin/kepala.php";
require_once "../layouts/admin/sidebar.php";
require_once "../layouts/admin/topbar.php";
?>

    <!-- =====================================================
         SIDEBAR


    <!-- =====================================================
         MAIN

            <div class="content-card">

                <div class="content-header">

                    <div>

                        <h2>
                            Edit Dokumen
                        </h2>

                        <p>
                            Perbarui informasi dokumen yang sudah tersimpan.
                        </p>

                    </div>

                </div>


                <?php if ($error !== ""): ?>

                    <div class="alert-error">

                        <?= e($error) ?>

                    </div>

                <?php endif; ?>


                <form
                    method="POST"
                    enctype="multipart/form-data"
                    id="formDokumen"
                >

                    <!-- =====================================
                         JUDUL
                    ====================================== -->

                    <div class="form-group">

                        <label for="judul">

                            Judul Dokumen

                            <span>
                                *
                            </span>

                        </label>


                        <input
                            type="text"
                            name="judul"
                            id="judul"
                            class="form-control"
                            value="<?= e($judul_tampil) ?>"
                            placeholder="Masukkan judul dokumen"
                            required
                        >

                    </div>


                    <!-- =====================================
                         KATEGORI
                    ====================================== -->

                    <div class="form-group">

                        <label for="kategori">

                            Kategori

                            <span>
                                *
                            </span>

                        </label>


                        <select
                            name="kategori"
                            id="kategori"
                            class="form-control"
                            required
                        >

                            <option value="">
                                -- Pilih Kategori --
                            </option>


                            <option
                                value="Administrasi"
                                <?= $kategori_tampil === "Administrasi"
                                    ? "selected"
                                    : "" ?>
                            >
                                Administrasi
                            </option>


                            <option
                                value="Akademik"
                                <?= $kategori_tampil === "Akademik"
                                    ? "selected"
                                    : "" ?>
                            >
                                Akademik
                            </option>


                            <option
                                value="Kurikulum"
                                <?= $kategori_tampil === "Kurikulum"
                                    ? "selected"
                                    : "" ?>
                            >
                                Kurikulum
                            </option>


                            <option
                                value="Kepegawaian"
                                <?= $kategori_tampil === "Kepegawaian"
                                    ? "selected"
                                    : "" ?>
                            >
                                Kepegawaian
                            </option>


                            <option
                                value="Kesiswaan"
                                <?= $kategori_tampil === "Kesiswaan"
                                    ? "selected"
                                    : "" ?>
                            >
                                Kesiswaan
                            </option>


                            <option
                                value="Surat"
                                <?= $kategori_tampil === "Surat"
                                    ? "selected"
                                    : "" ?>
                            >
                                Surat
                            </option>


                            <option
                                value="Lainnya"
                                <?= $kategori_tampil === "Lainnya"
                                    ? "selected"
                                    : "" ?>
                            >
                                Lainnya
                            </option>

                        </select>

                    </div>


                    <!-- =====================================
                         DESKRIPSI
                    ====================================== -->

                    <div class="form-group">

                        <label for="deskripsi">
                            Deskripsi
                        </label>


                        <textarea
                            name="deskripsi"
                            id="deskripsi"
                            class="form-control"
                            placeholder="Masukkan deskripsi dokumen"
                        ><?= e($deskripsi_tampil) ?></textarea>

                    </div>


                    <!-- =====================================
                         TANGGAL
                    ====================================== -->

                    <div class="form-group">

                        <label for="tanggal">

                            Tanggal

                            <span>
                                *
                            </span>

                        </label>


                        <input
                            type="date"
                            name="tanggal"
                            id="tanggal"
                            class="form-control"
                            value="<?= e($tanggal_tampil) ?>"
                            required
                        >

                    </div>


                    <!-- =====================================
                         FILE LAMA
                    ====================================== -->

                    <div class="form-group">

                        <label>
                            File Saat Ini
                        </label>


                        <div class="file-lama">

                            <div class="file-icon">

                                <i class="fa-solid <?= e(iconFile($nama_file_lama)) ?>"></i>

                            </div>


                            <div class="file-info">

                                <strong>
                                    <?= e($nama_file_lama) ?>
                                </strong>


                                <span>

                                    Ukuran:

                                    <?= e(
                                        ukuranFile(
                                            $nama_file_lama
                                        )
                                    ) ?>

                                </span>

                            </div>

                        </div>

                    </div>


                    <!-- =====================================
                         FILE BARU
                    ====================================== -->

                    <div class="form-group">

                        <label>
                            Ganti File
                        </label>


                        <div
                            class="upload-area"
                            id="uploadArea"
                        >

                            <div class="upload-icon">
                                <i class="fa-solid fa-folder-open"></i>
                            </div>


                            <strong>
                                Pilih file baru
                            </strong>


                            <p>
                                Klik atau tarik file ke area ini
                            </p>


                            <p>
                                Maksimal 10 MB
                            </p>


                            <input
                                type="file"
                                name="file"
                                id="file"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.zip,.rar"
                            >


                            <div
                                class="file-name"
                                id="fileName"
                            >
                                Tidak ada file baru dipilih
                            </div>

                        </div>

                    </div>


                    <!-- =====================================
                         ACTION
                    ====================================== -->

                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="fa-solid fa-floppy-disk"></i>

                            Simpan Perubahan

                        </button>


                        <a
                            href="dokumen.php"
                            class="btn btn-secondary"
                        >

                            <i class="fa-solid fa-arrow-left"></i>

                            Kembali

                        </a>

                    </div>

                </form>

            </div>

<!-- =====================================================
     JAVASCRIPT KHUSUS HALAMAN INI
===================================================== -->

<script>

        /* =================================================
           FILE INPUT
        ================================================== */

        const fileInput =
            document.getElementById("file");

        const fileName =
            document.getElementById("fileName");

        const uploadArea =
            document.getElementById("uploadArea");


        if (fileInput) {

            fileInput.addEventListener(
                "change",
                function () {

                    if (this.files.length > 0) {

                        fileName.textContent =
                            this.files[0].name;

                    } else {

                        fileName.textContent =
                            "Tidak ada file baru dipilih";

                    }

                }
            );

        }


        /* =================================================
           DRAG & DROP
        ================================================== */

        if (uploadArea) {

            [
                "dragenter",
                "dragover"
            ].forEach(function (eventName) {

                uploadArea.addEventListener(
                    eventName,
                    function (event) {

                        event.preventDefault();

                        uploadArea.classList.add(
                            "dragover"
                        );

                    }
                );

            });


            [
                "dragleave",
                "drop"
            ].forEach(function (eventName) {

                uploadArea.addEventListener(
                    eventName,
                    function (event) {

                        event.preventDefault();

                        uploadArea.classList.remove(
                            "dragover"
                        );

                    }
                );

            });


            uploadArea.addEventListener(
                "drop",
                function (event) {

                    const files =
                        event.dataTransfer.files;

                    if (files.length > 0) {

                        fileInput.files =
                            files;

                        fileName.textContent =
                            files[0].name;

                    }

                }
            );

        }


        /* =================================================
           VALIDASI FILE CLIENT
        ================================================== */

        if (fileInput) {

            fileInput.addEventListener(
                "change",
                function () {

                    if (
                        this.files.length === 0
                    ) {
                        return;
                    }


                    const file =
                        this.files[0];


                    const maxSize =
                        10 * 1024 * 1024;


                    if (file.size > maxSize) {

                        alert(
                            "Ukuran file maksimal 10 MB."
                        );


                        this.value = "";


                        fileName.textContent =
                            "Tidak ada file baru dipilih";

                    }

                }
            );

        }


</script>

<?php require_once "../layouts/admin/kaki.php"; ?>

</body>

</html>