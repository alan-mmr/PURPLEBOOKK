<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        // Hanya admin yang bisa mengelola user
        $users = User::orderBy('name')->get();
        return view('pages.user.index', compact('users'));
    }

    public function create()
    {
        return view('pages.user.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role'     => ['required', Rule::in(['admin', 'vendor', 'user'])],
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return redirect()->route('user.index')->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('pages.user.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'  => ['required', Rule::in(['admin', 'vendor', 'user'])],
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ];

        // Jika password diisi, maka update password
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('user.index')->with('success', 'Akun pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // Jangan biarkan user menghapus dirinya sendiri
        if (auth()->id() === $user->id) {
            return redirect()->route('user.index')->with('error', 'Tidak bisa menghapus akun yang sedang digunakan.');
        }

        try {
            $user->delete();
            return redirect()->route('user.index')->with('success', 'Akun berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('user.index')->with('error', 'Akun ini terkait dengan data lain (misal Vendor) sehingga tidak bisa dihapus.');
        }
    }
}
