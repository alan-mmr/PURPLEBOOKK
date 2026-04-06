@extends('layouts.main')

@section('title', 'Pesan Sekarang - PURPLEBOOK')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white mr-2">
            <i class="mdi mdi-cart"></i>
        </span> Pesan Menu
    </h3>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="row">
    {{-- ── Kiri: Pilih Vendor & Menu ── --}}
    <div class="col-md-7 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title"><i class="mdi mdi-store text-primary"></i> Pilih Vendor & Menu</h4>

                {{-- Select Vendor --}}
                <div class="form-group">
                    <label class="font-weight-bold">Vendor / Toko</label>
                    <select id="selectVendor" class="form-control">
                        <option value="">-- Pilih Vendor --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->idvendor }}">{{ $v->nama_vendor }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Loading indicator --}}
                <div id="menuLoading" class="text-center py-3" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Memuat menu...</p>
                </div>

                {{-- Daftar Menu (muncul via AJAX) --}}
                <div id="menuContainer" style="display:none;">
                    <label class="font-weight-bold">Pilih Menu</label>
                    <div id="menuList" class="row"></div>
                </div>

                <div id="emptyMenu" class="text-center text-muted py-4" style="display:none;">
                    <i class="mdi mdi-food-off mdi-48px"></i>
                    <p>Vendor ini belum memiliki menu.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Kanan: Keranjang & Form Pesan ── --}}
    <div class="col-md-5 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title"><i class="mdi mdi-receipt text-success"></i> Keranjang</h4>

                <div id="cartEmpty" class="text-center text-muted py-3">
                    <i class="mdi mdi-cart-off mdi-36px"></i>
                    <p>Belum ada item dipilih.</p>
                </div>

                <table id="cartTable" class="table table-sm" style="display:none;">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cartBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="font-weight-bold">Total</td>
                            <td class="text-right font-weight-bold text-primary" id="cartTotal">Rp 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <hr>

                <form action="{{ route('pesan.store') }}" method="POST" id="formPesan">
                    @csrf
                    <input type="hidden" name="idvendor" id="hiddenVendor">
                    <div id="hiddenItems"></div>

                    <button type="submit" id="btnPesan"
                        class="btn btn-gradient-primary btn-block font-weight-bold"
                        disabled>
                        <i class="mdi mdi-credit-card-check"></i> Lanjut ke Pembayaran
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const cart = {}; // { idmenu: { nama, harga, jumlah } }

// ── Load menu saat pilih vendor ─────────────────────────────────
$('#selectVendor').on('change', function () {
    const idvendor = $(this).val();
    if (!idvendor) {
        $('#menuContainer, #emptyMenu').hide();
        $('#menuList').html('');
        resetCart();
        return;
    }

    $('#menuLoading').show();
    $('#menuContainer, #emptyMenu').hide();
    $('#hiddenVendor').val(idvendor);

    $.ajax({
        url: '{{ route("pesan.getMenu") }}',
        data: { idvendor },
        success: function (menus) {
            $('#menuLoading').hide();
            if (!menus.length) {
                $('#emptyMenu').show();
                return;
            }
            let html = '';
            menus.forEach(m => {
                html += `
                <div class="col-6 mb-3">
                    <div class="card border h-100 menu-card" style="cursor:pointer;" data-id="${m.idmenu}" data-nama="${m.nama_menu}" data-harga="${m.harga}">
                        <div class="card-body p-2 text-center">
                            ${m.path_gambar ? `<img src="${m.path_gambar}" class="mb-2 rounded" style="width:100%;height:80px;object-fit:cover;">` : '<i class="mdi mdi-food mdi-36px text-muted"></i>'}
                            <p class="mb-1 font-weight-bold small">${m.nama_menu}</p>
                            <span class="badge badge-primary">Rp ${m.harga.toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                </div>`;
            });
            $('#menuList').html(html);
            $('#menuContainer').show();
        },
        error: function () {
            $('#menuLoading').hide();
            alert('Gagal memuat menu. Coba lagi.');
        }
    });
});

// ── Klik menu → masuk keranjang ────────────────────────────────
$(document).on('click', '.menu-card', function () {
    const id    = $(this).data('id');
    const nama  = $(this).data('nama');
    const harga = $(this).data('harga');

    if (cart[id]) {
        cart[id].jumlah++;
    } else {
        cart[id] = { nama, harga, jumlah: 1 };
    }
    renderCart();
});

// ── Render keranjang ────────────────────────────────────────────
function renderCart() {
    const keys = Object.keys(cart);
    if (!keys.length) {
        $('#cartEmpty').show();
        $('#cartTable').hide();
        $('#btnPesan').prop('disabled', true);
        return;
    }

    let total = 0, rows = '', items = '';
    keys.forEach(id => {
        const { nama, harga, jumlah } = cart[id];
        const subtotal = harga * jumlah;
        total += subtotal;
        rows += `
        <tr>
            <td>${nama}</td>
            <td class="text-center">
                <div class="input-group input-group-sm justify-content-center" style="width:80px;margin:auto;">
                    <button class="btn btn-outline-secondary btn-sm" onclick="changeQty(${id}, -1)">-</button>
                    <span class="px-2 align-self-center">${jumlah}</span>
                    <button class="btn btn-outline-secondary btn-sm" onclick="changeQty(${id}, 1)">+</button>
                </div>
            </td>
            <td class="text-right">Rp ${subtotal.toLocaleString('id-ID')}</td>
            <td><button class="btn btn-sm btn-outline-danger" onclick="removeItem(${id})"><i class="mdi mdi-delete"></i></button></td>
        </tr>`;
        items += `<input type="hidden" name="items[${id}][idmenu]" value="${id}">
                  <input type="hidden" name="items[${id}][jumlah]" value="${jumlah}">`;
    });

    $('#cartBody').html(rows);
    $('#cartTotal').text('Rp ' + total.toLocaleString('id-ID'));
    $('#hiddenItems').html(items);
    $('#cartEmpty').hide();
    $('#cartTable').show();
    $('#btnPesan').prop('disabled', false);
}

function changeQty(id, delta) {
    if (!cart[id]) return;
    cart[id].jumlah += delta;
    if (cart[id].jumlah <= 0) delete cart[id];
    renderCart();
}

function removeItem(id) {
    delete cart[id];
    renderCart();
}

function resetCart() {
    Object.keys(cart).forEach(k => delete cart[k]);
    renderCart();
    $('#hiddenVendor').val('');
}
</script>
@endpush
@endsection
