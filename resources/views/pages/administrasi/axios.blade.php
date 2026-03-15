@extends('layouts.main')

@section('title', 'Administrasi Wilayah — Axios | PURPLEBOOK')

@push('styles')
<style>
    .card-wilayah .card-header {
        background: linear-gradient(135deg, #0d6efd, #6610f2);
        color: white;
        border-radius: 8px 8px 0 0;
        padding: 14px 20px;
    }
    .card-wilayah .card-header h5 { margin: 0; font-weight: 600; font-size: 1rem; }
    .card-wilayah .card-header small { opacity: 0.85; font-size: 0.78rem; }
    .select-level {
        border: 2px solid #d4d8f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.92rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24'%3E%3Cpath fill='%230d6efd' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-color: #fff;
        cursor: pointer;
    }
    .select-level:focus { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,0.15); outline: none; }
    .select-level:disabled { background-color: #f0f2f8; color: #aaa; cursor: not-allowed; border-color: #d4d8f0; }
    .level-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; border-radius: 50%;
        background: linear-gradient(135deg, #0d6efd, #6610f2);
        color: white; font-size: 0.75rem; font-weight: 700;
        flex-shrink: 0; margin-right: 8px;
    }
    .select-wrapper { position: relative; }
    .select-spinner {
        display: none; position: absolute; right: 36px; top: 50%; transform: translateY(-50%);
        width: 16px; height: 16px; border: 2px solid #d4d8f0;
        border-top-color: #0d6efd; border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }
    .select-spinner.active { display: block; }
    @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }
    .result-card { background: linear-gradient(135deg, #f0f4ff, #fff); border: 1px solid #d4d8f0; border-radius: 10px; padding: 18px 20px; }
    .result-item { display: flex; align-items: center; padding: 8px 0; border-bottom: 1px dashed #dde2f5; }
    .result-item:last-child { border-bottom: none; }
    .result-label { font-size: 0.78rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px; width: 110px; flex-shrink: 0; }
    .result-value { font-weight: 600; color: #1a2b6b; font-size: 0.9rem; }
    .result-value.empty { color: #bbb; font-style: italic; font-weight: 400; }
    .versi-badge { background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4); border-radius: 20px; padding: 3px 12px; font-size: 0.75rem; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-info text-white mr-2">
            <i class="mdi mdi-map-marker-radius"></i>
        </span>
        Administrasi Wilayah Indonesia
        <small class="text-muted ml-2" style="font-size:13px;">(Axios)</small>
    </h3>
    <nav>
        <a href="{{ route('administrasi.ajax') }}" class="btn btn-outline-primary btn-sm">
            <i class="mdi mdi-swap-horizontal"></i> Lihat Versi AJAX
        </a>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8 grid-margin">
        <div class="card card-wilayah">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5><i class="mdi mdi-map mr-1"></i> Pilih Wilayah Administrasi</h5>
                    <small>Pilih dari Level 1 (Provinsi) hingga Level 4 (Kelurahan)</small>
                </div>
                <span class="versi-badge"><i class="mdi mdi-alpha-a-box"></i> Axios</span>
            </div>
            <div class="card-body pt-4">

                {{-- Level 1: Provinsi --}}
                <div class="form-group">
                    <label class="font-weight-bold d-flex align-items-center mb-2">
                        <span class="level-badge">1</span> Provinsi
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
                        <span class="level-badge">2</span> Kota / Kabupaten
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
                        <span class="level-badge">3</span> Kecamatan
                    </label>
                    <div class="select-wrapper">
                        <select id="selectKecamatan" class="form-control select-level w-100" disabled>
                            <option value="">— Pilih Kecamatan —</option>
                        </select>
                        <div class="select-spinner" id="spinnerKecamatan"></div>
                    </div>
                </div>

                {{-- Level 4: Kelurahan --}}
                <div class="form-group mb-0">
                    <label class="font-weight-bold d-flex align-items-center mb-2">
                        <span class="level-badge">4</span> Kelurahan / Desa
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

    <div class="col-lg-4 grid-margin">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="mdi mdi-check-circle text-success mr-1"></i> Hasil Pilihan
                </h5>
                <p class="text-muted" style="font-size:0.82rem;">
                    Panel ini menampilkan wilayah yang kamu pilih secara lengkap.
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
                <button type="button" id="btnReset" class="btn btn-outline-danger btn-sm btn-block mt-3">
                    <i class="mdi mdi-refresh"></i> Reset Pilihan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Axios CDN --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
// ── Helper functions ───────────────────────────────────────────────
function resetSelect(id, label) {
    const sel = document.getElementById(id);
    sel.innerHTML = '<option value="">— ' + label + ' —</option>';
    sel.disabled  = true;
}
function showSpinner(id, show) {
    const el = document.getElementById(id);
    if (show) el.classList.add('active');
    else      el.classList.remove('active');
}
function updateResult(id, text) {
    const el = document.getElementById(id);
    if (text) { el.textContent = text; el.classList.remove('empty'); }
    else       { el.textContent = 'Belum dipilih'; el.classList.add('empty'); }
}
function buildOptions(data, placeholder) {
    let html = '<option value="">— ' + placeholder + ' —</option>';
    data.forEach(item => { html += `<option value="${item.id}">${item.name}</option>`; });
    return html;
}

// ── Event: Provinsi onChange ──────────────────────────────────────
document.getElementById('selectProvinsi').addEventListener('change', async function () {
    const provinsiId   = this.value;
    const provinsiText = this.options[this.selectedIndex].text;

    resetSelect('selectKota',      'Pilih Kota / Kabupaten');
    resetSelect('selectKecamatan', 'Pilih Kecamatan');
    resetSelect('selectKelurahan', 'Pilih Kelurahan / Desa');
    updateResult('resultProvinsi',  provinsiId ? provinsiText : null);
    updateResult('resultKota',      null);
    updateResult('resultKecamatan', null);
    updateResult('resultKelurahan', null);

    if (!provinsiId) return;

    showSpinner('spinnerKota', true);
    try {
        // Axios GET request ke /wilayah/kota
        const response = await axios.get('{{ route("wilayah.kota") }}', {
            params: { province_id: provinsiId }
        });
        const sel = document.getElementById('selectKota');
        sel.innerHTML = buildOptions(response.data, 'Pilih Kota / Kabupaten');
        sel.disabled  = false;
    } catch (err) {
        console.error('Error memuat kota:', err);
        alert('Gagal memuat data kota.');
    } finally {
        showSpinner('spinnerKota', false);
    }
});

// ── Event: Kota onChange ──────────────────────────────────────────
document.getElementById('selectKota').addEventListener('change', async function () {
    const kotaId   = this.value;
    const kotaText = this.options[this.selectedIndex].text;

    resetSelect('selectKecamatan', 'Pilih Kecamatan');
    resetSelect('selectKelurahan', 'Pilih Kelurahan / Desa');
    updateResult('resultKota',      kotaId ? kotaText : null);
    updateResult('resultKecamatan', null);
    updateResult('resultKelurahan', null);

    if (!kotaId) return;

    showSpinner('spinnerKecamatan', true);
    try {
        // Axios GET request ke /wilayah/kecamatan
        const response = await axios.get('{{ route("wilayah.kecamatan") }}', {
            params: { regency_id: kotaId }
        });
        const sel = document.getElementById('selectKecamatan');
        sel.innerHTML = buildOptions(response.data, 'Pilih Kecamatan');
        sel.disabled  = false;
    } catch (err) {
        console.error('Error memuat kecamatan:', err);
        alert('Gagal memuat data kecamatan.');
    } finally {
        showSpinner('spinnerKecamatan', false);
    }
});

// ── Event: Kecamatan onChange ─────────────────────────────────────
document.getElementById('selectKecamatan').addEventListener('change', async function () {
    const kecamatanId   = this.value;
    const kecamatanText = this.options[this.selectedIndex].text;

    resetSelect('selectKelurahan', 'Pilih Kelurahan / Desa');
    updateResult('resultKecamatan', kecamatanId ? kecamatanText : null);
    updateResult('resultKelurahan', null);

    if (!kecamatanId) return;

    showSpinner('spinnerKelurahan', true);
    try {
        // Axios GET request ke /wilayah/kelurahan
        const response = await axios.get('{{ route("wilayah.kelurahan") }}', {
            params: { district_id: kecamatanId }
        });
        const sel = document.getElementById('selectKelurahan');
        sel.innerHTML = buildOptions(response.data, 'Pilih Kelurahan / Desa');
        sel.disabled  = false;
    } catch (err) {
        console.error('Error memuat kelurahan:', err);
        alert('Gagal memuat data kelurahan.');
    } finally {
        showSpinner('spinnerKelurahan', false);
    }
});

// ── Event: Kelurahan onChange ─────────────────────────────────────
document.getElementById('selectKelurahan').addEventListener('change', function () {
    const val  = this.value;
    const text = this.options[this.selectedIndex].text;
    updateResult('resultKelurahan', val ? text : null);
});

// ── Reset ─────────────────────────────────────────────────────────
document.getElementById('btnReset').addEventListener('click', function () {
    const prov = document.getElementById('selectProvinsi');
    prov.value = '';
    prov.dispatchEvent(new Event('change'));
});
</script>
@endpush
