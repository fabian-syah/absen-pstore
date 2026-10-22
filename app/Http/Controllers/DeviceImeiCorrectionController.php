<?php

namespace App\Http\Controllers;

use App\Models\DeviceImei;
use App\Models\DeviceImeiHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeviceImeiCorrectionController extends Controller
{
    /**
     * Helper untuk validasi hak akses khusus Super Admin
     */
    private function isSuperAdmin(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return in_array(strtolower(trim($user->role ?? '')), ['admin', 'superadmin', 'super admin'])
            || strtolower(trim($user->login_id ?? '')) === 'superadmin'
            || ($user->role === 'admin' && empty($user->branch_id));
    }

    /**
     * Halaman Utama: Search IMEI, Form Rubah Tipe, dan Daftar Riwayat Koreksi
     */
    public function index(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            abort(403, 'Akses terbatas khusus Super Admin.');
        }

        $searchImei = trim($request->get('imei', ''));
        $cleanSearchImei = preg_replace('/[^0-9A-Za-z]/', '', $searchImei);

        $foundDevice = null;
        $orphanHistories = collect();
        $searched = false;

        if (!empty($cleanSearchImei)) {
            $searched = true;
            $foundDevice = DeviceImei::with(['histories.admin', 'creator', 'updater'])
                ->where('imei', $cleanSearchImei)
                ->first();

            // Jika unit belum ada di tabel device_imeis, cek apakah ada riwayat tersimpan sebelumnya
            if (!$foundDevice) {
                $orphanHistories = DeviceImeiHistory::with('admin')
                    ->where('imei', $cleanSearchImei)
                    ->latest()
                    ->get();
            }
        }

        // Daftar riwayat koreksi terbaru di sistem (audit log)
        $recentHistories = DeviceImeiHistory::with(['admin', 'device'])
            ->latest()
            ->paginate(15);

        return view('admin.device_imei.index', [
            'searchImei' => $searchImei,
            'cleanSearchImei' => $cleanSearchImei,
            'foundDevice' => $foundDevice,
            'orphanHistories' => $orphanHistories,
            'searched' => $searched,
            'recentHistories' => $recentHistories,
        ]);
    }

    /**
     * Proses Simpan / Koreksi Spesifikasi Tipe HP Berdasarkan IMEI
     */
    public function storeOrUpdate(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            abort(403, 'Akses terbatas khusus Super Admin.');
        }

        $request->validate([
            'imei' => 'required|string|min:8|max:25',
            'brand' => 'required|string|max:60',
            'type' => 'required|string|max:120',
            'storage' => 'required|string|max:60',
            'color' => 'nullable|string|max:60',
            'cs_name_mistake' => 'required|string|max:120',
            'correction_reason' => 'nullable|string|max:500',
            'previous_type' => 'nullable|string|max:120',
            'previous_storage' => 'nullable|string|max:60',
        ], [
            'imei.required' => 'Nomor IMEI wajib diisi.',
            'type.required' => 'Tipe HP yang benar wajib diisi.',
            'storage.required' => 'Kapasitas Storage atau GB wajib dipilih atau diisi.',
            'cs_name_mistake.required' => 'Nama CS yang salah input wajib diisi.',
        ]);

        $cleanImei = preg_replace('/[^0-9A-Za-z]/', '', $request->imei);

        DB::beginTransaction();
        try {
            $device = DeviceImei::where('imei', $cleanImei)->first();

            if ($device) {
                // Simpan snapshot data lama sebelum perubahan
                $oldBrand = $device->brand;
                $oldType = $device->type;
                $oldStorage = $device->storage;
                $oldColor = $device->color;

                // Perbarui unit ke data spesifikasi yang benar
                $device->update([
                    'brand' => $request->brand,
                    'type' => $request->type,
                    'storage' => $request->storage,
                    'color' => $request->color,
                    'last_cs_name' => $request->cs_name_mistake,
                    'notes' => $request->correction_reason,
                    'updated_by' => Auth::id(),
                ]);

                // Buat entri riwayat baru (Audit Trail)
                DeviceImeiHistory::create([
                    'device_imei_id' => $device->id,
                    'imei' => $device->imei,
                    'action_type' => 'koreksi_tipe',
                    'old_brand' => $oldBrand,
                    'old_type' => $oldType,
                    'old_storage' => $oldStorage,
                    'old_color' => $oldColor,
                    'new_brand' => $request->brand,
                    'new_type' => $request->type,
                    'new_storage' => $request->storage,
                    'new_color' => $request->color,
                    'cs_name_mistake' => $request->cs_name_mistake,
                    'correction_reason' => $request->correction_reason ?: 'Koreksi tipe HP dan spesifikasi oleh Super Admin',
                    'corrected_by' => Auth::id(),
                ]);

                $message = "Spesifikasi IMEI {$cleanImei} berhasil dikoreksi dari {$oldType} ({$oldStorage}) menjadi {$request->type} ({$request->storage}). Riwayat lama tetap tersimpan.";
            } else {
                // Unit baru belum ada di master device_imeis
                $device = DeviceImei::create([
                    'imei' => $cleanImei,
                    'brand' => $request->brand,
                    'type' => $request->type,
                    'storage' => $request->storage,
                    'color' => $request->color,
                    'last_cs_name' => $request->cs_name_mistake,
                    'notes' => $request->correction_reason,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                $oldType = $request->previous_type ?: null;
                $oldStorage = $request->previous_storage ?: null;
                $actionType = $oldType ? 'koreksi_tipe' : 'input_awal';

                DeviceImeiHistory::create([
                    'device_imei_id' => $device->id,
                    'imei' => $device->imei,
                    'action_type' => $actionType,
                    'old_brand' => $oldType ? $request->brand : null,
                    'old_type' => $oldType,
                    'old_storage' => $oldStorage,
                    'old_color' => null,
                    'new_brand' => $request->brand,
                    'new_type' => $request->type,
                    'new_storage' => $request->storage,
                    'new_color' => $request->color,
                    'cs_name_mistake' => $request->cs_name_mistake,
                    'correction_reason' => $request->correction_reason ?: ($oldType ? "Koreksi data dari input awal CS ({$oldType})" : 'Pendaftaran awal spesifikasi unit IMEI'),
                    'corrected_by' => Auth::id(),
                ]);

                $message = "Unit IMEI {$cleanImei} berhasil didaftarkan dan dikoreksi menjadi {$request->type} ({$request->storage}).";
            }

            DB::commit();

            return redirect()
                ->route('device-corrections.index', ['imei' => $cleanImei])
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan perubahan: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Detail Lacak Riwayat Spesifikasi IMEI (Timeline Lengkap)
     */
    public function track($imei)
    {
        if (!$this->isSuperAdmin()) {
            abort(403, 'Akses terbatas khusus Super Admin.');
        }

        $cleanImei = preg_replace('/[^0-9A-Za-z]/', '', $imei);

        $device = DeviceImei::with(['histories.admin', 'creator', 'updater'])
            ->where('imei', $cleanImei)
            ->first();

        $histories = DeviceImeiHistory::with('admin')
            ->where('imei', $cleanImei)
            ->latest()
            ->get();

        if (!$device && $histories->isEmpty()) {
            return redirect()
                ->route('device-corrections.index', ['imei' => $cleanImei])
                ->with('error', "Nomor IMEI {$cleanImei} belum memiliki data atau riwayat koreksi di sistem.");
        }

        return view('admin.device_imei.track', [
            'cleanImei' => $cleanImei,
            'device' => $device,
            'histories' => $histories,
        ]);
    }

    /**
     * Endpoint API / AJAX untuk Pencarian Cepat IMEI
     */
    public function searchAjax(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = trim($request->get('query', ''));
        $cleanQuery = preg_replace('/[^0-9A-Za-z]/', '', $query);

        if (strlen($cleanQuery) < 4) {
            return response()->json([]);
        }

        $results = DeviceImei::where('imei', 'LIKE', "%{$cleanQuery}%")
            ->orWhere('type', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['id', 'imei', 'brand', 'type', 'storage', 'color', 'last_cs_name']);

        return response()->json($results);
    }
}
