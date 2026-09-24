<?php

/* =====================================================================
   LAPISAN DATABASE SUPABASE (ONLINE)     config/db_supabase.php
   ---------------------------------------------------------------------
   Dipakai kalau website dijalankan di Vercel (online).

   Masalah yang diselesaikan file ini:
   di Vercel tidak ada MySQL, jadi `mysqli_connect()` tidak bisa dipakai.
   Database di Supabase juga PostgreSQL, bukan MySQL.

   Solusinya: PHP tidak menyambung langsung ke database, tetapi MENGIRIM
   perintah SQL-nya lewat REST API Supabase ke fungsi `app_query` yang
   sudah kita buat di database (lihat database/schema-supabase.sql).

   Alur satu perintah:

     db_query($koneksi, "SELECT * FROM guru")
          |
          v
     diubah sedikit supaya cocok dengan PostgreSQL (backtick dibuang,
     LIKE jadi ILIKE, LIMIT 5,10 jadi LIMIT 10 OFFSET 5)
          |
          v
     POST https://xxxx.supabase.co/rest/v1/rpc/app_query
          body: {"p_sql":"SELECT ...","p_params":[]}
          |
          v
     fungsi app_query menjalankannya, hasilnya dikirim balik sebagai
     data JSON  ->  dibungkus jadi objek DB_Hasil (mirip mysqli_result)

   Jadi halaman tetap menulis SQL gaya MySQL, lalu diterjemahkan di sini.
   ===================================================================== */


/* =====================================================================
   1. HTTP — alat pengirim permintaan ke Supabase
   ===================================================================== */

/**
 * Mengirim permintaan HTTP ke Supabase.
 *
 * @param string      $metode  'GET', 'POST', 'PUT', 'DELETE'
 * @param string      $alamat  alamat lengkap
 * @param array       $header  daftar header
 * @param string|null $badan   isi permintaan (body)
 * @param int         $status  diisi status HTTP (200, 201, 400, ...)
 * @param string      $pesan   diisi keterangan kegagalan
 *
 * @return string|false isi balasan, atau false kalau gagal
 */
function db_http(
    string $metode,
    string $alamat,
    array $header = [],
    ?string $badan = null,
    ?int &$status = null,
    ?string &$pesan = null
) {
    $status = 0;
    $pesan  = '';

    $percobaan = 0;

    while ($percobaan < 2) {

        $percobaan++;

        /* --- cara 1: cURL (biasanya tersedia, termasuk di Vercel) --- */
        if (function_exists('curl_init')) {

            $ch = curl_init($alamat);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST  => $metode,
                /* "Expect:" kosong memerintahkan cURL tidak mengirim
                   "Expect: 100-continue". Ini khusus cURL — di jalur
                   tanpa-cURL header itu justru ditolak server (417). */
                CURLOPT_HTTPHEADER     => array_merge($header, ['Expect:']),
                CURLOPT_TIMEOUT        => 25,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            ]);

            if ($badan !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $badan);
            }

            $balasan = curl_exec($ch);
            $errno   = curl_errno($ch);

            if ($errno === 0) {
                $status  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $pesan   = (string) curl_error($ch);
                curl_close($ch);

                return $balasan === false ? '' : (string) $balasan;
            }

            $pesan = 'cURL: ' . curl_error($ch);
            curl_close($ch);

        } else {

            /* --- cara 2: tanpa cURL, pakai bawaan PHP --- */
            $konteks = stream_context_create([
                'http' => [
                    'method'        => $metode,
                    'header'        => implode("\r\n", $header),
                    'content'       => $badan,
                    'timeout'       => 25,
                    'ignore_errors' => true,
                ],
            ]);

            $balasan = @file_get_contents($alamat, false, $konteks);

            /* PHP 8.4+: $http_response_header dilarang (deprecated);
               di Vercel jalur tanpa-cURL inilah yang dipakai, jadi
               gunakan fungsi penggantinya kalau tersedia. */
            $baris_status = '';

            if (function_exists('http_get_last_response_headers')) {
                $baris_http  = http_get_last_response_headers() ?? [];
                $baris_status = (string) ($baris_http[0] ?? '');
            } elseif (isset($http_response_header[0])) {
                $baris_status = (string) $http_response_header[0];
            }

            if ($baris_status !== ''
                && preg_match('#HTTP/\S+\s+(\d{3})#', $baris_status, $cocok)) {
                $status = (int) $cocok[1];
            }

            if ($balasan !== false) {
                return (string) $balasan;
            }

            $pesan = 'Tidak bisa menghubungi Supabase.';
        }

        sleep(1);       // jeda sebentar, lalu coba sekali lagi
    }

    return false;
}


