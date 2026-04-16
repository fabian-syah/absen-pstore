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
            ['level' => 1, 'name' => 'Novice', 'min_xp' => 0, 'color' => '#94a3b8', 'icon' => 'mdi-account-outline'],
            ['level' => 2, 'name' => 'Apprentice', 'min_xp' => 100, 'color' => '#64748b', 'icon' => 'mdi-account-badge-outline'],
            ['level' => 3, 'name' => 'Iron', 'min_xp' => 300, 'color' => '#475569', 'icon' => 'mdi-shield-outline'],
            ['level' => 4, 'name' => 'Bronze', 'min_xp' => 600, 'color' => '#865231', 'icon' => 'mdi-shield-half-full'],
            ['level' => 5, 'name' => 'Silver', 'min_xp' => 1000, 'color' => '#9ca3af', 'icon' => 'mdi-shield-check'],
            ['level' => 6, 'name' => 'Gold', 'min_xp' => 1500, 'color' => '#fbbf24', 'icon' => 'mdi-trophy-outline'],
            ['level' => 7, 'name' => 'Platinum', 'min_xp' => 2500, 'color' => '#b4d4ff', 'icon' => 'mdi-trophy-variant'],
            ['level' => 8, 'name' => 'Diamond', 'min_xp' => 4000, 'color' => '#22d3ee', 'icon' => 'mdi-diamond-stone'],
            ['level' => 9, 'name' => 'Emerald', 'min_xp' => 6000, 'color' => '#10b981', 'icon' => 'mdi-gemstone-outline'],
            ['level' => 10, 'name' => 'Ruby', 'min_xp' => 9000, 'color' => '#ef4444', 'icon' => 'mdi-ruby'],
            ['level' => 11, 'name' => 'Sapphire', 'min_xp' => 13000, 'color' => '#3b82f6', 'icon' => 'mdi-star-face'],
            ['level' => 12, 'name' => 'Crystal', 'min_xp' => 18000, 'color' => '#afeeee', 'icon' => 'mdi-crystal-ball'],
            ['level' => 13, 'name' => 'Master', 'min_xp' => 25000, 'color' => '#f59e0b', 'icon' => 'mdi-crown-outline'],
            ['level' => 14, 'name' => 'Grandmaster', 'min_xp' => 35000, 'color' => '#8b5cf6', 'icon' => 'mdi-crown'],
            ['level' => 15, 'name' => 'Epic', 'min_xp' => 50000, 'color' => '#f87171', 'icon' => 'mdi-flash-circle'],
            ['level' => 16, 'name' => 'Legend', 'min_xp' => 100000, 'color' => '#ec4899', 'icon' => 'mdi-auto-fix'],
            ['level' => 17, 'name' => 'Mythic', 'min_xp' => 250000, 'color' => '#06b6d4', 'icon' => 'mdi-creation'],
            ['level' => 18, 'name' => 'Immortal', 'min_xp' => 750000, 'color' => '#991b1b', 'icon' => 'mdi-death-star'],
            ['level' => 19, 'name' => 'Celestial', 'min_xp' => 2000000, 'color' => '#fef08a', 'icon' => 'mdi-weather-night'],
            ['level' => 20, 'name' => 'Eternal', 'min_xp' => 5000000, 'color' => '#ffffff', 'icon' => 'mdi-infinity'],
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
