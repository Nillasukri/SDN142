<?php 
 
 
 
/* ===================================================== 
   SESSION 
===================================================== */ 
 
session_start(); 
 
if (!isset($_SESSION["admin_id"])) { 
    header("Location: login.php"); 
    exit; 
} 
 
 
/* ===================================================== 
   KONEKSI DATABASE 
===================================================== */ 
 
require_once "../config/koneksi.php"; 
 
 
/* ===================================================== 
/* ===================================================== 
   DATA ADMIN 
===================================================== */ 
 
$nama_admin = 
    $_SESSION["admin_nama"] 
    ?? "Administrator"; 
 
$username_admin = 
    $_SESSION["admin_username"] 
    ?? "admin"; 
 
 
/* =========================================================
   LOGO SEKOLAH
========================================================= */

$logo_sekolah = '';

$query_logo = db_query(
    $koneksi,
    "SELECT logo FROM profil ORDER BY id ASC LIMIT 1"
);

if ($query_logo && db_num_rows($query_logo) > 0) {

    $data_logo = db_fetch_assoc($query_logo);

    $logo_sekolah = $data_logo['logo'] ?? '';
}


/* ===================================================== 
   CEK ID GALERI 
===================================================== */ 
 
$id = 
    isset($_GET["id"]) 
    ? (int)$_GET["id"] 
    : 0; 
 
 
if ($id <= 0) { 
 
    header( 
        "Location: galeri.php?status=id_tidak_valid" 
    ); 
 
    exit; 
} 
 
 
/* ===================================================== 
   AMBIL DATA GALERI 
===================================================== */ 
 
$stmt = db_prepare( 
    $koneksi, 
    "SELECT * FROM galeri WHERE id = ? LIMIT 1" 
); 
 
 
if (!$stmt) { 
 
    die( 
        "Gagal menyiapkan query galeri." 
    ); 
} 
 
 
db_stmt_bind_param( 
    $stmt, 
    "i", 
    $id 
); 
 
 
db_stmt_execute($stmt); 
 
 
$result = 
    db_stmt_get_result($stmt); 
 
 
$galeri = 
    db_fetch_assoc($result); 
 
 
db_stmt_close($stmt); 
 
 
if (!$galeri) { 
 
    header( 
        "Location: galeri.php?status=data_tidak_ditemukan" 
    ); 
 
    exit; 
} 
 
 
/* ===================================================== 
   DATA LAMA 
===================================================== */ 
 
$judulLama = 
    $galeri["judul"] 
    ?? ""; 
 
$keteranganLama = 
    $galeri["keterangan"] 
    ?? ""; 
 
$fotoLama = 
    $galeri["foto"] 
    ?? ""; 
 
 
/* ===================================================== 
   PESAN 
===================================================== */ 
 
$pesan = ""; 
 
$tipePesan = ""; 
 
 
/* ===================================================== 
   PROSES UPDATE 
===================================================== */ 
 
