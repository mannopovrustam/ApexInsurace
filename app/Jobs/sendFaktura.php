<?php

namespace App\Jobs;

use App\Models\Contract\Contract;
use App\Services\FakturaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class sendFaktura implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $url, $postData, $contractId;
    public function __construct($url, $postData, $contractId)
    {
        $this->url = $url;
        $this->postData = $postData;
        $this->contractId = $contractId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = (new FakturaService())->getSendRequest($this->url, 'POST', $this->postData, $this->contractId);
    }
}
