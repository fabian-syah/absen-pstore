<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminGajiUser;
use App\Models\User;
use App\Models\Branch;
use App\Models\EmployeeSalary;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
        $users = AdminGajiUser::with('user')->latest()->get();
        return view('admin_gaji.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|max:10240',
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

        // Upload foto profil jika ada
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $realUser->update(['profile_photo_path' => $path]);
        }

        // 4. Buat AdminGajiUser record dan link ke User
        AdminGajiUser::create([
            'name' => $request->name,
            'location' => $request->location,
            'user_id' => $realUser->id,
        ]);

        // 5. Otomatis buat Master Gaji User dengan kategori promotor
        EmployeeSalary::firstOrCreate(
            ['user_id' => $realUser->id],
            [
                'category' => 'promotor',
                'basic_salary' => 0,
                'position_allowance' => 0,
                'daily_salary' => 0,
                'promotor_bonus' => 0,
            ]
        );

        return redirect()->back()->with('success', 'Data User berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|max:10240',
        ]);

        $adminGajiUser = AdminGajiUser::findOrFail($id);
        $adminGajiUser->update([
            'name' => $request->name,
            'location' => $request->location,
        ]);

        // Update juga nama dan foto di tabel users jika ada link
        if ($adminGajiUser->user_id) {
            $updateData = ['name' => $request->name];

            if ($request->hasFile('profile_photo')) {
                $realUser = User::find($adminGajiUser->user_id);
                if ($realUser && $realUser->profile_photo_path) {
                    Storage::disk('public')->delete($realUser->profile_photo_path);
                }
                $updateData['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
            }

            User::where('id', $adminGajiUser->user_id)->update($updateData);
        }

        return redirect()->back()->with('success', 'Data User berhasil diupdate!');
    }

    public function destroy($id)
    {
        $adminGajiUser = AdminGajiUser::findOrFail($id);

        // Hapus juga EmployeeSalary dan User record jika ada link
        if ($adminGajiUser->user_id) {
            EmployeeSalary::where('user_id', $adminGajiUser->user_id)->delete();
            User::where('id', $adminGajiUser->user_id)->delete();
        }

        $adminGajiUser->delete();

        return redirect()->back()->with('success', 'Data User berhasil dihapus!');
    }
}
