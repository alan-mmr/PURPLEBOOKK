<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WilayahController extends Controller
{
    // ── Halaman AJAX (jQuery) ──────────────────────────────────────
    public function ajax()
    {
        // Ambil semua provinsi untuk populate select Level 1
        $provinsi = DB::table('reg_provinces')->orderBy('name')->get();
        return view('pages.administrasi.ajax', compact('provinsi'));
    }

    // ── Halaman Axios ──────────────────────────────────────────────
    public function axios()
    {
        // Ambil semua provinsi untuk populate select Level 1
        $provinsi = DB::table('reg_provinces')->orderBy('name')->get();
        return view('pages.administrasi.axios', compact('provinsi'));
    }

    // ── API: Ambil Kota/Kabupaten by Provinsi ─────────────────────
    public function getKota(Request $request)
    {
        $provinsiId = $request->query('province_id');
        $kota = DB::table('reg_regencies')
            ->where('province_id', $provinsiId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($kota);
    }

    // ── API: Ambil Kecamatan by Kota/Kabupaten ────────────────────
    public function getKecamatan(Request $request)
    {
        $regencyId = $request->query('regency_id');
        $kecamatan = DB::table('reg_districts')
            ->where('regency_id', $regencyId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($kecamatan);
    }

    // ── API: Ambil Kelurahan/Desa by Kecamatan ────────────────────
    public function getKelurahan(Request $request)
    {
        $districtId = $request->query('district_id');
        $kelurahan = DB::table('reg_villages')
            ->where('district_id', $districtId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($kelurahan);
    }
}
