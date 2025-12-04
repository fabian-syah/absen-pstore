<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
// Import Model
use App\Models\User;
use App\Models\Broadcast; 
use App\Models\Division;
use App\Models\Branch;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        // 1. Validasi Input
        $query = $request->get('q');
        
        // Minimal 2 huruf untuk mencari
        if (!$query || strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        // 2. Cek User Login
        $user = Auth::user();
        if (!$user) {
             return response()->json(['results' => []], 401);
        }

        // 3. Validasi Role (UBAH DISINI: Admin DAN Audit boleh akses)
        // Kita gunakan !in_array untuk mengecek apakah role user TIDAK ADA di dalam daftar
        if (!in_array($user->role, ['admin', 'audit'])) {
             return response()->json(['results' => []]); 
        }

        $results = collect([]);

        try {
            // === SEARCH 1: USERS ===
            $users = User::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->with(['division', 'branch']) // Eager loading
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    $divName = $item->division?->name ?? '-'; 
                    $branchName = $item->branch?->name ?? '-';

                    return [
                        'type' => 'user',
                        'title' => $item->name,
                        'description' => "{$item->email} - {$divName} ({$branchName})",
                        'url' => route('users.show', $item->id),
                        'icon' => 'mdi-account'
                    ];
                });
            $results = $results->merge($users);

            // === SEARCH 2: BROADCASTS ===
            $broadcasts = Broadcast::where('title', 'like', "%{$query}%")
                ->orWhere('message', 'like', "%{$query}%")
                ->orderBy('created_at', 'desc') // Tampilkan yang terbaru
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    $status = $item->is_published ? 'Published' : 'Draft';
                    $priority = ucfirst($item->priority); 
                    
                    return [
                        'type' => 'broadcast',
                        'title' => $item->title,
                        'description' => "[{$status} - {$priority}] " . Str::limit($item->message, 40),
                        'url' => route('broadcast.show', $item->id), 
                        'icon' => 'mdi-bullhorn'
                    ];
                });
            $results = $results->merge($broadcasts);

            // === SEARCH 3: DIVISIONS ===
            $divisions = Division::where('name', 'like', "%{$query}%")
                ->with('branch')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    $branchName = $item->branch?->name ?? 'No Branch';
                    return [
                        'type' => 'division',
                        'title' => $item->name,
                        'description' => "Branch: {$branchName}",
                        'url' => route('divisions.show', $item->id),
                        'icon' => 'mdi-sitemap'
                    ];
                });
            $results = $results->merge($divisions);

            // === SEARCH 4: BRANCHES ===
            $branches = Branch::where('name', 'like', "%{$query}%")
                ->orWhere('address', 'like', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'type' => 'branch',
                        'title' => $item->name,
                        'description' => Str::limit($item->address, 40),
                        'url' => route('branches.show', $item->id),
                        'icon' => 'mdi-office-building'
                    ];
                });
            $results = $results->merge($branches);

            // Gabungkan dan ambil maksimal 10 hasil
            return response()->json(['results' => $results->take(10)]);

        } catch (\Exception $e) {
            // Tangkap error agar tidak White Screen of Death
            return response()->json([
                'results' => [],
                'error' => $e->getMessage() 
            ], 500);
        }
    }
}