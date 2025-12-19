<?php

namespace App\Http\Controllers;

use App\Models\BranchMessage;
use App\Models\Branch;
use App\Models\ChatRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BranchMessageController extends Controller
{
    /**
     * 1. API: Mengambil Daftar Cabang User + Notifikasi Unread
     */
    public function getBranchList()
    {
        $user = Auth::user();
        
        // --- LOGIKA MENGAMBIL CABANG (Sama seperti TeamController) ---
        $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
        if ($user->branch_id) $myBranchIds[] = $user->branch_id;
        
        // Jika Admin Pusat (tanpa cabang spesifik), ambil semua
        if ($user->role == 'admin' && $user->branch_id == null) {
            $myBranchIds = Branch::pluck('id')->toArray();
        }
        
        $myBranchIds = array_filter(array_unique($myBranchIds));

        if (empty($myBranchIds)) {
            return response()->json(['branches' => []]);
        }

        // Ambil Data Cabang beserta hitungan pesan baru
        $branches = Branch::whereIn('id', $myBranchIds)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($branch) use ($user) {
                // Ambil waktu terakhir user baca chat di cabang ini
                $lastRead = ChatRead::where('user_id', $user->id)
                    ->where('branch_id', $branch->id)
                    ->value('last_read_at');

                // Hitung pesan yang dibuat SETELAH terakhir baca
                $unreadQuery = BranchMessage::where('branch_id', $branch->id);
                
                if ($lastRead) {
                    $unreadQuery->where('created_at', '>', $lastRead);
                }
                
                // Jangan hitung pesan saya sendiri sebagai unread
                $unreadCount = $unreadQuery->where('user_id', '!=', $user->id)->count();

                // Ambil pesan terakhir untuk preview (opsional)
                $lastMsg = BranchMessage::where('branch_id', $branch->id)->latest()->first();
                $preview = 'Belum ada pesan';
                if($lastMsg) {
                    $sender = $lastMsg->user_id == $user->id ? 'Anda' : explode(' ', $lastMsg->user->name)[0];
                    $content = $lastMsg->image_path ? '📷 Foto' : \Illuminate\Support\Str::limit($lastMsg->message, 20);
                    $preview = "$sender: $content";
                }

                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'unread_count' => $unreadCount,
                    'last_message' => $preview,
                    'timezone' => $branch->timezone ?? 'Asia/Jakarta'
                ];
            });

        // Urutkan: Cabang dengan pesan unread di atas
        $sortedBranches = $branches->sortByDesc('unread_count')->values();

        return response()->json([
            'branches' => $sortedBranches,
            'total_unread' => $branches->sum('unread_count') // Untuk badge di navbar utama
        ]);
    }

    /**
     * 2. API: Mengambil Pesan dari Cabang Tertentu
     */
    public function index(Request $request)
    {
        $request->validate(['branch_id' => 'required|exists:branches,id']);
        
        $user = Auth::user();
        $branchId = $request->branch_id;

        // Validasi Akses (Cek apakah user berhak akses cabang ini)
        // (Logic akses bisa disederhanakan atau diperketat sesuai kebutuhan)
        // Disini kita asumsikan FE sudah memfilter via getBranchList, tapi validasi BE tetap penting.
        
        $branch = Branch::find($branchId);
        $timezone = $branch->timezone ?? 'Asia/Jakarta';

        // UPDATE STATUS BACA (Tandai sudah dibaca sekarang)
        ChatRead::updateOrCreate(
            ['user_id' => $user->id, 'branch_id' => $branchId],
            ['last_read_at' => now()]
        );

        $messages = BranchMessage::with('user')
            ->where('branch_id', $branchId)
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($msg) use ($timezone, $user) {
                return [
                    'id' => $msg->id,
                    'user_name' => $msg->user->name,
                    'user_avatar' => $msg->user->profile_photo_path, 
                    'message' => $msg->message,
                    'image_url' => $msg->image_path ? Storage::url($msg->image_path) : null,
                    'is_me' => $msg->user_id === $user->id,
                    'time' => Carbon::parse($msg->created_at)->setTimezone($timezone)->format('H:i'),
                    'date' => Carbon::parse($msg->created_at)->setTimezone($timezone)->format('d M'),
                ];
            });

        return response()->json(['messages' => $messages->reverse()->values()]);
    }

    /**
     * 3. API: Kirim Pesan
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'message' => 'nullable|string|max:1000',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Validasi minimal ada isi
        if (!$request->message && !$request->hasFile('image')) {
            return response()->json(['error' => 'Pesan kosong'], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('chat-images', 'public');
        }

        BranchMessage::create([
            'branch_id' => $request->branch_id, // Gunakan branch_id dari request
            'user_id' => $user->id,
            'message' => $request->message,
            'image_path' => $imagePath,
        ]);

        // Otomatis update read status pengirim
        ChatRead::updateOrCreate(
            ['user_id' => $user->id, 'branch_id' => $request->branch_id],
            ['last_read_at' => now()]
        );

        return response()->json(['success' => true]);
    }
}