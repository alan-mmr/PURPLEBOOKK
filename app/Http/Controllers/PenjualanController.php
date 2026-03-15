<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Barang;

class PenjualanController extends Controller
{
    // ── Halaman POS versi AJAX (jQuery) ───────────────────────────
    public function ajax()
    {
        return view('pages.pos.ajax');
    }

    // ── Halaman POS versi Axios ────────────────────────────────────
    public function axios()
    {
        return view('pages.pos.axios');
    }

    // ── API: Cari Barang by Kode ───────────────────────────────────
    // GET /pos/barang?kode=XXXXXXXX
    public function getBarang(Request $request)
    {
        $kode = trim($request->query('kode'));

        $barang = DB::table('barang')
            ->where('id_barang', $kode)
            ->first(['id_barang', 'nama', 'harga']);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success'   => true,
            'id_barang' => $barang->id_barang,
            'nama'      => $barang->nama,
            'harga'     => $barang->harga,
        ]);
    }

    // ── API: Simpan Transaksi Penjualan ───────────────────────────
    // POST /pos/store
    public function store(Request $request)
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.kode'   => 'required|string',
            'items.*.jumlah' => 'required|integer|min:1',
            'total'          => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 1. Insert ke tabel penjualan (header)
            $idPenjualan = DB::table('penjualan')->insertGetId([
                'total'     => $request->total,
                'timestamp' => now(),
            ], 'id_penjualan');

            // 2. Insert tiap item ke penjualan_detail
            foreach ($request->items as $item) {
                // Ambil harga terkini dari DB (agar akurat)
                $barang = DB::table('barang')
                    ->where('id_barang', $item['kode'])
                    ->first(['harga']);

                if (!$barang) continue;

                $subtotal = $barang->harga * $item['jumlah'];

                DB::table('penjualan_detail')->insert([
                    'id_penjualan' => $idPenjualan,
                    'id_barang'    => $item['kode'],
                    'jumlah'       => $item['jumlah'],
                    'subtotal'     => $subtotal,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'       => true,
                'message'       => 'Transaksi berhasil disimpan!',
                'id_penjualan'  => $idPenjualan,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
