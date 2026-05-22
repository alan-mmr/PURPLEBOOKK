<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'user_id',
        'kartu_nfc_id',
        'metode',
        'status',
        'keterangan',
        'scanned_by',
    ];

    // ─── Relasi ──────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kartuNfc()
    {
        return $this->belongsTo(KartuNfc::class, 'kartu_nfc_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────
    public function scopeHariIni(Builder $q): Builder
    {
        return $q->whereDate('created_at', today());
    }

    public function scopeMetode(Builder $q, string $metode): Builder
    {
        return $q->where('metode', $metode);
    }
}
