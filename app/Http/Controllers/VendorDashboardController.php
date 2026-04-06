<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\Vendor;

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
}
