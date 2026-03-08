<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KotaController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // SK4 — Halaman Kota (Select biasa + Select2)
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        return view('pages.kota.index');
    }
}
