<?php
/* =====================================================================
   DATA SEKOLAH                            config/sekolah.php
   ---------------------------------------------------------------------
   >>> DATA SEKOLAH DIUBAH CUKUP DI FILE INI <<<

   File ini dipanggil otomatis oleh config/koneksi.php, jadi semua
   halaman (admin maupun pengunjung) sudah bisa memakainya tanpa perlu
   menulis require lagi.

   CARA PAKAI di halaman lain:

       <?= NAMA_SEKOLAH ?>            -> UPT SDN 142 Inpres Lassang II
       <?= NAMA_SEKOLAH_PANJANG ?>    -> UPT SD Negeri 142 Inpres Lassang II
       <?= NAMA_SEKOLAH_KAPITAL ?>    -> UPT SD NEGERI 142 INPRES LASSANG II
       <?= ALAMAT_SEKOLAH ?>

   CATATAN soal nama sekolah:
   Ada tiga gaya penulisan yang sudah dipakai di tempat berbeda
   (pendek untuk judul tab, resmi untuk footer, huruf besar untuk
   bagian yang tampil menonjol). Kalau mau semuanya disamakan,
   cukup isi ketiganya dengan tulisan yang sama.
   ===================================================================== */


/* ---------------------------------------------------------------
   NAMA SEKOLAH
--------------------------------------------------------------- */

define('NAMA_SEKOLAH',         'UPT SDN 142 Inpres Lassang II');

define('NAMA_SEKOLAH_PANJANG', 'UPT SD Negeri 142 Inpres Lassang II');

define('NAMA_SEKOLAH_KAPITAL', 'UPT SD NEGERI 142 INPRES LASSANG II');


/* ---------------------------------------------------------------
   ALAMAT SEKOLAH
--------------------------------------------------------------- */

define(
    'ALAMAT_SEKOLAH',
    'Desa Kampung Beru, Kec. Polombangkeng Timur, ' .
    'Kab. Takalar, Sulawesi Selatan'
);

define('DESA_SEKOLAH',      'Kampung Beru');

define('KECAMATAN_SEKOLAH', 'Polombangkeng Timur');

define('KABUPATEN_SEKOLAH', 'Takalar');

define('PROVINSI_SEKOLAH',  'Sulawesi Selatan');
