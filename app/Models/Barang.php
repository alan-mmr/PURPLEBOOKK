<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    /**
     * Nama tabel di database.
     */
    protected $table = 'barang';

    /**
     * Primary key tabel barang.
     * Menggunakan id_barang (varchar), bukan id integer standar.
     */
    protected $primaryKey = 'id_barang';

    /**
     * Tipe primary key adalah string (bukan integer).
     */
    protected $keyType = 'string';

    /**
     * id_barang BUKAN auto-increment Laravel,
     * karena nilainya diisi oleh trigger PostgreSQL.
     */
    public $incrementing = false;

    /**
     * Nonaktifkan timestamps Laravel (created_at/updated_at),
     * karena tabel barang hanya punya kolom 'timestamp' tunggal
     * yang dihandle langsung di database.
     */
    public $timestamps = false;

    /**
     * Kolom yang boleh diisi via mass assignment.
     * id_barang dan timestamp TIDAK disertakan karena dihandle oleh trigger/DB.
     */
    protected $fillable = [
        'nama',
        'harga',
    ];

    /**
     * Cast tipe data kolom.
     */
    protected $casts = [
        'harga'     => 'integer',
        'timestamp' => 'datetime',
    ];
}
