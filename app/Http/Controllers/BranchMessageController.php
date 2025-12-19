<?php

namespace App\Http\Controllers;

use App\Models\BranchMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BranchMessageController extends Controller
{
    // Ambil pesan (Fetch)
    public function index()
    {
        $user = Auth::user();

        if (!$user->branch_id) {
            return response()->json(['messages' => []]);
        }

        $timezone = $user->branch->timezone ?? 'Asia/Jakarta';

        $messages = BranchMessage::with('user')
            ->where('branch_id', $user->branch_id)
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($msg) use ($timezone, $user) {
                return [
                    'id' => $msg->id,
                    'user_name' => $msg->user->name,
                    'user_avatar' => $msg->user->profile_photo_path, 
                    'message' => $msg->message,
                    
                    // --- LOGIKA GAMBAR ---
                    'image_url' => $msg->image_path ? Storage::url($msg->image_path) : null,
                    // ---------------------

                    'is_me' => $msg->user_id === $user->id,
                    'time' => Carbon::parse($msg->created_at)->setTimezone($timezone)->format('H:i'),
                    'date' => Carbon::parse($msg->created_at)->setTimezone($timezone)->format('d M'),
                ];
            });

        return response()->json(['messages' => $messages->reverse()->values()]);
    }

    // Kirim pesan (Store) - Support Text & Image
    public function store(Request $request)
    {
        // Validasi: Pesan boleh kosong JIKA ada gambar
        $request->validate([
            'message' => 'nullable|string|max:1000',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        $user = Auth::user();

        if (!$user->branch_id) {
            return response()->json(['error' => 'No Branch'], 403);
        }

        // Cek minimal ada satu isi (text atau gambar)
        if (!$request->message && !$request->hasFile('image')) {
            return response()->json(['error' => 'Pesan atau gambar tidak boleh kosong'], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('chat-images', 'public');
        }

        BranchMessage::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'message' => $request->message,
            'image_path' => $imagePath,
        ]);

        return response()->json(['success' => true]);
    }
}