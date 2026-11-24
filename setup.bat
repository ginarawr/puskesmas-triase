@echo off
REM Setup sekali jalan untuk Windows: klik dua kali atau jalankan setup.bat
setlocal
set "PATH=C:\php82;%PATH%"
cd /d "%~dp0"

where php >nul 2>nul || (echo [X] PHP belum terpasang. Butuh PHP 8.2+ & exit /b 1)
where composer >nul 2>nul || (echo [X] Composer belum terpasang. Lihat https://getcomposer.org & exit /b 1)
php -r "exit(version_compare(PHP_VERSION,'8.2.0','>=')?0:1);" || (echo [X] Butuh PHP 8.2 atau lebih baru & exit /b 1)
php -m | findstr /i "pdo_sqlite" >nul || (echo [X] Ekstensi pdo_sqlite belum aktif di php.ini & exit /b 1)

set "SKEL=%TEMP%\triase_skeleton_%RANDOM%"
if not exist artisan (
  echo =^> Mengunduh kerangka Laravel 12 ...
  call composer create-project laravel/laravel "%SKEL%" "^12.0" --no-install --no-scripts --no-interaction || exit /b 1
  REM Salin tanpa menimpa file milik proyek ini
  robocopy "%SKEL%" "%CD%" /E /XC /XN /XO /NFL /NDL /NJH /NJS /NP >nul
  if errorlevel 8 (echo [X] Gagal menyalin kerangka Laravel & exit /b 1)
  rmdir /s /q "%SKEL%"
)

echo =^> Memasang dependensi ...
call composer install --no-interaction || exit /b 1

if not exist .env copy .env.example .env >nul
php artisan key:generate --force
php setup\configure-env.php
if not exist database mkdir database
if not exist database\database.sqlite type nul > database\database.sqlite

echo =^> Membuat database dan data contoh ...
php artisan migrate:fresh --seed --force || exit /b 1
php artisan optimize:clear >nul

echo.
echo [OK] Selesai. Server berjalan di http://127.0.0.1:8000
echo   Perawat : perawat@puskesmas.test / password123  (atau NIP: PRW-001)
echo   Dokter  : dokter@puskesmas.test  / password123  (atau NIP: DR-001)
echo Berikutnya cukup jalankan: php artisan serve
echo.
php artisan serve
