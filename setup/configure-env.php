<?php
// Menyesuaikan .env hasil kerangka Laravel untuk proyek ini (dipakai setup.sh & setup.bat).
$file = __DIR__ . '/../.env';
if (! file_exists($file)) { fwrite(STDERR, ".env tidak ditemukan\n"); exit(1); }

$s = file_get_contents($file);
$set = [
    'APP_NAME' => '"Puskesmas Wanita"',
    'APP_LOCALE' => 'id',
    'APP_FAKER_LOCALE' => 'id_ID',
    'APP_TIMEZONE' => 'Asia/Jakarta', // ganti ke Asia/Makassar (WITA) atau Asia/Jayapura (WIT) bila perlu
];
foreach ($set as $k => $v) {
    $line = "$k=$v";
    $s = preg_match("/^$k=.*/m", $s)
        ? preg_replace("/^$k=.*/m", $line, $s)
        : rtrim($s) . "\n$line\n";
}
file_put_contents($file, $s);
