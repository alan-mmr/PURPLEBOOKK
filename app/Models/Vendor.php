<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendor';
    protected $primaryKey = 'idvendor';

    protected $fillable = [
        'nama_vendor',
        'user_id',
    ];

    /**
     * Akun user yang memiliki toko ini (role = vendor).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Menu-menu yang dimiliki vendor ini.
     */
    public function menus()
    {
        return $this->hasMany(Menu::class, 'idvendor', 'idvendor');
    }

    /**
     * Semua pesanan yang masuk ke vendor ini.
     */
    public function pesanans()
    {
        return $this->hasMany(Pesanan::class, 'idvendor', 'idvendor');
    }
}
