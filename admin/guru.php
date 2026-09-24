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
   FILTER
========================================================= */

$keyword = trim($_GET['keyword'] ?? '');
$jabatan_filter = trim($_GET['jabatan'] ?? '');

/* =========================================================
   STATUS
========================================================= */

$pesan = pesan_status('guru');


$success = $pesan['jenis'] === 'success' ? $pesan['teks'] : '';

$error   = $pesan['jenis'] === 'danger'  ? $pesan['teks'] : '';

$status_ikon = $pesan['ikon'];


/* =========================================================
   DAFTAR JABATAN
========================================================= */

$jabatan_list = [];

$query_jabatan = db_query(
    $koneksi,
    "SELECT DISTINCT jabatan
     FROM guru
     WHERE jabatan IS NOT NULL
     AND jabatan != ''
     ORDER BY jabatan ASC"
);

if ($query_jabatan) {

    while ($row = db_fetch_assoc($query_jabatan)) {
        $jabatan_list[] = $row['jabatan'];
    }
}

/* =========================================================
   QUERY DATA GURU
========================================================= */

$sql = "SELECT *
        FROM guru
        WHERE 1=1";

$params = [];
$types = '';

if ($keyword !== '') {

    $sql .= " AND (
                nama LIKE ?
                OR jabatan LIKE ?
              )";

    $search = '%' . $keyword . '%';

    $params[] = $search;
    $params[] = $search;

    $types .= 'ss';
}

if ($jabatan_filter !== '') {

    $sql .= " AND jabatan = ?";

    $params[] = $jabatan_filter;

    $types .= 's';
}

$sql .= " ORDER BY nama ASC";

/* =========================================================
   PREPARED STATEMENT
========================================================= */

$stmt = db_prepare($koneksi, $sql);

$guru = [];

if ($stmt) {

    if (!empty($params)) {

        db_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );
    }

    db_stmt_execute($stmt);

    $result = db_stmt_get_result($stmt);

    if ($result) {

        while ($row = db_fetch_assoc($result)) {
            $guru[] = $row;
        }
    }

    db_stmt_close($stmt);
}

/* =========================================================
   TOTAL GURU
========================================================= */

$total_guru = 0;

$query_total = db_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM guru"
);

if ($query_total) {

    $data_total = db_fetch_assoc($query_total);

    $total_guru = (int) $data_total['total'];
}

/* =========================================================
   TOTAL GURU DENGAN FOTO
========================================================= */

$total_foto = 0;

$query_foto = db_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM guru
     WHERE foto IS NOT NULL
     AND foto != ''"
);

if ($query_foto) {

    $data_foto = db_fetch_assoc($query_foto);

    $total_foto = (int) $data_foto['total'];
}

/* =========================================================
   TOTAL JABATAN
========================================================= */

$total_jabatan = count($jabatan_list);

/* =========================================================
   INISIAL NAMA
========================================================= */

function inisial_nama($nama)
{
    $nama = trim($nama);

    if ($nama === '') {
        return 'G';
    }

    $parts = preg_split('/\s+/', $nama);

    $initial = '';

    foreach ($parts as $part) {

        if ($part !== '') {
            $initial .= strtoupper(substr($part, 0, 1));
        }

        if (strlen($initial) >= 2) {
            break;
        }
    }

    return $initial ?: 'G';
}

?>

<?php

/* =========================================================
   PENGATURAN TAMPILAN HALAMAN
   Bagian atas, menu, dan bagian bawah halaman diambil dari
   folder layouts/ supaya tidak ditulis berulang.
========================================================== */

$judul         = "Guru & Staf | Admin Sekolah";

$css           = "../assets/css/admin/guru.css";

$menu_aktif    = "guru";

$judul_halaman = "Guru & Staf";

$subjudul      = "Kelola data guru dan tenaga kependidikan";

require_once "../layouts/admin/kepala.php";
require_once "../layouts/admin/sidebar.php";
require_once "../layouts/admin/topbar.php";
?>


<!-- =========================================================
     SIDEBAR


<div
    class="sidebar-overlay"
    id="sidebarOverlay">
</div>


