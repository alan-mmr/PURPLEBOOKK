@extends('layouts.main')

@section('title', 'Kelola Toko - PURPLEBOOK')

@push('styles')
<style>
    .barcode-badge {
        font-family: monospace;
        font-size: 0.9rem;
        letter-spacing: 2px;
        background: linear-gradient(135deg, #7B2D8B, #a84fc2);
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
    }
    .coord-text {
        font-family: monospace;
        font-size: 0.8rem;
        color: #555;
    }
    .no-coord {
        color: #ccc;
        font-style: italic;
        font-size: 0.8rem;
    }
    #tokoTable th { white-space: nowrap; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-store-marker"></i>
        </span> Kelola Toko
    </h3>
</div>

<div class="row">

    {{-- ── SECTION 1: List Toko ──────────────────────────────────── --}}
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    <i class="mdi mdi-format-list-bulleted text-primary"></i> List Toko
                </h4>

                <form id="formCetakBarcode" method="POST" action="{{ route('toko.cetakBarcode') }}" target="_blank">
                    @csrf
                    <div class="table-responsive">
                        <table id="tokoTable" class="table table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th width="40"><input type="checkbox" id="checkAll" title="Pilih semua"></th>
                                    <th>Barcode</th>
                                    <th>Nama Toko</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Accuracy (m)</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tokos as $toko)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="barcode_ids[]" value="{{ $toko->barcode }}" class="toko-check">
                                    </td>
                                    <td>
                                        <span class="barcode-badge">{{ $toko->barcode }}</span>
                                    </td>
                                    <td><strong>{{ $toko->nama_toko }}</strong></td>
                                    <td>
                                        @if($toko->latitude != 0)
                                            <span class="coord-text">{{ $toko->latitude }}</span>
                                        @else
                                            <span class="no-coord">Belum diset</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($toko->longitude != 0)
                                            <span class="coord-text">{{ $toko->longitude }}</span>
                                        @else
                                            <span class="no-coord">Belum diset</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($toko->accuracy != 0)
                                            <span class="coord-text">{{ $toko->accuracy }} m</span>
                                        @else
                                            <span class="no-coord">Belum diset</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm"
                                                onclick="bukaModalEdit('{{ $toko->barcode }}', '{{ $toko->nama_toko }}', '{{ $toko->latitude }}', '{{ $toko->longitude }}', '{{ $toko->accuracy }}')">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button type="submit" form="formDelete_{{ $toko->barcode }}" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="mdi mdi-store-off mdi-36px d-block mb-2"></i>
                                        Belum ada toko. Tambah toko di bawah.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Tombol Cetak Barcode --}}
                    <div class="mt-3">
                        <button type="submit" class="btn btn-gradient-primary" id="btnCetak" disabled>
                            <i class="mdi mdi-barcode"></i> Cetak Barcode Terpilih
                        </button>
                        <small class="text-muted ml-2">Centang toko yang ingin dicetak barcode-nya</small>
                    </div>
                </form>

                {{-- Form Hapus (dipindah ke luar agar tidak nested form) --}}
                @foreach($tokos as $toko)
                <form id="formDelete_{{ $toko->barcode }}" method="POST" action="{{ route('toko.destroy', $toko->barcode) }}" 
                      onsubmit="return confirm('Yakin ingin menghapus toko {{ $toko->nama_toko }}?');" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── SECTION 2: Form Tambah Toko ──────────────────────────── --}}
    <div class="col-md-6 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">
                    <i class="mdi mdi-plus-circle text-success"></i> Tambah Toko Baru
                </h4>
                <p class="card-description text-muted small">
                    Barcode akan otomatis digenerate oleh sistem.
                </p>

                <form method="POST" action="{{ route('toko.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="nama_toko">Nama Toko <span class="text-danger">*</span></label>
                        <input type="text"
                               id="nama_toko"
                               name="nama_toko"
                               class="form-control @error('nama_toko') is-invalid @enderror"
                               placeholder="Contoh: Toko Maju Jaya"
                               value="{{ old('nama_toko') }}"
                               maxlength="50"
                               required>
                        @error('nama_toko')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-2">
                        <label>Koordinat Toko (Opsional)</label>
                        <p class="text-muted small mb-2">Klik tombol Geoloc untuk mendapatkan kordinat dari lokasi saat ini.</p>
                        <button type="button" class="btn btn-sm btn-info mb-2" onclick="ambilLokasiToko('tambah')">
                            <i class="mdi mdi-crosshairs-gps"></i> Ambil Geolokasi
                        </button>
                        <div id="geolocProgressTambah" class="text-info small mb-2" style="display:none;">
                            <i class="mdi mdi-loading mdi-spin"></i> Mencari sinyal GPS...
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="latitude">Latitude</label>
                            <input type="number" step="any" class="form-control" id="latitude" name="latitude" value="{{ old('latitude') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="longitude">Longitude</label>
                            <input type="number" step="any" class="form-control" id="longitude" name="longitude" value="{{ old('longitude') }}">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="accuracy">Accuracy (m)</label>
                            <input type="number" step="any" class="form-control" id="accuracy" name="accuracy" value="{{ old('accuracy') }}">
                            <div id="accuracyIndicatorTambah" class="mt-1 small font-weight-bold"></div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gradient-success btn-block mt-3">
                        <i class="mdi mdi-content-save"></i> Simpan Toko
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Info Card ──────────────────────────────────────────────── --}}
    <div class="col-md-6 grid-margin">
        <div class="card" style="background: linear-gradient(135deg, #f3e5f5, #e8eaf6); border: 1px solid #ce93d8;">
            <div class="card-body">
                <h5 class="font-weight-bold" style="color:#7B2D8B;">
                    <i class="mdi mdi-information-outline"></i> Cara Penggunaan
                </h5>
                <ol class="mb-0 pl-3" style="font-size: 0.9rem; line-height: 1.8;">
                    <li>Tambah toko baru menggunakan form di sebelah kiri (beserta kordinatnya).</li>
                    <li>Barcode toko akan otomatis digenerate (format: YYMMDD##).</li>
                    <li>Gunakan tombol <i class="mdi mdi-pencil text-warning"></i> untuk mengedit nama atau kordinat toko.</li>
                    <li>Centang toko → <strong>Cetak Barcode</strong> untuk mencetak label barcode toko.</li>
                    <li>Tempelkan barcode di lokasi toko tersebut.</li>
                    <li>Alternatif set koordinat GPS toko via menu <strong>Kunjungan Toko → Input Titik Awal</strong>.</li>
                    <li>Sales scan barcode saat kunjungan via menu <strong>Kunjungan Toko → Titik Kunjungan</strong>.</li>
                </ol>
            </div>
        </div>
    </div>

</div>

{{-- ── Modal Edit Toko ─────────────────────────────────────────── --}}
<div class="modal fade" id="modalEditToko" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="modalEditLabel">
                    <i class="mdi mdi-store-edit text-warning"></i> Edit Toko
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditToko" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body bg-light">
                    <div class="form-group">
                        <label for="edit_nama_toko">Nama Toko <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-white" id="edit_nama_toko" name="nama_toko" required>
                    </div>

                    <div class="form-group mb-2 mt-4">
                        <label>Koordinat Toko</label>
                        <button type="button" class="btn btn-sm btn-info float-right" onclick="ambilLokasiToko('edit')">
                            <i class="mdi mdi-crosshairs-gps"></i> Geoloc
                        </button>
                        <div id="geolocProgressEdit" class="text-info small mt-2" style="display:none;">
                            <i class="mdi mdi-loading mdi-spin"></i> Mencari sinyal GPS...
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="edit_latitude">Latitude</label>
                            <input type="number" step="any" class="form-control bg-white" id="edit_latitude" name="latitude">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="edit_longitude">Longitude</label>
                            <input type="number" step="any" class="form-control bg-white" id="edit_longitude" name="longitude">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="edit_accuracy">Accuracy (m)</label>
                            <input type="number" step="any" class="form-control bg-white" id="edit_accuracy" name="accuracy">
                            <div id="accuracyIndicatorEdit" class="mt-1 small font-weight-bold"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Check All / Uncheck All ───────────────────────────────────────
document.getElementById('checkAll').addEventListener('change', function () {
    document.querySelectorAll('.toko-check').forEach(cb => cb.checked = this.checked);
    updateCetakBtn();
});

document.querySelectorAll('.toko-check').forEach(cb => {
    cb.addEventListener('change', updateCetakBtn);
});

function updateCetakBtn() {
    const anyChecked = [...document.querySelectorAll('.toko-check')].some(cb => cb.checked);
    document.getElementById('btnCetak').disabled = !anyChecked;
}

// ── Buka Modal Edit ────────────────────────────────────────────────
function bukaModalEdit(barcode, nama, lat, lng, acc) {
    document.getElementById('edit_nama_toko').value = nama;
    document.getElementById('edit_latitude').value = lat != 0 ? lat : '';
    document.getElementById('edit_longitude').value = lng != 0 ? lng : '';
    document.getElementById('edit_accuracy').value = acc != 0 ? acc : '';
    document.getElementById('accuracyIndicatorEdit').innerHTML = '';

    const url = '{{ route("toko.update", ":id") }}'.replace(':id', barcode);
    document.getElementById('formEditToko').action = url;

    $('#modalEditToko').modal('show');
}

// ── Ambil Geolocation (Tambah & Edit) ────────────────────────────
function getAccuratePosition(targetAccuracy = 50, maxWait = 20000) {
    return new Promise((resolve, reject) => {
        let bestResult = null;
        const startTime = Date.now();

        const watchId = navigator.geolocation.watchPosition(
            (position) => {
                const acc = position.coords.accuracy;
                if (!bestResult || acc < bestResult.coords.accuracy) {
                    bestResult = position;
                }
                if (acc <= targetAccuracy) {
                    navigator.geolocation.clearWatch(watchId);
                    resolve(bestResult);
                }
                if (Date.now() - startTime >= maxWait) {
                    navigator.geolocation.clearWatch(watchId);
                    if (bestResult) resolve(bestResult);
                    else reject(new Error('Timeout'));
                }
            },
            (error) => reject(error),
            { enableHighAccuracy: true, maximumAge: 0, timeout: maxWait }
        );
    });
}

async function ambilLokasiToko(context) {
    if (!navigator.geolocation) { alert('Browser tidak mendukung Geolocation.'); return; }

    const isTambah = context === 'tambah';
    const progressEl = document.getElementById(isTambah ? 'geolocProgressTambah' : 'geolocProgressEdit');
    const latEl = document.getElementById(isTambah ? 'latitude' : 'edit_latitude');
    const lngEl = document.getElementById(isTambah ? 'longitude' : 'edit_longitude');
    const accEl = document.getElementById(isTambah ? 'accuracy' : 'edit_accuracy');
    const indEl = document.getElementById(isTambah ? 'accuracyIndicatorTambah' : 'accuracyIndicatorEdit');

    progressEl.style.display = 'block';
    indEl.innerHTML = '';

    try {
        const pos = await getAccuratePosition(50, 20000);
        latEl.value = pos.coords.latitude;
        lngEl.value = pos.coords.longitude;
        const acc = pos.coords.accuracy;
        accEl.value = Math.round(acc * 100) / 100;

        if (acc <= 20) {
            indEl.innerHTML = `<span class="text-success">Akurasi sangat baik: ${Math.round(acc)}m</span>`;
        } else if (acc <= 50) {
            indEl.innerHTML = `<span class="text-warning">Akurasi cukup: ${Math.round(acc)}m</span>`;
        } else {
            indEl.innerHTML = `<span class="text-danger">Akurasi rendah: ${Math.round(acc)}m</span>`;
        }
    } catch (err) {
        alert('Gagal mengambil lokasi GPS.');
    } finally {
        progressEl.style.display = 'none';
    }
}
</script>
@endpush
