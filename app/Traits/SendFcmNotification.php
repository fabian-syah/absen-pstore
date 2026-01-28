<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;

trait SendFcmNotification
{
    public function sendNotificationToBranchRoles($roles, $branchId, $title, $body)
    {
        $detailedResponses = [];

        // 1. Cari Token
        Log::info("FCM: Mencari token untuk Role: " . json_encode($roles) . " Branch ID: " . $branchId);

        $query = User::whereIn('role', $roles)
            ->whereNotNull('fcm_token');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $tokens = $query->pluck('fcm_token')->toArray();

        Log::info("FCM: Ditemukan " . count($tokens) . " token.");

        if (empty($tokens)) {
            echo " [FCM SKIP] Tidak ada token ditemukan.\n";
            Log::info("FCM: Tidak ada token user audit/admin ditemukan untuk cabang ID $branchId");
            return [['status' => 'SKIP', 'reason' => 'Zero tokens found for query']];
        }

        // 2. Ambil Access Token
        $credentialsPath = storage_path('app/firebase_credentials.json');

        try {
            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
            $credentials = new ServiceAccountCredentials($scopes, $credentialsPath);

            // Bypass SSL (Fix cURL error 60)
            $httpClient = new Client(['verify' => false]);
            $accessToken = $credentials->fetchAuthToken($httpClient);

            if (!isset($accessToken['access_token'])) {
                Log::error('FCM Error: Gagal generate access token.');
                return [['status' => 'FAIL', 'reason' => 'Gagal generate access token google/auth']];
            }
        } catch (\Exception $e) {
            Log::error('FCM Auth Error: ' . $e->getMessage());
            return [['status' => 'ERROR', 'reason' => 'Exception Auth: ' . $e->getMessage() . ' | Path: ' . $credentialsPath]];
        }

        $projectId = env('FIREBASE_PROJECT_ID', 'bote-1a4b9');
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        $link = url('/audit/verify-list'); // Link tujuan saat diklik

        // 3. Kirim ke Setiap Token
        foreach ($tokens as $token) {
            $payload = [
                "message" => [
                    "token" => $token,

                    // A. Bagian NOTIFICATION (Standar agar muncul otomatis di Background)
                    "notification" => [
                        "title" => $title,
                        "body" => $body,
                        "sound" => "default"
                    ],

                    // B. Bagian WEBPUSH (Khusus Chrome/Edge Desktop/Android)
                    "webpush" => [
                        "headers" => [
                            "Urgency" => "high"
                        ],
                        "notification" => [
                            "title" => $title,
                            "body" => $body,
                            "icon" => "https://cdn-icons-png.flaticon.com/512/1827/1827301.png", // Icon Online (Aman)
                            "click_action" => $link
                        ],
                        "fcm_options" => [
                            "link" => $link
                        ]
                    ],

                    // C. Bagian DATA (Untuk custom handler di JS Foreground)
                    "data" => [
                        "title" => (string) $title,
                        "body" => (string) $body,
                        "url" => (string) $link,
                        "type" => "audit_alert"
                    ]
                ]
            ];

            try {
                $response = Http::withoutVerifying()
                    ->withToken($accessToken['access_token'])
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, $payload);

                if ($response->successful()) {
                    $responseBody = $response->json();
                    $detailedResponses[] = ['status' => 'SUCCESS', 'token' => substr($token, 0, 10) . '...', 'response' => $responseBody];
                    echo " [FCM SUCCESS] " . json_encode($responseBody) . "\n";
                    Log::info('FCM Success: ' . substr($response->body(), 0, 50));
                } else {
                    $detailedResponses[] = ['status' => 'FAIL', 'token' => substr($token, 0, 10) . '...', 'response' => $response->body()];
                    echo " [FCM FAIL] " . $response->body() . "\n";
                    Log::error('FCM Failed: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('FCM Send Error: ' . $e->getMessage());
            }
        }

        return $detailedResponses;
    }
}