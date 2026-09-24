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

if ($query_logo) {
    $data_logo = db_fetch_assoc($query_logo);
    $logo_sekolah = $data_logo['logo'] ?? '';
}
 
 
/* ========================================================= 
/* ========================================================= 
   DATA DASHBOARD 
========================================================= */ 
 
$total_guru = 0; 
$total_informasi = 0; 
$total_dokumen = 0; 
$total_galeri = 0; 
 
 
/* ========================================================= 
   TOTAL GURU 
========================================================= */ 
 
$query_guru = db_query( 
    $koneksi, 
    "SELECT COUNT(*) AS total FROM guru" 
); 
 
if ($query_guru) { 
    $data_guru = db_fetch_assoc($query_guru); 
    $total_guru = (int) ($data_guru['total'] ?? 0); 
} 
 
 
/* ========================================================= 
   TOTAL INFORMASI 
========================================================= */ 
 
$query_informasi = db_query( 
    $koneksi, 
    "SELECT COUNT(*) AS total FROM informasi" 
); 
 
if ($query_informasi) { 
    $data_informasi = db_fetch_assoc($query_informasi); 
    $total_informasi = (int) ($data_informasi['total'] ?? 0); 
} 
 
 
/* ========================================================= 
   TOTAL DOKUMEN 
========================================================= */ 
 
$query_dokumen = db_query( 
    $koneksi, 
    "SELECT COUNT(*) AS total FROM dokumen" 
); 
 
if ($query_dokumen) { 
    $data_dokumen = db_fetch_assoc($query_dokumen); 
    $total_dokumen = (int) ($data_dokumen['total'] ?? 0); 
} 
 
 
/* ========================================================= 
   TOTAL GALERI 
========================================================= */ 
 
$query_galeri = db_query( 
    $koneksi, 
    "SELECT COUNT(*) AS total FROM galeri" 
); 
 
if ($query_galeri) { 
    $data_galeri = db_fetch_assoc($query_galeri); 
} 
 
 
/* ========================================================= 
   DATA TERBARU INFORMASI 
========================================================= */ 
 
$informasi_terbaru = []; 
 
$query_info_terbaru = db_query( 
    $koneksi, 
    "SELECT * FROM informasi ORDER BY id DESC LIMIT 5" 
); 
 
if ($query_info_terbaru) { 
 
    while ($row = db_fetch_assoc($query_info_terbaru)) { 
        $informasi_terbaru[] = $row; 
    } 
 
} 
 
 
/* ========================================================= 
   DATA TERBARU DOKUMEN 
========================================================= */ 
 
$dokumen_terbaru = []; 
 
$query_dokumen_terbaru = db_query( 
    $koneksi, 
    "SELECT * FROM dokumen ORDER BY id DESC LIMIT 5" 
); 
 
if ($query_dokumen_terbaru) { 
 
    while ($row = db_fetch_assoc($query_dokumen_terbaru)) { 
        $dokumen_terbaru[] = $row; 
    } 
 
} 
 
 
/* ========================================================= 
   DATA TERBARU GALERI 
========================================================= */ 
 
$galeri_terbaru = []; 
 
$query_galeri_terbaru = db_query( 
    $koneksi, 
    "SELECT * FROM galeri ORDER BY id DESC LIMIT 5" 
); 
 
if ($query_galeri_terbaru) { 
 
    while ($row = db_fetch_assoc($query_galeri_terbaru)) { 
        $galeri_terbaru[] = $row; 
    } 
 
} 
 
?> 
 
<?php

/* =========================================================
   PENGATURAN TAMPILAN HALAMAN
   Bagian atas, menu, dan bagian bawah halaman diambil dari
   folder layouts/ supaya tidak ditulis berulang.
========================================================== */

$judul         = "Dashboard Admin | " . NAMA_SEKOLAH;

$css           = "../assets/css/admin/dashboard.css";

$menu_aktif    = "dashboard";

$judul_halaman = "Dashboard";

