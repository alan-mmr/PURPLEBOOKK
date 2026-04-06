<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    protected $table = 'detail_pesanan';
    protected $primaryKey = 'iddetail';
    public $timestamps = false; // tabel ini tidak pakai created_at/updated_at

    protected $fillable = [
        'idpesanan',
        'idmenu',
        'jumlah',
        'subtotal',
    ];

    /**
     * Pesanan induk dari detail ini.
     */
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'idpesanan', 'idpesanan');
    }

    /**
     * Menu yang dipesan.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'idmenu', 'idmenu');
    }
}
