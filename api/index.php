<?php
// =====================================================================
//  PINTU MASUK TUNGGAL UNTUK VERCEL          api/index.php
// ---------------------------------------------------------------------
//  Di Vercel, hanya folder api/ yang bisa menjalankan PHP (lewat runtime
//  vercel-php). Karena itu vercel.json mengarahkan SEMUA permintaan ke
//  file ini, dan file ini menentukan file PHP mana di proyek yang
//  sebenarnya harus dibuka — meniru cara Apache/XAMPP membuka berkas
//  di htdocs.
//
//  Uji di komputer sendiri (tanpa Vercel):
//      php -S 127.0.0.1:8000 api/index.php
//  lalu buka http://127.0.0.1:8000
//
//  KEAMANAN: hanya halaman di root dan folder admin/ yang boleh
//  dijalankan. Folder config/, layouts/, database/, uploads/,
//  PHPMailer/ TIDAK BISA dibuka langsung dari browser.
// =====================================================================

define('APP_ROOT', dirname(__DIR__));


/**
 * Menyimpulkan jenis berkas (Content-Type) dari ekstensinya.
 */
function fc_jenis_berkas(string $berkas): string
{
    $peta = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'zip'  => 'application/zip',
        'rar'  => 'application/vnd.rar',
        'txt'  => 'text/plain; charset=UTF-8',
        'css'  => 'text/css; charset=UTF-8',
        'js'   => 'application/javascript; charset=UTF-8',
    ];

    $ekstensi = strtolower(pathinfo($berkas, PATHINFO_EXTENSION));

    return $peta[$ekstensi] ?? 'application/octet-stream';
}

/**
 * Mengirim isi berkas dari folder proyek ke browser.
 */
function fc_kirim_berkas(string $berkas): void
{
    header('Content-Type: ' . fc_jenis_berkas($berkas));
    header('Content-Length: ' . (string) filesize($berkas));
    header('Cache-Control: public, max-age=31536000');

    readfile($berkas);

    exit;
}

/**
 * Melayani permintaan /uploads/...
 *
 * Urutannya:
 *   1. berkas ada di folder proyek  -> kirim langsung
 *   2. berkas ada di Supabase Storage -> alihkan (redirect) ke alamat publiknya
 *   3. tidak ada di dua-duanya       -> 404
 */
function fc_layani_unggahan(string $path): void
{
    /* Berkas PHP tidak boleh dijalankan dari dalam uploads/ */
    if (preg_match('/\.(php|phtml|phar)$/i', $path)) {
        fc_tidak_ditemukan();
    }

    /* Tolak trik keluar folder (../) */
    if (strpos($path, '..') !== false) {
        fc_tidak_ditemukan();
    }

    /* Utamakan Supabase Storage saat mode online */
    require_once APP_ROOT . '/config/db.php';
    require_once APP_ROOT . '/config/storage.php';

    db_muat_lapisan('otomatis');

    if (storage_aktif()) {
        $objek = storage_objek($path);
        if ($objek !== '') {
            header('Cache-Control: public, max-age=86400');
            header('Location: ' . storage_url($objek, true), true, 302);
            exit;
        }
    }

    if (is_file(APP_ROOT . $path)) {
        fc_kirim_berkas(APP_ROOT . $path);
    }

    fc_tidak_ditemukan();
}


/**
 * Tampilkan halaman 404 lalu hentikan proses.
 */
function fc_tidak_ditemukan(): void
{
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');

    echo '<!doctype html><html lang="id"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>404 — Halaman tidak ditemukan</title></head>'
        . '<body style="font-family:system-ui,sans-serif;text-align:center;padding:60px">'
        . '<h1>404</h1><p>Halaman yang dicari tidak ditemukan.</p>'
        . '<p><a href="/">Kembali ke Beranda</a></p>'
        . '</body></html>';

    exit;
}


/* ---------------------------------------------------------------------
   1. Ambil path yang diminta (buang query string)
--------------------------------------------------------------------- */

$path = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$path = (string) parse_url($path, PHP_URL_PATH);
$path = rawurldecode($path);

if ($path === '') {
    $path = '/';
}


