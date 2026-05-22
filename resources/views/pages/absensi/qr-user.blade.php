@extends('layouts.clean')

@section('title', 'QR Absensi — {{ $user->name }}')

@section('content')
<div style="min-height:100vh; background:linear-gradient(135deg,#1a0a2e,#2d1454,#3d1a6e);
            display:flex; align-items:center; justify-content:center; padding:20px;">
    <div style="background:white; border-radius:24px; padding:36px 32px;
                max-width:380px; width:100%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.4);">

        {{-- Header --}}
        <div style="margin-bottom:24px;">
            <div style="font-size:2rem; margin-bottom:8px;">🎫</div>
            <h2 style="font-weight:800; color:#7B2D8B; margin:0; font-size:1.4rem;">QR Absensi</h2>
            <p style="color:#666; font-size:0.85rem; margin:4px 0 0;">PURPLEBOOK Attendance System</p>
        </div>

        {{-- QR Code --}}
        <div style="padding:12px; background:#f9f4ff; border-radius:16px;
                    border:2px solid #e9d5ff; display:inline-block; margin-bottom:20px;">
            <img src="data:image/png;base64,{{ $qrBase64 }}"
                 alt="QR Code {{ $user->name }}"
                 style="width:220px; height:220px; display:block;">
        </div>

        {{-- Info User --}}
        <div style="background:#f9f4ff; border-radius:12px; padding:16px; margin-bottom:20px;">
            <div style="font-size:1.2rem; font-weight:800; color:#1a0a2e;">{{ $user->name }}</div>
            <div style="font-size:0.85rem; color:#7B2D8B; font-weight:600;">{{ ucfirst($user->role) }}</div>
            <div style="font-size:0.75rem; color:#999; margin-top:4px;">{{ $user->email }}</div>
        </div>

        {{-- Token info --}}
        <div style="font-size:0.7rem; color:#aaa; font-family:monospace; word-break:break-all; margin-bottom:16px;">
            {{ $kartu->serial_number }}
        </div>

        {{-- Instruksi --}}
        <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:10px;
                    padding:12px; font-size:0.8rem; color:#92400e; text-align:left;">
            <strong>📌 Cara Pakai:</strong><br>
            Tunjukkan QR ini ke petugas atau security saat hadir. Petugas akan scan menggunakan halaman Scanner Absensi.
        </div>

        {{-- Tombol Print --}}
        <button onclick="window.print()"
            style="margin-top:20px; background:linear-gradient(135deg,#7B2D8B,#a855c7);
                   color:white; border:none; padding:12px 28px; border-radius:50px;
                   font-weight:600; cursor:pointer; font-size:0.9rem; width:100%;">
            🖨️ Print / Simpan
        </button>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .card-print, .card-print * { visibility: visible; }
}
</style>
@endsection
