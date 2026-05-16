@extends('layouts.clean')

@section('title', 'Ambil Antrian — PURPLEBOOK')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #f3e8ff 0%, #ede0f8 100%); min-height: 100vh; }

    .clean-page { max-width: 680px; margin: 0 auto; padding: 40px 20px 60px; }

    .clean-header {
        text-align: center;
        margin-bottom: 32px;
    }
    .clean-header .logo-text {
        font-size: 0.85rem;
        letter-spacing: 3px;
        color: #9B59B6;
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .antrian-hero {
        background: linear-gradient(135deg, #7B2D8B 0%, #4a1060 100%);
        border-radius: 20px;
        padding: 40px;
        color: white;
        text-align: center;
        margin-bottom: 30px;
    }
    .antrian-hero h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 8px; }
    .antrian-hero p  { opacity: 0.85; font-size: 1.05rem; }

    .vendor-card {
        border: 2px solid transparent;
        border-radius: 14px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s;
        background: #f8f4fc;
    }
    .vendor-card:hover { border-color: #7B2D8B; background: #f0e6f8; }
    .vendor-card.selected { border-color: #7B2D8B; background: #e8d5f5; box-shadow: 0 0 0 3px rgba(123,45,139,0.15); }
    .vendor-card .vendor-name { font-weight: 700; color: #4a1060; font-size: 1rem; }
    .vendor-card .vendor-icon { font-size: 2rem; margin-bottom: 8px; }

    .form-card {
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 24px rgba(123,45,139,0.10);
    }
    .btn-antrian {
        background: linear-gradient(135deg, #7B2D8B, #a855c7);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 14px 32px;
        font-size: 1.1rem;
        font-weight: 700;
        width: 100%;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-antrian:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(123,45,139,0.35);
        color: white;
    }
    .step-badge {
        width: 32px; height: 32px;
        background: #7B2D8B;
        color: white;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        margin-right: 8px;
        flex-shrink: 0;
    }
    .step-label { font-weight: 600; color: #4a1060; font-size: 1rem; }
</style>
@endpush

@section('content')
<div class="clean-page">

    {{-- Logo kecil di atas --}}
    <div class="clean-header">
        <div class="logo-text">🎟 PURPLEBOOK</div>
    </div>

    {{-- Hero --}}
    <div class="antrian-hero">
        <h1>🎟️ Ambil Nomor Antrian</h1>
        <p>Pilih vendor tujuan, masukkan nama kamu, dan dapatkan nomor antrian digital.</p>
    </div>

<div class="row justify-content-center">
    <div class="col-12">
        <div class="card form-card">
            <div class="card-body p-4">

                <form method="POST" action="{{ route('antrian.store') }}" id="formAntrian">
                    @csrf

                    {{-- Step 1: Pilih Vendor --}}
                    <div class="d-flex align-items-center mb-3">
                        <span class="step-badge">1</span>
                        <span class="step-label">Pilih Vendor Tujuan</span>
                    </div>

                    <div class="row mb-4" id="vendorGrid">
                        @forelse($vendors as $vendor)
                        <div class="col-6 col-md-4 mb-3">
                            <div class="vendor-card text-center" onclick="pilihVendor({{ $vendor->idvendor }}, this)">
                                <div class="vendor-icon">🏪</div>
                                <div class="vendor-name">{{ $vendor->nama_vendor }}</div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert-circle-outline"></i>
                                Belum ada vendor terdaftar. Hubungi admin.
                            </div>
                        </div>
                        @endforelse
                    </div>

                    <input type="hidden" name="idvendor" id="idvendorInput">
                    <div id="vendorError" class="text-danger small mb-3" style="display:none;">
                        <i class="mdi mdi-alert-circle-outline"></i> Pilih vendor terlebih dahulu.
                    </div>

                    {{-- Step 2: Nama --}}
                    <div class="d-flex align-items-center mb-3">
                        <span class="step-badge">2</span>
                        <span class="step-label">Masukkan Nama Kamu</span>
                    </div>

                    <div class="form-group mb-4">
                        <input type="text"
                               id="nama"
                               name="nama"
                               class="form-control form-control-lg @error('nama') is-invalid @enderror"
                               placeholder="Contoh: Budi Santoso"
                               value="{{ old('nama') }}"
                               maxlength="100"
                               autocomplete="off"
                               required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-antrian" id="btnAmbil" onclick="return validateForm()">
                        <i class="mdi mdi-ticket-confirmation-outline"></i> Ambil Nomor Antrian
                    </button>
                </form>

        </div>
    </div>
</div>
</div>{{-- end clean-page --}}
@endsection

@push('scripts')
<script>
function pilihVendor(id, el) {
    // Reset semua card
    document.querySelectorAll('.vendor-card').forEach(c => c.classList.remove('selected'));
    // Pilih yang diklik
    el.classList.add('selected');
    document.getElementById('idvendorInput').value = id;
    document.getElementById('vendorError').style.display = 'none';
}

function validateForm() {
    if (!document.getElementById('idvendorInput').value) {
        document.getElementById('vendorError').style.display = 'block';
        document.getElementById('vendorGrid').scrollIntoView({ behavior: 'smooth' });
        return false;
    }
    return true;
}

// Jika ada old value saat validasi gagal
@if(old('idvendor'))
document.getElementById('idvendorInput').value = '{{ old("idvendor") }}';
const cards = document.querySelectorAll('.vendor-card');
// highlight vendor yang sudah dipilih
cards.forEach(card => {
    if (card.getAttribute('onclick').includes('{{ old("idvendor") }}')) {
        card.classList.add('selected');
    }
});
@endif
</script>
@endpush
