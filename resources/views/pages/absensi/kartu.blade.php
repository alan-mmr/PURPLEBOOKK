@extends('layouts.main')

@section('title', 'Kelola Kartu NFC - PURPLEBOOK')

@push('styles')
<style>
    .kartu-badge-nfc { background:#a855c7; color:white; padding:3px 10px; border-radius:12px; font-size:0.75rem; }
    .kartu-badge-qr  { background:#3b82f6; color:white; padding:3px 10px; border-radius:12px; font-size:0.75rem; }
    .serial-code {
        font-family: monospace;
        font-size: 0.82rem;
        background: #f3e8ff;
        color: #7B2D8B;
        padding: 2px 8px;
        border-radius: 6px;
    }
    #modalNfc {
        display: none;
        position: fixed;
        top:0; left:0; right:0; bottom:0;
        z-index: 9999;
        background: rgba(0,0,0,0.78);
        align-items: center;
        justify-content: center;
    }
    #modalNfc.show { display: flex; }
    .modal-nfc-box {
        background: linear-gradient(135deg, #1a0a2e 0%, #2d1454 100%);
        border-radius: 24px;
        padding: 36px 28px;
        max-width: 360px;
        width: 90%;
        color: white;
        text-align: center;
        position: relative;
    }
    .modal-nfc-close {
        position: absolute; top: 12px; right: 16px;
        background: none; border: none;
        color: rgba(255,255,255,0.5);
        font-size: 1.4rem; cursor: pointer;
    }
    .modal-nfc-status {
        background: rgba(255,255,255,0.08);
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 0.85rem;
        color: rgba(255,255,255,0.85);
        min-height: 48px;
    }
    .btn-nfc-activate {
        background: linear-gradient(135deg, #7B2D8B, #a855c7);
        color: white; border: none;
        padding: 16px 32px; border-radius: 50px;
        font-size: 1rem; font-weight: 700;
        cursor: pointer; width: 100%; margin-bottom: 10px;
    }
    .btn-nfc-activate:disabled { opacity: 0.6; cursor: not-allowed; }
    .btn-nfc-cancel {
        background: none;
        border: 1px solid rgba(255,255,255,0.2);
        color: rgba(255,255,255,0.6);
        padding: 8px 24px; border-radius: 50px;
        cursor: pointer; font-size: 0.85rem; width: 100%;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <h4 style="font-weight:700; color:#7B2D8B;">
            <i class="mdi mdi-credit-card-settings"></i> Kelola Kartu NFC
        </h4>
        <p class="text-muted mb-0">Daftarkan kartu NFC atau token QR ke akun pengguna.</p>
    </div>

    @if(session('success'))
    <div class="col-12">
        <div class="alert alert-success alert-dismissible fade show">
            <i class="mdi mdi-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    </div>
    @endif

    {{-- Form Daftar Kartu Baru --}}
    <div class="col-lg-5 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="font-weight-bold mb-3" style="color:#7B2D8B;">
                    <i class="mdi mdi-plus-circle"></i> Daftarkan Kartu Baru
                </h5>
                <form action="{{ route('absensi.kartu.simpan') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-bold">Pemilik <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-control" required>
                            <option value="">-- Pilih User --</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->role }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Tipe <span class="text-danger">*</span></label>
                        <select name="tipe" class="form-control" id="tipeSelect" onchange="onTipeChange(this.value)" required>
                            <option value="nfc">NFC (Kartu Fisik)</option>
                            <option value="qr">QR (Token Digital)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Serial Number / Token <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="serial_number" id="inputSerial"
                                class="form-control" placeholder="04:AB:CD:EF atau klik Scan NFC"
                                value="{{ old('serial_number') }}" required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary"
                                    onclick="bukaModalNfc()" id="btnScanInput">
                                    <i class="mdi mdi-credit-card-wireless"></i> Scan NFC
                                </button>
                            </div>
                        </div>
                        <small class="text-muted" id="serialHint">
                            Klik "Scan NFC" lalu tempelkan kartu ke HP, atau isi manual.
                        </small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Label / Keterangan</label>
                        <input type="text" name="label" class="form-control"
                            placeholder="misal: KTM, Kartu Biru, ID Card"
                            value="{{ old('label') }}">
                    </div>
                    <button type="submit" class="btn btn-gradient-primary btn-block">
                        <i class="mdi mdi-content-save"></i> Simpan Kartu
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Daftar Kartu Terdaftar --}}
    <div class="col-lg-7 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="font-weight-bold mb-3" style="color:#7B2D8B;">
                    <i class="mdi mdi-format-list-bulleted"></i> Kartu Terdaftar
                    <span class="badge badge-pill" style="background:#7B2D8B; color:white;">{{ $kartus->count() }}</span>
                </h5>
                @if($kartus->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="mdi mdi-credit-card-off-outline" style="font-size:2.5rem;"></i>
                        <p class="mt-2">Belum ada kartu terdaftar.</p>
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Pemilik</th><th>Kartu</th><th>Serial</th><th>Status</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kartus as $k)
                            <tr>
                                <td>
                                    <strong>{{ $k->user->name ?? '?' }}</strong><br>
                                    <small class="text-muted">{{ $k->user->role ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="kartu-badge-{{ $k->tipe }}">{{ strtoupper($k->tipe) }}</span><br>
                                    <small class="text-muted">{{ $k->label }}</small>
                                </td>
                                <td><span class="serial-code">{{ Str::limit($k->serial_number, 20) }}</span></td>
                                <td>
                                    @if($k->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex" style="gap:4px;">
                                        @if($k->tipe === 'qr')
                                        <a href="{{ route('absensi.qr', $k->user_id) }}"
                                           class="btn btn-xs btn-outline-primary" target="_blank">
                                            <i class="mdi mdi-qrcode"></i>
                                        </a>
                                        @endif
                                        <form action="{{ route('absensi.kartu.hapus', $k->id) }}" method="POST"
                                              onsubmit="return confirm('Hapus kartu ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal NFC --}}
<div id="modalNfc">
    <div class="modal-nfc-box">
        <button class="modal-nfc-close" onclick="tutupModalNfc()" type="button">&times;</button>
        <div style="font-size:3rem; margin-bottom:8px;">&#128225;</div>
        <h5 style="font-weight:700; margin-bottom:6px;">Scan Kartu NFC</h5>
        <p style="color:rgba(255,255,255,0.65); font-size:0.85rem; margin-bottom:20px;">
            Tekan tombol di bawah, izinkan akses NFC saat popup muncul, lalu tempelkan kartu ke belakang HP.
        </p>
        <div id="nfcModalStatus" class="modal-nfc-status">Belum aktif. Tekan tombol di bawah.</div>
        <button id="btnAktifkanNfc" class="btn-nfc-activate" onclick="aktifkanNfc()" type="button">
            <i class="mdi mdi-credit-card-wireless"></i> Aktifkan NFC Sekarang
        </button>
        <button class="btn-nfc-cancel" onclick="tutupModalNfc()" type="button">Batal / Input Manual</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
var nfcReaderInput = null;
var nfcScanning = false;

function onTipeChange(val) {
    var hint = document.getElementById('serialHint');
    var btn = document.getElementById('btnScanInput');
    if (val === 'nfc') {
        hint.textContent = 'Klik "Scan NFC" lalu tempelkan kartu ke HP, atau isi manual.';
        btn.style.display = '';
    } else {
        hint.textContent = 'Token QR akan di-generate otomatis saat pertama kali scan QR user.';
        document.getElementById('inputSerial').value = 'QR-' + Math.random().toString(36).substr(2,12).toUpperCase();
        btn.style.display = 'none';
    }
}

function bukaModalNfc() {
    if (!('NDEFReader' in window)) {
        alert('Web NFC tidak tersedia.\n\nPastikan:\n1. Buka via Chrome Android\n2. Akses via HTTPS (URL ngrok)\n3. NFC di HP sudah aktif');
        return;
    }
    document.getElementById('nfcModalStatus').textContent = 'Belum aktif. Tekan tombol di bawah.';
    document.getElementById('nfcModalStatus').style.background = 'rgba(255,255,255,0.08)';
    document.getElementById('btnAktifkanNfc').disabled = false;
    document.getElementById('btnAktifkanNfc').innerHTML = '<i class="mdi mdi-credit-card-wireless"></i> Aktifkan NFC Sekarang';
    document.getElementById('modalNfc').classList.add('show');
}

function tutupModalNfc() {
    nfcReaderInput = null;
    nfcScanning = false;
    document.getElementById('modalNfc').classList.remove('show');
}

async function aktifkanNfc() {
    var statusEl = document.getElementById('nfcModalStatus');
    var btnEl = document.getElementById('btnAktifkanNfc');

    btnEl.disabled = true;
    btnEl.innerHTML = 'Meminta izin NFC...';
    statusEl.textContent = 'Meminta izin NFC dari browser...';
    statusEl.style.background = 'rgba(255,255,255,0.08)';

    try {
        nfcReaderInput = new NDEFReader();
        await nfcReaderInput.scan();

        nfcScanning = true;
        statusEl.style.background = 'rgba(34,197,94,0.2)';
        statusEl.textContent = 'NFC aktif! Dekatkan kartu ke bagian belakang HP...';
        btnEl.innerHTML = 'Menunggu kartu...';

        nfcReaderInput.addEventListener('reading', function(event) {
            var serial = event.serialNumber;
            document.getElementById('inputSerial').value = serial;
            statusEl.style.background = 'rgba(34,197,94,0.35)';
            statusEl.textContent = 'Terbaca: ' + serial;
            btnEl.innerHTML = 'Serial Terbaca!';
            nfcScanning = false;
            setTimeout(function() { tutupModalNfc(); }, 1500);
        });

        nfcReaderInput.addEventListener('readingerror', function() {
            statusEl.style.background = 'rgba(239,68,68,0.2)';
            statusEl.textContent = 'Gagal membaca kartu. Coba dekatkan lagi.';
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="mdi mdi-refresh"></i> Coba Lagi';
        });

    } catch(err) {
        nfcScanning = false;
        btnEl.disabled = false;
        var pesan = err.message;
        if (err.name === 'NotAllowedError')  pesan = 'Izin NFC ditolak! Klik Allow saat popup muncul, lalu coba lagi.';
        if (err.name === 'NotSupportedError') pesan = 'HP ini tidak memiliki NFC.';
        if (err.name === 'NotReadableError')  pesan = 'NFC dipakai aplikasi lain. Tutup dulu lalu coba lagi.';
        statusEl.style.background = 'rgba(239,68,68,0.2)';
        statusEl.textContent = pesan;
        btnEl.innerHTML = '<i class="mdi mdi-refresh"></i> Coba Lagi';
    }
}
</script>
@endpush