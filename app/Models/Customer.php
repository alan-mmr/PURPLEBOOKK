<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';
    protected $primaryKey = 'idcustomer';

    protected $fillable = [
        'nama',
        'foto_blob',
        'foto_path',
    ];

    /**
     * Cek apakah customer punya foto (blob atau file).
     */
    public function hasFoto(): bool
    {
        return $this->foto_blob !== null || $this->foto_path !== null;
    }
}
