<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    /**
     * GET /customer
     * Tampilkan daftar semua customer (DataTables).
     */
    public function index()
    {
        $customers = Customer::orderBy('created_at', 'desc')->get();
        return view('pages.customer.index', compact('customers'));
    }

    /**
     * GET /customer/create
     * Form tambah customer + akses kamera.
     */
    public function create()
    {
        return view('pages.customer.form');
    }

    /**
     * POST /customer
     * Simpan customer baru.
     * foto_mode = 'blob' → simpan base64 decode ke kolom foto_blob.
     * foto_mode = 'file' → simpan file ke storage, path ke kolom foto_path.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string|max:255',
            'foto_data' => 'nullable|string', // base64 data URI dari canvas
            'foto_mode' => 'required|in:blob,file',
        ]);

        $customer = new Customer();
        $customer->nama = $request->nama;

        // Proses foto jika ada
        if ($request->filled('foto_data')) {
            $this->processFoto($customer, $request->foto_data, $request->foto_mode);
        }

        $customer->save();

        return redirect()->route('customer.index')
            ->with('success', 'Customer berhasil ditambahkan!');
    }

    /**
     * GET /customer/{id}/edit
     * Form edit customer + akses kamera.
     */
    public function edit(string $id)
    {
        $customer = Customer::findOrFail($id);
        return view('pages.customer.form', compact('customer'));
    }

    /**
     * PUT /customer/{id}
     * Update data customer.
     */
    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'nama'      => 'required|string|max:255',
            'foto_data' => 'nullable|string',
            'foto_mode' => 'required|in:blob,file',
        ]);

        $customer->nama = $request->nama;
        $newMode = $request->foto_mode;

        if ($request->filled('foto_data')) {
            // Kasus 1: User ambil foto BARU dari kamera
            if ($customer->foto_path) {
                Storage::disk('public')->delete($customer->foto_path);
            }
            $this->processFoto($customer, $request->foto_data, $newMode);

        } elseif ($customer->hasFoto()) {
            // Kasus 2: Tidak ada foto baru, cek apakah mode berubah
            $currentMode = $customer->foto_blob ? 'blob' : 'file';

            if ($newMode !== $currentMode) {
                // Konversi blob → file
                if ($newMode === 'file' && $customer->foto_blob) {
                    $binary   = base64_decode($customer->foto_blob);
                    $filename = 'customer_' . time() . '_' . uniqid() . '.jpg';
                    Storage::disk('public')->put('customers/' . $filename, $binary);
                    $customer->foto_path = 'customers/' . $filename;
                    $customer->foto_blob = null;
                }
                // Konversi file → blob
                elseif ($newMode === 'blob' && $customer->foto_path) {
                    $binary = Storage::disk('public')->get($customer->foto_path);
                    Storage::disk('public')->delete($customer->foto_path);
                    $customer->foto_blob = base64_encode($binary);
                    $customer->foto_path = null;
                }
            }
        }

        $customer->save();

        return redirect()->route('customer.index')
            ->with('success', 'Data customer berhasil diperbarui!');
    }

    /**
     * DELETE /customer/{id}
     * Hapus customer beserta fotonya.
     */
    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);

        // Hapus file foto dari storage jika ada
        if ($customer->foto_path) {
            Storage::disk('public')->delete($customer->foto_path);
        }

        $customer->delete();

        return redirect()->route('customer.index')
            ->with('success', 'Customer berhasil dihapus!');
    }

    /**
     * GET /customer/{id}/photo
     * Serve foto dari kolom blob (jika disimpan sebagai blob).
     * Mengembalikan binary image response.
     */
    public function showPhoto(string $id)
    {
        $customer = Customer::findOrFail($id);

        if (!$customer->foto_blob) {
            abort(404, 'Foto tidak ditemukan.');
        }

        // foto_blob berisi base64 string — decode ke binary JPEG
        $binary = base64_decode($customer->foto_blob);

        return response($binary, 200)
            ->header('Content-Type', 'image/jpeg')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Helper: Proses foto dari base64 data URI.
     * Mendukung dua mode penyimpanan: blob (database) atau file (disk).
     */
    private function processFoto(Customer $customer, string $dataUri, string $mode): void
    {
        // Extract base64 payload dari data URI
        // Format: data:image/jpeg;base64,/9j/4AAQ...
        $parts      = explode(',', $dataUri, 2);
        $base64Data = $parts[1] ?? $parts[0];
        $binary     = base64_decode($base64Data);

        if ($mode === 'blob') {
            // Simpan sebagai base64 TEXT ke database (menghindari error UTF-8 PostgreSQL)
            $customer->foto_blob = $base64Data;
            $customer->foto_path = null;
        } else {
            // Simpan binary ke disk: storage/app/public/customers/
            $filename = 'customer_' . time() . '_' . uniqid() . '.jpg';
            Storage::disk('public')->put('customers/' . $filename, $binary);
            $customer->foto_path = 'customers/' . $filename;
            $customer->foto_blob = null;
        }
    }
}
