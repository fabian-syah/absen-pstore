<?php

namespace App\Services;

use Carbon\Carbon;
use DateTimeInterface;

class PersonalityService
{
    /**
     * Map of personality types 1-9 based on birthdate numerology / STIFIn typology.
     */
    public const TYPES = [
        1 => [
            'number' => 1,
            'name' => 'Thinking Introvert',
            'code' => 'TI',
            'full' => 'Thinking Introvert (TI)',
            'icon' => 'mdi-brain',
            'color' => '#0284c7',
            'bg_color' => '#e0f2fe',
            'border_color' => '#bae6fd',
            'text_color' => '#0369a1',
            'badge_class' => 'bg-sky-100 text-sky-800 border-sky-300',
            'description' => 'Logis, mandiri, analitis mendalam, dan fokus pada keahlian serta efisiensi sistem.',
        ],
        2 => [
            'number' => 2,
            'name' => 'Feeling Extrovert',
            'code' => 'FE',
            'full' => 'Feeling Extrovert (FE)',
            'icon' => 'mdi-heart-multiple',
            'color' => '#e11d48',
            'bg_color' => '#ffe4e6',
            'border_color' => '#fecdd3',
            'text_color' => '#be123c',
            'badge_class' => 'bg-rose-100 text-rose-800 border-rose-300',
            'description' => 'Sosial, hangat, komunikatif, mudah merangkul orang lain, dan pandai memotivasi.',
        ],
        3 => [
            'number' => 3,
            'name' => 'Sensing Introvert',
            'code' => 'SI',
            'full' => 'Sensing Introvert (SI)',
            'icon' => 'mdi-shield-check',
            'color' => '#059669',
            'bg_color' => '#d1fae5',
            'border_color' => '#a7f3d0',
            'text_color' => '#047857',
            'badge_class' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'description' => 'Disiplin, teliti, mengandalkan ingatan & fakta, tekun, dan pekerja keras yang rapi.',
        ],
        4 => [
            'number' => 4,
            'name' => 'Sensing Extrovert',
            'code' => 'SE',
            'full' => 'Sensing Extrovert (SE)',
            'icon' => 'mdi-lightning-bolt',
            'color' => '#d97706',
            'bg_color' => '#fef3c7',
            'border_color' => '#fde68a',
            'text_color' => '#b45309',
            'badge_class' => 'bg-amber-100 text-amber-800 border-amber-300',
            'description' => 'Aktif, energik, berorientasi aksi langsung, lincah, dan fleksibel menghadapi situasi.',
        ],
        5 => [
            'number' => 5,
            'name' => 'Feeling Introvert',
            'code' => 'FI',
            'full' => 'Feeling Introvert (FI)',
            'icon' => 'mdi-heart-pulse',
            'color' => '#7c3aed',
            'bg_color' => '#ede9fe',
            'border_color' => '#ddd6fe',
            'text_color' => '#6d28d9',
            'badge_class' => 'bg-purple-100 text-purple-800 border-purple-300',
            'description' => 'Penuh empati, setia, idealis, berprinsip mendalam, dan menghargai hubungan personal.',
        ],
        6 => [
            'number' => 6,
            'name' => 'Intuiting Extrovert',
            'code' => 'IE',
            'full' => 'Intuiting Extrovert (IE)',
            'icon' => 'mdi-lightbulb-on',
            'color' => '#4f46e5',
            'bg_color' => '#e0e7ff',
            'border_color' => '#c7d2fe',
            'text_color' => '#4338ca',
            'badge_class' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            'description' => 'Kreatif, visioner, inovatif, pandai menangkap peluang, dan selalu punya ide-ide segar.',
        ],
        7 => [
            'number' => 7,
            'name' => 'Intuiting Introvert',
            'code' => 'II',
            'full' => 'Intuiting Introvert (II)',
            'icon' => 'mdi-compass-rose',
            'color' => '#0d9488',
            'bg_color' => '#ccfbf1',
            'border_color' => '#99f6e4',
            'text_color' => '#0f766e',
            'badge_class' => 'bg-teal-100 text-teal-800 border-teal-300',
            'description' => 'Konseptual, berpikiran jauh ke depan, strategis, orisinal, dan mendalam dalam berpikir.',
        ],
        8 => [
            'number' => 8,
            'name' => 'Thinking Extrovert',
            'code' => 'TE',
            'full' => 'Thinking Extrovert (TE)',
            'icon' => 'mdi-account-tie',
            'color' => '#2563eb',
            'bg_color' => '#dbeafe',
            'border_color' => '#bfdbfe',
            'text_color' => '#1d4ed8',
            'badge_class' => 'bg-blue-100 text-blue-800 border-blue-300',
            'description' => 'Tegas, manajerial, berorientasi target dan hasil, logis objektif, dan berjiwa pemimpin.',
        ],
        9 => [
            'number' => 9,
            'name' => 'Insting',
            'code' => 'Ins',
            'full' => 'Insting (Ins)',
            'icon' => 'mdi-atom-variant',
            'color' => '#b45309',
            'bg_color' => '#ffedd5',
            'border_color' => '#fed7aa',
            'text_color' => '#c2410c',
            'badge_class' => 'bg-orange-100 text-orange-800 border-orange-300',
            'description' => 'Serba bisa, berjiwa penengah, spontan, adaptif, responsif, dan gemar menolong.',
        ],
    ];

    /**
     * Calculate personality data from birth date.
     *
     * @param mixed $birthDate
     * @return array|null
     */
    public function calculate($birthDate): ?array
    {
        if (empty($birthDate)) {
            return null;
        }

        try {
            if (!$birthDate instanceof DateTimeInterface) {
                if (class_exists(Carbon::class)) {
                    $birthDate = Carbon::parse($birthDate);
                } else {
                    $birthDate = new \DateTime((string) $birthDate);
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        $day = (int) $birthDate->format('j');
        $month = (int) $birthDate->format('n');
        $year = (int) $birthDate->format('Y');

        if ($year < 1900 || $day <= 0 || $month <= 0) {
            return null;
        }

        // Step 1: Penjumlahan tanggal lahir + bulan lahir + tahun lahir
        $total = $day + $month + $year;

        // Step 2: Menjumlahkan digit-digit dari hasil penjumlahan
        $sumDigits = array_sum(str_split((string) $total));

        // Step 3: Jika hasilnya masih di atas 9, kurangkan 9 sampai menghasilkan angka <= 9
        $finalNumber = $sumDigits;
        while ($finalNumber > 9) {
            $finalNumber -= 9;
        }

        if (!isset(self::TYPES[$finalNumber])) {
            return null;
        }

        $result = self::TYPES[$finalNumber];
        $result['calculation'] = [
            'day' => $day,
            'month' => $month,
            'year' => $year,
            'sum_step1' => $total,
            'sum_step2' => $sumDigits,
            'final_number' => $finalNumber,
        ];

        return $result;
    }
}
