<?php

namespace App\Repository;

use App\Models\Category;

class ReportRepository
{
    public function report_100()
    {
        $data1 = \DB::table('contracts as c')
            ->selectRaw("count(c.*) as count, sum(cp.amount) as amount,
            if(c.status = 2, count(c.*)),
            if(c.status = 3, count(c.*)),
            if(c.status = 10, count(c.*)),
            if(c.status = 10, sum(cp.amount)),
            (sum(cp.amount)/if(c.status = 10, sum(cp.amount)))*100,
            if(MONTH(cp.date) = MONTH(NOW()), sum(cp.amount)),
            sum(count(c.*) - if(c.status = 10, count(c.*))),
            sum(sum(cp.amount) - if(c.status = 10, sum(cp.amount)))
            )")
            ->leftjoin('contract_payments as cp', 'c.id', '=', 'cp.contract_id')
            ->where('category_id', 1)
            ->toSql();
        return $data1;
    }

    public function getIndex($data)
    {
        $report = 'report_'.$data;
        return $this->$report();
    }

}
