<?php

/* =====================================================================
   TEMPAT PENYIMPANAN FOTO & DOKUMEN              config/storage.php
   ---------------------------------------------------------------------
   Di XAMPP, foto & dokumen disimpan sebagai berkas di folder uploads/.
   Di Vercel, folder proyek TIDAK BISA ditulisi saat website berjalan
   (setiap deploy file baru akan hilang). Karena itu, saat mode online
   foto & dokumen dikirim ke SUPABASE STORAGE (bucket "foto").

   Supaya halaman-halaman lain tidak perlu diubah, semua perintah
   berkas dibungkus fungsi berawalan `unggah_`:

       move_uploaded_file(...)  ->  unggah_simpan(...)
       unlink(...)              ->  unggah_hapus(...)
       file_exists(...)         ->  unggah_ada(...)
       filesize(...)            ->  unggah_ukuran(...)
       is_dir(...) / mkdir(...) ->  unggah_ada_folder(...) / unggah_mkdir(...)

   Cara kerjanya:
     mode lokal    : fungsi unggah_* hanya meneruskan ke fungsi PHP asli
                     (perilaku sama persis seperti sebelum ini).
     mode online   : berkas dikirim/dihapus/dibaca dari Supabase Storage.

   Nama berkas di Storage memakai jalur yang sama seperti di folder
   uploads/, jadi foto lama (yang ikut diunggah ke GitHub) tetap
   ditemukan: uploads/guru/abc.jpg  ->  bucket foto ->  guru/abc.jpg
   ===================================================================== */


/* =====================================================================
   1. SETELAN: ALAMAT, KUNCI, BUCKET
===================================================================== */

/**
 * Alamat project Supabase (tanpa garis miring di akhir).
 */
function storage_alamat(): string
{
    return rtrim(db_ambil_setelan('SUPABASE_URL'), '/');
}

/**
 * Kunci rahasia Supabase (service_role, atau anon kalau itu yang diisi).
 */
function storage_kunci(): string
{
    $kunci = db_ambil_setelan('SUPABASE_SERVICE_KEY');

    if ($kunci === '') {
        $kunci = db_ambil_setelan('SUPABASE_ANON_KEY');
    }

    return $kunci;
}

/**
 * Nama bucket (tempat penyimpanan) di Supabase. Bawaannya "foto".
 */
function storage_bucket(): string
{
    $bucket = db_ambil_setelan('SUPABASE_BUCKET', 'foto');

    return $bucket !== '' ? $bucket : 'foto';
}

/**
 * Apakah Supabase Storage dipakai? (hanya kalau mode online + kunci ada)
 */
function storage_aktif(): bool
{
    if (!function_exists('db_lapisan') || db_lapisan() !== 'supabase') {
        return false;
    }

    return storage_alamat() !== '' && storage_kunci() !== '';
}


/* =====================================================================
   2. NAMA OBJEK DI STORAGE
===================================================================== */

/**
 * Mengubah jalur berkas lokal menjadi nama objek di Supabase Storage.
 *
 *   "../uploads/guru/guru_123.jpg"  ->  "guru/guru_123.jpg"
 *   "../uploads/logo_x.jpg"         ->  "logo_x.jpg"
 *   "uploads/dokumen/ppdb.docx"     ->  "dokumen/ppdb.docx"
 *
 * Mengembalikan "" kalau jalurnya bukan berkas di dalam folder uploads/
 * (misalnya berkas konfigurasi) — berkas seperti itu tidak ikut ke Storage.
 */
function storage_objek(string $jalan): string
{
    $jalan = str_replace('\\', '/', trim($jalan));

    $pos = strpos($jalan, 'uploads/');

    if ($pos === false) {
        return '';
    }

    $objek = substr($jalan, $pos + strlen('uploads/'));

    /* Rapikan: buang garis miring ganda, '.' dan '..' */
    $bagian = [];

    foreach (explode('/', $objek) as $potong) {

        if ($potong === '' || $potong === '.' || $potong === '..') {
            continue;
        }

        $bagian[] = $potong;
    }

    return implode('/', $bagian);
}

