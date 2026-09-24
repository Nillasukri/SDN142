<?php

/* =====================================================================
   KONEKSI DATABASE                            config/koneksi.php
   ---------------------------------------------------------------------
   Ini satu-satunya berkas yang boleh tahu soal database.
   Semua halaman cukup memuat berkas ini, lalu memakai $koneksi.

   ADA DUA MODE:

     lokal     -> MySQL di komputer sendiri (XAMPP), data di folder
                  htdocs, foto di folder uploads/.
     supabase  -> PostgreSQL milik Supabase (untuk website online di
                  Vercel), foto di Supabase Storage.

   Mode 'otomatis' artinya: kalau ditemukan alamat + kunci Supabase
   (dari Environment Variable Vercel ATAU dari config/rahasia.php),
   maka mode supabase dipakai; kalau tidak ada, ya MySQL lokal.
   Jadi komputer Bapak tetap seperti biasa, tidak ada yang berubah.
   ===================================================================== */

/* Data sekolah (config/sekolah.php) dan fungsi bantuan
   (config/bantuan.php) ikut dimuat di sini, supaya semua halaman
   bisa langsung memakainya tanpa menulis require lagi. */
require_once __DIR__ . '/sekolah.php';
require_once __DIR__ . '/bantuan.php';

/* Pemilih lapisan database (config/db.php) */
require_once __DIR__ . '/db.php';

/* Penyimpanan foto & dokumen (config/storage.php).
   Di mode lokal berkas disimpan di folder uploads/ seperti biasa,
   di mode online dikirim ke Supabase Storage. */
require_once __DIR__ . '/storage.php';

/* --- Pengaturan mode: 'otomatis', 'lokal', atau 'supabase' --- */
$mode = 'otomatis';

db_muat_lapisan($mode);