$subjudul      = "Panel administrasi website sekolah";

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
 
 
<!-- ========================================================= 
     MAIN 
 
 
        <!-- ================================================= 
             WELCOME 
        ================================================== --> 
 
        <div class="welcome"> 
 
            <h2> 
                Selamat Datang, <?= e($nama_admin) ?>  
            </h2> 
 
            <p> 
                Selamat datang di dashboard admin 
                <?= NAMA_SEKOLAH_PANJANG ?>. 
                Kelola informasi, guru & staf, dokumen, 
                galeri, dan profil sekolah melalui menu 
                navigasi di sebelah kiri. 
            </p> 
 
        </div> 
 
 
        <!-- ================================================= 
             STATISTIK 
        ================================================== --> 
 
        <div class="stats-grid"> 
 
 
            <!-- GURU --> 
 
            <div class="stat-card"> 
 
                <div class="stat-icon blue"> 
                    <i class="fa-solid fa-chalkboard-user"></i> 
                </div> 
 
                <div class="stat-info"> 
 
                    <strong> 
                        <?= $total_guru ?> 
                    </strong> 
 
                    <span> 
                        Guru & Staf 
                    </span> 
 
                </div> 
 
            </div> 
 
 
            <!-- INFORMASI --> 
 
            <div class="stat-card"> 
 
                <div class="stat-icon yellow"> 
                    <i class="fa-solid fa-bullhorn"></i> 
                </div> 
 
                <div class="stat-info"> 
 
                    <strong> 
                        <?= $total_informasi ?> 
                    </strong> 
 
                    <span> 
                        Informasi 
                    </span> 
 
                </div> 
 
            </div> 
 
 
            <!-- DOKUMEN --> 
 
            <div class="stat-card"> 
 
                <div class="stat-icon green"> 
                    <i class="fa-solid fa-folder-open"></i> 
                </div> 
 
                <div class="stat-info"> 
 
                    <strong> 
                        <?= $total_dokumen ?> 
                    </strong> 
 
                    <span> 
                        Dokumen 
                    </span> 
 
                </div> 
 
            </div> 
 
 
            <!-- GALERI --> 
 
            <div class="stat-card"> 
 
                <div class="stat-icon purple"> 
                    <i class="fa-solid fa-images"></i> 
                </div> 
 
                <div class="stat-info"> 
 
                    <strong> 
                        <?= $total_galeri ?> 
                    </strong> 
 
                    <span> 
                        Galeri 
                    </span> 
 
                </div> 
 
            </div> 
 
 
        </div> 
 
 
        <!-- ================================================= 
             GRID BAWAH 
        ================================================== --> 
 
        <div class="dashboard-grid"> 
 
 
            <!-- ================================================= 
                 INFORMASI TERBARU 
            ================================================== --> 
 
            <div class="card"> 
 
 
                <div class="card-header"> 
 
                    <div> 
 
                        <h2> 
                            Informasi Terbaru 
                        </h2> 
 
                        <p> 
                            Daftar informasi yang baru ditambahkan. 
                        </p> 
 
                    </div> 
 
                    <a 
                        href="informasi.php" 
                        class="card-link"> 
 
                        Lihat Semua 
 
                    </a> 
 
                </div> 
 
 
                <?php if (!empty($informasi_terbaru)): ?> 
 
 
                    <?php foreach ( 
                        $informasi_terbaru 
                        as $info 
                    ): ?> 
 
 
                        <div class="list-item"> 
 
 
                            <div class="list-main"> 
 
                                <strong> 
 
                                    <?= e( 
                                        $info['judul'] 
                                        ?? 'Tanpa judul' 
                                    ) ?> 
 
                                </strong> 
 
 
                                <span> 
 
                                    <?php 
 
                                    $tanggal = 
                                        $info['created_at'] 
                                        ?? ''; 
 
                                    if ($tanggal !== '') { 
 
                                        echo e( 
                                            date( 
                                                'd-m-Y', 
                                                strtotime($tanggal) 
                                            ) 
                                        ); 
 
                                    } else { 
 
                                        echo 'Informasi sekolah'; 
 
                                    } 
 
                                    ?> 
 
                                </span> 
 
                            </div> 
 
 
                            <span class="badge badge-blue"> 
                                Informasi 
                            </span> 
 
 
                        </div> 
 
 
                    <?php endforeach; ?> 
 
 
                <?php else: ?> 
 
 
                    <div class="empty"> 
 
                        Belum ada informasi. 
 
                    </div> 
 
                <?php endif; ?> 
 
            </div> 
 
 
            <!-- ================================================= 
                 DOKUMEN TERBARU 
            ================================================== --> 
 
            <div class="card"> 
 
 
                <div class="card-header"> 
 
                    <div> 
 
                        <h2> 
                            Dokumen Terbaru 
                        </h2> 
 
                        <p> 
                            Dokumen yang baru ditambahkan. 
                        </p> 
 
                    </div> 
 
                    <a 
                        href="dokumen.php" 
                        class="card-link"> 
 
                        Lihat Semua 
 
                    </a> 
 
                </div> 
 
 
                <?php if (!empty($dokumen_terbaru)): ?> 
 
 
                    <?php foreach ( 
                        $dokumen_terbaru 
                        as $dokumen 
                    ): ?> 
 
 
                        <div class="list-item"> 
 
 
                            <div class="list-main"> 
 
                                <strong> 
 
                                    <?= e( 
                                        $dokumen['judul'] 
                                        ?? 'Tanpa judul' 
                                    ) ?> 
 
                                </strong> 
 
 
                                <span> 
 
                                    <?= e( 
                                        $dokumen['kategori'] 
                                        ?? 'Dokumen sekolah' 
                                    ) ?> 
 
                                </span> 
 
                            </div> 
 
 
                            <span class="badge badge-yellow"> 
                                Dokumen 
                            </span> 
 
 
                        </div> 
 
 
                    <?php endforeach; ?> 
 
 
                <?php else: ?> 
 
 
                    <div class="empty"> 
 
                        Belum ada dokumen. 
 
                    </div> 
 
                <?php endif; ?> 
 
 
            </div> 
 
 
        </div> 
 
 
        <!-- ================================================= 
             GALERI + MENU CEPAT 
        ================================================== --> 
 
        <div class="dashboard-grid"> 
 
 
            <!-- ================================================= 
                 GALERI TERBARU 
            ================================================== --> 
 
            <div class="card"> 
 
 
                <div class="card-header"> 
 
                    <div> 
 
                        <h2> 
                            Galeri Terbaru 
                        </h2> 
 
                        <p> 
                            Foto yang baru ditambahkan. 
                        </p> 
 
                    </div> 
 
                    <a 
                        href="galeri.php" 
                        class="card-link"> 
 
                        Lihat Semua 
 
                    </a> 
 
                </div> 
 
 
                <?php if (!empty($galeri_terbaru)): ?> 
 
 
                    <?php foreach ( 
                        $galeri_terbaru 
                        as $galeri 
                    ): ?> 
 
 
                        <div class="list-item"> 
 
 
                            <div class="list-main"> 
 
                                <strong> 
 
                                    <?= e( 
                                        $galeri['judul'] 
                                        ?? 'Tanpa judul' 
                                    ) ?> 
 
                                </strong> 
 
 
                                <span> 
 
                                    <?= e( 
                                        $galeri['keterangan'] 
                                        ?? 'Galeri sekolah' 
                                    ) ?> 
 
                                </span> 
 
                            </div> 
 
 
                            <span class="badge badge-blue"> 
                                Galeri 
                            </span> 
 
 
                        </div> 
 
 
                    <?php endforeach; ?> 
 
 
                <?php else: ?> 
 
 
                    <div class="empty"> 
 
                        Belum ada foto galeri. 
 
                    </div> 
 
                <?php endif; ?> 
 
 
            </div> 
 
 
            <!-- ================================================= 
                 MENU CEPAT 
            ================================================== --> 
 
            <div class="card"> 
 
 
                <div class="card-header"> 
 
                    <div> 
 
                        <h2> 
                            Menu Cepat 
                        </h2> 
 
                        <p> 
                            Akses pengelolaan website. 
                        </p> 
 
                    </div> 
 
                </div> 
 
 
                <div class="quick-grid"> 
 
 
                    <a 
                        href="profil.php" 
                        class="quick-item"> 
 
                        <div class="quick-icon"> 
                            <i class="fa-solid fa-school"></i> 
                        </div> 
 
                        <span> 
                            Profil Sekolah 
                        </span> 
 
                    </a> 
 
 
                    <a 
                        href="guru.php" 
                        class="quick-item"> 
 
                        <div class="quick-icon"> 
                            <i class="fa-solid fa-chalkboard-user"></i> 
                        </div> 
 
                        <span> 
                            Guru & Staf 
                        </span> 
 
                    </a> 
 
 
                    <a 
                        href="informasi.php" 
                        class="quick-item"> 
 
                        <div class="quick-icon"> 
                            <i class="fa-solid fa-bullhorn"></i> 
                        </div> 
 
                        <span> 
                            Informasi 
                        </span> 
 
                    </a> 
 
 
                    <a 
                        href="dokumen.php" 
                        class="quick-item"> 
 
                        <div class="quick-icon"> 
                            <i class="fa-solid fa-folder-open"></i> 
                        </div> 
 
                        <span> 
                            Dokumen 
                        </span> 
 
                    </a> 
 
 
                    <a 
                        href="galeri.php" 
                        class="quick-item"> 
 
                        <div class="quick-icon"> 
                            <i class="fa-solid fa-images"></i> 
                        </div> 
 
                        <span> 
                            Galeri 
                        </span> 
 
                    </a> 
 
 
                </div> 
 
 
            </div> 
 
 
        </div> 
 
 
<?php require_once "../layouts/admin/kaki.php"; ?>
