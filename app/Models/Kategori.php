<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'kategori';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'idkategori';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_kategori',
    ];

    /**
     * Get the books for the category.
     */
    public function buku()
    {
        return $this->hasMany(Buku::class, 'idkategori', 'idkategori');
    }
}
