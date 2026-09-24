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
    'url' => 'https://ghayvuppmdhqyhjbeopa.supabase.co',

    /* Key RAHASIA — bukan key publishable/anon!
       Ambil di: Project Settings -> API.
       Tampilan baru : "Secret key"    (awalan sb_secret_...)
       Tampilan lama : "service_role"  (awalan eyJ..., di Legacy API Keys)
       Key ini bisa penuh mengakses database, jadi JANGAN disebar. */
    'service_key' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImdoYXl2dXBwbWRocXloamJlb3BhIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc5MDI1MTg0MSwiZXhwIjoyMTA1ODI3ODQxfQ.wzYnLMiVPqJtLPuLuKi7kXvbOLWNKDP45N9vd-zoycc',

    /* Key publik (anon) — boleh dipakai di halaman publik, tidak wajib diisi */
    'anon_key' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImdoYXl2dXBwbWRocXloamJlb3BhIiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTAyNTE4NDEsImV4cCI6MjEwNTgyNzg0MX0.ZusRJRF5zlRQKtL7uE4y2GsBkiSCOt4b3s9cvroh0Gc',

    /* Nama bucket penyimpanan foto di Supabase Storage (default: foto) */
    'bucket' => 'foto',

];
