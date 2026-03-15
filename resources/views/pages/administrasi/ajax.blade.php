@extends('layouts.main')

@section('title', 'Administrasi Wilayah — AJAX | PURPLEBOOK')

@push('styles')
<style>
    /* ── Card gradient header ─────────────────────────────────────── */
    .card-wilayah .card-header {
        background: linear-gradient(135deg, #7B2D8B, #a84fc2);
        color: white;
        border-radius: 8px 8px 0 0;
        padding: 14px 20px;
    }
    .card-wilayah .card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1rem;
    }
    .card-wilayah .card-header small {
        opacity: 0.85;
        font-size: 0.78rem;
    }

    /* ── Select styling ───────────────────────────────────────────── */
    .select-level {
        border: 2px solid #e2d4f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.92rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24'%3E%3Cpath fill='%237B2D8B' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-color: #fff;
        cursor: pointer;
    }
    .select-level:focus {
        border-color: #7B2D8B;
        box-shadow: 0 0 0 3px rgba(123,45,139,0.15);
        outline: none;
    }
    .select-level:disabled {
        background-color: #f5f0f8;
        color: #aaa;
        cursor: not-allowed;
        border-color: #e2d4f0;
    }

    /* ── Level badge ─────────────────────────────────────────────── */
    .level-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, #7B2D8B, #a84fc2);
        color: white;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
        margin-right: 8px;
    }

    /* ── Loading spinner pada select ─────────────────────────────── */
    .select-wrapper {
        position: relative;
    }
    .select-spinner {
        display: none;
        position: absolute;
        right: 36px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        border: 2px solid #e2d4f0;
        border-top-color: #7B2D8B;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }
    .select-spinner.active { display: block; }
    @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }

    /* ── Result card ─────────────────────────────────────────────── */
    .result-card {
        background: linear-gradient(135deg, #f9f4ff, #fff);
        border: 1px solid #e2d4f0;
        border-radius: 10px;
        padding: 18px 20px;
    }
    .result-item {
        display: flex;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px dashed #e8ddf5;
    }
    .result-item:last-child { border-bottom: none; }
    .result-label {
        font-size: 0.78rem;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        width: 110px;
        flex-shrink: 0;
    }
    .result-value {
        font-weight: 600;
        color: #3d1a4b;
        font-size: 0.9rem;
    }
    .result-value.empty { color: #bbb; font-style: italic; font-weight: 400; }

    /* ── Versi badge ─────────────────────────────────────────────── */
    .versi-badge {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.4);
        border-radius: 20px;
        padding: 3px 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-map-marker-radius"></i>
        </span>
        Administrasi Wilayah Indonesia
        <small class="text-muted ml-2" style="font-size:13px;">(jQuery AJAX)</small>
    </h3>
    <nav>
        <a href="{{ route('administrasi.axios') }}" class="btn btn-outline-primary btn-sm">
            <i class="mdi mdi-swap-horizontal"></i> Lihat Versi Axios
        </a>
    </nav>
</div>

<div class="row">
    {{-- ── Form Select Wilayah ── --}}
    <div class="col-lg-8 grid-margin">
        <div class="card card-wilayah">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5><i class="mdi mdi-map mr-1"></i> Pilih Wilayah Administrasi</h5>
                    <small>Pilih dari Level 1 (Provinsi) hingga Level 4 (Kelurahan)</small>
                </div>
                <span class="versi-badge">
                    <i class="mdi mdi-lightning-bolt"></i> jQuery AJAX
                </span>
            </div>
            <div class="card-body pt-4">

                {{-- Level 1: Provinsi --}}
                <div class="form-group">
                    <label class="font-weight-bold d-flex align-items-center mb-2">
                        <span class="level-badge">1</span>
                        Provinsi
                    </label>
                    <div class="select-wrapper">
                        <select id="selectProvinsi" class="form-control select-level w-100">
                            <option value="">— Pilih Provinsi —</option>
                            @foreach($provinsi as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Level 2: Kota/Kabupaten --}}
                <div class="form-group">
                    <label class="font-weight-bold d-flex align-items-center mb-2">
                        <span class="level-badge">2</span>
                        Kota / Kabupaten
                    </label>
                    <div class="select-wrapper">
                        <select id="selectKota" class="form-control select-level w-100" disabled>
                            <option value="">— Pilih Kota / Kabupaten —</option>
                        </select>
                        <div class="select-spinner" id="spinnerKota"></div>
                    </div>
                </div>

                {{-- Level 3: Kecamatan --}}
                <div class="form-group">
                    <label class="font-weight-bold d-flex align-items-center mb-2">
                        <span class="level-badge">3</span>
                        Kecamatan
                    </label>
                    <div class="select-wrapper">
                        <select id="selectKecamatan" class="form-control select-level w-100" disabled>
                            <option value="">— Pilih Kecamatan —</option>
                        </select>
                        <div class="select-spinner" id="spinnerKecamatan"></div>
                    </div>
                </div>

                {{-- Level 4: Kelurahan/Desa --}}
                <div class="form-group mb-0">
                    <label class="font-weight-bold d-flex align-items-center mb-2">
                        <span class="level-badge">4</span>
                        Kelurahan / Desa
                    </label>
                    <div class="select-wrapper">
                        <select id="selectKelurahan" class="form-control select-level w-100" disabled>
                            <option value="">— Pilih Kelurahan / Desa —</option>
                        </select>
                        <div class="select-spinner" id="spinnerKelurahan"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Panel Hasil Pilihan ── --}}
    <div class="col-lg-4 grid-margin">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="mdi mdi-check-circle text-success mr-1"></i>
                    Hasil Pilihan
                </h5>
                <p class="text-muted" style="font-size:0.82rem;">
                    Panel ini akan menampilkan wilayah yang kamu pilih secara lengkap.
                </p>
                <div class="result-card mt-3">
                    <div class="result-item">
                        <span class="result-label">Provinsi</span>
                        <span class="result-value empty" id="resultProvinsi">Belum dipilih</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Kota/Kab</span>
                        <span class="result-value empty" id="resultKota">Belum dipilih</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Kecamatan</span>
                        <span class="result-value empty" id="resultKecamatan">Belum dipilih</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Kelurahan</span>
                        <span class="result-value empty" id="resultKelurahan">Belum dipilih</span>
                    </div>
                </div>

                <button type="button" id="btnReset"
                    class="btn btn-outline-danger btn-sm btn-block mt-3">
                    <i class="mdi mdi-refresh"></i> Reset Pilihan
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Helper: Reset select ke kondisi kosong + disabled ─────────────
function resetSelect(selectId, label) {
    var $sel = $('#' + selectId);
    $sel.html('<option value="">— ' + label + ' —</option>');
    $sel.prop('disabled', true);
}

