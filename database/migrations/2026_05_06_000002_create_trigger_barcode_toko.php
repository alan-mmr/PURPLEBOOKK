<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Buat function + trigger PostgreSQL untuk auto-generate barcode toko.
     *
     * Format barcode: YYMMDD## (8 karakter total)
     * Contoh: 26050601 = tanggal 6 Mei 2026, toko ke-1 hari itu
     *
     * Logika:
     *   - Hitung jumlah toko yang sudah diinsert hari ini
     *   - Tambah 1 untuk nomor urut toko baru
     *   - Gabungkan: 2 digit tahun + 2 digit bulan + 2 digit hari + 2 digit urut
     */
    public function up(): void
    {
        // Buat fungsi PostgreSQL yang akan dipakai trigger
        DB::unprepared('
            CREATE OR REPLACE FUNCTION fn_generate_barcode_toko()
            RETURNS TRIGGER AS $$
            DECLARE
                nr INTEGER DEFAULT 0;
            BEGIN
                -- Hitung toko yang diinsert pada tanggal hari ini
                SELECT COUNT(barcode) INTO nr
                FROM lokasi_toko
                WHERE DATE(created_at) = CURRENT_DATE;

                -- Generate barcode dengan format YYMMDD##
                NEW.barcode := CONCAT(
                    RIGHT(EXTRACT(YEAR FROM CURRENT_TIMESTAMP)::TEXT, 2),
                    LPAD(EXTRACT(MONTH FROM CURRENT_TIMESTAMP)::TEXT::TEXT, 2, \'0\'),
                    LPAD(EXTRACT(DAY FROM CURRENT_TIMESTAMP)::TEXT::TEXT, 2, \'0\'),
                    LPAD((nr + 1)::TEXT, 2, \'0\')
                );

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ');

        // Buat trigger yang memanggil function di atas sebelum setiap INSERT
        DB::unprepared('
            CREATE TRIGGER trigger_barcode_toko
            BEFORE INSERT ON lokasi_toko
            FOR EACH ROW
            EXECUTE FUNCTION fn_generate_barcode_toko();
        ');
    }

    /**
     * Hapus trigger dan function saat rollback.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_barcode_toko ON lokasi_toko;');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_generate_barcode_toko();');
    }
};
