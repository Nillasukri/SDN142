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

if ($query_logo && db_num_rows($query_logo) > 0) {

    $data_logo = db_fetch_assoc($query_logo);

    $logo_sekolah = $data_logo['logo'] ?? '';
}
 
 
/* 
|-------------------------------------------------------------------------- 
| Pencarian 
|-------------------------------------------------------------------------- 
*/ 
 
$keyword = isset($_GET["keyword"]) 
    ? trim($_GET["keyword"]) 
    : ""; 
 
 
/* 
|-------------------------------------------------------------------------- 
| Query Galeri 
|-------------------------------------------------------------------------- 
*/ 
 
$sql = " 
    SELECT 
        id, 
        judul, 
        foto, 
        keterangan, 
        created_at 
    FROM galeri 
    WHERE 1=1 
"; 
 
$params = []; 
$types = ""; 
 
if ($keyword !== "") { 
 
    $sql .= " 
        AND ( 
            judul LIKE ? 
            OR keterangan LIKE ? 
        ) 
    "; 
 
    $search = "%" . $keyword . "%"; 
 
    $params[] = $search; 
    $params[] = $search; 
 
    $types = "ss"; 
} 
 
$sql .= " 
    ORDER BY id DESC 
"; 
 
 
$stmt = db_prepare( 
    $koneksi, 
    $sql 
); 
 
if (!$stmt) { 
    die("Query galeri gagal diproses."); 
} 
 
 
if (!empty($params)) { 
 
    db_stmt_bind_param( 
        $stmt, 
        $types, 
        ...$params 
    ); 
} 
 
 
db_stmt_execute($stmt); 
 
$result = db_stmt_get_result($stmt); 
 
 
/* 
|-------------------------------------------------------------------------- 
| Total Galeri 
|-------------------------------------------------------------------------- 
*/ 
 
$total_galeri = 0; 
 
$query_total = db_query( 
    $koneksi, 
    "SELECT COUNT(*) AS total FROM galeri" 
); 
 
if ($query_total) { 
 
    $data_total = 
        db_fetch_assoc( 
            $query_total 
        ); 
 
    $total_galeri = 
        (int)($data_total["total"] ?? 0); 
} 
 
 
/* 
|-------------------------------------------------------------------------- 
| Fungsi Tanggal Indonesia 
|-------------------------------------------------------------------------- 
*/ 
 
function tanggalIndonesia($tanggal) 
{ 
    if (!$tanggal) { 
        return "-"; 
    } 
 
    $bulan = [ 
        1  => "Januari", 
        2  => "Februari", 
        3  => "Maret", 
        4  => "April", 
        5  => "Mei", 
        6  => "Juni", 
        7  => "Juli", 
        8  => "Agustus", 
        9  => "September", 
        10 => "Oktober", 
        11 => "November", 
        12 => "Desember" 
    ]; 
 
    $timestamp = strtotime($tanggal); 
 
    if (!$timestamp) { 
        return $tanggal; 
    } 
 
    $hari = 
        date("d", $timestamp); 
 
    $bulan_angka = 
        (int)date("m", $timestamp); 
 
    $tahun = 
        date("Y", $timestamp); 
 
    return 
        $hari 
        . " " 
        . $bulan[$bulan_angka] 
        . " " 
        . $tahun; 
} 
 
 
/* 
|-------------------------------------------------------------------------- 
| Pesan Status 
|-------------------------------------------------------------------------- 
*/ 
 
$pesan = pesan_status("galeri"); 

$status_message = $pesan["teks"]; 

$status_type    = $pesan["jenis"]; 

$status_ikon    = $pesan["ikon"]; 

?> 
<?php

/* =========================================================
   PENGATURAN TAMPILAN HALAMAN
   Bagian atas, menu, dan bagian bawah halaman diambil dari
   folder layouts/ supaya tidak ditulis berulang.
========================================================== */

$judul         = "Galeri | Admin " . NAMA_SEKOLAH_PANJANG;

$css           = "../assets/css/admin/galeri.css";

$menu_aktif    = "galeri";

$judul_halaman = "Galeri";

$subjudul      = "Kelola foto kegiatan sekolah";

$footer_admin  = true;      /* halaman ini memakai tulisan footer */

require_once "../layouts/admin/kepala.php";
require_once "../layouts/admin/sidebar.php";
require_once "../layouts/admin/topbar.php";
?>
 
 
<!-- ================================================== 
     SIDEBAR 
     PERSIS SEPERTI DASHBOARD 
