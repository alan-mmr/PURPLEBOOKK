@extends('layouts.main')

@section('title', (isset($customer) ? 'Edit' : 'Tambah') . ' Customer - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-{{ isset($customer) ? 'account-edit' : 'account-plus' }}"></i>
        </span> {{ isset($customer) ? 'Edit' : 'Tambah' }} Customer
    </h3>
    <a href="{{ route('customer.index') }}" class="btn btn-outline-secondary">
        <i class="mdi mdi-arrow-left"></i> Kembali
    </a>
</div>

{{-- Error Messages --}}
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<form action="{{ isset($customer) ? route('customer.update', $customer->idcustomer) : route('customer.store') }}"
      method="POST" id="customerForm">
    @csrf
    @if(isset($customer))
        @method('PUT')
    @endif

    <div class="row">
        {{-- Kolom Kiri: Form Data --}}
        <div class="col-md-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title"><i class="mdi mdi-account"></i> Data Customer</h4>

                    {{-- Input Nama --}}
                    <div class="form-group">
                        <label for="nama" class="font-weight-bold">Nama Customer <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama"
                               value="{{ old('nama', $customer->nama ?? '') }}"
                               placeholder="Masukkan nama customer" required>
                    </div>

                    {{-- Mode Penyimpanan Foto --}}
                    @php
                        // Tentukan mode aktif saat ini (untuk edit mode)
                        $currentMode = 'blob'; // default
                        if (isset($customer)) {
                            if ($customer->foto_path) $currentMode = 'file';
                        }
                    @endphp
                    <div class="form-group">
                        <label class="font-weight-bold">Mode Simpan Foto</label>
                        <div class="d-flex">
                            <div class="form-check mr-4">
                                <input class="form-check-input" type="radio" name="foto_mode"
                                       id="modeBlob" value="blob"
                                       {{ $currentMode === 'blob' ? 'checked' : '' }}>
                                <label class="form-check-label" for="modeBlob">
                                    <i class="mdi mdi-database"></i> Blob (Database)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="foto_mode"
                                       id="modeFile" value="file"
                                       {{ $currentMode === 'file' ? 'checked' : '' }}>
                                <label class="form-check-label" for="modeFile">
                                    <i class="mdi mdi-folder"></i> File (Disk)
                                </label>
                            </div>
                        </div>
                        <small class="text-muted">
                            Blob = foto disimpan langsung di database PostgreSQL.<br>
                            File = foto disimpan sebagai file .jpg di server (storage/customers/).<br>
                            <strong>Tip:</strong> Ganti mode + ambil foto baru untuk konversi penyimpanan.
                        </small>
                    </div>

                    {{-- Foto Saat Ini (Edit Mode) --}}
                    @if(isset($customer) && $customer->hasFoto())
                    <div class="form-group">
                        <label class="font-weight-bold">Foto Saat Ini</label>
                        <div class="text-center p-2" style="background:#f5f5f5; border-radius:8px;">
                            @if($customer->foto_blob)
                                <img src="{{ route('customer.photo', $customer->idcustomer) }}"
                                     style="max-width:150px; border-radius:8px;">
                                <br><span class="badge badge-info mt-1">Tersimpan sebagai Blob</span>
                            @elseif($customer->foto_path)
                                <img src="{{ asset('storage/' . $customer->foto_path) }}"
                                     style="max-width:150px; border-radius:8px;">
                                <br><span class="badge badge-success mt-1">Tersimpan sebagai File</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Hidden input untuk base64 foto dari canvas --}}
                    <input type="hidden" name="foto_data" id="fotoData">

                    {{-- Tombol Submit --}}
                    <button type="submit" class="btn btn-gradient-primary btn-block mt-3" id="btnSubmit">
                        <i class="mdi mdi-content-save"></i> {{ isset($customer) ? 'Update' : 'Simpan' }} Customer
                    </button>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Kamera --}}
        <div class="col-md-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title"><i class="mdi mdi-camera"></i> Ambil Foto dari Kamera</h4>

                    {{-- Status Kamera --}}
                    <div id="cameraStatus" class="alert alert-info" style="display:none;">
                        <i class="mdi mdi-loading mdi-spin"></i> Mengakses kamera...
                    </div>
                    <div id="cameraError" class="alert alert-danger" style="display:none;">
                        <i class="mdi mdi-alert"></i> <span id="cameraErrorMsg"></span>
                    </div>

                    {{-- Video Preview (Live Camera) --}}
                    <div id="cameraContainer" class="text-center mb-3">
                        <video id="cameraVideo" autoplay playsinline
                               style="width:100%; max-height:400px; border-radius:12px; border:3px solid #7B2D8B; background:#000;">
                        </video>
                    </div>

                    {{-- Canvas (Hidden, untuk capture) --}}
                    <canvas id="cameraCanvas" style="display:none;"></canvas>

                    {{-- Preview Foto Hasil Capture --}}
                    <div id="photoPreview" class="text-center mb-3" style="display:none;">
                        <img id="photoPreviewImg" src=""
                             style="width:100%; max-height:400px; border-radius:12px; border:3px solid #28a745; object-fit:contain; background:#000;">
                        <p class="text-success mt-2 font-weight-bold">
                            <i class="mdi mdi-check-circle"></i> Foto berhasil diambil!
                        </p>
                    </div>

                    {{-- Tombol Kontrol Kamera --}}
                    <div class="text-center">
                        <button type="button" class="btn btn-gradient-info mr-2" id="btnStartCamera">
                            <i class="mdi mdi-camera"></i> Nyalakan Kamera
                        </button>
                        <button type="button" class="btn btn-gradient-success mr-2" id="btnCapture" style="display:none;">
                            <i class="mdi mdi-camera-iris"></i> Ambil Foto
                        </button>
                        <button type="button" class="btn btn-gradient-warning" id="btnRetake" style="display:none;">
                            <i class="mdi mdi-refresh"></i> Ulangi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    var video   = document.getElementById('cameraVideo');
    var canvas  = document.getElementById('cameraCanvas');
    var preview = document.getElementById('photoPreviewImg');
    var input   = document.getElementById('fotoData');

    var btnStart   = document.getElementById('btnStartCamera');
    var btnCapture = document.getElementById('btnCapture');
    var btnRetake  = document.getElementById('btnRetake');

    var cameraContainer = document.getElementById('cameraContainer');
    var photoPreview    = document.getElementById('photoPreview');
    var cameraStatus    = document.getElementById('cameraStatus');
    var cameraError     = document.getElementById('cameraError');
    var cameraErrorMsg  = document.getElementById('cameraErrorMsg');

    var stream = null;

    // ── Nyalakan Kamera ──────────────────────────────────────────
    btnStart.addEventListener('click', function () {
        cameraStatus.style.display = 'block';
        cameraError.style.display  = 'none';

        navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
        })
        .then(function (mediaStream) {
            stream = mediaStream;
            video.srcObject = stream;
            cameraStatus.style.display = 'none';

            // Tampilkan video, sembunyikan preview
            cameraContainer.style.display = 'block';
            photoPreview.style.display    = 'none';

            // Tampilkan tombol capture
            btnStart.style.display   = 'none';
            btnCapture.style.display = 'inline-block';
            btnRetake.style.display  = 'none';
        })
        .catch(function (err) {
            cameraStatus.style.display = 'none';
            cameraError.style.display  = 'block';

            if (err.name === 'NotAllowedError') {
                cameraErrorMsg.textContent = 'Akses kamera ditolak. Izinkan akses kamera di browser Anda.';
            } else if (err.name === 'NotFoundError') {
                cameraErrorMsg.textContent = 'Kamera tidak ditemukan di perangkat ini.';
            } else {
                cameraErrorMsg.textContent = 'Gagal mengakses kamera: ' + err.message;
            }
        });
    });

    // ── Ambil Foto (Capture) ─────────────────────────────────────
    btnCapture.addEventListener('click', function () {
        // Set canvas size sesuai video
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;

        // Gambar frame video ke canvas
        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Convert ke base64 JPEG (kualitas 80%)
        var dataUri = canvas.toDataURL('image/jpeg', 0.8);

        // Set ke hidden input dan preview
        input.value = dataUri;
        preview.src = dataUri;

        // Tampilkan preview, sembunyikan video
        cameraContainer.style.display = 'none';
        photoPreview.style.display    = 'block';

        // Tombol
        btnCapture.style.display = 'none';
        btnRetake.style.display  = 'inline-block';

        // Stop kamera untuk hemat resource
        stopCamera();
    });

    // ── Ulangi (Retake) ──────────────────────────────────────────
    btnRetake.addEventListener('click', function () {
        input.value = '';
        preview.src = '';

        photoPreview.style.display = 'none';
        btnRetake.style.display    = 'none';

        // Nyalakan kamera lagi
        btnStart.click();
    });

    // ── Helper: Stop kamera ──────────────────────────────────────
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(function (track) { track.stop(); });
            stream = null;
        }
    }

    // Stop kamera saat pindah halaman
    window.addEventListener('beforeunload', stopCamera);
})();
</script>
@endpush
