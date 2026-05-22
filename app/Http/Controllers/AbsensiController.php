<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\KartuNfc;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class AbsensiController extends Controller
{
    // ─── Halaman Scanner NFC / QR ─────────────────────────────────
    public function scanner()
    {
        return view('pages.absensi.scan');
    }

    // ─── POST: Catat Absensi ──────────────────────────────────────
    // Menerima serial_number (dari NFC tap atau QR scan) + metode
    public function catat(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string',
            'metode'        => 'required|in:nfc,qr',
        ]);

        // 1. Cari kartu aktif berdasarkan serial
        $kartu = KartuNfc::with('user')
            ->where('serial_number', $request->serial_number)
            ->where('is_active', true)
            ->first();

        if (!$kartu) {
            return response()->json([
                'success' => false,
                'pesan'   => 'Kartu tidak terdaftar atau sudah dinonaktifkan.',
            ], 404);
        }

        // 2. Cek apakah sudah absen hari ini
        $sudah = Absensi::where('user_id', $kartu->user_id)
            ->whereDate('created_at', today())
            ->exists();

        if ($sudah) {
            return response()->json([
                'success' => false,
                'pesan'   => $kartu->user->name . ' sudah absen hari ini.',
                'nama'    => $kartu->user->name,
            ], 409);
        }

        // 3. Simpan absensi
        $absensi = Absensi::create([
            'user_id'      => $kartu->user_id,
            'kartu_nfc_id' => $kartu->id,
            'metode'       => $request->metode,
            'status'       => 'hadir',
            'scanned_by'   => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'pesan'   => $kartu->user->name . ' berhasil absen!',
            'nama'    => $kartu->user->name,
            'metode'  => $request->metode,
            'waktu'   => $absensi->created_at->format('H:i:s'),
        ]);
    }

    // ─── Halaman Kelola Kartu NFC per User ────────────────────────
    public function daftarKartu()
    {
        $users  = User::orderBy('name')->get();
        $kartus = KartuNfc::with('user')->orderByDesc('id')->get();
        return view('pages.absensi.kartu', compact('users', 'kartus'));
    }

    // ─── POST: Daftarkan Kartu Baru ───────────────────────────────
    public function simpanKartu(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'serial_number' => 'required|string|max:100|unique:kartu_nfc,serial_number',
            'tipe'          => 'required|in:nfc,qr',
            'label'         => 'nullable|string|max:100',
        ]);

        KartuNfc::create([
            'user_id'       => $request->user_id,
            'serial_number' => $request->serial_number,
            'tipe'          => $request->tipe,
            'label'         => $request->label ?? 'Kartu ' . now()->format('d/m/Y'),
            'is_active'     => true,
        ]);

        return redirect()->route('absensi.kartu')
            ->with('success', 'Kartu berhasil didaftarkan!');
    }

    // ─── DELETE: Nonaktifkan / Hapus Kartu ───────────────────────
    public function hapusKartu($id)
    {
        $kartu = KartuNfc::findOrFail($id);
        $kartu->delete();
        return redirect()->route('absensi.kartu')
            ->with('success', 'Kartu berhasil dihapus.');
    }

    // ─── DELETE: Hapus Record Absensi (admin only) ────────────────
    public function hapusAbsensi($id)
    {
        $absensi = Absensi::findOrFail($id);
        $absensi->delete();

        return redirect()->back()
            ->with('success', 'Record absensi berhasil dihapus.');
    }

    // ─── Halaman Riwayat Absensi ──────────────────────────────────
    public function riwayat(Request $request)
    {
        $query = Absensi::with(['user', 'petugas', 'kartuNfc'])
            ->orderByDesc('created_at');

        // Filter tanggal
        if ($request->tanggal) {
            $query->whereDate('created_at', $request->tanggal);
        } else {
            $query->whereDate('created_at', today());
        }

        // Filter user
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter metode
        if ($request->metode) {
            $query->where('metode', $request->metode);
        }

        $absensis = $query->get();
        $users    = User::orderBy('name')->get();

        return view('pages.absensi.riwayat', compact('absensis', 'users'));
    }

    // ─── Generate QR Code untuk User ─────────────────────────────
    // Setiap user punya 1 token QR unik — jika belum ada, otomatis dibuat
    public function generateQr($userId = null)
    {
        $userId = $userId ?? Auth::id();
        $user   = User::findOrFail($userId);

        // Cari kartu QR yang sudah ada, jika belum ada buat baru
        $kartu = KartuNfc::where('user_id', $user->id)
            ->where('tipe', 'qr')
            ->where('is_active', true)
            ->first();

        if (!$kartu) {
            $kartu = KartuNfc::create([
                'user_id'       => $user->id,
                'serial_number' => 'QR-' . strtoupper(Str::random(12)),
                'tipe'          => 'qr',
                'label'         => 'QR Absensi - ' . $user->name,
                'is_active'     => true,
            ]);
        }

        // Generate QR code image (PNG, base64)
        $qrCode = QrCode::create($kartu->serial_number)
            ->setSize(300)
            ->setMargin(10);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        $qrBase64 = base64_encode($result->getString());

        return view('pages.absensi.qr-user', compact('user', 'kartu', 'qrBase64'));
    }
}