if ( 
    isset( 
        $_POST["update_galeri"] 
    ) 
) { 
 
    $judul = 
        trim( 
            $_POST["judul"] 
            ?? "" 
        ); 
 
 
    $keterangan = 
        trim( 
            $_POST["keterangan"] 
            ?? "" 
        ); 
 
 
    /* ================================================= 
       VALIDASI JUDUL 
    ================================================= */ 
 
    if ($judul === "") { 
 
        $pesan = 
            "Judul foto wajib diisi."; 
 
        $tipePesan = 
            "error"; 
 
    } else { 
 
        /* ============================================= 
           FOTO BARU 
        ============================================= */ 
 
        $fotoBaru = 
            $fotoLama; 
 
        $namaFotoBaru = 
            null; 
 
        $folderUpload = 
            "../uploads/galeri/"; 
 
 
        /* ============================================= 
           CEK FOTO BARU 
        ============================================= */ 
 
        if ( 
            isset($_FILES["foto"]) && 
            $_FILES["foto"]["error"] 
            !== UPLOAD_ERR_NO_FILE 
        ) { 
 
 
            /* ========================================= 
               CEK ERROR UPLOAD 
            ========================================= */ 
 
            if ( 
                $_FILES["foto"]["error"] 
                !== UPLOAD_ERR_OK 
            ) { 
 
                $pesan = 
                    "Foto gagal diupload."; 
 
                $tipePesan = 
                    "error"; 
 
            } 
 
 
            /* ========================================= 
               CEK UKURAN 
            ========================================= */ 
 
            elseif ( 
                $_FILES["foto"]["size"] 
                > 2 * 1024 * 1024 
            ) { 
 
                $pesan = 
                    "Ukuran foto maksimal 2 MB."; 
 
                $tipePesan = 
                    "error"; 
 
            } 
 
 
            else { 
 
                /* ===================================== 
                   CEK MIME TYPE 
                ===================================== */ 
 
                $finfo = 
                    finfo_open( 
                        FILEINFO_MIME_TYPE 
                    ); 
 
 
                $mime = 
                    finfo_file( 
                        $finfo, 
                        $_FILES["foto"]["tmp_name"] 
                    ); 
 
 
                finfo_close($finfo); 
 
 
                $mimeDiizinkan = [ 
 
                    "image/jpeg", 
 
                    "image/png", 
 
                    "image/webp" 
 
                ]; 
 
 
                if ( 
                    !in_array( 
                        $mime, 
                        $mimeDiizinkan, 
                        true 
                    ) 
                ) { 
 
                    $pesan = 
                        "Format foto harus JPG, JPEG, PNG, atau WEBP."; 
 
                    $tipePesan = 
                        "error"; 
 
                } else { 
 
                    /* ================================= 
                       EXTENSION 
                    ================================= */ 
 
                    switch ($mime) { 
 
                        case "image/jpeg": 
 
                            $extension = 
                                "jpg"; 
 
                            break; 
 
 
                        case "image/png": 
 
                            $extension = 
                                "png"; 
 
                            break; 
 
 
                        case "image/webp": 
 
                            $extension = 
                                "webp"; 
 
                            break; 
 
 
                        default: 
 
                            $extension = 
                                ""; 
 
                            break; 
                    } 
 
 
                    if ( 
                        $extension === "" 
                    ) { 
 
                        $pesan = 
                            "Format foto tidak valid."; 
 
                        $tipePesan = 
                            "error"; 
 
                    } else { 
 
                        /* ============================= 
                           BUAT FOLDER 
                        ============================= */ 
 
                        if ( 
                            !is_dir( 
                                $folderUpload 
                            ) 
                        ) { 
 
                            if ( 
                                !mkdir( 
                                    $folderUpload, 
                                    0755, 
                                    true 
                                ) 
                            ) { 
 
                                $pesan = 
                                    "Folder upload tidak dapat dibuat."; 
 
                                $tipePesan = 
                                    "error"; 
                            } 
                        } 
 
 
                        /* ============================= 
                           UPLOAD 
                        ============================= */ 
 
                        if ( 
                            $pesan === "" 
                        ) { 
 
                            $namaFotoBaru = 
                                "galeri_" . 
                                date("Ymd_His") . 
                                "_" . 
                                bin2hex( 
                                    random_bytes(4) 
                                ) . 
                                "." . 
                                $extension; 
 
 
                            $targetFile = 
                                $folderUpload . 
                                $namaFotoBaru; 
 
 
                            if ( 
                                move_uploaded_file( 
                                    $_FILES["foto"]["tmp_name"], 
                                    $targetFile 
                                ) 
                            ) { 
 
                                $fotoBaru = 
                                    $namaFotoBaru; 
 
                            } else { 
 
                                $namaFotoBaru = 
                                    null; 
 
                                $pesan = 
                                    "Foto gagal disimpan ke folder upload."; 
 
                                $tipePesan = 
                                    "error"; 
                            } 
                        } 
                    } 
                } 
            } 
        } 
 
 
        /* ================================================= 
           UPDATE DATABASE 
        ================================================= */ 
 
        if ( 
            $pesan === "" 
        ) { 
 
            $stmtUpdate = 
                db_prepare( 
                    $koneksi, 
                    "UPDATE galeri 
                     SET judul = ?, 
                         keterangan = ?, 
                         foto = ? 
                     WHERE id = ?" 
                ); 
 
 
            /* ============================================= 
               CEK PREPARE 
            ============================================= */ 
 
            if ( 
                !$stmtUpdate 
            ) { 
 
                if ( 
                    $namaFotoBaru !== null && 
                    file_exists( 
                        $folderUpload . 
                        $namaFotoBaru 
                    ) 
                ) { 
 
                    unlink( 
                        $folderUpload . 
                        $namaFotoBaru 
                    ); 
                } 
 
 
                $pesan = 
                    "Gagal menyiapkan proses update: " . 
                    db_error($koneksi); 
 
                $tipePesan = 
                    "error"; 
 
            } else { 
 
                /* ========================================= 
                   BIND 
                ========================================= */ 
 
                db_stmt_bind_param( 
                    $stmtUpdate, 
                    "sssi", 
                    $judul, 
                    $keterangan, 
                    $fotoBaru, 
                    $id 
                ); 
 
 
                /* ========================================= 
                   EXECUTE 
                ========================================= */ 
 
                if ( 
                    db_stmt_execute( 
                        $stmtUpdate 
                    ) 
                ) { 
 
                    db_stmt_close( 
                        $stmtUpdate 
                    ); 
 
 
                    /* ================================= 
                       HAPUS FOTO LAMA 
                    ================================= */ 
 
                    if ( 
                        $namaFotoBaru !== null && 
                        !empty($fotoLama) 
                    ) { 
 
                        $fileLama = 
                            "../uploads/galeri/" . 
                            basename($fotoLama); 
 
 
                        if ( 
                            file_exists($fileLama) && 
                            is_file($fileLama) 
                        ) { 
 
                            unlink($fileLama); 
                        } 
                    } 
 
 
                    /* ================================= 
                       REDIRECT 
                    ================================= */ 
 
                    header( 
                        "Location: galeri.php?status=edit_sukses" 
                    ); 
 
                    exit; 
 
                } else { 
 
                    $errorDatabase = 
                        db_stmt_error( 
                            $stmtUpdate 
                        ); 
 
 
                    db_stmt_close( 
                        $stmtUpdate 
                    ); 
 
 
                    /* ================================= 
                       HAPUS FOTO BARU JIKA GAGAL 
                    ================================= */ 
 
                    if ( 
                        $namaFotoBaru !== null && 
                        file_exists( 
                            $folderUpload . 
                            $namaFotoBaru 
                        ) 
                    ) { 
 
                        unlink( 
                            $folderUpload . 
                            $namaFotoBaru 
                        ); 
                    } 
 
 
                    $pesan = 
                        "Gagal memperbarui data galeri: " . 
                        $errorDatabase; 
 
                    $tipePesan = 
                        "error"; 
                } 
            } 
        } 
    } 
 
 
    /* ================================================= 
       PERTAHANKAN INPUT JIKA ERROR 
    ================================================= */ 
 
    $judulLama = 
        $judul 
        ?? $judulLama; 
 
 
    $keteranganLama = 
        $keterangan 
        ?? $keteranganLama; 
} 
 
 
/* ===================================================== 
   FOTO PREVIEW 
===================================================== */ 
 
