#!/bin/sh
# Dijalankan di dalam container composer:2
# /sol = solusi-laravel, /uji = folder ini
set -e
cd /tmp
if [ ! -f app/artisan ]; then
  composer create-project laravel/laravel app --no-interaction --prefer-dist -q
fi
cd app
php artisan install:api --no-interaction >/dev/null 2>&1 || true
cp -r /sol/. .
php artisan --version
echo "--- migrate:fresh --seed"
php artisan migrate:fresh --seed --force
echo "--- migrate:status"
php artisan migrate:status | tail -6
echo "--- uji ORM"
cp /uji/cek.php .
php cek.php
echo "--- uji API"
php artisan migrate:fresh --seed --force >/dev/null
php artisan route:list --path=api/v1
php artisan serve --host=127.0.0.1 --port=8009 >/dev/null 2>&1 &
sleep 3
H='-H Accept:application/json -H Content-Type:application/json'
c() { printf '\n## %s\n' "$*"; curl -s -w '\n[HTTP %{http_code}]\n' $H "$@" | cut -c1-400; }
c http://127.0.0.1:8009/api/v1/jenis-surat
c "http://127.0.0.1:8009/api/v1/mahasiswa?q=andi"
c -X POST http://127.0.0.1:8009/api/v1/mahasiswa -d '{"nim":"2301050","nama":"Rina Wulandari","prodi":"Sistem Informasi","angkatan":2023}'
c -X POST http://127.0.0.1:8009/api/v1/mahasiswa -d '{"nim":"2201001","nama":"","prodi":"SI","angkatan":1990}'
c -X PATCH http://127.0.0.1:8009/api/v1/mahasiswa/23 -d '{"prodi":"Teknik Informatika"}'
c http://127.0.0.1:8009/api/v1/mahasiswa/1
c -X POST http://127.0.0.1:8009/api/v1/surat -d '{"mahasiswa_id":23,"jenis_surat_id":3,"keperluan":"Penelitian skripsi di Diskominfo."}'
c -X POST http://127.0.0.1:8009/api/v1/surat -d '{"mahasiswa_id":999,"jenis_surat_id":3,"keperluan":"x"}'
c -X PATCH http://127.0.0.1:8009/api/v1/surat/23/status -d '{"status":"diproses","catatan":"Verifikasi BAAK"}'
c -X PATCH http://127.0.0.1:8009/api/v1/surat/1/status -d '{"status":"diproses"}'
c "http://127.0.0.1:8009/api/v1/surat?status=diproses&per_page=2"
c -X DELETE http://127.0.0.1:8009/api/v1/mahasiswa/23
c http://127.0.0.1:8009/api/v1/surat/23
c http://127.0.0.1:8009/api/v1/mahasiswa/999