/* =====================================================================
   2. KELAS PENAMPUNG HASIL (mirip mysqli_result)
   ===================================================================== */

class DB_Hasil
{
    /** @var array daftar baris data */
    public array $rows = [];

    /** @var int baris yang sedang ditunjuk */
    private int $posisi = 0;

    public function __construct(array $rows)
    {
        $this->rows = array_values($rows);
    }

    /** Jumlah baris (seperti mysqli_num_rows) */
    public function num_rows(): int
    {
        return count($this->rows);
    }

    /** Ambil satu baris (seperti mysqli_fetch_assoc); null kalau sudah habis */
    public function fetch_assoc(): ?array
    {
        if (!isset($this->rows[$this->posisi])) {
            return null;
        }

        return $this->rows[$this->posisi++];
    }

    /** Ambil semua baris sekaligus */
    public function fetch_all(): array
    {
        return $this->rows;
    }
}


/* =====================================================================
   3. KELAS KONEKSI SUPABASE
   ===================================================================== */

class DB_Koneksi
{
    public string $url;
    public string $kunci;
    public string $bucket;
    public string $error    = '';
    public int    $insert_id = 0;

    public function __construct(string $url, string $kunci, string $bucket = 'foto')
    {
        $this->url    = rtrim($url, '/');
        $this->kunci  = $kunci;
        $this->bucket = $bucket;
    }

    /**
     * Mengirim satu perintah SQL ke fungsi app_query di Supabase.
     *
     * @return array{rows: array, num_rows: int}|false
     */
    public function panggil(string $sql, array $params = [])
    {
        $this->error = '';

        $badan = json_encode(
            ['p_sql' => $sql, 'p_params' => array_values($params)],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($badan === false) {
            $this->error = 'Data yang dikirim tidak bisa diubah ke bentuk JSON.';
            return false;
        }

        $alamat = $this->url . '/rest/v1/rpc/app_query';

        $header = [
            'Content-Type: application/json',
            'Accept: application/json',
            'apikey: ' . $this->kunci,
            'Authorization: Bearer ' . $this->kunci,
            'Prefer: return=representation',
        ];

        $isi = db_http('POST', $alamat, $header, $badan, $status, $pesan);

        if ($isi === false) {
            $this->error = 'Gagal menghubungi Supabase. ' . $pesan;
            return false;
        }

        $balasan = json_decode($isi, true);

        if (is_array($balasan) && isset($balasan['code']) && isset($balasan['message'])) {
            // bentuk balasan kesalahan dari Supabase
            $this->error = (string) $balasan['message'];
            return false;
        }

        if ($status >= 400) {
            $this->error = is_string($balasan['message'] ?? null)
                ? (string) $balasan['message']
                : ('Supabase menolak permintaan (status ' . $status . '): ' . substr($isi, 0, 300));
            return false;
        }

        if (!is_array($balasan)) {
            $this->error = 'Balasan Supabase tidak bisa dibaca: ' . substr($isi, 0, 300);
            return false;
        }

        /* Balasan app_query: {"rows":[...], "num_rows":n, "insert_id":0} */
        if (array_key_exists('rows', $balasan)) {
            $rows = is_array($balasan['rows']) ? $balasan['rows'] : [];
        } else {
            /* cadangan: kalau balasannya langsung berupa daftar baris */
            $rows = $balasan;
        }

        return ['rows' => $rows, 'num_rows' => count($rows)];
    }
}


/* =====================================================================
   4. KELAS PERINTAH BERSIAP (mirip mysqli_stmt)
   ===================================================================== */

class DB_Stmt
{
    public DB_Koneksi $koneksi;
    public string     $sql   = '';
    public array      $params = [];
    public string     $error = '';
    public ?DB_Hasil  $hasil  = null;

