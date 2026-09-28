<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BroadcastController extends Controller
{
    public function index()
    {
        // Hanya admin yang bisa melihat halaman index
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $broadcasts = Broadcast::with('creator')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return view('broadcast.index', compact('broadcasts'));
    }

    public function create()
    {
        // Hanya admin yang bisa membuat broadcast
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        return view('broadcast.create');
    }

    public function store(Request $request)
    {
        // Hanya admin yang bisa membuat broadcast
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high'
        ]);

        Broadcast::create([
            'title' => $request->title,
            'message' => $request->message,
            'priority' => $request->priority,
            'created_by' => Auth::id(),
            'is_published' => true,
            'published_at' => now()
        ]);

        return redirect()->route('broadcast.index')
            ->with('success', 'Broadcast berhasil dikirim!');
    }

    // TAMBAHKAN METHOD SHOW YANG MISSING
   public function show(Broadcast $broadcast)
    {
        // Pastikan kode ini TETAP DIKOMENTARI atau DIHAPUS agar user biasa bisa baca
        /* if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        } */

        // Validasi opsional: hanya tampilkan jika published
        if (!$broadcast->is_published && Auth::user()->role !== 'admin') {
            abort(404);
        }

        return view('broadcast.show', compact('broadcast'));
    }

    // Method untuk mendapatkan notifikasi broadcast
    public function getNotifications()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        try {
            $broadcasts = Broadcast::published()
                ->recent(7)
                ->orderBy('published_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($broadcast) {
                    return [
                        'id' => $broadcast->id,
                        'title' => $broadcast->title,
                        'message' => $broadcast->message,
                        'priority' => $broadcast->priority,
                        'priority_icon' => $broadcast->getPriorityIcon(),
                        'priority_color' => $broadcast->getPriorityColor(),
                        'published_at' => $broadcast->published_at->toISOString(),
                        'time_ago' => $broadcast->published_at->diffForHumans()
                    ];
                });

            return response()->json([
                'broadcasts' => $broadcasts,
                'unread_count' => $broadcasts->count()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Broadcast Notification Error: ' . $e->getMessage());
            return response()->json([
                'broadcasts' => [],
                'unread_count' => 0
            ]);
        }
    }

    // Method untuk mark as read (jika diperlukan)
    public function markAsRead($id)
    {
        // Implementasi jika ingin menandai broadcast sudah dibaca
        return response()->json(['success' => true]);
    }

    public function edit(Broadcast $broadcast)
    {
        // Hanya admin yang bisa edit broadcast
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        return view('broadcast.edit', compact('broadcast'));
    }

    public function update(Request $request, Broadcast $broadcast)
    {
        // Hanya admin yang bisa update broadcast
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high'
        ]);

        $broadcast->update([
            'title' => $request->title,
            'message' => $request->message,
            'priority' => $request->priority
        ]);

        return redirect()->route('broadcast.index')
            ->with('success', 'Broadcast berhasil diperbarui!');
    }

    public function destroy(Broadcast $broadcast)
    {
        // Hanya admin yang bisa menghapus broadcast
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $broadcast->delete();

        return redirect()->route('broadcast.index')
            ->with('success', 'Broadcast berhasil dihapus!');
    }
}