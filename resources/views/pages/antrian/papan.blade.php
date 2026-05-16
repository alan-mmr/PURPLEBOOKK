@extends('layouts.clean')

@section('title', 'Papan Antrian' . ($vendor ? ' — ' . $vendor->nama_vendor : '') . ' — PURPLEBOOK')

@push('styles')
<style>
    body { background: #0d0d1a; }
    .papan-page { padding: 24px 28px; }

    .papan-wrapper {
        min-height: 85vh;
        display: flex;
        flex-direction: column;
        gap: 20px;
        padding-bottom: 20px;
    }

    /* ── Vendor Switcher ── */
    .vendor-tabs { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 8px; }
    .vendor-tab-btn {
        padding: 6px 18px;
        border-radius: 20px;
        border: 2px solid rgba(168,85,199,0.5);
        color: rgba(200,150,240,0.9);
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s;
        background: transparent;
    }
    .vendor-tab-btn:hover, .vendor-tab-btn.active {
        background: #7B2D8B;
        border-color: #7B2D8B;
        color: white;
        text-decoration: none;
    }

    /* ── Nomor Aktif ── */
    .panel-nomor-aktif {
        background: linear-gradient(135deg, #1a0030, #3d0070);
        border: 2px solid rgba(168,85,199,0.4);
        border-radius: 24px;
        padding: 36px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .panel-nomor-aktif::before {
        content: '';
        position: absolute;
        top: -50%; left: -50%;
        width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(168,85,199,0.08) 0%, transparent 60%);
        animation: shimmer 3s ease-in-out infinite;
    }
    @keyframes shimmer { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }

    .aktif-label { font-size: 0.8rem; color: #a855c7; text-transform: uppercase; letter-spacing: 3px; font-weight: 600; margin-bottom: 8px; }
    .aktif-vendor { color: rgba(200,150,240,0.8); font-size: 1rem; margin-bottom: 8px; }
    .nomor-aktif {
        font-size: 9rem;
        font-weight: 900;
        font-family: monospace;
        line-height: 1;
        background: linear-gradient(135deg, #e040fb, #a855c7, #7c3aed);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: glow 2s ease-in-out infinite alternate;
    }
    @keyframes glow {
        from { filter: drop-shadow(0 0 8px rgba(168,85,199,0.4)); }
        to   { filter: drop-shadow(0 0 20px rgba(168,85,199,0.8)); }
    }
    .nama-aktif { font-size: 2rem; font-weight: 700; color: white; margin-top: 8px; }
    .kosong-text { color: rgba(255,255,255,0.3); font-size: 1.5rem; padding: 40px; }

    /* ── List Menunggu ── */
    .panel-menunggu { background: #111122; border: 1px solid rgba(168,85,199,0.2); border-radius: 20px; padding: 24px; }
    .panel-menunggu .panel-title { color: #a855c7; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 16px; }
    .menunggu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; }
    .menunggu-item {
        background: rgba(168,85,199,0.08);
        border: 1px solid rgba(168,85,199,0.2);
        border-radius: 12px;
        padding: 12px;
        text-align: center;
        transition: all 0.3s;
    }
    .menunggu-item .m-nomor { font-family: monospace; font-size: 1.6rem; font-weight: 800; color: #d0a0ff; }
    .menunggu-item .m-nama  { font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .menunggu-item .m-estimasi { font-size: 0.7rem; color: #a855c7; margin-top: 4px; }
    .menunggu-item.next-up { border-color: rgba(168,85,199,0.6); background: rgba(168,85,199,0.15); }

    /* ── Footer Status ── */
    .papan-footer { display: flex; align-items: center; justify-content: space-between; color: rgba(255,255,255,0.3); font-size: 0.8rem; padding: 0 8px; }
    .sse-dot { width: 8px; height: 8px; border-radius: 50%; background: #27ae60; display: inline-block; margin-right: 6px; animation: blink 1.5s infinite; }
    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.2; } }

    /* ── Panggilan Flash ── */
    .flash-overlay {
        position: fixed;
        inset: 0;
        background: rgba(168,85,199,0.15);
        z-index: 9999;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .flash-overlay.show { opacity: 1; }
</style>
@endpush

@section('content')
<div class="papan-page">

{{-- Flash overlay saat ada panggilan --}}
<div class="flash-overlay" id="flashOverlay"></div>

{{-- Audio bel antrian (Airplane Ding Dong - professional) --}}
<audio id="audioDingdong" src="{{ asset('assets/audio/dingdong.mp3') }}?v={{ filemtime(public_path('assets/audio/dingdong.mp3')) }}" preload="auto"></audio>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0" style="color:rgba(255,255,255,0.9); font-weight:700; font-size:1.3rem;">
        <span style="background:#7B2D8B; border-radius:8px; padding:4px 10px; margin-right:8px;">
            <i class="mdi mdi-television" style="color:white;"></i>
        </span>
        Papan Antrian
        <span id="judulVendor" style="color:#a855c7; margin-left:8px; font-size:0.9rem;">{{ $vendor ? '— '.$vendor->nama_vendor : '— Semua Vendor' }}</span>
    </h3>
    <small style="color:rgba(255,255,255,0.4); font-size:0.8rem;" id="waktuSekarang"></small>
</div>

{{-- Vendor Switcher --}}
<div class="vendor-tabs">
    <button type="button" onclick="switchVendorPapan(null)" id="btn-vendor-semua"
       class="vendor-tab-btn {{ !$vendorId ? 'active' : '' }}">🌐 Semua</button>
    @foreach($vendors as $v)
    <button type="button" onclick="switchVendorPapan({{ $v->idvendor }})" id="btn-vendor-{{ $v->idvendor }}"
       class="vendor-tab-btn {{ $vendorId == $v->idvendor ? 'active' : '' }}">
        🏪 {{ $v->nama_vendor }}
    </button>
    @endforeach
</div>


<div class="papan-wrapper">

    {{-- Nomor Aktif --}}
    <div class="panel-nomor-aktif">
        <div class="aktif-label">Sedang Dipanggil</div>
        <div class="aktif-vendor" id="vendorAktif">—</div>
        <div class="nomor-aktif" id="nomorAktif">—</div>
        <div class="nama-aktif" id="namaAktif">Menunggu panggilan...</div>
    </div>

    {{-- List Menunggu --}}
    <div class="panel-menunggu">
        <div class="panel-title">⏳ Antrian Selanjutnya</div>
        <div class="menunggu-grid" id="gridMenunggu">
            <div style="color:rgba(255,255,255,0.3); font-size:0.9rem;">Memuat data...</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="papan-footer">
        <div>
            <span class="sse-dot" id="sseDot"></span>
            <span id="sseStatusPapan">Menghubungkan...</span>
        </div>
        <div id="lastUpdate">—</div>
    </div>

</div>

{{-- Aktifkan audio dengan klik pertama (user gesture policy) --}}
<div id="activateOverlay" style="
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.85);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
" onclick="aktivasiAudio()">
    <div style="text-align:center; color:white;">
        <div style="font-size:4rem;">🔊</div>
        <div style="font-size:1.5rem; font-weight:700; margin-top:16px;">Klik untuk Aktifkan Suara</div>
        <div style="color:rgba(255,255,255,0.6); margin-top:8px; font-size:0.9rem;">Papan Antrian Digital — PURPLEBOOK</div>
    </div>
</div>

</div>{{-- end papan-page --}}
@endsection

@push('scripts')
<script>
let currentVendorId = {{ $vendorId ? $vendorId : 'null' }};
const MENIT_PER_ORANG = 5;
const audio = document.getElementById('audioDingdong');
let lastNomorDipanggil = null;
let audioAktif = false;
let source = null;

// ── Aktivasi Audio (User Gesture) ────────────────────────────
function aktivasiAudio() {
    audioAktif = true;
    document.getElementById('activateOverlay').style.display = 'none';
    // Unlock audio context dengan play pendek
    audio.play().then(() => { audio.pause(); audio.currentTime = 0; }).catch(() => {});
}

// ── Jam Real-time ─────────────────────────────────────────────
function updateJam() {
    const now = new Date();
    document.getElementById('waktuSekarang').textContent =
        now.toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' }) +
        ' ' + now.toLocaleTimeString('id-ID');
}
setInterval(updateJam, 1000);
updateJam();

// ── Vendor Switcher & SSE ─────────────────────────────────────
function switchVendorPapan(vendorId) {
    if (source) source.close(); // Tutup koneksi lama!
    currentVendorId = vendorId;

    // Update URL tanpa reload
    window.history.pushState({}, '', vendorId ? '?vendor=' + vendorId : '{{ route("antrian.papan") }}');

    // Update tombol aktif
    document.querySelectorAll('.vendor-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(vendorId ? 'btn-vendor-' + vendorId : 'btn-vendor-semua').classList.add('active');

    // Update judul
    const judulEl = document.getElementById('judulVendor');
    if (judulEl) {
        judulEl.textContent = vendorId
            ? '— ' + (document.getElementById('btn-vendor-' + vendorId)?.textContent?.trim() ?? '')
            : '— Semua Vendor';
    }

    document.getElementById('sseStatusPapan').textContent = 'Menghubungkan...';
    document.getElementById('sseDot').style.background = '#f39c12';

    // Buka SSE baru
    const sseUrl = '{{ route("sse.antrian") }}' + (vendorId ? '?vendor=' + vendorId : '');
    source = new EventSource(sseUrl);

    source.addEventListener('antrian-update', function(e) {
        const data = JSON.parse(e.data);
        updatePapan(data);
        document.getElementById('sseDot').style.background = '#27ae60';
        document.getElementById('sseStatusPapan').textContent = 'Live';
        document.getElementById('lastUpdate').textContent = 'Update: ' + (data.updated_at || '—');
    });

    source.onerror = function() {
        document.getElementById('sseDot').style.background = '#e74c3c';
        document.getElementById('sseStatusPapan').textContent = 'Terputus, reconnect...';
    };
}

// Initial start
switchVendorPapan(currentVendorId);

// Pastikan SSE ditutup saat keluar halaman
window.addEventListener('beforeunload', () => { if(source) source.close(); });

// Interceptor ekstra untuk memastikan SSE mati sebelum request HTTP baru (Sidebar/Menu links)
document.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', function() {
        if (source && !this.target && !this.href.startsWith('javascript:')) {
            source.close();
        }
    });
});


// ── Update Tampilan Papan ─────────────────────────────────────
function updatePapan(data) {
    const dipanggil = data.dipanggil;
    const menunggu  = data.menunggu || [];

    // ── Nomor aktif ──
    if (dipanggil) {
        document.getElementById('nomorAktif').textContent   = String(dipanggil.nomor).padStart(3, '0');
        document.getElementById('namaAktif').textContent    = dipanggil.nama;
        document.getElementById('vendorAktif').textContent  = dipanggil.vendor || data.nama_vendor || '—';

        // Putar suara saat nomor berubah ATAU saat Ulangi ditekan (called_at berubah)
        // Composite key: nomor + called_at sehingga keduanya di-track sekaligus
        const suaraKey = dipanggil.nomor + '|' + (dipanggil.called_at || '');
        if (suaraKey !== lastNomorDipanggil) {
            lastNomorDipanggil = suaraKey;
            putarSuara(dipanggil.nomor, dipanggil.nama, dipanggil.vendor || data.nama_vendor || '');
            flashEffect();
        }
    } else {
        document.getElementById('nomorAktif').textContent   = '—';
        document.getElementById('namaAktif').textContent    = 'Menunggu panggilan...';
        document.getElementById('vendorAktif').textContent  = '—';
    }

    // ── Grid menunggu ──
    const grid = document.getElementById('gridMenunggu');
    if (menunggu.length === 0) {
        grid.innerHTML = '<div style="color:rgba(255,255,255,0.3); font-size:0.9rem; grid-column:1/-1; text-align:center; padding:20px;">Tidak ada antrian menunggu</div>';
    } else {
        grid.innerHTML = menunggu.slice(0, 10).map((a, idx) => {
            const estimasi = idx * MENIT_PER_ORANG;
            return `
            <div class="menunggu-item ${idx === 0 ? 'next-up' : ''}">
                <div class="m-nomor">${String(a.nomor).padStart(3,'0')}</div>
                <div class="m-nama">${a.nama}</div>
                <div class="m-estimasi">~${estimasi > 0 ? estimasi + ' mnt' : 'Berikutnya'}</div>
            </div>`;
        }).join('');
    }
}

// ── Putar Suara: MP3 Bel + TTS ───────────────────────────────
function putarSuara(nomor, nama, vendor) {
    if (!audioAktif) return;

    window.speechSynthesis.cancel();

    const teks = `Nomor antrian, ${nomor}, ${nama}, silakan menuju, ${vendor}.`;
    const pesan = new SpeechSynthesisUtterance(teks);
    pesan.lang   = 'id-ID';
    pesan.rate   = 0.85;
    pesan.pitch  = 1.0;
    pesan.volume = 1.0;

    // Putar MP3 ding dong dulu, setelah selesai baru TTS
    audio.currentTime = 0;
    audio.play().then(() => {
        audio.onended = () => {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.speak(pesan);
            }
        };
    }).catch(() => {
        // Jika audio gagal, langsung TTS saja
        if ('speechSynthesis' in window) {
            window.speechSynthesis.speak(pesan);
        }
    });
}

// ── Flash Effect ──────────────────────────────────────────────
function flashEffect() {
    const overlay = document.getElementById('flashOverlay');
    overlay.classList.add('show');
    setTimeout(() => overlay.classList.remove('show'), 600);
}
</script>
@endpush