================================================== --> 
 
<div 
    class="overlay" 
    id="overlay" 
></div> 
 
 
<!-- ================================================== 
     MAIN 
 
 
        <!-- PAGE HEADER --> 
 
        <div class="page-header"> 
 
 
            <div class="page-header-left"> 
 
 
                <h2> 
                    Galeri Sekolah 
                </h2> 
 
 
                <p> 
                    Kelola dokumentasi foto kegiatan 
                    dan aktivitas <?= NAMA_SEKOLAH_PANJANG ?>. 
                </p> 
 
 
            </div> 
 
 
            <a 
                href="tambah_galeri.php" 
                class="add-button" 
            > 
 
                <i class="fa-solid fa-plus"></i> Tambah Foto 
 
            </a> 
 
 
        </div> 
 
 
        <!-- STATUS --> 
 
        <?php if ($status_message !== ""): ?> 
 
 
            <div class="alert alert-<?= e($status_type) ?>"> 
 
                <i class="fa-solid <?= e($status_ikon) ?>"></i> 
                <?= e($status_message) ?> 
 
            </div> 
 
 
        <?php endif; ?> 
 
 
        <!-- ================================================== 
             STAT 
        ================================================== --> 
 
        <div class="stat-card"> 
 
 
            <div class="stat-icon"> 
 
                <i class="fa-solid fa-images"></i> 
 
            </div> 
 
 
            <div class="stat-info"> 
 
 
                <span> 
                    Total Foto Galeri 
                </span> 
 
 
                <strong> 
                    <?= $total_galeri ?> 
                </strong> 
 
 
            </div> 
 
 
        </div> 
 
 
        <!-- ================================================== 
             FILTER 
        ================================================== --> 
 
        <div class="filter-card"> 
 
 
            <form 
                method="GET" 
                class="search-form" 
            > 
 
 
                <div class="search-box"> 
 
 
                    <span class="search-icon"> 
                        <i class="fa-solid fa-magnifying-glass"></i> 
                    </span> 
 
 
                    <input 
                        type="search" 
                        name="keyword" 
                        value="<?= e($keyword) ?>" 
                        placeholder="Cari judul atau keterangan foto..." 
                    > 
 
 
                </div> 
 
 
                <button 
                    type="submit" 
                    class="search-button" 
                > 
 
                    Cari 
 
                </button> 
 
 
                <?php if ($keyword !== ""): ?> 
 
 
                    <a 
                        href="galeri.php" 
                        class="reset-button" 
                    > 
 
                        Reset 
 
                    </a> 
 
 
                <?php endif; ?> 
 
 
            </form> 
 
 
        </div> 
 
 
        <!-- ================================================== 
             GALLERY 
        ================================================== --> 
 
        <div class="gallery-grid"> 
 
 
            <?php if ( 
                $result && 
                db_num_rows($result) > 0 
            ): ?> 
 
 
                <?php while ( 
                    $row = db_fetch_assoc($result) 
                ): ?> 
 
 
                    <?php 
 
                    $foto = 
                        trim( 
                            $row["foto"] ?? "" 
                        ); 
 
                    $foto_url = 
                        "../uploads/galeri/" 
                        . rawurlencode($foto); 
 
                    ?> 
 
 
                    <article class="gallery-card"> 
 
 
                        <!-- FOTO --> 
 
                        <div class="gallery-photo"> 
 
 
                            <?php if ($foto !== ""): ?> 
 
 
                                <img 
                                    src="<?= e($foto_url) ?>" 
                                    alt="<?= e( 
                                        $row["judul"] 
                                    ) ?>" 
                                    loading="lazy" 
                                    onerror=" 
                                        this.style.display='none'; 
                                        this.nextElementSibling.style.display='flex'; 
                                    " 
                                > 
 
 
                                <div 
                                    style=" 
                                        display:none; 
                                        position:absolute; 
                                        inset:0; 
                                        align-items:center; 
                                        justify-content:center; 
                                        flex-direction:column; 
                                        gap:8px; 
                                        color:#858990; 
                                        font-size:12px; 
                                        text-align:center; 
                                    " 
                                > 
 
                                    <span 
                                        style="font-size:30px;" 
                                    > 
                                        <i class="fa-solid fa-images"></i> 
                                    </span> 
 
                                    Foto tidak ditemukan 
 
                                </div> 
 
 
                                <div 
                                    class="photo-overlay" 
                                ></div> 
 
 
                                <a 
                                    href="<?= e($foto_url) ?>" 
                                    target="_blank" 
                                    rel="noopener noreferrer" 
                                    class="photo-view" 
                                    title="Lihat foto" 
                                > 
 
                                    <i class="fa-solid fa-eye"></i> 
 
                                </a> 
 
 
                            <?php else: ?> 
 
 
                                <div 
                                    style=" 
                                        height:100%; 
                                        display:flex; 
                                        align-items:center; 
                                        justify-content:center; 
                                        flex-direction:column; 
                                        gap:8px; 
                                        color:#858990; 
                                        font-size:12px; 
                                    " 
                                > 
 
                                    <span 
                                        style="font-size:34px;" 
                                    > 
                                        <i class="fa-solid fa-images"></i> 
                                    </span> 
 
                                    Foto tidak tersedia 
 
                                </div> 
 
 
                            <?php endif; ?> 
 
 
                        </div> 
 
 
                        <!-- CONTENT --> 
 
                        <div class="gallery-content"> 
 
 
                            <h3> 
 
                                <?= e( 
                                    $row["judul"] 
                                    ?: "Tanpa Judul" 
                                ) ?> 
 
                            </h3> 
 
 
                            <div class="gallery-description"> 
 
 
                                <?php if ( 
                                    trim( 
                                        $row["keterangan"] 
                                        ?? "" 
                                    ) !== "" 
                                ): ?> 
 
 
                                    <?= e( 
                                        $row["keterangan"] 
                                    ) ?> 
 
 
                                <?php else: ?> 
 
 
                                    Tidak ada keterangan. 
 
 
                                <?php endif; ?> 
 
 
                            </div> 
 
 
                            <div class="gallery-date"> 
 
 
                                <i class="fa-solid fa-calendar-days"></i> 
 
                                <?= e( 
                                    tanggalIndonesia( 
                                        $row["created_at"] 
                                    ) 
                                ) ?> 
 
 
                            </div> 
 
 
                            <!-- ACTION --> 
 
                            <div class="gallery-actions"> 
 
 
                                <a 
                                    href="edit_galeri.php?id=<?= (int)$row["id"] ?>" 
                                    class="action-button action-edit" 
                                > 
 
                                    <i class="fa-solid fa-pen-to-square"></i> Edit 
 
                                </a> 
 
 
                                <a 
                                    href="hapus_galeri.php?id=<?= (int)$row["id"] ?>" 
                                    class="action-button action-delete" 
                                    onclick=" 
                                        return confirm( 
                                            'Yakin ingin menghapus foto ini? File foto juga akan dihapus dari server.' 
                                        ); 
                                    " 
                                > 
 
                                    <i class="fa-solid fa-trash"></i> Hapus 
 
                                </a> 
 
 
                            </div> 
 
 
                        </div> 
 
 
                    </article> 
 
 
                <?php endwhile; ?> 
 
 
            <?php else: ?> 
 
 
                <!-- EMPTY STATE --> 
 
                <div class="empty-state"> 
 
 
                    <div class="empty-icon"> 
 
                        <i class="fa-solid fa-images"></i> 
 
                    </div> 
 
 
                    <?php if ($keyword !== ""): ?> 
 
 
                        <h3> 
                            Foto tidak ditemukan 
                        </h3> 
 
 
                        <p> 
 
                            Tidak ada foto yang sesuai 
                            dengan pencarian 
                            "<strong><?= e($keyword) ?></strong>". 
 
                        </p> 
 
 
                        <a 
                            href="galeri.php" 
                            class="empty-add" 
                        > 
 
                            Reset Pencarian 
 
                        </a> 
 
 
                    <?php else: ?> 
 
 
                        <h3> 
                            Belum Ada Foto 
                        </h3> 
 
 
                        <p> 
 
                            Belum ada foto kegiatan 
                            sekolah yang ditambahkan 
                            ke galeri. 
 
                        </p> 
 
 
                        <a 
                            href="tambah_galeri.php" 
                            class="empty-add" 
                        > 
 
                            <i class="fa-solid fa-plus"></i> Tambah Foto Pertama 
 
                        </a> 
 
 
                    <?php endif; ?> 
 
 
                </div> 
 
 
            <?php endif; ?> 
 
 
        </div> 
 
 
<?php require_once "../layouts/admin/kaki.php"; ?>
 
 
</body> 
</html>