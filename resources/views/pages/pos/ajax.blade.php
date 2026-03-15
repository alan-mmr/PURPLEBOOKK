@extends('layouts.main')

@section('title', 'Point of Sales — AJAX | PURPLEBOOK')

@push('styles')
<style>
    /* ── POS Card Header ──────────────────────────────────────────── */
    .pos-header {
        background: linear-gradient(135deg, #1a7a4a, #28a745);
        color: white;
        border-radius: 8px 8px 0 0;
        padding: 14px 20px;
    }

    /* ── Input Fields ─────────────────────────────────────────────── */
    .input-kode {
        font-family: monospace;
        font-size: 1rem;
        letter-spacing: 2px;
        border: 2px solid #28a745;
        border-radius: 8px;
    }
    .input-kode:focus {
        box-shadow: 0 0 0 3px rgba(40,167,69,0.2);
        border-color: #1a7a4a;
        outline: none;
    }
    .input-readonly {
        background: #fff0f3 !important;
        border: 2px solid #f5c2cc !important;
        border-radius: 8px;
        color: #6c757d;
    }
    .input-jumlah {
        border: 2px solid #28a745;
        border-radius: 8px;
    }
    .input-jumlah:focus {
        box-shadow: 0 0 0 3px rgba(40,167,69,0.2);
        border-color: #1a7a4a;
        outline: none;
    }

    /* ── Tabel Transaksi ─────────────────────────────────────────── */
    #tabelTransaksi {
        border-radius: 8px;
        overflow: hidden;
    }
    #tabelTransaksi thead {
        background: linear-gradient(135deg, #1a7a4a, #28a745);
        color: white;
    }
    #tabelTransaksi thead th {
        border: none;
        padding: 12px 14px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    #tabelTransaksi tbody tr:hover { background: #f0fff4; }
    #tabelTransaksi .input-jumlah-tabel {
        width: 80px;
        text-align: center;
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 0.88rem;
    }
    #tabelTransaksi .input-jumlah-tabel:focus {
        outline: none;
        border-color: #28a745;
        box-shadow: 0 0 0 2px rgba(40,167,69,0.2);
    }

    /* ── Total Bar ────────────────────────────────────────────────── */
    .total-bar {
        background: linear-gradient(135deg, #0f3d23, #1a7a4a);
        color: white;
        border-radius: 8px;
        padding: 14px 20px;
    }
    .total-amount {
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: 1px;
    }

    /* ── Status badge barang ────────────────────────────────────────── */
    .barang-status {
        font-size: 0.82rem;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
        margin-top: 6px;
    }
    .barang-status.found    { background: #d4edda; color: #155724; }
    .barang-status.notfound { background: #f8d7da; color: #721c24; }
    .barang-status.idle     { display: none; }

    /* ── Tombol Hapus baris ───────────────────────────────────────── */
    .btn-hapus-baris {
        width: 30px; height: 30px;
        padding: 0;
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-success text-white mr-2">
            <i class="mdi mdi-cash-register"></i>
        </span>
        Point Of Sales
        <small class="text-muted ml-2" style="font-size:13px;">(jQuery AJAX)</small>
    </h3>
    <nav>
        <a href="{{ route('pos.axios') }}" class="btn btn-outline-success btn-sm">
            <i class="mdi mdi-swap-horizontal"></i> Lihat Versi Axios
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
                    <i class="mdi mdi-lightning-bolt"></i> jQuery AJAX
                </span>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    {{-- Kode Barang --}}
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">
                                Kode Barang <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="inputKode"
                                class="form-control input-kode"
                                placeholder="Scan / Ketik kode..."
                                autocomplete="off">
                            <span class="barang-status idle" id="statusBarang"></span>
                        </div>
                    </div>

                    {{-- Nama Barang (readonly) --}}
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Nama Barang</label>
                            <input type="text" id="inputNama"
                                class="form-control input-readonly"
                                readonly placeholder="Otomatis terisi...">
                        </div>
                    </div>

                    {{-- Harga Barang (readonly) --}}
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Harga Barang</label>
                            <input type="text" id="inputHarga"
                                class="form-control input-readonly"
                                readonly placeholder="Otomatis... ">
                        </div>
                    </div>

                    {{-- Jumlah --}}
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Jumlah</label>
                            <input type="number" id="inputJumlah"
                                class="form-control input-jumlah"
                                value="1" min="1">
                        </div>
                    </div>

                    {{-- Tombol Tambahkan --}}
                    <div class="col-md-2">
                        <button type="button" id="btnTambahkan"
                            class="btn btn-success btn-block" disabled>
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
                    <i class="mdi mdi-receipt text-success mr-1"></i>
                    Daftar Item Transaksi
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
                    <button type="button" id="btnBayar"
                        class="btn btn-gradient-success btn-lg px-5" disabled>
                        <i class="mdi mdi-cash mr-1"></i> Bayar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ── State ──────────────────────────────────────────────────────────
var barangDitemukan = false;  // true jika barang valid ditemukan
var hargaBarang     = 0;      // harga asli (integer)
var noRow           = 0;      // counter nomor baris

// ── Rupiah formatter ───────────────────────────────────────────────
function rupiah(num) {
    return 'Rp ' + parseInt(num).toLocaleString('id-ID');
}

// ── Update tombol Tambahkan ────────────────────────────────────────
// Aktif hanya jika barang ditemukan DAN jumlah > 0
function updateBtnTambahkan() {
    var jumlah = parseInt($('#inputJumlah').val()) || 0;
    var aktif  = barangDitemukan && jumlah > 0;
    $('#btnTambahkan').prop('disabled', !aktif);
}

// ── Hitung & tampilkan Total ───────────────────────────────────────
function hitungTotal() {
    var total = 0;
    $('.subtotal-val').each(function () {
        total += parseInt($(this).data('raw')) || 0;
    });
    $('#displayTotal').text(rupiah(total));

    // Tombol Bayar aktif jika ada minimal 1 baris
    var adaBaris = $('#bodyTransaksi tr:not(#rowKosong)').length > 0;
    $('#btnBayar').prop('disabled', !adaBaris);
}

// ── Fungsi Cari Barang via AJAX ────────────────────────────────────
function cariBarang(kode) {
    if (!kode) return;

    // Reset status
    barangDitemukan = false;
    $('#inputNama').val('');
    $('#inputHarga').val('');
    $('#inputJumlah').val(1);
    $('#statusBarang').removeClass('found notfound').addClass('idle');
    updateBtnTambahkan();

    // AJAX GET ke /pos/barang
    $.ajax({
        url:      '{{ route("pos.getBarang") }}',
        method:   'GET',
        data:     { kode: kode },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                // Barang ditemukan
                barangDitemukan = true;
                hargaBarang     = res.harga;
                $('#inputNama').val(res.nama);
                $('#inputHarga').val(rupiah(res.harga));
                $('#statusBarang')
                    .removeClass('idle notfound')
                    .addClass('found')
                    .text('✓ Barang ditemukan');
            }
        },
        error: function (xhr) {
            // 404 = tidak ditemukan
            barangDitemukan = false;
            hargaBarang     = 0;
            $('#inputNama').val('');
            $('#inputHarga').val('');
            $('#statusBarang')
                .removeClass('idle found')
                .addClass('notfound')
                .text('✗ Kode barang tidak ditemukan');
        },
        complete: function () {
            updateBtnTambahkan();
        }
    });
}

// ── Event: Ketik Enter ATAU Input mencapai 8 karakter otomatis ─────
$('#inputKode').on('keypress', function (e) {
    if (e.which === 13 || e.keyCode === 13) {
        e.preventDefault();
        cariBarang($(this).val().trim());
    }
});

$('#inputKode').on('input', function () {
    var kode = $(this).val().trim();
    if (kode.length === 8) {
        cariBarang(kode);
    }
});

// Update btn saat jumlah berubah
$('#inputJumlah').on('input', updateBtnTambahkan);

// ── Tambahkan ke Tabel ─────────────────────────────────────────────
$('#btnTambahkan').on('click', function () {
    var kode   = $('#inputKode').val().trim();
    var nama   = $('#inputNama').val().trim();
    var jumlah = parseInt($('#inputJumlah').val()) || 1;
    var subtot = hargaBarang * jumlah;

    // Cek apakah kode sudah ada di tabel (update jumlah + subtotal)
    var barisExisting = $('#bodyTransaksi tr[data-kode="' + kode + '"]');
    if (barisExisting.length > 0) {
        var jumlahLama = parseInt(barisExisting.find('.input-jumlah-tabel').val()) || 0;
        var jumlahBaru = jumlahLama + jumlah;
        var subtotBaru = hargaBarang * jumlahBaru;

        barisExisting.find('.input-jumlah-tabel').val(jumlahBaru);
        barisExisting.find('.subtotal-val').text(rupiah(subtotBaru)).data('raw', subtotBaru);
    } else {
        // Kode baru → tambah baris baru
        noRow++;
        $('#rowKosong').hide();

        var row = '<tr data-kode="' + kode + '" data-harga="' + hargaBarang + '">' +
            '<td>' + noRow + '</td>' +
            '<td><span class="badge badge-dark" style="font-family:monospace;">' + kode + '</span></td>' +
            '<td>' + nama + '</td>' +
            '<td class="text-right">' + rupiah(hargaBarang) + '</td>' +
            '<td>' +
                '<input type="number" class="input-jumlah-tabel" value="' + jumlah + '" min="1" ' +
                    'data-kode="' + kode + '">' +
            '</td>' +
            '<td class="text-right subtotal-val" data-raw="' + subtot + '">' + rupiah(subtot) + '</td>' +
            '<td class="text-center">' +
                '<button type="button" class="btn btn-danger btn-hapus-baris" title="Hapus">' +
                    '<i class="mdi mdi-delete"></i>' +
                '</button>' +
            '</td>' +
        '</tr>';

        $('#bodyTransaksi').append(row);
    }

    hitungTotal();

    // Reset input form
    $('#inputKode').val('').focus();
    $('#inputNama').val('');
    $('#inputHarga').val('');
    $('#inputJumlah').val(1);
    $('#statusBarang').removeClass('found notfound').addClass('idle');
    barangDitemukan = false;
    hargaBarang     = 0;
    updateBtnTambahkan();

    // Spinner singkat pada tombol (SK pertemuan sebelumnya)
    var $btn = $(this);
    $btn.prop('disabled', true)
        .html('<span class="spinner-grow spinner-grow-sm mr-1"></span> Menambahkan...');
    setTimeout(function () {
        $btn.html('<i class="mdi mdi-plus-circle"></i> Tambahkan');
        // tombol tetap disabled karena barang direset
    }, 400);
});

// ── Edit Jumlah di Tabel → update subtotal & total ────────────────
$(document).on('input', '.input-jumlah-tabel', function () {
    var rawVal = $(this).val();
    var jumlah = 1; // default jika kosong
    
    // Jangan ubah isi textbox kalau sedang dikosongkan oleh user (misal tekan Backspace)
    if (rawVal !== '') {
        jumlah = parseInt(rawVal);
        if (isNaN(jumlah) || jumlah < 1) {
            $(this).val(1);
            jumlah = 1;
        }
    }
    
    var $row   = $(this).closest('tr');
    var harga  = parseInt($row.attr('data-harga')) || 0;
    var subtot = harga * jumlah;
    $row.find('.subtotal-val').text(rupiah(subtot)).data('raw', subtot);
    hitungTotal();
});

// Jika user mengosongkan textbox lalu pindah (blur), balikin otomatis ke 1
$(document).on('blur', '.input-jumlah-tabel', function () {
    if ($(this).val() === '') {
        $(this).val(1);
        $(this).trigger('input');
    }
});

// ── Hapus Baris ────────────────────────────────────────────────────
$(document).on('click', '.btn-hapus-baris', function () {
    $(this).closest('tr').remove();

    // Renomor baris
    $('#bodyTransaksi tr[data-kode]').each(function (i) {
        $(this).find('td:first').text(i + 1);
    });

    // Tampilkan placeholder jika kosong
    if ($('#bodyTransaksi tr[data-kode]').length === 0) {
        noRow = 0;
        $('#rowKosong').show();
    }

    hitungTotal();
});

// ── Tombol Bayar → simpan ke DB via AJAX POST ──────────────────────
$('#btnBayar').on('click', function () {
    var barisData = [];
    var total     = 0;

    $('#bodyTransaksi tr[data-kode]').each(function () {
        // Gunakan attr() bukan data(), karena jQuery data() otomatis
        // mengonversi string angka '26030219' menjadi Integer.
        // Controller Laravel mengharuskan string.
        var kode   = $(this).attr('data-kode');
        var jumlah = parseInt($(this).find('.input-jumlah-tabel').val()) || 1;
        var subtot = parseInt($(this).find('.subtotal-val').data('raw')) || 0;
        barisData.push({ kode: kode, jumlah: jumlah });
        total += subtot;
    });

    if (barisData.length === 0) return;

    // SK1: Spinner & disable
    var $btn = $(this);
    $btn.prop('disabled', true)
        .html('<span class="spinner-grow spinner-grow-sm mr-1"></span> Memproses...');

    // AJAX POST ke /pos/store
    $.ajax({
        url:         '{{ route("pos.store") }}',
        method:      'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            _token: '{{ csrf_token() }}',
            items:  barisData,
            total:  total
        }),
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                // SWAL2 sukses
                Swal.fire({
                    icon:             'success',
                    title:            'Pembayaran Berhasil!',
                    html:             '<p>Transaksi <b>#' + res.id_penjualan + '</b> berhasil disimpan.</p>' +
                                      '<p class="mb-0">Total: <b>' + rupiah(total) + '</b></p>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#28a745',
                    timer:             4000,
                    timerProgressBar:  true,
                }).then(function () {
                    // Kosongkan semua data halaman
                    resetHalaman();
                });
            }
        },
        error: function (xhr) {
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
            Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
        },
        complete: function () {
            $btn.prop('disabled', false)
                .html('<i class="mdi mdi-cash mr-1"></i> Bayar');
        }
    });
});

// ── Reset seluruh halaman ──────────────────────────────────────────
function resetHalaman() {
    // Reset form input
    $('#inputKode').val('').focus();
    $('#inputNama').val('');
    $('#inputHarga').val('');
    $('#inputJumlah').val(1);
    $('#statusBarang').removeClass('found notfound').addClass('idle');
    barangDitemukan = false;
    hargaBarang     = 0;
    noRow           = 0;
    updateBtnTambahkan();

    // Kosongkan tabel
    $('#bodyTransaksi').html(
        '<tr id="rowKosong">' +
            '<td colspan="7" class="text-center text-muted py-4">' +
                '<i class="mdi mdi-cart-outline" style="font-size:2rem;"></i>' +
                '<br><small>Belum ada barang. Scan atau ketik kode barang di atas.</small>' +
            '</td>' +
        '</tr>'
    );

    $('#displayTotal').text('Rp 0');
    $('#btnBayar').prop('disabled', true);
}
</script>
@endpush
