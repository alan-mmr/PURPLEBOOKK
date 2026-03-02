@extends('layouts.main')

@section('title', 'Barang - PURPLEBOOK')

@push('styles')
{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
    /* ── Harga badge ──────────────────────────────────────────── */
    .badge-harga {
        font-size: 0.85em;
        background: linear-gradient(135deg, #7B2D8B, #a84fc2);
        color: white;
        padding: 3px 8px;
        border-radius: 12px;
    }

    /* ── Mode Selection: banner info ─────────────────────────── */
    #selectionBanner {
        display: none;
        background: linear-gradient(135deg, #7B2D8B, #a84fc2);
        color: white;
        border-radius: 8px;
        padding: 10px 16px;
        margin-bottom: 12px;
        align-items: center;
        justify-content: space-between;
    }
    #selectionBanner.active { display: flex; }

    /* ── Kolom checkbox: DataTables yang kendalikan visibility ─── */
    /* JANGAN ada CSS display:none untuk .col-checkbox              */
    /* karena akan bentrok dengan table.column(0).visible() API     */

    /* ── Sort toggle button active state ─────────────────────── */
    #btnSortToggle.sort-desc .icon-asc  { display: none; }
    #btnSortToggle.sort-asc  .icon-desc { display: none; }
    #btnSortToggle:not(.sort-asc):not(.sort-desc) .icon-asc { display: none; }

    /* ── Grid Preview Label (5x8) ────────────────────────────── */
    .label-grid-preview {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 2px;
        border: 1px solid #6c757d;
        padding: 4px;
        background: #f8f9fa;
        border-radius: 4px;
    }
    .label-cell { height: 22px; background: #dee2e6; border-radius: 2px; transition: background 0.15s; }
    .label-cell.active-start  { background: #7B2D8B; }
    .label-cell.active-filled { background: #b579c8; }
    .label-cell.empty-before  { background: #f8f9fa; border: 1px dashed #ced4da; }

    /* Sembunyikan aksi saat selection mode */
    table#tableBarang td.col-aksi,
    table#tableBarang th.col-aksi {
        transition: opacity 0.2s;
    }
    .selection-mode-active td.col-aksi,
    .selection-mode-active th.col-aksi {
        opacity: 0.35;
        pointer-events: none;
    }

    /* ── Tombol close (×) di modal header ────────────────────── */
    #modalKoordinat .modal-header .close {
        color: #fff !important;
        opacity: 1;
        font-size: 1.6rem;
        font-weight: 700;
        line-height: 1;
        text-shadow: 0 1px 2px rgba(0,0,0,0.4);
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.4);
        border-radius: 6px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        cursor: pointer;
        transition: background 0.2s;
    }
    #modalKoordinat .modal-header .close:hover {
        background: rgba(255,255,255,0.3);
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-tag-multiple"></i>
        </span> Manajemen Barang
    </h3>
    <nav>
        <a href="{{ route('barang.create') }}" id="btnTambah" class="btn btn-gradient-primary btn-sm">
            <i class="mdi mdi-plus"></i> Tambah Barang
        </a>
    </nav>
</div>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Barang Toko PurpleBook</h4>

                {{-- ── Banner mode pilih (muncul saat selection mode) ── --}}
                <div id="selectionBanner">
                    <span>
                        <i class="mdi mdi-checkbox-marked-outline mr-1"></i>
                        Mode Pilih Barang — centang barang yang ingin dicetak labelnya.
                        <strong id="bannerCount" class="ml-2">0 dipilih</strong>
                    </span>
                    <div>
                        <button type="button" id="btnKonfirmasi" class="btn btn-sm btn-light mr-2" disabled>
                            <i class="mdi mdi-check"></i> Konfirmasi
                        </button>
                        <button type="button" id="btnBatal" class="btn btn-sm btn-outline-light">
                            <i class="mdi mdi-close"></i> Batal
                        </button>
                    </div>
                </div>

                {{-- ── Toolbar normal mode ─────────────────────────────── --}}
                <div id="toolbarNormal" class="mb-3 d-flex align-items-center flex-wrap" style="gap:8px;">
                    {{-- Tombol masuk ke mode pilih --}}
                    <button type="button" id="btnMasukPilih" class="btn btn-gradient-info btn-sm">
                        <i class="mdi mdi-printer"></i> Cetak Label
                    </button>

                    {{-- Toggle sort terbaru/terlama --}}
                    <button type="button" id="btnSortToggle" class="btn btn-outline-secondary btn-sm sort-desc">
                        <span class="icon-desc">
                            <i class="mdi mdi-sort-descending"></i> Terbaru
                        </span>
                        <span class="icon-asc">
                            <i class="mdi mdi-sort-ascending"></i> Terlama
                        </span>
                    </button>
                </div>

                {{-- ── Tabel DataTables ─────────────────────────────────── --}}
                <div class="table-responsive" id="tableWrapper">
                    <table id="tableBarang" class="table table-striped table-hover" width="100%">
                        <thead>
                            <tr>
                                <th class="col-checkbox" width="40px">✓</th>
                                <th>No</th>
                                <th>ID Barang</th>
                                <th>Nama</th>
                                <th>Harga</th>
                                <th>Tanggal</th>
                                <th class="col-aksi">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($barang as $index => $item)
                            <tr>
                                <td class="col-checkbox">
                                    <input type="checkbox" class="check-barang" value="{{ $item->id_barang }}">
                                </td>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="badge badge-dark" style="font-family:monospace;letter-spacing:1px;">
                                        {{ $item->id_barang }}
                                    </span>
                                </td>
                                <td><strong>{{ $item->nama }}</strong></td>
                                <td>
                                    <span class="badge-harga">
                                        Rp {{ number_format($item->harga, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->timestamp)->format('d M Y, H:i') }}</td>
                                <td class="col-aksi">
                                    <a href="{{ route('barang.edit', $item->id_barang) }}"
                                        class="btn btn-sm btn-gradient-info">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('barang.destroy', $item->id_barang) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-gradient-danger">
                                            <i class="mdi mdi-delete"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────
     MODAL CETAK LABEL — Qty per Barang + Koordinat + Preview Grid
     ───────────────────────────────────────────────────────────────── --}}
<div class="modal fade" id="modalKoordinat" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="mdi mdi-printer"></i>
                    Konfigurasi Cetak Label — Kertas TnJ No. 108
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form id="formCetakLabel" method="POST" action="{{ route('barang.cetakLabel') }}" target="_blank">
                @csrf
                <div id="hiddenBarangIds"></div>

                <div class="modal-body">
                    <div class="row">

                        {{-- ── Kolom Kiri: Qty per Barang + Pengaturan ── --}}
                        <div class="col-md-6">

                            {{-- Tabel qty per barang --}}
                            <label class="font-weight-bold mb-2">
                                <i class="mdi mdi-format-list-numbered text-primary"></i>
                                Jumlah Label per Barang
                            </label>
                            <div class="table-responsive mb-3" style="max-height:220px; overflow-y:auto;">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Nama Barang</th>
                                            <th style="width:90px;">Jumlah Label</th>
                                        </tr>
                                    </thead>
                                    <tbody id="qtyTableBody">
                                    </tbody>
                                </table>
                            </div>

                            {{-- Info slot tersedia --}}
                            <div id="slotInfo" class="alert py-2 alert-info mb-3">
                                <small>
                                    <i class="mdi mdi-information-outline"></i>
                                    Total label: <strong id="totalQty">0</strong> /
                                    <strong id="availableSlots">40</strong> slot tersedia
                                    <span id="slotWarning" class="text-danger d-none font-weight-bold ml-1">
                                        ⚠ Melebihi kapasitas! Label selebihnya tidak akan dicetak.
                                    </span>
                                </small>
                            </div>

                            {{-- Ukuran Kertas --}}
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="mdi mdi-file-document-outline text-primary"></i> Ukuran Kertas
                                </label>
                                <select name="paper_size" class="form-control form-control-sm">
                                    <option value="A4" selected>A4 (default — TnJ No. 108)</option>
                                    <option value="A3">A3</option>
                                    <option value="Letter">Letter (US)</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            <i class="mdi mdi-arrow-right text-primary"></i>
                                            Kolom Awal (X) <small class="text-muted">1–5</small>
                                        </label>
                                        <input type="number" name="start_x" id="startX"
                                            class="form-control form-control-sm" value="1" min="1" max="5">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            <i class="mdi mdi-arrow-down text-primary"></i>
                                            Baris Awal (Y) <small class="text-muted">1–8</small>
                                        </label>
                                        <input type="number" name="start_y" id="startY"
                                            class="form-control form-control-sm" value="1" min="1" max="8">
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- ── Kolom Kanan: Preview Grid ── --}}
                        <div class="col-md-6">
                            <label class="font-weight-bold">
                                <i class="mdi mdi-grid text-primary"></i>
                                Preview Posisi Label (5×8 = 40 slot)
                            </label>
                            <div id="gridPreview" class="label-grid-preview mb-2"></div>
                            <small class="text-muted">
                                <span class="badge" style="background:#7B2D8B;color:white;">■</span> posisi awal &nbsp;
                                <span class="badge" style="background:#b579c8;color:white;">■</span> label diisi &nbsp;
                                <span class="badge badge-light border">□</span> kosong/dilewati
                            </small>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="mdi mdi-information-outline"></i>
                                    Label melebihi sisa slot akan otomatis dipotong.
                                </small>
                            </div>
                        </div>

                    </div>
                </div>
            </form>{{-- Form SELESAI sebelum footer --}}

            <div class="modal-footer">
                {{-- Batal: type button, id eksplisit, JS handler sebagai backup --}}
                <button type="button" id="btnBatalModal" class="btn btn-danger">
                    <i class="mdi mdi-close"></i> Batal
                </button>
                {{-- Submit via form= attribute — terhubung ke form di atas --}}
                <button type="submit" form="formCetakLabel" id="btnGeneratePdf" class="btn btn-gradient-primary">
                    <i class="mdi mdi-file-pdf-box"></i> Generate PDF
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function () {

    // ── 1. Inisialisasi DataTables ─────────────────────────────────
    var table = $('#tableBarang').DataTable({
        language: {
            search:      "Cari:",
            lengthMenu:  "Tampilkan _MENU_ data",
            info:        "Menampilkan _START_–_END_ dari _TOTAL_ barang",
            infoEmpty:   "Tidak ada data",
            zeroRecords: "Tidak ditemukan data yang cocok",
            paginate: { first:"Pertama", last:"Terakhir", next:"Selanjutnya", previous:"Sebelumnya" }
        },
        order: [[5, 'desc']],
        columnDefs: [
            { orderable: false, targets: [0, 6] },
            { visible: false, targets: [0] }
        ]
    });

    // ── 2. Toggle Sort Terbaru / Terlama ──────────────────────────
    var sortOrder = 'desc';
    $('#btnSortToggle').on('click', function () {
        if (sortOrder === 'desc') {
            sortOrder = 'asc';
            table.order([5, 'asc']).draw();
            $(this).removeClass('sort-desc').addClass('sort-asc');
        } else {
            sortOrder = 'desc';
            table.order([5, 'desc']).draw();
            $(this).removeClass('sort-asc').addClass('sort-desc');
        }
    });

    // ── 3. Mode Pilih (Selection Mode) ────────────────────────────
    function enterSelectionMode() {
        // DataTables column visibility — harus dipanggil setelah DataTables ready
        try { table.column(0).visible(true); } catch(e) { console.warn('col vis', e); }

        $('#toolbarNormal').hide();
        $('#btnTambah').hide();
        $('#selectionBanner').addClass('active');
        $('#tableBarang').addClass('selection-mode-active');

        // Uncheck semua checkbox
        table.rows().nodes().each(function(row) {
            $(row).find('.check-barang').prop('checked', false);
        });
        updateSelectionCount();
    }

    function exitSelectionMode() {
        try { table.column(0).visible(false); } catch(e) {}

        $('#toolbarNormal').show();
        $('#btnTambah').show();
        $('#selectionBanner').removeClass('active');
        $('#tableBarang').removeClass('selection-mode-active');

        table.rows().nodes().each(function(row) {
            $(row).find('.check-barang').prop('checked', false);
        });
        updateSelectionCount();
    }

    function updateSelectionCount() {
        // DataTables: checkbox bisa di semua halaman, bukan hanya yang visible
        var count = 0;
        table.rows().nodes().each(function(row) {
            if ($(row).find('.check-barang').is(':checked')) count++;
        });
        $('#bannerCount').text(count + ' dipilih');
        $('#btnKonfirmasi').prop('disabled', count === 0);
    }

    $('#btnMasukPilih').on('click', enterSelectionMode);
    $('#btnBatal').on('click', exitSelectionMode);
    $(document).on('change', '.check-barang', updateSelectionCount);

    // ── Klik baris = toggle checkbox (hanya saat selection mode aktif) ──
    // Kecuali klik pada tombol/form (Edit, Hapus, checkbox itu sendiri)
    $(document).on('click', '#tableBarang tbody tr', function (e) {
        // Cek apakah sedang dalam selection mode
        if (!$('#selectionBanner').hasClass('active')) return;

        // Abaikan jika klik pada tombol, link, form, atau checkbox sendiri
        if ($(e.target).closest('button, a, form, input').length) return;

        // Toggle checkbox di baris ini
        var cb = $(this).find('.check-barang');
        cb.prop('checked', !cb.prop('checked'));
        updateSelectionCount();
    });

    // Cursor jadi pointer saat hover di baris (selection mode)
    $(document).on('mouseenter', '#tableBarang tbody tr', function () {
        if ($('#selectionBanner').hasClass('active')) {
            $(this).css('cursor', 'pointer');
        }
    });

    // ── 4. Konfirmasi → buka modal dengan tabel qty ───────────────
    // Simpan data barang yang dipilih (id + nama) untuk ditampilkan di modal
    var selectedItems = []; // [{id, nama}, ...]

    $('#btnKonfirmasi').on('click', function () {
        selectedItems = [];
        // Iterasi semua baris DataTables (semua pages)
        table.rows().nodes().each(function(row) {
            var cb = $(row).find('.check-barang');
            if (cb.is(':checked')) {
                selectedItems.push({
                    id:   cb.val(),
                    nama: $(row).find('td').eq(3).text().trim()
                });
            }
        });

        if (selectedItems.length === 0) return;

        $('#startX').val(1);
        $('#startY').val(1);

        buildQtyTable();
        updateSlotInfo();
        updateGridPreview();

        $('#modalKoordinat').modal('show');
    });

    // ── 5. Bangun tabel qty di modal ──────────────────────────────
    function buildQtyTable() {
        var html = '';
        selectedItems.forEach(function (item) {
            html += '<tr>' +
                '<td>' + item.nama + '</td>' +
                '<td>' +
                    '<input type="number" ' +
                        'class="form-control form-control-sm qty-input" ' +
                        'data-id="' + item.id + '" ' +
                        'value="1" min="1" max="40">' +
                '</td>' +
                '</tr>';
        });
        $('#qtyTableBody').html(html);
    }

    // ── 6. Hitung total qty & slot tersedia + warning ─────────────
    function getTotalQty() {
        var total = 0;
        $('.qty-input').each(function () {
            total += Math.max(1, parseInt($(this).val()) || 1);
        });
        return total;
    }

    function getAvailableSlots() {
        var startX = Math.max(1, Math.min(5, parseInt($('#startX').val()) || 1));
        var startY = Math.max(1, Math.min(8, parseInt($('#startY').val()) || 1));
        return 40 - ((startY - 1) * 5 + (startX - 1));
    }

    function updateSlotInfo() {
        var total     = getTotalQty();
        var available = getAvailableSlots();

        $('#totalQty').text(total);
        $('#availableSlots').text(available);

        if (total > available) {
            $('#slotInfo').removeClass('alert-info').addClass('alert-warning');
            $('#slotWarning').removeClass('d-none');
        } else {
            $('#slotInfo').removeClass('alert-warning').addClass('alert-info');
            $('#slotWarning').addClass('d-none');
        }
    }

    // Update saat qty berubah
    $(document).on('input', '.qty-input', function () {
        updateSlotInfo();
        updateGridPreview();
    });

    // Update saat koordinat berubah
    $('#startX, #startY').on('input', function () {
        updateSlotInfo();
        updateGridPreview();
    });

    // ── 7. Preview grid ───────────────────────────────────────────
    function updateGridPreview() {
        var startX   = Math.max(1, Math.min(5, parseInt($('#startX').val()) || 1));
        var startY   = Math.max(1, Math.min(8, parseInt($('#startY').val()) || 1));
        var startIdx = (startY - 1) * 5 + (startX - 1);
        var available= 40 - startIdx;
        var total    = getTotalQty();
        var filled   = Math.min(total, available); // yang benar-benar tercetak (dipotong)
        var html     = '';

        for (var i = 0; i < 40; i++) {
            if      (i < startIdx)               html += '<div class="label-cell empty-before" title="Slot kosong (sebelum posisi awal)"></div>';
            else if (i === startIdx && filled>0)  html += '<div class="label-cell active-start" title="Posisi awal (X='+startX+', Y='+startY+')"></div>';
            else if (i < startIdx + filled)       html += '<div class="label-cell active-filled" title="Label ke-'+(i-startIdx+1)+'"></div>';
            else                                  html += '<div class="label-cell" title="Slot tidak dipakai"></div>';
        }
        $('#gridPreview').html(html);
    }

    // ── 8. Submit form: isi hidden inputs sebelum kirim ──────────
    $('#formCetakLabel').on('submit', function () {
        // Isi hidden input barang_ids[]
        var hiddenHtml = '';
        // Isi hidden input qty[id_barang] untuk setiap barang
        $('.qty-input').each(function () {
            var id  = $(this).data('id');
            var qty = Math.max(1, parseInt($(this).val()) || 1);
            hiddenHtml += '<input type="hidden" name="barang_ids[]" value="' + id + '">';
            hiddenHtml += '<input type="hidden" name="qty[' + id + ']" value="' + qty + '">';
        });
        $('#hiddenBarangIds').html(hiddenHtml);
        return true; // lanjut submit
    });

    // ── 9. Explicit close handlers (backup dari data-dismiss) ─────
    // Pastikan tombol ✕ dan Batal pasti nutup modal via JS langsung
    $(document).on('click', '#btnBatalModal', function () {
        $('#modalKoordinat').modal('hide');
    });

    // Tombol close (×) di header
    $(document).on('click', '#modalKoordinat .close', function () {
        $('#modalKoordinat').modal('hide');
    });

    updateGridPreview();

});
</script>
@endpush

