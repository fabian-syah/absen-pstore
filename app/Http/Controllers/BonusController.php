<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BonusController extends Controller
{
    public function create(Request $request)
    {
        $userId = $request->query('user_id');
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        $user = User::with(['branch', 'division', 'employeeSalary'])->findOrFail($userId);

        // Check if there's already a bonus for this user in this period
        $existingBonus = Bonus::where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        return view('bonuses.create', compact('user', 'month', 'year', 'existingBonus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required',
            'year' => 'required',
            'category' => 'required|in:bonus,thr',
            'payment_method' => 'required|in:cash,transfer',
        ]);

        // Clean Rupiah formatting
        $amount = (float) str_replace('.', '', $request->input('amount', 0));

        $data = [
            'payment_method' => $request->payment_method,
            'created_by' => Auth::id(),
        ];

        if ($request->category === 'bonus') {
            $data['bonus_amount'] = $amount;
        } else {
            $data['thr_amount'] = $amount;
        }

        Bonus::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'month' => $request->month,
                'year' => $request->year,
            ],
            $data
        );

        $user = User::findOrFail($request->user_id);

        return redirect()->route('branch-salary.show', $user->branch_id)
            ->with('success', 'Kompensasi berhasil disimpan.');
    }
}
