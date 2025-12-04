<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Auth\Credentials\ServiceAccountCredentials;

trait SendFcmNotification
{
    public function sendNotificationToBranchRoles($roles, $branchId, $title, $body)
    {
        // 1. Cari Token FCM milik Audit/Admin di cabang yang sama
        $tokens = User::whereIn('role', $roles)
            ->where('branch_id', $branchId)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            Log::info('FCM: Tidak ada token user audit/admin ditemukan untuk cabang ID ' . $branchId);
            return;
        }

        // 2. Ambil Access Token dari File JSON (Pengganti Server Key)
        $credentialsPath = storage_path('app/firebase_credentials.json');
        
        if (!file_exists($credentialsPath)) {
            Log::error('FCM Error: File firebase_credentials.json tidak ditemukan di storage/app/');
            return;
        }

        // Generate OAuth2 Token dari Google
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        
        try {
            $credentials = new ServiceAccountCredentials($scopes, $credentialsPath);
            $accessToken = $credentials->fetchAuthToken();

            if (!isset($accessToken['access_token'])) {
                Log::error('FCM Error: Gagal generate access token.');
                return;
            }
        } catch (\Exception $e) {
            Log::error('FCM Auth Error: ' . $e->getMessage());
            return;
        }

        // 3. Konfigurasi URL HTTP v1
        // Project ID diambil dari file JSON atau .env (Pastikan sama: bote-1a4b9)
        $projectId = 'bote-1a4b9'; 
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        // 4. Kirim ke SETIAP Token (HTTP v1 butuh loop atau topic)
        foreach ($tokens as $token) {
            $payload = [
                "message" => [
                    "token" => $token,
                    "notification" => [
                        "title" => $title,
                        "body"  => $body,
                    ],
                    "webpush" => [
                        "headers" => [
                            "Urgency" => "high"
                        ],
                        "fcm_options" => [
                            "link" => url('/audit/verify-list') // Link saat notif diklik
                        ]
                    ]
                ]
            ];

            try {
                $response = Http::withToken($accessToken['access_token'])
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, $payload);

                // Log hasil sukses/gagal
                if ($response->successful()) {
                    Log::info('FCM Success: ' . $response->body());
                } else {
                    Log::error('FCM Failed: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('FCM Send Error: ' . $e->getMessage());
            }
        }
    }
}