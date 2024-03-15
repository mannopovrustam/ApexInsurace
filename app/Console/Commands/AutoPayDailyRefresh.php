<?php

namespace App\Console\Commands;

use App\Services\EmitRepaymentService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoPayDailyRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autopay:daily-refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $emit = new EmitRepaymentService();

        $startDate = Carbon::now()->subDay()->format('Ymd');
        $endDate = Carbon::now()->subDay()->format('Ymd');

        $transAll = $emit->transHistory($startDate,$endDate);
        $transAll = json_decode($transAll, true);

        if ($transAll['result']){
            foreach ($transAll['result'] as $r){
                if (!\DB::table('contract_auto_pays')->where([['refnum', $r['refnum']],['respText', $r['respText']]])->first()){
                    $details = [
                        'autopayId' => $r['autopayId'],
                        'contractId' => $r['contractId'],
                        'username' => $r['username'],
                        'refnum' => $r['refnum'],
                        'ext' => $r['ext'],
                        'pan' => $r['pan'],
                        'tranType' => $r['tranType'],
                        'transType' => $r['transType'],
                        'date7' => $r['date7'],
                        'date12' => $r['date12'],
                        'amount' => $r['amount'],
                        'currency' => $r['currency'],
                        'stan' => $r['stan'],
                        'field38' => $r['field38'],
                        'merchantId' => $r['merchantId'],
                        'terminalId' => $r['terminalId'],
                        'respText' => $r['respText'],
                    ];
                    \DB::table('contract_auto_pays')->insert($details);
                    $this->info(json_encode($details));
                }
            }
        }

    }
}
