<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Vendor;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    private function getCurrentVendor()
    {
        // Cari vendor yang terkait dengan user login saat ini
        return Vendor::where('user_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $vendor = $this->getCurrentVendor();
        $menus = Menu::where('idvendor', $vendor->idvendor)->get();
        
        return view('pages.menu.index', compact('menus', 'vendor'));
    }

    public function create()
    {
        $vendor = $this->getCurrentVendor();
        return view('pages.menu.form', compact('vendor'));
    }

    public function store(Request $request)
    {
        $vendor = $this->getCurrentVendor();

        $request->validate([
            'nama_menu' => 'required|string|max:255',
            'harga'     => 'required|integer|min:0',
        ]);

        Menu::create([
            'nama_menu' => $request->nama_menu,
            'harga'     => $request->harga,
            'idvendor'  => $vendor->idvendor,
            'path_gambar' => ''
        ]);

        return redirect()->route('menu.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $vendor = $this->getCurrentVendor();
        // Pastikan menu milik vendor ini
        $menu = Menu::where('idmenu', $id)->where('idvendor', $vendor->idvendor)->firstOrFail();
        
        return view('pages.menu.form', compact('menu', 'vendor'));
    }

    public function update(Request $request, $id)
    {
        $vendor = $this->getCurrentVendor();
        $menu = Menu::where('idmenu', $id)->where('idvendor', $vendor->idvendor)->firstOrFail();

        $request->validate([
            'nama_menu' => 'required|string|max:255',
            'harga'     => 'required|integer|min:0',
        ]);

        $menu->update([
            'nama_menu' => $request->nama_menu,
            'harga'     => $request->harga,
        ]);

        return redirect()->route('menu.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $vendor = $this->getCurrentVendor();
        $menu = Menu::where('idmenu', $id)->where('idvendor', $vendor->idvendor)->firstOrFail();
        
        // Asumsi jika menu dihapus, tapi ada detail pesanan nyangkut, akan kena catch restrict.
        // Sama polanya kayak penghapusan vendor.
        try {
            $menu->delete();
            return redirect()->route('menu.index')->with('success', 'Menu berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('menu.index')->with('error', 'Gagal: Menu tidak bisa dihapus karena sudah pernah dipesan pelanggan.');
        }
    }
}
