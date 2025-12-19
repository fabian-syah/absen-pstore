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
        
        $branches = collect();

        // --- LOGIKA PERUBAHAN DISINI ---
        if ($user->role === 'admin') {
            // JIKA ADMIN: Ambil SEMUA Cabang
            $branches = Branch::orderBy('name', 'asc')->get();
        } else {
            // JIKA BUKAN ADMIN: Ambil Cabang Sesuai Akses (Pivot & Single)
            $myBranchIds = $user->branches()->pluck('branches.id')->toArray();
            if ($user->branch_id) $myBranchIds[] = $user->branch_id;
            
            $myBranchIds = array_filter(array_unique($myBranchIds));

            if (empty($myBranchIds)) {
                return response()->json(['branches' => []]);
            }

            $branches = Branch::whereIn('id', $myBranchIds)
                ->orderBy('name', 'asc')
                ->get();
        }
        // -------------------------------

        // Map data cabang untuk frontend (hitung unread, preview pesan)
        $mappedBranches = $branches->map(function ($branch) use ($user) {
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

            // Ambil pesan terakhir untuk preview
            $lastMsg = BranchMessage::where('branch_id', $branch->id)->latest()->first();
            $preview = 'Belum ada pesan';
            
            if($lastMsg) {
                $sender = $lastMsg->user_id == $user->id ? 'Anda' : explode(' ', $lastMsg->user->name)[0];
                // Jika pesan teks kosong (cuma gambar), tulis "Foto"
                $msgContent = $lastMsg->message ? \Illuminate\Support\Str::limit($lastMsg->message, 20) : '📷 Foto';
                $preview = "$sender: $msgContent";
            }

            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'unread_count' => $unreadCount,
                'last_message' => $preview,
                'timezone' => $branch->timezone ?? 'Asia/Jakarta'
            ];
        });

        // Urutkan: Cabang dengan pesan unread di atas, lalu berdasarkan nama
        $sortedBranches = $mappedBranches->sortByDesc('unread_count')->values();

        return response()->json([
            'branches' => $sortedBranches,
            'total_unread' => $mappedBranches->sum('unread_count') // Total badge merah di navbar
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

        // Validasi Akses: Jika bukan admin, pastikan dia punya akses ke cabang ini
        if ($user->role !== 'admin') {
            $hasAccess = false;
            if ($user->branch_id == $branchId) $hasAccess = true;
            if (!$hasAccess && $user->branches()->where('branches.id', $branchId)->exists()) $hasAccess = true;

            if (!$hasAccess) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        
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

        // Validasi Akses Kirim (Sama seperti index)
        if ($user->role !== 'admin') {
            $hasAccess = false;
            if ($user->branch_id == $request->branch_id) $hasAccess = true;
            if (!$hasAccess && $user->branches()->where('branches.id', $request->branch_id)->exists()) $hasAccess = true;

            if (!$hasAccess) {
                return response()->json(['error' => 'Anda tidak memiliki akses ke cabang ini.'], 403);
            }
        }

        // Validasi minimal ada isi
        if (!$request->message && !$request->hasFile('image')) {
            return response()->json(['error' => 'Pesan kosong'], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('chat-images', 'public');
        }

        BranchMessage::create([
            'branch_id' => $request->branch_id, 
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