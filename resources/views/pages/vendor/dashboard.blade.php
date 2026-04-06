@extends('layouts.main')

@section('title', 'Dashboard Vendor - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-success text-white mr-2">
            <i class="mdi mdi-store"></i>
        </span> Dashboard Vendor — {{ $vendor->nama_vendor }}
    </h3>
</div>

<div class="row">
    <div class="col-md-3 stretch-card grid-margin">
        <div class="card bg-gradient-success card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="">
                <h4 class="font-weight-normal mb-3">Pesanan Dibayar
                    <i class="mdi mdi-check-circle mdi-24px float-right"></i>
                </h4>
                <h2 class="mb-5">{{ $pesanans->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 stretch-card grid-margin">
        <div class="card bg-gradient-info card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="">
                <h4 class="font-weight-normal mb-3">Total Pendapatan
                    <i class="mdi mdi-currency-usd mdi-24px float-right"></i>
                </h4>
                <h4 class="mb-5">Rp {{ number_format($pesanans->sum('total_harga'), 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    <i class="mdi mdi-clipboard-check text-success"></i> Daftar Pesanan Lunas
                </h4>

                @if($pesanans->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="mdi mdi-inbox mdi-48px"></i>
                        <p class="mt-2">Belum ada pesanan yang terbayar.</p>
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Tamu</th>
                                <th>Menu Dipesan</th>
                                <th>Metode Bayar</th>
                                <th class="text-right">Total</th>
                                <th>Waktu Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pesanans as $i => $p)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <span class="badge badge-success">{{ $p->nama_pemesan }}</span>
                                </td>
                                <td>
                                    @foreach($p->detailPesanans as $d)
                                        <span class="badge badge-light border">
                                            {{ $d->menu->nama_menu }} ×{{ $d->jumlah }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($p->payment_type)
                                        <span class="badge badge-info">
                                            {{ strtoupper(str_replace('_', ' ', $p->payment_type)) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-right font-weight-bold">
                                    Rp {{ number_format($p->total_harga, 0, ',', '.') }}
                                </td>
                                <td>
                                    {{ $p->paid_at ? $p->paid_at->format('d M Y, H:i') : '-' }}
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
@endsection
