@extends('layouts.main')

@section('title', isset($buku) ? 'Edit Buku' : 'Tambah Buku')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-book-open-variant"></i>
        </span> {{ isset($buku) ? 'Edit' : 'Tambah' }} Buku
    </h3>
    <nav aria-label="breadcrumb">
        <a href="{{ route('buku.index') }}" class="btn btn-gradient-secondary btn-sm">
            <i class="mdi mdi-arrow-left"></i> Kembali
        </a>
    </nav>
</div>

<div class="row">
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Form Buku</h4>
                <form class="forms-sample" method="POST" action="{{ isset($buku) ? route('buku.update', $buku->idbuku) : route('buku.store') }}">
                    @csrf
                    @if(isset($buku))
                        @method('PUT')
                    @endif
                    
                    <div class="form-group">
                        <label for="kode">Kode Buku</label>
                        <input type="text" class="form-control @error('kode') is-invalid @enderror" 
                               id="kode" name="kode" 
                               value="{{ old('kode', $buku->kode ?? '') }}" 
                               placeholder="Contoh: NV-01" required>
                        @error('kode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="judul">Judul Buku</label>
                        <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                               id="judul" name="judul" 
                               value="{{ old('judul', $buku->judul ?? '') }}" 
                               placeholder="Masukkan judul buku" required>
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="pengarang">Pengarang</label>
                        <input type="text" class="form-control @error('pengarang') is-invalid @enderror" 
                               id="pengarang" name="pengarang" 
                               value="{{ old('pengarang', $buku->pengarang ?? '') }}" 
                               placeholder="Masukkan nama pengarang" required>
                        @error('pengarang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="idkategori">Kategori</label>
                        <select class="form-control @error('idkategori') is-invalid @enderror" 
                                id="idkategori" name="idkategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategori as $item)
                                <option value="{{ $item->idkategori }}" 
                                    {{ old('idkategori', $buku->idkategori ?? '') == $item->idkategori ? 'selected' : '' }}>
                                    {{ $item->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        @error('idkategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-gradient-primary mr-2">
                        <i class="mdi mdi-content-save"></i> Simpan
                    </button>
                    <a href="{{ route('buku.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
