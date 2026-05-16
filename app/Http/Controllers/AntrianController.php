<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AntrianController extends Controller
{
    // ─── GUEST: Tampilkan form daftar antrian ────────────────────
    public function guestForm()
    {
        $vendors = Vendor::orderBy('nama_vendor')->get();
        return view('pages.antrian.guest', compact('vendors'));
    }

    // ─── GUEST: Simpan antrian baru ──────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:100',
            'idvendor' => 'required|exists:vendor,idvendor',
        ]);

        // Hitung nomor urut hari ini per vendor
        $lastNomor = Antrian::hariIni()
            ->vendor($request->idvendor)
            ->max('nomor_antrian') ?? 0;

        $antrian = Antrian::create([
            'idvendor'       => $request->idvendor,
            'nomor_antrian'  => $lastNomor + 1,
            'nama'           => $request->nama,
            'status'         => 'menunggu',
        ]);

        // Update cache untuk vendor ini
        $this->updateCache($request->idvendor);

        return redirect()->route('antrian.tiket', $antrian->id);
    }

    // ─── GUEST: Halaman tiket personal ───────────────────────────
    public function tiket($id)
    {
        $antrian = Antrian::with('vendor')->findOrFail($id);

        // Hitung posisi dan estimasi waktu tunggu langsung di server
        $menunggu = Antrian::hariIni()
            ->vendor($antrian->idvendor)
            ->menunggu()
            ->orderBy('nomor_antrian')
            ->pluck('nomor_antrian')
            ->toArray();

        $posisiSaya   = array_search($antrian->nomor_antrian, $menunggu); // 0-based, false jika tidak ada
        $posisi       = $posisiSaya !== false ? $posisiSaya + 1 : null;   // 1-based
        $sisaOrang    = $posisiSaya !== false ? $posisiSaya : 0;           // orang di depan
        $estimasiMnt  = $sisaOrang * 5; // 5 menit per orang
        $totalMenunggu = count($menunggu);

        return view('pages.antrian.tiket', compact(
            'antrian', 'posisi', 'sisaOrang', 'estimasiMnt', 'totalMenunggu'
        ));
    }

    // ─── PETUGAS/ADMIN: Dashboard antrian ────────────────────────
    public function adminPanel(Request $request)
    {
        $vendors        = Vendor::orderBy('nama_vendor')->get();
        $activeVendorId = $request->query('vendor', null); // null = global
        $activeVendor   = $activeVendorId ? Vendor::find($activeVendorId) : null;

        // Jika global (null), query semua vendor; jika specific, filter per vendor
        $baseMenunggu  = $activeVendorId
            ? Antrian::hariIni()->vendor($activeVendorId)->menunggu()
            : Antrian::hariIni()->menunggu();
        $baseTerlewat  = $activeVendorId
            ? Antrian::hariIni()->vendor($activeVendorId)->terlewat()
            : Antrian::hariIni()->terlewat();
        $baseDipanggil = $activeVendorId
            ? Antrian::hariIni()->vendor($activeVendorId)->dipanggil()
            : Antrian::hariIni()->dipanggil();

        $menunggu  = $baseMenunggu->orderBy('id')->with('vendor')->get();
        $terlewat  = $baseTerlewat->orderBy('id')->with('vendor')->get();
        $dipanggil = $baseDipanggil->orderByDesc('called_at')->with('vendor')->first();

        return view('pages.antrian.admin', compact(
            'vendors', 'activeVendor', 'menunggu', 'terlewat', 'dipanggil'
        ));
    }

    // ─── ACTION: Panggil nomor ───────────────────────────────────
    public function panggil(Request $request, $id)
    {
        $antrian = Antrian::findOrFail($id);
        $antrian->update(['status' => 'dipanggil', 'called_at' => now()]);
        $this->updateCache($antrian->idvendor);

        return $this->redirectBackToAdmin($request, $antrian, 'Nomor ' . $antrian->nomor_antrian . ' (' . $antrian->nama . ') dipanggil!');
    }

    // ─── ACTION: Panggil ulang yang terlewat ─────────────────────
    public function panggilTerlewat(Request $request, $id)
    {
        $antrian = Antrian::findOrFail($id);
        $antrian->update(['status' => 'dipanggil', 'called_at' => now()]);
        $this->updateCache($antrian->idvendor);

        return $this->redirectBackToAdmin($request, $antrian, 'Nomor ' . $antrian->nomor_antrian . ' (' . $antrian->nama . ') dipanggil ulang!');
    }

    // ─── ACTION: Ulangi panggilan (nomor apapun) ─────────────────
    public function ulangi(Request $request, $id)
    {
        $antrian = Antrian::findOrFail($id);
        $antrian->update(['status' => 'dipanggil', 'called_at' => now()]);
        $this->updateCache($antrian->idvendor);

        return $this->redirectBackToAdmin($request, $antrian, 'Panggilan diulangi: Nomor ' . $antrian->nomor_antrian . ' (' . $antrian->nama . ').');
    }

    // ─── ACTION: Tandai terlewat ──────────────────────────────────
    public function terlewat(Request $request, $id)
    {
        $antrian = Antrian::findOrFail($id);
        $antrian->update(['status' => 'terlewat']);
        $this->updateCache($antrian->idvendor);

        return $this->redirectBackToAdmin($request, $antrian, 'Nomor ' . $antrian->nomor_antrian . ' (' . $antrian->nama . ') ditandai terlewat.');
    }

    // ─── ACTION: Tandai selesai ───────────────────────────────────
    public function selesai(Request $request, $id)
    {
        $antrian = Antrian::findOrFail($id);
        $antrian->update(['status' => 'selesai']);
        $this->updateCache($antrian->idvendor);

        return $this->redirectBackToAdmin($request, $antrian, 'Nomor ' . $antrian->nomor_antrian . ' (' . $antrian->nama . ') selesai.');
    }

    // ─── HELPER: Redirect kembali ke view yang sama ───────────────
    // Jika JS kirim _vendor_redirect='', berarti user sedang di mode Global → redirect tanpa vendor
    // Jika JS kirim _vendor_redirect='3', redirect ke vendor 3
    // Jika tidak ada (Blade form biasa), redirect ke vendor antrian
    private function redirectBackToAdmin(Request $request, $antrian, string $msg)
    {
        $vendorRedirect = $request->input('_vendor_redirect', null);

        if ($vendorRedirect === '') {
            // Mode Global — kembali ke global
            return redirect()->route('antrian.admin')->with('success', $msg);
        }

        $targetVendor = $vendorRedirect ?: $antrian->idvendor;
        return redirect()->route('antrian.admin', ['vendor' => $targetVendor])->with('success', $msg);
    }

    // ─── PAPAN: Halaman display publik ───────────────────────────
    public function papan(Request $request)
    {
        $vendors  = Vendor::orderBy('nama_vendor')->get();
        $vendorId = $request->query('vendor');
        $vendor   = $vendorId ? Vendor::find($vendorId) : null;
        return view('pages.antrian.papan', compact('vendors', 'vendor', 'vendorId'));
    }

    // ─── SSE: Streaming endpoint ─────────────────────────────────
    public function stream(Request $request)
    {
        $vendorId = $request->query('vendor');
        $cacheKey = $vendorId ? "antrian_state_{$vendorId}" : 'antrian_state_all';

        // Lepas session lock sebelum stream
        session_write_close();

        $data = Cache::get($cacheKey);

        // Jika cache kosong (misal setelah cache:clear), rebuild dari DB
        if (!$data) {
            if ($vendorId) {
                $this->updateCache($vendorId);
            } else {
                $this->updateCacheAll();
            }
            $data = Cache::get($cacheKey, [
                'dipanggil'       => null,
                'menunggu'        => [],
                'terlewat'        => [],
                'jumlah_menunggu' => 0,
                'updated_at'      => now()->toDateTimeString(),
            ]);
        }

        // Short-lived SSE: kirim data sekali lalu tutup.
        // Browser EventSource akan auto-reconnect setiap `retry` ms.
        // Ini WAJIB untuk Apache agar tidak menahan seluruh response.
        return response(
            "retry: 2000\nevent: antrian-update\ndata: " . json_encode($data) . "\n\n",
            200,
            [
                'Content-Type'      => 'text/event-stream',
                'Cache-Control'     => 'no-cache, no-store',
                'X-Accel-Buffering' => 'no',
                'Connection'        => 'close',
            ]
        );
    }


    // ─── HELPER: Update cache state per vendor ───────────────────
    private function updateCache($idvendor): void
    {
        $dipanggil = Antrian::hariIni()->vendor($idvendor)->dipanggil()
            ->orderByDesc('called_at')->first();

        $menunggu = Antrian::hariIni()->vendor($idvendor)->menunggu()
            ->orderBy('id')
            ->with('vendor')
            ->get()
            ->map(fn($a) => [
                'id'             => $a->id,
                'nomor'          => $a->nomor_antrian,
                'nama'           => $a->nama,
                'vendor'         => optional($a->vendor)->nama_vendor,
            ])->toArray();

        $terlewat = Antrian::hariIni()->vendor($idvendor)->terlewat()
            ->orderBy('id')
            ->get()
            ->map(fn($a) => [
                'id'    => $a->id,
                'nomor' => $a->nomor_antrian,
                'nama'  => $a->nama,
            ])->toArray();

        $vendor = Vendor::find($idvendor);

        Cache::put("antrian_state_{$idvendor}", [
            'dipanggil' => $dipanggil ? [
                'id'        => $dipanggil->id,
                'nomor'     => $dipanggil->nomor_antrian,
                'nama'      => $dipanggil->nama,
                'vendor'    => optional($dipanggil->vendor)->nama_vendor,
                'called_at' => $dipanggil->called_at ? $dipanggil->called_at->toDateTimeString() : null,
            ] : null,
            'menunggu'        => $menunggu,
            'terlewat'        => $terlewat,
            'jumlah_menunggu' => count($menunggu),
            'nama_vendor'     => optional($vendor)->nama_vendor,
            'updated_at'      => now()->toDateTimeString(),
        ], now()->addHours(12));

        // ⚡ Update juga cache global (dipakai tab "Semua")
        $this->updateCacheAll();
    }

    // ─── HELPER: Update cache global semua vendor ────────────────
    private function updateCacheAll(): void
    {
        // Ambil antrian dipanggil terbaru dari semua vendor
        $dipanggil = Antrian::hariIni()->dipanggil()
            ->orderByDesc('called_at')
            ->with('vendor')
            ->first();

        $menunggu = Antrian::hariIni()->menunggu()
            ->orderBy('id')
            ->with('vendor')
            ->get()
            ->map(fn($a) => [
                'id'     => $a->id,
                'nomor'  => $a->nomor_antrian,
                'nama'   => $a->nama,
                'vendor' => optional($a->vendor)->nama_vendor,
            ])->toArray();

        $terlewat = Antrian::hariIni()->terlewat()
            ->orderBy('id')
            ->with('vendor')
            ->get()
            ->map(fn($a) => [
                'id'     => $a->id,
                'nomor'  => $a->nomor_antrian,
                'nama'   => $a->nama,
                'vendor' => optional($a->vendor)->nama_vendor,
            ])->toArray();

        Cache::put('antrian_state_all', [
            'dipanggil' => $dipanggil ? [
                'id'        => $dipanggil->id,
                'nomor'     => $dipanggil->nomor_antrian,
                'nama'      => $dipanggil->nama,
                'vendor'    => optional($dipanggil->vendor)->nama_vendor,
                'called_at' => $dipanggil->called_at ? $dipanggil->called_at->toDateTimeString() : null,
            ] : null,
            'menunggu'        => $menunggu,
            'terlewat'        => $terlewat,
            'jumlah_menunggu' => count($menunggu),
            'nama_vendor'     => 'Semua Vendor',
            'updated_at'      => now()->toDateTimeString(),
        ], now()->addHours(12));
    }
}
