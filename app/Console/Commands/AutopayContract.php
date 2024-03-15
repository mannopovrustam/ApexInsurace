<?php

namespace App\Console\Commands;

use App\Jobs\autoPay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutopayContract extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start:autopay-contract';

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
        $contracts = DB::table('contracts')
            ->select('clients.fullname', 'clients.pinfl', 'clients.passport', DB::raw("concat(`contract_judges`.`work_number`,' (',contracts.id,')') as contract_name"), 'contracts.id', 'contracts.client_id', 'contracts.auto_pay_activate', 'contracts.auto_pay', DB::raw('(contracts.amount + contracts.tax + contracts.expense - ifnull(sum(contract_payments.amount), 0)) as residue', 'auto_pay'))
            ->join('clients', 'contracts.client_id', '=', 'clients.id')
            ->join('contract_judges', 'contract_judges.contract_id', '=', 'contracts.id')
            ->leftjoin('contract_payments', 'contracts.id', '=', 'contract_payments.contract_id')
            ->whereNull('contracts.contract_name')
            ->whereNull('contracts.autopay_id')
            ->whereNull('contracts.auto_pay')
            ->where('contracts.auto_pay_activate', 0)
            ->whereNull('contracts.closed_at')
            ->where(DB::raw('ABS(contracts.amount + contracts.tax + contracts.expense - contracts.amount_paid)'), '>', 20000.00)
            ->whereIn('contracts.status',[3, 8])
            ->whereNotNull('contract_judges.work_number')
            ->where(DB::raw('LENGTH(pinfl)'), '=', 14)
            ->where(DB::raw('LENGTH(passport)'), '=', 9)
            ->groupBy('contracts.id', 'contract_judges.work_number')
            ->get();

        foreach ($contracts as $contract){
            $this->info($contract->pinfl);
            if ($contract->residue > 20000) autoPay::dispatch($contract);
        }



    }
}
