@extends('layouts.main')

@section('title', isset($barang) ? 'Edit Barang - PURPLEBOOK' : 'Tambah Barang - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-tag-multiple"></i>
        </span>
        {{ isset($barang) ? 'Edit Barang' : 'Tambah Barang Baru' }}
    </h3>
    <nav aria-label="breadcrumb">
        <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-arrow-left"></i> Kembali
        </a>
    </nav>
</div>

<div class="row">
    <div class="col-md-8 col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    {{ isset($barang) ? 'Ubah Data Barang' : 'Form Tambah Barang' }}
                </h4>

                @if(isset($barang))
                    {{-- Mode EDIT --}}
                    <form id="formBarang" action="{{ route('barang.update', $barang->id_barang) }}" method="POST">
                        @method('PUT')
                @else
                    {{-- Mode CREATE --}}
                    <form id="formBarang" action="{{ route('barang.store') }}" method="POST">
                @endif
                @csrf

                    {{-- ID Barang (read-only saat edit, otomatis saat create) --}}
                    @if(isset($barang))
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="mdi mdi-barcode text-primary"></i>
                            ID Barang
                        </label>
                        <input type="text"
                            class="form-control bg-light"
                            value="{{ $barang->id_barang }}"
                            readonly>
                        <small class="text-muted">
                            <i class="mdi mdi-lock-outline"></i>
                            ID barang tidak dapat diubah — digenerate otomatis oleh sistem saat pertama dibuat.
                        </small>
                    </div>
                    @else
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="mdi mdi-barcode text-primary"></i>
                            ID Barang
                        </label>
                        <input type="text"
                            class="form-control bg-light text-muted"
                            value="(otomatis — format: YYMMDD##)"
                            readonly>
                        <small class="text-muted">
                            <i class="mdi mdi-information-outline"></i>
                            ID akan digenerate otomatis oleh trigger PostgreSQL menggunakan format tanggal + nomor urut harian.
                        </small>
                    </div>
                    @endif

                    {{-- Nama Barang --}}
                    <div class="form-group">
                        <label class="font-weight-bold" for="nama">
                            <i class="mdi mdi-package-variant text-primary"></i>
                            Nama Barang <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            name="nama"
                            id="nama"
                            class="form-control @error('nama') is-invalid @enderror"
                            value="{{ old('nama', $barang->nama ?? '') }}"
                            placeholder="Contoh: Novel Laskar Pelangi"
                            maxlength="50"
                            required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Maksimal 50 karakter</small>
                    </div>

                    {{-- Harga --}}
                    <div class="form-group">
                        <label class="font-weight-bold" for="harga">
                            <i class="mdi mdi-currency-usd text-primary"></i>
                            Harga (Rp.) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number"
                                name="harga"
                                id="harga"
                                class="form-control @error('harga') is-invalid @enderror"
                                value="{{ old('harga', $barang->harga ?? '') }}"
                                placeholder="Contoh: 85000"
                                min="0"
                                required>
                            @error('harga')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">Dalam Rupiah (integer, tanpa titik atau koma)</small>
                    </div>

                    {{-- Timestamp (read-only info saat edit) --}}
                    @if(isset($barang))
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="mdi mdi-clock-outline text-primary"></i>
                            Dibuat Pada
                        </label>
                        <input type="text"
                            class="form-control bg-light"
                            value="{{ \Carbon\Carbon::parse($barang->timestamp)->format('d M Y, H:i:s') }}"
                            readonly>
                    </div>
                    @endif

                    {{-- Tombol submit (di LUAR form, trigger via JS) --}}
                </form>
                <div class="mt-4">
                    <button type="button" id="btnSubmitBarang" class="btn btn-gradient-primary">
                        <i class="mdi mdi-content-save"></i>
                        {{ isset($barang) ? 'Simpan Perubahan' : 'Tambah Barang' }}
                    </button>
                    <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary ml-2">
                        <i class="mdi mdi-cancel"></i> Batal
                    </a>
                </div>

                <script>
                // SK1 — Spinner: cegah double submit, validasi required, lalu spinner
                document.getElementById('btnSubmitBarang').addEventListener('click', function () {
                    const form = document.getElementById('formBarang');
                    // Cek semua input required via HTML5
                    if (!form.checkValidity()) {
                        form.reportValidity(); // tampilkan popup ke field yang kosong
                        return;
                    }
                    // Semua valid → spinner + disabled (cegah double click)
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
