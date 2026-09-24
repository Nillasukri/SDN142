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
 
$nama_admin = $_SESSION['admin_nama'] ?? 'Administrator'; 
$username_admin = $_SESSION['admin_username'] ?? 'admin';


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
 
 
/* ========================================================= 
/* ========================================================= 
   PESAN 
========================================================= */ 
 
$pesan = ""; 
$tipe_pesan = ""; 
 
 
/* ========================================================= 
   PROSES TAMBAH DOKUMEN 
========================================================= */ 
 
if ($_SERVER["REQUEST_METHOD"] === "POST") { 
 
    $judul = trim($_POST["judul"] ?? ""); 
    $kategori = trim($_POST["kategori"] ?? ""); 
    $deskripsi = trim($_POST["deskripsi"] ?? ""); 
    $tanggal = trim($_POST["tanggal"] ?? ""); 
 
 
    /* ========================= 
       DEFAULT TANGGAL 
    ========================= */ 
 
    if ($tanggal === "") { 
        $tanggal = date("Y-m-d"); 
    } 
 
 
    /* ========================= 
       VALIDASI JUDUL 
    ========================= */ 
 
    if ($judul === "") { 
 
        $pesan = "Judul dokumen wajib diisi."; 
        $tipe_pesan = "error"; 
 
 
    /* ========================= 
       VALIDASI KATEGORI 
    ========================= */ 
 
    } elseif ($kategori === "") { 
 
        $pesan = "Kategori dokumen wajib diisi."; 
        $tipe_pesan = "error"; 
 
 
    /* ========================= 
       VALIDASI FILE 
    ========================= */ 
 
    } elseif ( 
        !isset($_FILES["file_dokumen"]) || 
        $_FILES["file_dokumen"]["error"] !== UPLOAD_ERR_OK 
    ) { 
 
        $pesan = "Silakan pilih file dokumen terlebih dahulu."; 
        $tipe_pesan = "error"; 
 
 
    } else { 
 
        $file = $_FILES["file_dokumen"]; 
 
        $nama_asli = $file["name"]; 
        $tmp_file = $file["tmp_name"]; 
        $ukuran_file = $file["size"]; 
        $error_file = $file["error"]; 
 
 
        /* ========================= 
           EXTENSION 
        ========================= */ 
 
        $extension = strtolower( 
            pathinfo( 
                $nama_asli, 
                PATHINFO_EXTENSION 
            ) 
        ); 
 
 
        /* ========================= 
           FORMAT YANG DIIZINKAN 
        ========================= */ 
 
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
 
 
        /* ========================= 
           MAKSIMAL 10 MB 
        ========================= */ 
 
        $maksimal_ukuran = 
            10 * 1024 * 1024; 
 
 
        /* ========================= 
           VALIDASI FORMAT 
        ========================= */ 
 
        if ( 
            !in_array( 
                $extension, 
                $ekstensi_diizinkan, 
                true 
            ) 
        ) { 
 
            $pesan = 
                "Format file tidak diperbolehkan. " 
                . "Gunakan PDF, Word, Excel, PowerPoint, " 
                . "gambar, ZIP, atau RAR."; 
 
            $tipe_pesan = "error"; 
 
 
        /* ========================= 
           VALIDASI UKURAN 
        ========================= */ 
 
        } elseif ( 
            $ukuran_file > $maksimal_ukuran 
        ) { 
 
            $pesan = 
                "Ukuran file terlalu besar. " 
                . "Maksimal 10 MB."; 
 
            $tipe_pesan = "error"; 
 
 
        /* ========================= 
           VALIDASI ERROR UPLOAD 
        ========================= */ 
 
        } elseif ( 
            $error_file !== UPLOAD_ERR_OK 
        ) { 
 
            $pesan = 
                "Terjadi kesalahan saat " 
                . "mengunggah file."; 
 
            $tipe_pesan = "error"; 
 
 
        } else { 
 
 
            /* ================================================= 
               FOLDER UPLOAD 
            ================================================= */ 
 
            $folder_upload = 
                "../uploads/dokumen/"; 
 
 
            if (!unggah_ada_folder($folder_upload)) { 
 
                unggah_mkdir( 
                    $folder_upload, 
                    0777, 
                    true 
                ); 
 
            } 
 
 
            /* ================================================= 
               NAMA FILE BARU 
            ================================================= */ 
 
            $nama_file_baru = 
                date("YmdHis") 
                . "_" 
                . bin2hex(random_bytes(5)) 
                . "." 
                . $extension; 
 
 
            $lokasi_file = 
                $folder_upload 
                . $nama_file_baru; 
 
 
            /* ================================================= 
               PINDAHKAN FILE 
            ================================================= */ 
 
            if ( 
                !unggah_simpan( 
                    $tmp_file, 
                    $lokasi_file 
                ) 
            ) { 
 
                $pesan = 
                    "File gagal disimpan ke server."; 
 
                $tipe_pesan = "error"; 
 
 
            } else { 
 
 
                /* ================================================= 
                   SIMPAN KE DATABASE 
                ================================================= */ 
 
                $sql = " 
                    INSERT INTO dokumen 
                    ( 
                        judul, 
                        kategori, 
                        deskripsi, 
                        nama_file, 
                        tanggal 
                    ) 
                    VALUES (?, ?, ?, ?, ?) 
                "; 
 
 
                $stmt = 
                    db_prepare( 
                        $koneksi, 
                        $sql 
                    ); 
 
 
                if (!$stmt) { 
 
 
                    /* ========================= 
                       HAPUS FILE JIKA GAGAL 
                    ========================= */ 
 
                    if ( 
                        unggah_ada( 
                            $lokasi_file 
                        ) 
                    ) { 
 
                        unggah_hapus( 
                            $lokasi_file 
                        ); 
 
                    } 
 
 
                    $pesan = 
                        "Data dokumen gagal diproses."; 
 
                    $tipe_pesan = "error"; 
 
 
                } else { 
 
 
                    db_stmt_bind_param( 
                        $stmt, 
                        "sssss", 
                        $judul, 
                        $kategori, 
                        $deskripsi, 
                        $nama_file_baru, 
                        $tanggal 
                    ); 
 
 
                    if ( 
                        db_stmt_execute( 
                            $stmt 
                        ) 
                    ) { 
 
 
                        db_stmt_close( 
                            $stmt 
                        ); 
 
 
                        header( 
                            "Location: dokumen.php?status=tambah_sukses" 
                        ); 
 
                        exit; 
 
 
                    } else { 
 
 
                        /* ========================= 
                           HAPUS FILE JIKA DATABASE GAGAL 
                        ========================= */ 
 
                        if ( 
                            unggah_ada( 
                                $lokasi_file 
                            ) 
                        ) { 
 
                            unggah_hapus( 
                                $lokasi_file 
                            ); 
 
                        } 
 
 
                        $pesan = 
                            "Dokumen gagal disimpan ke database."; 
 
                        $tipe_pesan = "error"; 
 
 
                        db_stmt_close( 
                            $stmt 
                        ); 
 
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

$judul         = "Tambah Dokumen | " . NAMA_SEKOLAH;

$css           = "../assets/css/admin/tambah_dokumen.css";

$menu_aktif    = "dokumen";

$judul_halaman = "Tambah Dokumen";

$subjudul      = "Panel administrasi website sekolah";

$footer_admin  = true;      /* halaman ini memakai tulisan footer */

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
 
 
        <!-- PAGE HEADER --> 
 
        <div class="page-header"> 
 
 
            <div class="page-header-left"> 
 
                <h2> 
                    Tambah Dokumen 
                </h2> 
 
                <p> 
                    Tambahkan dokumen baru 
                    untuk website sekolah. 
                </p> 
 
            </div> 
 
 
            <a 
                href="dokumen.php" 
                class="back-button"> 
 
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Dokumen 
 
            </a> 
 
 
        </div> 
 
 
        <!-- ALERT --> 
 
        <?php if ($pesan !== ""): ?> 
 
            <div class="alert alert-error"> 
 
                <?= e($pesan) ?> 
 
            </div> 
 
        <?php endif; ?> 
 
 
        <!-- ================================================= 
             FORM CARD 
        ================================================== --> 
 
        <div class="form-card"> 
 
 
            <!-- FORM TITLE --> 
 
            <div class="form-title"> 
 
 
                <div class="form-title-icon"> 
                     
                </div> 
 
 
                <div> 
 
                    <h3> 
                        Formulir Dokumen 
                    </h3> 
 
                    <p> 
                        Lengkapi data dokumen 
                        sebelum menyimpannya. 
                    </p> 
 
                </div> 
 
 
            </div> 
 
 
            <!-- ================================================= 
                 FORM 
            ================================================== --> 
 
            <form 
                method="POST" 
                enctype="multipart/form-data" 
                id="formDokumen"> 
 
 
                <!-- JUDUL + KATEGORI --> 
 
                <div class="form-row"> 
 
 
                    <!-- JUDUL --> 
 
                    <div class="form-group"> 
 
                        <label 
                            class="form-label" 
                            for="judul"> 
 
                            Judul Dokumen 
 
                            <span class="required"> 
                                * 
                            </span> 
 
                        </label> 
 
 
                        <input 
                            type="text" 
                            name="judul" 
                            id="judul" 
                            class="form-control" 
                            placeholder="Contoh: Jadwal Ujian Semester" 
                            value="<?= e( 
                                $_POST["judul"] ?? "" 
                            ) ?>" 
                            maxlength="200" 
                            required 
                        > 
 
                    </div> 
 
 
                    <!-- KATEGORI --> 
 
                    <div class="form-group"> 
 
                        <label 
                            class="form-label" 
                            for="kategori"> 
 
                            Kategori 
 
                            <span class="required"> 
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
                                <?= ( 
                                    ($_POST["kategori"] ?? "") 
                                    === "Administrasi" 
                                ) 
                                    ? "selected" 
                                    : "" 
                                ?> 
                            > 
                                Administrasi 
                            </option> 
 
 
                            <option 
                                value="Akademik" 
                                <?= ( 
                                    ($_POST["kategori"] ?? "") 
                                    === "Akademik" 
                                ) 
                                    ? "selected" 
                                    : "" 
                                ?> 
                            > 
                                Akademik 
                            </option> 
 
 
                            <option 
                                value="Kesiswaan" 
                                <?= ( 
                                    ($_POST["kategori"] ?? "") 
                                    === "Kesiswaan" 
                                ) 
                                    ? "selected" 
                                    : "" 
                                ?> 
                            > 
                                Kesiswaan 
                            </option> 
 
 
                            <option 
                                value="PPDB" 
                                <?= ( 
                                    ($_POST["kategori"] ?? "") 
                                    === "PPDB" 
                                ) 
                                    ? "selected" 
                                    : "" 
                                ?> 
                            > 
                                PPDB / Pendaftaran 
                            </option> 
 
 
                            <option 
                                value="Pengumuman" 
                                <?= ( 
                                    ($_POST["kategori"] ?? "") 
                                    === "Pengumuman" 
                                ) 
                                    ? "selected" 
                                    : "" 
                                ?> 
                            > 
                                Pengumuman 
                            </option> 
 
 
                            <option 
                                value="Surat" 
                                <?= ( 
                                    ($_POST["kategori"] ?? "") 
                                    === "Surat" 
                                ) 
                                    ? "selected" 
                                    : "" 
                                ?> 
                            > 
                                Surat 
                            </option> 
 
 
                            <option 
                                value="Lainnya" 
                                <?= ( 
                                    ($_POST["kategori"] ?? "") 
                                    === "Lainnya" 
                                ) 
                                    ? "selected" 
                                    : "" 
                                ?> 
                            > 
                                Lainnya 
                            </option> 
 
 
                        </select> 
 
                    </div> 
 
 
                </div> 
 
 
                <!-- TANGGAL --> 
 
                <div class="form-group"> 
 
                    <label 
                        class="form-label" 
                        for="tanggal"> 
 
                        Tanggal Dokumen 
 
                        <span class="required"> 
                            * 
                        </span> 
 
                    </label> 
 
 
                    <input 
                        type="date" 
                        name="tanggal" 
                        id="tanggal" 
                        class="form-control" 
                        value="<?= e( 
                            $_POST["tanggal"] 
                            ?? date("Y-m-d") 
                        ) ?>" 
                        required 
                    > 
 
                </div> 
 
 
                <!-- DESKRIPSI --> 
 
                <div class="form-group"> 
 
                    <label 
                        class="form-label" 
                        for="deskripsi"> 
 
                        Deskripsi 
 
                    </label> 
 
 
                    <textarea 
                        name="deskripsi" 
                        id="deskripsi" 
                        class="form-control" 
                        placeholder="Tuliskan keterangan singkat mengenai dokumen ini..." 
                        maxlength="1000" 
                    ><?= e( 
                        $_POST["deskripsi"] ?? "" 
                    ) ?></textarea> 
 
                </div> 
 
 
                <!-- FILE --> 
 
                <div class="form-group"> 
 
 
                    <label 
                        class="form-label"> 
 
                        File Dokumen 
 
                        <span class="required"> 
                            * 
                        </span> 
 
                    </label> 
 
 
                    <div 
                        class="file-upload" 
                        id="fileUpload"> 
 
 
                        <input 
                            type="file" 
                            name="file_dokumen" 
                            id="fileDokumen" 
                            required 
                        > 
 
 
                        <div class="upload-icon"> 
                            <i class="fa-solid fa-upload"></i> 
                        </div> 
 
 
                        <strong> 
                            Klik untuk memilih file 
                        </strong> 
 
 
                        <span> 
                            atau tarik file ke area ini 
                        </span> 
 
 
                        <div 
                            class="file-name" 
                            id="fileName"> 
 
                            Belum ada file dipilih 
 
                        </div> 
 
 
                    </div> 
 
 
                    <div class="file-info"> 
 
                        Format yang diperbolehkan: 
                        PDF, DOC, DOCX, XLS, XLSX, 
                        PPT, PPTX, JPG, JPEG, PNG, 
                        WEBP, ZIP, RAR. 
 
                        <br> 
 
                        Maksimal ukuran file: 
                        <strong>10 MB</strong>. 
 
                    </div> 
 
 
                </div> 
 
 
                <!-- ACTION --> 
 
                <div class="form-actions"> 
 
 
                    <a 
                        href="dokumen.php" 
                        class="btn btn-secondary"> 
 
                        Batal 
 
                    </a> 
 
 
                    <button 
                        type="submit" 
                        class="btn btn-primary"> 
 
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Dokumen 
 
                    </button> 
 
 
                </div> 
 
 
            </form> 
 
 
        </div> 
 
 
<!-- =====================================================
     JAVASCRIPT KHUSUS HALAMAN INI
===================================================== -->

<script>
 
 
    /* ===================================================== 
       FILE INPUT 
    ====================================================== */ 
 
    const fileDokumen = 
        document.getElementById( 
            "fileDokumen" 
        ); 
 
    const fileName = 
        document.getElementById( 
            "fileName" 
        ); 
 
 
    if (fileDokumen) { 
 
        fileDokumen?.addEventListener( 
            "change", 
            function() 
            { 
 
                if ( 
                    this.files && 
                    this.files.length > 0 
                ) { 
 
                    const file = 
                        this.files[0]; 
 
                    let ukuran = 
                        file.size; 
 
 
                    if (ukuran < 1024) { 
 
                        ukuran = 
                            ukuran + " B"; 
 
                    } 
 
                    else if ( 
                        ukuran < 1048576 
                    ) { 
 
                        ukuran = 
                            ( 
                                ukuran / 1024 
                            ).toFixed(1) 
                            + " KB"; 
 
                    } 
 
                    else { 
 
                        ukuran = 
                            ( 
                                ukuran / 
                                1048576 
                            ).toFixed(1) 
                            + " MB"; 
 
                    } 
 
 
                    fileName.textContent = 
                        file.name 
                        + " • " 
                        + ukuran; 
 
                } 
 
                else { 
 
                    fileName.textContent = 
                        "Belum ada file dipilih"; 
 
                } 
 
            } 
        ); 
 
    } 
 
 
    /* ===================================================== 
       DRAG & DROP 
    ====================================================== */ 
 
    const fileUpload = 
        document.getElementById( 
            "fileUpload" 
        ); 
 
 
    if (fileUpload) { 
 
 
        fileUpload?.addEventListener( 
            "dragover", 
            function(event) 
            { 
 
                event.preventDefault(); 
 
                this.style.borderColor = 
                    "#8f959d"; 
 
                this.style.background = 
                    "#f3f4f6"; 
 
            } 
        ); 
 
 
        fileUpload?.addEventListener( 
            "dragleave", 
            function() 
            { 
 
                this.style.borderColor = 
                    "#d9dde3"; 
 
                this.style.background = 
                    "#fafbfc"; 
 
            } 
 
        ); 
 
 
        fileUpload?.addEventListener( 
            "drop", 
            function(event) 
            { 
 
                event.preventDefault(); 
 
                this.style.borderColor = 
                    "#d9dde3"; 
 
                this.style.background = 
                    "#fafbfc"; 
 
 
                const files = 
                    event.dataTransfer.files; 
 
 
                if ( 
                    files.length > 0 
                ) { 
 
                    fileDokumen.files = 
                        files; 
 
 
                    const file = 
                        files[0]; 
 
                    let ukuran = 
                        file.size; 
 
 
                    if ( 
                        ukuran < 1024 
                    ) { 
 
                        ukuran = 
                            ukuran + " B"; 
 
                    } 
 
                    else if ( 
                        ukuran < 1048576 
                    ) { 
 
                        ukuran = 
                            ( 
                                ukuran / 1024 
                            ).toFixed(1) 
                            + " KB"; 
 
                    } 
 
                    else { 
 
                        ukuran = 
                            ( 
                                ukuran / 
                                1048576 
                            ).toFixed(1) 
                            + " MB"; 
 
                    } 
 
 
                    fileName.textContent = 
                        file.name 
                        + " • " 
                        + ukuran; 
 
                } 
 
            } 
 
        ); 
 
    } 
 
 
    /* ===================================================== 
       VALIDASI FORM 
    ====================================================== */ 
 
    const formDokumen = 
        document.getElementById( 
            "formDokumen" 
        ); 
 
 
    if (formDokumen) { 
 
        formDokumen?.addEventListener( 
            "submit", 
            function(event) 
            { 
 
                if ( 
                    fileDokumen && 
                    fileDokumen.files.length === 0 
                ) { 
 
                    event.preventDefault(); 
 
                    alert( 
                        "Silakan pilih file dokumen terlebih dahulu." 
                    ); 
 
                    return; 
 
                } 
 
 
                if ( 
                    fileDokumen && 
                    fileDokumen.files.length > 0 
                ) { 
 
                    const ukuran = 
                        fileDokumen.files[0].size; 
 
                    const maksimal = 
                        10 * 1024 * 1024; 
 
 
                    if ( 
                        ukuran > maksimal 
                    ) { 
 
                        event.preventDefault(); 
 
                        alert( 
                            "Ukuran file terlalu besar. Maksimal 10 MB." 
                        ); 
 
                    } 
 
                } 
 
            } 
        ); 
 
    } 
 
</script>

<?php require_once "../layouts/admin/kaki.php"; ?>