// ── Helper: Tampilkan/Sembunyikan spinner ──────────────────────────
function showSpinner(spinnerId, show) {
    if (show) $('#' + spinnerId).addClass('active');
    else       $('#' + spinnerId).removeClass('active');
}

// ── Helper: Update result panel ────────────────────────────────────
function updateResult(id, text) {
    var $el = $('#' + id);
    if (text) {
        $el.text(text).removeClass('empty');
    } else {
        $el.text('Belum dipilih').addClass('empty');
    }
}

// ── Event: Provinsi (Level 1) onChange ────────────────────────────
// Trigger AJAX → fetch Kota, dan reset Level 3 & Level 4
$('#selectProvinsi').on('change', function () {
    var provinsiId   = $(this).val();
    var provinsiText = $(this).find('option:selected').text();

    // Reset semua level bawah
    resetSelect('selectKota', 'Pilih Kota / Kabupaten');
    resetSelect('selectKecamatan', 'Pilih Kecamatan');
    resetSelect('selectKelurahan', 'Pilih Kelurahan / Desa');

    updateResult('resultProvinsi',  provinsiId ? provinsiText : null);
    updateResult('resultKota',      null);
    updateResult('resultKecamatan', null);
    updateResult('resultKelurahan', null);

    if (!provinsiId) return;

    // AJAX ke /wilayah/kota
    showSpinner('spinnerKota', true);
    $.ajax({
        url:      '{{ route("wilayah.kota") }}',
        method:   'GET',
        data:     { province_id: provinsiId },
        dataType: 'json',
        success: function (data) {
            var options = '<option value="">— Pilih Kota / Kabupaten —</option>';
            $.each(data, function (i, item) {
                options += '<option value="' + item.id + '">' + item.name + '</option>';
            });
            $('#selectKota').html(options).prop('disabled', false);
        },
        error: function () {
            alert('Gagal memuat data kota. Silakan coba lagi.');
        },
        complete: function () {
            showSpinner('spinnerKota', false);
        }
    });
});

