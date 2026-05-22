@extends('layouts.main')

@section('title', 'Scanner Absensi NFC — PURPLEBOOK')

@push('styles')
<style>
    .scanner-card {
        background: linear-gradient(135deg, #1a0a2e 0%, #2d1454 60%, #3d1a6e 100%);
        border-radius: 24px;
        padding: 40px 32px;
        color: white;
        min-height: 480px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .scanner-card::before {
        content: '';
        position: absolute;
        width: 300px; height: 300px;
        border-radius: 50%;
        background: rgba(168, 85, 199, 0.12);
        top: -80px; right: -80px;
    }
    .scanner-card::after {
        content: '';
        position: absolute;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: rgba(168, 85, 199, 0.08);
        bottom: -50px; left: -50px;
    }

    .mode-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 14px;
        border-radius: 20px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .badge-nfc { background: #a855c7; color: white; }
    .badge-qr  { background: #3b82f6; color: white; }

    .nfc-icon-wrap {
        width: 120px; height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
        display: flex; align-items: center; justify-content: center;
        margin: 24px auto;
        position: relative;
        z-index: 1;
    }
    .nfc-icon-wrap i { font-size: 3rem; color: #d8b4fe; }

    /* Pulse ring animation */
    .nfc-pulse {
        position: absolute;
        border-radius: 50%;
        border: 2px solid rgba(168, 85, 199, 0.4);
        animation: nfcPulse 1.8s ease-out infinite;
    }
    .nfc-pulse:nth-child(2) { animation-delay: 0.6s; }
    .nfc-pulse:nth-child(3) { animation-delay: 1.2s; }
    @keyframes nfcPulse {
        0%   { width: 120px; height: 120px; opacity: 0.8; }
        100% { width: 220px; height: 220px; opacity: 0; }
    }

    .scanner-status {
        font-size: 1rem;
        color: rgba(255,255,255,0.75);
        text-align: center;
        margin-top: 8px;
        min-height: 24px;
    }
    .scanner-status.success { color: #86efac; }
    .scanner-status.error   { color: #fca5a5; }

    .result-card {
        background: rgba(255,255,255,0.07);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 16px;
        padding: 20px 24px;
        width: 100%;
        max-width: 360px;
        text-align: center;
        margin-top: 20px;
        position: relative;
        z-index: 1;
    }
    .result-nama { font-size: 1.4rem; font-weight: 700; color: white; }
    .result-waktu { font-size: 0.85rem; color: rgba(255,255,255,0.55); }

    .btn-scan {
        background: linear-gradient(135deg, #7B2D8B, #a855c7);
        color: white;
        border: none;
        padding: 14px 36px;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        position: relative; z-index: 1;
    }
    .btn-scan:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(168,85,199,0.4); color: white; }

    #qrReader { border-radius: 12px; overflow: hidden; max-width: 100%; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <h4 style="font-weight:700; color:#7B2D8B;">
            <i class="mdi mdi-credit-card-wireless"></i> Scanner Absensi
        </h4>
        <p class="text-muted mb-0">Scan kartu NFC atau QR Code untuk mencatat kehadiran.</p>
    </div>

    {{-- ── Scanner Utama ── --}}
    <div class="col-lg-6 mb-4">
        <div class="scanner-card">
            {{-- Mode badge --}}
            <span id="modeBadge" class="mode-badge badge-nfc">Mode: NFC</span>

            {{-- NFC Mode --}}
            <div id="nfcSection">
                <div class="nfc-icon-wrap">
                    <div class="nfc-pulse"></div>
                    <div class="nfc-pulse"></div>
                    <div class="nfc-pulse"></div>
                    <i class="mdi mdi-credit-card-wireless"></i>
                </div>
                <p style="font-size:1.1rem; font-weight:600; z-index:1; position:relative; margin-bottom:4px;">
                    Tempelkan Kartu NFC
                </p>
                <p class="scanner-status" id="nfcStatus">Tekan tombol untuk mulai scan</p>
                <button class="btn-scan mt-3" onclick="startNFC()" id="btnNfc">
                    <i class="mdi mdi-credit-card-wireless"></i> Aktifkan NFC
                </button>
            </div>

            {{-- QR Mode --}}
            <div id="qrSection" style="display:none; width:100%; position:relative; z-index:1;">
                <div id="qrReader" style="width:100%;"></div>
                <p class="scanner-status mt-2" id="qrStatus">
                    <i class="mdi mdi-camera"></i> Arahkan kamera ke QR Code...
                </p>
            </div>

            {{-- Hasil --}}
            <div id="hasilScan" class="result-card" style="display:none;">
                <div id="hasilIcon" style="font-size:2.5rem;">✅</div>
                <div class="result-nama" id="hasilNama">—</div>
                <div class="result-waktu" id="hasilWaktu">—</div>
                <div id="hasilPesan" class="mt-2" style="font-size:0.9rem; color:rgba(255,255,255,0.7);">—</div>
                <button class="btn-scan mt-3" onclick="resetScanner()">
                    <i class="mdi mdi-refresh"></i> Scan Lagi
                </button>
            </div>
        </div>
    </div>

    {{-- ── Info Panel ── --}}
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="font-weight-bold mb-3" style="color:#7B2D8B;">
                    <i class="mdi mdi-information-outline"></i> Panduan
                </h5>

                {{-- Mode Switch --}}
                <div class="mb-4">
                    <p class="text-muted mb-2 font-weight-bold">Pilih Mode Scanner:</p>
                    <div class="d-flex gap-2" style="gap:10px; display:flex;">
                        <button class="btn btn-sm btn-gradient-primary" onclick="setMode('nfc')" id="btnModeNfc">
                            <i class="mdi mdi-credit-card-wireless"></i> NFC
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="setMode('qr')" id="btnModeQr">
                            <i class="mdi mdi-qrcode-scan"></i> QR Code
                        </button>
                    </div>
                    <small class="text-muted mt-1 d-block">
                        NFC: Android Chrome saja. QR: semua device termasuk iOS.
                    </small>
                </div>

                <hr>

                <div class="mb-3">
                    <div class="d-flex align-items-start mb-2">
                        <span class="badge badge-success mr-2 mt-1">NFC</span>
                        <div>
                            <strong>Android Chrome ≥ 89</strong><br>
                            <small class="text-muted">Tekan "Aktifkan NFC" → tempelkan kartu ke bagian belakang HP.</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <span class="badge badge-primary mr-2 mt-1">QR</span>
                        <div>
                            <strong>Semua Device (iOS/Android/Desktop)</strong><br>
                            <small class="text-muted">Kamera terbuka otomatis. Arahkan ke QR Code milik peserta.</small>
                        </div>
                    </div>
                </div>

                <hr>

                <h6 class="font-weight-bold">Absensi Hari Ini</h6>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total hadir:</span>
                    <strong id="totalHadir">{{ \App\Models\Absensi::hariIni()->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Via NFC:</span>
                    <strong>{{ \App\Models\Absensi::hariIni()->metode('nfc')->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Via QR:</span>
                    <strong>{{ \App\Models\Absensi::hariIni()->metode('qr')->count() }}</strong>
                </div>

                <a href="{{ route('absensi.riwayat') }}" class="btn btn-outline-primary btn-sm btn-block mt-3">
                    <i class="mdi mdi-history"></i> Lihat Riwayat Lengkap
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const CSRF  = '{{ csrf_token() }}';
const URL_CATAT = '{{ route("absensi.catat") }}';
let currentMode = 'nfc'; // default
let html5QrCode = null;
let ndefReader  = null;
let scanning    = false;

// ── Beep Audio API ─────────────────────────────────────────────
function beep(ok = true) {
    try {
        const ctx  = new (window.AudioContext || window.webkitAudioContext)();
        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.value = ok ? 1046 : 400;
        osc.type = 'sine';
        gain.gain.setValueAtTime(0.4, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
        osc.start(); osc.stop(ctx.currentTime + 0.5);
    } catch(e) {}
}

// ── Set Mode: NFC atau QR ──────────────────────────────────────
function setMode(mode) {
    stopAll();
    currentMode = mode;

    if (mode === 'nfc') {
        document.getElementById('nfcSection').style.display = 'block';
        document.getElementById('qrSection').style.display  = 'none';
        document.getElementById('modeBadge').className = 'mode-badge badge-nfc';
        document.getElementById('modeBadge').textContent = 'Mode: NFC';
        document.getElementById('btnModeNfc').className = 'btn btn-sm btn-gradient-primary';
        document.getElementById('btnModeQr').className  = 'btn btn-sm btn-outline-primary';

        // Jika browser tidak support NFC, tampilkan peringatan
        if (!('NDEFReader' in window)) {
            document.getElementById('nfcStatus').textContent = '⚠️ Browser ini tidak support Web NFC. Gunakan Android Chrome.';
            document.getElementById('nfcStatus').className = 'scanner-status error';
            document.getElementById('btnNfc').disabled = true;
        }
    } else {
        document.getElementById('nfcSection').style.display = 'none';
        document.getElementById('qrSection').style.display  = 'block';
        document.getElementById('modeBadge').className = 'mode-badge badge-qr';
        document.getElementById('modeBadge').textContent = 'Mode: QR';
        document.getElementById('btnModeNfc').className = 'btn btn-sm btn-outline-primary';
        document.getElementById('btnModeQr').className  = 'btn btn-sm btn-gradient-primary';
        startQR();
    }
}

// ── Mode NFC (Web NFC API — sesuai modul) ─────────────────────
async function startNFC() {
    if (!('NDEFReader' in window)) {
        alert('Browser ini tidak mendukung Web NFC API.\nGunakan Android Chrome ≥ 89.');
        return;
    }
    if (scanning) return;
    scanning = true;

    document.getElementById('nfcStatus').textContent = '🔄 Menginisialisasi NFC...';
    document.getElementById('nfcStatus').className   = 'scanner-status';

    try {
        ndefReader = new NDEFReader();
        await ndefReader.scan(); // Meminta izin NFC dari user (user gesture)

        document.getElementById('nfcStatus').textContent = '✅ NFC aktif. Dekatkan kartu ke HP...';
        document.getElementById('btnNfc').disabled = true;

        // console.log untuk debugging (Chrome Remote Debugging)
        console.log('[NFC] Reader aktif, menunggu kartu...');

        ndefReader.addEventListener('reading', ({ serialNumber, message }) => {
            // Log untuk debugging (lihat di chrome://inspect)
            console.log('[NFC] Serial:', serialNumber);
            console.log('[NFC] Records:', message.records.length);

            kirimAbsensi(serialNumber, 'nfc');
        });

        ndefReader.addEventListener('readingerror', () => {
            setStatus('nfcStatus', '❌ Gagal membaca tag NFC.', false);
            scanning = false;
        });

    } catch (err) {
        console.error('[NFC] Error:', err);
        setStatus('nfcStatus', '❌ Error: ' + err.message, false);
        document.getElementById('btnNfc').disabled = false;
        scanning = false;
    }
}

// ── Mode QR (html5-qrcode — fallback iOS/desktop) ─────────────
function startQR() {
    if (html5QrCode) return;

    html5QrCode = new Html5Qrcode('qrReader');
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
    };

    Html5Qrcode.getCameras().then(cameras => {
        if (!cameras || cameras.length === 0) {
            setStatus('qrStatus', '❌ Kamera tidak ditemukan.', false);
            return;
        }
        const camId = cameras.length > 1 ? cameras[cameras.length - 1].id : cameras[0].id;
        html5QrCode.start(camId, config, (text) => {
            if (scanning) return;
            scanning = true;
            html5QrCode.stop().catch(() => {});
            kirimAbsensi(text, 'qr');
        }).catch(err => {
            setStatus('qrStatus', '❌ Gagal membuka kamera: ' + err, false);
        });
    });
}

// ── Kirim ke Backend ───────────────────────────────────────────
function kirimAbsensi(serial, metode) {
    fetch(URL_CATAT, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ serial_number: serial, metode: metode }),
    })
    .then(res => res.json().then(data => ({ status: res.status, data })))
    .then(({ status, data }) => {
        if (status === 200) {
            beep(true);
            tampilHasil(true, data);
        } else {
            beep(false);
            tampilHasil(false, data);
        }
    })
    .catch(() => {
        beep(false);
        tampilHasil(false, { pesan: 'Gagal menghubungi server.' });
    });
}

// ── Tampilkan Hasil ────────────────────────────────────────────
function tampilHasil(sukses, data) {
    document.getElementById('hasilScan').style.display = 'block';
    document.getElementById('nfcSection').style.display = 'none';
    document.getElementById('qrSection').style.display  = 'none';
    document.getElementById('hasilIcon').textContent = sukses ? '✅' : '❌';
    document.getElementById('hasilNama').textContent = data.nama || '—';
    document.getElementById('hasilPesan').textContent = data.pesan || '';
    document.getElementById('hasilWaktu').textContent = data.waktu
        ? 'Absen pukul ' + data.waktu
        : new Date().toLocaleTimeString('id-ID');

    if (sukses) {
        document.getElementById('totalHadir').textContent =
            parseInt(document.getElementById('totalHadir').textContent) + 1;
    }
}

// ── Reset untuk scan berikutnya ────────────────────────────────
function resetScanner() {
    document.getElementById('hasilScan').style.display = 'none';
    scanning    = false;
    html5QrCode = null;
    ndefReader  = null;
    document.getElementById('btnNfc').disabled = false;
    setMode(currentMode);
}

function stopAll() {
    if (html5QrCode) { html5QrCode.stop().catch(() => {}); html5QrCode = null; }
    scanning   = false;
    ndefReader = null;
}

function setStatus(id, msg, ok = true) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg;
    el.className   = 'scanner-status ' + (ok ? 'success' : 'error');
}

// ── Init: otomatis pakai mode yang cocok ───────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if ('NDEFReader' in window) {
        setMode('nfc');  // Android Chrome → default NFC
    } else {
        setMode('qr');   // iOS/Desktop → langsung QR
    }
});
</script>
@endpush
