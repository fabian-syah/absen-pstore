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
            'color' => '#334155',
            'bg_color' => '#f8fafc',
            'border_color' => '#94a3b8',
            'text_color' => '#0f172a',
            'badge_class' => 'bg-slate-100 text-slate-900 border-slate-300',
            'description' => 'Fokus pada data, analisa logis mendalam, keahlian mandiri, dan ketepatan sistem.',
        ],
        2 => [
            'number' => 2,
            'name' => 'Feeling Extrovert',
            'code' => 'FE',
            'full' => 'Feeling Extrovert (FE)',
            'icon' => 'mdi-account-group',
            'color' => '#16a34a',
            'bg_color' => '#f0fdf4',
            'border_color' => '#86efac',
            'text_color' => '#166534',
            'badge_class' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'description' => 'Komunikatif, mudah merangkul orang lain, memotivasi tim, dan pandai menjalin relasi.',
        ],
        3 => [
            'number' => 3,
            'name' => 'Sensing Introvert',
            'code' => 'SI',
            'full' => 'Sensing Introvert (SI)',
            'icon' => 'mdi-shield-check',
            'color' => '#dc2626',
            'bg_color' => '#fef2f2',
            'border_color' => '#fca5a5',
            'text_color' => '#991b1b',
            'badge_class' => 'bg-red-100 text-red-800 border-red-300',
            'description' => 'Disiplin tinggi, teliti, mengandalkan bukti nyata, tekun, dan pekerja keras yang teratur.',
        ],
        4 => [
            'number' => 4,
            'name' => 'Sensing Extrovert',
            'code' => 'SE',
            'full' => 'Sensing Extrovert (SE)',
            'icon' => 'mdi-lightning-bolt',
            'color' => '#e11d48',
            'bg_color' => '#fff1f2',
            'border_color' => '#fda4af',
            'text_color' => '#9f1239',
            'badge_class' => 'bg-rose-100 text-rose-800 border-rose-300',
            'description' => 'Gesit, penuh energi, tanggap aksi lapangan, dan fleksibel menghadapi berbagai situasi kerja.',
        ],
        5 => [
            'number' => 5,
            'name' => 'Feeling Introvert',
            'code' => 'FI',
            'full' => 'Feeling Introvert (FI)',
            'icon' => 'mdi-heart-pulse',
            'color' => '#059669',
            'bg_color' => '#ecfdf5',
            'border_color' => '#6ee7b7',
            'text_color' => '#065f46',
            'badge_class' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
            'description' => 'Berprinsip kuat, empati tinggi, tenang, setia, dan menghargai keharmonisan kerja.',
        ],
        6 => [
            'number' => 6,
            'name' => 'Intuiting Extrovert',
            'code' => 'IE',
            'full' => 'Intuiting Extrovert (IE)',
            'icon' => 'mdi-lightbulb-on',
            'color' => '#2563eb',
            'bg_color' => '#eff6ff',
            'border_color' => '#93c5fd',
            'text_color' => '#1e40af',
            'badge_class' => 'bg-blue-100 text-blue-800 border-blue-300',
            'description' => 'Kreatif, inovatif, pandai melihat peluang baru, dan visioner dalam mengembangkan ide.',
        ],
        7 => [
            'number' => 7,
            'name' => 'Intuiting Introvert',
            'code' => 'II',
            'full' => 'Intuiting Introvert (II)',
            'icon' => 'mdi-compass-outline',
            'color' => '#0284c7',
            'bg_color' => '#f0f9ff',
            'border_color' => '#7dd3fc',
            'text_color' => '#075985',
            'badge_class' => 'bg-sky-100 text-sky-800 border-sky-300',
            'description' => 'Strategis, konseptual, orisinal, dan memiliki pandangan masa depan yang mendalam.',
        ],
        8 => [
            'number' => 8,
            'name' => 'Thinking Extrovert',
            'code' => 'TE',
            'full' => 'Thinking Extrovert (TE)',
            'icon' => 'mdi-account-tie',
            'color' => '#1e293b',
            'bg_color' => '#f1f5f9',
            'border_color' => '#64748b',
            'text_color' => '#0f172a',
            'badge_class' => 'bg-slate-200 text-slate-900 border-slate-400',
            'description' => 'Tegas, terstruktur, berorientasi target hasil, dan memimpin dengan logika objektif.',
        ],
        9 => [
            'number' => 9,
            'name' => 'Insting',
            'code' => 'Insting',
            'full' => 'Insting',
            'icon' => 'mdi-atom',
            'color' => '#d97706',
            'bg_color' => '#fffbeb',
            'border_color' => '#fcd34d',
            'text_color' => '#92400e',
            'badge_class' => 'bg-amber-100 text-amber-900 border-amber-300',
            'description' => 'Serba bisa, spontan, cinta damai, adaptif, cepat merespons, dan berjiwa penolong.',
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