/* ---------------------------------------------------------------------
   2. Berkas statis (CSS/JS/gambar di assets/, foto di uploads/)

   Di Vercel, berkas /assets/ dan /uploads/ sudah dilayani langsung oleh
   aturan routes di vercel.json sebelum sampai ke sini. Blok ini gunanya
   untuk server uji lokal `php -S`, supaya aset & foto tetap tampil.

   Catatan: saat website sudah online, foto BARU tidak lagi disimpan di
   folder uploads/, melainkan di Supabase Storage. Folder ini hanya
   untuk foto lama yang ikut diunggah ke GitHub.
--------------------------------------------------------------------- */

/* --- /assets/ : CSS, JS, gambar tampilan (tidak pernah berubah) --- */
if (strpos($path, '/assets/') === 0) {

    if (preg_match('/\.(php|phtml|phar)$/i', $path)) {
        fc_tidak_ditemukan();
    }

    if (is_file(APP_ROOT . $path)) {
        return false;               // serahkan ke server bawaan PHP
    }

    fc_tidak_ditemukan();
}

/* --- /uploads/ : foto & dokumen unggahan ---

   Ada DUA kemungkinan tempat berkasnya:
     1. Ikut diunggah ke GitHub (foto lama)  -> dikirim langsung dari folder proyek
     2. Ada di Supabase Storage (unggahan baru dari dashboard admin)
        -> dialihkan ke alamat publik Storage, jadi berkasnya diambil
           langsung dari Supabase (tidak lewat fungsi PHP, aman untuk
           berkas besar dan hemat kuota Vercel).
--------------------------------------------------------------------- */
if (strpos($path, '/uploads/') === 0) {
    fc_layani_unggahan($path);
}


/* ---------------------------------------------------------------------
   3. Tentukan file PHP yang diminta
--------------------------------------------------------------------- */

$rel = ltrim($path, '/');

if ($rel === '') {
    $rel = 'index.php';             // beranda
} elseif (substr($rel, -1) === '/') {
    $rel .= 'index.php';            // folder -> index.php di folder itu
}

/* Tanpa ekstensi .php: hanya boleh kalau itu folder yang punya index.php */
if (!preg_match('/\.php$/i', $rel)) {

    $calon = rtrim($rel, '/') . '/index.php';

    if (is_file(APP_ROOT . '/' . $calon)) {
        $calon = $calon;            // dipakai
        $rel   = $calon;
    } else {
        fc_tidak_ditemukan();
    }
}


/* ---------------------------------------------------------------------
   4. Daftar putih (whitelist) yang boleh dijalankan
--------------------------------------------------------------------- */

/* Tolak tegas segala bentuk path traversal (../) */
if (preg_match('#(^|/)\.\.(/|$)#', $rel)) {
    fc_tidak_ditemukan();
}

/* Halaman pengunjung di root proyek */
$halaman_root = [
    'index.php',
    'profil.php',
    'informasi.php',
    'galeri.php',
    'dokumen.php',
    'pendaftaran.php',
];

/* Folder yang boleh dijalankan (semua file PHP di dalamnya) */
$folder_boleh = [
    'admin/',
];

$diizinkan = in_array($rel, $halaman_root, true);

if (!$diizinkan) {

    foreach ($folder_boleh as $awalan) {

        if (strpos($rel, $awalan) === 0) {
            $diizinkan = true;
            break;
        }
    }
}

if (!$diizinkan) {
    fc_tidak_ditemukan();
}


/* ---------------------------------------------------------------------
   5. Pastikan file-nya benar-benar ada & masih di dalam proyek
--------------------------------------------------------------------- */

$berkas = APP_ROOT . '/' . $rel;
$nyata  = realpath($berkas);

if (
    $nyata === false
    || strpos($nyata, realpath(APP_ROOT) . DIRECTORY_SEPARATOR) !== 0
    || !is_file($nyata)
) {
    fc_tidak_ditemukan();
}


/* ---------------------------------------------------------------------
   6. Jalankan halamannya

   Penting: direktori kerja diarahkan ke FOLDER FILE ITU SENDIRI,
   supaya `require_once "../config/koneksi.php"` di halaman admin
   dan `require_once "layouts/publik/kepala.php"` di halaman
   pengunjung tetap bekerja persis seperti di XAMPP.
--------------------------------------------------------------------- */

chdir(dirname($nyata));

require $nyata;