<!-- =========================================================
     MAIN


        <div class="page-header">

            <div>

                <h1>
                    Guru & Staf
                </h1>

                <p>
                    Kelola data guru dan tenaga kependidikan
                    yang ditampilkan pada website sekolah.
                </p>

            </div>


            <a
                href="tambah_guru.php"
                class="add-btn">

                <i class="fa-solid fa-plus"></i> Tambah Guru / Staf

            </a>

        </div>


        <!-- =================================================
             ALERT
        ================================================== -->

        <?php if ($success !== '' || $error !== ''): ?>

            <div class="alert alert-<?= $error !== '' ? 'danger' : 'success' ?>">

                <i class="fa-solid <?= e($status_ikon) ?>"></i>
                <?= e($error !== '' ? $error : $success); ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             STATISTIK
        ================================================== -->

        <div class="stats">


            <div class="stat-card">

                <div class="stat-icon">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>

                <div>

                    <div class="stat-number">
                        <?= $total_guru; ?>
                    </div>

                    <div class="stat-label">
                        Total Guru & Staf
                    </div>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon">
                    <i class="fa-solid fa-camera"></i>
                </div>

                <div>

                    <div class="stat-number">
                        <?= $total_foto; ?>
                    </div>

                    <div class="stat-label">
                        Sudah Memiliki Foto
                    </div>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon">
                    <i class="fa-solid fa-briefcase"></i>
                </div>

                <div>

                    <div class="stat-number">
                        <?= $total_jabatan; ?>
                    </div>

                    <div class="stat-label">
                        Jenis Jabatan
                    </div>

                </div>

            </div>


        </div>


        <!-- =================================================
             FILTER
        ================================================== -->

        <div class="filter-card">


            <form
                method="GET"
                class="filter-form">


                <div class="input-group">

                    <span class="search-icon">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>

                    <input
                        type="text"
                        name="keyword"
                        class="filter-input"
                        placeholder="Cari nama guru atau jabatan..."
                        value="<?= e($keyword); ?>">

                </div>


                <select
                    name="jabatan"
                    class="filter-select">


                    <option value="">
                        Semua Jabatan
                    </option>


                    <?php foreach ($jabatan_list as $jabatan): ?>

                        <option
                            value="<?= e($jabatan); ?>"
                            <?= $jabatan_filter === $jabatan
                                ? 'selected'
                                : ''; ?>>

                            <?= e($jabatan); ?>

                        </option>

                    <?php endforeach; ?>


                </select>


                <button
                    type="submit"
                    class="filter-btn">

                    <i class="fa-solid fa-magnifying-glass"></i> Cari

                </button>


                <a
                    href="guru.php"
                    class="reset-btn">

                    <i class="fa-solid fa-rotate-right"></i> Reset

                </a>


            </form>


        </div>


        <!-- =================================================
             GURU GRID
        ================================================== -->

        <?php if (!empty($guru)): ?>


            <div class="guru-grid">


                <?php foreach ($guru as $item): ?>


                    <article class="guru-card">


                        <!-- FOTO -->

                        <div class="guru-photo-wrapper">


                            <?php if (!empty($item['foto'])): ?>


                                <?php

                                $foto_path =
                                    "../uploads/" .
                                    $item['foto'];

                                $foto_exists =
                                    unggah_ada(
                                        $foto_path
                                    );

                                ?>


                                <?php if ($foto_exists): ?>


                                    <img
                                        src="<?= e($foto_path); ?>"
                                        alt="<?= e($item['nama']); ?>"
                                        class="guru-photo">


                                <?php else: ?>


                                    <div
                                        class="guru-placeholder">


                                        <div class="guru-initial">

                                            <?= e(
                                                inisial_nama(
                                                    $item['nama']
                                                )
                                            ); ?>

                                        </div>


                                        <span>
                                            Foto tidak ditemukan
                                        </span>


                                    </div>


                                <?php endif; ?>


                            <?php else: ?>


                                <div class="guru-placeholder">


                                    <div class="guru-initial">

                                        <?= e(
                                            inisial_nama(
                                                $item['nama']
                                            )
                                        ); ?>

                                    </div>


                                    <span>
                                        Belum ada foto
                                    </span>


                                </div>


                            <?php endif; ?>


                        </div>


                        <!-- DATA -->

                        <div class="guru-body">


                            <div class="guru-name">

                                <?= e(
                                    $item['nama']
                                ); ?>

                            </div>


                            <?php if (!empty($item['jabatan'])): ?>


                                <div class="guru-position">

                                    <i class="fa-solid fa-briefcase"></i>

                                    <?= e(
                                        $item['jabatan']
                                    ); ?>

                                </div>


                            <?php else: ?>


                                <div class="guru-position">

                                    <i class="fa-solid fa-user"></i>
                                    Guru / Staf

                                </div>


                            <?php endif; ?>


                            <!-- ACTION -->

                            <div class="guru-actions">


                                <a
                                    href="edit_guru.php?id=<?= (int) $item['id']; ?>"
                                    class="action-btn edit-btn">

                                    <i class="fa-solid fa-pen-to-square"></i> Edit

                                </a>


                                <a
                                    href="hapus_guru.php?id=<?= (int) $item['id']; ?>"
                                    class="action-btn delete-btn"
                                    onclick="
                                        return confirm(
                                            'Yakin ingin menghapus data <?= e($item['nama']); ?>?'
                                        );
                                    ">

                                    <i class="fa-solid fa-trash"></i> Hapus

                                </a>


                            </div>


                        </div>


                    </article>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <!-- EMPTY STATE -->

            <div class="empty-state">


                <div class="empty-icon">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>


                <h3>
                    Belum ada data guru atau staf
                </h3>


                <p>


                    <?php if (
                        $keyword !== '' ||
                        $jabatan_filter !== ''
                    ): ?>


                        Data yang sesuai dengan
                        pencarian atau filter tidak ditemukan.


                    <?php else: ?>


                        Silakan tambahkan data guru dan staf
                        sekolah terlebih dahulu.


                    <?php endif; ?>


                </p>


                <?php if (
                    $keyword === '' &&
                    $jabatan_filter === ''
                ): ?>


                    <a
                        href="tambah_guru.php"
                        class="empty-add-btn">

                        <i class="fa-solid fa-plus"></i> Tambah Guru / Staf

                    </a>


                <?php else: ?>


                    <a
                        href="guru.php"
                        class="empty-add-btn">

                        <i class="fa-solid fa-rotate-right"></i> Tampilkan Semua

                    </a>


                <?php endif; ?>


            </div>


        <?php endif; ?>


<!-- =====================================================
     JAVASCRIPT KHUSUS HALAMAN INI
===================================================== -->

<script>


    /* =========================================================
       AUTO HILANG ALERT
    ========================================================= */

    setTimeout(
        function() {

            const alerts =
                document.querySelectorAll(
                    '.alert'
                );


            alerts.forEach(
                function(alert) {

                    alert.style.transition =
                        'opacity .4s ease';

                    alert.style.opacity =
                        '0';


                    setTimeout(
                        function() {

                            alert.remove();

                        },
                        450
                    );

                }
            );

        },
        5000
    );


</script>

<?php require_once "../layouts/admin/kaki.php"; ?>
