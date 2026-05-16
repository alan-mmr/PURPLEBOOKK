@extends('layouts.main')

@section('title', 'Dashboard Antrian — PURPLEBOOK')

@push('styles')
<style>
    .vendor-switcher { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px; }
    .vendor-btn {
        padding: 8px 20px;
        border-radius: 20px;
        border: 2px solid #7B2D8B;
        color: #7B2D8B;
        font-weight: 600;
        cursor: pointer;
        background: white;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    .vendor-btn:hover, .vendor-btn.active {
        background: #7B2D8B;
        color: white;
        text-decoration: none;
    }

    .panel-dipanggil {
        background: linear-gradient(135deg, #7B2D8B, #a855c7);
        color: white;
        border-radius: 20px;
        padding: 28px;
        text-align: center;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .panel-dipanggil .label { font-size: 0.85rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 2px; }
    .panel-dipanggil .nomor-besar { font-size: 6rem; font-weight: 900; line-height: 1; font-family: monospace; }
    .panel-dipanggil .nama-dipanggil { font-size: 1.4rem; font-weight: 600; margin-top: 4px; opacity: 0.9; }
    .panel-dipanggil .kosong { font-size: 3rem; opacity: 0.4; }

    .antrian-list .antrian-item {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border-radius: 10px;
        background: #f8f4fc;
        margin-bottom: 8px;
        transition: background 0.2s;
    }
    .antrian-item:hover { background: #ede0f8; }
    .antrian-item .nomor-chip {
        background: #7B2D8B;
        color: white;
        font-weight: 800;
        font-size: 1rem;
        border-radius: 8px;
        padding: 4px 12px;
        margin-right: 12px;
        min-width: 44px;
        text-align: center;
        font-family: monospace;
    }
    .antrian-item .nama-item { font-weight: 600; color: #333; flex: 1; }
    .antrian-item .aksi-btn { display: flex; gap: 6px; }

    .terlewat-item .nomor-chip { background: #c0392b; }
    .terlewat-item { background: #fff0ee; }
    .terlewat-item:hover { background: #ffe0dc; }

    .btn-xs { padding: 4px 10px; font-size: 0.78rem; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; }
    .btn-panggil    { background: #27ae60; color: white; }
    .btn-terlewat   { background: #e67e22; color: white; }
    .btn-selesai    { background: #3498db; color: white; }
    .btn-ulangi     { background: #9b59b6; color: white; }
    .btn-panggil-ulang { background: #c0392b; color: white; }
    .btn-xs:hover { opacity: 0.85; }

    .section-title { font-weight: 700; color: #4a1060; font-size: 1rem; margin-bottom: 12px; }
    .count-badge { background: #7B2D8B; color: white; border-radius: 12px; padding: 2px 10px; font-size: 0.8rem; font-weight: 700; margin-left: 6px; }
    .empty-state { color: #bbb; font-style: italic; font-size: 0.9rem; text-align: center; padding: 20px 0; }

    .sse-indicator { width: 10px; height: 10px; border-radius: 50%; background: #27ae60; display: inline-block; margin-right: 6px; animation: blink 1.5s infinite; }
    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
</style>
@endpush

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <h3 class="page-title mb-0">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-account-multiple-check"></i>
        </span> Dashboard Antrian
    </h3>
    <div class="d-flex align-items-center">
        <span class="sse-indicator" id="sseIndicator"></span>
        <small class="text-muted" id="sseStatus">Menghubungkan SSE...</small>
    </div>
</div>

{{-- Vendor Switcher --}}
<div class="vendor-switcher">
    {{-- Tombol Global (default) --}}
    <button type="button" onclick="switchVendor(null)" id="btn-vendor-global"
       class="vendor-btn {{ !$activeVendor ? 'active' : '' }}" style="border-color:#3498db; color:#3498db;">
        🌐 Semua
    </button>
    @foreach($vendors as $v)
    <button type="button" onclick="switchVendor({{ $v->idvendor }})" id="btn-vendor-{{ $v->idvendor }}"
       class="vendor-btn {{ $activeVendor && $activeVendor->idvendor == $v->idvendor ? 'active' : '' }}">
        🏪 {{ $v->nama_vendor }}
    </button>
    @endforeach
    <a href="{{ route('antrian.papan') }}"
       id="btnBukaPapan"
       class="vendor-btn" target="_blank" style="border-color:#27ae60; color:#27ae60;">
        📺 Buka Papan
    </a>
</div>


{{-- Alert (selalu tersembunyi, ditampilkan JS jika diperlukan) --}}
<div class="alert alert-warning" id="alertPilihVendor" style="display:none;">Pilih vendor di atas untuk melihat antriannya.</div>

<div class="row" id="adminContent">

    {{-- Kolom Kiri: Antrian Menunggu --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="section-title">
                    ⏳ Menunggu
                    <span class="count-badge" id="jumlahMenunggu">{{ count($menunggu) }}</span>
                </div>
                <div class="antrian-list" id="listMenunggu">
                    @forelse($menunggu as $item)
                    <div class="antrian-item" id="item-{{ $item->id }}">
                        <div class="nomor-chip">{{ str_pad($item->nomor_antrian, 3, '0', STR_PAD_LEFT) }}</div>
                        <div class="nama-item">
                            {{ $item->nama }}
                            @if(!$activeVendor && $item->vendor)
                                <small style="color:#888; display:block;">{{ $item->vendor->nama_vendor }}</small>
                            @endif
                        </div>
                        <div class="aksi-btn">
                            <form method="POST" action="{{ route('antrian.panggil', $item->id) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn-xs btn-panggil" title="Panggil">📢</button>
                            </form>
                            <form method="POST" action="{{ route('antrian.terlewat', $item->id) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn-xs btn-terlewat" title="Tandai Terlewat">⏭</button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state" id="emptyMenunggu">Tidak ada antrian menunggu</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom Tengah: Sedang Dipanggil --}}
    <div class="col-md-4">
        <div class="panel-dipanggil mb-3" id="panelDipanggil">
            @if($dipanggil)
            <div class="label">Sedang Dipanggil</div>
            <div class="nomor-besar">{{ str_pad($dipanggil->nomor_antrian, 3, '0', STR_PAD_LEFT) }}</div>
            <div class="nama-dipanggil">{{ $dipanggil->nama }}</div>
            @if(!$activeVendor && $dipanggil->vendor)
                <div style="color:rgba(255,255,255,0.7); font-size:0.85rem; margin-top:4px;">{{ $dipanggil->vendor->nama_vendor }}</div>
            @endif
            <div class="mt-3" style="display:flex; gap:8px; flex-wrap:wrap; justify-content:center;">
                <form method="POST" action="{{ route('antrian.selesai', $dipanggil->id) }}">
                    @csrf
                    <button type="submit" class="btn-xs btn-selesai" style="padding:6px 16px;">✔ Selesai</button>
                </form>
                <form method="POST" action="{{ route('antrian.ulangi', $dipanggil->id) }}">
                    @csrf
                    <button type="submit" class="btn-xs btn-ulangi" style="padding:6px 16px;">🔁 Ulangi</button>
                </form>
                <form method="POST" action="{{ route('antrian.terlewat', $dipanggil->id) }}">
                    @csrf
                    <button type="submit" class="btn-xs btn-terlewat" style="padding:6px 16px;">⏭ Terlewat</button>
                </form>
            </div>
            @else
            <div class="kosong">🔔</div>
            <div style="opacity:0.6; margin-top:12px;">Belum ada yang dipanggil</div>
            @endif
        </div>

        {{-- Tombol Panggil Berikutnya --}}
        <div id="wrapperBerikutnya">
        @if(count($menunggu) > 0)
            <button id="btnBerikutnya" class="btn btn-gradient-success btn-block"
                style="border-radius:12px; font-weight:700; font-size:1rem; padding:14px;">
                📢 Panggil Berikutnya (No. {{ str_pad($menunggu->first()->nomor_antrian, 3, '0', STR_PAD_LEFT) }})
            </button>
        @else
            <button id="btnBerikutnya" class="btn btn-block" disabled
                style="border-radius:12px; background:#eee; color:#aaa; padding:14px;">
                Tidak ada antrian
            </button>
        @endif
        </div>
    </div>

    {{-- Kolom Kanan: Terlewat --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="section-title">
                    ❌ Terlewat
                    <span class="count-badge" id="jumlahTerlewat" style="background:#c0392b;">{{ count($terlewat) }}</span>
                </div>
                <div class="antrian-list" id="listTerlewat">
                    @forelse($terlewat as $item)
                    <div class="antrian-item terlewat-item">
                        <div class="nomor-chip">{{ str_pad($item->nomor_antrian, 3, '0', STR_PAD_LEFT) }}</div>
                        <div class="nama-item">
                            {{ $item->nama }}
                            @if(!$activeVendor && $item->vendor)
                                <small style="color:#888; display:block;">{{ $item->vendor->nama_vendor }}</small>
                            @endif
                        </div>
                        <div class="aksi-btn">
                            <form method="POST" action="{{ route('antrian.panggilTerlewat', $item->id) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn-xs btn-panggil-ulang" title="Panggil Ulang">🔄</button>
                            </form>
                            <form method="POST" action="{{ route('antrian.selesai', $item->id) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn-xs btn-selesai" title="Selesai">✔</button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">Tidak ada yang terlewat</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
let currentVendorId = {{ $activeVendor ? $activeVendor->idvendor : 'null' }};
let source = null;
const BASE_URL  = '{{ url("antrian") }}';
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;

// Default ke Global jika tidak ada vendor dipilih
switchVendor(currentVendorId);

function switchVendor(vendorId) {
    if (source) source.close(); // ⚡ KUNCI: Tutup koneksi lama sebelum buka yang baru!
    currentVendorId = vendorId;

    // Update URL tanpa reload halaman
    window.history.pushState({}, '', vendorId ? '?vendor=' + vendorId : '{{ route("antrian.admin") }}');

    // Update tampilan tombol vendor
    document.querySelectorAll('.vendor-switcher .vendor-btn').forEach(btn => btn.classList.remove('active'));
    const activeBtn = vendorId
        ? document.getElementById('btn-vendor-' + vendorId)
        : document.getElementById('btn-vendor-global');
    if (activeBtn) activeBtn.classList.add('active');

    // Update link Buka Papan
    document.getElementById('btnBukaPapan').href = vendorId
        ? '{{ route("antrian.papan") }}?vendor=' + vendorId
        : '{{ route("antrian.papan") }}';

    // Tampilkan panel antrian (selalu tampil, termasuk mode global)
    document.getElementById('alertPilihVendor').style.display = 'none';
    document.getElementById('adminContent').style.display = '';

    document.getElementById('sseStatus').textContent = 'Menghubungkan...';
    document.getElementById('sseIndicator').style.background = '#f39c12';

    // ── Buka SSE Connection Baru ───────────────────────────────
    const sseUrl = '{{ route("sse.antrian") }}' + (vendorId ? '?vendor=' + vendorId : '');
    source = new EventSource(sseUrl);

    source.addEventListener('antrian-update', function(e) {
        const data = JSON.parse(e.data);
        renderAdmin(data);
        document.getElementById('sseStatus').textContent = 'Live: ' + (data.updated_at || '—');
        document.getElementById('sseIndicator').style.background = '#27ae60';
    });

    source.onerror = function() {
        document.getElementById('sseStatus').textContent = 'Terputus, reconnecting...';
        document.getElementById('sseIndicator').style.background = '#e74c3c';
    };

    source.onopen = function() {
        document.getElementById('sseStatus').textContent = 'Terhubung (Live)';
    };
}


// ── Aksi Tombol via Form Submit ───────────────────────────────
// Pakai form submit (bukan fetch) agar halaman reload dan data
// langsung fresh dari DB — lebih reliable daripada tunggu SSE
function aksi(path) {
    if (source) source.close(); // Tutup SSE dulu sebelum navigasi

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = BASE_URL + '/' + path;

    const csrfInput = document.createElement('input');
    csrfInput.type  = 'hidden';
    csrfInput.name  = '_token';
    csrfInput.value = CSRF;
    form.appendChild(csrfInput);

    // Pertahankan vendor yang sedang aktif setelah redirect
    const vendorInput = document.createElement('input');
    vendorInput.type  = 'hidden';
    vendorInput.name  = '_vendor_redirect';
    vendorInput.value = currentVendorId || '';
    form.appendChild(vendorInput);

    document.body.appendChild(form);
    form.submit();
}

function pad(n) { return String(n).padStart(3, '0'); }

// ── Render Ulang Semua Section via Data SSE ───────────────────
function renderAdmin(data) {
    const menunggu  = data.menunggu  || [];
    const terlewat  = data.terlewat  || [];
    const dipanggil = data.dipanggil;

    // ── Kolom Kiri: Menunggu ──
    document.getElementById('jumlahMenunggu').textContent = menunggu.length;
    document.getElementById('listMenunggu').innerHTML = menunggu.length === 0
        ? '<div class="empty-state">Tidak ada antrian menunggu</div>'
        : menunggu.map(item => `
            <div class="antrian-item">
                <div class="nomor-chip">${pad(item.nomor)}</div>
                <div class="nama-item">${item.nama}</div>
                <div class="aksi-btn">
                    <button class="btn-xs btn-panggil" onclick="aksi('${item.id}/panggil')" title="Panggil">📢</button>
                    <button class="btn-xs btn-terlewat" onclick="aksi('${item.id}/terlewat')" title="Terlewat">⏭</button>
                </div>
            </div>`).join('');

    // ── Kolom Tengah: Dipanggil ──
    const panel = document.getElementById('panelDipanggil');
    if (dipanggil) {
        panel.innerHTML = `
            <div class="label">Sedang Dipanggil</div>
            <div class="nomor-besar">${pad(dipanggil.nomor)}</div>
            <div class="nama-dipanggil">${dipanggil.nama}</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:center;margin-top:12px;">
                <button class="btn-xs btn-selesai" style="padding:6px 16px;" onclick="aksi('${dipanggil.id}/selesai')">✔ Selesai</button>
                <button class="btn-xs btn-ulangi"  style="padding:6px 16px;" onclick="aksi('${dipanggil.id}/ulangi')">🔁 Ulangi</button>
                <button class="btn-xs btn-terlewat" style="padding:6px 16px;" onclick="aksi('${dipanggil.id}/terlewat')">⏭ Terlewat</button>
            </div>`;
    } else {
        panel.innerHTML = `
            <div class="kosong">🔔</div>
            <div style="opacity:0.6;margin-top:12px;">Belum ada yang dipanggil</div>`;
    }

    // ── Tombol Panggil Berikutnya ──
    const btn = document.getElementById('btnBerikutnya');
    if (btn) {
        if (menunggu.length > 0) {
            const first = menunggu[0];
            btn.disabled = false;
            btn.textContent = '📢 Panggil Berikutnya (No. ' + pad(first.nomor) + ')';
            btn.onclick = () => aksi(first.id + '/panggil');
            btn.style.background = '';
            btn.style.color = '';
        } else {
            btn.disabled = true;
            btn.textContent = 'Tidak ada antrian';
            btn.onclick = null;
            btn.style.background = '#eee';
            btn.style.color = '#aaa';
        }
    }

    // ── Kolom Kanan: Terlewat ──
    document.getElementById('jumlahTerlewat').textContent = terlewat.length;
    document.getElementById('listTerlewat').innerHTML = terlewat.length === 0
        ? '<div class="empty-state">Tidak ada yang terlewat</div>'
        : terlewat.map(item => `
            <div class="antrian-item terlewat-item">
                <div class="nomor-chip">${pad(item.nomor)}</div>
                <div class="nama-item">${item.nama}</div>
                <div class="aksi-btn">
                    <button class="btn-xs btn-panggil-ulang" onclick="aksi('${item.id}/panggil-terlewat')" title="Panggil Ulang">🔄</button>
                    <button class="btn-xs btn-selesai"       onclick="aksi('${item.id}/selesai')" title="Selesai">✔</button>
                </div>
            </div>`).join('');
}

// Inisialisasi awal saat halaman diload
if (currentVendorId) {
    switchVendor(currentVendorId);
}

// Pastikan SSE ditutup saat pindah halaman (misal klik menu sidebar)
window.addEventListener('beforeunload', function() {
    if (source) source.close();
});

// Interceptor: tutup SSE saat klik link biasa (bukan _blank)
document.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', function() {
        // Jangan intercept link yang buka tab baru (target="_blank")
        if (source && this.target !== '_blank' && !this.href.startsWith('javascript:')) {
            source.close();
        }
    });
});
</script>
@endpush
