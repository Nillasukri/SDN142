<?php


/* =====================================================
   SESSION
===================================================== */

session_start();


/* =====================================================
   JIKA SUDAH LOGIN
===================================================== */

if (isset($_SESSION["admin_id"])) {

    header("Location: dashboard.php");
    exit;

}


/* =====================================================
   KONEKSI DATABASE
===================================================== */

require_once "../config/koneksi.php";

/* Penanda login (cookie) — dipakai hanya di mode online.
   Lihat config/auth_admin.php. */
require_once "../config/auth_admin.php";


/* Kalau cookie-nya masih sah, admin tidak perlu mengetik ulang
   username & password (session PHP bisa saja sudah hilang). */
if (auth_admin_dari_cookie($koneksi)) {

    header("Location: dashboard.php");
    exit;

}



/* =====================================================
/* =====================================================
   PROSES LOGIN
===================================================== */

$pesan = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim(
        $_POST["username"] ?? ""
    );

    $password = $_POST["password"] ?? "";


    /* -------------------------------------------------
       VALIDASI
    ------------------------------------------------- */

    if ($username === "" || $password === "") {

        $pesan = "Username dan password wajib diisi.";

    } else {


        /* -------------------------------------------------
           CARI USER ADMIN
        ------------------------------------------------- */

        $stmt = db_prepare(
            $koneksi,
            "SELECT id, username, password, nama
             FROM admin
             WHERE username = ?
             LIMIT 1"
        );


        if ($stmt) {

            db_stmt_bind_param(
                $stmt,
                "s",
                $username
            );


            db_stmt_execute($stmt);


            $hasil = db_stmt_get_result(
                $stmt
            );


            $admin = db_fetch_assoc(
                $hasil
            );


            /* -------------------------------------------------
               CEK USER
            ------------------------------------------------- */

            if ($admin) {

                $passwordBenar = false;


                /*
                 * Password hash
                 */

                if (
                    password_verify(
                        $password,
                        $admin["password"]
                    )
                ) {

                    $passwordBenar = true;

                }


                /*
                 * Password plaintext
                 * untuk akun prototype lama
                 */

                elseif (
                    hash_equals(
                        (string)$admin["password"],
                        (string)$password
                    )
                ) {

                    $passwordBenar = true;

                }


                /* -------------------------------------------------
                   LOGIN BERHASIL
                ------------------------------------------------- */

                if ($passwordBenar) {


                    /*
                     * Regenerasi session
                     * untuk keamanan.
                     */

                    session_regenerate_id(true);


                    $_SESSION["admin_id"] =
                        $admin["id"];


                    $_SESSION["admin_username"] =
                        $admin["username"];


                    $_SESSION["admin_nama"] =
                        $admin["nama"];

                    /* -------------------------------------------------
                       PENANDA LOGIN DI COOKIE (untuk mode online)
                       Menyimpan token di tabel sesi_admin + cookie
                       `sesi_admin`, supaya login tidak mudah hilang
                       di Vercel. Di mode lokal baris ini tidak
                       mengubah apa pun (hanya mengisi session).
                    ------------------------------------------------- */

                    auth_buat_sesi(
                        $koneksi,
                        (int) $admin["id"],
                        (string) $admin["nama"],
                        (string) $admin["username"]
                    );



                    header(
                        "Location: dashboard.php"
                    );

                    exit;


                } else {

                    $pesan =
                        "Username atau password salah.";

                }


            } else {

                $pesan =
                    "Username atau password salah.";

            }


            db_stmt_close($stmt);


        } else {

            $pesan =
                "Terjadi kesalahan pada sistem login.";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <title>
        Login Admin |
        <?= NAMA_SEKOLAH_KAPITAL ?>
    </title>


    <!-- =================================================
         GOOGLE FONT
    ================================================== -->

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <!-- =================================================
         FONT AWESOME
    ================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <link rel="stylesheet" href="../assets/css/admin/login.css">

</head>


<body>


<div class="login-wrapper">


    <div class="login-box">


        <!-- =================================================
             INFORMASI LOGIN
        ================================================= -->

        <div class="login-info">


            <div class="info-content">


                <div class="logo-admin">

                    <i class="fa-solid fa-school"></i>

                </div>


                <span class="label">

                    <i class="fa-solid fa-shield-halved"></i>

                    AREA ADMINISTRATOR

                </span>


                <h1>

                    Sistem Administrasi
                    Sekolah

                </h1>


                <p>

                    Selamat datang di halaman
                    administrator website sekolah.
                    Silakan masuk menggunakan akun
                    admin untuk mengelola informasi
                    sekolah.

                </p>


                <div class="info-list">


                    <div class="info-item">

                        <i class="fa-solid fa-newspaper"></i>

                        <span>
                            Kelola Informasi
                        </span>

                    </div>


                    <div class="info-item">

                        <i class="fa-solid fa-file-lines"></i>

                        <span>
                            Kelola Dokumen
                        </span>

                    </div>


                    <div class="info-item">

                        <i class="fa-solid fa-users"></i>

                        <span>
                            Kelola Guru
                        </span>

                    </div>


                </div>

            </div>

        </div>


        <!-- =================================================
             FORM LOGIN
        ================================================= -->

        <div class="login-form-area">


            <form
                method="POST"
                class="login-form"
                autocomplete="off"
            >


                <div class="login-title">

                    <h2>
                        Login Admin
                    </h2>

                    <p>
                        Masukkan akun administrator
                        untuk melanjutkan.
                    </p>

                </div>


                <?php if ($pesan !== ""): ?>

                    <div class="alert">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            <?= e($pesan); ?>
                        </span>

                    </div>

                <?php endif; ?>


                <!-- USERNAME -->

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>


                    <div class="input-wrapper">

                        <i class="fa-solid fa-user"></i>


                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Masukkan username"
                            value="<?= e(
                                $_POST["username"] ?? ""
                            ); ?>"
                            autocomplete="username"
                            required
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>


                    <div class="input-wrapper">

                        <i class="fa-solid fa-lock"></i>


                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >


                        <button
                            type="button"
                            class="toggle-password"
                            id="togglePassword"
                            aria-label="Tampilkan password"
                        >

                            <i
                                class="fa-solid fa-eye"
                                id="eyeIcon"
                            ></i>

                        </button>

                    </div>

                </div>


                <!-- LOGIN -->

                <button
                    type="submit"
                    name="login_admin"
                    class="btn-login"
                >

                    <i class="fa-solid fa-right-to-bracket"></i>

                    Masuk ke Dashboard

                </button>


                <!-- KEMBALI -->

                <div class="back-home">

                    <a href="../index.php">

                        <i class="fa-solid fa-arrow-left"></i>

                        Kembali ke Website Sekolah

                    </a>

                </div>


                <div class="copyright">

                    &copy;
                    <?= date("Y"); ?>

                    <?= NAMA_SEKOLAH_KAPITAL ?>

                </div>


            </form>

        </div>


    </div>

</div>


<script>

/* =====================================================
   TAMPILKAN / SEMBUNYIKAN PASSWORD
===================================================== */

const togglePassword =
    document.getElementById(
        "togglePassword"
    );


const password =
    document.getElementById(
        "password"
    );


const eyeIcon =
    document.getElementById(
        "eyeIcon"
    );


togglePassword?.addEventListener(
    "click",
    function () {

        if (
            password.type === "password"
        ) {

            password.type = "text";

            eyeIcon.classList.remove(
                "fa-eye"
            );

            eyeIcon.classList.add(
                "fa-eye-slash"
            );

        } else {

            password.type = "password";

            eyeIcon.classList.remove(
                "fa-eye-slash"
            );

            eyeIcon.classList.add(
                "fa-eye"
            );

        }

    }
);


/* =====================================================
   FOKUS USERNAME
===================================================== */

document
    .getElementById("username")
    .focus();

</script>


</body>

</html>
