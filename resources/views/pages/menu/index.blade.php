@extends('layouts.main')

@section('title', 'Kelola Menu Makanan - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-success text-white mr-2">
            <i class="mdi mdi-food"></i>
        </span> Kelola Menu Makanan ({{ $vendor->nama_vendor }})
    </h3>
    <nav>
        <a href="{{ route('menu.create') }}" class="btn btn-gradient-success btn-sm">
            <i class="mdi mdi-plus"></i> Tambah Menu
        </a>
    </nav>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Menu Tersedia</h4>

                @if($menus->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="mdi mdi-food-off mdi-48px"></i>
                        <p>Toko Anda belum memiliki menu makanan. Klik "Tambah Menu" untuk mulai.</p>
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Menu</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($menus as $i => $m)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="font-weight-bold">{{ $m->nama_menu }}</td>
                                <td class="text-success font-weight-bold">
                                    Rp {{ number_format($m->harga, 0, ',', '.') }}
                                </td>
                                <td>
                                    <a href="{{ route('menu.edit', $m->idmenu) }}"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </a>
                                    
                                    <form action="{{ route('menu.destroy', $m->idmenu) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus menu {{ $m->nama_menu }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="mdi mdi-delete"></i> Hapus
                                        </button>
                                    </form>
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
