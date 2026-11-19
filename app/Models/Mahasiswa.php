<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    use HasFactory;

    // Tanpa ini Laravel mencari tabel "mahasiswas"
    protected $table = 'mahasiswa';

    // Kolom yang boleh diisi lewat create()/update() (mass assignment)
    protected $fillable = ['nim', 'nama', 'prodi', 'angkatan'];

    protected function casts(): array
    {
        return ['angkatan' => 'integer'];
    }

    /** Satu mahasiswa punya BANYAK surat (one-to-many). FK: surat.mahasiswa_id */
    public function surat(): HasMany
    {
        return $this->hasMany(Surat::class);
    }
}