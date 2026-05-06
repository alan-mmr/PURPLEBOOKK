@extends('layouts.main')

@section('title', 'Titik Kunjungan Toko - PURPLEBOOK')

@push('styles')
<style>
    /* ── Scanner ─────────────────────────────── */
    #readerKunjungan {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        border-radius: 12px;
        overflow: hidden;
        border: 3px solid #7B2D8B;
    }
    #readerKunjungan__scan_region img,
    #readerKunjungan__dashboard_section_csr span { display: none !important; }

    /* ── Info Cards ──────────────────────────── */
    .info-box {
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
    }
    .info-box.toko-box   { background: linear-gradient(135deg,#f3e5f5,#e8eaf6); border-color:#ce93d8; }
    .info-box.sales-box  { background: linear-gradient(135deg,#e3f2fd,#e8f5e9); border-color:#90caf9; }
    .info-box.result-box { border: none; }

    .coord-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        font-size: 0.9rem;
    }
    .coord-row:last-child { border-bottom: none; }
    .coord-label { color: #888; font-weight: 500; }
    .coord-value { font-family: monospace; font-weight: 700; }

    /* ── Hasil Validasi ───────────────────────── */
    .result-diterima {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        border: 2px solid #28a745;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        animation: fadeInUp 0.4s ease;
    }
    .result-ditolak {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        border: 2px solid #dc3545;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        animation: fadeInUp 0.4s ease;
    }
    .result-icon { font-size: 3rem; margin-bottom: 8px; }
    .result-label { font-size: 1.5rem; font-weight: 800; margin-bottom: 8px; }
    .result-detail { font-size: 0.85rem; color: #555; }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Step indicator ──────────────────────── */
    .step-badge {
        display: inline-block;
        width: 28px; height: 28px;
        border-radius: 50%;
        background: #7B2D8B;
        color: white;
        font-weight: bold;
        font-size: 0.85rem;
        line-height: 28px;
        text-align: center;
        margin-right: 8px;
    }
    .step-badge.done { background: #28a745; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-success text-white mr-2">
            <i class="mdi mdi-map-marker-check"></i>
        </span> Titik Kunjungan Toko
    </h3>
    <nav>
        <a href="{{ route('kunjungan.titikAwal') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-map-marker-plus"></i> Input Titik Awal
        </a>
    </nav>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">

        {{-- ── STEP 1: Scan Barcode Toko ──────────────────────────── --}}
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <span class="step-badge" id="badge1">1</span>
                    Scan Barcode Toko
                </h5>
                <p class="text-muted small mb-3">Arahkan kamera ke barcode yang tertempel di toko.</p>

                <div id="scanStatus" class="text-center text-muted mb-2" style="font-size:0.9rem; min-height:24px;">
                    <i class="mdi mdi-camera-outline"></i> Memulai kamera...
                </div>

                <div id="readerKunjungan"></div>

                <button id="btnScanLagi" class="btn btn-outline-primary btn-block mt-3" style="display:none;"
                        onclick="resetSemua()">
                    <i class="mdi mdi-refresh"></i> Scan Ulang
                </button>
            </div>
        </div>

        {{-- ── Data Toko (hasil scan) ─────────────────────────────── --}}
        <div class="card mb-4" id="cardToko" style="display:none;">
            <div class="card-body">
                <h5 class="card-title">
                    <span class="step-badge done" id="badge2">✓</span>
                    Data Toko (Hasil Scan)
                </h5>

                <div class="info-box toko-box">
                    <div class="coord-row">
                        <span class="coord-label"><i class="mdi mdi-barcode"></i> Barcode</span>
                        <span class="coord-value" id="tokoBarcode">—</span>
                    </div>
                    <div class="coord-row">
                        <span class="coord-label"><i class="mdi mdi-store"></i> Nama Toko</span>
                        <span class="coord-value" id="tokoNama">—</span>
                    </div>
                    <div class="coord-row">
                        <span class="coord-label"><i class="mdi mdi-latitude"></i> Latitude</span>
                        <span class="coord-value" id="tokoLat">—</span>
                    </div>
                    <div class="coord-row">
                        <span class="coord-label"><i class="mdi mdi-longitude"></i> Longitude</span>
                        <span class="coord-value" id="tokoLng">—</span>
                    </div>
                    <div class="coord-row">
                        <span class="coord-label"><i class="mdi mdi-radar"></i> Accuracy Toko</span>
                        <span class="coord-value" id="tokoAcc">—</span>
                    </div>
                </div>

                {{-- Tombol Ambil Lokasi Sales ──────────────────────── --}}
                <button id="btnAmbilLokasi" class="btn btn-gradient-info btn-block mt-2"
                        onclick="ambilLokasiSales()">
                    <i class="mdi mdi-crosshairs-gps"></i> Ambil Lokasi
                </button>

                <div id="geolocProgress" style="display:none;" class="mt-2">
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             style="width:100%"></div>
                    </div>
                    <small class="text-muted" id="geolocStatus">Mencari sinyal GPS terbaik...</small>
                </div>
            </div>
        </div>

        {{-- ── Data Titik Kunjungan Sales ──────────────────────────── --}}
        <div class="card mb-4" id="cardSales" style="display:none;">
            <div class="card-body">
                <h5 class="card-title">
                    <span class="step-badge done">✓</span>
                    Titik Kunjungan Sales
                </h5>

                <div class="info-box sales-box">
                    <div class="coord-row">
                        <span class="coord-label"><i class="mdi mdi-latitude"></i> Latitude Sales</span>
                        <span class="coord-value" id="salesLat">—</span>
                    </div>
                    <div class="coord-row">
                        <span class="coord-label"><i class="mdi mdi-longitude"></i> Longitude Sales</span>
                        <span class="coord-value" id="salesLng">—</span>
                    </div>
                    <div class="coord-row">
                        <span class="coord-label"><i class="mdi mdi-radar"></i> Accuracy Sales</span>
                        <span class="coord-value" id="salesAcc">—</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Hasil Validasi ──────────────────────────────────────── --}}
        <div id="hasilValidasi" style="display:none;" class="mb-4"></div>

        {{-- Error Box ──────────────────────────────────────────────── --}}
        <div id="errorBox" class="alert alert-danger mt-3" style="display:none;">
            <i class="mdi mdi-alert-circle"></i> <span id="errorText"></span>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// ── State ─────────────────────────────────────────────────────────
let html5QrCode = null;
let sudahScan   = false;
let dataToko    = null; // hasil lookup toko dari DB

// ── Beep ──────────────────────────────────────────────────────────
function playBeep() {
    try {
        const ctx  = new (window.AudioContext || window.webkitAudioContext)();
        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        osc.frequency.value = 880; osc.type = 'square';
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.18);
        osc.start(); osc.stop(ctx.currentTime + 0.18);
    } catch(e) {}
}

// ── Scanner Config ────────────────────────────────────────────────
const config = {
    fps: 10,
    qrbox: { width: 260, height: 110 },
    aspectRatio: 1.5,
    formatsToSupport: [
        Html5QrcodeSupportedFormats.CODE_128,
        Html5QrcodeSupportedFormats.CODE_39,
        Html5QrcodeSupportedFormats.QR_CODE,
    ]
};

// ── Callback: barcode terbaca ─────────────────────────────────────
function onScanSuccess(decodedText) {
    if (sudahScan) return;
    sudahScan = true;
    playBeep();

    if (html5QrCode) html5QrCode.stop().catch(() => {});

    document.getElementById('scanStatus').innerHTML =
        '<i class="mdi mdi-check text-success"></i> Barcode terbaca: <code>' + decodedText + '</code>';
    document.getElementById('btnScanLagi').style.display = 'block';

    // Lookup toko ke server
    fetch('{{ route("toko.lookup") }}?barcode=' + encodeURIComponent(decodedText))
        .then(r => r.json().then(d => ({ status: r.status, data: d })))
        .then(({ status, data }) => {
            if (status === 200) {
                tampilDataToko(data);
            } else {
                tampilError(data.error || 'Barcode toko tidak ditemukan.');
            }
        })
        .catch(() => tampilError('Gagal menghubungi server.'));
}

// ── Tampilkan data toko ───────────────────────────────────────────
function tampilDataToko(toko) {
    dataToko = toko;
    document.getElementById('tokoBarcode').textContent = toko.barcode;
    document.getElementById('tokoNama').textContent    = toko.nama_toko;
    document.getElementById('tokoLat').textContent     = toko.latitude;
    document.getElementById('tokoLng').textContent     = toko.longitude;
    document.getElementById('tokoAcc').textContent     = toko.accuracy + ' m';
    document.getElementById('cardToko').style.display  = 'block';

    if (toko.latitude == 0 && toko.longitude == 0) {
        tampilError('Koordinat toko belum diset! Silakan set via menu Input Titik Awal.');
        document.getElementById('btnAmbilLokasi').disabled = true;
    }
}

// ── Ambil lokasi GPS sales (Lampiran 1) ──────────────────────────
function getAccuratePosition(targetAccuracy = 50, maxWait = 20000) {
    return new Promise((resolve, reject) => {
        let bestResult = null;
        const startTime = Date.now();

        const watchId = navigator.geolocation.watchPosition(
            (position) => {
                const acc = position.coords.accuracy;
                if (!bestResult || acc < bestResult.coords.accuracy) {
                    bestResult = position;
                    document.getElementById('geolocStatus').textContent =
                        'Mencari akurasi terbaik... saat ini: ' + Math.round(acc) + 'm';
                }
                if (acc <= targetAccuracy) {
                    navigator.geolocation.clearWatch(watchId);
                    resolve(bestResult);
                }
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

async function ambilLokasiSales() {
    if (!navigator.geolocation) { alert('Browser tidak mendukung Geolocation.'); return; }

    const btn = document.getElementById('btnAmbilLokasi');
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Mengambil GPS...';
    document.getElementById('geolocProgress').style.display = 'block';
    document.getElementById('hasilValidasi').style.display  = 'none';
    document.getElementById('errorBox').style.display       = 'none';

    try {
        const pos = await getAccuratePosition(50, 20000);
        const latSales = pos.coords.latitude;
        const lngSales = pos.coords.longitude;
        const accSales = pos.coords.accuracy;

        // Tampilkan data sales
        document.getElementById('salesLat').textContent = latSales;
        document.getElementById('salesLng').textContent = lngSales;
        document.getElementById('salesAcc').textContent = Math.round(accSales * 100) / 100 + ' m';
        document.getElementById('cardSales').style.display = 'block';

        // Kirim ke server untuk validasi
        await validasiKunjungan(latSales, lngSales, accSales);

    } catch (err) {
        tampilError('Gagal mengambil lokasi: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-crosshairs-gps"></i> Ambil Lokasi';
        document.getElementById('geolocProgress').style.display = 'none';
    }
}

// ── Validasi kunjungan (kirim ke server) ─────────────────────────
async function validasiKunjungan(lat, lng, acc) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const res = await fetch('{{ route("kunjungan.validasi") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                barcode:   dataToko.barcode,
                lat_sales: lat,
                lng_sales: lng,
                acc_sales: acc,
            }),
        });

        const data = await res.json();

        if (!res.ok) {
            tampilError(data.error || 'Terjadi kesalahan saat validasi.');
            return;
        }

        tampilHasilValidasi(data);

    } catch (err) {
        tampilError('Gagal menghubungi server untuk validasi.');
    }
}

// ── Tampilkan hasil validasi ──────────────────────────────────────
function tampilHasilValidasi(data) {
    const el = document.getElementById('hasilValidasi');
    el.style.display = 'block';

    if (data.diterima) {
        el.innerHTML = `
            <div class="result-diterima">
                <div class="result-icon">✅</div>
                <div class="result-label text-success">KUNJUNGAN DITERIMA</div>
                <div class="result-detail">
                    <table class="table table-sm table-borderless mb-0 mt-2" style="max-width:320px;margin:auto;">
                        <tr><td class="text-left text-muted">Jarak Aktual</td><td><strong>${data.jarak_meter} m</strong></td></tr>
                        <tr><td class="text-left text-muted">Radius Dasar</td><td>${data.radius_dasar} m</td></tr>
                        <tr><td class="text-left text-muted">Accuracy Toko</td><td>+${data.accuracy_toko} m</td></tr>
                        <tr><td class="text-left text-muted">Accuracy Sales</td><td>+${Math.round(data.accuracy_sales*100)/100} m</td></tr>
                        <tr style="border-top:1px solid #ccc;">
                            <td class="text-left font-weight-bold">Threshold Efektif</td>
                            <td><strong>${data.threshold_efektif} m</strong></td>
                        </tr>
                    </table>
                    <p class="mt-2 text-success font-weight-bold">
                        ${data.jarak_meter} m ≤ ${data.threshold_efektif} m → DITERIMA ✓
                    </p>
                </div>
            </div>`;
    } else {
        el.innerHTML = `
            <div class="result-ditolak">
                <div class="result-icon">❌</div>
                <div class="result-label text-danger">KUNJUNGAN DITOLAK</div>
                <div class="result-detail">
                    <table class="table table-sm table-borderless mb-0 mt-2" style="max-width:320px;margin:auto;">
                        <tr><td class="text-left text-muted">Jarak Aktual</td><td><strong>${data.jarak_meter} m</strong></td></tr>
                        <tr><td class="text-left text-muted">Radius Dasar</td><td>${data.radius_dasar} m</td></tr>
                        <tr><td class="text-left text-muted">Accuracy Toko</td><td>+${data.accuracy_toko} m</td></tr>
                        <tr><td class="text-left text-muted">Accuracy Sales</td><td>+${Math.round(data.accuracy_sales*100)/100} m</td></tr>
                        <tr style="border-top:1px solid #ccc;">
                            <td class="text-left font-weight-bold">Threshold Efektif</td>
                            <td><strong>${data.threshold_efektif} m</strong></td>
                        </tr>
                    </table>
                    <p class="mt-2 text-danger font-weight-bold">
                        ${data.jarak_meter} m > ${data.threshold_efektif} m → DITOLAK ✗
                    </p>
                </div>
            </div>`;
    }
}

// ── Tampilkan error ───────────────────────────────────────────────
function tampilError(pesan) {
    const el = document.getElementById('errorBox');
    document.getElementById('errorText').textContent = pesan;
    el.style.display = 'block';
}

// ── Reset semua ───────────────────────────────────────────────────
function resetSemua() {
    sudahScan = false;
    dataToko  = null;
    document.getElementById('cardToko').style.display        = 'none';
    document.getElementById('cardSales').style.display       = 'none';
    document.getElementById('hasilValidasi').style.display   = 'none';
    document.getElementById('errorBox').style.display        = 'none';
    document.getElementById('btnScanLagi').style.display     = 'none';
    document.getElementById('btnAmbilLokasi').disabled       = false;
    document.getElementById('btnAmbilLokasi').innerHTML      = '<i class="mdi mdi-crosshairs-gps"></i> Ambil Lokasi';
    mulaiScanner();
}

// ── Mulai scanner ─────────────────────────────────────────────────
function mulaiScanner() {
    document.getElementById('scanStatus').innerHTML =
        '<i class="mdi mdi-camera-outline"></i> Kamera aktif — arahkan ke barcode toko...';

    html5QrCode = new Html5Qrcode('readerKunjungan');

    Html5Qrcode.getCameras().then(cameras => {
        if (!cameras || cameras.length === 0) {
            document.getElementById('scanStatus').innerHTML =
                '<i class="mdi mdi-camera-off text-danger"></i> Kamera tidak ditemukan.';
            return;
        }
        const kameraId = cameras.length > 1 ? cameras[cameras.length - 1].id : cameras[0].id;

        html5QrCode.start(kameraId, config, onScanSuccess)
            .then(() => {
                document.getElementById('scanStatus').innerHTML =
                    '<span class="text-success"><i class="mdi mdi-record-circle"></i> Scanning aktif...</span>';
            })
            .catch(err => {
                document.getElementById('scanStatus').innerHTML =
                    '<i class="mdi mdi-alert text-danger"></i> Gagal membuka kamera: ' + err;
            });
    }).catch(() => {
        document.getElementById('scanStatus').innerHTML =
            '<i class="mdi mdi-alert text-danger"></i> Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.';
    });
}

// ── Auto-start ────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', mulaiScanner);
</script>
@endpush
