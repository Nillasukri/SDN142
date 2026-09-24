<?php

require_once "../config/kirim_email.php";


/* =========================================================
   EMAIL TUJUAN UNTUK TEST
========================================================= */

$email_tujuan = "EMAIL_KAMU@gmail.com";


/* =========================================================
   ISI EMAIL
========================================================= */

$isi_email = "
<div style='font-family:Arial,sans-serif; line-height:1.6; color:#333;'>

    <h2 style='margin-bottom:10px;'>
        " . NAMA_SEKOLAH_KAPITAL . "
    </h2>

    <p>
        Halo Administrator,
    </p>

    <p>
        Ini adalah email percobaan dari website sekolah.
    </p>

    <p>
        Jika email ini berhasil masuk, berarti
        <strong>PHPMailer + Gmail SMTP</strong>
        sudah berhasil dikonfigurasi.
    </p>

    <hr>

    <p style='font-size:13px; color:#777;'>
        Email ini dikirim secara otomatis dari
        website sekolah.
    </p>

</div>
";


/* =========================================================
   KIRIM EMAIL
========================================================= */

$hasil = kirimEmail(
    $email_tujuan,
    "Administrator Sekolah",
    "Tes Email Website Sekolah",
    $isi_email
);


/* =========================================================
   HASIL
========================================================= */

if ($hasil['status']) {

    echo "
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css'>
    <div style='
        max-width:600px;
        margin:50px auto;
        padding:25px;
        border-radius:12px;
        background:#e8f8ee;
        border:1px solid #b7e4c7;
        font-family:Arial,sans-serif;
    '>

        <h2 style='color:#16803c;'>
            <i class='fa-solid fa-circle-check'></i> Email berhasil dikirim
        </h2>

        <p>
            Silakan cek inbox email tujuan.
        </p>

    </div>
    ";

} else {

    echo "
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css'>
    <div style='
        max-width:600px;
        margin:50px auto;
        padding:25px;
        border-radius:12px;
        background:#fff0f0;
        border:1px solid #f0b8b8;
        font-family:Arial,sans-serif;
    '>

        <h2 style='color:#c62828;'>
            <i class='fa-solid fa-circle-xmark'></i> Email gagal dikirim
        </h2>

        <p>
            <strong>Error:</strong>
        </p>

        <pre style='
            white-space:pre-wrap;
            background:#fff;
            padding:15px;
            border-radius:8px;
        '>" . htmlspecialchars($hasil['pesan']) . "</pre>

    </div>
    ";
}