/**
 * Menyusun alamat (URL) sebuah objek di Storage.
 *
 * @param bool $publik true  -> alamat publik (untuk dilihat pengunjung)
 *                     false -> alamat langsung (dipakai server, pakai kunci)
 */
function storage_url(string $objek, bool $publik = true): string
{
    $bagian = [];

    foreach (explode('/', $objek) as $potong) {
        $bagian[] = rawurlencode($potong);
    }

    $objek = implode('/', $bagian);

    $tengah = $publik ? '/storage/v1/object/public/' : '/storage/v1/object/';

    return storage_alamat() . $tengah . rawurlencode(storage_bucket()) . '/' . $objek;
}


/* =====================================================================
   3. KIRIMAN KE SUPABASE (HTTP)
===================================================================== */

/**
 * Mengirim permintaan HTTP ke Supabase.
 *
 * Mengembalikan isi balasan (string). Hasil status & header dikirim
 * lewat parameter $status dan $header_balasan.
 */
function storage_http(
    string $metode,
    string $alamat_api,
    array $header = [],
    ?string $badan = null,
    ?int &$status = null,
    ?array &$header_balasan = null,
    int $batas_detik = 60
): string {
    $status          = 0;
    $header_balasan  = [];

    $kunci = storage_kunci();

    if ($kunci !== '') {
        $header[] = 'apikey: ' . $kunci;
        $header[] = 'Authorization: Bearer ' . $kunci;
    }

    /* --- cara 1: cURL --- */
    if (function_exists('curl_init')) {

        $terkumpul = [];

        $ch = curl_init($alamat_api);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $metode,
            CURLOPT_HTTPHEADER     => $header,
            CURLOPT_TIMEOUT        => $batas_detik,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_NOBODY         => strtoupper($metode) === 'HEAD',
            CURLOPT_HEADERFUNCTION => function ($ch, $baris) use (&$terkumpul) {
                $terkumpul[] = trim($baris);
                return strlen($baris);
            },
        ]);

        if ($badan !== null && strtoupper($metode) !== 'HEAD') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $badan);
        }

        $balasan = curl_exec($ch);
        $status  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (!is_array($terkumpul)) {
            $terkumpul = [];
        }

        $header_balasan = $terkumpul;

        curl_close($ch);

        return $balasan === false ? '' : (string) $balasan;
    }

    /* --- cara 2: tanpa cURL (bawaan PHP) --- */
    $header[] = 'Expect:';

    $konteks = stream_context_create([
        'http' => [
            'method'        => $metode,
            'header'        => implode("\r\n", $header),
            'content'       => $badan,
            'timeout'       => $batas_detik,
            'ignore_errors' => true,
        ],
    ]);

    $balasan = @file_get_contents($alamat_api, false, $konteks);

    if (isset($http_response_header) && is_array($http_response_header)) {
        $header_balasan = $http_response_header;

        if (preg_match('#\s(\d{3})\s#', (string) $http_response_header[0], $cocok)) {
            $status = (int) $cocok[1];
        }
    }

    return $balasan === false ? '' : (string) $balasan;
}

/**
 * Membaca satu nilai header dari hasil storage_http().
 */
function storage_header(array $header_balasan, string $nama): string
{
    foreach ($header_balasan as $baris) {

        $pos = strpos($baris, ':');

        if ($pos === false) {
            continue;
        }

        if (strcasecmp(trim(substr($baris, 0, $pos)), $nama) === 0) {
            return trim(substr($baris, $pos + 1));
        }
    }

    return '';
}

/**
 * Pesan kesalahan terakhir dari Supabase Storage (untuk ditampilkan).
 */
function storage_error(): string
{
    return $GLOBALS['storage_error_terakhir'] ?? '';
}

function storage_catat_error(string $pesan): void
{
    $GLOBALS['storage_error_terakhir'] = $pesan;
}


/* =====================================================================
   4. SIMPAN / HAPUS / PERIKSA BERKAS DI STORAGE
===================================================================== */

/**
 * Mengunggah satu berkas lokal ke Supabase Storage.
 */
