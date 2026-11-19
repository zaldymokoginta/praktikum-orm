#!/bin/sh
# Uji cepat: db:show + perintah tinker yang dikutip di modul
set -e
cd /tmp/app
php artisan migrate:fresh --seed --force >/dev/null
php artisan db:show 2>&1 | head -12 || echo "db:show tidak tersedia"
php artisan tinker --execute='
use App\Models\{Mahasiswa, Surat, JenisSurat}; use App\Enums\StatusSurat; use Illuminate\Support\Facades\DB;
echo Mahasiswa::count(), PHP_EOL;
$andi = Mahasiswa::where("nim", "2201001")->first();
echo $andi->surat->first()->riwayat->pluck("catatan"), PHP_EOL;
echo Mahasiswa::where("angkatan", 2022)->orderBy("nama")->pluck("nama"), PHP_EOL;
DB::enableQueryLog();
Surat::all()->each(fn ($s) => $s->mahasiswa->nama);
echo count(DB::getQueryLog()), PHP_EOL;
DB::flushQueryLog();
Surat::with("mahasiswa")->get()->each(fn ($s) => $s->mahasiswa->nama);
echo count(DB::getQueryLog()), PHP_EOL;
try { Mahasiswa::create(["nim"=>"1","nama"=>"x","prodi"=>"x","angkatan"=>2020,"status"=>"x"]); } catch (Throwable $e) { echo get_class($e), ": ", $e->getMessage(), PHP_EOL; }
try { JenisSurat::find(1)->delete(); } catch (Throwable $e) { echo $e->getMessage(), PHP_EOL; }
$m = new App\Models\Mahasiswa; try { $m->fill(["nim"=>"1"])->forceFill(["x"=>1]); } catch (Throwable $e) {}
'
