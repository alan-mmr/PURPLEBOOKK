@extends('layouts.main')

@section('title', 'Dashboard - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-home"></i>
        </span> Dashboard
    </h3>
</div>

<div class="row">
    <div class="col-md-4 stretch-card grid-margin">
        <div class="card bg-gradient-danger card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                <h4 class="font-weight-normal mb-3">Total Buku <i class="mdi mdi-book-open-variant mdi-24px float-right"></i>
                </h4>
                <h2 class="mb-5">{{ $totalBuku }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 stretch-card grid-margin">
        <div class="card bg-gradient-info card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                <h4 class="font-weight-normal mb-3">Total Kategori <i class="mdi mdi-label mdi-24px float-right"></i>
                </h4>
                <h2 class="mb-5">{{ $totalKategori }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 stretch-card grid-margin">
        <div class="card bg-gradient-success card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                <h4 class="font-weight-normal mb-3">Welcome <i class="mdi mdi-account mdi-24px float-right"></i>
                </h4>
                <h2 class="mb-5">{{ Auth::user()->name }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">PURPLEBOOK - Book Collection Management</h4>
                <p>Sistem manajemen koleksi buku berbasis Laravel 11 dengan PostgreSQL database.</p>
                <ul>
                    <li><strong>Kelola Kategori:</strong> Buat, edit, dan hapus kategori buku</li>
                    <li><strong>Kelola Buku:</strong> CRUD lengkap untuk data buku dengan relasi kategori</li>
                    <li><strong>Dashboard:</strong> Statistik real-time koleksi buku Anda</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