function storage_simpan(string $sumber, string $objek): bool
{
    if (!storage_aktif() || $objek === '' || !is_file($sumber)) {
        return false;
    }

    $isi = @file_get_contents($sumber);

    if ($isi === false) {
        storage_catat_error('Berkas ' . basename($sumber) . ' tidak bisa dibaca.');

        return false;
    }

    $mime = 'application/octet-stream';

    if (function_exists('mime_content_type')) {
        $terdeteksi = @mime_content_type($sumber);

        if (is_string($terdeteksi) && $terdeteksi !== '') {
            $mime = $terdeteksi;
        }
    }

    $header = [
        'Content-Type: ' . $mime,
        'x-upsert: true',
        'Cache-Control: 3600',
    ];

    $status = 0;

    storage_http(
        'POST',
        storage_url($objek, false),
        $header,
        $isi,
        $status
    );

    /* 409 = berkas sudah ada. Ulangi dengan PUT (timpa). */
    if ($status === 409) {
        storage_http(
            'PUT',
            storage_url($objek, false),
            $header,
            $isi,
            $status
        );
    }

    if ($status >= 200 && $status < 300) {
        unggah_lupakan_cache($objek, strlen($isi));

        return true;
    }

    storage_catat_error('Unggah ke Storage gagal (status ' . $status . ').');

    return false;
}

/**
 * Menghapus satu objek di Supabase Storage.
 */
function storage_hapus(string $objek): bool
{
    if (!storage_aktif() || $objek === '') {
        return false;
    }

    $status = 0;

    storage_http(
        'DELETE',
        storage_url($objek, false),
        [],
        null,
        $status
    );

    unggah_lupakan_cache($objek);

    /* 200/204 = berhasil. */
    if ($status >= 200 && $status < 300) {
        return true;
    }

    /* 400 = berkasnya memang tidak ada di Storage (anggap sudah bersih). */
    if ($status === 400 || $status === 404) {
        return true;
    }

    storage_catat_error('Hapus di Storage gagal (status ' . $status . ').');

    return false;
}

/**
 * Memeriksa apakah sebuah objek ada di Supabase Storage.
 * Hasilnya diingat selama satu permintaan halaman supaya tidak
 * menanyakan hal yang sama berkali-kali.
 */
function storage_ada(string $objek): bool
{
    if (!storage_aktif() || $objek === '') {
        return false;
    }

    if (array_key_exists($objek, unggah_cache_ada())) {
        return (bool) unggah_cache_ada()[$objek];
    }

    $ada = storage_ukuran($objek) !== false;

    unggah_cache_ada()[$objek] = $ada;

    return $ada;
}

/**
 * Ukuran sebuah objek di Storage (dalam byte), atau false kalau tidak ada.
 */
function storage_ukuran(string $objek)
{
    if (!storage_aktif() || $objek === '') {
        return false;
    }

    if (array_key_exists($objek, unggah_cache_ukuran())) {
        return unggah_cache_ukuran()[$objek];
    }

    $status             = 0;
    $header_balasan     = [];

    /* Coba cara paling ringan dulu: HEAD */
    storage_http(
        'HEAD',
        storage_url($objek, false),
        [],
        null,
        $status,
        $header_balasan,
        20
    );

    /* Sebagian server tidak melayani HEAD -> pakai GET (isinya dibuang) */
    if ($status === 0 || $status === 405 || $status === 501) {
        storage_http(
            'GET',
            storage_url($objek, false),
            [],
            null,
            $status,
            $header_balasan,
            60
        );
    }

    if ($status < 200 || $status >= 300) {
        unggah_cache_ukuran()[$objek] = false;

        return false;
    }

    $ukuran = storage_header($header_balasan, 'content-length');

    $ukuran = ($ukuran === '') ? 0 : (int) $ukuran;

    unggah_cache_ukuran()[$objek] = $ukuran;

    return $ukuran;
}

/**
 * Mengambil isi sebuah objek di Storage.
 *
 * @return string|false
 */
function storage_baca(string $objek)
{
    if (!storage_aktif() || $objek === '') {
        return false;
    }

    $status = 0;

    $isi = storage_http(
        'GET',
        storage_url($objek, false),
        [],
        null,
        $status,
        60
    );

    if ($status < 200 || $status >= 300) {
        storage_catat_error('Berkas tidak ditemukan di Storage (status ' . $status . ').');

        return false;
    }

    return $isi;
}


