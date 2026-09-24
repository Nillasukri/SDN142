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

if ($query_logo && db_num_rows($query_logo) > 0) {

    $data_logo = db_fetch_assoc($query_logo);

    $logo_sekolah = $data_logo["logo"] ?? '';
}
 
/* ========================================================= 
   PENCARIAN & FILTER 
========================================================= */ 
 
$keyword = isset($_GET["keyword"]) 
    ? trim($_GET["keyword"]) 
    : ""; 
 
$kategori_filter = isset($_GET["kategori"]) 
    ? trim($_GET["kategori"]) 
    : ""; 
 
/* ========================================================= 
   QUERY DOKUMEN 
========================================================= */ 
 
$sql = " 
    SELECT 
        id, 
        judul, 
        kategori, 
        deskripsi, 
        nama_file, 
        tanggal, 
        created_at 
    FROM dokumen 
    WHERE 1=1 
"; 
 
$params = []; 
$types = ""; 
 
/* Pencarian */ 
if ($keyword !== "") { 
 
    $sql .= " 
        AND ( 
            judul LIKE ? 
            OR kategori LIKE ? 
            OR deskripsi LIKE ? 
            OR nama_file LIKE ? 
        ) 
    "; 
 
    $search = "%" . $keyword . "%"; 
 
    $params[] = $search; 
    $params[] = $search; 
    $params[] = $search; 
    $params[] = $search; 
 
    $types .= "ssss"; 
} 
 
/* Filter kategori */ 
if ($kategori_filter !== "") { 
 
    $sql .= " AND kategori = ?"; 
 
    $params[] = $kategori_filter; 
 
    $types .= "s"; 
} 
 
$sql .= " ORDER BY tanggal DESC, id DESC"; 
 
/* ========================================================= 
   EKSEKUSI QUERY 
========================================================= */ 
 
$stmt = db_prepare($koneksi, $sql); 
 
if (!$stmt) { 
    die("Query gagal diproses."); 
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
 
/* ========================================================= 
   AMBIL KATEGORI 
========================================================= */ 
 
$kategori_result = db_query( 
    $koneksi, 
    " 
    SELECT DISTINCT kategori 
    FROM dokumen 
    WHERE kategori IS NOT NULL 
      AND kategori != '' 
    ORDER BY kategori ASC 
    " 
); 
 
/* ========================================================= 
   TOTAL DOKUMEN 
========================================================= */ 
 
$total_dokumen = 0; 
 
$query_total = db_query( 
    $koneksi, 
    "SELECT COUNT(*) AS total FROM dokumen" 
); 
 
if ($query_total) { 
 
    $data_total = db_fetch_assoc($query_total); 
 
    $total_dokumen = (int)( 
        $data_total["total"] ?? 0 
    ); 
} 
 
/* ========================================================= 
   FORMAT TANGGAL INDONESIA 
========================================================= */ 
 
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
 
    $hari = date("d", $timestamp); 
    $bulan_angka = (int)date("m", $timestamp); 
    $tahun = date("Y", $timestamp); 
 
    return $hari . " " 
        . $bulan[$bulan_angka] 
        . " " 
        . $tahun; 
} 
 
/* ========================================================= 
   UKURAN FILE 
========================================================= */ 
 
function ukuranFile($nama_file) 
{ 
    $lokasi_file = 
        "../uploads/dokumen/" 
        . $nama_file; 
 
    if (!unggah_ada($lokasi_file)) { 
        return "-"; 
    } 
 
    $ukuran = unggah_ukuran($lokasi_file); 
 
    if ($ukuran < 1024) { 
        return $ukuran . " B"; 
    } 
 
    if ($ukuran < 1048576) { 
        return round( 
            $ukuran / 1024, 
            1 
        ) . " KB"; 
    } 
 
    return round( 
        $ukuran / 1048576, 
        1 
    ) . " MB"; 
} 
 
/* ========================================================= 
   ICON FILE 
========================================================= */ 
 