// ── Event: Kota (Level 2) onChange ────────────────────────────────
// Trigger AJAX → fetch Kecamatan, dan reset Level 4
$('#selectKota').on('change', function () {
    var kotaId   = $(this).val();
    var kotaText = $(this).find('option:selected').text();

    // Reset level 3 & 4
    resetSelect('selectKecamatan', 'Pilih Kecamatan');
    resetSelect('selectKelurahan', 'Pilih Kelurahan / Desa');

    updateResult('resultKota',      kotaId ? kotaText : null);
    updateResult('resultKecamatan', null);
    updateResult('resultKelurahan', null);

    if (!kotaId) return;

    // AJAX ke /wilayah/kecamatan
    showSpinner('spinnerKecamatan', true);
    $.ajax({
        url:      '{{ route("wilayah.kecamatan") }}',
        method:   'GET',
        data:     { regency_id: kotaId },
        dataType: 'json',
        success: function (data) {
            var options = '<option value="">— Pilih Kecamatan —</option>';
            $.each(data, function (i, item) {
                options += '<option value="' + item.id + '">' + item.name + '</option>';
            });
            $('#selectKecamatan').html(options).prop('disabled', false);
        },
        error: function () {
            alert('Gagal memuat data kecamatan. Silakan coba lagi.');
        },
        complete: function () {
            showSpinner('spinnerKecamatan', false);
        }
    });
});

// ── Event: Kecamatan (Level 3) onChange ───────────────────────────
// Trigger AJAX → fetch Kelurahan
$('#selectKecamatan').on('change', function () {
    var kecamatanId   = $(this).val();
    var kecamatanText = $(this).find('option:selected').text();

    // Reset level 4
    resetSelect('selectKelurahan', 'Pilih Kelurahan / Desa');

    updateResult('resultKecamatan', kecamatanId ? kecamatanText : null);
    updateResult('resultKelurahan', null);

    if (!kecamatanId) return;

    // AJAX ke /wilayah/kelurahan
    showSpinner('spinnerKelurahan', true);
    $.ajax({
        url:      '{{ route("wilayah.kelurahan") }}',
        method:   'GET',
        data:     { district_id: kecamatanId },
        dataType: 'json',
        success: function (data) {
            var options = '<option value="">— Pilih Kelurahan / Desa —</option>';
            $.each(data, function (i, item) {
                options += '<option value="' + item.id + '">' + item.name + '</option>';
            });
            $('#selectKelurahan').html(options).prop('disabled', false);
        },
        error: function () {
            alert('Gagal memuat data kelurahan. Silakan coba lagi.');
        },
        complete: function () {
            showSpinner('spinnerKelurahan', false);
        }
    });
});

// ── Event: Kelurahan (Level 4) onChange ───────────────────────────
$('#selectKelurahan').on('change', function () {
    var val  = $(this).val();
    var text = $(this).find('option:selected').text();
    updateResult('resultKelurahan', val ? text : null);
});

// ── Reset semua pilihan ────────────────────────────────────────────
$('#btnReset').on('click', function () {
    $('#selectProvinsi').val('').trigger('change');
});
</script>
@endpush
