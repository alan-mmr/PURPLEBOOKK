@extends('layouts.main')

@section('title', 'Scan Barcode Barang - PURPLEBOOK')

@push('styles')
<style>
    /* ── Scanner Container ─────────────────────────────────── */
    #reader {
        width: 100%;
        max-width: 480px;
        margin: 0 auto;
        border-radius: 12px;
        overflow: hidden;
        border: 3px solid #7B2D8B;
    }

    /* Hilangkan teks bawaan Html5-qrcode yang tidak perlu */
    #reader__scan_region img,
    #reader__dashboard_section_csr span {
        display: none !important;
    }

    /* ── Hasil Scan ────────────────────────────────────────── */
    #hasilScan {
        display: none;
        animation: fadeInUp 0.4s ease;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .badge-id {
        font-family: monospace;
        font-size: 1rem;
        letter-spacing: 2px;
        background: linear-gradient(135deg, #7B2D8B, #a84fc2);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
    }

    .harga-display {
        font-size: 2rem;
        font-weight: 700;
        color: #7B2D8B;
    }

    /* ── Overlay scanning indicator ────────────────────────── */
    #scanStatus {
        font-size: 0.9rem;
        min-height: 28px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-barcode-scan"></i>
        </span> Scan Barcode Barang
    </h3>
    <nav>
        <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-arrow-left"></i> Kembali ke Daftar Barang
        </a>
    </nav>
</div>

<div class="row justify-content-center">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-center mb-1">
                    <i class="mdi mdi-barcode text-primary"></i> Scanner Barcode
                </h4>
                <p class="text-muted text-center small mb-3">
                    Arahkan kamera ke barcode pada label kertas
                </p>

                {{-- ── Status Scanner ── --}}
                <div id="scanStatus" class="text-center text-muted mb-2">
                    <i class="mdi mdi-camera-outline"></i> Memulai kamera...
                </div>

                {{-- ── Area Kamera ── --}}
                <div id="reader"></div>

                {{-- ── Hasil Scan ── --}}
                <div id="hasilScan" class="mt-4 p-4 rounded" style="background: linear-gradient(135deg, #f3e5f5 0%, #e8eaf6 100%); border: 1px solid #ce93d8;">
                    <div class="text-center mb-3">
                        <i class="mdi mdi-check-circle mdi-36px text-success"></i>
                        <h5 class="font-weight-bold mt-1 mb-0" style="color: #7B2D8B;">Barang Ditemukan!</h5>
                    </div>

                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted font-weight-bold" width="120">ID Barang</td>
                            <td>
                                <span id="hasilId" class="badge-id">—</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted font-weight-bold">Nama</td>
                            <td>
                                <strong id="hasilNama" style="font-size: 1.1rem;">—</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted font-weight-bold">Harga</td>
                            <td>
                                <span id="hasilHarga" class="harga-display">—</span>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- ── Pesan Error ── --}}
                <div id="hasilError" class="alert alert-danger mt-3" style="display:none;">
                    <i class="mdi mdi-alert-circle"></i>
                    <span id="errorText">Barang tidak ditemukan.</span>
                </div>

                {{-- ── Tombol Scan Lagi ── --}}
                <button id="btnScanLagi" class="btn btn-gradient-primary btn-block mt-3" style="display:none;">
                    <i class="mdi mdi-refresh"></i> Scan Lagi
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Html5-qrcode CDN v2.3.8 — support barcode CODE_128, QR, EAN, UPC, dll --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// ── Beep Sound (Web Audio API) ─────────────────────────────────────────
// Tidak butuh file audio — di-generate secara programmatic
function playBeep(duration = 180, frequency = 880) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.value = frequency;
        osc.type = 'square';
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration / 1000);
        osc.start();
        osc.stop(ctx.currentTime + duration / 1000);
    } catch (e) {
        console.warn('Beep gagal:', e);
    }
}

// ── State Scanner ─────────────────────────────────────────────────────
let html5QrCode = null;
let sudahScan   = false; // flag: mencegah scan ganda

// ── Konfigurasi Scanner ───────────────────────────────────────────────
const config = {
    fps: 10,              // frame per second — cukup untuk barcode
    qrbox: { width: 280, height: 120 }, // area scan (lebih lebar untuk barcode 1D)
    aspectRatio: 1.5,
    formatsToSupport: [
        Html5QrcodeSupportedFormats.CODE_128,    // format utama label TnJ No.108
        Html5QrcodeSupportedFormats.CODE_39,
        Html5QrcodeSupportedFormats.EAN_13,
        Html5QrcodeSupportedFormats.EAN_8,
        Html5QrcodeSupportedFormats.QR_CODE,     // support QR juga sebagai bonus
    ]
};

// ── Callback: saat barcode berhasil dibaca ────────────────────────────
function onScanSuccess(decodedText) {
    if (sudahScan) return;  // abaikan scan berikutnya
    sudahScan = true;

    // 1. Bunyikan beep pendek
    playBeep();

    // 2. Hentikan scanner
    if (html5QrCode) {
        html5QrCode.stop().catch(() => {});
    }

    // 3. Update status
    document.getElementById('scanStatus').innerHTML =
        '<i class="mdi mdi-check text-success"></i> Barcode terbaca: <code>' + decodedText + '</code>';

    // 4. AJAX: lookup barang berdasarkan id_barang yang di-scan
    fetch('{{ route("barang.lookup") }}?id=' + encodeURIComponent(decodedText))
        .then(res => res.json().then(data => ({ status: res.status, data })))
        .then(({ status, data }) => {
            if (status === 200) {
                tampilHasil(data);
            } else {
                tampilError(data.error || 'Barang tidak ditemukan di database.');
            }
        })
        .catch(() => {
            tampilError('Gagal menghubungi server. Periksa koneksi internet.');
        });
}

// ── Tampilkan hasil barang ────────────────────────────────────────────
function tampilHasil(barang) {
    document.getElementById('hasilId').textContent    = barang.id_barang;
    document.getElementById('hasilNama').textContent  = barang.nama;
    document.getElementById('hasilHarga').textContent = 'Rp ' + barang.harga.toLocaleString('id-ID');
    document.getElementById('hasilScan').style.display  = 'block';
    document.getElementById('hasilError').style.display = 'none';
    document.getElementById('btnScanLagi').style.display = 'block';
}

// ── Tampilkan pesan error ─────────────────────────────────────────────
function tampilError(pesan) {
    document.getElementById('errorText').textContent    = pesan;
    document.getElementById('hasilError').style.display = 'block';
    document.getElementById('hasilScan').style.display  = 'none';
    document.getElementById('btnScanLagi').style.display = 'block';
}

// ── Mulai scanner ─────────────────────────────────────────────────────
function mulaiScanner() {
    sudahScan = false;
    document.getElementById('hasilScan').style.display  = 'none';
    document.getElementById('hasilError').style.display = 'none';
    document.getElementById('btnScanLagi').style.display = 'none';
    document.getElementById('scanStatus').innerHTML =
        '<i class="mdi mdi-camera-outline"></i> Kamera aktif — arahkan ke barcode...';

    html5QrCode = new Html5Qrcode('reader');

    Html5Qrcode.getCameras().then(cameras => {
        if (!cameras || cameras.length === 0) {
            document.getElementById('scanStatus').innerHTML =
                '<i class="mdi mdi-camera-off text-danger"></i> Kamera tidak ditemukan.';
            return;
        }

        // Pilih kamera belakang jika tersedia (di HP), atau default
        const kameraId = cameras.length > 1
            ? cameras[cameras.length - 1].id  // kamera belakang biasanya terakhir
            : cameras[0].id;

        html5QrCode.start(kameraId, config, onScanSuccess)
            .then(() => {
                document.getElementById('scanStatus').innerHTML =
                    '<span class="text-success"><i class="mdi mdi-record-circle"></i> Scanning aktif...</span>';
            })
            .catch(err => {
                document.getElementById('scanStatus').innerHTML =
                    '<i class="mdi mdi-alert text-danger"></i> Gagal membuka kamera: ' + err;
            });
    }).catch(err => {
        document.getElementById('scanStatus').innerHTML =
            '<i class="mdi mdi-alert text-danger"></i> Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.';
    });
}

// ── Tombol Scan Lagi ──────────────────────────────────────────────────
document.getElementById('btnScanLagi').addEventListener('click', function () {
    mulaiScanner();
});

// ── Auto-start saat halaman siap ──────────────────────────────────────
document.addEventListener('DOMContentLoaded', mulaiScanner);
</script>
@endpush
