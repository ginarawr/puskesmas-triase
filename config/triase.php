<?php

return [
    'name' => 'Puskesmas Harapan Sehat',
    // Zona waktu tampilan jam (WIB: Asia/Jakarta, WITA: Asia/Makassar, WIT: Asia/Jayapura)
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
    // Jeda pembaruan otomatis dashboard (detik)
    'refresh_seconds' => 10,
];
