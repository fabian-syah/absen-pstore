<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminGajiUserController extends Controller
{
    public function index()
    {
        $users = \App\Models\AdminGajiUser::latest()->get();
        return view('admin_gaji.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        \App\Models\AdminGajiUser::create($request->all());

        return redirect()->back()->with('success', 'Data User berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $user = \App\Models\AdminGajiUser::findOrFail($id);
        $user->update($request->all());

        return redirect()->back()->with('success', 'Data User berhasil diupdate!');
    }

    public function destroy($id)
    {
        $user = \App\Models\AdminGajiUser::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'Data User berhasil dihapus!');
    }
}
