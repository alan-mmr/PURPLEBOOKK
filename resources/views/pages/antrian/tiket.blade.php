@extends('layouts.clean')

@section('title', 'Tiket Antrian #' . $antrian->nomor_antrian . ' — PURPLEBOOK')

@push('styles')
<style>
    body { background: #f3e8ff; }

    .tiket-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 75vh;
        padding: 20px;
    }

    .tiket-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 12px 48px rgba(123,45,139,0.18);
        max-width: 440px;
        width: 100%;
        overflow: hidden;
        position: relative;
    }

    .tiket-header {
        background: linear-gradient(135deg, #7B2D8B, #a855c7);
        color: white;
        padding: 28px 32px 20px;
        text-align: center;
    }
    .tiket-header .logo { font-size: 1rem; opacity: 0.8; letter-spacing: 2px; text-transform: uppercase; }
    .tiket-header .vendor-name { font-size: 1.3rem; font-weight: 700; margin-top: 4px; }

    .tiket-number-wrapper {
        text-align: center;
        padding: 32px 32px 16px;
    }
    .tiket-label { font-size: 0.85rem; color: #888; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }
    .tiket-number {
        font-size: 7rem;
        font-weight: 900;
        color: #7B2D8B;
        line-height: 1;
        font-family: 'Courier New', monospace;
    }
    .tiket-nama {
        font-size: 1.4rem;
        font-weight: 700;
        color: #333;
        margin-top: 8px;
    }

    .tiket-divider {
        border: none;
        border-top: 2px dashed #e0d0f0;
        margin: 0 24px;
    }

    .tiket-info {
        padding: 20px 32px 32px;
        text-align: center;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 20px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 16px;
    }
    .status-menunggu   { background: #fff3e0; color: #e65100; }
    .status-dipanggil  { background: #e8f5e9; color: #1b5e20; animation: pulse-green 1s infinite; }
    .status-selesai    { background: #e3f2fd; color: #0d47a1; }
    .status-terlewat   { background: #fce4ec; color: #880e4f; }

    @keyframes pulse-green {
        0%, 100% { box-shadow: 0 0 0 0 rgba(27,94,32,0.3); }
        50% { box-shadow: 0 0 0 8px rgba(27,94,32,0); }
    }

    .posisi-info { color: #555; font-size: 0.95rem; margin-bottom: 8px; }
    .posisi-info strong { color: #7B2D8B; }

    .estimasi-box {
        background: linear-gradient(135deg, #f3e5fc, #e8d5f5);
        border-radius: 14px;
        padding: 16px;
        margin-bottom: 16px;
    }
    .estimasi-box .est-label { font-size: 0.78rem; color: #7B2D8B; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
    .countdown-display { font-size: 2.4rem; font-weight: 800; color: #4a1060; font-family: monospace; line-height: 1.2; }
    .estimasi-menit { font-size: 0.85rem; color: #7B2D8B; }

    .tiket-date { font-size: 0.8rem; color: #aaa; margin-top: 8px; }

    .btn-refresh {
        border: 2px solid #7B2D8B;
        color: #7B2D8B;
        border-radius: 10px;
        padding: 8px 24px;
        font-weight: 600;
        background: transparent;
        transition: all 0.2s;
    }
    .btn-refresh:hover { background: #7B2D8B; color: white; }

    .dipanggil-alert {
        background: linear-gradient(135deg, #1b5e20, #2e7d32);
        color: white;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 16px;
        text-align: center;
        animation: pulse-bg 1.5s ease-in-out infinite;
    }
    @keyframes pulse-bg {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.85; }
    }
</style>
@endpush

@section('content')
<div class="tiket-wrapper">
    <div class="tiket-card">
        {{-- Header --}}
        <div class="tiket-header">
            <div class="logo">🎟 PURPLEBOOK</div>
            <div class="vendor-name">{{ $antrian->vendor->nama_vendor ?? 'Vendor' }}</div>
        </div>

        {{-- Nomor --}}
        <div class="tiket-number-wrapper">
            <div class="tiket-label">Nomor Antrian</div>
            <div class="tiket-number">{{ str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT) }}</div>
            <div class="tiket-nama">{{ $antrian->nama }}</div>
        </div>

        <hr class="tiket-divider">

        {{-- Info & Countdown --}}
        <div class="tiket-info">

            {{-- Status Badge --}}
            <div id="statusBadge" class="status-badge status-{{ $antrian->status }}">
                @if($antrian->status === 'menunggu')   ⏳ Menunggu
                @elseif($antrian->status === 'dipanggil') ✅ Dipanggil!
                @elseif($antrian->status === 'selesai') ✔ Selesai
                @elseif($antrian->status === 'terlewat') ❌ Terlewat
                @endif
            </div>

            {{-- Pesan saat dipanggil --}}
            <div id="dipanggilAlert" class="dipanggil-alert" style="{{ $antrian->status === 'dipanggil' ? '' : 'display:none;' }}">
                <div style="font-size:1.8rem;">🔔</div>
                <div style="font-size:1.1rem; font-weight:700;">Nomor Anda Dipanggil!</div>
                <div style="font-size:0.9rem; opacity:0.9;">Silakan menuju {{ $antrian->vendor->nama_vendor ?? 'vendor' }}</div>
            </div>

            {{-- Countdown Estimasi (hanya tampil saat menunggu) --}}
            <div id="estimasiBox" class="estimasi-box" style="{{ in_array($antrian->status, ['menunggu']) ? '' : 'display:none;' }}">
                <div class="est-label">Estimasi Waktu Tunggu</div>
                <div class="countdown-display" id="countdownDisplay">
                    @if($antrian->status === 'menunggu' && $posisi)
                        {{ str_pad(floor($estimasiMnt / 60), 2, '0', STR_PAD_LEFT) }}:{{ str_pad($estimasiMnt % 60, 2, '0', STR_PAD_LEFT) }}
                    @else
                        —
                    @endif
                </div>
                <div class="estimasi-menit" id="estimasiMenit">
                    @if($antrian->status === 'menunggu' && $posisi)
                        ± {{ $estimasiMnt }} menit ({{ $sisaOrang }} orang di depan × 5 menit)
                    @endif
                </div>
            </div>

            {{-- Posisi antrian --}}
            <p class="posisi-info" id="posisiInfo">
                @if($antrian->status === 'menunggu' && $posisi)
                    Posisi antrian Anda: <strong>#{{ $posisi }}</strong> dari {{ $totalMenunggu }} orang menunggu
                @endif
            </p>

            <div class="tiket-date">
                Diambil: {{ $antrian->created_at->format('d M Y, H:i') }}
            </div>

            <div class="mt-3 d-flex gap-2 justify-content-center" style="gap:10px;">
                <button onclick="location.reload()" class="btn btn-refresh">
                    <i class="mdi mdi-refresh"></i> Refresh
                </button>
                <a href="{{ route('antrian.guest') }}" class="btn btn-refresh">
                    <i class="mdi mdi-ticket-outline"></i> Ambil Antrian Lain
                </a>
            </div>
        </div>
    </div>
</div>

<div style="text-align:center; padding: 16px; color:#aaa; font-size:0.8rem;">
    🎟 PURPLEBOOK — Antrian Digital
</div>
@endsection

@push('scripts')
<script>
const NOMOR_SAYA      = {{ $antrian->nomor_antrian }};
const VENDOR_ID       = {{ $antrian->idvendor }};
const MENIT_PER_ORANG = 5;
let countdownInterval = null;

// Mulai countdown dari nilai PHP (langsung tampil tanpa tunggu SSE)
@if($antrian->status === 'menunggu' && $posisi)
startCountdown({{ $estimasiMnt * 60 }});
@endif

// ── SSE Connection (update live jika SSE berfungsi) ──────────
const source = new EventSource('{{ route("sse.antrian") }}?vendor=' + VENDOR_ID);

source.addEventListener('antrian-update', function(e) {
    const data = JSON.parse(e.data);
    updateTiket(data);
});

source.onerror = function() {
    console.warn('SSE terputus, mencoba reconnect...');
};

// ── Update UI berdasarkan data SSE ───────────────────────────
function updateTiket(data) {
    const menunggu = data.menunggu || [];
    const dipanggil = data.dipanggil;

    // Cek apakah nomor saya dipanggil
    if (dipanggil && dipanggil.nomor === NOMOR_SAYA) {
        setStatusDipanggil();
        return;
    }

    // Cari posisi saya di antrian menunggu
    const posisiSaya = menunggu.findIndex(a => a.nomor === NOMOR_SAYA);

    if (posisiSaya === -1) {
        // Sudah tidak ada di antrian menunggu (mungkin terlewat/selesai)
        // Jangan update UI (biarkan status lama)
        return;
    }

    const posisi = posisiSaya + 1; // 1-based
    const sisaOrang = posisiSaya;  // orang di depan saya
    const estimasiMenit = sisaOrang * MENIT_PER_ORANG;

    // Update posisi text
    document.getElementById('posisiInfo').innerHTML =
        `Posisi antrian Anda: <strong>#${posisi}</strong> dari ${menunggu.length} orang menunggu`;

    // Update estimasi
    document.getElementById('estimasiMenit').textContent =
        `± ${estimasiMenit} menit (${sisaOrang} orang di depan × ${MENIT_PER_ORANG} menit)`;

    // Mulai countdown
    startCountdown(estimasiMenit * 60); // detik
}

// ── Countdown Timer ──────────────────────────────────────────
function startCountdown(totalDetik) {
    if (countdownInterval) clearInterval(countdownInterval);
    let sisa = totalDetik;

    function tick() {
        if (sisa <= 0) {
            document.getElementById('countdownDisplay').textContent = '⏰ Segera!';
            clearInterval(countdownInterval);
            return;
        }
        const jam  = Math.floor(sisa / 3600);
        const mnt  = Math.floor((sisa % 3600) / 60);
        const dtk  = sisa % 60;
        let display = '';
        if (jam > 0) display += `${jam}j `;
        display += `${String(mnt).padStart(2,'0')}:${String(dtk).padStart(2,'0')}`;
        document.getElementById('countdownDisplay').textContent = display;
        sisa--;
    }
    tick();
    countdownInterval = setInterval(tick, 1000);
}

// ── Set tampilan saat dipanggil ───────────────────────────────
function setStatusDipanggil() {
    if (countdownInterval) clearInterval(countdownInterval);
    document.getElementById('dipanggilAlert').style.display = 'block';
    document.getElementById('estimasiBox').style.display   = 'none';
    document.getElementById('posisiInfo').textContent = '';
    const badge = document.getElementById('statusBadge');
    badge.className = 'status-badge status-dipanggil';
    badge.textContent = '✅ Dipanggil!';
}

// Pastikan SSE ditutup saat pindah halaman (misal klik menu sidebar)
window.addEventListener('beforeunload', function() {
    if (source) source.close();
});

// Interceptor ekstra untuk memastikan SSE mati sebelum request HTTP baru (Sidebar links)
document.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', function() {
        if (source && !this.target && !this.href.startsWith('javascript:')) {
            source.close();
        }
    });
});
</script>
@endpush
