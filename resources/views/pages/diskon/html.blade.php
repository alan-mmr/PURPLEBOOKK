@extends('layouts.main')

@section('title', 'Diskon Barang — HTML Table')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-tag-heart"></i>
        </span>
        Diskon Barang
        <small class="text-muted ml-2" style="font-size:13px;">(HTML Table)</small>
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
                <button type="button" id="btnTambah" class="btn btn-gradient-success">
                    <i class="mdi mdi-plus-circle"></i> Submit
                </button>
            </div>
        </div>
    </div>

    {{-- Tabel HTML --}}
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Diskon Barang</h4>
                <p class="card-description text-muted">Klik baris untuk edit atau hapus.</p>
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:130px;">ID Barang</th>
                            <th>Nama</th>
                            <th style="width:170px;">Harga</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyHtml">
                        <tr id="emptyRow">
                            <td colspan="3" class="text-center text-muted">
                                <i class="mdi mdi-information-outline"></i> Belum ada data
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit/Hapus --}}
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
<script>
let counter   = 1;
let activeRow = null;

document.getElementById('btnTambah').addEventListener('click', function () {
    const form = document.getElementById('formDiskon');
    if (!form.checkValidity()) { form.  reportValidity(); return; }

    this.disabled = true;
    this.innerHTML = '<span class="spinner-grow spinner-grow-sm mr-1"></span> Memproses...';

    const nama     = document.getElementById('inputNama').value.trim();
    const harga    = parseInt(document.getElementById('inputHarga').value);
    const id       = 'BRG-' + String(counter).padStart(3, '0');
    const hargaFmt = 'Rp ' + harga.toLocaleString('id-ID');

    const emptyRow = document.getElementById('emptyRow');
    if (emptyRow) emptyRow.remove();

    const tbody = document.getElementById('tbodyHtml');
    const tr    = tbody.insertRow();
    tr.style.cursor  = 'pointer';
    tr.dataset.id    = id;
    tr.dataset.nama  = nama;
    tr.dataset.harga = harga;
    tr.innerHTML     = `<td>${id}</td><td>${nama}</td><td>${hargaFmt}</td>`;
    tr.addEventListener('click', function () {
        activeRow = this;
        document.getElementById('modalId').value    = this.dataset.id;
        document.getElementById('modalNama').value  = this.dataset.nama;
        document.getElementById('modalHarga').value = this.dataset.harga;
        document.getElementById('btnUbah').disabled = false;
        document.getElementById('btnUbah').innerHTML = '<i class="mdi mdi-content-save"></i> Ubah';
        $('#modalEditHapus').modal('show');
    });

    counter++;
    document.getElementById('inputNama').value  = '';
    document.getElementById('inputHarga').value = '';
    this.disabled = false;
    this.innerHTML = '<i class="mdi mdi-plus-circle"></i> Submit';
});

document.getElementById('btnHapus').addEventListener('click', function () {
    if (activeRow) {
        activeRow.remove();
        activeRow = null;
        if (document.getElementById('tbodyHtml').rows.length === 0) {
            document.getElementById('tbodyHtml').innerHTML =
                '<tr id="emptyRow"><td colspan="3" class="text-center text-muted">' +
                '<i class="mdi mdi-information-outline"></i> Belum ada data</td></tr>';
        }
    }
    $('#modalEditHapus').modal('hide');
});

document.getElementById('btnUbah').addEventListener('click', function () {
    const form = document.getElementById('formModal');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    this.disabled = true;
    this.innerHTML = '<span class="spinner-grow spinner-grow-sm mr-1"></span> Memproses...';
    const nama  = document.getElementById('modalNama').value.trim();
    const harga = parseInt(document.getElementById('modalHarga').value);
    if (activeRow) {
        activeRow.dataset.nama  = nama;
        activeRow.dataset.harga = harga;
        activeRow.cells[1].textContent = nama;
        activeRow.cells[2].textContent = 'Rp ' + harga.toLocaleString('id-ID');
    }
    $('#modalEditHapus').modal('hide');
    activeRow = null;
});
</script>
@endpush
