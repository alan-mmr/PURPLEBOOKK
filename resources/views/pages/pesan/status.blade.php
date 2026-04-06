@extends('layouts.main')

@section('title', 'Status Pembayaran - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-info text-white mr-2">
            <i class="mdi mdi-information-outline"></i>
        </span> Status Pembayaran
    </h3>
</div>

<div class="row justify-content-center">
    <div class="col-md-7 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                {{-- ── Badge Status ── --}}
                <div class="text-center mb-4">
                    @if($pesanan->status_bayar === 'paid')
                        <i class="mdi mdi-check-circle mdi-48px text-success"></i>
                        <h4 class="text-success font-weight-bold mt-2">Pembayaran Berhasil!</h4>
                    @elseif($pesanan->status_bayar === 'pending')
                        <i class="mdi mdi-clock-outline mdi-48px text-warning"></i>
                        <h4 class="text-warning font-weight-bold mt-2">Menunggu Pembayaran</h4>
                        <p class="text-muted">Selesaikan pembayaran sebelum waktu habis.</p>
                    @elseif($pesanan->status_bayar === 'failed')
                        <i class="mdi mdi-close-circle mdi-48px text-danger"></i>
                        <h4 class="text-danger font-weight-bold mt-2">Pembayaran Gagal</h4>
                    @elseif($pesanan->status_bayar === 'expired')
                        <i class="mdi mdi-timer-off mdi-48px text-secondary"></i>
                        <h4 class="text-secondary font-weight-bold mt-2">Pembayaran Kedaluwarsa</h4>
                    @endif
                </div>

                {{-- ── Info Pesanan ── --}}
                <table class="table table-borderless mb-3">
                    <tr>
                        <td class="text-muted">Nama Pemesan</td>
                        <td class="font-weight-bold">{{ $pesanan->nama_pemesan }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Vendor</td>
                        <td>{{ $pesanan->vendor->nama_vendor }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">ID Transaksi</td>
                        <td><code>{{ $pesanan->transaction_id }}</code></td>
                    </tr>
                    @if($pesanan->payment_type)
                    <tr>
                        <td class="text-muted">Metode</td>
                        <td>{{ strtoupper(str_replace('_', ' ', $pesanan->payment_type)) }}</td>
                    </tr>
                    @endif
                    @if($pesanan->paid_at)
                    <tr>
                        <td class="text-muted">Waktu Bayar</td>
                        <td>{{ $pesanan->paid_at->format('d M Y, H:i') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Total</td>
                        <td class="font-weight-bold text-primary">
                            Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>

                {{-- ── Detail Item ── --}}
                <h6 class="font-weight-bold mb-2">Detail Pesanan</h6>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Menu</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pesanan->detailPesanans as $d)
                        <tr>
                            <td>{{ $d->menu->nama_menu }}</td>
                            <td class="text-center">{{ $d->jumlah }}</td>
                            <td class="text-right">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- ── Actions ── --}}
                <div class="text-center mt-4">
                    @if($pesanan->status_bayar === 'pending')
                        <a href="{{ route('pesan.bayar', $pesanan->idpesanan) }}"
                           class="btn btn-gradient-success btn-block mb-2">
                            <i class="mdi mdi-credit-card"></i> Lanjutkan Pembayaran
                        </a>
                    @endif
                    <a href="{{ route('pesan.index') }}" class="btn btn-outline-primary btn-block">
                        <i class="mdi mdi-cart-plus"></i> Pesan Lagi
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
