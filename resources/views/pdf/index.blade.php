@extends('layouts.main')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-file-pdf-box"></i>
        </span> Cetak Dokumen
    </h3>
    <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">
                <span></span>Overview <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"></i>
            </li>
        </ul>
    </nav>
</div>

<div class="row">
    {{-- Card Sertifikat --}}
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card bg-gradient-danger card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                <h4 class="font-weight-normal mb-3">Sertifikat Apresiasi <i class="mdi mdi-certificate mdi-24px float-right"></i>
                </h4>
                <p class="mb-4">Cetak sertifikat penghargaan untuk pembaca setia Purplebook dengan format Landscape A4.</p>
                <a href="{{ route('pdf.sertifikat') }}" target="_blank" class="btn btn-outline-light btn-fw">Preview / Cetak (L)</a>
            </div>
        </div>
    </div>

    {{-- Card Undangan --}}
    <div class="col-md-6 grid-margin stretch-card">
        <div class="card bg-gradient-info card-img-holder text-white">
            <div class="card-body">
                <img src="{{ asset('assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                <h4 class="font-weight-normal mb-3">Undangan Event <i class="mdi mdi-email-open mdi-24px float-right"></i>
                </h4>
                <p class="mb-4">Cetak surat undangan resmi kegiatan "Meet & Greet" dengan format Portrait A4 + Header.</p>
                <a href="{{ route('pdf.undangan') }}" target="_blank" class="btn btn-outline-light btn-fw">Preview / Cetak (P)</a>
            </div>
        </div>
    </div>
</div>
@endsection
