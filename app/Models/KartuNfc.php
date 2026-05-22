<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class KartuNfc extends Model
{
    protected $table = 'kartu_nfc';

    protected $fillable = [
        'user_id',
        'serial_number',
        'tipe',
        'label',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relasi ──────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────
    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeNfc(Builder $q): Builder
    {
        return $q->where('tipe', 'nfc');
    }

    public function scopeQr(Builder $q): Builder
    {
        return $q->where('tipe', 'qr');
    }
}