    public function __construct(DB_Koneksi $koneksi, string $sql)
    {
        $this->koneksi = $koneksi;
        $this->sql     = $sql;
    }

    /** Menyimpan nilai untuk setiap tanda tanya (?) sesuai tipenya */
    public function bind_param(string $tipe, array $nilai): bool
    {
        $hasil = [];

        foreach (array_values($nilai) as $i => $v) {

            if ($v === null) {
                $hasil[] = null;
                continue;
            }

            switch ($tipe[$i] ?? 's') {
                case 'i':
                    $hasil[] = (int) $v;
                    break;
                case 'd':
                    $hasil[] = (float) $v;
                    break;
                default:
                    $hasil[] = (string) $v;
            }
        }

        $this->params = $hasil;

        return true;
    }

    public function execute(): bool
    {
        $this->error = '';

        $sql = db_sql_siapkan($this->sql, count($this->params));

        if ($sql === false) {
            $this->error = 'Jumlah tanda tanya (?) di SQL tidak sama dengan jumlah datanya.';
            return false;
        }

        $hasil = $this->koneksi->panggil($sql, $this->params);

        if ($hasil === false) {
            $this->error = $this->koneksi->error;
            return false;
        }

        $this->hasil = new DB_Hasil($hasil['rows']);

        /* simpan id baris baru (kalau ada), mirip mysqli_insert_id */
        if (isset($hasil['rows'][0]['id'])) {
            $this->koneksi->insert_id = (int) $hasil['rows'][0]['id'];
        }

        return true;
    }

    public function get_result(): DB_Hasil
    {
        return $this->hasil ?? new DB_Hasil([]);
    }

    public function num_rows(): int
    {
        return $this->hasil instanceof DB_Hasil ? $this->hasil->num_rows() : 0;
    }

