<?php
/* =====================================================================
   FUNGSI BANTUAN                       config/bantuan.php
   ---------------------------------------------------------------------
   Kumpulan fungsi kecil yang dipakai banyak halaman, supaya tidak
   ditulis berulang di setiap file.

   File ini dipanggil otomatis oleh config/koneksi.php, jadi semua
   halaman sudah bisa memakainya.

   ISI FILE INI:
     1. e()            -> mengamankan tulisan sebelum ditampilkan
     2. pesan_status() -> pesan "berhasil / gagal" setelah tambah,
                          edit, atau hapus data
   ===================================================================== */


/* ---------------------------------------------------------------
   1. e($text)

   Mengubah karakter berbahaya (< > " ') jadi kode aman, supaya
   tulisan dari database tidak bisa merusak halaman.

   Dulu fungsi ini ditulis ulang di 22 file. Sekarang cukup di sini.
--------------------------------------------------------------- */

if (!function_exists('e')) {

    function e($text)
    {
        return htmlspecialchars((string) ($text ?? ''), ENT_QUOTES, 'UTF-8');
    }
}


/* ---------------------------------------------------------------
   2. pesan_status($halaman)

   Membaca ?status=... dari alamat halaman (dikirim oleh file
   hapus_*.php, tambah_*.php, edit_*.php) lalu mengubahnya jadi
   pesan yang enak dibaca.

   Cara pakai di halaman:

       $pesan = pesan_status('galeri');

       $pesan['teks']   -> "Foto galeri berhasil dihapus."
       $pesan['jenis']  -> "success" atau "danger"
       $pesan['ikon']   -> nama ikon Font Awesome

   Daftar $halaman: galeri, guru, informasi, dokumen.
--------------------------------------------------------------- */

if (!function_exists('pesan_status')) {

    function pesan_status($halaman = '')
    {
        $label_data = [
            'galeri'    => 'Foto galeri',
            'guru'      => 'Data guru/staf',
            'informasi' => 'Informasi',
            'dokumen'   => 'Dokumen',
        ];

        $label = $label_data[$halaman] ?? 'Data';

        $daftar_pesan = [
            'tambah_sukses' => [$label . ' berhasil ditambahkan.', 'success'],
            'edit_sukses'   => [$label . ' berhasil diperbarui.', 'success'],
            'hapus_sukses'  => [$label . ' berhasil dihapus.', 'success'],

            'hapus_gagal'   => [$label . ' gagal dihapus. Silakan coba lagi.', 'danger'],
            'gagal'         => [$label . ' gagal diproses. Silakan coba lagi.', 'danger'],

            'id_tidak_valid'       => ['Data yang dipilih tidak valid.', 'danger'],
            'data_tidak_ditemukan' => ['Data tidak ditemukan. Mungkin sudah dihapus.', 'danger'],
        ];

        $status = $_GET['status'] ?? '';

        if (!isset($daftar_pesan[$status])) {

            return [
                'teks'  => '',
                'jenis' => 'success',
                'ikon'  => 'fa-circle-check',
            ];
        }

        $teks  = $daftar_pesan[$status][0];
        $jenis = $daftar_pesan[$status][1];

        return [
            'teks'  => $teks,
            'jenis' => $jenis,
            'ikon'  => $jenis === 'success'
                ? 'fa-circle-check'
                : 'fa-triangle-exclamation',
        ];
    }
}

/**
 * Menghasilkan URL publik untuk file unggahan (Supabase Storage atau lokal).
 * Mencegah 404 dan direct hit serverless Vercel untuk gambar/dokumen.
 */
function url_unggahan(?string $path): string
{
    if (empty($path)) {
        return '';
    }

    $clean = str_replace('\\', '/', trim($path));

    // Bersihkan prefix ../uploads/ atau uploads/
    if (strpos($clean, 'uploads/') !== false) {
        $pos = strpos($clean, 'uploads/');
        $clean = substr($clean, $pos + strlen('uploads/'));
    }

    $clean = ltrim($clean, '/');

    if (function_exists('storage_aktif') && storage_aktif()) {
        return storage_url($clean, true);
    }

    return 'uploads/' . $clean;
}
