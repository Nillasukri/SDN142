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
   FILTER PENCARIAN  
========================= */  
  
$keyword = trim($_GET['keyword'] ?? '');  
  
  
/* =========================  
   QUERY INFORMASI  
========================= */  
  
if ($keyword !== '') {  
  
    $sql = "  
        SELECT *  
        FROM informasi  
        WHERE judul LIKE ?  
           OR isi LIKE ?  
        ORDER BY tanggal DESC, id DESC  
    ";  
  
    $stmt = db_prepare($koneksi, $sql);  
  
    if (!$stmt) {  
        die("Query gagal diproses.");  
    }  
  
    $search = "%" . $keyword . "%";  
  
    db_stmt_bind_param(  
        $stmt,  
        "ss",  
        $search,  
        $search  
    );  
  
    db_stmt_execute($stmt);  
  
    $result = db_stmt_get_result($stmt);  
  
} else {  
  
    $sql = "  
        SELECT *  
        FROM informasi  
        ORDER BY tanggal DESC, id DESC  
    ";  
  
    $result = db_query($koneksi, $sql);  
  
    if (!$result) {  
        die("Data informasi gagal diambil.");  
    }  
}  
  
  
/* =========================  
   TOTAL INFORMASI  
========================= */  
  
$total_informasi = db_num_rows($result);  
  
  
/* =========================  
   FORMAT TANGGAL  
========================= */  
  
function tanggalIndonesia($tanggal)  
{  
    if (!$tanggal) {  
        return '-';  
    }  
  
    $bulan = [  
        1 => 'Januari',  
        2 => 'Februari',  
        3 => 'Maret',  
        4 => 'April',  
        5 => 'Mei',  
        6 => 'Juni',  
        7 => 'Juli',  
        8 => 'Agustus',  
        9 => 'September',  
        10 => 'Oktober',  
        11 => 'November',  
        12 => 'Desember'  
    ];  
  
    $timestamp = strtotime($tanggal);  
  
    if (!$timestamp) {  
        return '-';  
    }  
  
    $hari = date('d', $timestamp);  
    $bulan_id = $bulan[(int) date('m', $timestamp)];  
    $tahun = date('Y', $timestamp);  
  
    return $hari . ' ' . $bulan_id . ' ' . $tahun;  
}  
  
  
/* =========================  
   STATUS PESAN  
========================= */  
  
$pesan = pesan_status('informasi');

$pesan_status = $pesan['teks'];

$jenis_status = $pesan['jenis'];

$status_ikon  = $pesan['ikon'];
  
?>  
<?php

/* =========================================================
   PENGATURAN TAMPILAN HALAMAN
   Bagian atas, menu, dan bagian bawah halaman diambil dari
   folder layouts/ supaya tidak ditulis berulang.
========================================================== */

$judul         = "Informasi | Admin Sekolah";

$css           = "../assets/css/admin/informasi.css";

$menu_aktif    = "informasi";

$judul_halaman = "Informasi Sekolah";

$subjudul      = "Kelola pengumuman, berita, dan informasi yang ditampilkan pada website sekolah.";

require_once "../layouts/admin/kepala.php";
require_once "../layouts/admin/sidebar.php";
require_once "../layouts/admin/topbar.php";
?>
  
  
<!-- =========================  
     SIDEBAR  
  
  
<!-- OVERLAY -->  
  
<div  
    class="overlay"  
    id="overlay"  
></div>  
  
  
  
