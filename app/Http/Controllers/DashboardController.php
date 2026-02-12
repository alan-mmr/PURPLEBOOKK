<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the dashboard.
     */
    public function index()
    {
        // Get statistics for dashboard
        $totalBuku = Buku::count();
        $totalKategori = Kategori::count();
        
        return view('pages.dashboard', compact('totalBuku', 'totalKategori'));
    }
}
