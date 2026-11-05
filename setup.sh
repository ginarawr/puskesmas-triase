#!/usr/bin/env bash
# Setup sekali jalan (macOS / Linux / WSL / Git Bash): ./setup.sh
set -euo pipefail
cd "$(dirname "$0")"

need() { command -v "$1" >/dev/null 2>&1 || { echo "✗ '$1' belum terpasang. $2"; exit 1; }; }
need php "Pasang PHP 8.2 atau lebih baru."
need composer "Pasang Composer dari https://getcomposer.org"
php -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);' || { echo "✗ Butuh PHP 8.2+ (terpasang: $(php -r 'echo PHP_VERSION;'))"; exit 1; }
php -m | grep -qi '^pdo_sqlite$' || { echo "✗ Ekstensi PHP pdo_sqlite belum aktif (aktifkan di php.ini)."; exit 1; }

if [ ! -f artisan ]; then
  echo "→ Mengunduh kerangka Laravel 12 ..."
  TMP="$(mktemp -d)"
  composer create-project laravel/laravel "$TMP/skeleton" "^12.0" --no-install --no-scripts --no-interaction
  # Salin kerangka TANPA menimpa file milik proyek ini (-n)
  cp -Rn "$TMP/skeleton/." .
  rm -rf "$TMP"
fi

echo "→ Memasang dependensi ..."
composer install --no-interaction

[ -f .env ] || cp .env.example .env
php artisan key:generate --force
php setup/configure-env.php
mkdir -p database && touch database/database.sqlite

echo "→ Membuat database & data contoh ..."
php artisan migrate:fresh --seed --force
php artisan optimize:clear >/dev/null

cat <<MSG

✓ Selesai. Menjalankan server di http://127.0.0.1:8000

  Perawat : perawat@puskesmas.test  / password123   (atau NIP: PRW-001)
  Dokter  : dokter@puskesmas.test   / password123   (atau NIP: DR-001)

Berikutnya cukup jalankan:  php artisan serve
MSG

php artisan serve
