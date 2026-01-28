<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // <--- Implement ShouldQueue is key for background jobs
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\SendFcmNotification;
use Illuminate\Support\Facades\Log;

class SendAuditNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SendFcmNotification;

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
            // Using the trait method to send notification
            $this->sendNotificationToBranchRoles($this->roles, $this->branchId, $this->title, $this->body);
        } catch (\Throwable $e) {
            Log::error("FCM Background Job Error: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            $this->fail($e); // Mark job as failed so we can retry or inspect
        }
    }
}
