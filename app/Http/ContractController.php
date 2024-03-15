<?php

namespace App\Http\Controllers;

use App\Exports\ContractPaymentsExport;
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
use App\Services\EmitRepaymentService;
use App\Services\FakturaService;
use App\Services\IncardService;
use App\Services\UniAccessService;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\TemplateProcessor;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Validator;

class ContractController extends Controller
{
    public $path;
    protected $fakturaService;
    protected $emitRepayment;
    protected $uniAccess;

    public function __construct(FakturaService $fakturaService)
    {
        $this->fakturaService = $fakturaService;
        $this->middleware('auth');
        $this->middleware('permission:Ҳужжатларни ўзгартириш (тўлов суммаларидан ташқари)', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Ҳужжатларни ўчириш', ['only' => ['destroy']]);
        $this->path = 'view.'.Str::singular(\Request::segment(1));
        $this->emitRepayment = new EmitRepaymentService();
        $this->uniAccess = new UniAccessService();
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
//            'passport' => 'required_without_all:pinfl',
//            'pinfl' => 'required_without_all:passport',
            'address' => 'required',
//            'dtb' => 'required',
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
                'id' => $values['client_id']
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
                "account_number" => $values['account_number'],
		"created_by" => auth()->id()
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
                "bank_name" => $values['bank_name'],
                "clients" => isset($values['clients']) ? implode(',',$values['clients']):null,
		"created_by" => auth()->id()
            ]);
            session()->put('category_id', $values['category_id']);
            session()->put('number', $values['number']);
            session()->put('name', $values['name']);
            session()->put('date', $values['date']);
            session()->put('type', $values['type']);
            session()->put('inn', $values['inn']);
            session()->put('mfo', $values['mfo']);
            session()->put('account_number', $values['account_number']);

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
            ->where([[$type.'.region_id', $data->client->region_id], [$type.'.district_id', $data->client->district_id]])
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
            'type' => 'nullable',
        ]);

        $data = ContractPayment::where([
            ['client_id', $request->client_id],
            ['contract_id', $request->contract_id],
            ['date', $request->date],
            ['amount', $request->amount],
        ])->select('created_at')->first();



        $contact = Contract::find($request->contract_id);
        $payments = ContractPayment::where([
            ['contract_id', $request->contract_id],
            ['cancelled', false]
        ])->sum('amount');
        $contact->update(['amount_paid' => $payments]);
        if (($contact->amount_paid -$contact->amount - $contact->expense - $contact->tax) >= -0.99) {
            $contact->update([
                'auto_pay_activate' => 0,
                'status' => 10
            ]);
        }


        if ($data && Carbon::parse(now())->diffInSeconds(Carbon::parse($data->created_at)) < 60){
            return response(['message' => 'Тўлов 1 дақиқа ичида амалга оширилган!', 'content'=>'']);
        }

        $con = Contract::find($request->contract_id);

        $validate["created_by"] = auth()->id();
        $validate["user_id"] = $con->user_id;
        ContractPayment::updateOrCreate(['id' => $request->payment_id],$validate);



        $res = json_decode($this->emitRepayment->transAutopayHistory($con->auto_pay), true);
        if($res['result']){
            foreach ($res['result'] as $r){
                if (!\DB::table('contract_auto_pays')->where([['refnum', $r['refnum']],['respText', $r['respText']]])->first()){
                    \DB::table('contract_auto_pays')->insert($r);
                }
            }
        }

        $payments = ContractPayment::where([
            ['client_id', $request->client_id],
            ['contract_id', $request->contract_id],
            ['cancelled', false]
        ])->sum('amount');

        $payments_not_auto = ContractPayment::where([
            ['client_id', $request->client_id],
            ['contract_id', $request->contract_id],
            ['cancelled', false],
            ['auto_pay_trans_id', null]
        ])->sum('amount');

        $con->update(['amount_paid' => $payments]);

        $residue = (($con->amount+$con->tax+$con->expense) - $payments)*100;

        if ($con->auto_pay) $this->emitRepayment->autopayUpdate($con->auto_pay, (int)$residue);
        $this->uniAccess->debitUpdate($con->contract_name, (int)$residue);

        if (($con->amount_paid - ($con->amount+$con->tax+$con->expense)) > -0.99){
            $con->update(['status' => 10, 'closed_at' => Carbon::now(), 'auto_pay_activate' => false]);

            if ($con->auto_pay) $this->emitRepayment->autopayStop($con->auto_pay);
            $this->uniAccess->autopayToggle($con->contract_name, false);
        }


        return response(['message' => $request->payment_id ? 'Тўлов ўзгартирилди!':'Тўлов амалга оширилди!', 'content'=>$request->amount]);

    }

    public function postDeletePayment($id)
    {
        $data = ContractPayment::find($id);
        if ($data->auto_pay_trans_id){
            $cap = \DB::table('contract_auto_pays')->where('id', $data->auto_pay_trans_id)->first();
            $res = json_decode($this->emitRepayment->transReverseRefnum($cap->refnum),true);
            if($res['result']){
                foreach ($res['result'] as $r){
                    if (!\DB::table('contract_auto_pays')->where([['refnum', $r['refnum']],['respText', $r['respText']]])->first()){
                        \DB::table('contract_auto_pays')->insert($r);
                    }
                    if ($r['respText'] == 'ROK') $data->update(['cancelled'=>true]);
                }
            }
        } elseif ($data->uni_trans_id){
            $cap = \DB::table('auto_uni_pays')->where('id', $data->uni_trans_id)->first();
            $res = json_decode($this->uniAccess->paymentCancel($cap->ext_id, $cap->transaction_id),true);
            $res = json_decode($res,true);
            if($res['status']){
                $r = Arr::except($res['result'], ['client', 'updated_at','is_sent','refunded_by']);

                $r['date'] = Carbon::parse($r['date'])->format('Y-m-d');
                $r['refunded_at'] = Carbon::parse($r['refunded_at'])->format('Y-m-d H:i:s');
                $r['created_at'] = Carbon::parse($r['created_at'])->format('Y-m-d H:i:s');

                if (!\DB::table('auto_uni_pays')->where([['transaction_id', $r['transaction_id']],['status', $r['status']]])->first())
                    \DB::table('auto_uni_pays')->insert($r);
                if ($r['status'] == 'Cancelled') $data->update(['cancelled'=>true]);
            }
        }else{
            $data->update(['cancelled'=>true]);
        }

        $payments = ContractPayment::where([
            ['client_id', $data->client_id],
            ['contract_id', $data->contract_id],
            ['cancelled', false]
        ])->sum('amount');

        Contract::find($data->contract_id)->update(['amount_paid' => $payments]);

        return response(['message' => $data->cancelled ? 'Тўлов ўчирилди!':'Тўлов ўчирилмади!', 'content'=>$data->amount]);

    }


    public function getPayments($id, $type = 2)
    {
        $html = '';
        $html .= '<table class="table table-sm table-hover table-bordered table-striped table-nowrap align-middle" id="cp"><tr><th>Сумма</th><th>Сана</th><th>Изоҳ</th><th>Тўлов тури</th></tr>';
        $cp = ContractPayment::where('contract_id', $id);
        if ($type == 2){
            $cp->where(function ($query) {
                $query->where('type', 2)
                    ->orWhereNull('type');
            });
        }
        if ($type == 1){
            $cp->where('type', $type);
        }
        $cp = $cp->get();
        foreach ($cp as $c){
            $html .= "<tr><td>".number_format($c->amount,2,"."," ")."</td><td>".Carbon::parse($c->date)->format('d.m.Y')."</td>";
            $html .= "<td>".( $c->cancelled ? 'Cancelled':$c->note )."</td>";
            $html .= "<td>".
                ($c->type == 1 ? ("Plastik".($c->auto_pay_trans_id ? " (".$c->auto_pay_trans_id."-EMIT)":
                        ($c->uni_trans_id ? " (".$c->uni_trans_id."-UNIACCESS)":"")))
                    : ($c->type == 2 ? "Naqd":""))."</td>";
            $html .= "<td style='display: flex; justify-content: space-between'>".
                (!auth()->user()->can('autopay-delete') ? "" : "<i class='fa fa-pen' style='cursor: pointer' onclick='changePayment(`{$c->id}`, `".number_format($c->amount,2,"."," ")."`, `{$c->date}`, `{$c->note}`, `{$c->type}`)'></i>")
            .(auth()->user()->can('autopay-delete') && $c->type == 1 ? "&nbsp; <i class='fa fa-trash' style='cursor: pointer; color:red' onclick='deletePayment(`{$c->id}`)'></i>":"") . "
            </td></tr>";
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
	    ->leftjoin('contract_hybrids', 'contract_hybrids.contract_id', '=', 'contracts.id')
            ->join('clients', 'clients.id', '=', 'contracts.client_id')
            ->join('categories', 'categories.id', '=', 'contracts.category_id')
	    ->where('contract_hybrids.contract_id', null)
            ->select('clients.*','contracts.*','categories.name as cat', 'contract_hybrids.contract_id as hyd_id')->get();

        $user = auth()->user();

        foreach ($contracts as $contract){ sendEmail::dispatch($contract, $user); }
//        foreach ($contracts as $contract){ $this->handle($contract, $user); }

        return response(['message' => 'Шартномалар электрон почтага юборилди!', 'content'=>"", 'status'=>'success', 'color' => 'green']);
        //return response(['message' => 'Сақлаш билан муаммо юз берди!', 'content'=>"Бироздан сўнг уриниб кўринг!"]);
    }

/*
    public function handle($contract, $user)
    {

        $forQr = '';
        $forQr .= 'Talabnoma ID: '.$contract->id.';';
        $forQr .= ' FISh '.$contract->fullname.';';
        $forQr .= ' Summa '.$contract->amount;

        $qrName = md5('qrcode_'.$contract->id.'_'.time()).'.png';
        $imagePath = public_path('petition/qrcode/'.$qrName);
        $qr = QrCode::format('png')->size(300)->generate($forQr, $imagePath);

        $filePath = public_path('/petition/template/Talabnoma.docx');
        $templateProcessor = new TemplateProcessor($filePath);
        $templateProcessor->setValue('id', $contract->id);
        $templateProcessor->setValue('curdate', now()->translatedFormat('d.m.Y'));
        $templateProcessor->setValue('fullname', $contract->fullname);
        $templateProcessor->setValue('address', $contract->address);
        $templateProcessor->setValue('phone', $contract->phone);
        $templateProcessor->setValue('date_payment', $contract->amount);
        $templateProcessor->setValue('amount', $contract->amount);
        $templateProcessor->setValue('phone', $contract->phone);
        $templateProcessor->setValue('phone_user', $user->phone);
        $templateProcessor->setValue('name_user', $user->name);
        $templateProcessor->setImageValue('qr', $imagePath);

        if ($contract->category_id >= 8 && $contract->category_id <= 19)
            $templateProcessor->setValue('bank', $contract->cat);
        $file = 'petition/Talabnoma_'.$contract->id.'.docx';
        $values['file'] = public_path($file);
        $templateProcessor->saveAs($values['file']);

        $contract_files = ContractFile::create([
            'contract_id' => $contract->id,
            'file' => '/'.$file,
            'name' => 'Talabnoma',
        ]);

        User::auditable('contract_files', $contract_files->id, json_encode($contract_files->toArray()), 'C');

        $pdfContent = file_get_contents($file);
        $base64Pdf = base64_encode($pdfContent);

        $url = 'https://api.faktura.uz/Api/HybridDocument/Post?companyInn='.env('COMPANY_INN');
        $postData = [
            "CompanyInn" => env('COMPANY_INN'),
            "Region" => $contract->region_id,
            "Area" => $contract->district_id,
            "FullName" => $contract->fullname,
            "Address" => $contract->address,
            "SenderName" => "APEX  INSURANCE AJ",
            "SenderAddress" => "100099, ГОРОД ТАШКЕНТ, ЮНУСАБАДСКИЙ РАЙОН, УЛИЦА А.ТЕМУРА-О.ЗОКИРОВА , 5/1",
            "Base64Content" => $base64Pdf
        ];
        return (new FakturaService())->getSendRequest($url, 'POST', $postData, $contract->id);
    }
*/





    public function getGroupExcel(Request $request)
    {
	    ini_set("memory_limit", "1G");
	    if ($request->type == 'payments') return Excel::download(new ContractPaymentsExport(), 'contractPayments.xlsx');
            if ($request->type == 'contracts') return Excel::download(new ContractsExport(), 'contracts.xlsx');
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
                $vars = ['$contract_id','$category_name','$number','$contract_name','$contract_date','$fullname','$passport','$pinfl','$phone','$address','$dtb','$type','$date_payment','$amount_sum','$amount_paid','$residue','$status','$contract_note','$created_at','$tax','$expense','$judge_name','$judge_no','$judge_result','$judge_note','$mib_name','$mib_no','$mib_result','$mib_note','$curdate','$admin_name','$admin_phone', '$inn','$mfo','$account_number','$phone','$bank_name'];
                $changeVal = [$data->id,$data->category->name, $data->number, $data->name,Carbon::parse($data->date)->translatedFormat('d M Y'),$data->client->fullname,$data->client->passport,$data->client->pinfl,$item,$data->client->address,Carbon::parse($data->client->dtb)->format('d.m.Y'),$data->client->type ? 'Юридик шахс':'Жисмоний шахс',Carbon::parse($data->date_payment)->format('d.m.Y'),number_format($data->amount, 0, ',', ' '),number_format($data->amount_paid, 0, ',', ' '),round(($data->amount - $data->amount_paid), 2),Client::STATUS_COLOR[$data->status],$data->note,Carbon::parse($data->created_at)->format('d.m.Y'),$data->tax,$data->expense, $judge->$type, $data->judge?->work_number,$data->judge?->result,$data->judge?->note,$data->mib?->name,$data->mib?->work_number,$data->mib?->result,$data->mib?->note, now()->format('Y').' йил '. now()->translatedFormat('d M'), $user->name, $user->phone, $data->client->inn, $data->client->mfo, $data->client->account_number, $data->client->phone, $data->bank_name];

                $result = str_replace($vars, $changeVal, $sms_template->content);
                sendSMS::dispatch($item, $result, $data->id);
            }
        }
        return response(['message' => 'Шартномалар смс юборилди!', 'content'=>"", 'status'=>'success', 'color' => 'green']);
    }

    public function getNewTrans(Request $request)
    {
        $request->new_trans;
        $cap = \DB::table('contract_auto_pays')->where('refnum', $request->new_trans)->get();
        if (count($cap) == 0){
            return response(['message' => 'Emitdan yuklangmagan!', 'content'=>"", 'status'=>'success', 'color' => 'green']);
        }
        if (count($cap) == 1){
            $cap = collect($cap)->first();
            $cp = \DB::table('contract_payments')->where('auto_pay_trans_id', $cap->id)->count();
            if ($cp) return response(['message' => 'Bu tranzaksiya allaqachon qo\'shilgan!', 'content'=>"", 'status'=>'success', 'color' => 'green']);
            $cn = \DB::table('contract_names')->where('contractId', $cap->contractId)->first();

            \DB::table('contract_payments')->insert([
                'auto_pay_trans_id' => $cap->id,
                'client_id' => $cn->client_id,
                'contract_id' => $cn->contract_id,
                'date' => \Carbon\Carbon::createFromFormat('ymdHis',$cap->date12)->format('Y-m-d'),
                'amount' => $cap->amount/100,
                'type' => 1,
            ]);

            $contact = Contract::find($cn->contract_id);
            $payments = ContractPayment::where([
                ['contract_id', $cn->contract_id],
                ['cancelled', false]
            ])->sum('amount');

            $contact->update(['amount_paid' => $payments]);
            if (($contact->amount_paid -$contact->amount - $contact->expense - $contact->tax) >= -0.99) {
                $contact->update([
                    'auto_pay_activate' => 0,
                    'status' => 10
                ]);
            }


            return response(['message' => 'Qo\'shildi!', 'content'=>"", 'status'=>'success', 'color' => 'green']);
        }
        return response(['message' => 'Transaksiya qo\'shilmagan asosiy bazadan yangilang!', 'content'=>"", 'status'=>'success', 'color' => 'green']);
    }

    public function postChangeStatus(Request $request, $id)
    {
        Contract::find($id)->update(['status' => $request->change_status]);
        return response(['status'=>'success']);
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
	\DB::table('contracts')->where('id',$id)->update(['judge_number'=>$request->work_number]);
        $contract = Contract::find($id);
        if ($contract->status <= 2){
            $contract->status = 3;
            $contract->save();
        }
        return response(['message' => 'Маълумотлар сақланди!', 'content'=>""]);
    }

    public function postMibInfo(Request $request, $id)
    {


        ContractMib::updateOrCreate(['contract_id' => $id], $request->except(['_token','user_id','user_check','attached_at']));
	\DB::table('contracts')->where('id',$id)->update(['mib_number'=>$request->work_number]);
        $contract = Contract::find($id);
        if ($contract->status < 6){
            $contract->status = 8;
            $contract->save();
        }
        if (isset($request->user_id) && isset($request->user_check) && $request->work_number != null){
            $contract->user_id = $request->user_id;
            $contract->attached_at = $request->attached_at;
            $contract->save();
        }else{
	    if(isset($request->user_id)){
//        dd($request->all());
        	$contract->user_id = null;
                $contract->attached_at = null;
        	$contract->save();
	    }
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

    public function getAutoPayRefresh($date){
        $date = Carbon::parse($date)->format('Ymd');
	\Log::info($date);
        $transAll = $this->emitRepayment->transHistory($date,$date);
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
                }
            }
        }
        return response()->json(['message'=>'Муваффақиятли янгиланди!', 'color'=>'green']);
    }

    public function getAutoPay(Request $request, $id)
    {
        $contract_f = null;
        if (isset($request->contract_name) && $request->contract_name != \DB::table('contracts')->where('id',$id)->first()->contract_name){
            \DB::table('contracts')->where('id',$id)->update([
                'contract_name' => $request->contract_name,
                'autopay_id' => null,
                'auto_pay' => null,
                'auto_pay_activate' => null,
                'closed_at' => null,
            ]);
            $contract_f = \DB::table('contracts')->where('id',$id)->first();
            \DB::table('contract_names')->insert([
                'client_id' => $contract_f->client_id,
                'contract_id' => $contract_f->id,
                'contract_name' => $contract_f->contract_name,
            ]);
        }

        $contract = \DB::table('contracts')
            ->select('clients.fullname', 'clients.pinfl', 'clients.passport', 'clients.phone', 'contracts.contract_name', 'contracts.id', 'contracts.client_id', 'contracts.auto_pay_activate', 'contracts.auto_pay',
	    \DB::raw('(contracts.amount + contracts.tax + contracts.expense - ifnull(sum(contract_payments.amount), 0)) as residue', 'auto_pay'))
            ->join('clients', 'contracts.client_id', '=', 'clients.id')
            ->leftJoin('contract_payments', function($join){
                $join->on('contracts.id', '=', 'contract_payments.contract_id')->where('contract_payments.cancelled', '!=', '1');
            })
            ->where('contracts.id', $id);

//	if(\DB::table('contract_payments')->where('contract_id',$id)->count()) $contract->where('contract_payments.cancelled', '!=', true);

        \Log::info($contract->groupBy('contracts.id')->toSql());
        $contract = $contract->groupBy('contracts.id')->first();
        $conName = \DB::table('contract_names')->where('contract_name', $contract_f->contract_name)->first();

        $residue = $contract->residue == null ? 0.00 : $contract->residue*100;

//        return response()->json(['message'=>"Hali ishga tushmagan!", 'color'=>'red']);

        $uniAccess = json_decode($this->uniAccess->clientCreate($contract), true);
//        $emitClient = json_decode($this->emitRepayment->clientCreate($contract), true);
        $emitClient = [];
/*        if ($emitClient['result']) {
            $emitClient = $emitClient['result'];
            if (!(isset($emitClient['contractId']) && isset($emitClient['clientId']))) return response()->json(['message'=>'Parameter not found!', 'color'=>'red']);
            $con = \DB::table('contracts')->where('autopay_id', $emitClient['contractId']);
            $cli = \DB::table('clients')->where('autopay_id', $emitClient['clientId']);

            if ($con->count() == 0) {
                \DB::table('contracts')->where('id', $contract->id)->update(['autopay_id' => $emitClient['contractId']]);
                \DB::table('contract_names')->where('contract_name', $contract->contract_name)->update([
                    'contractId' => $emitClient['contractId']
                ]);
            }
            if ($cli->count() == 0) {
                \DB::table('clients')->where('id', $contract->client_id)->update(['autopay_id' => $emitClient['clientId']]);
                \DB::table('contract_names')->where('contract_name', $contract->contract_name)->update([
                    'clientId' => $emitClient['clientId']
                ]);
            }
        }*/
        /*else{
            return response()->json(['message'=>$emitClient["error"]['message'], 'color'=>'red']);
        }*/

        if ($contract->residue <= 0) return response()->json(['message'=>'Paid for this contract', 'color'=>'red']);

        if ($request->action == 'false' && $contract->auto_pay_activate && $contract->auto_pay) {
            $uniAccess = json_decode($this->uniAccess->autopayToggle($conName->contract_name, false), true);
            $emitAutopay = json_decode($this->emitRepayment->autopayPause($contract->auto_pay), true);
            if ($emitAutopay['result'] || $uniAccess["status"]) {
                \DB::table('contracts')->where('id', $contract->id)->update(['auto_pay_activate' => false]);
                return response()->json(['message'=>"Autopay for {$contract->contract_name} contract paused.", 'color'=>'green']);
            }
            else{
		        if ($emitAutopay['result'] == null && $emitAutopay['error']['message'] == 'AutoPayment is finished!') {
            	    \DB::table('contracts')->where('id', $contract->id)->update(['auto_pay_activate' => false]);
            	    return response()->json(['message'=>"{$contract->contract_name} - AutoPayment is finished!", 'color'=>'green']);
         	    }
                return response()->json(['message'=>$emitAutopay["error"]['message'], 'color'=>'red']);
            }
        }else{
            if (!$contract->auto_pay_activate && $contract->auto_pay) {
                $uniAccess = json_decode($this->uniAccess->autopayToggle($conName->contract_name, true), true);
//                $emitAutopay = json_decode($this->emitRepayment->autopayResume($contract->auto_pay), true);
                if ($uniAccess["status"]) {
                    \DB::table('contracts')->where('id', $contract->id)->update(['auto_pay_activate' => true]);
                    return response()->json(['message'=>"Autopay for {$contract->contract_name} contract resume.", 'color'=>'green']);
                }
//                else return response()->json(['message'=>$emitAutopay["error"]['message'], 'color'=>'red']);
            }
            if (!$contract->auto_pay) {
                $new_contract = \DB::table('contracts')->where('id',$id)->first();
                $payments = ContractPayment::where([
                    ['contract_id', $id],
                    ['cancelled', false]
                ])->sum('amount');

                $residue_new = (($new_contract->amount+$new_contract->tax+$new_contract->expense) - $payments)*100;

                /*$detailsAutopay = [ "contractId"=> $emitClient['contractId'],
                    "amount"=> (int)$residue_new,
                    "startDate"=> date('Ymd')
                ];*/
//                \Log::info('detailsAutopay: '. json_encode($detailsAutopay));

                $this->uniAccess->autopayToggle($contract->contract_name, true);

                /*$emitAutopay = json_decode($this->emitRepayment->autopayCreate($detailsAutopay), true);
                if($emitAutopay['result']){
                    \DB::table('contracts')->where('id', $contract->id)->update([
                        'autopay_id'=>$emitAutopay['result']['contractId'],
                        'auto_pay'=>$emitAutopay['result']['autopayId'],
                        'auto_pay_activate' => true
                    ]);
                    \DB::table('contract_names')->where('contract_name', $contract->contract_name)->update([
                        'contractId' => $emitAutopay['result']['contractId']
                    ]);

                    return response()->json(['message'=>"Autopay for {$contract->contract_name} contract created.", 'color'=>'green']);
                }else{
                    return response()->json(['message'=>$emitAutopay["error"]['message'], 'color'=>'red']);
                }*/
            }
        }
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
