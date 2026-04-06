@extends('layouts.main')

@section('title', 'Kelola Akun Pengguna - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-success text-white mr-2">
            <i class="mdi mdi-account-multiple"></i>
        </span> Kelola Akun
    </h3>
    <nav>
        <a href="{{ route('user.create') }}" class="btn btn-gradient-success btn-sm">
            <i class="mdi mdi-account-plus"></i> Tambah Akun Baru
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
                <h4 class="card-title">Daftar Akun Sistem</h4>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Role Akses</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $i => $u)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="font-weight-bold">{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    @if($u->role === 'admin')
                                        <span class="badge badge-primary"><i class="mdi mdi-shield-account"></i> Admin</span>
                                    @elseif($u->role === 'vendor')
                                        <span class="badge badge-success"><i class="mdi mdi-store"></i> Vendor</span>
                                    @else
                                        <span class="badge badge-secondary"><i class="mdi mdi-account"></i> User Biasa</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('user.edit', $u->id) }}"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </a>
                                    
                                    @if($u->id !== auth()->id())
                                    <form action="{{ route('user.destroy', $u->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus akun {{ $u->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="mdi mdi-delete"></i> Hapus
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
