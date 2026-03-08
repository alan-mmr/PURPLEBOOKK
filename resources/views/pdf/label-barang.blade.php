<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Label Barang — TnJ No. 108</title>
    <style>
        /* @page HARUS di atas, sebelum semua selector lain */
        @page {
            margin-top:    {{ $pageMarginTop }}mm;
            margin-bottom: {{ $pageMarginTop }}mm;
            margin-left:   {{ $pageMarginSide }}mm;
            margin-right:  {{ $pageMarginSide }}mm;
        }

        /* Reset — JANGAN pakai * karena bisa override @page di DomPDF */
        body, table, td, tr, span, hr, img, p, div {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7pt;
        }

        /* Tabel: border-separate untuk gap antar label, width 100% agar pas */
        table.label-sheet {
            border-collapse: separate;
            border-spacing: {{ $gapXMm }}mm {{ $gapYMm }}mm;
            table-layout: fixed;
            width: 100%;
        }

        /* Tiap baris */
        table.label-sheet tr {
            height: {{ $labelHeightMm }}mm;
        }

        /* Tiap sel = 1 label — TANPA width explicit, biar table-layout:fixed yang atur
           agar border 0.5pt tidak bikin overflow */
        table.label-sheet td {
            height: {{ $labelHeightMm }}mm;
            min-height: {{ $labelHeightMm }}mm;
            border: 0.5pt solid #cccccc;
            vertical-align: middle;
            text-align: center;
            padding: 2pt;
            overflow: hidden;
        }

        /* Slot kosong sebelum posisi awal cetak */
        table.label-sheet td.empty-slot {
            border: 0.5pt dashed #dddddd;
        }

        /* Konten dalam label */
        .label-content {
            display: block;
            width: 100%;
        }

        /* Nama barang — 20% dari tinggi label */
        .label-nama {
            font-size: {{ $fontNamaPt }}pt;
            font-weight: bold;
            color: #333333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            margin-bottom: 1pt;
        }

        /* Harga — 25% dari tinggi label (paling menonjol) */
        .label-harga {
            font-size: {{ $fontHargaPt }}pt;
            font-weight: bold;
            color: #7B2D8B;
            display: block;
            margin-bottom: 1pt;
        }

        /* ID Barang — 13% dari tinggi label */
        .label-id {
            font-size: {{ $fontIdPt }}pt;
            color: #666666;
            font-family: 'Courier New', Courier, monospace;
            display: block;
            margin-bottom: 1pt;
            letter-spacing: 0.5pt;
        }

        /* Area barcode — sekarang menampilkan SVG asli (Code128) dari picqer */
        .label-barcode {
            display: block;
            width: 100%;
            text-align: center;
            margin: 1pt 0;
            line-height: 0;
        }
        /* SVG barcode: full width, tinggi tetap */
        .label-barcode svg {
            width: 100%;
            height: 32pt;
            max-width: 100%;
        }

        /* Garis pemisah kecil dalam label */
        .label-divider {
            border: none;
            border-top: 0.3pt solid #dddddd;
            margin: 1pt 2pt;
        }

        /* Logo mini / watermark — 10% dari tinggi label */
        .label-brand {
            font-size: {{ $fontBrandPt }}pt;
            color: #bbbbbb;
            display: block;
        }
    </style>
</head>
<body>

{{--
    ────────────────────────────────────────────────────────────────
    TABEL LABEL 5×8
    $rows = array of 8 baris, masing-masing berisi 5 slot (bisa null)
    null = slot kosong (sebelum posisi start X,Y atau tidak dipakai)
    ────────────────────────────────────────────────────────────────
--}}
<table class="label-sheet">
    @foreach($rows as $rowIndex => $row)
    <tr>
        @foreach($row as $colIndex => $slot)
        @if($slot === null)
            {{--
                Slot kosong: harus ada konten (&nbsp;) agar DomPDF tidak
                collapse sel ini menjadi nol — yang menyebabkan label
                dicetak di posisi yang salah (terlalu ke atas/kiri).
            --}}
            <td class="empty-slot"><span style="color:transparent;">.</span></td>
        @else
            {{-- Slot berisi data barang --}}
            <td>
                <span class="label-content">

                    {{-- Nama barang (terpotong jika terlalu panjang) --}}
                    <span class="label-nama" title="{{ $slot->nama }}">
                        {{ Str::limit($slot->nama, 22) }}
                    </span>

                    <hr class="label-divider">

                    {{--
                        BARCODE ASLI Code128 (PNG base64)
                        Generate oleh picqer/php-barcode-generator di controller.
                        Di-embed sebagai data URI — DomPDF support penuh.
                    --}}
                    <span class="label-barcode">
                        <img src="data:image/png;base64,{{ $barcodes[$slot->id_barang] ?? '' }}"
                             alt="{{ $slot->id_barang }}"
                             style="width:100%; height:{{ $barcodeHeightPt }}pt; display:block;">
                    </span>

                    {{-- ID Barang (di bawah barcode) --}}
                    <span class="label-id">{{ $slot->id_barang }}</span>

                    <hr class="label-divider">

                    {{-- Harga — elemen paling penting di label harga --}}
                    <span class="label-harga">
                        Rp {{ number_format($slot->harga, 0, ',', '.') }}
                    </span>

                    {{-- Branding mini --}}
                    <span class="label-brand">PURPLEBOOK</span>

                </span>
            </td>
        @endif
        @endforeach
    </tr>
    @endforeach
</table>

</body>
</html>
