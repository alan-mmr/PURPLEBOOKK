@extends('layouts.main')

@section('title', 'Kelola Vendor - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-store"></i>
        </span> Kelola Vendor
    </h3>
    <nav>
        <a href="{{ route('vendor.create') }}" class="btn btn-gradient-primary btn-sm">
            <i class="mdi mdi-plus"></i> Tambah Vendor
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
                <h4 class="card-title">Daftar Vendor Terdaftar</h4>

                @if($vendors->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="mdi mdi-store-off mdi-48px"></i>
                        <p>Belum ada vendor. Klik "Tambah Vendor" untuk mulai.</p>
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Vendor</th>
                                <th>Akun Vendor</th>
                                <th>Total Menu</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendors as $i => $v)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="font-weight-bold">{{ $v->nama_vendor }}</td>
                                <td>
                                    @if($v->user)
                                        <span class="badge badge-success">
                                            <i class="mdi mdi-account"></i> {{ $v->user->name }}
                                        </span>
                                    @else
                                        <span class="badge badge-warning">Belum ada akun</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $v->menus->count() }} menu</span>
                                </td>
                                <td>
                                    <a href="{{ route('vendor.edit', $v->idvendor) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('vendor.destroy', $v->idvendor) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus vendor {{ $v->nama_vendor }}?')">
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
