<?php


/* =====================================================
   SESSION
===================================================== */

session_start();


/* =====================================================
   CEK LOGIN ADMIN
===================================================== */

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

$admin_id = (int) $_SESSION["admin_id"];

$nama_admin =
    $_SESSION["admin_nama"] ?? "Administrator";

$username_admin =
    $_SESSION["admin_username"] ?? "admin";


/* =====================================================
   LOGO SEKOLAH
===================================================== */

$logo_sekolah = '';

$query_logo = db_query(
    $koneksi,
    "SELECT logo
     FROM profil
     ORDER BY id ASC
     LIMIT 1"
);

if ($query_logo) {

    $data_logo = db_fetch_assoc(
        $query_logo
    );

    $logo_sekolah =
        $data_logo["logo"] ?? '';
}


/* =====================================================
   AMBIL DATA ADMIN TERBARU
===================================================== */

$stmt_admin = db_prepare(
    $koneksi,
    "SELECT id, username, password, nama
     FROM admin
     WHERE id = ?
     LIMIT 1"
);

$admin = null;

if ($stmt_admin) {

    db_stmt_bind_param(
        $stmt_admin,
        "i",
        $admin_id
    );

    db_stmt_execute(
        $stmt_admin
    );

    $hasil_admin =
        db_stmt_get_result(
            $stmt_admin
        );

    $admin =
        db_fetch_assoc(
            $hasil_admin
        );

    db_stmt_close(
        $stmt_admin
    );
}


if (!$admin) {

    session_destroy();

    header("Location: login.php");
    exit;

}


/* =====================================================
   PESAN
===================================================== */

$pesan = "";
$tipe_pesan = "";


