@extends('layouts.main')

@section('title', isset($vendor) ? 'Edit Vendor - PURPLEBOOK' : 'Tambah Vendor - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-store"></i>
        </span>
        {{ isset($vendor) ? 'Edit Vendor' : 'Tambah Vendor Baru' }}
    </h3>
    <nav>
        <a href="{{ route('vendor.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-arrow-left"></i> Kembali
        </a>
    </nav>
</div>

<div class="row">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    {{ isset($vendor) ? 'Ubah Data Vendor' : 'Form Tambah Vendor' }}
                </h4>

                @if(isset($vendor))
                    <form action="{{ route('vendor.update', $vendor->idvendor) }}" method="POST">
                        @method('PUT')
                @else
                    <form action="{{ route('vendor.store') }}" method="POST">
                @endif
                @csrf

                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="mdi mdi-store text-primary"></i> Nama Vendor / Toko
                        </label>
                        <input type="text"
                               class="form-control @error('nama_vendor') is-invalid @enderror"
                               name="nama_vendor"
                               value="{{ old('nama_vendor', $vendor->nama_vendor ?? '') }}"
                               placeholder="Contoh: Warung Bu Sari"
                               required>
                        @error('nama_vendor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="mdi mdi-account text-primary"></i> Assign Akun Vendor
                        </label>
                        <select name="user_id" class="form-control @error('user_id') is-invalid @enderror">
                            <option value="">-- Belum ada akun --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}"
                                    {{ old('user_id', $vendor->user_id ?? '') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Hanya user dengan role <strong>vendor</strong> yang muncul di sini.
                            Jika kosong, ubah role user terlebih dahulu.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-gradient-primary mr-2">
                        <i class="mdi mdi-content-save"></i>
                        {{ isset($vendor) ? 'Simpan Perubahan' : 'Tambah Vendor' }}
                    </button>
                    <a href="{{ route('vendor.index') }}" class="btn btn-outline-secondary">Batal</a>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection
