@extends('layouts.main')

@section('title', 'Scan QR Pesanan - PURPLEBOOK')

@push('styles')
<style>
    /* ── Scanner Container ─────────────────────────────────── */
    #readerQr {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        border-radius: 12px;
        overflow: hidden;
        border: 3px solid #28a745;
    }

    #readerQr__scan_region img,
    #readerQr__dashboard_section_csr span {
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

    /* ── Badge Status Bayar ────────────────────────────────── */
    .badge-status-paid     { background: #28a745; color: white; font-size: 1rem; padding: 5px 14px; border-radius: 20px; }
    .badge-status-pending  { background: #ffc107; color: #333;  font-size: 1rem; padding: 5px 14px; border-radius: 20px; }
    .badge-status-failed   { background: #dc3545; color: white; font-size: 1rem; padding: 5px 14px; border-radius: 20px; }
    .badge-status-expired  { background: #6c757d; color: white; font-size: 1rem; padding: 5px 14px; border-radius: 20px; }

    /* ── Item Menu Card ────────────────────────────────────── */
    .menu-item-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-success text-white mr-2">
            <i class="mdi mdi-qrcode-scan"></i>
        </span> Scan QR Pesanan — {{ $vendor->nama_vendor }}
    </h3>
    <nav>
        <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </nav>
</div>

<div class="row justify-content-center">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-center mb-1">
                    <i class="mdi mdi-qrcode text-success"></i> Scanner QR Code
                </h4>
                <p class="text-muted text-center small mb-3">
                    Scan QR Code dari layar HP customer
                </p>

                {{-- ── Status Scanner ── --}}
                <div id="scanStatus" class="text-center text-muted mb-2" style="font-size: 0.9rem; min-height: 28px;">
                    <i class="mdi mdi-camera-outline"></i> Memulai kamera...
                </div>

                {{-- ── Area Kamera ── --}}
                <div id="readerQr"></div>

                {{-- ── Hasil Scan: Pesanan ── --}}
                <div id="hasilScan" class="mt-4">
                    {{-- Header Info Pesanan --}}
                    <div class="p-3 rounded mb-3" style="background: linear-gradient(135deg, #e8f5e9, #f1f8e9); border: 1px solid #a5d6a7;">
                        <div class="text-center mb-2">
                            <i class="mdi mdi-check-circle mdi-36px text-success"></i>
                            <h5 class="font-weight-bold mt-1 mb-0 text-success">QR Code Valid!</h5>
                        </div>
                        <table class="table table-borderless mb-0 small">
                            <tr>
                                <td class="text-muted font-weight-bold" width="120">ID Pesanan</td>
                                <td><span id="hasilIdPesanan" class="badge badge-dark">—</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted font-weight-bold">Nama Tamu</td>
                                <td><strong id="hasilNama">—</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted font-weight-bold">Total</td>
                                <td><strong id="hasilTotal" class="text-primary">—</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted font-weight-bold">Status Bayar</td>
                                <td><span id="hasilStatus">—</span></td>
                            </tr>
                        </table>
                    </div>

                    {{-- Daftar Menu yang Dipesan --}}
                    <h6 class="font-weight-bold mb-2">
                        <i class="mdi mdi-food text-success"></i> Menu Dipesan:
                    </h6>
                    <div id="hasilItems">
                        {{-- Diisi oleh JavaScript --}}
                    </div>
                </div>

                {{-- ── Pesan Error ── --}}
                <div id="hasilError" class="alert alert-danger mt-3" style="display:none;">
                    <i class="mdi mdi-alert-circle"></i>
                    <span id="errorText">QR Code tidak valid.</span>
                </div>

                {{-- ── Tombol Scan Lagi ── --}}
                <button id="btnScanLagi" class="btn btn-gradient-success btn-block mt-3" style="display:none;">
                    <i class="mdi mdi-refresh"></i> Scan QR Berikutnya
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Html5-qrcode CDN v2.3.8 --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// ── Beep Sound (Web Audio API) ─────────────────────────────────────────
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

// ── State ─────────────────────────────────────────────────────────────
let html5QrCode = null;
let sudahScan   = false;

// ── Konfigurasi QR Scanner ────────────────────────────────────────────
const config = {
    fps: 10,
    qrbox: { width: 250, height: 250 },  // QR Code berbentuk kotak
    aspectRatio: 1.0,
    formatsToSupport: [
        Html5QrcodeSupportedFormats.QR_CODE,
    ]
};

// ── Callback: QR Code berhasil dibaca ────────────────────────────────
function onScanSuccess(decodedText) {
    if (sudahScan) return;
    sudahScan = true;

    // 1. Beep pendek
    playBeep();

    // 2. Stop scanner
    if (html5QrCode) {
        html5QrCode.stop().catch(() => {});
    }

    // 3. Update status
    document.getElementById('scanStatus').innerHTML =
        '<i class="mdi mdi-check text-success"></i> QR terbaca, memuat data...';

    // 4. AJAX: lookup pesanan
    fetch('{{ route("vendor.lookupPesanan") }}?id=' + encodeURIComponent(decodedText))
        .then(res => res.json().then(data => ({ status: res.status, data })))
        .then(({ status, data }) => {
            if (status === 200) {
                tampilHasil(data);
            } else {
                tampilError(data.error || 'Pesanan tidak ditemukan.');
            }
        })
        .catch(() => {
            tampilError('Gagal menghubungi server. Periksa koneksi internet.');
        });
}

// ── Tampilkan hasil pesanan ───────────────────────────────────────────
function tampilHasil(pesanan) {
    document.getElementById('hasilIdPesanan').textContent = '#' + pesanan.idpesanan;
    document.getElementById('hasilNama').textContent      = pesanan.nama_pemesan;
    document.getElementById('hasilTotal').textContent     = 'Rp ' + pesanan.total_harga.toLocaleString('id-ID');

    // Badge status bayar dengan warna berbeda
    const statusMap = {
        'paid':    ['badge-status-paid',    'Lunas ✓'],
        'pending': ['badge-status-pending', 'Menunggu Bayar'],
        'failed':  ['badge-status-failed',  'Gagal'],
        'expired': ['badge-status-expired', 'Kedaluwarsa'],
    };
    const [cls, label] = statusMap[pesanan.status_bayar] || ['badge-status-pending', pesanan.status_bayar];
    document.getElementById('hasilStatus').innerHTML = `<span class="${cls}">${label}</span>`;

    // Render daftar menu
    let itemsHtml = '';
    if (pesanan.items && pesanan.items.length > 0) {
        pesanan.items.forEach(item => {
            const subtotal = item.subtotal.toLocaleString('id-ID');
            itemsHtml += `
            <div class="menu-item-card">
                <div>
                    <strong>${item.nama_menu}</strong>
                    <span class="text-muted ml-2">×${item.jumlah}</span>
                </div>
                <span class="font-weight-bold text-primary">Rp ${subtotal}</span>
            </div>`;
        });
    } else {
        itemsHtml = '<p class="text-muted text-center">Tidak ada item menu.</p>';
    }
    document.getElementById('hasilItems').innerHTML    = itemsHtml;
    document.getElementById('hasilScan').style.display  = 'block';
    document.getElementById('hasilError').style.display = 'none';
    document.getElementById('btnScanLagi').style.display = 'block';
    document.getElementById('scanStatus').innerHTML =
        '<i class="mdi mdi-check-circle text-success"></i> Pesanan berhasil dimuat.';
}

// ── Tampilkan error ───────────────────────────────────────────────────
function tampilError(pesan) {
    document.getElementById('errorText').textContent    = pesan;
    document.getElementById('hasilError').style.display = 'block';
    document.getElementById('hasilScan').style.display  = 'none';
    document.getElementById('btnScanLagi').style.display = 'block';
    document.getElementById('scanStatus').innerHTML =
        '<i class="mdi mdi-alert text-danger"></i> Scan gagal.';
}

// ── Mulai scanner ─────────────────────────────────────────────────────
function mulaiScanner() {
    sudahScan = false;
    document.getElementById('hasilScan').style.display   = 'none';
    document.getElementById('hasilError').style.display  = 'none';
    document.getElementById('btnScanLagi').style.display = 'none';
    document.getElementById('hasilItems').innerHTML      = '';
    document.getElementById('scanStatus').innerHTML =
        '<i class="mdi mdi-camera-outline"></i> Kamera aktif — arahkan ke QR Code...';

    html5QrCode = new Html5Qrcode('readerQr');

    Html5Qrcode.getCameras().then(cameras => {
        if (!cameras || cameras.length === 0) {
            document.getElementById('scanStatus').innerHTML =
                '<i class="mdi mdi-camera-off text-danger"></i> Kamera tidak ditemukan.';
            return;
        }

        const kameraId = cameras.length > 1
            ? cameras[cameras.length - 1].id
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
    }).catch(() => {
        document.getElementById('scanStatus').innerHTML =
            '<i class="mdi mdi-alert text-danger"></i> Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.';
    });
}

// ── Tombol Scan Lagi ──────────────────────────────────────────────────
document.getElementById('btnScanLagi').addEventListener('click', mulaiScanner);

// ── Auto-start ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', mulaiScanner);
</script>
@endpush
