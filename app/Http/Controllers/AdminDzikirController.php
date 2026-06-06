<?php

namespace App\Http\Controllers;

use App\Models\Zikir;
use App\Models\UserZikirLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDzikirController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $zikirs = Zikir::orderBy('created_at', 'desc')->get();
        return view('admin.dzikir.index', compact('zikirs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.dzikir.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|array|min:1',
            'category.*' => 'string|in:umum,pagi,petang,sholat',
            'prayer_time' => 'nullable|string|in:semua,subuh,dzuhur,ashar,maghrib,isya',
            'arabic_text' => 'nullable|string',
            'latin_text' => 'nullable|string',
            'translation' => 'nullable|string',
            'default_target' => 'required|integer|min:1',
            'information' => 'nullable|string',
        ]);

        Zikir::create($request->all());

        return redirect()->route('admin.dzikir.index')->with('success', 'Zikir berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zikir $dzikir)
    {
        return view('admin.dzikir.edit', compact('dzikir'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Zikir $dzikir)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|array|min:1',
            'category.*' => 'string|in:umum,pagi,petang,sholat',
            'prayer_time' => 'nullable|string|in:semua,subuh,dzuhur,ashar,maghrib,isya',
            'arabic_text' => 'nullable|string',
            'latin_text' => 'nullable|string',
            'translation' => 'nullable|string',
            'default_target' => 'required|integer|min:1',
            'information' => 'nullable|string',
        ]);

        $dzikir->update($request->all());

        return redirect()->route('admin.dzikir.index')->with('success', 'Zikir berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Zikir $dzikir)
    {
        $dzikir->delete();

        return redirect()->route('admin.dzikir.index')->with('success', 'Zikir berhasil dihapus.');
    }

    /**
     * Display the stats of users' zikir.
     */
    public function stats(Request $request)
    {
        $filter = $request->query('filter', 'all'); // 'daily', 'monthly', 'all'
        $date = $request->query('date', date('Y-m-d'));
        $month = $request->query('month', date('Y-m'));

        $query = UserZikirLog::with('user')
            ->select('user_id', DB::raw('SUM(count) as total_count'));

        if ($filter == 'daily') {
            $query->whereDate('read_date', $date);
        } elseif ($filter == 'monthly') {
            $year = date('Y', strtotime($month));
            $m = date('m', strtotime($month));
            $query->whereYear('read_date', $year)->whereMonth('read_date', $m);
        }

        $stats = $query->groupBy('user_id')
            ->orderBy('total_count', 'desc')
            ->get();

        return view('admin.dzikir.stats', compact('stats', 'filter', 'date', 'month'));
    }
}