<!-- =========================  
     MAIN  
  
  
        <!-- PAGE HEADER -->  
  
        <div class="page-header">  
  
  
            <div class="page-title">  
  
  
                <h1>  
                    Informasi Sekolah  
                </h1>  
  
  
                <p>  
                    Kelola pengumuman, berita, dan informasi  
                    yang ditampilkan pada website sekolah.  
                </p>  
  
  
            </div>  
  
  
            <!-- TOMBOL TAMBAH INFORMASI  
                 TETAP DI BAGIAN ATAS -->  
  
            <a  
                href="tambah_informasi.php"  
                class="btn-primary"  
            >  
                <i class="fa-solid fa-plus"></i> Tambah Informasi  
            </a>  
  
  
        </div>  
  
  
  
        <!-- =========================  
             STATUS  
        ========================= -->  
  
        <?php if ($pesan_status !== ''): ?>  
  
  
            <div  
                class="alert alert-<?= e($jenis_status) ?>"  
            >  
  
  
                                    <span>  
                        <i class="fa-solid <?= e($status_ikon) ?>"></i>  
                    </span>    
  
  
                <span>  
                    <?= e($pesan_status) ?>  
                </span>  
  
  
            </div>  
  
  
        <?php endif; ?>  
  
  
  
        <!-- =========================  
             STATS  
        ========================= -->  
  
        <div class="stats">  
  
  
            <div class="stat-card">  
  
  
                <div class="stat-icon">  
                    <i class="fa-solid fa-bullhorn"></i>  
                </div>  
  
  
                <div class="stat-info">  
  
                    <span>  
                        Total Informasi  
                    </span>  
  
                    <strong>  
                        <?= $total_informasi ?>  
                    </strong>  
  
                </div>  
  
  
            </div>  
  
  
  
            <div class="stat-card">  
  
  
                <div class="stat-icon">  
                    <i class="fa-solid fa-calendar-days"></i>  
                </div>  
  
  
                <div class="stat-info">  
  
  
                    <span>  
                        Informasi Terbaru  
                    </span>  
  
  
                    <strong>  
  
  
                        <?php  
  
                        $terbaru = db_query(  
                            $koneksi,  
                            "  
                            SELECT id  
                            FROM informasi  
                            ORDER BY tanggal DESC, id DESC  
                            LIMIT 1  
                            "  
                        );  
  
                        echo db_num_rows($terbaru) > 0  
                            ? '1'  
                            : '0';  
  
                        ?>  
  
  
                    </strong>  
  
  
                </div>  
  
  
            </div>  
  
  
  
            <div class="stat-card">  
  
  
                <div class="stat-icon">  
                    <i class="fa-solid fa-images"></i>  
                </div>  
  
  
                <div class="stat-info">  
  
                    <span>  
                        Dengan Foto  
                    </span>  
  
                    <strong>  
  
  
                        <?php  
  
                        $foto_query = db_query(  
                            $koneksi,  
                            "  
                            SELECT COUNT(*) AS total  
                            FROM informasi  
                            WHERE foto IS NOT NULL  
                              AND foto != ''  
                            "  
                        );  
  
                        $foto_data =  
                            db_fetch_assoc(  
                                $foto_query  
                            );  
  
                        echo (int) (  
                            $foto_data['total'] ?? 0  
                        );  
  
                        ?>  
  
  
                    </strong>  
  
  
                </div>  
  
  
            </div>  
  
  
        </div>  
  
  
  
        <!-- =========================  
             FILTER  
        ========================= -->  
  
        <div class="filter-card">  
  
  
            <form  
                method="GET"  
                class="filter-form"  
            >  
  
  
                <div class="search-box">  
  
  
                    <span>  
                        <i class="fa-solid fa-magnifying-glass"></i>  
                    </span>  
  
  
                    <input  
                        type="text"  
                        name="keyword"  
                        value="<?= e($keyword) ?>"  
                        placeholder="Cari judul atau isi informasi..."  
                    >  
  
  
                </div>  
  
  
                <button  
                    type="submit"  
                    class="btn-search"  
                >  
                    Cari  
                </button>  
  
  
                <?php if ($keyword !== ''): ?>  
  
  
                    <a  
                        href="informasi.php"  
                        class="btn-reset"  
                    >  
                        Reset  
                    </a>  
  
  
                <?php endif; ?>  
  
  
            </form>  
  
  
        </div>  
  
  
  
        <!-- =========================  
             TABLE  
        ========================= -->  
  
        <div class="table-card">  
  
  
            <div class="table-header">  
  
  
                <h2>  
                    Daftar Informasi  
                </h2>  
  
  
                <span>  
                    <?= $total_informasi ?>  
                    informasi  
                </span>  
  
  
            </div>  
  
  
  
            <?php if ($total_informasi > 0): ?>  
  
  
                <div class="table-wrapper">  
  
  
                    <table>  
  
  
                        <thead>  
  
                            <tr>  
  
                                <th>  
                                    No  
                                </th>  
  
                                <th>  
                                    Foto  
                                </th>  
  
                                <th>  
                                    Informasi  
                                </th>  
  
                                <th>  
                                    Tanggal  
                                </th>  
  
                                <th>  
                                    Dibuat  
                                </th>  
  
                                <th>  
                                    Aksi  
                                </th>  
  
                            </tr>  
  
                        </thead>  
  
  
                        <tbody>  
  
  
                            <?php  
  
                            $no = 1;  
  
                            while (  
                                $item =  
                                db_fetch_assoc($result)  
                            ):  
  
                            ?>  
  
  
                                <tr>  
  
  
                                    <!-- NO -->  
  
                                    <td>  
                                        <?= $no++ ?>  
                                    </td>  
  
  
                                    <!-- FOTO -->  
  
                                    <td>  
  
  
                                        <div class="info-image">  
  
  
                                            <?php  
  
                                            $foto =  
                                                trim(  
                                                    $item['foto'] ?? ''  
                                                );  
  
                                            $foto_path =  
                                                "../uploads/informasi/" .  
                                                $foto;  
  
                                            ?>  
  
  
                                            <?php if (  
                                                $foto !== '' &&  
                                                unggah_ada($foto_path)  
                                            ): ?>  
  
  
                                                <img  
                                                    src="<?= e($foto_path) ?>"  
                                                    alt="<?= e($item['judul']) ?>"  
                                                >  
  
  
                                            <?php else: ?>  
  
  
                                                <div class="no-image">  
                                                    <i class="fa-solid fa-bullhorn"></i>  
                                                </div>  
  
  
                                            <?php endif; ?>  
  
  
                                        </div>  
  
  
                                    </td>  
  
  
                                    <!-- INFORMASI -->  
  
                                    <td>  
  
  
                                        <div class="info-title">  
  
  
                                            <strong>  
                                                <?= e(  
                                                    $item['judul']  
                                                ) ?>  
                                            </strong>  
  
  
                                            <p>  
                                                <?= e(  
                                                    $item['isi']  
                                                ) ?>  
                                            </p>  
  
  
                                        </div>  
  
  
                                    </td>  
  
  
                                    <!-- TANGGAL -->  
  
                                    <td>  
  
  
                                        <div class="date">  
  
                                            <?= tanggalIndonesia(  
                                                $item['tanggal']  
                                            ) ?>  
  
                                        </div>  
  
  
                                    </td>  
  
  
                                    <!-- CREATED -->  
  
                                    <td>  
  
  
                                        <div class="date">  
  
                                            <?= tanggalIndonesia(  
                                                substr(  
                                                    $item['created_at'],  
                                                    0,  
                                                    10  
                                                )  
                                            ) ?>  
  
                                        </div>  
  
  
                                    </td>  
  
  
                                    <!-- ACTION -->  
  
                                    <td>  
  
  
                                        <div class="actions">  
  
  
                                            <a  
                                                href="../informasi.php"  
                                                target="_blank"  
                                                class="btn-action btn-view"  
                                                title="Lihat website"  
                                            >  
                                                <i class="fa-solid fa-eye"></i> Lihat  
                                            </a>  
  
  
                                            <a  
                                                href="edit_informasi.php?id=<?= (int) $item['id'] ?>"  
                                                class="btn-action btn-edit"  
                                            >  
                                                <i class="fa-solid fa-pen-to-square"></i> Edit  
                                            </a>  
  
  
                                            <a  
                                                href="hapus_informasi.php?id=<?= (int) $item['id'] ?>"  
                                                class="btn-action btn-delete"  
                                                onclick="return confirm('Yakin ingin menghapus informasi ini? Data yang dihapus tidak dapat dikembalikan.')"  
                                            >  
                                                <i class="fa-solid fa-trash"></i> Hapus  
                                            </a>  
  
  
                                        </div>  
  
  
                                    </td>  
  
  
                                </tr>  
  
  
                            <?php endwhile; ?>  
  
  
                        </tbody>  
  
  
                    </table>  
  
  
                </div>  
  
  
            <?php else: ?>  
  
  
                <!-- =========================  
                     EMPTY  
                ========================= -->  
  
                <div class="empty">  
  
  
                    <div class="empty-icon">  
                        <i class="fa-solid fa-bullhorn"></i>  
                    </div>  
  
  
                    <?php if ($keyword !== ''): ?>  
  
  
                        <h3>  
                            Informasi tidak ditemukan  
                        </h3>  
  
  
                        <p>  
  
                            Tidak ada informasi yang cocok  
                            dengan pencarian  
  
                            "<strong>  
                                <?= e($keyword) ?>  
                            </strong>".  
  
                        </p>  
  
  
                        <a  
                            href="informasi.php"  
                            class="btn-primary"  
                        >  
                            Tampilkan Semua  
                        </a>  
  
  
                    <?php else: ?>  
  
  
                        <h3>  
                            Belum ada informasi  
                        </h3>  
  
  
                        <p>  
                            Silakan tambahkan informasi atau  
                            pengumuman sekolah pertama.  
                        </p>  
  
  
                        <!--  
                            TOMBOL TAMBAH INFORMASI  
                            DI BAGIAN BAWAH SUDAH DIHAPUS  
                        -->  
  
  
                    <?php endif; ?>  
  
  
                </div>  
  
  
            <?php endif; ?>  
  
  
        </div>  
  
  
<!-- =====================================================
     JAVASCRIPT KHUSUS HALAMAN INI
===================================================== -->

<script>
  
  
    /* =========================  
       AUTO HIDE ALERT  
    ========================= */  
  
    const alertBox =  
        document.querySelector('.alert');  
  
  
    if (alertBox) {  
  
        setTimeout(function() {  
  
            alertBox.style.opacity = '0';  
  
            alertBox.style.transition =  
                '0.4s ease';  
  
  
            setTimeout(function() {  
  
                alertBox.remove();  
  
            }, 400);  
  
        }, 4000);  
  
    }  
  
  
</script>

<?php require_once "../layouts/admin/kaki.php"; ?>
  
  
</body>  
  
</html>