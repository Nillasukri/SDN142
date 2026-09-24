<?php

require_once __DIR__ . '/sekolah.php';

/* =========================================================
   KONFIGURASI EMAIL (SMTP)

   File ini dibaca oleh config/kirim_email.php
   (lihat baris 15: require_once __DIR__ . '/email.php')

   ---------------------------------------------------------
   CARA MENGISI - UNTUK GMAIL
   ---------------------------------------------------------

   1. JANGAN pakai password akun Gmail biasa.
      Gmail sudah menolaknya untuk aplikasi pihak ketiga.

   2. Buat "Sandi Aplikasi" (App Password):

      a. Buka akun Google Bapak
      b. Nyalakan "Verifikasi 2 Langkah" dulu
         (wajib, kalau belum aktif menunya tidak muncul)
      c. Buka https://myaccount.google.com/apppasswords
      d. Ketik nama bebas, misal: Website Sekolah
      e. Klik Buat, lalu muncul 16 huruf, contoh:
              abcd efgh ijkl mnop
      f. Salin 16 huruf itu, dan HAPUS spasinya
         jadi: abcdefghijklmnop

   3. Tempel hasilnya di MAIL_PASSWORD di bawah.

   4. Simpan, lalu tes lewat halaman admin/test_email.php

   ---------------------------------------------------------
   KALAU PAKAI SELAIN GMAIL
   ---------------------------------------------------------

   MAIL_HOST dan MAIL_PORT harus disesuaikan, contoh:

     Gmail        : smtp.gmail.com          port 587
     Yahoo        : smtp.mail.yahoo.com     port 587
     Outlook      : smtp.office365.com      port 587
     Hosting cPanel: mail.namadomain.com    port 587

   ---------------------------------------------------------
   PENTING
   ---------------------------------------------------------

   - Jangan bagikan file ini ke siapa pun.
   - Jangan diunggah ke GitHub / internet.
   - Nanti saat migrasi, isi file ini akan dipindah ke file
     .env supaya tidak ikut terkirim ke mana-mana.

========================================================= */


/* =========================================================
   SMTP
========================================================= */

define('MAIL_HOST', 'smtp.gmail.com');

define('MAIL_USERNAME', 'emailsekolah@gmail.com');

define('MAIL_PASSWORD', 'isi-16-huruf-sandi-aplikasi');

define('MAIL_PORT', 587);


/* =========================================================
   EMAIL PENGIRIM
========================================================= */

define('MAIL_FROM', 'emailsekolah@gmail.com');

define('MAIL_FROM_NAME', NAMA_SEKOLAH_PANJANG);
