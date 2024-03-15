<?php

namespace App\Jobs;

use App\Models\Contract\Contract;
use App\Models\Contract\ContractPayment;
use App\Services\SMSService;
use App\Services\UniAccessService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class paymentPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $uniAccess;
    /**
     * Create a new job instance.
     */
    public $data;
    public function __construct($data)
    {
        $this->data = $data;
        $this->uniAccess = new UniAccessService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $value = $this->data;

        $value['respText'] = $value['isReverseTransaction'] == false ? 'OK':'ROK';

        if (!\DB::table('contract_auto_pays')->where([['refnum', $value['refNum']],['respText', $value['respText']]])->first()){
            $details = [
                'autopayId' => $value['autoPaymentId'],
                'contractId' => $value['organizationContractId'],
                'username' => 'repaymentapexapi',
                'refnum' => $value['refNum'],
                'ext' => $value['ext'],
                'pan' => $value['pan'],
                'tranType' => "DEBIT",
                'transType' => "508",
                'date7' => \Carbon\Carbon::parse($value['transactionDate'])->format('mdHis'),
                'date12' => \Carbon\Carbon::parse($value['transactionDate'])->format('ymdHis'),
                'amount' => $value['amount'],
                'currency' => "860",
                'respText' => $value['respText'],
                'service_name' => 'EMIT'
            ];
            \DB::table('contract_auto_pays')->insert($details);
        }

        $contract_name = \DB::table('contract_names')->where('contractId', $value['organizationContractId'])->first();

        $con = Contract::find($contract_name->contract_id);

        $payments = ContractPayment::where([
            ['client_id', $contract_name->client_id],
            ['contract_id', $contract_name->contract_id],
            ['cancelled', false]
        ])->sum('amount');

        $residue = (($con->amount+$con->tax+$con->expense) - $payments)*100;

        $this->uniAccess->debitUpdate($con->contract_name, (int)$residue);

        if ((($con->amount_paid - ($con->amount+$con->tax+$con->expense)) > -0.99) && $con->auto_pay)
            $this->uniAccess->autopayToggle($con->contract_name, false);


        \Log::info('New tran emit: ' . $value['refNum'] . ': OK');

    }
}
