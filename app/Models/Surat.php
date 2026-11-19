<?php

namespace App\Models;

use App\Enums\StatusSurat;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Surat extends Model
{
    protected $table = 'surat';

    // nomor & status TIDAK ada di sini: diatur oleh sistem, bukan input user
    protected $fillable = ['mahasiswa_id', 'jenis_surat_id', 'keperluan'];

    protected $attributes = ['status' => 'diajukan'];

    protected function casts(): array
    {
        return ['status' => StatusSurat::class]; // string di DB <-> enum di PHP
    }

    // ---------------- RELASI ----------------

    /** Surat MILIK satu mahasiswa (many-to-one). FK: surat.mahasiswa_id */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /** FK: surat.jenis_surat_id (ditebak dari nama method jenisSurat) */
    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class);
    }

    /** Satu surat punya banyak riwayat status. FK: riwayat_status.surat_id */
    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class)->orderBy('id');
    }

    // ---------------- QUERY SCOPE ----------------

    /** Pemakaian: Surat::status(StatusSurat::Diproses)->get() */
    public function scopeStatus(Builder $query, StatusSurat $status): void
    {
        $query->where('status', $status->value);
    }

    // ---------------- ATURAN BISNIS ----------------

    /** Membuat surat + riwayat pertama dalam SATU TRANSAKSI (lihat langkah 7). */
    public static function ajukan(array $data): self
    {
        return DB::transaction(function () use ($data) {
            $surat = self::create($data);

            // nomor butuh id, jadi diisi setelah INSERT
            $surat->nomor = sprintf('SRT/%04d/%s', $surat->id, now()->year);
            $surat->save();

            $surat->riwayat()->create([
                'status'  => StatusSurat::Diajukan,
                'catatan' => 'Pengajuan dibuat oleh mahasiswa.',
            ]);

            return $surat;
        });
    }

    public function ubahStatus(StatusSurat $baru, ?string $catatan = null): void
    {
        if (! $this->status->bisaBerubahKe($baru)) {
            throw new DomainException(
                "Status {$this->status->label()} tidak bisa diubah ke {$baru->label()}."
            );
        }

        DB::transaction(function () use ($baru, $catatan) {
            $this->status = $baru;
            $this->save();

            $this->riwayat()->create(['status' => $baru, 'catatan' => $catatan]);
        });
    }
}