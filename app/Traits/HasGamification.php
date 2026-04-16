<?php

namespace App\Traits;

use App\Models\Attendance;
use Carbon\Carbon;

trait HasGamification
{
    /**
     * Get the rank configuration.
     * 20 Tiers as requested by USER.
     */
    public static function getRankConfig()
    {
        return [
            // 1. Tier Pemula (The Foundation)
            ['level' => 1, 'name' => 'Novice', 'min_xp' => 0, 'color' => '#475569', 'icon' => 'mdi-stone-variant', 'category' => 'foundation'],
            ['level' => 2, 'name' => 'Apprentice', 'min_xp' => 100, 'color' => '#8B4513', 'icon' => 'mdi-wood', 'category' => 'foundation'],
            ['level' => 3, 'name' => 'Iron', 'min_xp' => 300, 'color' => '#1A1A1A', 'icon' => 'mdi-anvil', 'category' => 'foundation'],
            ['level' => 4, 'name' => 'Bronze', 'min_xp' => 600, 'color' => '#CD7F32', 'icon' => 'mdi-shield-outline', 'category' => 'foundation'],
            ['level' => 5, 'name' => 'Silver', 'min_xp' => 1000, 'color' => '#C0C0C0', 'icon' => 'mdi-shield-check-outline', 'category' => 'foundation'],

            // 2. Tier Menengah (The Elite)
            ['level' => 6, 'name' => 'Gold', 'min_xp' => 1500, 'color' => '#FFD700', 'icon' => 'mdi-trophy-outline', 'category' => 'elite'],
            ['level' => 7, 'name' => 'Platinum', 'min_xp' => 2500, 'color' => '#E2E8F0', 'icon' => 'mdi-trophy-variant', 'category' => 'elite'],
            ['level' => 8, 'name' => 'Diamond', 'min_xp' => 4000, 'color' => '#B9F2FF', 'icon' => 'mdi-diamond-stone', 'category' => 'elite'],
            ['level' => 9, 'name' => 'Emerald', 'min_xp' => 6000, 'color' => '#50C878', 'icon' => 'mdi-gemstone-outline', 'category' => 'elite'],
            ['level' => 10, 'name' => 'Ruby', 'min_xp' => 9000, 'color' => '#E0115F', 'icon' => 'mdi-ruby', 'category' => 'elite'],

            // 3. Tier Tinggi (The Masterclass)
            ['level' => 11, 'name' => 'Sapphire', 'min_xp' => 13000, 'color' => '#0F52BA', 'icon' => 'mdi-star-face', 'category' => 'masterclass'],
            ['level' => 12, 'name' => 'Crystal', 'min_xp' => 18000, 'color' => '#E0FFFF', 'icon' => 'mdi-crystal-ball', 'category' => 'masterclass'],
            ['level' => 13, 'name' => 'Master', 'min_xp' => 25000, 'color' => '#7851A9', 'icon' => 'mdi-crown-outline', 'category' => 'masterclass'],
            ['level' => 14, 'name' => 'Grandmaster', 'min_xp' => 35000, 'color' => '#FFBF00', 'icon' => 'mdi-crown', 'category' => 'masterclass'],
            ['level' => 15, 'name' => 'Epic', 'min_xp' => 50000, 'color' => '#40E0D0', 'icon' => 'mdi-flash-circle', 'category' => 'masterclass'],

            // 4. Tier Puncak (The Godlike)
            ['level' => 16, 'name' => 'Legend', 'min_xp' => 100000, 'color' => '#F5F5F5', 'icon' => 'mdi-auto-fix', 'category' => 'godlike'],
            ['level' => 17, 'name' => 'Mythic', 'min_xp' => 250000, 'color' => '#663399', 'icon' => 'mdi-creation', 'category' => 'godlike'],
            ['level' => 18, 'name' => 'Immortal', 'min_xp' => 750000, 'color' => '#FF0000', 'icon' => 'mdi-death-star', 'category' => 'godlike'],
            ['level' => 19, 'name' => 'Celestial', 'min_xp' => 2000000, 'color' => '#00FFFF', 'icon' => 'mdi-weather-night', 'category' => 'godlike'],
            ['level' => 20, 'name' => 'Eternal', 'min_xp' => 5000000, 'color' => '#000000', 'icon' => 'mdi-infinity', 'category' => 'godlike'],
        ];
    }

    /**
     * Calculate Rank based on current XP.
     */
    public function calculateRank()
    {
        $config = self::getRankConfig();

        // Admin Special Case: Always Eternal
        if ($this->role === 'admin') {
            return $config[19]; // Level 20 (Eternal) is at index 19
        }

        $currentRank = $config[0];

        foreach ($config as $rank) {
            if ($this->xp >= $rank['min_xp']) {
                $currentRank = $rank;
            } else {
                break;
            }
        }

        return $currentRank;
    }

    /**
     * Get Progress to next rank.
     */
    public function getRankProgress()
    {
        $config = self::getRankConfig();
        $nextRank = null;
        $currentRankMin = 0;

        foreach ($config as $index => $rank) {
            if ($this->xp >= $rank['min_xp']) {
                $currentRankMin = $rank['min_xp'];
                $nextRank = $config[$index + 1] ?? null;
            } else {
                break;
            }
        }

        if (!$nextRank) {
            return 100;
        }

        $range = $nextRank['min_xp'] - $currentRankMin;
        $earned = $this->xp - $currentRankMin;

        return round(($earned / $range) * 100);
    }

    /**
     * Sync XP from historical attendance data.
     */
    public function syncXpFromHistory()
    {
        // Define Scoring Rules
        $xp = 0;

        // 1. Get all verified attendances
        $attendances = Attendance::where('user_id', $this->id)
            ->where('status', 'verified')
            ->get();

        foreach ($attendances as $att) {
            // Basic Presence (Hadir/Masuk/WFH/etc)
            if (in_array($att->presence_status, ['Masuk', 'Hadir', 'Tepat Waktu', 'WFH', 'WFH / Dinas Luar', 'Dinas Luar', 'Lembur'])) {
                $xp += 10;
            }

            // Not Late Bonus
            if (!$att->is_late_checkin) {
                $xp += 5;
            } else {
                $xp -= 5;
            }

            // Early Checkout Penalty
            if ($att->is_early_checkout) {
                $xp -= 5;
            }

            // Checkout Bonus
            if ($att->check_out_time) {
                $xp += 5;
            }
        }

        // 2. Penalties for Alpha
        $alphasCount = Attendance::where('user_id', $this->id)
            ->where('presence_status', 'Alpha')
            ->count();
        $xp -= ($alphasCount * 20);

        // 3. Small appreciation for Izin/Sakit
        $izinCount = Attendance::where('user_id', $this->id)
            ->whereIn('presence_status', ['Sakit', 'Izin'])
            ->where('status', 'verified')
            ->count();
        $xp += ($izinCount * 2);

        // Update User
        $this->xp = max(0, $xp); // Avoid negative XP for profile display
        $rank = $this->calculateRank();
        $this->rank_title = $rank['name'];
        $this->save();

        return $this->xp;
    }
}
