<?php

/* =====================================================================
   LAPISAN DATABASE MySQL (XAMPP)            config/db_mysql.php
   ---------------------------------------------------------------------
   Ini lapisan untuk dipakai di KOMPUTER SENDIRI (XAMPP).

   Isinya sengaja dibuat sederhana: setiap fungsi `db_...` hanya
   meneruskan perintah ke fungsi `mysqli_...` yang asli.

   Contoh:
       db_query($koneksi, $sql)   ->  mysqli_query($koneksi, $sql)

   Gunanya: kode halaman cukup memanggil `db_...` saja. Kalau website
   dipasang di Vercel (Supabase), `db_...` yang aktif adalah versi
   di config/db_supabase.php — kode halaman tidak perlu diubah.
   ===================================================================== */


/* ---------------------------------------------------------------------
   Menyambung & keterangan koneksi
--------------------------------------------------------------------- */

function db_connect(...$argumen)
{
    return mysqli_connect(...$argumen);
}

function db_connect_error(): string
{
    return (string) mysqli_connect_error();
}

function db_set_charset($koneksi, string $set): bool
{
    return mysqli_set_charset($koneksi, $set);
}


/* ---------------------------------------------------------------------
   Menjalankan perintah
--------------------------------------------------------------------- */

function db_query($koneksi, string $sql)
{
    return mysqli_query($koneksi, $sql);
}

function db_prepare($koneksi, string $sql)
{
    return mysqli_prepare($koneksi, $sql);
}

function db_stmt_bind_param($stmt, string $tipe, &...$nilai): bool
{
    return mysqli_stmt_bind_param($stmt, $tipe, ...$nilai);
}

function db_stmt_execute($stmt): bool
{
    return mysqli_stmt_execute($stmt);
}

function db_stmt_get_result($stmt)
{
    return mysqli_stmt_get_result($stmt);
}

function db_stmt_close($stmt): bool
{
    return mysqli_stmt_close($stmt);
}

function db_stmt_error($stmt): string
{
    return (string) mysqli_stmt_error($stmt);
}


/* ---------------------------------------------------------------------
   Membaca hasil & keterangan tambahan
--------------------------------------------------------------------- */

function db_fetch_assoc($hasil)
{
    return mysqli_fetch_assoc($hasil);
}

function db_num_rows($hasil): int
{
    return $hasil ? (int) mysqli_num_rows($hasil) : 0;
}

function db_insert_id($koneksi): int
{
    return (int) mysqli_insert_id($koneksi);
}

function db_error($koneksi): string
{
    return (string) mysqli_error($koneksi);
}

function db_real_escape_string($koneksi, string $teks): string
{
    return mysqli_real_escape_string($koneksi, $teks);
}
