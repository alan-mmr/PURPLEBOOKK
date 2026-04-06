@extends('layouts.main')

@section('title', isset($menu) ? 'Edit Menu - PURPLEBOOK' : 'Tambah Menu - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-success text-white mr-2">
            <i class="mdi mdi-food"></i>
        </span>
        {{ isset($menu) ? 'Edit Menu Makanan' : 'Tambah Menu Baru' }}
    </h3>
    <nav>
        <a href="{{ route('menu.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-arrow-left"></i> Kembali
        </a>
    </nav>
</div>

<div class="row">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    {{ isset($menu) ? 'Form Ubah Data Menu' : 'Form Menu Toko' }} ({{ $vendor->nama_vendor }})
                </h4>

                @if(isset($menu))
                    <form action="{{ route('menu.update', $menu->idmenu) }}" method="POST">
                        @method('PUT')
                @else
                    <form action="{{ route('menu.store') }}" method="POST">
                @endif
                @csrf

                    <div class="form-group">
                        <label class="font-weight-bold">Nama Makanan / Minuman</label>
                        <input type="text" class="form-control @error('nama_menu') is-invalid @enderror"
                               name="nama_menu" value="{{ old('nama_menu', $menu->nama_menu ?? '') }}" 
                               placeholder="Contoh: Nasi Goreng Spesial" required>
                        @error('nama_menu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Harga Satuan (Rp)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent border-right-0">Rp</span>
                            </div>
                            <input type="number" class="form-control border-left-0 @error('harga') is-invalid @enderror"
                                   name="harga" value="{{ old('harga', $menu->harga ?? '') }}" 
                                   placeholder="Contoh: 15000" min="0" required>
                        </div>
                        @error('harga') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-gradient-success mr-2">
                        <i class="mdi mdi-content-save"></i>
                        {{ isset($menu) ? 'Simpan Perubahan' : 'Tambah Menu' }}
                    </button>
                    <a href="{{ route('menu.index') }}" class="btn btn-outline-secondary">Batal</a>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection
