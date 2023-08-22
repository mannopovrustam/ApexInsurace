<?php

namespace App\Http\Controllers;

use App\Exports\ContractsExport;
use App\Imports\ContractsImport;
use App\Jobs\sendEmail;
use App\Jobs\sendFaktura;
use App\Jobs\sendSMS;
use App\Models\Category;
use App\Models\Client;
use App\Models\Contract\Contract;
use App\Models\Contract\ContractFile;
use App\Models\Contract\ContractHybrid;
use App\Models\Contract\ContractJudge;
use App\Models\Contract\ContractMib;
use App\Models\Contract\ContractPayment;
use App\Models\Contract\ContractSms;
use App\Models\Petition;
use App\Models\User;
use App\Services\FakturaService;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\TemplateProcessor;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Validator;

class ContractController extends Controller
{
    public $path;
    protected $fakturaService;

    public function __construct(FakturaService $fakturaService)
    {
        $this->fakturaService = $fakturaService;
        $this->middleware('auth');
        $this->middleware('permission:Ҳужжатларни ўзгартириш (тўлов суммаларидан ташқари)', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Ҳужжатларни ўчириш', ['only' => ['destroy']]);
        $this->path = 'view.'.Str::singular(\Request::segment(1));
    }

    public function index()
    {
        $sms_templates = DB::table('sms_templates')->get();
        return view($this->path.'.index', ['sms_templates' => $sms_templates]);
    }

    public function getData()
    {
        $sms_templates = DB::table('sms_templates')->get();
        return view($this->path.'.data', ['sms_templates' => $sms_templates]);
    }

    public function create()
    {
        $data = new Contract();
        $data->client = new Client();
        $data->category = new Category();
        $clients = \DB::table('clients')->get();

        return view($this->path.'.create', ['data' => $data, 'clients' => $clients]);
    }

    public function store(Request $request)
    {
        $values = $request->except(['_token', 'data_id']);
        $messages = [
            'region_id.required' => 'Вилоят танланг',
            'district_id.required' => 'Туман танланг',
            'fullname.required' => 'Ф.И.Ш. ни киритинг',
            'passport.required_without_all:pinfl' => 'Паспорт серияси ва рақами ни киритинг',
            'pinfl.required_without_all:passport' => 'ПИНФЛ ни киритинг',
            'address.required'   => 'Манзилни киритинг',
            'dtb.required' => 'Туғилган санани киритинг',
            'type.required' => 'Типни танланг',
            'category_id.required'   => 'Категорияни танланг',
            'number.required'   => 'Шартнома рақамини киритинг',
            'name.required'  => 'Шартнома номини киритинг',
            'date_payment.required' => 'Тўлов санасини киритинг',
            'date.required' => 'Санани киритинг',
            'amount.required' => 'Суммани киритинг',
        ];

        $validator = Validator::make($values, [
            'region_id' => 'required',
            'district_id' => 'required',
            'fullname' => 'required',
            'passport' => 'required_without_all:pinfl',
            'pinfl' => 'required_without_all:passport',
            'address' => 'required',
            'dtb' => 'required',
            'type' => 'required',
            'category_id' => 'required',
            'number' => 'required',
            'name' => 'required',
            'date_payment' => 'required',
            'date' => 'required',
            'amount' => 'required',
        ], $messages);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        try {
            DB::beginTransaction();

            $client = Client::updateOrCreate([
                'passport' => $values['passport'],
                "dtb" => $values['dtb']
            ],[
                "region_id" => $values['region_id'],
                "district_id" => $values['district_id'],
                "fullname" => $values['fullname'],
                "passport" => $values['passport'],
                "pinfl" => $values['pinfl'],
                "phone" => isset($values['phone']) ? implode(',',$values['phone']):null,
                "address" => $values['address'],
                "dtb" => $values['dtb'],
                "type" => $values['type'],
                "inn" => $values['inn'],
                "mfo" => $values['mfo'],
                "account_number" => $values['account_number']
            ]);

            $contract = Contract::updateOrCreate([
                "id" => $request->data_id,
            ],[
                "client_id" => $client->id,
                "category_id" => $values['category_id'],
                "number" => $values['number'],
                "name" => $values['name'],
                "date_payment" => $values['date_payment'],
                "date" => $values['date'],
                "amount" => $values['amount'],
                "status" => $values['status'],
                "clients" => isset($values['clients']) ? implode(',',$values['clients']):null,
            ]);

            if ($values['status'] == 10){
                $contract->update([
                    'closed_at' => Carbon::now()
                ]);
            }

            User::auditable('contracts', $contract->id, $contract, ($request->data_id ? 'U':'C'));

            $base64Pdf = null;
            if (isset($request->file_name) && count($request->file_name) > 0){
                foreach ($request->file_name as $key => $item){
                    $request->file[$key]->move(public_path('uploads'), $request->file_name[$key]);
                    $file = public_path('uploads/'.$request->file_name[$key]);

                    $contract_files = ContractFile::create([
                        'contract_id' => $contract->id,
                        'name' => $request->file_name[$key],
                        'file' => $file,
                    ]);
                    User::auditable('contract_files', $contract_files->id, json_encode($contract_files->toArray()), 'C');

                    $pdfContent = file_get_contents($file);
                    $base64Pdf = base64_encode($pdfContent);
                }
            }

            DB::commit();

            return back()->withMessage($contract->number.' рақамли шартнома муваффақиятли яратилди')->withColor('success');
        }catch (Exception $e){
            DB::rollBack();
            return back()->withMessage($e->getMessage())->withColor('danger');
        }

    }

    public function update(Request $request, $id)
    {
        $base64Pdf = null;
        if (isset($request->file_name) && count($request->file_name) > 0){
            foreach ($request->file_name as $key => $item){
                $name = $request->file_name[$key].'_'.time().'.'.$request->file[$key]->getClientOriginalExtension();
                $request->file[$key]->move(public_path('uploads'), $name);
                $file = '/uploads/'.$name;

                ContractFile::create([
                    'contract_id' => $id,
                    'name' => $request->file_name[$key],
                    'file' => $file,
                ]);

                $pdfContent = file_get_contents(public_path($file));
                $base64Pdf = base64_encode($pdfContent);
            }
        }
        return back();
    }

    public function postFaktura(Request $request, $id)
    {
        $base64Pdf = null;

        // $request->file must be pdf file
        if (strtolower($request->file->getClientOriginalExtension()) != 'pdf'){
            return response(['data' => null, 'message' => 'Файл pdf форматида бўлиши керак', 'status'=>'error', 'color' => 'danger']);
        }

        $contract = Contract::with(['client', 'category'])->find($id);

        if (isset($request->file)){
            $request->file->move(public_path('uploads'), $contract->id .'hybrid.'. $request->file->getFilename());
            $file = public_path('uploads/'.$contract->id .'hybrid.'. $request->file->getFilename());

            ContractFile::create([
                'contract_id' => $contract->id,
                'file' => $file,
                'name' => 'Hybrid',
            ]);

            $pdfContent = file_get_contents($file);
            $base64Pdf = base64_encode($pdfContent);
        }


        $url = 'https://api.faktura.uz/Api/HybridDocument/Post?companyInn='.env('COMPANY_INN');
        $postData = [
            "CompanyInn" => env('COMPANY_INN'),
            "Region" => $contract->client->region_id,
            "Area" => $contract->client->district_id,
            "FullName" => $contract->client->fullname,
            "Address" => $contract->client->address,
            "SenderName" => "APEX  INSURANCE AJ",
            "SenderAddress" => "100099, ГОРОД ТАШКЕНТ, ЮНУСАБАДСКИЙ РАЙОН, УЛИЦА А.ТЕМУРА-О.ЗОКИРОВА , 5/1",
            "Base64Content" => $base64Pdf
        ];

        $response = null;
        if (env('IS_IDE',0) == 1) {
            sendFaktura::dispatch($url, $postData, $contract->id);
            return response(['data' => $response, 'message' => 'Гибрид почтага муваффақиятли юборилди!!', 'status'=>'success', 'color' => 'green']);
        }

        return response(['data' => $response, 'message' => 'Гибрид почтага юбориш ўчирилган!!', 'status'=>'success', 'color' => 'yellow']);
    }


    public function postImportData(Request $request)
    {
        if ($request->file->getClientOriginalExtension() != 'xlsx' && $request->file->getClientOriginalExtension() != 'xls'){
            return response(['data' => null, 'message' => 'Файл xlsx йоки xls форматида бўлиши керак', 'status'=>'error', 'color' => 'danger']);
        }
        ini_set('memory_limit', '256M');
        Excel::import(new ContractsImport(), $request->file);
    }

    public function show($id)
    {
        $data = Contract::with(['client', 'category', 'files', 'petitions', 'hybrids', 'judge', 'mib', 'sms'])->find($id);

        if($data->client->type == 1) $type = 'legal_judge';
        else $type = 'phy_judge';

        $judge = DB::table($type)
            ->join('judges', $type.'.judge_id', '=', 'judges.id')
            ->where([['region_id', $data->client->region_id], ['district_id', $data->client->district_id]])
            ->select('judges.*')
            ->first();
        $sms_templates = DB::table('sms_templates')->get();
        return view($this->path.'.show', [
            'data' => $data,
            'judge' => $judge,
            'sms_templates' => $sms_templates,
        ]);
    }

    public function getFindGuest(Request $request)
    {
        $data = DB::table('clients');
        if ($request->passport) $data->where('passport', $request->passport);
        if ($request->pinfl) $data->where('pinfl', $request->pinfl);
        $data = $data->select('fullname','phone','region_id','district_id','address','type','dtb','passport','pinfl')->first();

        if ($data) return response(['data' => $data, 'message' => 'Мижоз топилди!', 'status'=>'success', 'color' => 'green']);
        return response(['message' => 'Мижоз топилмади!', 'status'=>'error', 'color' => 'error']);
    }

    public function postPayment(Request $request)
    {
        $values = $request->except(['_token']);
        $validate = $this->validate($request,[
            'client_id' => 'required',
            'contract_id' => 'required',
            'date' => 'required|date',
            'amount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'note' => 'nullable|string|max:255',
        ]);

        $data = ContractPayment::where([
            ['client_id', $request->client_id],
            ['contract_id', $request->contract_id],
            ['date', $request->date],
            ['amount', $request->amount],
        ])->select('created_at')->first();


        if ($data && Carbon::parse(now())->diffInSeconds(Carbon::parse($data->created_at)) < 60){
            return response(['message' => 'Тўлов 1 дақиқа ичида амалга оширилган!', 'content'=>'']);
        }

        ContractPayment::updateOrCreate(['id' => $request->payment_id],$validate);

        $payments = ContractPayment::where([
            ['client_id', $request->client_id],
            ['contract_id', $request->contract_id],
        ])->sum('amount');

        Contract::find($request->contract_id)->update(['amount_paid' => $payments]);

        $con = Contract::find($request->contract_id);
        if ($con->amount_paid >= $con->amount){
            $con->update(['status' => 10]);
        }


        return response(['message' => $request->payment_id ? 'Тўлов ўзгартирилди!':'Тўлов амалга оширилди!', 'content'=>$request->amount]);

    }

    public function getPayments($id)
    {
        $html = '';
        $html .= '<table class="table table-sm table-hover table-bordered table-striped table-nowrap align-middle" id="cp"><tr><th>Summa</th><th>Sana</th></tr>';
        $cp = ContractPayment::where('contract_id', $id)->get();
        foreach ($cp as $c){
            $html .= "<tr><td>".number_format($c->amount,2,"."," ")."</td><td>".Carbon::parse($c->date)->format('d.m.Y')."</td>";
            $html .= "<td><i class='fa fa-pen' style='cursor: pointer' onclick='changePayment(`{$c->id}`, `".number_format($c->amount,2,"."," ")."`, `{$c->date}`, `{$c->note}`)'></i></td></tr>";
        }
        $html .= '</table>';
        return $html;
    }

    public function postExpense(Request $request)
    {
        $detail = [
            "contract_id" => $request->contract_id,
            "expense" => (float)$request->expense,
            "tax" => (float)$request->tax
        ];
        $validate = $this->validate($request,[
            'contract_id' => 'required',
            'expense' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'tax' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
        ]);

        $data = Contract::where('id', $request->contract_id)->update([
            "expense"=>$request->expense,
            "tax"=>$request->tax
        ]);

        if ($data) return response(['message' => 'Харажатлар ўзгартирилди!', 'content'=>"DAVLAT BOJ: {$request->tax}, POCHTA XARAJATI: {$request->expense}"]);

        return response(['message' => 'Сақлаш билан муаммо юз берди!', 'content'=>"Бироздан сўнг уриниб кўринг!"]);

    }

    public function getGroupEmail(Request $request)
    {
        // get rows from contracts table where id in $request->ids and send to sendEmail job
        $contracts = DB::table('contracts')->whereIn('contracts.id', $request->ids)
            ->join('clients', 'clients.id', '=', 'contracts.client_id')
            ->join('categories', 'categories.id', '=', 'contracts.category_id')
            ->select('clients.*','contracts.*','categories.name as cat')->get();

        $user = auth()->user();
        foreach ($contracts as $contract){ sendEmail::dispatch($contract, $user); }
//        foreach ($contracts as $contract){ $this->handle($contract, $user); }
        return response(['message' => 'Шартномалар электрон почтага юборилди!', 'content'=>"", 'status'=>'success', 'color' => 'green']);
        //return response(['message' => 'Сақлаш билан муаммо юз берди!', 'content'=>"Бироздан сўнг уриниб кўринг!"]);
    }

    public function getGroupExcel(Request $request)
    {
        return Excel::download(new ContractsExport($request->ids), 'contracts.xlsx');
        // return  response(['message' => 'Шартномалар Excel га юборилди!', 'content'=>"", 'status'=>'success', 'color' => 'green']);
    }
    public function getGroupSms(Request $request)
    {
        $sms_template = DB::table('sms_templates')->where('id', $request->template_id)->first();
        $user = auth()->user();
        $contracts = DB::table('contracts')->whereIn('contracts.id', $request->ids)
            ->join('clients', 'clients.id', '=', 'contracts.client_id')
            ->select('clients.phone', 'contracts.id')
            ->get();

        foreach ($contracts as $contract){
            $data = Contract::with(['client', 'category', 'files', 'petitions', 'hybrids', 'judge', 'mib'])->find($contract->id);

            if($data->client->type == 1) $type = 'legal_judge';
            else $type = 'phy_judge';

            $judge = DB::table($type)->join('judges', $type.'.judge_id', '=', 'judges.id')
                ->where([['region_id', $data->client->region_id], ['district_id', $data->client->district_id]])
                ->select('judges.*')->first();


            foreach (explode(',', $data->client->phone) as $item) {
                Carbon::setLocale('uz');
                $vars = ['$contract_id','$category_name','$number','$contract_name','$contract_date','$fullname','$passport','$pinfl','$phone','$address','$dtb','$type','$date_payment','$amount_sum','$amount_paid','$residue','$status','$contract_note','$created_at','$tax','$expense','$judge_name','$judge_no','$judge_result','$judge_note','$mib_name','$mib_no','$mib_result','$mib_note','$curdate','$admin_name','$admin_phone', '$inn','$mfo','$account_number'];
                $changeVal = [$data->id,$data->category->name, $data->number, $data->name,Carbon::parse($data->date)->translatedFormat('d M Y'),$data->client->fullname,$data->client->passport,$data->client->pinfl,$item,$data->client->address,Carbon::parse($data->client->dtb)->format('d.m.Y'),$data->client->type ? 'Юридик шахс':'Жисмоний шахс',Carbon::parse($data->date_payment)->format('d.m.Y'),number_format($data->amount, 0, ',', ' '),number_format($data->amount_paid, 0, ',', ' '),round(($data->amount - $data->amount_paid), 2),Client::STATUS_COLOR[$data->status],$data->note,Carbon::parse($data->created_at)->format('d.m.Y'),$data->tax,$data->expense, $judge->$type, $data->judge?->work_number,$data->judge?->result,$data->judge?->note,$data->mib?->name,$data->mib?->work_number,$data->mib?->result,$data->mib?->note, now()->format('Y').' йил '. now()->translatedFormat('d M'), $user->name, $user->phone, $data->client->inn, $data->client->mfo, $data->client->account_number];

                $result = str_replace($vars, $changeVal, $sms_template->content);
                sendSMS::dispatch($item, $result, $data->id);
            }
        }
        return response(['message' => 'Шартномалар смс юборилди!', 'content'=>"", 'status'=>'success', 'color' => 'green']);
    }

    public function getDataHybrid($id)
    {
        $hybrids = Contract::with('hybrids')->find($id);
        foreach ($hybrids->hybrids as $hybrid){
            $response = (new FakturaService())->getDetail($hybrid->uid);
            $uid = $response->Data->Uid;
            $createdAt = $response->Data->CreatedDate;
            $updatedAt = $response->Data->UpdatedDate;
            $status = $response->Data->Status;

            ContractHybrid::where('uid', $uid)->update([
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt
            ]);
        }
        $hybrids = Contract::with('hybrids')->find($id);
        $html = '';
        // <tbody> <tr> <th>Status</th> <td>Yubprilgan</td> </tr> </tbody>
        $html .= '<tbody>';
        foreach ($hybrids->hybrids as $hybrid){
            $html .= "<tr><td>".ContractHybrid::STATUS[(int)$hybrid->status]."</td><td>".$hybrid->created_at."</td><td>".$hybrid->updated_at."</td></tr>";
        }
        $html .= '</tbody>';

        return response(['message' => 'Почта маълумотлари янгиланди!', 'content'=>$html]);
    }

    public function postGroupDelete(Request $request)
    {
        $contracts = DB::table('contracts')->whereIn('contracts.id', $request->ids)->delete();

        foreach($request->ids as $id) {
            User::auditable('contracts', $id, 'Шартнома ўчирилди', 'D');
        }
        return response(['message' => 'Шартномалар ўчирилди!', 'content'=>""]);
    }

/*    public function getGroupDelete()
    {
        $arr = [
            'feca6c8f03ef443d9831199fe33db0a9',
            '90d33b55f0a74807b5605ff550e13aaa',
            '4d131e3d8033437bb76b7f1439b57329',
            '6ef29f1a8b5c44a5934d3cc32a1df72a',
            '5957848897de484c982c0e98a85ad9c8',
            'a55440d7871c498580a9048e745eea8e',
            '7eba34c0b734472d9aa48ef1adc141cc'
        ];

        if (env('IS_IDE', 0) == 1){
            foreach ($arr as $item) {
                (new FakturaService())->getDelete($item);
            }
        }
    }*/

    public function postJudgeInfo(Request $request, $id)
    {
        ContractJudge::updateOrCreate(['contract_id' => $id], $request->except('_token'));
        $contract = Contract::find($id);
        if ($contract->status <= 2){
            $contract->status = 3;
            $contract->save();
        }
        return response(['message' => 'Маълумотлар сақланди!', 'content'=>""]);
    }

    public function postMibInfo(Request $request, $id)
    {
        ContractMib::updateOrCreate(['contract_id' => $id], $request->except('_token'));
        $contract = Contract::find($id);
        if ($contract->status < 6){
            $contract->status = 8;
            $contract->save();
        }
        return response(['message' => 'Маълумотлар сақланди!', 'content'=>""]);
    }

    public function getPdfCheck($uid, $check = false)
    {
        $check == 'true' ? $check = true : $check = false;

        $pdf = (new \App\Services\FakturaService())->getPreview($uid, $check);
        file_put_contents(public_path('uploads/faktura_check/'.$uid.'.pdf'), $pdf);
        header('Content-Type: application/pdf');
        echo $pdf;
        exit;
    }

    public function edit($id)
    {
        $data = Contract::with(['client', 'category', 'files'])->find($id);
        $clients = Client::all();
        return view($this->path.'.create', ['data' => $data, 'clients' => $clients]);
    }

    public function destroy($id)
    {
        $data = Contract::find($id);
        $data->files()->delete();
        $data->petitions()->delete();
        $data->payments()->delete();
        $data->hybrids()->delete();
        $data->judge()->delete();
        $data->mib()->delete();
        $data->sms()->delete();

        User::auditable('contracts', $id, json_encode($data), 'D');

        $data->delete();
        return response(['message' => 'Шартнома ўчирилди!', 'content'=>""]);
    }

}
