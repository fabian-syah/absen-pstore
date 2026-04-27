<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // <--- Implement ShouldQueue is key for background jobs
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\SendWebPushNotification;
use Illuminate\Support\Facades\Log;

class SendAuditNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SendWebPushNotification;

    protected $roles;
    protected $branchId;
    protected $title;
    protected $body;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($roles, $branchId, $title, $body)
    {
        $this->roles = $roles;
        $this->branchId = $branchId;
        $this->title = $title;
        $this->body = $body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Using the new Web Push trait method
            $this->sendWebPushToBranchRoles(
                $this->roles, 
                $this->branchId, 
                $this->title, 
                $this->body,
                url('/dashboard')
            );
        } catch (\Throwable $e) {
            echo " [ERROR] " . $e->getMessage() . "\n";
            Log::error("FCM Background Job Error: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            $this->fail($e); // Mark job as failed so we can retry or inspect
        }
    }
}