$fotoPreview = ""; 
 
 
if ( 
    !empty($fotoLama) 
) { 
 
    $fotoPreview = 
        "../uploads/galeri/" . 
        basename($fotoLama); 
} 
 
?> 
 
<?php

/* =========================================================
   PENGATURAN TAMPILAN HALAMAN
   Bagian atas, menu, dan bagian bawah halaman diambil dari
   folder layouts/ supaya tidak ditulis berulang.
========================================================== */

$judul         = "Edit Galeri | Admin " . NAMA_SEKOLAH_PANJANG;

$css           = "../assets/css/admin/edit_galeri.css";

$menu_aktif    = "galeri";

$judul_halaman = "Edit Galeri";

$subjudul      = "Perbarui foto kegiatan sekolah";

$footer_admin  = true;      /* halaman ini memakai tulisan footer */

require_once "../layouts/admin/kepala.php";
require_once "../layouts/admin/sidebar.php";
require_once "../layouts/admin/topbar.php";
?>
 
 
<!-- ===================================================== 
     OVERLAY 
===================================================== --> 
 
<div 
    class="overlay" 
    id="overlay" 
></div> 
 
 
<!-- ===================================================== 
     SIDEBAR 
     PERSIS SEPERTI DASHBOARD 
 
 
<!-- ===================================================== 
     MAIN 
 
 
        <!-- BREADCRUMB --> 
 
        <div class="breadcrumb"> 
 
 
            <a href="dashboard.php"> 
                Dashboard 
            </a> 
 
 
            <span> 
                / 
            </span> 
 
 
            <a href="galeri.php"> 
                Galeri 
            </a> 
 
 
            <span> 
                / Edit 
            </span> 
 
 
        </div> 
 
 
        <!-- FORM CARD --> 
 
        <div class="form-card"> 
 
 
            <h2> 
                Edit Foto Galeri 
            </h2> 
 
 
            <p class="subtitle"> 
 
                Perbarui informasi foto galeri sekolah. 
 
            </p> 
 
 
            <!-- ALERT --> 
 
            <?php if ($pesan !== ""): ?> 
 
 
                <div class="alert alert-error"> 
 
 
                    <i class="fa-solid fa-triangle-exclamation"></i> 
 
                    <?= e($pesan) ?> 
 
 
                </div> 
 
 
            <?php endif; ?> 
 
 
            <!-- FORM --> 
 
            <form 
                method="POST" 
                enctype="multipart/form-data" 
            > 
 
 
                <!-- JUDUL --> 
 
                <div class="form-group"> 
 
 
                    <label for="judul"> 
 
                        Judul Foto 
 
 
                        <span class="required"> 
                            * 
                        </span> 
 
 
                    </label> 
 
 
                    <input 
                        type="text" 
                        id="judul" 
                        name="judul" 
                        class="form-control" 
                        value="<?= e($judulLama) ?>" 
                        placeholder="Masukkan judul foto" 
                        required 
                    > 
 
 
                </div> 
 
 
                <!-- KETERANGAN --> 
 
                <div class="form-group"> 
 
 
                    <label for="keterangan"> 
 
                        Keterangan 
 
                    </label> 
 
 
                    <textarea 
                        id="keterangan" 
                        name="keterangan" 
                        class="form-control" 
                        placeholder="Masukkan keterangan foto (opsional)" 
                    ><?= e($keteranganLama) ?></textarea> 
 
 
                    <div class="form-help"> 
 
                        Keterangan boleh dikosongkan. 
 
                    </div> 
 
 
                </div> 
 
 
                <!-- FOTO SAAT INI --> 
 
                <div class="form-group"> 
 
 
                    <label> 
 
                        Foto Saat Ini 
 
                    </label> 
 
 
                    <div class="current-photo"> 
 
 
                        <?php if ( 
                            !empty($fotoPreview) && 
                            file_exists($fotoPreview) 
                        ): ?> 
 
 
                            <img 
                                src="<?= e($fotoPreview) ?>" 
                                alt="Foto Galeri" 
                                class="photo-preview" 
                            > 
 
 
                        <?php else: ?> 
 
 
                            <div class="no-photo"> 
 
 
                                <span class="no-photo-icon"> 
                                    <i class="fa-solid fa-images"></i> 
                                </span> 
 
 
                                <span> 
                                    Tidak ada foto 
                                </span> 
 
 
                            </div> 
 
 
                        <?php endif; ?> 
 
 
                    </div> 
 
 
                </div> 
 
 
                <!-- GANTI FOTO --> 
 
                <div class="form-group"> 
 
 
                    <label for="foto"> 
 
                        Ganti Foto 
 
                    </label> 
 
 
                    <input 
                        type="file" 
                        id="foto" 
                        name="foto" 
                        class="form-control" 
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" 
                    > 
 
 
                    <div class="form-help"> 
 
                        Kosongkan jika tidak ingin mengganti foto. 
                        Format JPG, JPEG, PNG, atau WEBP. 
                        Maksimal 2 MB. 
 
                    </div> 
 
 
                    <!-- PREVIEW FOTO BARU --> 
 
                    <div 
                        class="new-preview" 
                        id="newPreview" 
                    > 
 
 
                        <div class="new-preview-title"> 
 
                            Preview foto baru: 
 
                        </div> 
 
 
                        <img 
                            id="previewImage" 
                            src="" 
                            alt="Preview foto baru" 
                        > 
 
 
                    </div> 
 
 
                </div> 
 
 
                <!-- ACTION --> 
 
                <div class="form-actions"> 
 
 
                    <!-- SIMPAN --> 
 
                    <button 
                        type="submit" 
                        name="update_galeri" 
                        class="button button-primary" 
                    > 
 
                        <i class="fa-solid fa-floppy-disk"></i> 
 
                        Simpan Perubahan 
 
                    </button> 
 
 
                    <!-- KEMBALI --> 
 
                    <a 
                        href="galeri.php" 
                        class="button button-secondary" 
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
 
