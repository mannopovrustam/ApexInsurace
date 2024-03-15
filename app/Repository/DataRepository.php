<?php

namespace App\Repository;

use App\Models\Petition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DataRepository
{
    public function getContracts()
    {

        $request = request();

        $data = \DB::table('contracts')
            ->join('clients', 'contracts.client_id', '=', 'clients.id')
            ->join('categories', 'contracts.category_id', '=', 'categories.id')
            ->leftjoin('contract_mibs', 'contract_mibs.contract_id', '=', 'contracts.id')
            ->leftjoin('users', 'users.id', '=', 'contracts.user_id')
//            ->leftjoin('contract_judges', 'contract_judges.contract_id', '=', 'contracts.id')
            ->leftjoin('contract_payments', function($join){
			$join->on('contracts.id', '=', 'contract_payments.contract_id')
			->where('contract_payments.cancelled', 0);
		})
//'contracts.id', '=', 'contract_payments.contract_id')
            ->leftjoin('regions', 'regions.id', '=', 'clients.region_id')
            ->select('contracts.*', 'clients.fullname', 'clients.phone', 'clients.passport', 'clients.pinfl', 'clients.inn', 
			'users.name as user_name', 
			'categories.name as category_name', 'contract_mibs.work_number as mib_no', 
			'contracts.judge_number as judge_no', \DB::raw('sum(contract_payments.amount) as payment_total'));

        // start_ins, end_ins
        // start_created, end_created
        $data = $data->whereBetween('contracts.created_at', [$request->start_created, $request->end_created])->groupBy('contracts.id', 'contract_mibs.work_number', 'contracts.judge_number');
        $data = $data->whereBetween('contracts.date_payment', [$request->start_pay, $request->end_pay]);
        if($request->type != "") $data = $data->where('clients.type', $request->type);

        $data = $data->where(function ($query) use ($request){
            $query->whereBetween('contract_payments.date', [$request->start_ins, $request->end_ins]);
            if ($request->start_ins == "2016-01-01" && $request->end_ins == Carbon::now()->addDay()->format('Y-m-d')) $query->orWhereNull('contract_payments.date');
        });

        if ($request->category) $data = $data->whereIn('contracts.category_id', $request->category);
        if ($request->sts) $data = $data->whereIn('contracts.status', $request->sts);
//        if ($request->autopay) $data = $data->where('contracts.auto_pay_activate', !$request->autopay);
        if ($request->autopay) {
            $data = $data->where(function ($query) use ($request){
                $query->where('contracts.auto_pay_activate', !$request->autopay)
                    ->Orwhere('contracts.auto_pay_uniaccess_activate', !$request->autopay);
            });
        }

        if ($request->autopay_dt) $data = $data->where('contracts.autopay_start_dt', Carbon::now()->format('Y-m-d'));

        $addSlashes = str_replace('?', "'?'", $data->toSql());
        $forExcel = vsprintf(str_replace('?', '%s', $addSlashes), $data->getBindings());

        $replaced = str_replace('`contracts`.*', '`contracts`.`name`,`contracts`.`number`,`contracts`.`date`,`contracts`.`date_payment`,`contracts`.`amount`,`contracts`.`amount_paid`,`contracts`.`created_at`,`contracts`.`closed_at`', $forExcel);
        session()->put('contracts_query', $replaced);

        $replaced = str_replace('sum(contract_payments.amount) as payment_total', 'contract_payments.amount as payment_amount, contract_payments.date as payment_date, contract_payments.note as payment_note, if(contract_payments.type = 1, \'Plastik\', if(contract_payments.type = 2, \'Naqd\', \'\')) as payment_type', $replaced);
        $replaced = str_replace(' group by `contracts`.`id`, `contract_mibs`.`work_number`, `contracts`.`judge_number`', '', $replaced);
        session()->put('contract_payments_query', $replaced);

        // set row attr for update row
        return DataTables::of($data)
            ->setRowAttr([
		'data-id' => function ($data) {return $data->id;},
                'data-amount' => function ($data) {return ($data->amount+$data->tax+$data->expense);},
                'data-residue' => function ($data) {return (($data->amount+$data->tax+$data->expense) - $data->amount_paid);},
                'data-payment' => function ($data) {return (float)$data->payment_total;},
                'data-fullname' => function ($data) {return $data->fullname;}
	    ])
            ->addColumn('check', function ($data) {
                return "";
            })
            ->addColumn('api', function ($data) {
                $txt = '';
                if ($data->hybrid_send){
                    $txt .= '✉️ ';
                }
                if ($data->sms_send){
                    $txt .= '✍️';
                }
                return $txt;
            })
            ->editColumn('date_payment', function ($data) {
                return \Carbon\Carbon::parse($data->date_payment)->format('d/m/Y');
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::parse($data->created_at)->format('H:i d/m/Y');
            })
            ->editColumn('amount', function ($data) {
                return number_format($data->amount+$data->tax+$data->expense,2,"."," ");
            })
            ->addColumn('residue', function ($data) {
                return number_format((($data->amount+$data->tax+$data->expense) - $data->amount_paid),2,"."," ");
            })
            ->editColumn('payment_total', function ($data) {
                return $data->payment_total ? number_format($data->payment_total,2,"."," ") : 0.00;
            })
            ->editColumn('status', function ($data) {
                return "<span class='badge ".\App\Models\Client::STATUS_COLOR[$data->status]."'>".\App\Models\Client::STATUS_NAME[$data->status]."</span>";
            })
            ->editColumn('auto_pay_activate', function ($data) {
                if ($data->auto_pay_activate) return '<span class="spinner-grow text-success" style="width: .8rem;height: .8rem;">';
                if ($data->auto_pay_uniaccess_activate) return '<span class="spinner-grow text-success" style="width: .8rem;height: .8rem;">';
            })
            ->rawColumns(['api', 'status', 'auto_pay_activate'])
            ->make(true);
    }

    public function getPetitions()
    {
        $data = \DB::table('petitions')
            ->join('contracts', 'petitions.contract_id', '=', 'contracts.id')
            ->join('clients', 'contracts.client_id', '=', 'clients.id')
            ->select('petitions.id', 'petitions.type', 'petitions.file', 'petitions.created_at', 'clients.fullname', 'clients.phone', 'contracts.id as contract_id', 'contracts.number');

        // set row attr for update row
        return DataTables::of($data)
            ->setRowAttr([
                'id' => function ($data) {return $data->id;},
                'contract_id' => function ($data) {return $data->contract_id;}
            ])
            ->make(true);
    }

    public function getRegions(Request $request)
    {
        $regions = \DB::table('regions')->get();
        $html = '';
        foreach ($regions as $region) {
            $html .= "<option value='{$region->id}'". ($region->id == $request->selected ? ' selected':'') .">{$region->name}</option>";
        }
        return $html;
    }

    public function getDistricts(Request $request)
    {
        $districts = \DB::table('districts')->where('region_id', $request->region_id)->get();
        $html = '';
        foreach ($districts as $district) {
            $html .= "<option value='{$district->id}'". ($district->id == $request->selected ? ' selected':'') .">{$district->name}</option>";
        }
        return $html;
    }

    public function getSms(Request $request)
    {
        $districts = \DB::table('districts')->where('region_id', $request->region_id)->get();
        $html = '';
        foreach ($districts as $district) {
            $html .= "<option value='{$district->id}'". ($district->id == $request->selected ? ' selected':'') .">{$district->name}</option>";
        }
        return $html;
    }


    public function getClient()
    {
        $data = \DB::table('clients')->select('id','fullname','passport','pinfl','address','phone','created_at');
        return DataTables::of($data)
            ->setRowAttr([
                'id' => function ($data) {return $data->id;},
            ])->make(true);
    }
}
