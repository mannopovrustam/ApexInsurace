<?php

namespace App\Jobs;

use App\Models\Contract\Contract;
use App\Models\Contract\ContractPayment;
use App\Services\EmitRepaymentService;
use App\Services\SMSService;
use App\Services\UniAccessService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class paymentPushUNI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $uniAccess;
    protected $emitRepayment;
    /**
     * Create a new job instance.
     */
    public $data;
    public function __construct($data)
    {
        $this->data = $data;
        $this->uniAccess = new UniAccessService();
        $this->emitRepayment = new EmitRepaymentService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $value = $this->data;

        if (!\DB::table('auto_uni_pays')->where([['transaction_id', $value['transaction_id']],['status', $value['status']]])->first()){
            $details = [
                'pinfl' => $value['pinfl'],
                'loan_id' => $value['loan_id'],
                'rrn' => $value['rrn'],
                'transaction_id' => $value['transaction_id'],
                'ext_id' => $value['ext_id'],
                'card_mask' => $value['card_mask'],
                'card_owner' => $value['card_owner'],
                'amount' => $value['amount'],
                'date' => Carbon::parse($value['date'])->format('Y-m-d'),
                'terminal' => $value['terminal'],
                'merchant' => $value['merchant'],
                'filial_id' => $value['filial_id'],
                'type' => $value['type'],
                'status' => $value['status'],
                'refunded_at' => Carbon::parse($value['refunded_at'])->format('Y-m-d H:i:s'),
                'created_at' => Carbon::parse($value['created_at'])->format('Y-m-d H:i:s'),
                'created_at_utc' => Carbon::parse($value['created_at_utc'])->format('Y-m-d H:i:s'),
            ];
            \DB::table('auto_uni_pays')->insert($details);
        }

        $contract_name = \DB::table('contract_names')->where('contract_name', $value['loan_id'])->first();

        $con = Contract::find($contract_name->contract_id);

        $payments = ContractPayment::where([
            ['client_id', $contract_name->client_id],
            ['contract_id', $contract_name->contract_id],
            ['cancelled', false]
        ])->sum('amount');

        $residue = (($con->amount+$con->tax+$con->expense) - $payments)*100;

        if ($con->auto_pay) $this->emitRepayment->autopayUpdate($con->auto_pay, (int)$residue);

        if (($con->amount_paid - ($con->amount+$con->tax+$con->expense)) > -0.99){
            $con->update(['status' => 10, 'closed_at' => Carbon::now(), 'auto_pay_activate' => false]);
            if ($con->auto_pay) $this->emitRepayment->autopayStop($con->auto_pay);
        }

        \Log::info('New tran: ' . $value['transaction_id'] . ': OK');

    }
}
