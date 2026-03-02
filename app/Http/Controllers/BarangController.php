<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarangController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // KONSTANTA UKURAN LABEL KERTAS TnJ No 108 (A4: 5 kolom × 8 baris)
    // Ubah di sini jika ingin ganti ukuran kertas atau jumlah label
    // ─────────────────────────────────────────────────────────────
    const LABEL_COLS       = 5;         // Jumlah kolom per halaman
    const LABEL_ROWS       = 8;         // Jumlah baris per halaman
    const LABEL_TOTAL      = 40;        // Total label per halaman (5 x 8)
    const LABEL_WIDTH_MM   = 37.0;      // Lebar tiap label dalam mm (A4 landscape ~ 38mm)
    const LABEL_HEIGHT_MM  = 33.8;      // Tinggi tiap label dalam mm (A4 portrait ~ 33.8mm)
    const PAGE_MARGIN_MM   = 5.0;       // Margin halaman PDF

    // ─────────────────────────────────────────────────────────────
    // INDEX - Tampilkan semua barang (DataTables)
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $barang = Barang::orderBy('timestamp', 'desc')->get();
        return view('pages.barang.index', compact('barang'));
    }

    // ─────────────────────────────────────────────────────────────
    // CREATE - Form tambah barang baru
    // ─────────────────────────────────────────────────────────────
    public function create()
    {
        return view('pages.barang.form');
    }

    // ─────────────────────────────────────────────────────────────
    // STORE - Simpan barang baru ke database
    // id_barang akan diisi otomatis oleh trigger PostgreSQL
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'  => 'required|string|max:50',
            'harga' => 'required|integer|min:0',
        ]);

        Barang::create($validated);

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil ditambahkan! ID otomatis digenerate oleh sistem.');
    }

    // ─────────────────────────────────────────────────────────────
    // SHOW - Detail barang (tidak dipakai, redirect ke index)
    // ─────────────────────────────────────────────────────────────
    public function show(string $id)
    {
        return redirect()->route('barang.index');
    }

    // ─────────────────────────────────────────────────────────────
    // EDIT - Form edit barang yang sudah ada
    // ─────────────────────────────────────────────────────────────
    public function edit(string $id)
    {
        $barang = Barang::findOrFail($id);
        return view('pages.barang.form', compact('barang'));
    }

    // ─────────────────────────────────────────────────────────────
    // UPDATE - Simpan perubahan nama dan harga barang
    // id_barang tidak bisa diubah (sudah digenerate oleh trigger)
    // ─────────────────────────────────────────────────────────────
    public function update(Request $request, string $id)
    {
        $barang = Barang::findOrFail($id);

        $validated = $request->validate([
            'nama'  => 'required|string|max:50',
            'harga' => 'required|integer|min:0',
        ]);

        $barang->update($validated);

        return redirect()->route('barang.index')
            ->with('success', 'Data barang berhasil diperbarui!');
    }

    // ─────────────────────────────────────────────────────────────
    // DESTROY - Hapus barang dari database
    // ─────────────────────────────────────────────────────────────
    public function destroy(string $id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil dihapus!');
    }

    // ─────────────────────────────────────────────────────────────
    // CETAK LABEL - Generate PDF label harga untuk kertas TnJ No 108
    //
    // Logika penomoran slot (1-indexed):
    //   Slot ke-N = (baris-1) × COLS + (kolom)
    //   Contoh: X=3, Y=2 → startIndex = (2-1)*5 + (3-1) = 7 (0-indexed)
    //   Artinya: 7 slot pertama kosong, mulai isi dari slot ke-8
    //
    // ─────────────────────────────────────────────────────────────
    public function cetakLabel(Request $request)
    {
        $request->validate([
            'barang_ids'    => 'required|array|min:1',
            'barang_ids.*'  => 'string|exists:barang,id_barang',
            'qty'           => 'required|array',
            'qty.*'         => 'integer|min:1|max:40',
            'start_x'       => 'required|integer|min:1|max:' . self::LABEL_COLS,
            'start_y'       => 'required|integer|min:1|max:' . self::LABEL_ROWS,
            'paper_size'    => 'required|string|in:A4,A3,Letter',
        ]);

        // Hitung posisi awal (0-indexed) dan slot tersedia
        $startX         = (int) $request->start_x;
        $startY         = (int) $request->start_y;
        $startIndex     = ($startY - 1) * self::LABEL_COLS + ($startX - 1);
        $availableSlots = self::LABEL_TOTAL - $startIndex;

        // Ambil data barang yang dipilih
        $selectedBarang = Barang::whereIn('id_barang', $request->barang_ids)
            ->orderBy('timestamp', 'asc')
            ->get()
            ->keyBy('id_barang');

        // Expand barang sesuai qty masing-masing
        // Contoh: Novel qty=3 → [Novel, Novel, Novel]
        $expanded = [];
        foreach ($request->barang_ids as $idBarang) {
            $qty  = (int) ($request->qty[$idBarang] ?? 1);
            $item = $selectedBarang[$idBarang] ?? null;
            if ($item) {
                for ($i = 0; $i < $qty; $i++) {
                    $expanded[] = $item;
                }
            }
        }

        // Potong jika total label melebihi slot yang tersedia
        // (slot tersedia = 40 - startIndex)
        $expanded = array_slice($expanded, 0, $availableSlots);

        // Buat array 40 slot: null = kosong, isi = data barang
        $slots = array_fill(0, self::LABEL_TOTAL, null);
        foreach ($expanded as $i => $item) {
            $slots[$startIndex + $i] = $item;
        }

        // Pecah menjadi 8 baris × 5 kolom
        $rows = array_chunk($slots, self::LABEL_COLS);

        // Generate barcode PNG (base64) untuk setiap barang unik
        // BarcodeGeneratorPNG → encode base64 → embed sebagai data URI di <img>
        // DomPDF support penuh untuk data:image/png;base64
        $barcodeGen = new BarcodeGeneratorPNG();
        $barcodes   = [];
        foreach ($selectedBarang as $idBarang => $item) {
            $barcodes[$idBarang] = base64_encode(
                $barcodeGen->getBarcode(
                    $idBarang,
                    BarcodeGeneratorPNG::TYPE_CODE_128,
                    2,   // lebar batang (px)
                    60   // tinggi barcode (px)
                )
            );
        }

        $data = [
            'rows'          => $rows,
            'barcodes'      => $barcodes,   // SVG string per id_barang
            'labelWidthMm'  => self::LABEL_WIDTH_MM,
            'labelHeightMm' => self::LABEL_HEIGHT_MM,
            'pageMargn'     => self::PAGE_MARGIN_MM,
            'cols'          => self::LABEL_COLS,
            'startX'        => $startX,
            'startY'        => $startY,
        ];

        $pdf = Pdf::loadView('pdf.label-barang', $data);
        $pdf->setPaper($request->paper_size, 'portrait');

        return $pdf->stream('Label_Barang_TnJ108_' . date('Ymd_His') . '.pdf');
    }

}
