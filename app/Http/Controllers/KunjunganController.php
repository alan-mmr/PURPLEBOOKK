<?php

namespace App\Http\Controllers;

use App\Models\LokasiToko;
use Illuminate\Http\Request;

class KunjunganController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // Radius validasi kunjungan (meter) — fixed
    // ─────────────────────────────────────────────────────────────
    const RADIUS = 500;

    // ─────────────────────────────────────────────────────────────
    // TITIK AWAL — Halaman set koordinat toko (Solusi 1)
    // ─────────────────────────────────────────────────────────────
    public function titikAwal()
    {
        $tokos = LokasiToko::orderBy('nama_toko', 'asc')->get();
        return view('pages.kunjungan.titik-awal', compact('tokos'));
    }

    // ─────────────────────────────────────────────────────────────
    // UPDATE TITIK — Simpan koordinat GPS ke toko yang dipilih
    // ─────────────────────────────────────────────────────────────
    public function updateTitik(Request $request)
    {
        $request->validate([
            'barcode'   => 'required|string|exists:lokasi_toko,barcode',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'required|numeric|min:0',
        ]);

        $toko = LokasiToko::findOrFail($request->barcode);
        $toko->update([
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy'  => $request->accuracy,
        ]);

        return redirect()->route('kunjungan.titikAwal')
            ->with('success', "Koordinat toko \"{$toko->nama_toko}\" berhasil disimpan!");
    }

    // ─────────────────────────────────────────────────────────────
    // KUNJUNGAN — Halaman scan barcode + GPS validation (Solusi 2)
    // ─────────────────────────────────────────────────────────────
    public function kunjungan()
    {
        return view('pages.kunjungan.kunjungan', [
            'radius' => self::RADIUS,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // VALIDASI — AJAX: hitung Haversine → diterima/ditolak
    // POST /kunjungan/validasi
    // Body: { barcode, lat_sales, lng_sales, acc_sales }
    // ─────────────────────────────────────────────────────────────
    public function validasiKunjungan(Request $request)
    {
        $request->validate([
            'barcode'   => 'required|string|exists:lokasi_toko,barcode',
            'lat_sales' => 'required|numeric',
            'lng_sales' => 'required|numeric',
            'acc_sales' => 'required|numeric|min:0',
        ]);

        $toko = LokasiToko::findOrFail($request->barcode);

        // Cek apakah koordinat toko sudah diset
        if ($toko->latitude == 0 && $toko->longitude == 0) {
            return response()->json([
                'error' => 'Koordinat toko belum diset. Silakan set titik awal toko terlebih dahulu.'
            ], 422);
        }

        // Hitung jarak menggunakan Formula Haversine (Lampiran 2)
        $jarak = $this->haversine(
            $toko->latitude,
            $toko->longitude,
            $request->lat_sales,
            $request->lng_sales
        );

        // Threshold efektif = radius + accuracy toko + accuracy sales (Lampiran 3)
        $thresholdEfektif = self::RADIUS + $toko->accuracy + $request->acc_sales;

        $diterima = $jarak <= $thresholdEfektif;

        return response()->json([
            'diterima'          => $diterima,
            'jarak_meter'       => round($jarak, 2),
            'threshold_efektif' => round($thresholdEfektif, 2),
            'radius_dasar'      => self::RADIUS,
            'accuracy_toko'     => $toko->accuracy,
            'accuracy_sales'    => $request->acc_sales,
            'toko' => [
                'barcode'   => $toko->barcode,
                'nama_toko' => $toko->nama_toko,
                'latitude'  => $toko->latitude,
                'longitude' => $toko->longitude,
                'accuracy'  => $toko->accuracy,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // HAVERSINE — Hitung jarak antara 2 titik GPS (meter)
    // Formula dari Lampiran 2
    // ─────────────────────────────────────────────────────────────
    private function haversine($lat1, $lng1, $lat2, $lng2): float
    {
        $R    = 6371000; // radius bumi dalam meter
        $dLat = ($lat2 - $lat1) * M_PI / 180;
        $dLng = ($lng2 - $lng1) * M_PI / 180;

        $a = sin($dLat / 2) ** 2
           + cos($lat1 * M_PI / 180)
           * cos($lat2 * M_PI / 180)
           * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }
}
