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
$admin_username = $_SESSION['admin_username'] ?? 'admin'; 

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
   PROSES TAMBAH GURU 
========================= */ 
$error = ''; 
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
 
    $nama    = trim($_POST['nama'] ?? ''); 
    $jabatan = trim($_POST['jabatan'] ?? ''); 
 
    /* Validasi */ 
    if ($nama === '') { 
        $error = "Nama guru/staf wajib diisi."; 
    } elseif ($jabatan === '') { 
        $error = "Jabatan wajib diisi."; 
    } 
 
    /* ========================= 
       PROSES FOTO 
    ========================= */ 
    $nama_file = ''; 
 
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
            $tmp_file  = $file['tmp_name']; 
            $ukuran    = $file['size']; 
 
            $ext = strtolower( 
                pathinfo($nama_asli, PATHINFO_EXTENSION) 
            ); 
 
            /* Cek ekstensi */ 
            if (!in_array($ext, $allowed_ext)) { 
 
                $error = 
                    "Format foto harus JPG, JPEG, PNG, atau WEBP."; 
 
            } 
 
            /* Cek ukuran maksimal 2 MB */ 
            elseif ($ukuran > 2 * 1024 * 1024) { 
 
                $error = 
                    "Ukuran foto maksimal 2 MB."; 
 
            } 
 
            /* Cek MIME */ 
            else { 
 
                $finfo = finfo_open(FILEINFO_MIME_TYPE); 
 
                $mime = finfo_file( 
                    $finfo, 
                    $tmp_file 
                ); 
 
                /* finfo auto-closed in PHP 8.5+ */ 
 
                if (!in_array($mime, $allowed_mime)) { 
 
                    $error = 
                        "File yang diupload bukan gambar yang valid."; 
                } 
            } 
 
            /* Upload */ 
            if ($error === '') { 
 
                $folder_upload = "../uploads/guru/"; 
 
                if (!unggah_ada_folder($folder_upload)) { 
 
                    unggah_mkdir( 
                        $folder_upload, 
                        0755, 
                        true 
                    ); 
                } 
 
                $nama_file = 
                    'guru_' . 
                    time() . 
                    '_' . 
                    bin2hex(random_bytes(5)) . 
                    '.' . 
                    $ext; 
 
                $tujuan_file = 
                    $folder_upload . 
                    $nama_file; 
 
                if ( 
                    !unggah_simpan( 
                        $tmp_file, 
                        $tujuan_file 
                    ) 
                ) { 
 
                    $error = 
                        "Foto gagal disimpan ke server."; 
 
                    $nama_file = ''; 
                } 
            } 
        } 
    } 
 
    /* ========================= 
       SIMPAN DATABASE 
    ========================= */ 
    if ($error === '') { 
 
        $sql = 
            "INSERT INTO guru (nama, jabatan, foto) 
             VALUES (?, ?, ?)"; 
 
        $stmt = db_prepare( 
            $koneksi, 
            $sql 
        ); 
 
        if ($stmt) { 
 
            db_stmt_bind_param( 
                $stmt, 
                "sss", 
                $nama, 
                $jabatan, 
                $nama_file 
            ); 
 
            if (db_stmt_execute($stmt)) { 
 
                db_stmt_close($stmt); 
 
                header( 
                    "Location: guru.php?status=tambah_sukses" 
                ); 
 
                exit; 
 
            } else { 
 
                db_stmt_close($stmt); 
 
                /* Hapus foto jika database gagal */ 
                if ($nama_file !== '') { 
 
                    $file_hapus = 
                        "../uploads/guru/" . 
                        $nama_file; 
 
                    if (unggah_ada($file_hapus)) { 
 
                        unggah_hapus($file_hapus); 
                    } 
                } 
 
                $error = 
                    "Data guru gagal disimpan ke database."; 
            } 
 
        } else { 
 
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

$judul         = "Tambah Guru & Staf | Admin Sekolah";

$css           = "../assets/css/admin/tambah_guru.css";

$menu_aktif    = "guru";

$judul_halaman = "Tambah Guru & Staf";

$subjudul      = "Kelola data tenaga pendidik dan kependidikan";

require_once "../layouts/admin/kepala.php";
require_once "../layouts/admin/sidebar.php";
require_once "../layouts/admin/topbar.php";
?>
 
    <!-- ===================================================== 
         SIDEBAR 
 
 
    <!-- ===================================================== 
         OVERLAY 
    ====================================================== --> 
 
    <div 
        class="overlay" 
        id="overlay" 
    ></div> 
 
 
    <!-- ===================================================== 
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
                    Tambah Data 
                </span> 
 
            </div> 
 
 
            <!-- PAGE HEADER --> 
 
            <div class="page-header"> 
 
                <div> 
 
                    <h1> 
                        Tambah Guru & Staf 
                    </h1> 
 
                    <p> 
                        Tambahkan data guru atau tenaga kependidikan 
                        baru ke dalam website sekolah. 
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
                         
                    </div> 
 
                    <div> 
 
                        <h2> 
                            Form Data Guru & Staf 
                        </h2> 
 
                        <p> 
                            Isi data dengan lengkap dan benar. 
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
                                value="<?= e($_POST['nama'] ?? '') ?>" 
                                required 
                                autocomplete="off" 
                            > 
 
                            <span class="form-help"> 
                                Masukkan nama lengkap beserta gelar jika ada. 
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
                                value="<?= e($_POST['jabatan'] ?? '') ?>" 
                                required 
                                autocomplete="off" 
                            > 
 
                            <span class="form-help"> 
                                Contoh: Guru Kelas, Guru Mapel, Kepala Sekolah, 
                                Operator, atau Tenaga Administrasi. 
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
 
 
                            <label 
                                for="foto" 
                                class="upload-box" 
                                id="uploadBox" 
                            > 
 
                                <div class="upload-icon"> 
                                    <i class="fa-solid fa-camera"></i> 
                                </div> 
 
                                <strong> 
                                    Klik untuk memilih foto 
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
                                    alt="Preview foto" 
                                > 
 
                                <span 
                                    class="preview-name" 
                                    id="previewName" 
                                ></span> 
 
                            </div> 
 
 
                            <span class="form-help"> 
                                Gunakan foto yang jelas dan memiliki pencahayaan 
                                yang cukup. Foto akan ditampilkan pada halaman 
                                Guru & Staf. 
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
                            Simpan Data 
                        </button> 
 
                    </div> 
 
                </form> 
 
            </div> 
 
 
            <!-- INFO --> 
 
            <div class="info-box"> 
 
                <div class="info-icon"> 
                     
                </div> 
 
                <div> 
 
                    <strong> 
                        Informasi 
                    </strong> 
 
                    <p> 
                        Foto bersifat opsional. Jika tidak mengupload foto, 
                        sistem akan menampilkan inisial nama guru pada halaman 
                        Guru & Staf. Foto yang diupload akan disimpan di folder 
                        <b>uploads/guru/</b>. 
                    </p> 
 
                </div> 
 
            </div> 
 
 
<!-- =====================================================
     JAVASCRIPT KHUSUS HALAMAN INI
===================================================== -->

<script>
 
        /* ===================================================== 
           PREVIEW FOTO 
        ===================================================== */ 
 
        const fotoInput = 
            document.getElementById("foto"); 
 
        const previewWrapper = 
            document.getElementById("previewWrapper"); 
 
        const previewImage = 
            document.getElementById("previewImage"); 
 
        const previewName = 
            document.getElementById("previewName"); 
 
 
        if (fotoInput) { 
 
            fotoInput?.addEventListener( 
                "change", 
                function() 
                { 
 
                    const file = 
                        this.files[0]; 
 
 
                    if (!file) { 
 
                        previewWrapper.classList.remove("show"); 
 
                        previewImage.src = ""; 
 
                        previewName.textContent = ""; 
 
                        return; 
                    } 
 
 
                    /* Validasi ukuran */ 
 
                    if ( 
                        file.size > 
                        2 * 1024 * 1024 
                    ) { 
 
                        alert( 
                            "Ukuran foto maksimal 2 MB." 
                        ); 
 
                        this.value = ""; 
 
                        previewWrapper.classList.remove("show"); 
 
                        return; 
                    } 
 
 
                    /* Validasi tipe */ 
 
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
                            "Format foto harus JPG, JPEG, PNG atau WEBP." 
                        ); 
 
                        this.value = ""; 
 
                        previewWrapper.classList.remove("show"); 
 
                        return; 
                    } 
 
 
                    const reader = 
                        new FileReader(); 
 
 
                    reader.onload = 
                        function(event) 
                        { 
 
                            previewImage.src = 
                                event.target.result; 
 
                            previewName.textContent = 
                                file.name + 
                                " • " + 
                                ( 
                                    file.size / 1024 
                                ).toFixed(0) + 
                                " KB"; 
 
                            previewWrapper.classList.add( 
                                "show" 
                            ); 
 
                        }; 
 
 
                    reader.readAsDataURL(file); 
 
                } 
            ); 
 
        } 
 
</script>

<?php require_once "../layouts/admin/kaki.php"; ?>
