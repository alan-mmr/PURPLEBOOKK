@extends('layouts.main')

@section('title', 'Data Customer - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-account-box"></i>
        </span> Data Customer
    </h3>
    <a href="{{ route('customer.create') }}" class="btn btn-gradient-success btn-fw">
        <i class="mdi mdi-plus"></i> Tambah Customer
    </a>
</div>

{{-- Flash Messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Customer Terdaftar</h4>
                <div class="table-responsive">
                    <table class="table table-hover" id="tableCustomer">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Foto</th>
                                <th>Nama Customer</th>
                                <th>Mode Simpan</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $i => $c)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    @if($c->foto_blob)
                                        <img src="{{ route('customer.photo', $c->idcustomer) }}"
                                             alt="{{ $c->nama }}"
                                             style="width:50px; height:50px; object-fit:cover; border-radius:50%; border:2px solid #7B2D8B;">
                                    @elseif($c->foto_path)
                                        <img src="{{ asset('storage/' . $c->foto_path) }}"
                                             alt="{{ $c->nama }}"
                                             style="width:50px; height:50px; object-fit:cover; border-radius:50%; border:2px solid #7B2D8B;">
                                    @else
                                        <div style="width:50px; height:50px; border-radius:50%; background:#e0e0e0; display:flex; align-items:center; justify-content:center;">
                                            <i class="mdi mdi-account" style="font-size:24px; color:#999;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="font-weight-bold">{{ $c->nama }}</td>
                                <td>
                                    @if($c->foto_blob)
                                        <span class="badge badge-info">Blob (DB)</span>
                                    @elseif($c->foto_path)
                                        <span class="badge badge-success">File (Disk)</span>
                                    @else
                                        <span class="badge badge-secondary">Tanpa Foto</span>
                                    @endif
                                </td>
                                <td>{{ $c->created_at ? $c->created_at->format('d M Y, H:i') : '-' }}</td>
                                <td>
                                    <a href="{{ route('customer.edit', $c->idcustomer) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('customer.destroy', $c->idcustomer) }}" method="POST"
                                          style="display:inline-block;"
                                          onsubmit="return confirm('Yakin hapus customer {{ $c->nama }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="mdi mdi-delete"></i> Hapus
                                        </button>
                                    </form>
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

@push('scripts')
<script>
$(document).ready(function () {
    $('#tableCustomer').DataTable({
        order: [[4, 'desc']],
        columnDefs: [
            { orderable: false, targets: [1, 5] }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ customer",
            emptyTable: "Belum ada data customer.",
            zeroRecords: "Tidak ditemukan customer yang cocok.",
            paginate: { previous: "‹", next: "›" }
        }
    });
});
</script>
@endpush
