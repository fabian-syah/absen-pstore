<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait SendFcmNotification
{
    public function sendNotificationToBranchRoles($roles, $branchId, $title, $body)
    {
        // 1. Cari Audit/Admin di Cabang yang sama yang punya FCM Token
        $tokens = User::whereIn('role', $roles)
            ->where('branch_id', $branchId)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        // 2. Kirim Notifikasi via Firebase (Contoh menggunakan Legacy API atau HTTP v1)
        // Pastikan Anda menaruh FIREBASE_SERVER_KEY di .env
        
        $serverKey = env('FIREBASE_SERVER_KEY'); 
        $url = 'https://fcm.googleapis.com/fcm/send';

        $data = [
            "registration_ids" => $tokens,
            "notification" => [
                "title" => $title,
                "body" => $body,
                "icon" => "ic_launcher", // Sesuaikan icon aplikasi jika ada
                "sound" => "default",
                "click_action" => url('/audit/verify-list') // Link saat notif diklik
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post($url, $data);

            Log::info('FCM Result: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
        }
    }
}