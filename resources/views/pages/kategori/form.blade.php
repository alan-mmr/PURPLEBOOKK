@extends('layouts.main')

@section('title', isset($kategori) ? 'Edit Kategori' : 'Tambah Kategori')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-label"></i>
        </span> {{ isset($kategori) ? 'Edit' : 'Tambah' }} Kategori
    </h3>
    <nav aria-label="breadcrumb">
        <a href="{{ route('kategori.index') }}" class="btn btn-gradient-secondary btn-sm">
            <i class="mdi mdi-arrow-left"></i> Kembali
        </a>
    </nav>
</div>

<div class="row">
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Form Kategori</h4>
                <form id="formKategori" class="forms-sample" method="POST" action="{{ isset($kategori) ? route('kategori.update', $kategori->idkategori) : route('kategori.store') }}">
                    @csrf
                    @if(isset($kategori))
                        @method('PUT')
                    @endif
                    
                    <div class="form-group">
                        <label for="nama_kategori">Nama Kategori</label>
                        <input type="text" class="form-control @error('nama_kategori') is-invalid @enderror" 
                               id="nama_kategori" name="nama_kategori" 
                               value="{{ old('nama_kategori', $kategori->nama_kategori ?? '') }}" 
                               placeholder="Masukkan nama kategori" required>
                        @error('nama_kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                </form>
                <button type="button" id="btnSubmitKategori" class="btn btn-gradient-primary mr-2">
                    <i class="mdi mdi-content-save"></i> Simpan
                </button>
                <a href="{{ route('kategori.index') }}" class="btn btn-light">Batal</a>

                <script>
                document.getElementById('btnSubmitKategori').addEventListener('click', function () {
                    const form = document.getElementById('formKategori');
                    if (!form.checkValidity()) { form.reportValidity(); return; }
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-grow spinner-grow-sm mr-1"></span> Memproses...';
                    form.submit();
                });
                </script>
            </div>
        </div>
    </div>
</div>
@endsection
