<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client; // Tambahkan ini

trait SendFcmNotification
{
    public function sendNotificationToBranchRoles($roles, $branchId, $title, $body)
    {
        // 1. Cari Token User Target
        $tokens = User::whereIn('role', $roles)
            ->where('branch_id', $branchId)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            Log::info("FCM: Tidak ada token user audit/admin ditemukan untuk cabang ID $branchId");
            return;
        }

        // 2. Cek File Credentials
        $credentialsPath = storage_path('app/firebase_credentials.json');
        if (!file_exists($credentialsPath)) {
            Log::error('FCM Error: File firebase_credentials.json tidak ditemukan.');
            return;
        }

        // 3. Generate OAuth2 Token (DENGAN BYPASS SSL)
        // Ini perbaikan utama untuk Error cURL 60
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

        try {
            $credentials = new ServiceAccountCredentials($scopes, $credentialsPath);

            // Buat Client Guzzle khusus yang mematikan verifikasi SSL
            $httpClient = new Client(['verify' => false]);

            // Minta token menggunakan client tersebut
            $accessToken = $credentials->fetchAuthToken($httpClient);

            if (!isset($accessToken['access_token'])) {
                Log::error('FCM Error: Gagal generate access token (Empty result).');
                return;
            }
        } catch (\Exception $e) {
            Log::error('FCM Auth Error: ' . $e->getMessage());
            return;
        }

        // 4. Konfigurasi URL HTTP v1
        $projectId = 'bote-1a4b9'; // Sesuai log anda
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        // 4. Kirim ke SETIAP Token
        foreach ($tokens as $token) {
            $payload = [
                "message" => [
                    "token" => $token,
                    
                    // [PERBAIKAN UTAMA] Gunakan 'notification' object standar
                    "notification" => [
                        "title" => $title,
                        "body"  => $body,
                    ],
                    // Data tambahan untuk logika klik (URL redirection)
                    "data" => [
                        "click_action" => url('/audit/verifikasi/absensi'), // Link halaman verifikasi
                        "type" => "audit_alert"
                    ],
                    // Konfigurasi agar Prioritas Tinggi (Muncul di layar)
                    "android" => [
                        "priority" => "high",
                        "notification" => [
                            "channel_id" => "default_channel",
                            "default_sound" => true,
                            "default_vibrate_timings" => true,
                            "click_action" => url('/audit/verifikasi/absensi')
                        ]
                    ],
                    "webpush" => [
                        "headers" => [
                            "Urgency" => "high"
                        ],
                        "fcm_options" => [
                            "link" => url('/audit/verifikasi/absensi')
                        ]
                    ]
                ]
            ];

            try {
                $response = Http::withoutVerifying() // Bypass SSL jika perlu
                    ->withToken($accessToken['access_token'])
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, $payload);

                if ($response->successful()) {
                    Log::info('FCM Success: ' . substr($response->body(), 0, 100));
                } else {
                    Log::error('FCM Failed: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('FCM Send Error: ' . $e->getMessage());
            }
        }
    }
}
