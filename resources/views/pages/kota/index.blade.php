@extends('layouts.main')

@section('title', 'Kota — Select & Select2')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-map-marker-multiple"></i>
        </span>
        Kota
    </h3>
</div>

<div class="row">

    {{-- ────────────────────────────────────────────────────── --}}
    {{-- Card 1: Select Biasa --}}
    {{-- ────────────────────────────────────────────────────── --}}
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-header bg-gradient-primary text-white">
                <h5 class="mb-0">Select</h5>
            </div>
            <div class="card-body">

                {{-- Input tambah kota --}}
                <form id="formKota1">
                    <div class="form-group">
                        <label for="inputKota1">Kota:</label>
                        <input type="text" class="form-control" id="inputKota1"
                               placeholder="Contoh: Jakarta" required>
                    </div>
                </form>
                <button type="button" id="btnTambah1" class="btn btn-gradient-success mb-3">
                    Tambahkan
                </button>

                {{-- Select biasa --}}
                <div class="form-group">
                    <label for="selectKota1">Select Kota:</label>
                    <select class="form-control" id="selectKota1" onchange="updateTerpilih('terpilih1', this.value)">
                        <option value="">-- Pilih Kota --</option>
                    </select>
                </div>

                {{-- Kota Terpilih --}}
                <div class="form-group">
                    <label>Kota Terpilih:</label>
                    <p id="terpilih1" class="text-muted font-weight-bold">—</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ────────────────────────────────────────────────────── --}}
    {{-- Card 2: Select2 --}}
    {{-- ────────────────────────────────────────────────────── --}}
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-header bg-gradient-info text-white">
                <h5 class="mb-0">select 2</h5>
            </div>
            <div class="card-body">

                {{-- Input tambah kota --}}
                <form id="formKota2">
                    <div class="form-group">
                        <label for="inputKota2">Kota:</label>
                        <input type="text" class="form-control" id="inputKota2"
                               placeholder="Contoh: Surabaya" required>
                    </div>
                </form>
                <button type="button" id="btnTambah2" class="btn btn-gradient-success mb-3">
                    Tambahkan
                </button>

                {{-- Select2 --}}
                <div class="form-group">
                    <label for="selectKota2">Select Kota:</label>
                    <select class="form-control" id="selectKota2" style="width:100%">
                        <option value="">-- Pilih Kota --</option>
                    </select>
                </div>

                {{-- Kota Terpilih --}}
                <div class="form-group">
                    <label>Kota Terpilih:</label>
                    <p id="terpilih2" class="text-muted font-weight-bold">—</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// ── Inisialisasi Select2 ───────────────────────────────────────
$(document).ready(function () {
    $('#selectKota2').select2({
        placeholder: '-- Pilih Kota --',
        allowClear: true
    });

    // Event Select2: update Kota Terpilih
    $('#selectKota2').on('select2:select select2:unselect', function () {
        const val = $(this).val();
        document.getElementById('terpilih2').textContent = val || '—';
    });
});

// ── Update teks "Kota Terpilih" (untuk select biasa) ──────────
function updateTerpilih(elId, val) {
    document.getElementById(elId).textContent = val || '—';
}

// ── Tombol Tambahkan — Card 1 (Select biasa + SK1 spinner) ────
document.getElementById('btnTambah1').addEventListener('click', function () {
    const form  = document.getElementById('formKota1');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    // Spinner SK1
    this.disabled = true;
    this.innerHTML = '<span class="spinner-grow spinner-grow-sm mr-1"></span> Memproses...';

    const kota   = document.getElementById('inputKota1').value.trim();
    const select = document.getElementById('selectKota1');
    const opt    = new Option(kota, kota);
    select.appendChild(opt);
    select.value = kota;
    updateTerpilih('terpilih1', kota);
    document.getElementById('inputKota1').value = '';

    // Reset button
    this.disabled = false;
    this.innerHTML = 'Tambahkan';
});

// ── Tombol Tambahkan — Card 2 (Select2 + SK1 spinner) ─────────
document.getElementById('btnTambah2').addEventListener('click', function () {
    const form = document.getElementById('formKota2');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    // Spinner SK1
    this.disabled = true;
    this.innerHTML = '<span class="spinner-grow spinner-grow-sm mr-1"></span> Memproses...';

    const kota = document.getElementById('inputKota2').value.trim();
    const opt  = new Option(kota, kota, true, true);
    $('#selectKota2').append(opt).trigger('change');
    document.getElementById('terpilih2').textContent = kota;
    document.getElementById('inputKota2').value = '';

    // Reset button
    this.disabled = false;
    this.innerHTML = 'Tambahkan';
});
</script>
@endpush
