<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarangController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // KONSTANTA UKURAN LABEL KERTAS TnJ No 108
    // ✏️  Ubah nilai berikut sesuai pengukuran fisik kertas:
    // ─────────────────────────────────────────────────────────────
    const LABEL_COLS       = 5;      // Jumlah kolom per halaman
    const LABEL_ROWS       = 8;      // Jumlah baris per halaman
    const LABEL_TOTAL      = 40;     // Total label (COLS × ROWS)
    const LABEL_WIDTH_MM   = 38.0;   // Lebar 1 label (mm)
    const LABEL_HEIGHT_MM  = 16.0;   // Tinggi 1 label (mm) — 1.8cm fisik - ~2pt padding

    // Margin kertas — diukur dari tepi kertas ke label pertama
    const PAPER_MARGIN_TOP_MM  = 3.0;  // 0.3 cm (atas & bawah)
    const PAPER_MARGIN_SIDE_MM = 4.0;  // 0.4 cm (kiri & kanan)

    // Jarak antar label di kertas fisik
    const LABEL_GAP_X_MM   = 3.0;   // 0.3 cm = 3mm (horizontal antar kolom)
    const LABEL_GAP_Y_MM   = 2.0;   // 0.2 cm = 2mm (vertikal antar baris)

    // Dimensi kertas A4
    const PAPER_WIDTH_MM   = 210.0;
    const PAPER_HEIGHT_MM  = 297.0;

    // ─────────────────────────────────────────────────────────────
    // INDEX - Tampilkan semua barang (DataTables)
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $barang = Barang::orderBy('timestamp', 'desc')->get();
        return view('pages.barang.index', compact('barang'));
    }

    // ─────────────────────────────────────────────────────────────
    // SK2 & SK3 — Diskon Barang (client-side, tanpa DB)
    // ─────────────────────────────────────────────────────────────
    public function diskonHtml()
    {
        return view('pages.barang.diskon-html');
    }

    public function diskonDatatables()
    {
        return view('pages.barang.diskon-datatables');
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

        // ── Gap antar label: dari pengukuran fisik (bukan auto) ────────────
        // border-spacing = jarak antar label di kertas fisik
        $gapX = self::LABEL_GAP_X_MM;
        $gapY = self::LABEL_GAP_Y_MM;

        // @page margin: kurangi gap karena border-spacing juga tambah jarak di tepi luar
        // Contoh: margin fisik 4mm, gap 3mm → @page margin = 1mm (1mm + 3mm spacing = 4mm total)
        $pageMarginSide = max(0, self::PAPER_MARGIN_SIDE_MM - $gapX);
        $pageMarginTop  = max(0, self::PAPER_MARGIN_TOP_MM  - $gapY);

        // ── Auto-calculate tinggi barcode (40% dari tinggi label) ────────
        // Generate PNG barcode dengan tinggi proporsional
        $barcodeHeightPx = (int) round(self::LABEL_HEIGHT_MM * 0.40 * 3.78); // mm → px (96dpi)
        $barcodeHeightPt = round(self::LABEL_HEIGHT_MM * 0.40 * 2.835, 1);   // mm → pt (CSS)

        // ── Font size proporsional dari tinggi label ─────────────────────────
        // Konversi tinggi label mm → pt (1mm = 2.835pt), lalu ambil persentase
        $labelHPt      = self::LABEL_HEIGHT_MM * 2.835;
        $fontNamaPt    = round($labelHPt * 0.20, 1); // 20% → nama barang
        $fontHargaPt   = round($labelHPt * 0.25, 1); // 25% → harga (terbesar)
        $fontIdPt      = round($labelHPt * 0.13, 1); // 13% → id barang
        $fontBrandPt   = round($labelHPt * 0.10, 1); // 10% → watermark brand

        // Generate barcode PNG (base64) untuk setiap barang unik
        $barcodeGen = new BarcodeGeneratorPNG();
        $barcodes   = [];
        foreach ($selectedBarang as $idBarang => $item) {
            $barcodes[$idBarang] = base64_encode(
                $barcodeGen->getBarcode(
                    $idBarang,
                    BarcodeGeneratorPNG::TYPE_CODE_128,
                    2,
                    max(20, $barcodeHeightPx) // minimum 20px
                )
            );
        }

        $data = [
            'rows'            => $rows,
            'barcodes'        => $barcodes,
            'labelWidthMm'    => self::LABEL_WIDTH_MM,
            'labelHeightMm'   => self::LABEL_HEIGHT_MM,
            'gapXMm'          => $gapX,
            'gapYMm'          => $gapY,
            'pageMarginTop'   => $pageMarginTop,
            'pageMarginSide'  => $pageMarginSide,
            'barcodeHeightPt' => $barcodeHeightPt,
            'fontNamaPt'      => $fontNamaPt,
            'fontHargaPt'     => $fontHargaPt,
            'fontIdPt'        => $fontIdPt,
            'fontBrandPt'     => $fontBrandPt,
            'cols'            => self::LABEL_COLS,
            'startX'          => $startX,
            'startY'          => $startY,
        ];

        $pdf = Pdf::loadView('pdf.label-barang', $data);
        $pdf->setPaper($request->paper_size, 'portrait');

        return $pdf->stream('Label_Barang_TnJ108_' . date('Ymd_His') . '.pdf');
    }

}
