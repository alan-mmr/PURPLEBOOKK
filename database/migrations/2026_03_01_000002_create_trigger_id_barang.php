<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Buat function + trigger PostgreSQL untuk auto-generate id_barang.
     *
     * Format id_barang: YYMMDD## (8 karakter total)
     * Contoh: 26030101 = tanggal 1 Maret 2026, barang ke-1 hari itu
     *
     * Logika:
     *   - Hitung jumlah barang yang sudah diinsert hari ini
     *   - Tambah 1 untuk nomor urut barang baru
     *   - Gabungkan: 2 digit tahun + 2 digit bulan + 2 digit hari + 2 digit urut
     */
    public function up(): void
    {
        // Buat fungsi PostgreSQL yang akan dipakai trigger
        DB::unprepared('
            CREATE OR REPLACE FUNCTION fn_generate_id_barang()
            RETURNS TRIGGER AS $$
            DECLARE
                nr INTEGER DEFAULT 0;
            BEGIN
                -- Hitung barang yang diinsert pada tanggal hari ini
                SELECT COUNT(id_barang) INTO nr
                FROM barang
                WHERE DATE(timestamp) = CURRENT_DATE;

                -- Generate id_barang dengan format YYMMDD##
                NEW.id_barang := CONCAT(
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
            CREATE TRIGGER trigger_id_barang
            BEFORE INSERT ON barang
            FOR EACH ROW
            EXECUTE FUNCTION fn_generate_id_barang();
        ');
    }

    /**
     * Hapus trigger dan function saat rollback.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_id_barang ON barang;');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_generate_id_barang();');
    }
};
