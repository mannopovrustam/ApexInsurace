<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

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
//	    $judges = \DB::table('contract_judges')->select('contract_id', 'work_number')->get();
//	    foreach($judges as $j){
//       		 \DB::table('contracts')->where('id',$j->contract_id)->update(['judge_number'=>$j->work_number]);
//	    }


	$contracts =  \DB::table('contracts as c')
                        ->join('contract_judges as cj', 'c.id', '=', 'cj.contract_id')
                        ->join('judges as j', 'j.id', '=', 'cj.judge_id')
			->where('c.client_type', 1)
                        ->select('c.id', 'c.client_type', 'j.legal_judge', 'j.phy_judge')->get();

	foreach($contracts as $c){
		\DB::table('contracts')->where('id', $c->id)->update(['judge_name' => $c->legal_judge]);
		$this->info("changed: ". $c->id);
	}

    }
}
