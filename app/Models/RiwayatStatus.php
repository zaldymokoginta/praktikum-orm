<?php

namespace App\Models;

use App\Enums\StatusSurat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatus extends Model
{
    protected $table = 'riwayat_status';

    protected $fillable = ['status', 'catatan'];

    protected function casts(): array
    {
        return ['status' => StatusSurat::class];
    }

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }
}