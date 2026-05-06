<?php

namespace App\Http\Controllers;

use App\Models\LokasiToko;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Barryvdh\DomPDF\Facade\Pdf;

class TokoController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // INDEX — List semua toko + form tambah toko
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $tokos = LokasiToko::orderBy('created_at', 'desc')->get();
        return view('pages.toko.index', compact('tokos'));
    }

    // ─────────────────────────────────────────────────────────────
    // STORE — Simpan toko baru (barcode auto-generate oleh trigger)
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'nama_toko' => 'required|string|max:50',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'accuracy'  => 'nullable|numeric|min:0',
        ]);

        LokasiToko::create([
            'nama_toko' => $request->nama_toko,
            'latitude'  => $request->latitude  ?? 0,
            'longitude' => $request->longitude ?? 0,
            'accuracy'  => $request->accuracy  ?? 0,
        ]);

        return redirect()->route('toko.index')
            ->with('success', 'Toko berhasil ditambahkan! Barcode otomatis digenerate.');
    }

    // ─────────────────────────────────────────────────────────────
    // UPDATE — Edit nama toko dan/atau koordinat GPS
    // ─────────────────────────────────────────────────────────────
    public function update(Request $request, $barcode)
    {
        $toko = LokasiToko::findOrFail($barcode);

        $request->validate([
            'nama_toko' => 'required|string|max:50',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'accuracy'  => 'nullable|numeric|min:0',
        ]);

        $toko->update([
            'nama_toko' => $request->nama_toko,
            'latitude'  => $request->latitude  ?? $toko->latitude,
            'longitude' => $request->longitude ?? $toko->longitude,
            'accuracy'  => $request->accuracy  ?? $toko->accuracy,
        ]);

        return redirect()->route('toko.index')
            ->with('success', "Toko \"{$toko->nama_toko}\" berhasil diperbarui!");
    }

    // ─────────────────────────────────────────────────────────────
    // DESTROY — Hapus toko
    // ─────────────────────────────────────────────────────────────
    public function destroy($barcode)
    {
        $toko = LokasiToko::findOrFail($barcode);
        $toko->delete();

        return redirect()->route('toko.index')
            ->with('success', 'Toko berhasil dihapus!');
    }

    // ─────────────────────────────────────────────────────────────
    // CETAK BARCODE — Generate PDF label barcode toko
    // ─────────────────────────────────────────────────────────────
    public function cetakBarcode(Request $request)
    {
        $request->validate([
            'barcode_ids'   => 'required|array|min:1',
            'barcode_ids.*' => 'string|exists:lokasi_toko,barcode',
        ]);

        // Ambil data toko yang dipilih
        $selectedTokos = LokasiToko::whereIn('barcode', $request->barcode_ids)
            ->orderBy('created_at', 'asc')
            ->get();

        // Generate barcode PNG (base64) untuk setiap toko
        $barcodeGen = new BarcodeGeneratorPNG();
        $barcodes   = [];
        foreach ($selectedTokos as $toko) {
            $barcodes[$toko->barcode] = base64_encode(
                $barcodeGen->getBarcode(
                    $toko->barcode,
                    BarcodeGeneratorPNG::TYPE_CODE_128,
                    2,
                    60
                )
            );
        }

        $data = [
            'tokos'    => $selectedTokos,
            'barcodes' => $barcodes,
        ];

        $pdf = Pdf::loadView('pdf.label-toko', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Label_Toko_' . date('Ymd_His') . '.pdf');
    }

    // ─────────────────────────────────────────────────────────────
    // LOOKUP — AJAX: cari toko by barcode → return JSON
    // ─────────────────────────────────────────────────────────────
    public function lookupToko(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $toko = LokasiToko::find($request->barcode);

        if (!$toko) {
            return response()->json(['error' => 'Toko tidak ditemukan'], 404);
        }

        return response()->json([
            'barcode'   => $toko->barcode,
            'nama_toko' => $toko->nama_toko,
            'latitude'  => $toko->latitude,
            'longitude' => $toko->longitude,
            'accuracy'  => $toko->accuracy,
        ]);
    }
}
