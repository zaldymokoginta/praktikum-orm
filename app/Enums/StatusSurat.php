<?php

namespace App\Enums;

enum StatusSurat: string
{
    case Diajukan = 'diajukan';
    case Diproses = 'diproses';
    case Selesai  = 'selesai';
    case Ditolak  = 'ditolak';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** @return StatusSurat[] */
    public function berikutnya(): array
    {
        return match ($this) {
            self::Diajukan => [self::Diproses, self::Ditolak],
            self::Diproses => [self::Selesai, self::Ditolak],
            self::Selesai, self::Ditolak => [],
        };
    }

    public function bisaBerubahKe(self $baru): bool
    {
        return in_array($baru, $this->berikutnya(), true);
    }
}