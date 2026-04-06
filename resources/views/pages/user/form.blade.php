@extends('layouts.main')

@section('title', isset($user) ? 'Edit Akun - PURPLEBOOK' : 'Tambah Akun - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-success text-white mr-2">
            <i class="mdi mdi-account-card-details"></i>
        </span>
        {{ isset($user) ? 'Edit Akun Pengguna' : 'Tambah Akun Baru' }}
    </h3>
    <nav>
        <a href="{{ route('user.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-arrow-left"></i> Kembali
        </a>
    </nav>
</div>

<div class="row">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    {{ isset($user) ? 'Form Ubah Data Akun' : 'Form Registrasi Akun' }}
                </h4>

                @if(isset($user))
                    <form action="{{ route('user.update', $user->id) }}" method="POST">
                        @method('PUT')
                @else
                    <form action="{{ route('user.store') }}" method="POST">
                @endif
                @csrf

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Nama Lengkap</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name', $user->name ?? '') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email', $user->email ?? '') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">
                                Password {{ isset($user) ? '(Kosongkan jika tidak diubah)' : '' }}
                            </label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" {{ isset($user) ? '' : 'required' }}>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Konfirmasi Password</label>
                            <input type="password" class="form-control" name="password_confirmation" {{ isset($user) ? '' : 'required' }}>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-12">
                            <label class="font-weight-bold">Akses Role</label>
                            @php $role = old('role', $user->role ?? 'user'); @endphp
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-check-primary">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="role" value="user" {{ $role == 'user' ? 'checked' : '' }}> User Biasa
                                <i class="input-helper"></i></label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-check-success">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="role" value="vendor" {{ $role == 'vendor' ? 'checked' : '' }}> Vendor (Toko)
                                <i class="input-helper"></i></label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-check-danger">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="role" value="admin" {{ $role == 'admin' ? 'checked' : '' }}> Administrator
                                <i class="input-helper"></i></label>
                            </div>
                        </div>
                        @error('role') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-gradient-success mr-2">
                        <i class="mdi mdi-content-save"></i>
                        {{ isset($user) ? 'Simpan Perubahan' : 'Buat Akun' }}
                    </button>
                    <a href="{{ route('user.index') }}" class="btn btn-outline-secondary">Batal</a>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection
