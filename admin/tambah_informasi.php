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
 
 
 
 
 
/* ========================= 
   DATA ADMIN 
========================= */ 
 
$admin_nama = $_SESSION['admin_nama'] ?? 'Administrator'; 
$admin_user = $_SESSION['admin_username'] ?? 'admin'; 


/* =========================
   LOGO SEKOLAH
========================= */

$logo_sekolah = '';

$query_logo = db_query(
    $koneksi,
    "SELECT logo FROM profil ORDER BY id ASC LIMIT 1"
);

if ($query_logo && db_num_rows($query_logo) > 0) {

    $data_logo = db_fetch_assoc($query_logo);

    $logo_sekolah = $data_logo['logo'] ?? '';
}
 
 
/* ========================= 
   VARIABEL FORM 
========================= */ 
 
$judul = ''; 
$isi = ''; 
$tanggal = date('Y-m-d'); 
 
$error = ''; 
 
 
/* ========================= 
   PROSES SIMPAN 
========================= */ 
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
 
    $judul = trim($_POST['judul'] ?? ''); 
    $isi = trim($_POST['isi'] ?? ''); 
    $tanggal = trim($_POST['tanggal'] ?? ''); 
 
 
    /* ========================= 
       VALIDASI DATA 
    ========================= */ 
 
    if ($judul === '') { 
 
        $error = 'Judul informasi wajib diisi.'; 
 
    } elseif ($isi === '') { 
 
        $error = 'Isi informasi wajib diisi.'; 
 
    } elseif ($tanggal === '') { 
 
        $error = 'Tanggal informasi wajib diisi.'; 
 
    } else { 
 
 
        /* ========================= 
           VALIDASI TANGGAL 
        ========================= */ 
 
        $tanggal_valid = DateTime::createFromFormat( 
            'Y-m-d', 
            $tanggal 
        ); 
 
        $tanggal_ok = 
            $tanggal_valid && 
            $tanggal_valid->format('Y-m-d') === $tanggal; 
 
 
        if (!$tanggal_ok) { 
 
            $error = 'Format tanggal tidak valid.'; 
 
        } else { 
 
 
            /* ========================= 
               UPLOAD FOTO 
            ========================= */ 
 
            $nama_foto = ''; 
 
            $foto_berhasil_upload = false; 
 
            $foto_path_baru = ''; 
 
 
            if ( 
                isset($_FILES['foto']) && 
                $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE 
            ) { 
 
                $foto = $_FILES['foto']; 
 
 
                /* ========================= 
                   CEK ERROR UPLOAD 
                ========================= */ 
 
                if ( 
                    $foto['error'] !== UPLOAD_ERR_OK 
                ) { 
 
                    $error = 
                        'Foto gagal diupload. Silakan coba lagi.'; 
 
                } elseif ( 
                    $foto['size'] > 2 * 1024 * 1024 
                ) { 
 
                    $error = 
                        'Ukuran foto maksimal 2 MB.'; 
 
                } else { 
 
 
                    /* ========================= 
                       CEK EXTENSION 
                    ========================= */ 
 
                    $allowed_extensions = [ 
                        'jpg', 
                        'jpeg', 
                        'png', 
                        'webp' 
                    ]; 
 
                    $nama_asli = 
                        $foto['name']; 
 
                    $extension = 
                        strtolower( 
                            pathinfo( 
                                $nama_asli, 
                                PATHINFO_EXTENSION 
                            ) 
                        ); 
 
 
                    if ( 
                        !in_array( 
                            $extension, 
                            $allowed_extensions, 
                            true 
                        ) 
                    ) { 
 
                        $error = 
                            'Format foto harus JPG, JPEG, PNG, atau WEBP.'; 
 
                    } else { 
 
 
                        /* ========================= 
                           CEK MIME 
                        ========================= */ 
 
                        $finfo = 
                            finfo_open(FILEINFO_MIME_TYPE); 
 
                        $mime = 
                            finfo_file( 
                                $finfo, 
                                $foto['tmp_name'] 
                            ); 
 
                        finfo_close($finfo); 
 
 
                        $allowed_mime = [ 
                            'image/jpeg', 
                            'image/png', 
                            'image/webp' 
                        ]; 
 
 
                        if ( 
                            !in_array( 
                                $mime, 
                                $allowed_mime, 
                                true 
                            ) 
                        ) { 
 
                            $error = 
                                'File yang diupload bukan gambar yang valid.'; 
 
                        } else { 
 
 
                            /* ========================= 
                               FOLDER UPLOAD 
                            ========================= */ 
 
                            $folder_upload = 
                                "../uploads/informasi/"; 
 
 
                            if ( 
                                !unggah_ada_folder( 
                                    $folder_upload 
                                ) 
                            ) { 
 
                                unggah_mkdir( 
                                    $folder_upload, 
                                    0755, 
                                    true 
                                ); 
                            } 
 
 
                            /* ========================= 
                               NAMA FILE BARU 
                            ========================= */ 
 
                            $nama_foto = 
                                'informasi_' . 
                                date('YmdHis') . 
                                '_' . 
                                bin2hex( 
                                    random_bytes(5) 
                                ) . 
                                '.' . 
                                $extension; 
 
 
                            $foto_path_baru = 
                                $folder_upload . 
                                $nama_foto; 
 
 
                            /* ========================= 
                               PINDAHKAN FILE 
                            ========================= */ 
 
                            if ( 
                                unggah_simpan( 
                                    $foto['tmp_name'], 
                                    $foto_path_baru 
                                ) 
                            ) { 
 
                                $foto_berhasil_upload = 
                                    true; 
 
                            } else { 
 
                                $nama_foto = ''; 
 
                                $error = 
                                    'Foto gagal disimpan ke server.'; 
                            } 
 
                        } 
 
                    } 
 
                } 
 
            } 
 
 
            /* ========================= 
               SIMPAN DATABASE 
            ========================= */ 
 
            if ($error === '') { 
 
                $sql = " 
                    INSERT INTO informasi 
                    ( 
                        judul, 
                        isi, 
                        tanggal, 
                        foto 
                    ) 
                    VALUES 
                    ( 
                        ?, 
                        ?, 
                        ?, 
                        ? 
                    ) 
                "; 
 
 
                $stmt = 
                    db_prepare( 
                        $koneksi, 
                        $sql 
                    ); 
 
 
                if (!$stmt) { 
 
                    if ( 
                        $foto_berhasil_upload && 
                        unggah_ada($foto_path_baru) 
                    ) { 
 
                        unggah_hapus( 
                            $foto_path_baru 
                        ); 
                    } 
 
 
                    $error = 
                        'Query database gagal diproses.'; 
 
                } else { 
 
 
                    db_stmt_bind_param( 
                        $stmt, 
                        "ssss", 
                        $judul, 
                        $isi, 
                        $tanggal, 
                        $nama_foto 
                    ); 
 
 
                    if ( 
                        db_stmt_execute($stmt) 
                    ) { 
 
                        db_stmt_close($stmt); 
 
 
                        header( 
                            "Location: informasi.php?status=tambah_sukses" 
                        ); 
 
                        exit; 
 
                    } else { 
 
                        db_stmt_close($stmt); 
 
 
                        if ( 
                            $foto_berhasil_upload && 
                            unggah_ada($foto_path_baru) 
                        ) { 
 
                            unggah_hapus( 
                                $foto_path_baru 
                            ); 
                        } 
 
 
                        $error = 
                            'Informasi gagal disimpan ke database.'; 
                    } 
 
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

$judul         = "Tambah Informasi | Admin Sekolah";

$css           = "../assets/css/admin/tambah_informasi.css";

$menu_aktif    = "informasi";

$judul_halaman = "Tambah Informasi";

$subjudul      = "Kelola pengumuman, berita, dan informasi sekolah";

require_once "../layouts/admin/kepala.php";
require_once "../layouts/admin/sidebar.php";
require_once "../layouts/admin/topbar.php";
?>
 
 
<!-- ========================= 
     SIDEBAR 
 
 
 
<!-- ========================= 
     OVERLAY 
========================= --> 
 
<div 
    class="overlay" 
    id="overlay" 
></div> 
 
 
 
<!-- ========================= 
     MAIN 
 
 
        <!-- PAGE HEADER --> 
 
        <div class="page-header"> 
 
 
            <a 
                href="informasi.php" 
                class="back-link" 
            > 
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Informasi 
            </a> 
 
 
            <h1> 
                Tambah Informasi 
            </h1> 
 
 
            <p> 
                Buat pengumuman atau informasi baru 
                yang akan ditampilkan pada website sekolah. 
            </p> 
 
 
        </div> 
 
 
 
        <!-- ERROR --> 
 
        <?php if ($error !== ''): ?> 
 
            <div class="alert-error"> 
 
                <span> 
                    <i class="fa-solid fa-triangle-exclamation"></i> 
                </span> 
 
                <span> 
                    <?= e($error) ?> 
                </span> 
 
            </div> 
 
        <?php endif; ?> 
 
 
 
        <!-- FORM CARD --> 
 
        <div class="form-card"> 
 
 
            <form 
                method="POST" 
                enctype="multipart/form-data" 
                id="informasiForm" 
            > 
 
 
                <!-- ========================= 
                     DATA INFORMASI 
                ========================= --> 
 
                <div class="form-section"> 
 
 
                    <div class="section-title"> 
                        Data Informasi 
                    </div> 
 
 
 
                    <!-- JUDUL --> 
 
                    <div class="form-group"> 
 
 
                        <label for="judul"> 
 
                            Judul Informasi 
 
                            <span class="required"> 
                                * 
                            </span> 
 
                        </label> 
 
 
                        <input 
                            type="text" 
                            name="judul" 
                            id="judul" 
                            class="form-control" 
                            value="<?= e($judul) ?>" 
                            placeholder="Contoh: Pengumuman Libur Sekolah" 
                            maxlength="200" 
                            required 
                        > 
 
 
                        <span class="form-help"> 
                            Gunakan judul yang singkat, 
                            jelas, dan mudah dipahami. 
                        </span> 
 
 
                    </div> 
 
 
 
                    <!-- TANGGAL --> 
 
                    <div class="form-group"> 
 
 
                        <label for="tanggal"> 
 
                            Tanggal Informasi 
 
                            <span class="required"> 
                                * 
                            </span> 
 
                        </label> 
 
 
                        <input 
                            type="date" 
                            name="tanggal" 
                            id="tanggal" 
                            class="form-control" 
                            value="<?= e($tanggal) ?>" 
                            required 
                        > 
 
 
                        <span class="form-help"> 
                            Tanggal yang akan ditampilkan 
                            pada informasi sekolah. 
                        </span> 
 
 
                    </div> 
 
 
 
                    <!-- ISI --> 
 
                    <div class="form-group"> 
 
 
                        <label for="isi"> 
 
                            Isi Informasi 
 
                            <span class="required"> 
                                * 
                            </span> 
 
                        </label> 
 
 
                        <textarea 
                            name="isi" 
                            id="isi" 
                            class="form-control" 
                            placeholder="Tuliskan isi pengumuman atau informasi sekolah di sini..." 
                            required 
                        ><?= e($isi) ?></textarea> 
 
 
                        <span class="form-help"> 
                            Tulis informasi secara lengkap 
                            agar mudah dipahami oleh siswa, 
                            orang tua, dan masyarakat. 
                        </span> 
 
 
                    </div> 
 
 
                </div> 
 
 
 
                <!-- ========================= 
                     FOTO 
                ========================= --> 
 
                <div class="form-section"> 
 
 
                    <div class="section-title"> 
                        Foto Informasi 
                    </div> 
 
 
                    <div 
                        class="upload-area" 
                        id="uploadArea" 
                    > 
 
 
                        <div class="upload-icon"> 
                            <i class="fa-solid fa-camera"></i> 
                        </div> 
 
 
                        <h3> 
                            Tambahkan Foto 
                        </h3> 
 
 
                        <p> 
                            Pilih foto yang ingin ditampilkan 
                            bersama informasi. 
                            Maksimal 2 MB. 
                        </p> 
 
 
                        <label 
                            for="foto" 
                            class="upload-button" 
                        > 
                             Pilih Foto 
                        </label> 
 
 
                        <input 
                            type="file" 
                            name="foto" 
                            id="foto" 
                            class="file-input" 
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" 
                        > 
 
 
                        <div 
                            class="preview-wrapper" 
                            id="previewWrapper" 
                        > 
 
 
                            <img 
                                src="" 
                                alt="Preview Foto" 
                                class="preview-image" 
                                id="previewImage" 
                            > 
 
 
                            <div 
                                class="preview-name" 
                                id="previewName" 
                            ></div> 
 
 
                        </div> 
 
 
                    </div> 
 
 
                </div> 
 
 
 
                <!-- ========================= 
                     INFO 
                ========================= --> 
 
                <div class="form-section"> 
 
 
                    <div class="info-box"> 
 
 
                        <strong> 
                            <i class="fa-solid fa-lightbulb"></i> Tips 
                        </strong> 
 
 
                        Gunakan foto dengan kualitas yang 
                        cukup baik dan ukuran file maksimal 
                        <strong>2 MB</strong>. 
                        Format yang diperbolehkan: 
                        JPG, JPEG, PNG, dan WEBP. 
 
 
                    </div> 
 
 
                </div> 
 
 
 
                <!-- ========================= 
                     ACTION 
                ========================= --> 
 
                <div class="form-actions"> 
 
 
                    <a 
                        href="informasi.php" 
                        class="btn btn-cancel" 
                    > 
                        Batal 
                    </a> 
 
 
                    <button 
                        type="submit" 
                        class="btn btn-save" 
                    > 
                        Simpan Informasi 
                    </button> 
 
 
                </div> 
 
 
            </form> 
 
 
        </div> 
 
 
<!-- =====================================================
     JAVASCRIPT KHUSUS HALAMAN INI
===================================================== -->

<script>
 
 
    /* ========================= 
       PREVIEW FOTO 
    ========================= */ 
 
    const fotoInput = 
        document.getElementById('foto'); 
 
    const previewWrapper = 
        document.getElementById( 
            'previewWrapper' 
        ); 
 
    const previewImage = 
        document.getElementById( 
            'previewImage' 
        ); 
 
    const previewName = 
        document.getElementById( 
            'previewName' 
        ); 
 
 
    fotoInput.addEventListener( 
        'change', 
        function() { 
 
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
               CEK UKURAN 
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
               PREVIEW 
            ========================= */ 
 
            const reader = 
                new FileReader(); 
 
 
            reader.onload = 
                function(event) { 
 
                    previewImage.src = 
                        event.target.result; 
 
                    previewName.textContent = 
                        file.name; 
 
                    previewWrapper.classList.add( 
                        'show' 
                    ); 
 
                }; 
 
 
            reader.readAsDataURL(file); 
 
        } 
    ); 
 
 
 
    /* ========================= 
       CLICK AREA 
    ========================= */ 
 
    const uploadArea = 
        document.getElementById( 
            'uploadArea' 
        ); 
 
 
    uploadArea.addEventListener( 
        'click', 
        function(event) { 
 
            if ( 
                event.target.tagName !== 'LABEL' && 
                event.target.tagName !== 'INPUT' 
            ) { 
 
                fotoInput.click(); 
 
            } 
 
        } 
    ); 
 
 
 
    /* ========================= 
       SUBMIT 
    ========================= */ 
 
    const form = 
        document.getElementById( 
            'informasiForm' 
        ); 
 
 
    form.addEventListener( 
        'submit', 
        function() { 
 
            const button = 
                form.querySelector( 
                    '.btn-save' 
                ); 
 
 
            button.disabled = true; 
 
            button.innerHTML = 
                '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...'; 
 
        } 
    ); 
 
 
</script>

<?php require_once "../layouts/admin/kaki.php"; ?>
 
 
</body> 
 
</html>