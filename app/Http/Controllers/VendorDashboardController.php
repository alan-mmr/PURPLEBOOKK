<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorDashboardController extends Controller
{
    /**
     * GET /vendor/dashboard
     * Tampilkan pesanan paid milik vendor yang sedang login.
     * Filter ketat by idvendor — tidak ada kebocoran data vendor lain.
     */
    public function index()
    {
        // Ambil entitas vendor dari akun yang sedang login
        $vendor = Vendor::where('user_id', auth()->id())->first();

        if (!$vendor) {
            abort(403, 'Akun ini belum terhubung ke toko vendor manapun. Hubungi admin.');
        }

        $pesanans = Pesanan::where('idvendor', $vendor->idvendor)
            ->where('status_bayar', 'paid')
            ->with('detailPesanans.menu')
            ->latest()
            ->get();

        return view('pages.vendor.dashboard', compact('vendor', 'pesanans'));
    }

    // ─────────────────────────────────────────────────────────────
    // PRAKTIKUM 2B — QR Code Scanner
    // ─────────────────────────────────────────────────────────────

    /**
     * GET /vendor/scan-qr
     * Tampilkan halaman scanner QR Code untuk vendor.
     */
    public function scanQr()
    {
        $vendor = Vendor::where('user_id', auth()->id())->first();

        if (!$vendor) {
            abort(403, 'Akun ini belum terhubung ke toko vendor manapun. Hubungi admin.');
        }

        return view('pages.vendor.scan-qr', compact('vendor'));
    }

    /**
     * GET /vendor/lookup-pesanan?id=XXX
     * AJAX endpoint — cari pesanan berdasarkan idpesanan dari QR Code.
     *
     * QR Content format: PURPLEBOOK-ORDER-{idpesanan}|{transaction_id}|...
     * Filter ketat: hanya tampilkan jika pesanan milik vendor yang login.
     *
     * Return JSON:
     *   200: { idpesanan, nama_pemesan, status_bayar, items: [{nama_menu, jumlah, subtotal}] }
     *   403: { error: '...' }  jika pesanan bukan milik vendor ini
     *   404: { error: '...' }  jika pesanan tidak ditemukan
     */
    public function lookupPesanan(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        // Parse QR content: ambil idpesanan dari format PURPLEBOOK-ORDER-{id}|...
        $raw = trim($request->id);

        // Support dua format: QR string lengkap atau id langsung
        if (str_starts_with($raw, 'PURPLEBOOK-ORDER-')) {
            // Format QR: "PURPLEBOOK-ORDER-42|ORDER-XYZ|Guest_...|Rp..."
            $parts     = explode('|', $raw);
            $idPesanan = (int) str_replace('PURPLEBOOK-ORDER-', '', $parts[0]);
        } else {
            $idPesanan = (int) $raw;
        }

        if (!$idPesanan) {
            return response()->json(['error' => 'Format QR Code tidak valid'], 422);
        }

        // Ambil vendor yang login
        $vendor = Vendor::where('user_id', auth()->id())->first();
        if (!$vendor) {
            return response()->json(['error' => 'Akun vendor tidak ditemukan'], 403);
        }

        // Cari pesanan dengan relasi detail + menu
        $pesanan = Pesanan::with('detailPesanans.menu')
            ->find($idPesanan);

        if (!$pesanan) {
            return response()->json(['error' => 'Pesanan #' . $idPesanan . ' tidak ditemukan'], 404);
        }

        // Filter ketat: pastikan pesanan ini milik vendor yang sedang login
        if ($pesanan->idvendor !== $vendor->idvendor) {
            return response()->json(['error' => 'Pesanan ini bukan milik toko Anda'], 403);
        }

        // Susun daftar item menu
        $items = $pesanan->detailPesanans->map(function ($d) {
            return [
                'nama_menu' => $d->menu->nama_menu ?? '(menu dihapus)',
                'jumlah'    => $d->jumlah,
                'subtotal'  => $d->subtotal,
            ];
        });

        return response()->json([
            'idpesanan'    => $pesanan->idpesanan,
            'nama_pemesan' => $pesanan->nama_pemesan,
            'total_harga'  => $pesanan->total_harga,
            'status_bayar' => $pesanan->status_bayar,
            'items'        => $items,
        ]);
    }
}
