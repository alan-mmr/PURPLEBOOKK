<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $primaryKey = 'idmenu';

    protected $fillable = [
        'nama_menu',
        'harga',
        'path_gambar',
        'idvendor',
    ];

    /**
     * Vendor pemilik menu ini.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'idvendor', 'idvendor');
    }

    /**
     * Detail pesanan yang memuat menu ini.
     */
    public function detailPesanans()
    {
        return $this->hasMany(DetailPesanan::class, 'idmenu', 'idmenu');
    }
}
