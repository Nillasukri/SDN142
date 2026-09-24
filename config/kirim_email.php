<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/* =========================================================
   LOAD PHPMailer
========================================================= */

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

require_once __DIR__ . '/email.php';


/* =========================================================
   FUNGSI KIRIM EMAIL
========================================================= */

function kirimEmail($tujuan, $nama_tujuan, $subjek, $isi_html)
{
    $mail = new PHPMailer(true);

    // Menampung debug SMTP
    $debug = [];

    try {

        /* =================================================
           SMTP
        ================================================= */

        $mail->isSMTP();

        $mail->Host = MAIL_HOST;

        $mail->SMTPAuth = true;

        $mail->Username = MAIL_USERNAME;

        $mail->Password = MAIL_PASSWORD;

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = MAIL_PORT;


        /* =================================================
           SSL / SERTIFIKAT
        ================================================= */

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => false,
                'cafile'            => 'C:/xampp/apache/bin/curl-ca-bundle.crt'
            ]
        ];


        /* =================================================
           DEBUG SMTP
        ================================================= */

        $mail->SMTPDebug = 2;

        $mail->Debugoutput = function ($str, $level) use (&$debug) {
            $debug[] = trim($str);
        };


        /* =================================================
           CHARSET
        ================================================= */

        $mail->CharSet = 'UTF-8';


        /* =================================================
           EMAIL PENGIRIM
        ================================================= */

        $mail->setFrom(
            MAIL_FROM,
            MAIL_FROM_NAME
        );


        /* =================================================
           EMAIL PENERIMA
        ================================================= */

        $mail->addAddress(
            $tujuan,
            $nama_tujuan
        );


        /* =================================================
           ISI EMAIL
        ================================================= */

        $mail->isHTML(true);

        $mail->Subject = $subjek;

        $mail->Body = $isi_html;

        $mail->AltBody = strip_tags($isi_html);


        /* =================================================
           KIRIM EMAIL
        ================================================= */

        $mail->send();


        /* =================================================
           BERHASIL
        ================================================= */

        return [
            'status' => true,
            'pesan'  => 'Email berhasil dikirim.',
            'debug'  => $debug
        ];

    } catch (Exception $e) {

        /* =================================================
           ERROR
        ================================================= */

        $pesan_error = $mail->ErrorInfo;

        if (empty($pesan_error)) {
            $pesan_error = $e->getMessage();
        }

        return [
            'status' => false,
            'pesan'  => $pesan_error,
            'debug'  => $debug
        ];
    }
}
?>