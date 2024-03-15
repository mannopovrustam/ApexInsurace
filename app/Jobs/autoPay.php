<?php

namespace App\Jobs;

use App\Services\EmitRepaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class autoPay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $contract;
    protected $emitRepayment;
    public function __construct($contract)
    {
        $this->emitRepayment = new EmitRepaymentService();
        $this->contract = $contract;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $emitClient = json_decode($this->emitRepayment->clientCreate($this->contract), true);
        $residue = $this->contract->residue*100;
        \Log::info('emitClient: '. $this->contract->fullname . "____" . json_encode($emitClient, JSON_UNESCAPED_UNICODE));

        if($emitClient['result']) {
            $detailsAutopay = ["contractId" => $emitClient['result']['contractId'],
                "amount" => (int)$residue,
                "startDate" => date('Ymd')
            ];
            \Log::info('detailsAutopay: ' . json_encode($detailsAutopay, JSON_UNESCAPED_UNICODE));

            $emitAutopay = json_decode($this->emitRepayment->autopayCreate($detailsAutopay), true);
            if ($emitAutopay['result']) {
                \DB::table('contracts')->where('id', $this->contract->id)->update([
                    'autopay_id' => $emitAutopay['result']['contractId'],
                    'auto_pay' => $emitAutopay['result']['autopayId'],
                    'auto_pay_activate' => true
                ]);
            }
        }

    }
}
