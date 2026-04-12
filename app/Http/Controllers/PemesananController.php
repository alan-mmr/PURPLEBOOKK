<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class PemesananController extends Controller
{
    public function __construct(protected MidtransService $midtrans) {}

    /**
     * GET /pesan
     * Halaman pemesanan publik — customer pilih vendor & menu.
     */
    public function index()
    {
        $vendors = Vendor::orderBy('nama_vendor')->get();
        return view('pages.pesan.index', compact('vendors'));
    }

    /**
     * GET /pesan/menu?idvendor=X
     * AJAX endpoint — kembalikan menu milik vendor yang dipilih.
     */
    public function getMenu(Request $request)
    {
        $request->validate(['idvendor' => 'required|integer|exists:vendor,idvendor']);

        $menus = \App\Models\Menu::where('idvendor', $request->idvendor)
            ->orderBy('nama_menu')
            ->get(['idmenu', 'nama_menu', 'harga', 'path_gambar']);

        return response()->json($menus);
    }

    /**
     * POST /pesan
     * Simpan pesanan, auto-generate nama guest, ambil Snap token.
     */
    public function store(Request $request)
    {
        $request->validate([
            'idvendor'       => 'required|integer|exists:vendor,idvendor',
            'items'          => 'required|array|min:1',
            'items.*.idmenu' => 'required|integer|exists:menu,idmenu',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // ── Auto-generate nama guest ────────────────────────────────────
            $last = Pesanan::where('nama_pemesan', 'like', 'Guest_%')
                ->orderByDesc('idpesanan')
                ->value('nama_pemesan');

            $nextNum   = $last ? ((int) substr($last, 6)) + 1 : 1;
            $guestName = 'Guest_' . str_pad($nextNum, 7, '0', STR_PAD_LEFT);

            // ── Hitung total dari item yang dipesan ─────────────────────────
            $total   = 0;
            $details = [];
            foreach ($request->items as $item) {
                $menu     = \App\Models\Menu::findOrFail($item['idmenu']);
                $subtotal = $menu->harga * $item['jumlah'];
                $total   += $subtotal;
                $details[] = [
                    'idmenu'   => $menu->idmenu,
                    'jumlah'   => $item['jumlah'],
                    'subtotal' => $subtotal,
                ];
            }

            // ── Buat pesanan header ─────────────────────────────────────────
            $pesanan = Pesanan::create([
                'nama_pemesan'   => $guestName,
                'idvendor'       => $request->idvendor,
                'total_harga'    => $total,
                'status'         => 'pending',
                'status_bayar'   => 'pending',
                // transaction_id = format unik untuk Midtrans order_id
                'transaction_id' => 'ORDER-' . strtoupper(uniqid()),
            ]);

            // ── Simpan detail item ──────────────────────────────────────────
            foreach ($details as $d) {
                DetailPesanan::create([
                    'idpesanan' => $pesanan->idpesanan,
                    'idmenu'    => $d['idmenu'],
                    'jumlah'    => $d['jumlah'],
                    'subtotal'  => $d['subtotal'],
                ]);
            }

            // ── Ambil Snap token dari Midtrans ──────────────────────────────
            $snapToken = $this->midtrans->createSnapToken($pesanan);
            $pesanan->update(['snap_token' => $snapToken]);

            DB::commit();

            return redirect()->route('pesan.bayar', $pesanan->idpesanan);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat pesanan: ' . $e->getMessage()]);
        }
    }

    /**
     * GET /pesan/{id}/status
     * Halaman konfirmasi status pembayaran setelah proses bayar.
     */
    public function status($id)
    {
        $pesanan = Pesanan::with('detailPesanans.menu', 'vendor')->findOrFail($id);

        // ── Generate QR Code hanya jika sudah PAID ──────────────────────
        $qrCodeDataUri = null;
        if ($pesanan->status_bayar === 'paid') {
            $qrContent = "PURPLEBOOK-ORDER-" . $pesanan->idpesanan
                       . "|" . $pesanan->transaction_id
                       . "|" . $pesanan->nama_pemesan
                       . "|Rp" . number_format($pesanan->total_harga, 0, ',', '.');

            // endroid/qr-code v5.x: constructor-based, tidak ada create() static method
            $qrCode = new QrCode(
                data: $qrContent,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 250,
                margin: 10,
            );

            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            $qrCodeDataUri = $result->getDataUri();
        }

        return view('pages.pesan.status', compact('pesanan', 'qrCodeDataUri'));
    }
}
