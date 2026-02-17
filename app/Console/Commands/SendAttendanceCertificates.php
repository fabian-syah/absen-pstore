<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\User;
use App\Traits\SendFcmNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendAttendanceCertificates extends Command
{
    use SendFcmNotification;

    protected $signature = 'certificates:send 
                            {--month= : Bulan (angka 1-12)} 
                            {--year= : Tahun (4 digit)}
                            {--dry-run : Jalankan tanpa mengirim notifikasi}';

    protected $description = 'Kirim sertifikat penghargaan ke Top 3 absensi per cabang';

    public function handle()
    {
        // 1. TENTUKAN PERIODE WAKTU
        $inputMonth = $this->option('month');
        $inputYear = $this->option('year');
        $dryRun = $this->option('dry-run');

        if ($inputMonth && $inputYear) {
            $month = (int) $inputMonth;
            $year = (int) $inputYear;
        } else {
            // Default: bulan lalu
            $lastMonthDate = Carbon::now()->subMonth();
            $month = $lastMonthDate->month;
            $year = $lastMonthDate->year;
        }

        if ($month < 1 || $month > 12 || $year < 2020 || $year > 2030) {
            $this->error('Error: Format Bulan atau Tahun salah.');
            return 1;
        }

        $periodName = Carbon::create($year, $month, 1)->translatedFormat('F Y');
        $this->info("🏆 Mengirim Sertifikat Top 3 Absensi - {$periodName}");
        $this->newLine();

        if ($dryRun) {
            $this->warn("⚠️  Mode DRY RUN: Tidak akan mengirim notifikasi");
            $this->newLine();
        }

        // 2. AMBIL SEMUA CABANG AKTIF
        $branches = Branch::where('is_active', true)->get();
        $totalSent = 0;

        foreach ($branches as $branch) {
            $this->line("📍 Cabang: {$branch->name}");

            // 3. HITUNG TOP 3 PER CABANG
            $branchTimezone = $branch->timezone ?? 'Asia/Jakarta';
            $branchOffset = Carbon::now($branchTimezone)->format('P');
            $appOffset = Carbon::now(config('app.timezone'))->format('P');

            $winners = Attendance::select('user_id', DB::raw('count(*) as total_attendance'))
                ->where('branch_id', $branch->id)
                ->whereRaw("MONTH(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$appOffset, $branchOffset, $month])
                ->whereRaw("YEAR(CONVERT_TZ(check_in_time, ?, ?)) = ?", [$appOffset, $branchOffset, $year])
                ->whereIn('presence_status', ['Masuk', 'WFH', 'WFH / Dinas Luar', 'Hadir', 'Tepat Waktu'])
                ->where('status', 'verified')
                ->whereHas('user', function ($q) {
                    $q->where('is_active', true)
                        ->whereNotIn('role', ['admin', 'security']);
                })
                ->groupBy('user_id')
                ->orderByDesc('total_attendance')
                ->orderBy(DB::raw("MIN(TIME(CONVERT_TZ(check_in_time, '$appOffset', '$branchOffset')))"), 'asc')
                ->take(3)
                ->with('user')
                ->get();

            if ($winners->isEmpty()) {
                $this->warn("   ⚠️  Tidak ada data untuk cabang ini.");
                continue;
            }

            // 4. KIRIM NOTIFIKASI KE SETIAP PEMENANG
            foreach ($winners as $index => $winner) {
                $rank = $index + 1;
                $user = $winner->user;

                if (!$user)
                    continue;

                $rankEmoji = match ($rank) {
                    1 => '🥇',
                    2 => '🥈',
                    3 => '🥉',
                    default => '🎖️'
                };

                $rankText = match ($rank) {
                    1 => 'JUARA 1',
                    2 => 'JUARA 2',
                    3 => 'JUARA 3',
                    default => "Peringkat $rank"
                };

                $title = "{$rankEmoji} Selamat! Anda {$rankText} Absensi {$periodName}";
                $body = "Anda meraih {$rankText} dengan {$winner->total_attendance} hari kehadiran di cabang {$branch->name}. Sertifikat penghargaan Anda dapat dilihat di profil. Terima kasih atas dedikasi Anda! 🎉";

                $this->line("   {$rankEmoji} #{$rank} {$user->name} ({$winner->total_attendance} hari)");

                if (!$dryRun && $user->fcm_token) {
                    try {
                        $this->sendCertificateNotification($user, $title, $body, $rank, $periodName);
                        $totalSent++;
                    } catch (\Exception $e) {
                        Log::error("Certificate notification error: " . $e->getMessage());
                        $this->error("      ❌ Gagal kirim ke {$user->name}");
                    }
                } elseif (!$user->fcm_token) {
                    $this->warn("      ⚠️  Tidak ada FCM token");
                }
            }

            $this->newLine();
        }

        $this->info("✅ Selesai! Total notifikasi terkirim: {$totalSent}");

        return 0;
    }

    /**
     * Kirim notifikasi sertifikat ke user tertentu
     */
    private function sendCertificateNotification(User $user, string $title, string $body, int $rank, string $period)
    {
        $credentialsPath = storage_path('app/firebase_credentials.json');

        if (!file_exists($credentialsPath)) {
            Log::error('Firebase credentials not found');
            return false;
        }

        try {
            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
            $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials($scopes, $credentialsPath);

            $httpClient = new \GuzzleHttp\Client(['verify' => false]);
            $accessToken = $credentials->fetchAuthToken(function ($request) use ($httpClient) {
                return $httpClient->send($request);
            });

            if (!isset($accessToken['access_token'])) {
                Log::error('FCM: Gagal generate access token');
                return false;
            }

            $projectId = env('FIREBASE_PROJECT_ID', 'bote-1a4b9');
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            // Link ke halaman sertifikat
            $certificateUrl = url('/attendance-certificate?period=' . urlencode($period) . '&rank=' . $rank);

            $payload = [
                "message" => [
                    "token" => $user->fcm_token,
                    "notification" => [
                        "title" => $title,
                        "body" => $body,
                    ],
                    "webpush" => [
                        "headers" => ["Urgency" => "high"],
                        "notification" => [
                            "title" => $title,
                            "body" => $body,
                            "icon" => "https://cdn-icons-png.flaticon.com/512/3135/3135789.png",
                            "badge" => "https://cdn-icons-png.flaticon.com/512/744/744922.png",
                            "click_action" => $certificateUrl
                        ],
                        "fcm_options" => ["link" => $certificateUrl]
                    ],
                    "data" => [
                        "type" => "certificate",
                        "rank" => (string) $rank,
                        "period" => $period,
                        "url" => $certificateUrl
                    ]
                ]
            ];

            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->withToken($accessToken['access_token'])
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->successful()) {
                $this->info("      ✅ Notifikasi terkirim ke {$user->name}");
                return true;
            } else {
                Log::warning("FCM Certificate Failed: " . $response->body());
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Certificate FCM Error: ' . $e->getMessage());
            return false;
        }
    }
}
