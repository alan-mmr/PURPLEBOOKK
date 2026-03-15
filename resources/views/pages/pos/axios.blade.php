@extends('layouts.main')

@section('title', 'Point of Sales — Axios | PURPLEBOOK')

@push('styles')
<style>
    .pos-header { background: linear-gradient(135deg, #0d4e8a, #0d6efd); color: white; border-radius: 8px 8px 0 0; padding: 14px 20px; }
    .input-kode { font-family: monospace; font-size: 1rem; letter-spacing: 2px; border: 2px solid #0d6efd; border-radius: 8px; }
    .input-kode:focus { box-shadow: 0 0 0 3px rgba(13,110,253,0.2); border-color: #0d4e8a; outline: none; }
    .input-readonly { background: #fff0f3 !important; border: 2px solid #f5c2cc !important; border-radius: 8px; color: #6c757d; }
    .input-jumlah { border: 2px solid #0d6efd; border-radius: 8px; }
    .input-jumlah:focus { box-shadow: 0 0 0 3px rgba(13,110,253,0.2); border-color: #0d4e8a; outline: none; }
    #tabelTransaksi { border-radius: 8px; overflow: hidden; }
    #tabelTransaksi thead { background: linear-gradient(135deg, #0d4e8a, #0d6efd); color: white; }
    #tabelTransaksi thead th { border: none; padding: 12px 14px; font-weight: 600; font-size: 0.85rem; }
    #tabelTransaksi tbody tr:hover { background: #f0f4ff; }
    #tabelTransaksi .input-jumlah-tabel { width: 80px; text-align: center; border: 1px solid #ced4da; border-radius: 6px; padding: 4px 8px; font-size: 0.88rem; }
    #tabelTransaksi .input-jumlah-tabel:focus { outline: none; border-color: #0d6efd; box-shadow: 0 0 0 2px rgba(13,110,253,0.2); }
    .total-bar { background: linear-gradient(135deg, #0a3060, #0d4e8a); color: white; border-radius: 8px; padding: 14px 20px; }
    .total-amount { font-size: 1.5rem; font-weight: 700; letter-spacing: 1px; }
    .barang-status { font-size: 0.82rem; padding: 4px 10px; border-radius: 20px; display: inline-block; margin-top: 6px; }
    .barang-status.found    { background: #cfe2ff; color: #084298; }
    .barang-status.notfound { background: #f8d7da; color: #721c24; }
    .barang-status.idle     { display: none; }
    .btn-hapus-baris { width: 30px; height: 30px; padding: 0; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-info text-white mr-2">
            <i class="mdi mdi-cash-register"></i>
        </span>
        Point Of Sales
        <small class="text-muted ml-2" style="font-size:13px;">(Axios)</small>
    </h3>
    <nav>
        <a href="{{ route('pos.ajax') }}" class="btn btn-outline-primary btn-sm">
            <i class="mdi mdi-swap-horizontal"></i> Lihat Versi AJAX
        </a>
    </nav>
</div>

<div class="row">
    {{-- ── Form Input Barang ── --}}
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="pos-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 font-weight-bold">
                        <i class="mdi mdi-barcode-scan mr-1"></i> Input Barang
                    </h5>
                    <small class="opacity-75">Masukkan kode barang lalu tekan <kbd style="background:rgba(255,255,255,0.25);border:1px solid rgba(255,255,255,0.5);padding:2px 6px;border-radius:4px;">Enter</kbd></small>
                </div>
                <span style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.4);border-radius:20px;padding:3px 12px;font-size:0.75rem;font-weight:600;">
                    <i class="mdi mdi-alpha-a-box"></i> Axios
                </span>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" id="inputKode" class="form-control input-kode" placeholder="Scan / Ketik kode..." autocomplete="off">
                            <span class="barang-status idle" id="statusBarang"></span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Nama Barang</label>
                            <input type="text" id="inputNama" class="form-control input-readonly" readonly placeholder="Otomatis terisi...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Harga Barang</label>
                            <input type="text" id="inputHarga" class="form-control input-readonly" readonly placeholder="Otomatis...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Jumlah</label>
                            <input type="number" id="inputJumlah" class="form-control input-jumlah" value="1" min="1">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="btnTambahkan" class="btn btn-primary btn-block" disabled>
                            <i class="mdi mdi-plus-circle"></i> Tambahkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabel Transaksi ── --}}
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="mdi mdi-receipt text-primary mr-1"></i> Daftar Item Transaksi
                </h5>
                <div class="table-responsive">
                    <table id="tabelTransaksi" class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="50px">No</th>
                                <th width="120px">Kode</th>
                                <th>Nama</th>
                                <th width="130px">Harga</th>
                                <th width="110px">Jumlah</th>
                                <th width="140px">Subtotal</th>
                                <th width="60px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="bodyTransaksi">
                            <tr id="rowKosong">
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="mdi mdi-cart-outline" style="font-size:2rem;"></i>
                                    <br><small>Belum ada barang. Scan atau ketik kode barang di atas.</small>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7">
                                    <div class="total-bar d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold" style="font-size:1rem;">
                                            <i class="mdi mdi-sigma mr-1"></i> TOTAL
                                        </span>
                                        <span class="total-amount" id="displayTotal">Rp 0</span>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="text-right mt-3">
                    <button type="button" id="btnBayar" class="btn btn-gradient-info btn-lg px-5" disabled>
                        <i class="mdi mdi-cash mr-1"></i> Bayar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- SweetAlert2 + Axios CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
// ── State ──────────────────────────────────────────────────────────
let barangDitemukan = false;
let hargaBarang     = 0;
let noRow           = 0;

// ── Axios default headers (CSRF) ──────────────────────────────────
axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';

// ── Rupiah formatter ───────────────────────────────────────────────
const rupiah = num => 'Rp ' + parseInt(num).toLocaleString('id-ID');

// ── Update tombol Tambahkan ────────────────────────────────────────
function updateBtnTambahkan() {
    const jumlah = parseInt(document.getElementById('inputJumlah').value) || 0;
    document.getElementById('btnTambahkan').disabled = !(barangDitemukan && jumlah > 0);
}

// ── Hitung Total ───────────────────────────────────────────────────
function hitungTotal() {
    let total = 0;
    document.querySelectorAll('.subtotal-val').forEach(el => {
        total += parseInt(el.dataset.raw) || 0;
    });
    document.getElementById('displayTotal').textContent = rupiah(total);
    const adaBaris = document.querySelectorAll('#bodyTransaksi tr[data-kode]').length > 0;
    document.getElementById('btnBayar').disabled = !adaBaris;
}

// ── Fungsi Cari Barang via Axios ───────────────────────────────────
async function cariBarang(kode) {
    if (!kode) return;

    // Reset state
    barangDitemukan = false;
    document.getElementById('inputNama').value  = '';
    document.getElementById('inputHarga').value = '';
    document.getElementById('inputJumlah').value = 1;
    const status = document.getElementById('statusBarang');
    status.className = 'barang-status idle';
    updateBtnTambahkan();

    try {
        // Axios GET request ke /pos/barang
        const res = await axios.get('{{ route("pos.getBarang") }}', { params: { kode } });
        if (res.data.success) {
            barangDitemukan = true;
            hargaBarang     = res.data.harga;
            document.getElementById('inputNama').value  = res.data.nama;
            document.getElementById('inputHarga').value = rupiah(res.data.harga);
            status.className   = 'barang-status found';
            status.textContent = '✓ Barang ditemukan';
        }
    } catch (err) {
        barangDitemukan = false;
        hargaBarang     = 0;
        status.className   = 'barang-status notfound';
        status.textContent = '✗ Kode barang tidak ditemukan';
    } finally {
        updateBtnTambahkan();
    }
}

// ── Event: Ketik Enter ATAU Input mencapai 8 karakter otomatis ─────
document.getElementById('inputKode').addEventListener('keypress', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13 || e.which === 13) {
        e.preventDefault();
        cariBarang(this.value.trim());
    }
});

document.getElementById('inputKode').addEventListener('input', function () {
    const kode = this.value.trim();
    if (kode.length === 8) {
        cariBarang(kode);
    }
});

// Jumlah berubah → update button
document.getElementById('inputJumlah').addEventListener('input', updateBtnTambahkan);

// ── Tambahkan ke Tabel ─────────────────────────────────────────────
document.getElementById('btnTambahkan').addEventListener('click', function () {
    const kode   = document.getElementById('inputKode').value.trim();
    const nama   = document.getElementById('inputNama').value.trim();
    const jumlah = parseInt(document.getElementById('inputJumlah').value) || 1;
    const subtot = hargaBarang * jumlah;

    // Cek kode sudah ada di tabel
    const existingRow = document.querySelector(`#bodyTransaksi tr[data-kode="${kode}"]`);
    if (existingRow) {
        const inputJml  = existingRow.querySelector('.input-jumlah-tabel');
        const jumlahBaru = parseInt(inputJml.value) + jumlah;
        const subtotBaru = hargaBarang * jumlahBaru;
        inputJml.value = jumlahBaru;
        const subtotEl = existingRow.querySelector('.subtotal-val');
        subtotEl.textContent  = rupiah(subtotBaru);
        subtotEl.dataset.raw  = subtotBaru;
    } else {
        noRow++;
        document.getElementById('rowKosong').style.display = 'none';

        const tr = document.createElement('tr');
        tr.dataset.kode  = kode;
        tr.dataset.harga = hargaBarang;
        tr.innerHTML = `
            <td>${noRow}</td>
            <td><span class="badge badge-dark" style="font-family:monospace;">${kode}</span></td>
            <td>${nama}</td>
            <td class="text-right">${rupiah(hargaBarang)}</td>
            <td><input type="number" class="input-jumlah-tabel" value="${jumlah}" min="1" data-kode="${kode}"></td>
            <td class="text-right subtotal-val" data-raw="${subtot}">${rupiah(subtot)}</td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-hapus-baris" title="Hapus">
                    <i class="mdi mdi-delete"></i>
                </button>
            </td>`;
        document.getElementById('bodyTransaksi').appendChild(tr);
    }

    hitungTotal();

    // Reset form
    document.getElementById('inputKode').value  = '';
    document.getElementById('inputNama').value  = '';
    document.getElementById('inputHarga').value = '';
    document.getElementById('inputJumlah').value = 1;
    document.getElementById('statusBarang').className = 'barang-status idle';
    barangDitemukan = false;
    hargaBarang     = 0;
    updateBtnTambahkan();
    document.getElementById('inputKode').focus();

    // Spinner SK
    this.disabled = true;
    this.innerHTML = '<span class="spinner-grow spinner-grow-sm mr-1"></span> Menambahkan...';
    setTimeout(() => { this.innerHTML = '<i class="mdi mdi-plus-circle"></i> Tambahkan'; }, 400);
});

// ── Edit Jumlah di Tabel ──────────────────────────────────────────
document.getElementById('bodyTransaksi').addEventListener('input', function (e) {
    if (!e.target.classList.contains('input-jumlah-tabel')) return;
    
    let rawVal = e.target.value;
    let jumlah = 1; // Biarkan default hitung sebagai 1 jika kosong (backspace)
    
    if (rawVal !== '') {
        jumlah = parseInt(rawVal);
        if (isNaN(jumlah) || jumlah < 1) {
            e.target.value = 1;
            jumlah = 1;
        }
    }
    
    const row    = e.target.closest('tr');
    const harga  = parseInt(row.dataset.harga) || 0;
    const subtot = harga * jumlah;
    const subtotEl = row.querySelector('.subtotal-val');
    subtotEl.textContent  = rupiah(subtot);
    subtotEl.dataset.raw  = subtot;
    hitungTotal();
});

// Jika dikosongkan dan ditinggalkan (blur/focusout), paksa jadi 1
document.getElementById('bodyTransaksi').addEventListener('focusout', function (e) {
    if (e.target.classList.contains('input-jumlah-tabel') && e.target.value === '') {
        e.target.value = 1;
        e.target.dispatchEvent(new Event('input', { bubbles: true }));
    }
});

// ── Hapus Baris ────────────────────────────────────────────────────
document.getElementById('bodyTransaksi').addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-hapus-baris');
    if (!btn) return;
    btn.closest('tr').remove();

    // Renomor
    document.querySelectorAll('#bodyTransaksi tr[data-kode]').forEach((row, i) => {
        row.querySelector('td:first-child').textContent = i + 1;
    });

    if (document.querySelectorAll('#bodyTransaksi tr[data-kode]').length === 0) {
        noRow = 0;
        document.getElementById('rowKosong').style.display = '';
    }
    hitungTotal();
});

// ── Bayar → Axios POST ────────────────────────────────────────────
document.getElementById('btnBayar').addEventListener('click', async function () {
    const rows = document.querySelectorAll('#bodyTransaksi tr[data-kode]');
    if (rows.length === 0) return;

    const items = [];
    let total   = 0;
    rows.forEach(row => {
        const kode   = row.dataset.kode;
        const jumlah = parseInt(row.querySelector('.input-jumlah-tabel').value) || 1;
        const subtot = parseInt(row.querySelector('.subtotal-val').dataset.raw) || 0;
        items.push({ kode, jumlah });
        total += subtot;
    });

    // Spinner SK
    this.disabled   = true;
    this.innerHTML  = '<span class="spinner-grow spinner-grow-sm mr-1"></span> Memproses...';

    try {
        // Axios POST request ke /pos/store
        const res = await axios.post('{{ route("pos.store") }}', { items, total });

        if (res.data.success) {
            await Swal.fire({
                icon:             'success',
                title:            'Pembayaran Berhasil!',
                html:             `<p>Transaksi <b>#${res.data.id_penjualan}</b> berhasil disimpan.</p>
                                   <p class="mb-0">Total: <b>${rupiah(total)}</b></p>`,
                confirmButtonText: 'OK',
                confirmButtonColor: '#0d6efd',
                timer:             4000,
                timerProgressBar:  true,
            });
            resetHalaman();
        }
    } catch (err) {
        const msg = err.response?.data?.message || 'Terjadi kesalahan.';
        Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
    } finally {
        this.disabled  = false;
        this.innerHTML = '<i class="mdi mdi-cash mr-1"></i> Bayar';
    }
});

// ── Reset halaman ─────────────────────────────────────────────────
function resetHalaman() {
    document.getElementById('inputKode').value  = '';
    document.getElementById('inputNama').value  = '';
    document.getElementById('inputHarga').value = '';
    document.getElementById('inputJumlah').value = 1;
    document.getElementById('statusBarang').className = 'barang-status idle';
    barangDitemukan = false;
    hargaBarang     = 0;
    noRow           = 0;
    updateBtnTambahkan();

    document.getElementById('bodyTransaksi').innerHTML = `
        <tr id="rowKosong">
            <td colspan="7" class="text-center text-muted py-4">
                <i class="mdi mdi-cart-outline" style="font-size:2rem;"></i>
                <br><small>Belum ada barang. Scan atau ketik kode barang di atas.</small>
            </td>
        </tr>`;

    document.getElementById('displayTotal').textContent = 'Rp 0';
    document.getElementById('btnBayar').disabled = true;
    document.getElementById('inputKode').focus();
}
</script>
@endpush
