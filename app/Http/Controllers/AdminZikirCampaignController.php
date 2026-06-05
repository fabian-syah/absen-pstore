<?php

namespace App\Http\Controllers;

use App\Models\ZikirCampaign;
use Illuminate\Http\Request;

class AdminZikirCampaignController extends Controller
{
    /**
     * Display a listing of campaigns.
     */
    public function index()
    {
        $campaigns = ZikirCampaign::orderBy('created_at', 'desc')->get();
        return view('admin.dzikir.campaigns.index', compact('campaigns'));
    }

    /**
     * Show the form for creating a new campaign.
     */
    public function create()
    {
        return view('admin.dzikir.campaigns.create');
    }

    /**
     * Store a newly created campaign.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'arabic_text' => 'nullable|string',
            'latin_text' => 'nullable|string',
            'target' => 'required|integer|min:1',
            'current_count' => 'nullable|integer|min:0',
            'emoji' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['current_count'] = $data['current_count'] ?? 0;

        ZikirCampaign::create($data);

        return redirect()->route('admin.dzikir-campaign.index')->with('success', 'Campaign berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified campaign.
     */
    public function edit(ZikirCampaign $dzikir_campaign)
    {
        return view('admin.dzikir.campaigns.edit', ['campaign' => $dzikir_campaign]);
    }

    /**
     * Update the specified campaign.
     */
    public function update(Request $request, ZikirCampaign $dzikir_campaign)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'arabic_text' => 'nullable|string',
            'latin_text' => 'nullable|string',
            'target' => 'required|integer|min:1',
            'current_count' => 'nullable|integer|min:0',
            'emoji' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $dzikir_campaign->update($data);

        return redirect()->route('admin.dzikir-campaign.index')->with('success', 'Campaign berhasil diperbarui.');
    }

    /**
     * Remove the specified campaign.
     */
    public function destroy(ZikirCampaign $dzikir_campaign)
    {
        $dzikir_campaign->delete();

        return redirect()->route('admin.dzikir-campaign.index')->with('success', 'Campaign berhasil dihapus.');
    }
}
