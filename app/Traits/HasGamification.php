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
            // 1. The Foundation (Level 1-5)
            ['level' => 1, 'name' => 'Novice', 'min_xp' => 0, 'color' => '#6B7280', 'icon' => 'mdi-stone-variant', 'rank_image' => '/public/rank_icons/rank1.png', 'category' => 'foundation', 'effect_class' => 'rank-foundation'],
            ['level' => 2, 'name' => 'Apprentice', 'min_xp' => 100, 'color' => '#92400E', 'icon' => 'mdi-wood', 'rank_image' => '/public/rank_icons/rank2.png', 'category' => 'foundation', 'effect_class' => 'rank-foundation'],
            ['level' => 3, 'name' => 'Iron', 'min_xp' => 300, 'color' => '#1F2937', 'icon' => 'mdi-anvil', 'rank_image' => '/public/rank_icons/rank3.png', 'category' => 'foundation', 'effect_class' => 'rank-foundation'],
            ['level' => 4, 'name' => 'Bronze', 'min_xp' => 600, 'color' => '#B45309', 'icon' => 'mdi-shield-outline', 'rank_image' => '/public/rank_icons/rank4.png', 'category' => 'foundation', 'effect_class' => 'rank-foundation'],
            ['level' => 5, 'name' => 'Silver', 'min_xp' => 1000, 'color' => '#9CA3AF', 'icon' => 'mdi-shield-check-outline', 'rank_image' => '/public/rank_icons/rank5.png', 'category' => 'foundation', 'effect_class' => 'rank-foundation'],

            // 2. The Elite (Level 6-10)
            ['level' => 6, 'name' => 'Gold', 'min_xp' => 1500, 'color' => '#FBBF24', 'icon' => 'mdi-trophy-outline', 'rank_image' => '/public/rank_icons/rank6.png', 'category' => 'elite', 'effect_class' => 'rank-elite'],
            ['level' => 7, 'name' => 'Platinum', 'min_xp' => 2500, 'color' => '#CBD5E1', 'icon' => 'mdi-trophy-variant', 'rank_image' => '/public/rank_icons/rank7.png', 'category' => 'elite', 'effect_class' => 'rank-platinum'],
            ['level' => 8, 'name' => 'Diamond', 'min_xp' => 4000, 'color' => '#22D3EE', 'icon' => 'mdi-diamond-stone', 'rank_image' => '/public/rank_icons/rank8.png', 'category' => 'elite', 'effect_class' => 'rank-elite'],
            ['level' => 9, 'name' => 'Emerald', 'min_xp' => 6000, 'color' => '#10B981', 'icon' => 'mdi-gemstone-outline', 'rank_image' => '/public/rank_icons/rank9.png', 'category' => 'elite', 'effect_class' => 'rank-elite'],
            ['level' => 10, 'name' => 'Ruby', 'min_xp' => 9000, 'color' => '#EF4444', 'icon' => 'mdi-ruby', 'rank_image' => '/public/rank_icons/rank10.png', 'category' => 'elite', 'effect_class' => 'rank-elite'],

            // 3. The Masterclass (Level 11-15)
            ['level' => 11, 'name' => 'Sapphire', 'min_xp' => 13000, 'color' => '#2563EB', 'icon' => 'mdi-star-face', 'rank_image' => '/public/rank_icons/rank11.png', 'category' => 'masterclass', 'effect_class' => 'rank-masterclass'],
            ['level' => 12, 'name' => 'Crystal', 'min_xp' => 18000, 'color' => '#F0ABFC', 'icon' => 'mdi-crystal-ball', 'rank_image' => '/public/rank_icons/rank12.png', 'category' => 'masterclass', 'effect_class' => 'rank-masterclass'],
            ['level' => 13, 'name' => 'Master', 'min_xp' => 25000, 'color' => '#7C3AED', 'icon' => 'mdi-crown-outline', 'rank_image' => '/public/rank_icons/rank13.png', 'category' => 'masterclass', 'effect_class' => 'rank-masterclass'],
            ['level' => 14, 'name' => 'Grandmaster', 'min_xp' => 35000, 'color' => '#F59E0B', 'icon' => 'mdi-crown', 'rank_image' => '/public/rank_icons/rank14.png', 'category' => 'masterclass', 'effect_class' => 'rank-masterclass'],
            ['level' => 15, 'name' => 'Epic', 'min_xp' => 50000, 'color' => '#06B6D4', 'icon' => 'mdi-flash-circle', 'rank_image' => '/public/rank_icons/rank15.png', 'category' => 'masterclass', 'effect_class' => 'rank-masterclass'],

            // 4. The Godlike (Level 16-20)
            ['level' => 16, 'name' => 'Legend', 'min_xp' => 100000, 'color' => '#FFFBEB', 'icon' => 'mdi-auto-fix', 'rank_image' => '/public/rank_icons/rank16.png', 'category' => 'godlike', 'effect_class' => 'rank-legend'],
            ['level' => 17, 'name' => 'Mythic', 'min_xp' => 250000, 'color' => '#581C87', 'icon' => 'mdi-creation', 'rank_image' => '/public/rank_icons/rank17.png', 'category' => 'godlike', 'effect_class' => 'rank-mythic'],
            ['level' => 18, 'name' => 'Immortal', 'min_xp' => 750000, 'color' => '#B91C1C', 'icon' => 'mdi-fire', 'rank_image' => '/public/rank_icons/rank18.png', 'category' => 'godlike', 'effect_class' => 'rank-immortal'],
            ['level' => 19, 'name' => 'Celestial', 'min_xp' => 2000000, 'color' => '#BAE6FD', 'icon' => 'mdi-weather-night', 'rank_image' => null, 'category' => 'godlike', 'effect_class' => 'rank-celestial'],
            ['level' => 20, 'name' => 'Eternal', 'min_xp' => 5000000, 'color' => '#000000', 'icon' => 'mdi-infinity', 'rank_image' => null, 'category' => 'godlike', 'effect_class' => 'rank-eternal'],
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
