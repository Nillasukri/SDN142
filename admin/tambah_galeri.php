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



$nama_admin = $_SESSION['admin_nama'] ?? 'Administrator';
$username_admin = $_SESSION['admin_username'] ?? 'admin';

$error = '';

/* =========================
   PROSES TAMBAH GALERI
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $judul = trim($_POST['judul'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');

    /* Validasi judul */
    if ($judul === '') {
        $error = 'Judul foto wajib diisi.';
    }

    /* Validasi file */
    if ($error === '' && (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE)) {
        $error = 'Silakan pilih foto terlebih dahulu.';
    }

    if ($error === '' && isset($_FILES['foto'])) {

        $file = $_FILES['foto'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Terjadi kesalahan saat mengupload foto.';
        }

        /* Maksimal 5 MB */
        if ($error === '' && $file['size'] > 5 * 1024 * 1024) {
            $error = 'Ukuran foto terlalu besar. Maksimal 5 MB.';
        }

        /* Cek MIME */
        if ($error === '') {

            $allowed_mime = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif'
            ];

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed_mime, true)) {
                $error = 'Format foto tidak diperbolehkan. Gunakan JPG, PNG, WEBP, atau GIF.';
            }
        }

        /* Cek gambar benar-benar valid */
        if ($error === '') {
            if (@getimagesize($file['tmp_name']) === false) {
                $error = 'File yang dipilih bukan gambar yang valid.';
            }
        }

        /* =========================
           SIMPAN FILE
        ========================= */
        if ($error === '') {

            $folder = "../uploads/galeri/";

            if (!unggah_ada_folder($folder)) {
                unggah_mkdir($folder, 0777, true);
            }

            $extension_map = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif'
            ];

            $extension = $extension_map[$mime] ?? 'jpg';

            $nama_file = 'galeri_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

            $target = $folder . $nama_file;

            if (!unggah_simpan($file['tmp_name'], $target)) {
                $error = 'Foto gagal disimpan ke server.';
            } else {

                /* =========================
                   SIMPAN DATABASE
                ========================= */
                $stmt = db_prepare(
                    $koneksi,
                    "INSERT INTO galeri (judul, foto, keterangan)
                     VALUES (?, ?, ?)"
                );

                if ($stmt) {

                    db_stmt_bind_param(
                        $stmt,
                        "sss",
                        $judul,
                        $nama_file,
                        $keterangan
                    );

                    if (db_stmt_execute($stmt)) {

                        db_stmt_close($stmt);

                        header("Location: galeri.php?status=tambah_sukses");
                        exit;

                    } else {

                        db_stmt_close($stmt);

                        /* Hapus file jika database gagal */
                        if (unggah_ada($target)) {
                            unggah_hapus($target);
                        }

                        $error = 'Data foto gagal disimpan ke database.';
                    }

                } else {

                    if (unggah_ada($target)) {
                        unggah_hapus($target);
                    }

                    $error = 'Terjadi kesalahan pada database.';
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

$judul         = "Tambah Foto Galeri | Admin Sekolah";

$css           = "../assets/css/admin/tambah_galeri.css";

$menu_aktif    = "galeri";

$judul_halaman = "Admin / Galeri / Tambah Foto";

$subjudul      = "";

require_once "../layouts/admin/kepala.php";
require_once "../layouts/admin/sidebar.php";
require_once "../layouts/admin/topbar.php";
?>

<!-- =========================
     SIDEBAR

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- =========================
     MAIN

        <div class="page-header">

            <div>
                <h1>Tambah Foto Galeri</h1>

                <p>
                    Tambahkan foto kegiatan atau dokumentasi sekolah
                    ke dalam galeri website.
                </p>
            </div>

            <a href="galeri.php" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Galeri
            </a>

        </div>

        <div class="form-card">

            <div class="form-section-title">
                Informasi Foto
            </div>

            <div class="form-section-desc">
                Isi data foto dengan lengkap. Foto yang diupload akan
                langsung tampil pada halaman galeri setelah berhasil disimpan.
            </div>

            <?php if ($error !== ''): ?>

                <div class="alert-error">
                    <span class="alert-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>

                    <div>
                        <?= e($error); ?>
                    </div>
                </div>

            <?php endif; ?>

            <form
                method="POST"
                enctype="multipart/form-data"
                id="galeriForm">

                <!-- JUDUL -->
                <div class="form-group">

                    <label for="judul">
                        Judul Foto <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="judul"
                        id="judul"
                        class="form-control"
                        maxlength="200"
                        placeholder="Contoh: Upacara Bendera Hari Senin"
                        value="<?= e($_POST['judul'] ?? ''); ?>"
                        required>

                    <div class="form-help">
                        Gunakan judul yang singkat dan mudah dipahami.
                    </div>

                </div>

                <!-- KETERANGAN -->
                <div class="form-group">

                    <label for="keterangan">
                        Keterangan
                    </label>

                    <textarea
                        name="keterangan"
                        id="keterangan"
                        class="form-control"
                        placeholder="Tuliskan keterangan atau deskripsi singkat mengenai foto..."><?= e($_POST['keterangan'] ?? ''); ?></textarea>

                    <div class="form-help">
                        Keterangan bersifat opsional.
                    </div>

                </div>

                <!-- FOTO -->
                <div class="form-group">

                    <label>
                        Foto <span class="required">*</span>
                    </label>

                    <label
                        for="foto"
                        class="upload-area"
                        id="uploadArea">

                        <div class="upload-icon">
                            <i class="fa-solid fa-camera"></i>
                        </div>

                        <h3>
                            Klik untuk memilih foto
                        </h3>

                        <p>
                            Atau seret foto ke area ini
                            <br>
                            JPG, PNG, WEBP, atau GIF — maksimal 5 MB
                        </p>

                        <input
                            type="file"
                            name="foto"
                            id="foto"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            required>

                    </label>

                    <!-- PREVIEW -->
                    <div class="preview-box" id="previewBox">

                        <img
                            src=""
                            alt="Preview foto"
                            class="preview-image"
                            id="previewImage">

                        <div class="preview-info">

                            <strong id="previewName">
                                -
                            </strong>

                            <span id="previewSize">
                                -
                            </span>

                        </div>

                    </div>

                </div>

                <!-- ACTION -->
                <div class="form-actions">

                    <a
                        href="galeri.php"
                        class="btn btn-cancel">
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Foto
                    </button>

                </div>

            </form>

        </div>

<!-- =====================================================
     JAVASCRIPT KHUSUS HALAMAN INI
===================================================== -->

<script>

    /* =========================
       UPLOAD PREVIEW
    ========================= */

    const fotoInput = document.getElementById('foto');
    const previewBox = document.getElementById('previewBox');
    const previewImage = document.getElementById('previewImage');
    const previewName = document.getElementById('previewName');
    const previewSize = document.getElementById('previewSize');
    const uploadArea = document.getElementById('uploadArea');

    function formatFileSize(bytes) {

        if (bytes < 1024) {
            return bytes + ' B';
        }

        if (bytes < 1024 * 1024) {
            return (bytes / 1024).toFixed(1) + ' KB';
        }

        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function tampilkanPreview(file) {

        if (!file) {
            previewBox.classList.remove('show');
            return;
        }

        if (!file.type.startsWith('image/')) {
            alert('File yang dipilih harus berupa gambar.');
            fotoInput.value = '';
            previewBox.classList.remove('show');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran foto terlalu besar. Maksimal 5 MB.');
            fotoInput.value = '';
            previewBox.classList.remove('show');
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {

            previewImage.src = event.target.result;

            previewName.textContent = file.name;

            previewSize.textContent =
                formatFileSize(file.size);

            previewBox.classList.add('show');

        };

        reader.readAsDataURL(file);
    }

    fotoInput?.addEventListener('change', function () {

        if (this.files && this.files.length > 0) {
            tampilkanPreview(this.files[0]);
        }

    });

    /* =========================
       DRAG & DROP
    ========================= */

    uploadArea?.addEventListener('dragover', function (event) {

        event.preventDefault();

        uploadArea.classList.add('dragover');

    });

    uploadArea?.addEventListener('dragleave', function () {

        uploadArea.classList.remove('dragover');

    });

    uploadArea?.addEventListener('drop', function (event) {

        event.preventDefault();

        uploadArea.classList.remove('dragover');

        const files = event.dataTransfer.files;

        if (files.length > 0) {

            try {

                const dataTransfer = new DataTransfer();

                dataTransfer.items.add(files[0]);

                fotoInput.files = dataTransfer.files;

                tampilkanPreview(files[0]);

            } catch (error) {

                console.log('Drag & drop tidak didukung browser.');

            }

        }

    });

    /* =========================
       SUBMIT PROTECTION
    ========================= */

    const galeriForm = document.getElementById('galeriForm');

    galeriForm?.addEventListener('submit', function () {

        const submitButton = this.querySelector(
            'button[type="submit"]'
        );

        if (submitButton) {

            submitButton.disabled = true;

            submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        }

    });

</script>

<?php require_once "../layouts/admin/kaki.php"; ?>
