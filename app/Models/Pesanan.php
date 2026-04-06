<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    protected $primaryKey = 'idpesanan';

    protected $fillable = [
        'nama_pemesan',
        'idvendor',
        'total_harga',
        'status',
        'status_bayar',
        'snap_token',
        'payment_type',
        'transaction_id',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    /**
     * Vendor tujuan pesanan ini.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'idvendor', 'idvendor');
    }

    /**
     * Item-item dalam pesanan ini.
     */
    public function detailPesanans()
    {
        return $this->hasMany(DetailPesanan::class, 'idpesanan', 'idpesanan');
    }

    /**
     * Cek apakah status pembayaran sudah final (immutable).
     * Jika sudah paid, status tidak boleh diubah lagi.
     */
    public function isBayarFinal(): bool
    {
        return $this->status_bayar === 'paid';
    }
}