    public function close(): bool
    {
        return true;
    }
}


/* =====================================================================
   5. PENERJEMAH SQL: MySQL -> PostgreSQL
   ===================================================================== */

/**
 * Menyesuaikan penulisan SQL gaya MySQL supaya cocok di PostgreSQL.
 */
function db_sql_ubah(string $sql): string
{
    /* 1. Buang tanda backtick (`angka`) yang tidak dikenal PostgreSQL */
    $sql = str_replace('`', '', $sql);

    /* 2. LIKE -> ILIKE
          Di MySQL pencarian pakai LIKE tidak membedakan huruf besar/kecil,
          di PostgreSQL membedakan. ILIKE mengembalikan sifat MySQL itu. */
    $sql = preg_replace('/\bLIKE\b/i', 'ILIKE', $sql);

    /* 3. LIMIT 5,10 (MySQL) -> LIMIT 10 OFFSET 5 (PostgreSQL) */
    $sql = preg_replace('/\bLIMIT\s+(\d+)\s*,\s*(\d+)/i', 'LIMIT $2 OFFSET $1', $sql);

    /* 4. Fungsi-fungsi MySQL yang penulisannya beda di PostgreSQL */
    $ganti = [
        '/\bNOW\s*\(\s*\)/i'            => 'now()',
        '/\bCURDATE\s*\(\s*\)/i'        => 'CURRENT_DATE',
        '/\bCURTIME\s*\(\s*\)/i'        => 'CURRENT_TIME',
        '/\bRAND\s*\(\s*\)/i'           => 'random()',
        '/\bIFNULL\s*\(/i'              => 'coalesce(',
        '/\bUNIX_TIMESTAMP\s*\(\s*\)/i' => 'extract(epoch from now())::bigint',
    ];

    return trim((string) preg_replace(array_keys($ganti), array_values($ganti), $sql));
}


/**
 * Mengubah setiap tanda tanya (?) menjadi $1, $2, ... seperti yang
 * diminta PostgreSQL. Tanda tanya di dalam tanda kutip ('...' atau "...")
 * TIDAK diubah, karena itu bagian dari teks, bukan tempat data.
 *
 * @return string|false false kalau jumlah tanda tanya tidak cocok
 */
function db_sql_siapkan(string $sql, int $jumlah_data)
{
    $sql     = db_sql_ubah($sql);
    $keluar  = '';
    $nomor   = 0;
    $kutip   = '';                  // '' | "'" | '"'
    $panjang = strlen($sql);

    for ($i = 0; $i < $panjang; $i++) {

        $c = $sql[$i];

        if ($kutip === '') {

            if ($c === "'" || $c === '"') {
                $kutip   = $c;
                $keluar .= $c;
                continue;
            }

            if ($c === '?') {
                $nomor++;
                $keluar .= '$' . $nomor;
                continue;
            }

            $keluar .= $c;
            continue;
        }

        /* sedang di dalam tanda kutip */
        $keluar .= $c;

        if ($c === $kutip) {

            /* dua tanda kutip berurutan ('') berarti satu tanda kutip,
               jadi belum keluar dari teks */
            if ($i + 1 < $panjang && $sql[$i + 1] === $kutip) {
                $keluar .= $sql[$i + 1];
                $i++;
                continue;
            }

            $kutip = '';
        }
    }

    if ($nomor !== $jumlah_data) {
        return false;
    }

    return $keluar;
}


/* =====================================================================
   6. FUNGSI db_... YANG DIPAKAI HALAMAN
   ===================================================================== */

function db_connect($host = null, $user = null, $pass = null, $db = null, $port = null)
{
    $url   = db_ambil_setelan('SUPABASE_URL');
    $kunci = db_ambil_setelan('SUPABASE_SERVICE_KEY');

    if ($kunci === '') {
        $kunci = db_ambil_setelan('SUPABASE_ANON_KEY');
    }

    if ($url === '' || $kunci === '') {

        db_catat_error_koneksi('Alamat atau kunci Supabase belum diisi. '
            . 'Isi Environment Variable SUPABASE_URL dan SUPABASE_SERVICE_KEY di Vercel, '
            . 'atau buat berkas config/rahasia.php.');

        return false;
    }

    $bucket = db_ambil_setelan('SUPABASE_BUCKET', 'foto');

    return new DB_Koneksi($url, $kunci, $bucket);
}

function db_catat_error_koneksi(string $pesan): void
{
    $GLOBALS['db_error_koneksi'] = $pesan;
}

function db_connect_error(): string
{
    return (string) ($GLOBALS['db_error_koneksi'] ?? '');
}

function db_set_charset($koneksi, string $set): bool
{
    return true;        /* Supabase sudah memakai UTF-8 */
}

function db_query($koneksi, string $sql)
{
    if (!($koneksi instanceof DB_Koneksi)) {
        return false;
    }

    $hasil = $koneksi->panggil(db_sql_ubah($sql), []);

    if ($hasil === false) {
        return false;
    }

    if (isset($hasil['rows'][0]['id'])) {
        $koneksi->insert_id = (int) $hasil['rows'][0]['id'];
    }

    return new DB_Hasil($hasil['rows']);
}

function db_prepare($koneksi, string $sql)
{
    if (!($koneksi instanceof DB_Koneksi)) {
        return false;
    }

    return new DB_Stmt($koneksi, $sql);
}

function db_stmt_bind_param($stmt, string $tipe, &...$nilai): bool
{
    if (!($stmt instanceof DB_Stmt)) {
        return false;
    }

    return $stmt->bind_param($tipe, $nilai);
}

function db_stmt_execute($stmt): bool
{
    return $stmt instanceof DB_Stmt ? $stmt->execute() : false;
}

function db_stmt_get_result($stmt)
{
    return $stmt instanceof DB_Stmt ? $stmt->get_result() : new DB_Hasil([]);
}

function db_stmt_close($stmt): bool
{
    return true;
}

function db_stmt_error($stmt): string
{
    return $stmt instanceof DB_Stmt ? $stmt->error : '';
}

function db_fetch_assoc($hasil)
{
    return $hasil instanceof DB_Hasil ? $hasil->fetch_assoc() : null;
}

function db_num_rows($hasil): int
{
    return $hasil instanceof DB_Hasil ? $hasil->num_rows() : 0;
}

function db_insert_id($koneksi): int
{
    return $koneksi instanceof DB_Koneksi ? $koneksi->insert_id : 0;
}

function db_error($koneksi): string
{
    return $koneksi instanceof DB_Koneksi ? $koneksi->error : '';
}

function db_real_escape_string($koneksi, string $teks): string
{
    /* PostgreSQL: satu tanda kutip ditulis dua kali */
    return str_replace("'", "''", $teks);
}