/* =====================================================
   PROSES UBAH PASSWORD
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password_lama =
        $_POST["password_lama"] ?? "";

    $password_baru =
        $_POST["password_baru"] ?? "";

    $konfirmasi_password =
        $_POST["konfirmasi_password"] ?? "";


    /* -------------------------------------------------
       VALIDASI KOSONG
    ------------------------------------------------- */

    if (
        $password_lama === "" ||
        $password_baru === "" ||
        $konfirmasi_password === ""
    ) {

        $pesan =
            "Semua kolom password wajib diisi.";

        $tipe_pesan = "error";

    }


    /* -------------------------------------------------
       VALIDASI PASSWORD LAMA
    ------------------------------------------------- */

    elseif (
        !password_verify(
            $password_lama,
            $admin["password"]
        )
    ) {

        /*
         * Dukungan untuk akun prototype lama
         * yang password-nya masih plaintext.
         */

        if (
            !hash_equals(
                (string) $admin["password"],
                (string) $password_lama
            )
        ) {

            $pesan =
                "Password lama yang kamu masukkan salah.";

            $tipe_pesan = "error";

        }

    }


    /* -------------------------------------------------
       VALIDASI PASSWORD BARU
    ------------------------------------------------- */

    if (
        $pesan === "" &&
        strlen($password_baru) < 8
    ) {

        $pesan =
            "Password baru minimal 8 karakter.";

        $tipe_pesan = "error";

    }


    /* -------------------------------------------------
       KONFIRMASI PASSWORD
    ------------------------------------------------- */

    if (
        $pesan === "" &&
        $password_baru !== $konfirmasi_password
    ) {

        $pesan =
            "Konfirmasi password baru tidak cocok.";

        $tipe_pesan = "error";

    }


    /* -------------------------------------------------
       PASSWORD BARU TIDAK BOLEH SAMA
    ------------------------------------------------- */

    if (
        $pesan === "" &&
        (
            password_verify(
                $password_baru,
                $admin["password"]
            )
            ||
            hash_equals(
                (string) $admin["password"],
                (string) $password_baru
            )
        )
    ) {

        $pesan =
            "Password baru harus berbeda dari password lama.";

        $tipe_pesan = "error";

    }


    /* -------------------------------------------------
       SIMPAN PASSWORD BARU
    ------------------------------------------------- */

    if ($pesan === "") {

        $password_hash =
            password_hash(
                $password_baru,
                PASSWORD_DEFAULT
            );


        $stmt_update = db_prepare(
            $koneksi,
            "UPDATE admin
             SET password = ?
             WHERE id = ?"
        );


        if ($stmt_update) {

            db_stmt_bind_param(
                $stmt_update,
                "si",
                $password_hash,
                $admin_id
            );


            if (
                db_stmt_execute(
                    $stmt_update
                )
            ) {

                $pesan =
                    "Password berhasil diubah.";

                $tipe_pesan =
                    "success";


                /*
                 * Bersihkan field password
                 * dari POST setelah berhasil.
                 */

                $_POST = [];


                /*
                 * Logout otomatis setelah
                 * password berhasil diganti.
                 *
                 * Admin diarahkan ke login agar
                 * login kembali menggunakan password baru.
                 */

                session_regenerate_id(true);

                $_SESSION["admin_id"] =
                    $admin["id"];

                $_SESSION["admin_username"] =
                    $admin["username"];

                $_SESSION["admin_nama"] =
                    $admin["nama"];


                /*
                 * Tandai bahwa password berhasil
                 * diganti sehingga pesan dapat
                 * ditampilkan setelah kembali login.
                 */

                $_SESSION["password_berhasil_diubah"] =
                    true;

            } else {

                $pesan =
                    "Password gagal diubah. Silakan coba lagi.";

                $tipe_pesan =
                    "error";

            }


            db_stmt_close(
                $stmt_update
            );

        } else {

            $pesan =
                "Terjadi kesalahan pada sistem.";

            $tipe_pesan =
                "error";

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

$judul         = "Keamanan Akun | " . NAMA_SEKOLAH_KAPITAL;

$css           = "../assets/css/admin/keamanan.css";

$menu_aktif    = "keamanan";

$judul_halaman = "Keamanan Akun";

$subjudul      = "Kelola password administrator";

$font_pusat    = true;      /* halaman ini memakai font Poppins */

require_once "../layouts/admin/kepala.php";
require_once "../layouts/admin/sidebar.php";
require_once "../layouts/admin/topbar.php";
?>


<!-- =====================================================
     SIDEBAR


<!-- =====================================================
     OVERLAY MOBILE
===================================================== -->

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
></div>


<!-- =====================================================
     MAIN


        <div class="page-heading">

            <h2>
                Pengaturan Keamanan
            </h2>

            <p>
                Ubah password akun administrator
                untuk menjaga keamanan website sekolah.
            </p>

        </div>


        <div class="security-card">


            <!-- HEADER CARD -->

            <div class="security-header">

                <div class="security-icon">

                    <i class="fa-solid fa-lock"></i>

                </div>


                <div class="security-header-text">

                    <h3>
                        Ubah Password
                    </h3>

                    <p>
                        Gunakan password yang kuat
                        dan mudah kamu ingat.
                    </p>

                </div>

            </div>


            <!-- FORM -->

            <form
                method="POST"
                class="security-form"
                autocomplete="off"
            >


                <!-- PESAN -->

                <?php if ($pesan !== ""): ?>

                    <div
                        class="alert alert-<?= $tipe_pesan === "success"
                            ? "success"
                            : "danger"; ?>"
                    >

                        <i class="fa-solid
                            <?= $tipe_pesan === "success"
                                ? "fa-circle-check"
                                : "fa-triangle-exclamation";
                            ?>">
                        </i>

                        <span>
                            <?= e($pesan); ?>
                        </span>

                    </div>

                <?php endif; ?>


                <!-- INFO AKUN -->

                <div class="account-info">

                    <div class="account-info-title">

                        Akun yang sedang digunakan

                    </div>


                    <div class="account-info-row">

                        <i class="fa-solid fa-user"></i>

                        <span>
                            <strong>
                                <?= e(
                                    $admin["nama"]
                                ); ?>
                            </strong>

                            &nbsp;(@<?= e(
                                $admin["username"]
                            ); ?>)
                        </span>

                    </div>

                </div>


                <!-- PASSWORD LAMA -->

                <div class="form-group">

                    <label for="password_lama">
                        Password Lama
                    </label>


                    <div class="input-wrapper">

                        <i class="fa-solid fa-lock"></i>


                        <input
                            type="password"
                            id="password_lama"
                            name="password_lama"
                            placeholder="Masukkan password lama"
                            autocomplete="current-password"
                            required
                        >


                        <button
                            type="button"
                            class="toggle-password"
                            data-target="password_lama"
                            aria-label="Tampilkan password"
                        >

                            <i class="fa-solid fa-eye"></i>

                        </button>

                    </div>

                </div>


                <!-- PASSWORD BARU -->

                <div class="form-group">

                    <label for="password_baru">
                        Password Baru
                    </label>


                    <div class="input-wrapper">

                        <i class="fa-solid fa-key"></i>


                        <input
                            type="password"
                            id="password_baru"
                            name="password_baru"
                            placeholder="Masukkan password baru"
                            autocomplete="new-password"
                            required
                        >


                        <button
                            type="button"
                            class="toggle-password"
                            data-target="password_baru"
                            aria-label="Tampilkan password"
                        >

                            <i class="fa-solid fa-eye"></i>

                        </button>

                    </div>

                </div>


                <!-- KONFIRMASI -->

                <div class="form-group">

                    <label for="konfirmasi_password">
                        Konfirmasi Password Baru
                    </label>


                    <div class="input-wrapper">

                        <i class="fa-solid fa-check-double"></i>


                        <input
                            type="password"
                            id="konfirmasi_password"
                            name="konfirmasi_password"
                            placeholder="Ulangi password baru"
                            autocomplete="new-password"
                            required
                        >


                        <button
                            type="button"
                            class="toggle-password"
                            data-target="konfirmasi_password"
                            aria-label="Tampilkan password"
                        >

                            <i class="fa-solid fa-eye"></i>

                        </button>

                    </div>

                </div>


                <!-- ATURAN PASSWORD -->

                <div class="password-rules">

                    <div class="password-rules-title">

                        <i class="fa-solid fa-circle-info"></i>

                        Ketentuan Password

                    </div>


                    <ul>

                        <li>
                            Minimal 8 karakter.
                        </li>

                        <li>
                            Gunakan kombinasi huruf,
                            angka, dan karakter khusus
                            jika memungkinkan.
                        </li>

                        <li>
                            Jangan gunakan password
                            yang mudah ditebak.
                        </li>

                    </ul>

                </div>


                <!-- ACTION -->

                <div class="form-actions">


                    <button
                        type="submit"
                        class="btn-save"
                    >

                        <i class="fa-solid fa-key"></i>

                        Ubah Password

                    </button>


                    <a
                        href="dashboard.php"
                        class="btn-cancel"
                    >

                        <i class="fa-solid fa-arrow-left"></i>

                        Kembali ke Dashboard

                    </a>


                </div>


            </form>


        </div>


<!-- =====================================================
     JAVASCRIPT KHUSUS HALAMAN INI
===================================================== -->

<script>


/* =====================================================
   TAMPILKAN / SEMBUNYIKAN PASSWORD
===================================================== */

const toggleButtons =
    document.querySelectorAll(
        ".toggle-password"
    );


toggleButtons.forEach(
    function (button) {

        button.addEventListener(
            "click",
            function () {

                const targetId =
                    this.getAttribute(
                        "data-target"
                    );

                const input =
                    document.getElementById(
                        targetId
                    );

                const icon =
                    this.querySelector("i");


                if (
                    input.type === "password"
                ) {

                    input.type = "text";

                    icon.classList.remove(
                        "fa-eye"
                    );

                    icon.classList.add(
                        "fa-eye-slash"
                    );

                } else {

                    input.type = "password";

                    icon.classList.remove(
                        "fa-eye-slash"
                    );

                    icon.classList.add(
                        "fa-eye"
                    );

                }

            }
        );

    }
);


/* =====================================================
   KONFIRMASI SEBELUM SUBMIT
===================================================== */

const securityForm =
    document.querySelector(
        ".security-form"
    );


securityForm.addEventListener(
    "submit",
    function (event) {

        const passwordBaru =
            document.getElementById(
                "password_baru"
            ).value;

        const konfirmasi =
            document.getElementById(
                "konfirmasi_password"
            ).value;


        if (passwordBaru.length < 8) {

            alert(
                "Password baru minimal 8 karakter."
            );

            event.preventDefault();

            return;

        }


        if (passwordBaru !== konfirmasi) {

            alert(
                "Konfirmasi password baru tidak cocok."
            );

            event.preventDefault();

            return;

        }


        const yakin =
            confirm(
                "Apakah kamu yakin ingin mengubah password?"
            );


        if (!yakin) {

            event.preventDefault();

        }

    }
);

</script>

<?php require_once "../layouts/admin/kaki.php"; ?>


</body>

</html>