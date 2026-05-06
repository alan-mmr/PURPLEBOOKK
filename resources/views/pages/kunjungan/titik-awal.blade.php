@extends('layouts.main')

@section('title', 'Input Titik Awal Toko - PURPLEBOOK')

@push('styles')
<style>
    .coord-input-group .form-control {
        background: #ffffff;
        color: #333333;
        font-weight: 600;
        font-size: 1rem;
        text-align: center;
        border: 1px solid #ced4da;
        height: 48px;
    }
    .coord-input-group .form-control:focus {
        border-color: #7B2D8B;
        box-shadow: 0 0 0 0.2rem rgba(123, 45, 139, 0.25);
    }
    .coord-input-group .form-control::placeholder { color: #aaaaaa; font-weight: normal; }
    .coord-input-group label { font-weight: 600; color: #555; }

    .accuracy-indicator {
        font-size: 0.85rem;
        min-height: 24px;
        transition: all 0.3s;
    }
    .acc-good  { color: #28a745; font-weight: bold; }
    .acc-ok    { color: #ffc107; font-weight: bold; }
    .acc-bad   { color: #dc3545; font-weight: bold; }

    .geoloc-progress {
        display: none;
        margin-top: 10px;
    }
    .toko-select-card {
        border-left: 4px solid #7B2D8B;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-map-marker-plus"></i>
        </span> Input Titik Awal Toko
    </h3>
    <nav>
        <a href="{{ route('toko.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-arrow-left"></i> Kelola Toko
        </a>
    </nav>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">

        <div class="card toko-select-card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="mdi mdi-store text-primary"></i> Pilih Toko
                </h5>

                @if($tokos->isEmpty())
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert"></i>
                        Belum ada toko. <a href="{{ route('toko.index') }}">Tambah toko terlebih dahulu.</a>
                    </div>
                @else
                    <div class="form-group mb-0">
                        <select id="selectToko" class="form-control form-control-lg">
                            <option value="">-- Pilih Toko --</option>
                            @foreach($tokos as $toko)
                            <option value="{{ $toko->barcode }}"
                                    data-nama="{{ $toko->nama_toko }}"
                                    data-lat="{{ $toko->latitude }}"
                                    data-lng="{{ $toko->longitude }}"
                                    data-acc="{{ $toko->accuracy }}">
                                [{{ $toko->barcode }}] {{ $toko->nama_toko }}
                                @if($toko->latitude != 0)
                                    ✓ Sudah ada koordinat
                                @else
                                    (belum ada koordinat)
                                @endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>

        {{-- Form Input Koordinat --}}
        <div class="card" id="formCard" style="{{ $tokos->isEmpty() ? 'opacity:0.4; pointer-events:none;' : '' }}">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="mdi mdi-crosshairs-gps text-success"></i> Input Koordinat GPS
                </h5>
                <p class="text-muted small mb-3">
                    Klik <strong>"Geoloc"</strong> untuk mengambil posisi GPS otomatis (akurasi terbaik, seperti Share Location WhatsApp),
                    atau isi manual dari Google Maps.
                </p>

                <form method="POST" action="{{ route('kunjungan.updateTitik') }}" id="formTitikAwal">
                    @csrf
                    <input type="hidden" name="barcode" id="inputBarcode">

                    <div class="coord-input-group">
                        <div class="form-group">
                            <label for="inputLatitude"><i class="mdi mdi-latitude"></i> Latitude</label>
                            <input type="number" step="any" id="inputLatitude" name="latitude"
                                   class="form-control" placeholder="Contoh: -7.250445" required>
                        </div>
                        <div class="form-group">
                            <label for="inputLongitude"><i class="mdi mdi-longitude"></i> Longitude</label>
                            <input type="number" step="any" id="inputLongitude" name="longitude"
                                   class="form-control" placeholder="Contoh: 112.768845" required>
                        </div>
                        <div class="form-group">
                            <label for="inputAccuracy"><i class="mdi mdi-radar"></i> Accuracy (meter)</label>
                            <input type="number" step="any" id="inputAccuracy" name="accuracy"
                                   class="form-control" placeholder="Contoh: 25" required>
                        </div>
                    </div>

                    {{-- Indikator akurasi --}}
                    <div id="accuracyIndicator" class="accuracy-indicator mb-3"></div>

                    {{-- Progress Geoloc --}}
                    <div class="geoloc-progress" id="geolocProgress">
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                 style="width: 100%"></div>
                        </div>
                        <small class="text-muted" id="geolocStatus">Mencari sinyal GPS terbaik...</small>
                    </div>

                    <div class="row mt-2">
                        <div class="col-6">
                            <button type="button" id="btnGeoloc" class="btn btn-gradient-info btn-block"
                                    onclick="ambilLokasiToko()">
                                <i class="mdi mdi-crosshairs-gps"></i> Geoloc
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="submit" id="btnSubmit" class="btn btn-gradient-primary btn-block" disabled>
                                <i class="mdi mdi-content-save"></i> Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Pilih toko → isi barcode ke form ────────────────────────────
document.getElementById('selectToko').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    const barcode = opt.value;

    document.getElementById('inputBarcode').value  = barcode;
    document.getElementById('btnSubmit').disabled  = !barcode;
    document.getElementById('formCard').style.opacity = barcode ? '1' : '0.4';
    document.getElementById('formCard').style.pointerEvents = barcode ? 'auto' : 'none';

    // Isi koordinat yang sudah ada (jika ada)
    if (barcode) {
        const lat = parseFloat(opt.dataset.lat) || 0;
        const lng = parseFloat(opt.dataset.lng) || 0;
        const acc = parseFloat(opt.dataset.acc) || 0;

        if (lat !== 0) {
            document.getElementById('inputLatitude').value  = lat;
            document.getElementById('inputLongitude').value = lng;
            document.getElementById('inputAccuracy').value  = acc;
            updateAccuracyIndicator(acc);
        } else {
            document.getElementById('inputLatitude').value  = '';
            document.getElementById('inputLongitude').value = '';
            document.getElementById('inputAccuracy').value  = '';
            document.getElementById('accuracyIndicator').innerHTML = '';
        }
    }
});

// ── Ambil lokasi GPS terbaik (Lampiran 1) ────────────────────────
// getAccuratePosition: mencari posisi dengan akurasi terbaik
// targetAccuracy = 50m, maxWait = 20 detik
function getAccuratePosition(targetAccuracy = 50, maxWait = 20000) {
    return new Promise((resolve, reject) => {
        let bestResult = null;
        const startTime = Date.now();

        const watchId = navigator.geolocation.watchPosition(
            (position) => {
                const acc = position.coords.accuracy;

                // Simpan hasil terbaik sejauh ini
                if (!bestResult || acc < bestResult.coords.accuracy) {
                    bestResult = position;
                    document.getElementById('geolocStatus').textContent =
                        `Mencari akurasi terbaik... saat ini: ${Math.round(acc)}m`;
                }

                // Kalau sudah cukup akurat, berhenti
                if (acc <= targetAccuracy) {
                    navigator.geolocation.clearWatch(watchId);
                    resolve(bestResult);
                }

                // Kalau timeout, pakai hasil terbaik yang ada
                if (Date.now() - startTime >= maxWait) {
                    navigator.geolocation.clearWatch(watchId);
                    if (bestResult) resolve(bestResult);
                    else reject(new Error('Timeout, tidak dapat posisi GPS'));
                }
            },
            (error) => reject(error),
            { enableHighAccuracy: true, maximumAge: 0, timeout: maxWait }
        );
    });
}

// ── Tombol Geoloc diklik ─────────────────────────────────────────
async function ambilLokasiToko() {
    if (!navigator.geolocation) {
        alert('Browser tidak mendukung Geolocation.');
        return;
    }

    const btnGeoloc = document.getElementById('btnGeoloc');
    const progressEl = document.getElementById('geolocProgress');

    btnGeoloc.disabled = true;
    btnGeoloc.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Mengambil GPS...';
    progressEl.style.display = 'block';
    document.getElementById('accuracyIndicator').innerHTML = '';

    try {
        const pos = await getAccuratePosition(50, 20000);
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        const acc = pos.coords.accuracy;

        document.getElementById('inputLatitude').value  = lat;
        document.getElementById('inputLongitude').value = lng;
        document.getElementById('inputAccuracy').value  = Math.round(acc * 100) / 100;

        updateAccuracyIndicator(acc);
        document.getElementById('btnSubmit').disabled = !document.getElementById('inputBarcode').value;

    } catch (err) {
        alert('Gagal mengambil lokasi: ' + err.message);
    } finally {
        btnGeoloc.disabled = false;
        btnGeoloc.innerHTML = '<i class="mdi mdi-crosshairs-gps"></i> Geoloc';
        progressEl.style.display = 'none';
    }
}

// ── Indikator kualitas akurasi ───────────────────────────────────
function updateAccuracyIndicator(acc) {
    const el = document.getElementById('accuracyIndicator');
    if (acc <= 20) {
        el.innerHTML = `<span class="acc-good">✅ Akurasi sangat baik: ${Math.round(acc)}m</span>`;
    } else if (acc <= 50) {
        el.innerHTML = `<span class="acc-ok">⚡ Akurasi cukup baik: ${Math.round(acc)}m</span>`;
    } else {
        el.innerHTML = `<span class="acc-bad">⚠️ Akurasi kurang baik: ${Math.round(acc)}m — coba pindah ke area terbuka</span>`;
    }
}
</script>
@endpush
