@extends('layouts.main')

@section('title', 'Pembayaran - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-success text-white mr-2">
            <i class="mdi mdi-credit-card"></i>
        </span> Pembayaran
    </h3>
</div>

<div class="row justify-content-center">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="card-title">Selesaikan Pembayaran</h4>

                <div class="mb-3">
                    <p class="text-muted mb-1">Tamu</p>
                    <h5 class="font-weight-bold">{{ $pesanan->nama_pemesan }}</h5>
                </div>

                <div class="mb-3">
                    <p class="text-muted mb-1">Vendor</p>
                    <h6>{{ $pesanan->vendor->nama_vendor }}</h6>
                </div>

                <div class="mb-4">
                    <p class="text-muted mb-1">Total Pembayaran</p>
                    <h3 class="text-primary font-weight-bold">
                        Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                    </h3>
                </div>

                <button id="btnBayar" class="btn btn-gradient-success btn-lg btn-block font-weight-bold">
                    <i class="mdi mdi-credit-card-check-outline"></i> Bayar Sekarang
                </button>

                <a href="{{ route('pesan.status', $pesanan->idpesanan) }}"
                   class="btn btn-outline-secondary btn-block mt-2">
                    Cek Status Pembayaran
                </a>

                <p class="text-muted small mt-3">
                    <i class="mdi mdi-lock"></i>
                    Pembayaran diproses dengan aman oleh Midtrans.
                    Mendukung Transfer Bank, GoPay, dan QRIS.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Snap.js dari Midtrans — URL otomatis menyesuaikan sandbox/production via env --}}
<script src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>
<script>
document.getElementById('btnBayar').addEventListener('click', function () {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

    snap.pay('{{ $pesanan->snap_token }}', {
        onSuccess: function(result) {
            // Redirect ke halaman status setelah bayar berhasil
            window.location.href = '{{ route("pesan.status", $pesanan->idpesanan) }}';
        },
        onPending: function(result) {
            window.location.href = '{{ route("pesan.status", $pesanan->idpesanan) }}';
        },
        onError: function(result) {
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-credit-card-check-outline"></i> Coba Lagi';
            alert('Pembayaran gagal. Silakan coba lagi.');
        },
        onClose: function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-credit-card-check-outline"></i> Bayar Sekarang';
        }
    });
});
</script>
@endpush
