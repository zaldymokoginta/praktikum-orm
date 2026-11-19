<?php

namespace Database\Seeders;

use App\Enums\StatusSurat;
use App\Models\JenisSurat;
use App\Models\Mahasiswa;
use App\Models\Surat;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data master (tetap)
        foreach ([
            'SKAK' => 'Surat Keterangan Aktif Kuliah',
            'PKL'  => 'Surat Pengantar Magang / PKL',
            'SIP'  => 'Surat Izin Penelitian',
            'SRB'  => 'Surat Rekomendasi Beasiswa',
        ] as $kode => $nama) {
            JenisSurat::create(['kode' => $kode, 'nama' => $nama]);
        }

        // 2. Data contoh yang mudah dikenali
        $andi = Mahasiswa::create(['nim' => '2201001', 'nama' => 'Andi Pratama',
            'prodi' => 'Teknik Informatika', 'angkatan' => 2022]);
        $siti = Mahasiswa::create(['nim' => '2201002', 'nama' => 'Siti Rahma',
            'prodi' => 'Teknik Informatika', 'angkatan' => 2022]);

        $s1 = Surat::ajukan(['mahasiswa_id' => $andi->id, 'jenis_surat_id' => 1,
            'keperluan' => 'Persyaratan tunjangan anak orang tua (PNS).']);
        $s1->ubahStatus(StatusSurat::Diproses, 'Sedang diverifikasi BAAK.');
        $s1->ubahStatus(StatusSurat::Selesai, 'Surat dapat diambil di BAAK.');

        $s2 = Surat::ajukan(['mahasiswa_id' => $siti->id, 'jenis_surat_id' => 2,
            'keperluan' => 'Magang di PT Maju Jaya selama 3 bulan.']);
        $s2->ubahStatus(StatusSurat::Diproses, 'Menunggu tanda tangan Kaprodi.');

        // 3. Data palsu dalam jumlah banyak (pakai factory)
        Mahasiswa::factory()->count(20)->create()->each(function (Mahasiswa $m) {
            Surat::ajukan([
                'mahasiswa_id'   => $m->id,
                'jenis_surat_id' => fake()->numberBetween(1, 4),
                'keperluan'      => 'Keperluan administrasi ' . fake()->sentence(4),
            ]);
        });
    }
}