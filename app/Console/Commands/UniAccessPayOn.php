<?php

namespace App\Console\Commands;

use App\Jobs\autoPay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UniAccessPayOn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uni:autopay-contract';

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
        // show info and time
        $start_time = microtime(true);
        $this->info('Startad');

        $contracts = \DB::table('contracts')
            ->select('clients.fullname', 'clients.pinfl', 'clients.passport', 'clients.phone', 'contracts.contract_name', 'contracts.id', 'contracts.client_id', 'contracts.auto_pay_activate', 'contracts.auto_pay',
                \DB::raw('(contracts.amount + contracts.tax + contracts.expense - ifnull(sum(contract_payments.amount), 0)) as residue', 'auto_pay'))
            ->join('clients', 'contracts.client_id', '=', 'clients.id')
            ->leftJoin('contract_payments', function($join){
                $join->on('contracts.id', '=', 'contract_payments.contract_id')->where('contract_payments.cancelled', '!=', '1');
            })->whereNotNULL('contracts.judge_number')
            ->where('contracts.auto_pay_activate', 1)
            ->groupBy('contracts.id')
            ->get();
        $this->info('Contracts selected'.count($contracts));

        foreach ($contracts as $contract) {
            $start_time_con = microtime(true);
            json_decode((new \App\Services\UniAccessService())->clientCreate($contract), true);
            $toggle = json_decode((new \App\Services\UniAccessService())->autopayToggle($contract->contract_name, true), true);
            \DB::table('uni_contracts')->insert(['contract_id' => $contract->id, 'is_active'=>$toggle['status']]);
            $this->info($contract->contract_name.' '.$toggle['status'].', time: '.(microtime(true) - $start_time_con));
        }
        $this->info('Finished, time: '.(microtime(true) - $start_time));

    }
}
