<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Load books with their category relationship
        $buku = Buku::with('kategori')->get();
        return view('pages.buku.index', compact('buku'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all categories for dropdown
        $kategori = Kategori::all();
        return view('pages.buku.form', compact('kategori'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:buku,kode',
            'judul' => 'required|string|max:255',
            'pengarang' => 'required|string|max:255',
            'idkategori' => 'required|exists:kategori,idkategori',
        ]);

        Buku::create($validated);

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $buku = Buku::with('kategori')->findOrFail($id);
        return view('pages.buku.show', compact('buku'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $buku = Buku::findOrFail($id);
        $kategori = Kategori::all();
        return view('pages.buku.form', compact('buku', 'kategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $buku = Buku::findOrFail($id);

        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:buku,kode,' . $id . ',idbuku',
            'judul' => 'required|string|max:255',
            'pengarang' => 'required|string|max:255',
            'idkategori' => 'required|exists:kategori,idkategori',
        ]);

        $buku->update($validated);

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $buku = Buku::findOrFail($id);
        $buku->delete();

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil dihapus!');
    }
}
