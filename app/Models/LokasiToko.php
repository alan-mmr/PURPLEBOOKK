<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiToko extends Model
{
    /**
     * Nama tabel di database.
     */
    protected $table = 'lokasi_toko';

    /**
     * Primary key tabel lokasi_toko.
     * Menggunakan barcode (varchar), bukan id integer standar.
     */
    protected $primaryKey = 'barcode';

    /**
     * Tipe primary key adalah string (bukan integer).
     */
    protected $keyType = 'string';

    /**
     * barcode BUKAN auto-increment Laravel,
     * karena nilainya diisi oleh trigger PostgreSQL.
     */
    public $incrementing = false;

    /**
     * Nonaktifkan timestamps otomatis Laravel (updated_at),
     * karena tabel hanya punya created_at tunggal.
     */
    public $timestamps = false;

    /**
     * Kolom yang boleh diisi via mass assignment.
     * barcode dan created_at TIDAK disertakan karena dihandle oleh trigger/DB.
     */
    protected $fillable = [
        'nama_toko',
        'latitude',
        'longitude',
        'accuracy',
    ];

    /**
     * Cast tipe data kolom.
     */
    protected $casts = [
        'latitude'   => 'float',
        'longitude'  => 'float',
        'accuracy'   => 'float',
        'created_at' => 'datetime',
    ];
}
