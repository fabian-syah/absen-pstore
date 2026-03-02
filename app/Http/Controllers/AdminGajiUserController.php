<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminGajiUser;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AdminGajiUserController extends Controller
{
    /**
     * Mendapatkan atau membuat cabang khusus "Data User (Admin Gaji)"
     */
    private function getOrCreateBranch()
    {
        return Branch::firstOrCreate(
            ['name' => 'Data User (Admin Gaji)'],
            [
                'address' => 'Cabang Khusus untuk Data User Admin Gaji',
                'is_active' => true,
            ]
        );
    }

    public function index()
    {
        $users = AdminGajiUser::latest()->get();
        return view('admin_gaji.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        // 1. Buat cabang khusus jika belum ada
        $branch = $this->getOrCreateBranch();

        // 2. Generate login_id unik dari nama
        $baseName = Str::slug($request->name, '');
        $loginId = $baseName;
        $counter = 1;
        while (User::where('login_id', $loginId)->exists()) {
            $loginId = $baseName . $counter;
            $counter++;
        }

        // 3. Buat User record (supaya bisa digaji di Penggajian Cabang)
        $realUser = User::create([
            'name' => $request->name,
            'login_id' => $loginId,
            'password' => Hash::make('password123'),
            'role' => 'user',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        // 4. Buat AdminGajiUser record dan link ke User
        AdminGajiUser::create([
            'name' => $request->name,
            'location' => $request->location,
            'user_id' => $realUser->id,
        ]);

        return redirect()->back()->with('success', 'Data User berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $adminGajiUser = AdminGajiUser::findOrFail($id);
        $adminGajiUser->update([
            'name' => $request->name,
            'location' => $request->location,
        ]);

        // Update juga nama di tabel users jika ada link
        if ($adminGajiUser->user_id) {
            User::where('id', $adminGajiUser->user_id)->update([
                'name' => $request->name,
            ]);
        }

        return redirect()->back()->with('success', 'Data User berhasil diupdate!');
    }

    public function destroy($id)
    {
        $adminGajiUser = AdminGajiUser::findOrFail($id);

        // Hapus juga User record jika ada link
        if ($adminGajiUser->user_id) {
            User::where('id', $adminGajiUser->user_id)->delete();
        }

        $adminGajiUser->delete();

        return redirect()->back()->with('success', 'Data User berhasil dihapus!');
    }
}
