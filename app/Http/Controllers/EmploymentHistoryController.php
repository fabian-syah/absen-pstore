<?php

namespace App\Http\Controllers;

use App\Models\EmploymentHistory;
use App\Models\Branch;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmploymentHistoryController extends Controller
{
    public function index()
    {
        // Ambil data user yang sedang login
        $user = auth()->user();

        // Ambil history, urutkan berdasarkan TANGGAL KEJADIAN (Terbaru di atas)
        // Jadi 'Awal Masuk' (tahun lama) otomatis akan ada di paling bawah
        $histories = EmploymentHistory::where('user_id', $user->id)
            ->with(['branch', 'division'])
            ->orderBy('event_date', 'desc') 
            ->get();

        $branches = Branch::all();
        $divisions = Division::all();

        return view('employment_history.index', compact('histories', 'branches', 'divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'event_date' => 'required|date',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->user()->id;

        // Upload Foto jika ada
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('employment_attachments', 'public');
        }

        // Logika Khusus berdasarkan Tipe
        if ($request->type == 'transfer_division') {
            // Jika pindah divisi, cabang otomatis ikut cabang user saat ini
            $data['branch_id'] = auth()->user()->branch_id;
        } 
        elseif ($request->type == 'resign') {
            // Jika resign, kosongkan branch dan division (opsional, tergantung kebutuhan data)
            $data['branch_id'] = null;
            $data['division_id'] = null;
        }

        EmploymentHistory::create($data);

        return redirect()->back()->with('success', 'Riwayat berhasil ditambahkan.');
    }
    
    public function destroy($id)
    {
        $history = EmploymentHistory::findOrFail($id);
        if($history->attachment) {
            Storage::disk('public')->delete($history->attachment);
        }
        $history->delete();
        return redirect()->back()->with('success', 'Riwayat dihapus.');
    }
}