<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

trait SendWebPushNotification
{
    /**
     * Send Web Push notification to specific users.
     */
    public function sendWebPushToBranchRoles($roles, $branchId, $title, $body, $url = null)
    {
        $query = User::whereIn('role', $roles)
            ->whereNotNull('push_subscription');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            Log::info("WebPush: No users with subscriptions found for roles: " . implode(', ', $roles));
            return [['status' => 'SKIP', 'reason' => 'No active subscriptions']];
        }

        // Setup WebPush dengan VAPID Keys Permanen
        $auth = [
            'VAPID' => [
                'subject' => 'mailto:khusussharebian@gmail.com',
                'publicKey' => 'BCdgL0IeSqxtiJT-ymrp1RRF-1wy8-Y74PY_LZ3S7z93noZNnL19bLTXcxR-I9iPvgbKI8KuWbLObuKJsj9Skmw',
                'privateKey' => 'hE3A9x0-50D6mYyN3Q8p3v5X8v6Z8n9W8z7L8k7J8iM', // Kunci privat pasangan public di atas
            ],
        ];

        $webPush = new WebPush($auth);
        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url ?? url('/'),
            'icon' => asset('img/icon.png'), // Sesuaikan path icon Anda
        ]);

        $results = [];
        foreach ($users as $user) {
            $subData = is_string($user->push_subscription)
                ? json_decode($user->push_subscription, true)
                : $user->push_subscription;

            // Masukkan ke antrean pengiriman
            $webPush->queueNotification(
                Subscription::create($subData),
                $payload
            );
        }

        // Jalankan pengiriman massal
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getEndpoint();
            if ($report->isSuccess()) {
                $results[] = ['status' => 'SUCCESS', 'endpoint' => $endpoint];
                Log::info("WebPush: Success for {$endpoint}");
            } else {
                $results[] = ['status' => 'FAIL', 'endpoint' => $endpoint, 'reason' => $report->getReason()];
                Log::error("WebPush: Failed for {$endpoint}: {$report->getReason()}");

                // Jika errornya karena subscription expired/invalid, hapus dari DB
                if ($report->isSubscriptionExpired()) {
                    User::where('push_subscription->endpoint', $endpoint)->update(['push_subscription' => null]);
                }
            }
        }

        return $results;
    }

    /**
     * Send Web Push notification to a single user.
     */
    public function sendWebPushToUser($user, $title, $body, $url = null)
    {
        if (!$user || !$user->push_subscription) {
            return false;
        }

        $auth = [
            'VAPID' => [
                'subject' => 'mailto:khusussharebian@gmail.com',
                'publicKey' => 'BCdgL0IeSqxtiJT-ymrp1RRF-1wy8-Y74PY_LZ3S7z93noZNnL19bLTXcxR-I9iPvgbKI8KuWbLObuKJsj9Skmw',
                'privateKey' => 'hE3A9x0-50D6mYyN3Q8p3v5X8v6Z8n9W8z7L8k7J8iM',
            ],
        ];

        $webPush = new WebPush($auth);
        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url ?? url('/'),
            'icon' => asset('img/icon.png'),
        ]);

        $subData = is_string($user->push_subscription)
            ? json_decode($user->push_subscription, true)
            : $user->push_subscription;

        $webPush->queueNotification(
            Subscription::create($subData),
            $payload
        );

        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                return true;
            } else {
                Log::error("WebPush Single Fail: " . $report->getReason());
                if ($report->isSubscriptionExpired()) {
                    $user->update(['push_subscription' => null]);
                }
                return false;
            }
        }

        return false;
    }
}
