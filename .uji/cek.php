<?php
// Skrip uji otomatis: menjalankan langkah-langkah Tinker di modul dan memeriksa hasilnya.
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\StatusSurat;
use App\Models\{Mahasiswa, JenisSurat, Surat};
use Illuminate\Support\Facades\DB;

$ok = 0; $gagal = 0;
function cek(string $nama, bool $hasil) { global $ok, $gagal; $hasil ? $ok++ : $gagal++; echo ($hasil ? 'OK   ' : 'GAGAL') . " $nama\n"; }

// --- CREATE
$m = Mahasiswa::create(['nim' => '2301099', 'nama' => 'Rina Wulandari', 'prodi' => 'Sistem Informasi', 'angkatan' => 2023]);
cek('create mahasiswa dapat id', $m->id > 0);

$m2 = new Mahasiswa();
$m2->nim = '2301100'; $m2->nama = 'Joko'; $m2->prodi = 'Teknik Informatika'; $m2->angkatan = 2023;
$m2->save();
cek('new + save', $m2->exists);

// --- READ
cek('find', Mahasiswa::find($m->id)->nama === 'Rina Wulandari');
cek('where first', Mahasiswa::where('nim', '2201001')->first()->nama === 'Andi Pratama');
cek('count', Mahasiswa::count() === 24);
cek('where angkatan', Mahasiswa::where('angkatan', 2022)->orderBy('nama')->pluck('nama')->contains('Siti Rahma'));
try { Mahasiswa::findOrFail(9999); cek('findOrFail lempar exception', false); }
catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) { cek('findOrFail lempar exception', true); }

// --- UPDATE
$m->update(['prodi' => 'Teknik Informatika']);
cek('update', Mahasiswa::find($m->id)->prodi === 'Teknik Informatika');
$m->nama = 'Rina W.'; cek('isDirty', $m->isDirty('nama')); $m->save();
cek('save update', $m->fresh()->nama === 'Rina W.');

// --- mass assignment: kolom tidak di $fillable diabaikan (atau exception jika strict)
try { $x = Mahasiswa::create(['nim' => '2301101', 'nama' => 'X', 'prodi' => 'TI', 'angkatan' => 2023, 'id' => 777]); cek('fillable abaikan id', $x->id !== 777); }
catch (Throwable $e) { cek('fillable lempar exception: ' . class_basename($e), true); $x = null; }

// --- RELASI
$andi = Mahasiswa::where('nim', '2201001')->first();
cek('hasMany surat', $andi->surat->count() === 1);
$s = $andi->surat->first();
cek('belongsTo mahasiswa', $s->mahasiswa->nama === 'Andi Pratama');
cek('belongsTo jenisSurat', $s->jenisSurat->kode === 'SKAK');
cek('riwayat 3 baris', $s->riwayat->count() === 3);
cek('enum cast', $s->status === StatusSurat::Selesai);
cek('scope status', Surat::status(StatusSurat::Diproses)->count() === 1);
cek('withCount', Mahasiswa::withCount('surat')->where('nim', '2201001')->first()->surat_count === 1);

// --- create lewat relasi
$baru = $m->surat()->create(['jenis_surat_id' => 3, 'keperluan' => 'Penelitian skripsi di Diskominfo.']);
cek('create lewat relasi isi FK', $baru->mahasiswa_id === $m->id);

// --- N+1
DB::flushQueryLog(); DB::enableQueryLog();
foreach (Surat::all() as $su) { $su->mahasiswa->nama; }
$n1 = count(DB::getQueryLog());
DB::flushQueryLog();
foreach (Surat::with('mahasiswa')->get() as $su) { $su->mahasiswa->nama; }
$eager = count(DB::getQueryLog());
DB::disableQueryLog();
echo "     N+1: $n1 query, eager: $eager query (jumlah surat: " . Surat::count() . ")\n";
cek('N+1 vs eager', $n1 === Surat::count() + 1 && $eager === 2);

// --- TRANSAKSI
$sebelum = Surat::count();
try {
    DB::transaction(function () {
        Surat::create(['mahasiswa_id' => 1, 'jenis_surat_id' => 1, 'keperluan' => 'Uji rollback transaksi.']);
        throw new RuntimeException('gagal di tengah');
    });
} catch (RuntimeException $e) {}
cek('rollback transaksi', Surat::count() === $sebelum);

$aj = Surat::ajukan(['mahasiswa_id' => $m->id, 'jenis_surat_id' => 1, 'keperluan' => 'Uji ajukan dengan transaksi.']);
cek('ajukan: nomor + riwayat', str_starts_with($aj->nomor, 'SRT/') && $aj->riwayat()->count() === 1);
$aj->ubahStatus(StatusSurat::Diproses, 'cek');
cek('ubahStatus', $aj->fresh()->status === StatusSurat::Diproses && $aj->riwayat()->count() === 2);
try { $aj->ubahStatus(StatusSurat::Diajukan); cek('aturan status ditolak', false); } catch (DomainException $e) { cek('aturan status ditolak', true); }

// --- DELETE + cascade / restrict
$idSurat = $aj->id;
$m->delete();
cek('delete cascade surat', Surat::find($idSurat) === null && DB::table('riwayat_status')->where('surat_id', $idSurat)->count() === 0);
try { JenisSurat::find(1)->delete(); cek('restrict on delete', false); }
catch (Illuminate\Database\QueryException $e) { cek('restrict on delete', true); }

echo "\nHASIL: $ok OK, $gagal GAGAL\n";
exit($gagal ? 1 : 0);
