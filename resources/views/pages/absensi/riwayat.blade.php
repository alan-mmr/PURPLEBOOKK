@extends('layouts.main')

@section('title', 'Riwayat Absensi — PURPLEBOOK')

@push('styles')
<style>
    .badge-nfc { background:#a855c7; color:white; padding:3px 10px; border-radius:12px; font-size:0.75rem; }
    .badge-qr  { background:#3b82f6; color:white; padding:3px 10px; border-radius:12px; font-size:0.75rem; }
    .filter-card { background:#f9f4ff; border:1px solid #e9d5ff; border-radius:12px; padding:16px 20px; }
    .stat-mini {
        background: linear-gradient(135deg, #7B2D8B, #a855c7);
        border-radius: 12px;
        padding: 14px 20px;
        color: white;
        text-align: center;
    }
    .stat-mini .num { font-size: 2rem; font-weight: 800; line-height: 1; }
    .stat-mini .lbl { font-size: 0.78rem; opacity: 0.85; margin-top: 4px; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <h4 style="font-weight:700; color:#7B2D8B;">
            <i class="mdi mdi-history"></i> Riwayat Absensi
        </h4>
    </div>

    @if(session('success'))
    <div class="col-12">
        <div class="alert alert-success alert-dismissible fade show">
            <i class="mdi mdi-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    </div>
    @endif

    {{-- ── Statistik Hari Ini ── --}}
    <div class="col-md-4 mb-3">
        <div class="stat-mini">
            <div class="num">{{ \App\Models\Absensi::hariIni()->count() }}</div>
            <div class="lbl">Total Hadir Hari Ini</div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-mini" style="background:linear-gradient(135deg,#2563eb,#3b82f6);">
            <div class="num">{{ \App\Models\Absensi::hariIni()->metode('qr')->count() }}</div>
            <div class="lbl">Via QR Code</div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-mini" style="background:linear-gradient(135deg,#059669,#10b981);">
            <div class="num">{{ \App\Models\Absensi::hariIni()->metode('nfc')->count() }}</div>
            <div class="lbl">Via NFC</div>
        </div>
    </div>

    {{-- ── Filter ── --}}
    <div class="col-12 mb-4">
        <div class="filter-card">
            <form method="GET" action="{{ route('absensi.riwayat') }}" class="form-inline" style="gap:10px; flex-wrap:wrap; display:flex;">
                <div class="form-group mr-2">
                    <label class="mr-1 font-weight-bold">Tanggal:</label>
                    <input type="date" name="tanggal" class="form-control form-control-sm"
                        value="{{ request('tanggal', today()->format('Y-m-d')) }}">
                </div>
                <div class="form-group mr-2">
                    <label class="mr-1 font-weight-bold">User:</label>
                    <select name="user_id" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label class="mr-1 font-weight-bold">Metode:</label>
                    <select name="metode" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        <option value="nfc" {{ request('metode') === 'nfc' ? 'selected' : '' }}>NFC</option>
                        <option value="qr"  {{ request('metode') === 'qr'  ? 'selected' : '' }}>QR Code</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-gradient-primary">
                    <i class="mdi mdi-filter"></i> Filter
                </button>
                <a href="{{ route('absensi.riwayat') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-refresh"></i> Reset
                </a>
            </form>
        </div>
    </div>

    {{-- ── Tabel Riwayat ── --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if($absensis->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="mdi mdi-calendar-blank-outline" style="font-size:3rem;"></i>
                        <p class="mt-2">Tidak ada data absensi untuk filter ini.</p>
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Waktu</th>
                                <th>Nama</th>
                                <th>Role</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Petugas</th>
                                @if(auth()->user()->role === 'admin')
                                <th style="width:60px;">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($absensis as $i => $a)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $a->created_at->format('H:i:s') }}</strong><br>
                                    <small class="text-muted">{{ $a->created_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $a->user->name ?? '?' }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $a->user->role ?? '—' }}</small>
                                </td>
                                <td>
                                    <span class="badge-{{ $a->metode }}">
                                        <i class="mdi mdi-{{ $a->metode === 'nfc' ? 'nfc-variant' : 'qrcode-scan' }}"></i>
                                        {{ strtoupper($a->metode) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $a->status === 'hadir' ? 'success' : 'warning' }}">
                                        {{ ucfirst($a->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $a->petugas->name ?? '—' }}</small>
                                </td>
                                @if(auth()->user()->role === 'admin')
                                <td>
                                    <form action="{{ route('absensi.hapus', $a->id) }}" method="POST"
                                          onsubmit="return confirm('Hapus record absensi ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Menampilkan {{ $absensis->count() }} data</small>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
