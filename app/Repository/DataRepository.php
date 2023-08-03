<?php

namespace App\Repository;

use App\Models\Petition;
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
            ->select('contracts.*', 'clients.fullname', 'clients.phone', 'clients.passport', 'clients.pinfl', 'categories.name as category_name');
        // start_ins, end_ins
        // start_created, end_created
        $data = $data->whereBetween('contracts.date', [$request->start_ins, $request->end_ins]);
        $data = $data->whereBetween('contracts.created_at', [$request->start_created, $request->end_created]);
        $data = $data->whereBetween('contracts.amount_paid', [$request->start_amount, $request->end_amount]);

        // set row attr for update row
        return DataTables::of($data)
            ->setRowAttr(['data-id' => function ($data) {return $data->id;}])
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
                return number_format($data->amount,2,"."," ");
            })
            ->addColumn('residue', function ($data) {
                return number_format(($data->amount - $data->amount_paid),2,"."," ");
            })
            ->editColumn('status', function ($data) {
                return "<span class='badge ".\App\Models\Client::STATUS_COLOR[$data->status]."'>".\App\Models\Client::STATUS_NAME[$data->status]."</span>";
            })
            ->rawColumns(['api', 'status'])
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

}
