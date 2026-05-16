<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Antrian extends Model
{
    protected $table = 'antrian';

    protected $fillable = [
        'idvendor',
        'nomor_antrian',
        'nama',
        'status',
        'called_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
    ];

    // ─── Relasi ──────────────────────────────────────────────────
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'idvendor', 'idvendor');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    /** Hanya antrian hari ini */
    public function scopeHariIni(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /** Filter per vendor */
    public function scopeVendor(Builder $query, $idvendor): Builder
    {
        return $query->where('idvendor', $idvendor);
    }

    /** Status menunggu */
    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->where('status', 'menunggu');
    }

    /** Status terlewat */
    public function scopeTerlewat(Builder $query): Builder
    {
        return $query->where('status', 'terlewat');
    }

    /** Status dipanggil */
    public function scopeDipanggil(Builder $query): Builder
    {
        return $query->where('status', 'dipanggil');
    }
}
