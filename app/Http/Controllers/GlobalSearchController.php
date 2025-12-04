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

        // 3. Validasi Role (Hanya Admin dan Audit)
        if (!in_array($user->role, ['admin', 'audit'])) {
             return response()->json(['results' => []]); 
        }

        // 4. SIAPKAN FILTER CABANG KHUSUS AUDIT
        $auditBranchIds = [];
        if ($user->role == 'audit') {
            // Ambil array ID cabang yang dipegang audit
            $auditBranchIds = $user->branches()->pluck('branches.id')->toArray();
        }

        $results = collect([]);

        try {
            // ==========================================
            // SEARCH 1: USERS
            // ==========================================
            $userQuery = User::where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            });

            // FILTER AUDIT: Hanya tampilkan user di cabang pegangannya
            if ($user->role == 'audit') {
                $userQuery->whereIn('branch_id', $auditBranchIds);
            } elseif ($user->role == 'admin' && $user->branch_id != null) {
                // Tambahan: Jika admin cabang, batasi juga ke cabangnya
                $userQuery->where('branch_id', $user->branch_id);
            }

            $users = $userQuery->with(['division', 'branch'])
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

            // ==========================================
            // SEARCH 2: BROADCASTS (Bebas / Global)
            // ==========================================
            $broadcasts = Broadcast::where('title', 'like', "%{$query}%")
                ->orWhere('message', 'like', "%{$query}%")
                ->orderBy('created_at', 'desc')
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

            // ==========================================
            // SEARCH 3: DIVISIONS
            // ==========================================
            $divQuery = Division::where('name', 'like', "%{$query}%");

            // Filter Divisi berdasarkan cabang (jika divisi terikat cabang)
            // Asumsi tabel divisions punya branch_id. Jika tidak, hapus blok if ini.
            if ($user->role == 'audit') {
                // Cek dulu apakah tabel divisions punya kolom branch_id
                // Jika ya, filter. Jika tidak, lewati.
                // $divQuery->whereIn('branch_id', $auditBranchIds); 
            }

            $divisions = $divQuery->with('branch')
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

            // ==========================================
            // SEARCH 4: BRANCHES
            // ==========================================
            $branchQuery = Branch::where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('address', 'like', "%{$query}%");
            });

            // FILTER AUDIT: Hanya cari cabang yang dia pegang
            if ($user->role == 'audit') {
                $branchQuery->whereIn('id', $auditBranchIds);
            }

            $branches = $branchQuery->limit(5)
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
            return response()->json([
                'results' => [],
                'error' => $e->getMessage() 
            ], 500);
        }
    }
}