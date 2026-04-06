<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\User;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * GET /vendor
     * Daftar semua vendor — hanya admin.
     */
    public function index()
    {
        $vendors = Vendor::with('user')->orderBy('nama_vendor')->get();
        return view('pages.vendor.index', compact('vendors'));
    }

    /**
     * GET /vendor/create
     */
    public function create()
    {
        // Ambil semua user ber-role vendor yang belum punya toko
        $users = User::where('role', 'vendor')
            ->whereNotIn('id', Vendor::whereNotNull('user_id')->pluck('user_id'))
            ->orderBy('name')
            ->get();

        return view('pages.vendor.form', compact('users'));
    }

    /**
     * POST /vendor
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_vendor' => 'required|string|max:255',
            'user_id'     => 'nullable|integer|exists:users,id',
        ]);

        Vendor::create($request->only('nama_vendor', 'user_id'));

        return redirect()->route('vendor.index')
            ->with('success', 'Vendor berhasil ditambahkan.');
    }

    /**
     * GET /vendor/{id}/edit
     */
    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);

        // User vendor yang belum dipakai + user yang sekarang dipakai vendor ini
        $users = User::where('role', 'vendor')
            ->where(function ($q) use ($vendor) {
                $q->whereNotIn('id', Vendor::whereNotNull('user_id')->pluck('user_id'))
                  ->orWhere('id', $vendor->user_id);
            })
            ->orderBy('name')
            ->get();

        return view('pages.vendor.form', compact('vendor', 'users'));
    }

    /**
     * PUT /vendor/{id}
     */
    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);

        $request->validate([
            'nama_vendor' => 'required|string|max:255',
            'user_id'     => 'nullable|integer|exists:users,id',
        ]);

        $vendor->update($request->only('nama_vendor', 'user_id'));

        return redirect()->route('vendor.index')
            ->with('success', 'Vendor berhasil diperbarui.');
    }

    /**
     * DELETE /vendor/{id}
     */
    public function destroy($id)
    {
        try {
            $vendor = Vendor::findOrFail($id);
            $vendor->delete();

            return redirect()->route('vendor.index')
                ->with('success', 'Vendor berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Menangkap error foreign key / restrict constraint
            if ($e->getCode() == 23503 || $e->getCode() == 23000 || $e->getCode() == '23001') {
                return redirect()->route('vendor.index')
                    ->with('error', 'Gagal: Vendor tidak bisa dihapus karena masih terhubung dengan riwayat pesanan pelanggan. Hapus pesanannya terlebih dahulu, atau biarkan vendor tersimpan sebagai riwayat.');
            }
            // Lempar error jika masalah lain
            throw $e;
        }
    }
}
