#!/bin/sh
cd /tmp/app
php -v | head -1
rm -rf /tmp/coba && mkdir /tmp/coba
php artisan make:enum Enums/Coba --string 2>&1 | tail -2
find app -name 'Coba.php'; head -8 $(find app -name 'Coba.php') 2>/dev/null
php artisan make:migration create_coba_tabel_table 2>&1 | tail -1
grep -h "Schema::create" database/migrations/*create_coba_tabel_table.php
rm -f database/migrations/*create_coba_tabel_table.php $(find app -name 'Coba.php')
php artisan db:table mahasiswa 2>&1 | head -8
php artisan tinker --execute='echo App\Models\Mahasiswa::where("angkatan", 2022)->orderBy("nama")->toRawSql();'
echo
php artisan make:controller Api/CobaController --api 2>&1 | tail -1; rm -f app/Http/Controllers/Api/CobaController.php