function iconFile($nama_file) 
{ 
    $extension = strtolower( 
        pathinfo( 
            $nama_file, 
            PATHINFO_EXTENSION 
        ) 
    ); 
 
    switch ($extension) { 
 
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
 
        case "jpg": 
        case "jpeg": 
        case "png": 
        case "webp": 
            return "fa-file-image"; 
 
        case "zip": 
        case "rar": 
            return "fa-file-zipper"; 
 
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

$judul         = "Dokumen | Admin " . NAMA_SEKOLAH_PANJANG;

$css           = "../assets/css/admin/dokumen.css";

$menu_aktif    = "dokumen";

$judul_halaman = "Dokumen";

$subjudul      = "Kelola dokumen dan file sekolah";

/* =========================================================
   PESAN STATUS
========================================================= */

$pesan = pesan_status('dokumen');

$status_message = $pesan['teks'];

$status_type    = $pesan['jenis'];

$status_ikon    = $pesan['ikon'];


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
 
 
    <!-- ===================================================== 
         MAIN 
 
 
            <!-- ================================================= 
                 PAGE HEADER 
            ================================================== --> 
 
            <div class="page-header"> 
 
 
                <div> 
 
                    <h2> 
                        Dokumen Sekolah 
                    </h2> 
 
                    <p> 
                        Kelola file, surat, formulir, 
                        sertifikat, jadwal, dan dokumen 
                        administrasi sekolah. 
                    </p> 
 
                </div> 
 
 
                <!-- ================================================= 
                     TOMBOL TAMBAH DOKUMEN ATAS 
                     TETAP DIPERTAHANKAN 
                ================================================== --> 
 
                <a 
                    href="tambah_dokumen.php" 
                    class="btn-primary" 
                > 
 
                    <span> 
                        <i class="fa-solid fa-plus"></i> 
                    </span> 
 
                    Tambah Dokumen 
 
                </a> 
 
 
            </div> 
 
 
            <!-- ================================================= 
                 STATUS 
            ================================================== --> 
 
            <?php if ($status_message !== ""): ?> 
 
                <div class="alert alert-<?= e($status_type) ?>"> 
 
                    <i class="fa-solid <?= e($status_ikon) ?>"></i> 
                    <?= e($status_message) ?> 
 
                </div> 
 
            <?php endif; ?> 
 
 
            <!-- ================================================= 
                 INFO 
            ================================================== --> 
 
            <div class="info-card"> 
 
 
                <div class="info-icon"> 
                    <i class="fa-solid fa-folder-open"></i> 
                </div> 
 
 
                <div> 
 
                    <strong> 
                        Manajemen Dokumen 
                    </strong> 
 
                    <p> 
                        Dokumen yang ditambahkan di halaman 
                        admin akan tersedia pada halaman 
                        dokumen website sekolah untuk 
                        diunduh oleh pengguna. 
                    </p> 
 
                </div> 
 
 
            </div> 
 
 
            <!-- ================================================= 
                 FILTER 
            ================================================== --> 
 
            <div class="filter-card"> 
 
 
                <form 
                    method="GET" 
                    action="" 
                    class="filter-form" 
                > 
 
 
                    <!-- PENCARIAN --> 
 
                    <div class="form-group"> 
 
                        <label for="keyword"> 
                            Cari Dokumen 
                        </label> 
 
                        <input 
                            type="text" 
                            name="keyword" 
                            id="keyword" 
                            class="input-control" 
                            placeholder="Cari judul, file, kategori..." 
                            value="<?= e($keyword) ?>" 
                        > 
 
                    </div> 
 
 
                    <!-- KATEGORI --> 
 
                    <div class="form-group"> 
 
                        <label for="kategori"> 
                            Kategori 
                        </label> 
 
                        <select 
                            name="kategori" 
                            id="kategori" 
                            class="input-control" 
                        > 
 
                            <option value=""> 
                                Semua Kategori 
                            </option> 
 
 
                            <?php if ($kategori_result): ?> 
 
                                <?php while ( 
                                    $kategori = 
                                    db_fetch_assoc( 
                                        $kategori_result 
                                    ) 
                                ): ?> 
 
 
                                    <option 
                                        value="<?= e($kategori["kategori"]) ?>" 
                                        <?= ( 
                                            $kategori_filter === 
                                            $kategori["kategori"] 
                                        ) 
                                            ? "selected" 
                                            : "" 
                                        ?> 
                                    > 
 
                                        <?= e( 
                                            $kategori["kategori"] 
                                        ) ?> 
 
                                    </option> 
 
 
                                <?php endwhile; ?> 
 
                            <?php endif; ?> 
 
 
                        </select> 
 
                    </div> 
 
 
                    <!-- BUTTON FILTER --> 
 
                    <div class="filter-actions"> 
 
 
                        <button 
                            type="submit" 
                            class="btn-filter" 
                        > 
                            <i class="fa-solid fa-magnifying-glass"></i> Cari 
                        </button> 
 
 
                        <a 
                            href="dokumen.php" 
                            class="btn-reset" 
                        > 
                            Reset 
                        </a> 
 
 
                    </div> 
 
 
                </form> 
 
 
            </div> 
 
 
            <!-- ================================================= 
                 DOCUMENT PANEL 
            ================================================== --> 
 
            <section class="document-panel"> 
 
 
                <!-- PANEL HEADER --> 
 
                <div class="panel-header"> 
 
 
                    <div> 
 
                        <h3> 
                            Daftar Dokumen 
                        </h3> 
 
                        <p> 
                            Dokumen yang tersimpan 
                            di database sekolah. 
                        </p> 
 
                    </div> 
 
 
                    <div class="total-badge"> 
 
                        Total 
                        <?= $total_dokumen ?> 
                        Dokumen 
 
                    </div> 
 
 
                </div> 
 
 
                <!-- TABLE --> 
 
                <div class="table-wrap"> 
 
 
                    <table> 
 
 
                        <thead> 
 
                            <tr> 
 
                                <th> 
                                    Dokumen 
                                </th> 
 
                                <th> 
                                    Kategori 
                                </th> 
 
                                <th> 
                                    Deskripsi 
                                </th> 
 
                                <th> 
                                    Tanggal 
                                </th> 
 
                                <th> 
                                    Ukuran 
                                </th> 
 
                                <th> 
                                    Aksi 
                                </th> 
 
                            </tr> 
 
                        </thead> 
 
 
                        <tbody> 
 
 
                            <?php if ( 
                                $result && 
                                db_num_rows($result) > 0 
                            ): ?> 
 
 
                                <?php while ( 
                                    $row = 
                                    db_fetch_assoc($result) 
                                ): ?> 
 
 
                                    <tr> 
 
 
                                        <!-- DOKUMEN --> 
 
                                        <td> 
 
                                            <div 
                                                class="document-name" 
                                            > 
 
 
                                                <div 
                                                    class="file-icon" 
                                                > 
 
                                                    <i class="fa-solid <?= e(iconFile($row["nama_file"])) ?>"></i> 
 
                                                </div> 
 
 
                                                <div> 
 
 
                                                    <div 
                                                        class="document-title" 
                                                    > 
 
                                                        <?= e( 
                                                            $row["judul"] 
                                                        ) ?> 
 
                                                    </div> 
 
 
                                                    <div 
                                                        class="document-file" 
                                                        title="<?= e($row["nama_file"]) ?>" 
                                                    > 
 
                                                        <?= e( 
                                                            $row["nama_file"] 
                                                        ) ?> 
 
                                                    </div> 
 
 
                                                </div> 
 
 
                                            </div> 
 
                                        </td> 
 
 
                                        <!-- KATEGORI --> 
 
                                        <td> 
 
                                            <span 
                                                class="category-badge" 
                                            > 
 
                                                <?= e( 
                                                    $row["kategori"] 
                                                ) ?> 
 
                                            </span> 
 
                                        </td> 
 
 
                                        <!-- DESKRIPSI --> 
 
                                        <td> 
 
                                            <div 
                                                class="description" 
                                            > 
 
                                                <?php 
 
                                                $deskripsi = 
                                                    trim( 
                                                        $row["deskripsi"] 
                                                        ?? "" 
                                                    ); 
 
 
                                                if ( 
                                                    $deskripsi === "" 
                                                ) { 
 
                                                    echo "-"; 
 
                                                } else { 
 
 
                                                    if ( 
                                                        strlen( 
                                                            $deskripsi 
                                                        ) > 100 
                                                    ) { 
 
                                                        echo e( 
                                                            substr( 
                                                                $deskripsi, 
                                                                0, 
                                                                100 
                                                            ) 
                                                        ) . "..."; 
 
                                                    } else { 
 
                                                        echo e( 
                                                            $deskripsi 
                                                        ); 
 
                                                    } 
 
                                                } 
 
                                                ?> 
 
                                            </div> 
 
                                        </td> 
 
 
                                        <!-- TANGGAL --> 
 
                                        <td> 
 
                                            <div class="date"> 
 
                                                <?= e( 
                                                    tanggalIndonesia( 
                                                        $row["tanggal"] 
                                                    ) 
                                                ) ?> 
 
                                            </div> 
 
                                        </td> 
 
 
                                        <!-- UKURAN --> 
 
                                        <td> 
 
                                            <div class="size"> 
 
                                                <?= e( 
                                                    ukuranFile( 
                                                        $row["nama_file"] 
                                                    ) 
                                                ) ?> 
 
                                            </div> 
 
                                        </td> 
 
 
                                        <!-- AKSI --> 
 
                                        <td> 
 
                                            <div class="actions"> 
 
 
                                                <!-- LIHAT / DOWNLOAD --> 
 
                                                <a 
                                                    href="../uploads/dokumen/<?= rawurlencode($row["nama_file"]) ?>" 
                                                    target="_blank" 
                                                    class="action-btn action-view" 
                                                    title="Lihat / Download" 
                                                > 
                                                    <i class="fa-solid fa-eye"></i> 
                                                </a> 
 
 
                                                <!-- EDIT --> 
 
                                                <a 
                                                    href="edit_dokumen.php?id=<?= (int)$row["id"] ?>" 
                                                    class="action-btn action-edit" 
                                                    title="Edit Dokumen" 
                                                > 
                                                    <i class="fa-solid fa-pen-to-square"></i> 
                                                </a> 
 
 
                                                <!-- HAPUS --> 
 
                                                <a 
                                                    href="hapus_dokumen.php?id=<?= (int)$row["id"] ?>" 
                                                    class="action-btn action-delete" 
                                                    title="Hapus Dokumen" 
                                                    onclick="return konfirmasiHapus();" 
                                                > 
                                                    <i class="fa-solid fa-trash"></i> 
                                                </a> 
 
 
                                            </div> 
 
                                        </td> 
 
 
                                    </tr> 
 
 
                                <?php endwhile; ?> 
 
 
                            <?php else: ?> 
 
 
                                <!-- ================================================= 
                                     EMPTY 
                                     TOMBOL TAMBAH DOKUMEN BAWAH DIHAPUS 
                                ================================================== --> 
 
                                <tr> 
 
                                    <td 
                                        colspan="6" 
                                        class="empty" 
                                    > 
 
 
                                        <div 
                                            class="empty-icon" 
                                        > 
                                            <i class="fa-solid fa-folder-open"></i> 
                                        </div> 
 
 
                                        <h3> 
                                            Dokumen belum ditemukan 
                                        </h3> 
 
 
                                        <p> 
 
 
                                            <?php if ( 
                                                $keyword !== "" || 
                                                $kategori_filter !== "" 
                                            ): ?> 
 
                                                Tidak ada dokumen 
                                                yang sesuai dengan 
                                                pencarian atau filter. 
 
 
                                            <?php else: ?> 
 
                                                Belum ada dokumen 
                                                yang ditambahkan 
                                                ke dalam sistem. 
 
 
                                            <?php endif; ?> 
 
 
                                        </p> 
 
 
                                        <!-- ================================================= 
                                             TOMBOL TAMBAH DOKUMEN BAWAH DIHAPUS 
                                             JIKA ADA FILTER, TETAP TAMPILKAN RESET 
                                        ================================================== --> 
 
                                        <?php if ( 
                                            $keyword !== "" || 
                                            $kategori_filter !== "" 
                                        ): ?> 
 
 
                                            <a 
                                                href="dokumen.php" 
                                                class="btn-reset" 
                                            > 
 
                                                Tampilkan Semua 
 
                                            </a> 
 
 
                                        <?php endif; ?> 
 
 
                                    </td> 
 
                                </tr> 
 
 
                            <?php endif; ?> 
 
 
                        </tbody> 
 
 
                    </table> 
 
 
                </div> 
 
 
            </section> 
 
 
            <!-- ================================================= 
                 FOOTER 
            ================================================== --> 
 
            <div class="footer"> 
 
                © <?= date("Y") ?> 
 
                <?= NAMA_SEKOLAH_PANJANG ?> 
 
                · Admin Dokumen 
 
            </div> 
 
 
<!-- =====================================================
     JAVASCRIPT KHUSUS HALAMAN INI
===================================================== -->

<script>
 
 
        /* ===================================================== 
           KONFIRMASI HAPUS 
        ===================================================== */ 
 
        function konfirmasiHapus() 
        { 
            return confirm( 
                "Yakin ingin menghapus dokumen ini?" 
            ); 
        } 
 
 
</script>

<?php require_once "../layouts/admin/kaki.php"; ?>
