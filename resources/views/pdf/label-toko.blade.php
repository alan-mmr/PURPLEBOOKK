<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Label Barcode Toko — PURPLEBOOK</title>
    <style>
        @page {
            margin-top: 8mm;
            margin-bottom: 8mm;
            margin-left: 10mm;
            margin-right: 10mm;
        }

        body, table, td, tr, span, img, p, div {
            margin: 0; padding: 0; box-sizing: border-box;
        }

        body { font-family: Arial, Helvetica, sans-serif; font-size: 8pt; }

        table.label-sheet {
            border-collapse: separate;
            border-spacing: 4mm 4mm;
            table-layout: fixed;
            width: 100%;
        }

        table.label-sheet tr { height: 28mm; }

        table.label-sheet td {
            height: 28mm;
            min-height: 28mm;
            border: 0.5pt solid #cccccc;
            vertical-align: middle;
            text-align: center;
            padding: 3pt;
            overflow: hidden;
        }

        .label-content { display: block; width: 100%; }

        .label-nama {
            font-size: 8pt;
            font-weight: bold;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            margin-bottom: 2pt;
        }

        .label-barcode {
            display: block;
            width: 100%;
            text-align: center;
            margin: 2pt 0;
            line-height: 0;
        }

        .label-kode {
            font-size: 7pt;
            color: #666;
            font-family: 'Courier New', Courier, monospace;
            display: block;
            margin-top: 2pt;
            letter-spacing: 1pt;
        }

        .label-brand {
            font-size: 6pt;
            color: #bbb;
            display: block;
            margin-top: 2pt;
        }

        .label-divider {
            border: none;
            border-top: 0.3pt solid #ddd;
            margin: 2pt 2pt;
        }
    </style>
</head>
<body>

<table class="label-sheet">
    @foreach($tokos->chunk(3) as $row)
    <tr>
        @foreach($row as $toko)
        <td>
            <span class="label-content">
                <span class="label-nama" title="{{ $toko->nama_toko }}">
                    {{ Str::limit($toko->nama_toko, 20) }}
                </span>

                <hr class="label-divider">

                {{-- Barcode PNG (base64) --}}
                <span class="label-barcode">
                    <img src="data:image/png;base64,{{ $barcodes[$toko->barcode] ?? '' }}"
                         alt="{{ $toko->barcode }}"
                         style="width:100%; height:32pt; display:block;">
                </span>

                <span class="label-kode">{{ $toko->barcode }}</span>

                <hr class="label-divider">

                <span class="label-brand">PURPLEBOOK — TOKO</span>
            </span>
        </td>
        @endforeach

        {{-- Isi sisa kolom dengan sel kosong jika kurang dari 3 --}}
        @for($i = $row->count(); $i < 3; $i++)
        <td style="border: 0.5pt dashed #eee;"><span style="color:transparent;">.</span></td>
        @endfor
    </tr>
    @endforeach
</table>

</body>
</html>
