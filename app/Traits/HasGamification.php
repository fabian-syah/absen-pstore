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
            ['name' => 'Novice', 'min_xp' => 0, 'color' => '#f1f1f1', 'icon' => 'mdi-account-outline'],
            ['name' => 'Apprentice', 'min_xp' => 100, 'color' => '#e0e0e0', 'icon' => 'mdi-account-badge-outline'],
            ['name' => 'Iron', 'min_xp' => 300, 'color' => '#a1a1a1', 'icon' => 'mdi-shield-outline'],
            ['name' => 'Bronze', 'min_xp' => 600, 'color' => '#cd7f32', 'icon' => 'mdi-shield-half-full'],
            ['name' => 'Silver', 'min_xp' => 1000, 'color' => '#c0c0c0', 'icon' => 'mdi-shield-check'],
            ['name' => 'Gold', 'min_xp' => 1500, 'color' => '#ffd700', 'icon' => 'mdi-trophy-outline'],
            ['name' => 'Platinum', 'min_xp' => 2500, 'color' => '#e5e4e2', 'icon' => 'mdi-trophy-variant'],
            ['name' => 'Diamond', 'min_xp' => 4000, 'color' => '#b9f2ff', 'icon' => 'mdi-diamond-stone'],
            ['name' => 'Emerald', 'min_xp' => 6000, 'color' => '#50c878', 'icon' => 'mdi-gemstone-outline'],
            ['name' => 'Ruby', 'min_xp' => 9000, 'color' => '#e0115f', 'icon' => 'mdi-ruby'],
            ['name' => 'Sapphire', 'min_xp' => 13000, 'color' => '#0f52ba', 'icon' => 'mdi-star-face'],
            ['name' => 'Crystal', 'min_xp' => 18000, 'color' => '#afeeee', 'icon' => 'mdi-crystal-ball'],
            ['name' => 'Master', 'min_xp' => 25000, 'color' => '#ff8c00', 'icon' => 'mdi-crown-outline'],
            ['name' => 'Grandmaster', 'min_xp' => 35000, 'color' => '#9400d3', 'icon' => 'mdi-crown'],
            ['name' => 'Epic', 'min_xp' => 50000, 'color' => '#ff4500', 'icon' => 'mdi-flash-circle'],
            ['name' => 'Legend', 'min_xp' => 100000, 'color' => '#ff00ff', 'icon' => 'mdi-auto-fix'],
            ['name' => 'Mythic', 'min_xp' => 250000, 'color' => '#00ffff', 'icon' => 'mdi-creation'],
            ['name' => 'Immortal', 'min_xp' => 750000, 'color' => '#ff1493', 'icon' => 'mdi-death-star'],
            ['name' => 'Celestial', 'min_xp' => 2000000, 'color' => '#fffacd', 'icon' => 'mdi-weather-night'],
            ['name' => 'Eternal', 'min_xp' => 5000000, 'color' => '#ffffff', 'icon' => 'mdi-infinity'],
        ];
    }

    /**
     * Calculate Rank based on current XP.
     */
    public function calculateRank()
    {
        $config = self::getRankConfig();
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