/* =====================================================================
   5. PENERJEMAH PERINTAH BERKAS (dipakai halaman-halaman)
===================================================================== */

/**
 * Ingatan sementara selama satu halaman dibuka, supaya tidak
 * menanyakan hal yang sama berulang kali ke Supabase.
 */
function &unggah_cache_ada(): array
{
    static $cache = [];

    return $cache;
}

function &unggah_cache_ukuran(): array
{
    static $cache = [];

    return $cache;
}

function unggah_lupakan_cache(string $objek, $ukuran = null): void
{
    $ada = &unggah_cache_ada();

    unset($ada[$objek]);

    if ($ukuran !== null) {
        $ukuran_cache = &unggah_cache_ukuran();

        $ukuran_cache[$objek] = $ukuran;
    }
}

/**
 * Pengganti move_uploaded_file(): menyimpan berkas hasil unggahan.
 *
 * mode lokal  : sama seperti move_uploaded_file()
 * mode online : dikirim ke Supabase Storage
 */
function unggah_simpan($sumber, string $tujuan): bool
{
    if (!storage_aktif()) {
        return move_uploaded_file($sumber, $tujuan);
    }

    $objek = storage_objek($tujuan);

    if ($objek === '') {
        return move_uploaded_file($sumber, $tujuan);
    }

    return storage_simpan((string) $sumber, $objek);
}

/**
 * Pengganti unlink(): menghapus berkas unggahan.
 */
function unggah_hapus(string $jalan): bool
{
    if (!storage_aktif()) {
        return @unlink($jalan);
    }

    $objek = storage_objek($jalan);

    if ($objek === '') {
        return @unlink($jalan);
    }

    $berhasil = true;

    if (storage_ada($objek)) {
        $berhasil = storage_hapus($objek);
    }

    /* Kalau foto lama masih ada di folder proyek, ikut dibersihkan. */
    if (file_exists($jalan)) {
        @unlink($jalan);
    }

    return $berhasil;
}

/**
 * Pengganti file_exists(): memeriksa berkas unggahan ada atau tidak.
 *
 * Berkas lama yang ikut diunggah ke GitHub tetap dianggap ada,
 * begitu juga berkas baru yang ada di Supabase Storage.
 */
function unggah_ada(string $jalan): bool
{
    if (file_exists($jalan)) {
        return true;
    }

    if (!storage_aktif()) {
        return false;
    }

    $objek = storage_objek($jalan);

    if ($objek === '') {
        return false;
    }

    return storage_ada($objek);
}

/**
 * Pengganti filesize(): ukuran berkas unggahan (dalam byte).
 *
 * @return int|false
 */
function unggah_ukuran(string $jalan)
{
    if (file_exists($jalan)) {
        return @filesize($jalan);
    }

    if (!storage_aktif()) {
        return false;
    }

    $objek = storage_objek($jalan);

    if ($objek === '') {
        return false;
    }

    return storage_ukuran($objek);
}

/**
 * Pengganti is_dir() untuk folder unggahan.
 *
 * Di mode online folder tidak perlu ada (Storage membuatnya otomatis),
 * jadi jawabannya selalu "sudah ada" supaya pengecekan pembuatan folder
 * dilewati.
 */
function unggah_ada_folder(string $folder): bool
{
    if (storage_aktif()) {
        return true;
    }

    return is_dir($folder);
}

/**
 * Pengganti mkdir() untuk folder unggahan.
 *
 * Di mode online tidak ada yang perlu dibuat (folder proyek tidak bisa
 * ditulisi di Vercel), jadi langsung dijawab berhasil.
 */
function unggah_mkdir(string $folder, int $mode = 0777, bool $rekursif = false): bool
{
    if (storage_aktif()) {
        return true;
    }

    if (is_dir($folder)) {
        return true;
    }

    return @mkdir($folder, $mode, $rekursif);
}

/**
 * Membaca isi berkas unggahan dari Storage.
 * Dipakai api/index.php untuk melayani permintaan /uploads/...
 *
 * @return string|false
 */
function unggah_baca(string $jalan)
{
    if (!storage_aktif()) {
        return false;
    }

    $objek = storage_objek($jalan);

    if ($objek === '') {
        return false;
    }

    return storage_baca($objek);
}
