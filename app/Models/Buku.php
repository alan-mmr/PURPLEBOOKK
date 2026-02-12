<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'buku';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'idbuku';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'kode',
        'judul',
        'pengarang',
        'idkategori',
    ];

    /**
     * Get the category that owns the book.
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'idkategori', 'idkategori');
    }
}
