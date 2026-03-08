@extends('layouts.main')

@section('title', 'Diskon Barang — DataTables')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@endpush

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-tag-heart"></i>
        </span>
        Diskon Barang
        <small class="text-muted ml-2" style="font-size:13px;">(DataTables)</small>
    </h3>
</div>

<div class="row">
    {{-- Form Input --}}
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Input Harga Diskon</h4>
                <p class="card-description text-muted">Data tidak tersimpan ke database.</p>
                <form id="formDiskon">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nama Barang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="inputNama"
                                       placeholder="Contoh: Novel Laskar Pelangi" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Harga Barang (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="inputHarga"
                                       placeholder="Contoh: 75000" min="0" required>
                            </div>
                        </div>
                    </div>
                </form>
                {{-- Button di LUAR form — SK1 --}}
                <button type="button" id="btnTambah" class="btn btn-gradient-success">
                    <i class="mdi mdi-plus-circle"></i> Submit
                </button>
            </div>
        </div>
    </div>

    {{-- DataTables --}}
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Diskon Barang</h4>
                <p class="card-description text-muted">Klik baris untuk edit atau hapus.</p>
                <table id="dtDiskon" class="table table-bordered table-hover" style="width:100%">
                    <thead class="thead-light">
                        <tr>
                            <th>ID Barang</th>
                            <th>Nama</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit/Hapus (SK3) --}}
<div class="modal fade" id="modalEditHapus" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title"><i class="mdi mdi-pencil"></i> Edit / Hapus Barang</h5>
                <button type="button" class="close text-white" data-dismiss="modal" onclick="$('#modalEditHapus').modal('hide')"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formModal">
                    <div class="form-group">
                        <label>ID Barang</label>
                        <input type="text" class="form-control bg-light" id="modalId" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modalNama" required>
                    </div>
                    <div class="form-group">
                        <label>Harga Barang (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="modalHarga" min="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnHapus" class="btn btn-danger">
                    <i class="mdi mdi-delete"></i> Hapus
                </button>
                <button type="button" id="btnUbah" class="btn btn-success ml-auto">
                    <i class="mdi mdi-content-save"></i> Ubah
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
let counter   = 1;
let dtTable   = null;
let activeRow = null; // DataTables row object

// ── Init DataTables ────────────────────────────────────────────
$(document).ready(function () {
    dtTable = $('#dtDiskon').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        columns: [
            { title: 'ID Barang' },
            { title: 'Nama' },
            { title: 'Harga' }
        ]
    });

    // SK3: hover pointer
    $('#dtDiskon tbody').on('mouseenter', 'tr', function () {
        $(this).css('cursor', 'pointer');
    });

    // SK3: klik row → modal
    $('#dtDiskon tbody').on('click', 'tr', function () {
        const data = dtTable.row(this).data();
        if (!data) return;

        activeRow = dtTable.row(this);
        document.getElementById('modalId').value    = data[0];
        document.getElementById('modalNama').value  = data[1];
        document.getElementById('modalHarga').value = $(this).data('harga');

        const btnUbah = document.getElementById('btnUbah');
        btnUbah.disabled = false;
        btnUbah.innerHTML = '<i class="mdi mdi-content-save"></i> Ubah';

        $('#modalEditHapus').modal('show');
    });
});

// ── Tambah (SK1 + SK2) ────────────────────────────────────────
document.getElementById('btnTambah').addEventListener('click', function () {
    const form = document.getElementById('formDiskon');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    // Spinner SK1
    this.disabled = true;
    this.innerHTML = '<span class="spinner-grow spinner-grow-sm mr-1"></span> Memproses...';

    const nama     = document.getElementById('inputNama').value.trim();
    const harga    = parseInt(document.getElementById('inputHarga').value);
    const id       = 'BRG-' + String(counter).padStart(3, '0');
    const hargaFmt = 'Rp ' + harga.toLocaleString('id-ID');

    // Tambah via DataTables API
    const newRow = dtTable.row.add([id, nama, hargaFmt]).draw();
    $(newRow.node()).data('harga', harga); // simpan harga asli

    counter++;
    document.getElementById('inputNama').value  = '';
    document.getElementById('inputHarga').value = '';
    this.disabled = false;
    this.innerHTML = '<i class="mdi mdi-plus-circle"></i> Submit';
});

// ── Hapus (SK3) ───────────────────────────────────────────────
document.getElementById('btnHapus').addEventListener('click', function () {
    if (activeRow) {
        activeRow.remove().draw();
        activeRow = null;
    }
    $('#modalEditHapus').modal('hide');
});

// ── Ubah (SK1 spinner + SK3) ──────────────────────────────────
document.getElementById('btnUbah').addEventListener('click', function () {
    const form = document.getElementById('formModal');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    this.disabled = true;
    this.innerHTML = '<span class="spinner-grow spinner-grow-sm mr-1"></span> Memproses...';

    const id       = document.getElementById('modalId').value;
    const nama     = document.getElementById('modalNama').value.trim();
    const harga    = parseInt(document.getElementById('modalHarga').value);
    const hargaFmt = 'Rp ' + harga.toLocaleString('id-ID');

    activeRow.data([id, nama, hargaFmt]).draw();
    $(activeRow.node()).data('harga', harga);

    $('#modalEditHapus').modal('hide');
    activeRow = null;
});
</script>
@endpush
