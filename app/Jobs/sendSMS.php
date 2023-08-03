<?php

namespace App\Jobs;

use App\Services\SMSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class sendSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $phone;
    protected $message;
    protected $contract_id;
    public function __construct($phone, $message, $contract_id = null)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->contract_id = $contract_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        SMSService::sendSMS($this->phone, $this->message, $this->contract_id);
    }
}
