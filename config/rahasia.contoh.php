<?php
/* =====================================================================
   CONTOH ISI DATA RAHASIA           config/rahasia.contoh.php
   ---------------------------------------------------------------------
   CARA PAKAI:
     1. Salin (copy) file ini, lalu ganti namanya menjadi:
            config/rahasia.php
     2. Isi tiga nilai di bawah dengan data dari project Supabase Bapak
        (menu: Project Settings -> API).
     3. File config/rahasia.php TIDAK BOLEH diunggah ke GitHub.
        (sudah otomatis dicegah oleh file .gitignore)

   CATATAN:
     - Saat dipasang di Vercel, nilai-nilai ini TIDAK dipakai. Vercel
       membaca "Environment Variables" (lihat PANDUAN-ONLINE.md),
       dengan nama: SUPABASE_URL, SUPABASE_SERVICE_KEY, SUPABASE_BUCKET.
     - File ini gunanya kalau Bapak mau menjalankan versi Supabase
       di komputer sendiri (XAMPP) atau di hosting lain.
   ===================================================================== */

return [

    /* Alamat project Supabase.
       Contoh: https://abcdefghijklm.supabase.co  (TANPA garis miring di akhir) */
    'url' => '',

    /* Key RAHASIA (service_role / secret key) — bukan key anon!
       Ambil di: Project Settings -> API -> service_role (Reveal).
       Key ini bisa penuh mengakses database, jadi JANGAN disebar. */
    'service_key' => '',

    /* Key publik (anon) — boleh dipakai di halaman publik, tidak wajib diisi */
    'anon_key' => '',

    /* Nama bucket penyimpanan foto di Supabase Storage (default: foto) */
    'bucket' => 'foto',

];