if (db_lapisan() === 'supabase') {

    /* --------------------------------------------------------------
       MODE ONLINE (Supabase)
       Alamat & kunci dibaca dari Environment Variable di Vercel:
         SUPABASE_URL, SUPABASE_SERVICE_KEY, SUPABASE_BUCKET
       (atau dari config/rahasia.php kalau dijalankan di komputer)
    -------------------------------------------------------------- */
    $koneksi = db_connect();

    if (!$koneksi) {
        die("Koneksi ke Supabase gagal: " . db_connect_error());
    }

    /* Uji cepat: pastikan Supabase menjawab dan fungsi app_query ada.
       Kalau gagal, pesannya dibuat jelas supaya mudah diperbaiki. */
    if (db_query($koneksi, "select 1 as uji") === false) {

        /* -------------------------------------------------------------
           DIAGNOSA CEPAT — supaya penyebabnya langsung kelihatan
           tanpa harus menebak-nebak. Kunci hanya ditampilkan sepotong
           (disamarkan), jadi rahasianya tetap aman.
        ------------------------------------------------------------- */
        $url_kini   = db_ambil_setelan('SUPABASE_URL');
        $kunci_kini = db_ambil_setelan('SUPABASE_SERVICE_KEY');

        if ($kunci_kini === '') {
            $kunci_kini = db_ambil_setelan('SUPABASE_ANON_KEY');
        }

        /* Nama variabel yang benar-benar menyediakan kunci */
        $sumber_kunci = '(tidak ada)';

        foreach (['SUPABASE_SERVICE_KEY', 'SUPABASE_SECRET_KEY',
                  'SUPABASE_ANON_KEY', 'SUPABASE_PUBLISHABLE_KEY'] as $nama_var) {
            if (db_baca_setelan($nama_var) !== '') {
                $sumber_kunci = $nama_var;
                break;
            }
        }

        /* Kunci disamarkan: cukup awalan + panjangnya */
        $kunci_tampil = ($kunci_kini === '')
            ? '(kosong)'
            : substr($kunci_kini, 0, 6) . '… (' . strlen($kunci_kini) . ' huruf)';

        /* Petunjuk berdasarkan bentuk isinya */
        $petunjuk = '';

        if ($kunci_kini === '') {
            $petunjuk = 'Kuncinya kosong — Environment Variables belum terisi.';
        } elseif (strpos($kunci_kini, 'http') === 0) {
            $petunjuk = 'Isi kuncinya kelihatannya ALAMAT WEB — kemungkinan kolom URL dan kunci tertukar tempat.';
        } elseif (strpos($kunci_kini, 'sb_publishable') === 0) {
            $petunjuk = 'Itu kunci PUBLIK (publishable) — yang dibutuhkan kunci RAHASIA (Secret key / service_role).';
        } elseif (strpos($kunci_kini, 'sb_secret') === 0) {
            $petunjuk = 'Bentuknya sudah benar (kunci rahasia baru). Kalau tetap ditolak: kemungkinan kuncinya milik project LAIN, atau ada karakter tambahan (tanda kutip / spasi) yang ikut tersalin.';
        } elseif (strpos($kunci_kini, 'eyJ') === 0) {

            /* Kunci lama (JWT): baca perannya untuk memastikan bukan anon */
            $bagian = explode('.', $kunci_kini);
            $b64    = strtr((string) ($bagian[1] ?? ''), '-_', '+/');
            $b64   .= str_repeat('=', (4 - strlen($b64) % 4) % 4);
            $muatan = json_decode((string) base64_decode($b64), true);
            $peran  = is_array($muatan) ? (string) ($muatan['role'] ?? '') : '';

            if ($peran === 'anon') {
                $petunjuk = 'Itu kunci LAMA yang berperan ANON (publik) — yang dibutuhkan service_role.';
            } elseif ($peran === 'service_role') {
                $petunjuk = 'Bentuknya sudah benar (kunci lama service_role). Kalau tetap ditolak: kemungkinan kuncinya milik project LAIN.';
            } else {
                $petunjuk = 'Itu kunci lama (JWT) dengan peran tidak dikenal (' . $peran . ').';
            }
        } elseif (strlen($kunci_kini) < 40) {
            $petunjuk = 'Kuncinya terlalu pendek — kelihatannya terpotong saat disalin.';
        } else {
            $petunjuk = 'Bentuk kunci tidak dikenali — salin ulang dari Supabase → Settings → API.';
        }

        die(
            "<h2>Website belum bisa terhubung ke database Supabase</h2>"
            . "<p><b>Pesan dari Supabase:</b> " . e(db_error($koneksi)) . "</p>"
            . "<p><b>Diagnosa cepat:</b></p><ul>"
            . "<li>Alamat project: <code>" . e($url_kini !== '' ? $url_kini : '(kosong)') . "</code></li>"
            . "<li>Kunci diambil dari variabel: <code>" . e($sumber_kunci) . "</code></li>"
            . "<li>Isi kunci (disamarkan): <code>" . e($kunci_tampil) . "</code></li>"
            . "<li>" . e($petunjuk) . "</li>"
            . "</ul>"
            . "<p>Yang perlu diperiksa:</p>"
            . "<ol>"
            . "<li>Sudah menjalankan <code>database/schema-supabase.sql</code> di SQL Editor Supabase?</li>"
            . "<li>Environment Variable <code>SUPABASE_URL</code> dan <code>SUPABASE_SERVICE_KEY</code> "
            . "sudah diisi dengan benar (kunci <b>rahasia</b>, bukan publik)?</li>"
            . "<li>Alamat project masih benar (tidak salah ketik/kedaluwarsa)?</li>"
            . "</ol>"
        );
    }

} else {

    /* --------------------------------------------------------------
       MODE LOKAL (XAMPP) — ini yang biasa Bapak pakai
       Sesuaikan kalau setelan MySQL di komputer berbeda.
    -------------------------------------------------------------- */
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "db_sekolah";
    $port = 3306;

    /* Catatan: sejak PHP 8, kegagalan koneksi MySQL melempar pengecualian
       (bukan mengembalikan false), jadi dibungkus try-catch di sini supaya
       pesannya bisa dibuat jelas. */
    $koneksi        = false;
    $koneksi_pesan  = '';

    try {
        $koneksi = db_connect($host, $user, $pass, $db, $port);
    } catch (Throwable $e) {
        $koneksi_pesan = $e->getMessage();
    }

    if (!$koneksi) {

        /* Kalau ini terjadi di server online (Vercel), hampir pasti
           penyebabnya Environment Variables SUPABASE_URL dan
           SUPABASE_SERVICE_KEY belum diisi di Vercel, sehingga website
           mengira dirinya dijalankan di XAMPP lalu mencoba membuka MySQL
           (di Vercel tidak ada MySQL). Beritahu caranya dengan jelas. */
        $di_server_online = getenv('VERCEL') !== false
                         || getenv('AWS_LAMBDA_FUNCTION_NAME') !== false
                         || getenv('LAMBDA_TASK_ROOT') !== false
                         || is_dir('/var/task/user');

        if ($di_server_online) {

            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');

            die(
                '<!doctype html><html lang="id"><head><meta charset="utf-8">'
                . '<meta name="viewport" content="width=device-width, initial-scale=1">'
                . '<title>Database belum terhubung</title></head>'
                . '<body style="font-family:system-ui,sans-serif;max-width:640px;margin:40px auto;padding:0 16px;color:#333;line-height:1.6">'
                . '<h1 style="color:#c0392b;margin-bottom:4px">Database belum terhubung</h1>'
                . '<p>Website sudah berhasil berjalan di Vercel, tetapi <b>kunci Supabase '
                . 'belum dipasang</b>, jadi website mengira dirinya dijalankan di XAMPP dan '
                . 'mencoba membuka MySQL &mdash; padahal di Vercel tidak ada MySQL.</p>'
                . '<p><b>Cara memperbaiki (±2 menit):</b></p>'
                . '<ol>'
                . '<li>Buka <b>vercel.com</b> &rarr; proyek <b>sekolah</b> &rarr; '
                . '<b>Settings</b> &rarr; <b>Environment Variables</b>.</li>'
                . '<li>Tambah dua baris ini (nilainya diambil dari Supabase &rarr; '
                . '<b>Project Settings &rarr; API</b>):'
                . '<table style="border-collapse:collapse;margin:8px 0;font-size:14px">'
                . '<tr><td style="border:1px solid #ccc;padding:6px"><code>SUPABASE_URL</code></td>'
                . '<td style="border:1px solid #ccc;padding:6px"><code>https://xxxxx.supabase.co</code></td></tr>'
                . '<tr><td style="border:1px solid #ccc;padding:6px"><code>SUPABASE_SERVICE_KEY</code><br>'
                . '(nama <code>SUPABASE_SECRET_KEY</code> juga dikenal)</td>'
                . '<td style="border:1px solid #ccc;padding:6px">kunci rahasia: <b>Secret key</b> '
                . '(awalan <code>sb_secret_...</code>) &mdash; di tampilan lama disebut '
                . '<b>service_role</b> (<code>eyJ...</code>). Bukan kunci publishable/anon</td></tr>'
                . '</table></li>'
                . '<li>Klik <b>Save</b> untuk masing-masing.</li>'
                . '<li>Buka <b>Deployments</b> &rarr; baris paling atas &rarr; tombol '
                . '<b>&hellip;</b> (tiga titik) &rarr; <b>Redeploy</b>.</li>'
                . '</ol>'
                . '<p>Setelah itu halaman ini hilang sendiri. Panduan lengkap ada di '
                . '<code>PANDUAN-ONLINE.md</code> bagian <b>Langkah 3</b>.</p>'
                . '<p style="color:#777;font-size:13px">Pesan teknisnya: '
                . e($koneksi_pesan !== '' ? $koneksi_pesan : db_connect_error())
                . '</p></body></html>'
            );
        }

        die("Koneksi database gagal: " . e($koneksi_pesan !== '' ? $koneksi_pesan : db_connect_error()));
    }

    db_set_charset($koneksi, "utf8mb4");
}
