@extends('layouts.main')

@section('title', 'Buku - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-book-open-variant"></i>
        </span> Koleksi Buku
    </h3>
    <nav aria-label="breadcrumb">
        <a href="{{ route('buku.create') }}" class="btn btn-gradient-primary btn-sm">
            <i class="mdi mdi-plus"></i> Tambah Buku
        </a>
    </nav>
</div>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Buku</h4>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Judul</th>
                                <th>Pengarang</th>
                                <th>Kategori</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($buku as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><span class="badge badge-warning">{{ $item->kode }}</span></td>
                                <td><strong>{{ $item->judul }}</strong></td>
                                <td>{{ $item->pengarang }}</td>
                                <td><span class="badge badge-info">{{ $item->kategori->nama_kategori }}</span></td>
                                <td>
                                    <a href="{{ route('buku.edit', $item->idbuku) }}" class="btn btn-sm btn-gradient-info">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('buku.destroy', $item->idbuku) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-gradient-danger">
                                            <i class="mdi mdi-delete"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data buku.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