/* ===================================================== 
   PREVIEW FOTO BARU 
===================================================== */ 
 
const fotoInput = 
    document.getElementById( 
        "foto" 
    ); 
 
 
const newPreview = 
    document.getElementById( 
        "newPreview" 
    ); 
 
 
const previewImage = 
    document.getElementById( 
        "previewImage" 
    ); 
 
 
fotoInput.addEventListener( 
    "change", 
    function() { 
 
        const file = 
            this.files[0]; 
 
 
        /* ============================================= 
           TIDAK ADA FILE 
        ============================================= */ 
 
        if (!file) { 
 
            newPreview.style.display = 
                "none"; 
 
            previewImage.src = 
                ""; 
 
            return; 
        } 
 
 
        /* ============================================= 
           FORMAT 
        ============================================= */ 
 
        const allowedTypes = [ 
 
            "image/jpeg", 
 
            "image/png", 
 
            "image/webp" 
 
        ]; 
 
 
        if ( 
            !allowedTypes.includes( 
                file.type 
            ) 
        ) { 
 
            alert( 
                "Format foto harus JPG, JPEG, PNG, atau WEBP." 
            ); 
 
            this.value = 
                ""; 
 
            newPreview.style.display = 
                "none"; 
 
            previewImage.src = 
                ""; 
 
            return; 
        } 
 
 
        /* ============================================= 
           UKURAN 
        ============================================= */ 
 
        if ( 
            file.size > 
            2 * 1024 * 1024 
        ) { 
 
            alert( 
                "Ukuran foto maksimal 2 MB." 
            ); 
 
            this.value = 
                ""; 
 
            newPreview.style.display = 
                "none"; 
 
            previewImage.src = 
                ""; 
 
            return; 
        } 
 
 
        /* ============================================= 
           PREVIEW 
        ============================================= */ 
 
        const reader = 
            new FileReader(); 
 
 
        reader.onload = 
            function(event) { 
 
                previewImage.src = 
                    event.target.result; 
 
                newPreview.style.display = 
                    "block"; 
 
            }; 
 
 
        reader.readAsDataURL( 
            file 
        ); 
 
    } 
); 
 
</script>

<?php require_once "../layouts/admin/kaki.php"; ?>
 
 
</body> 
 
</html>