<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;

class PushBroadcastController extends Controller
{
    /**
     * Tampilkan form kirim push notification broadcast.
     */
    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $roles = ['admin', 'audit', 'user', 'security'];

        return view('push-broadcast.create', compact('branches', 'roles'));
    }

    /**
     * Kirim push notification ke semua user yang dipilih.
     */
    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'target' => 'required|in:all,branch,role',
            'branch_id' => 'nullable|exists:branches,id',
            'roles' => 'nullable|array',
            'roles.*' => 'in:admin,audit,user,security',
        ]);

        // Build query untuk target users
        $query = User::where('is_active', 1)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '');

        if ($request->target === 'branch' && $request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->target === 'role' && !empty($request->roles)) {
            $query->whereIn('role', $request->roles);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            return back()->with('warning', 'Tidak ada user dengan FCM token yang ditemukan untuk target yang dipilih.');
        }

        // Kirim push notification
        $results = $this->sendPushToUsers($users, $request->title, $request->body);

        // Simpan hasil ke session untuk ditampilkan
        return redirect()->route('push-broadcast.result')
            ->with('push_results', $results)
            ->with('push_title', $request->title)
            ->with('push_body', $request->body);
    }

    /**
     * Tampilkan hasil pengiriman push notification.
     */
    public function result()
    {
        $results = session('push_results');
        $title = session('push_title');
        $body = session('push_body');

        if (!$results) {
            return redirect()->route('push-broadcast.create')
                ->with('warning', 'Tidak ada hasil pengiriman. Silakan kirim ulang.');
        }

        return view('push-broadcast.result', compact('results', 'title', 'body'));
    }

    /**
     * Kirim FCM push notification ke list users.
     */
    private function sendPushToUsers($users, $title, $body)
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total' => $users->count(),
        ];

        // Ambil Access Token Firebase
        $credentialsPath = storage_path('app/firebase_credentials.json');

        try {
            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
            $credentials = new ServiceAccountCredentials($scopes, $credentialsPath);

            $httpClient = new Client(['verify' => false]);
            $accessToken = $credentials->fetchAuthToken(function ($request) use ($httpClient) {
                return $httpClient->send($request);
            });

            if (!isset($accessToken['access_token'])) {
                // Semua gagal karena auth error
                foreach ($users as $user) {
                    $results['failed'][] = [
                        'name' => $user->name,
                        'reason' => 'Gagal generate access token Firebase',
                    ];
                }
                return $results;
            }
        } catch (\Exception $e) {
            Log::error('Push Broadcast Auth Error: ' . $e->getMessage());
            foreach ($users as $user) {
                $results['failed'][] = [
                    'name' => $user->name,
                    'reason' => 'Error autentikasi Firebase: ' . $e->getMessage(),
                ];
            }
            return $results;
        }

        $projectId = env('FIREBASE_PROJECT_ID', 'bote-1a4b9');
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        // Kirim ke setiap user
        foreach ($users as $user) {
            $payload = [
                "message" => [
                    "token" => $user->fcm_token,
                    "notification" => [
                        "title" => $title,
                        "body" => $body,
                    ],
                    "webpush" => [
                        "headers" => [
                            "Urgency" => "high"
                        ],
                        "notification" => [
                            "title" => $title,
                            "body" => $body,
                            "icon" => "https://cdn-icons-png.flaticon.com/512/1827/1827301.png",
                        ],
                    ],
                    "data" => [
                        "title" => (string) $title,
                        "body" => (string) $body,
                        "type" => "broadcast_push"
                    ]
                ]
            ];

            try {
                $response = Http::withoutVerifying()
                    ->withToken($accessToken['access_token'])
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, $payload);

                if ($response->successful()) {
                    $results['success'][] = [
                        'name' => $user->name,
                        'branch' => $user->branch->name ?? '-',
                    ];
                } else {
                    $responseBody = $response->json();
                    $reason = $responseBody['error']['message'] ?? 'Unknown error';

                    // Cek jika token sudah tidak valid
                    if (
                        isset($responseBody['error']['details'][0]['errorCode']) &&
                        $responseBody['error']['details'][0]['errorCode'] === 'UNREGISTERED'
                    ) {
                        $user->update(['fcm_token' => null]);
                        $reason = 'Token tidak valid (expired/unregistered)';
                    }

                    $results['failed'][] = [
                        'name' => $user->name,
                        'branch' => $user->branch->name ?? '-',
                        'reason' => $reason,
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Push Broadcast Send Error for ' . $user->name . ': ' . $e->getMessage());
                $results['failed'][] = [
                    'name' => $user->name,
                    'branch' => $user->branch->name ?? '-',
                    'reason' => 'Exception: ' . $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
