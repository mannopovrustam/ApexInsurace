<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\Contract\Contract;
use App\Models\DocTemplate;
use App\Models\Petition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;

class PetitionController extends Controller
{

    public $path;

    public function __construct()
    {
        $this->middleware('auth');
        $this->path = 'view.' . Str::singular(\Request::segment(1));
    }

    public function index()
    {
        return view($this->path . '.index');
    }

    public function create()
    {
        $data = new Petition();
        $data->client = new Client();
        $data->category = new Category();

        return view($this->path . '.create', ['data' => $data]);
    }

    public function getAddPetition()
    {
        $request = request();
        $values = $request->except(['_token', 'data_id']);
        Carbon::setLocale('uz');

        try {
            \DB::beginTransaction();
            $data = Contract::with(['client', 'category', 'files', 'petitions', 'hybrids', 'judge', 'mib'])->find($request->contract_id);

            if ($data->client->type == 1) $type = 'legal_judge';
            else $type = 'phy_judge';

            $judge = \DB::table($type)->join('judges', $type . '.judge_id', '=', 'judges.id')
                ->where([['region_id', $data->client->region_id], ['district_id', $data->client->district_id]])
                ->select('judges.*')->first();

            $curdate = now()->format('Y') . ' йил ' . now()->translatedFormat('d M');
            $date = Carbon::createFromFormat('Y-m-d', $data->date)->format('Y') . ' йил ' . Carbon::createFromFormat('Y-m-d', $data->date)->translatedFormat('d M');
            $user = auth()->user();

            $changeVal = [
                'contract_id' => $data->id,
                'category_name' => $data->category->name,
                'number' => $data->number,
                'contract_name' => $data->name,
                'contract_date' => $date,
                'inn' => $data->client->inn,
                'mfo' => $data->client->mfo,
                'account_number' => $data->client->account_number,
                'fullname' => $data->client->fullname,
                'passport' => $data->client->passport,
                'pinfl' => $data->client->pinfl,
                'phone' => $data->phone,
                'address' => $data->client->address,
                'dtb' => Carbon::parse($data->client->dtb)->format('d.m.Y'),
                'type' => $data->client->type ? 'Юридик шахс' : 'Жисмоний шахс',
                'date_payment' => Carbon::parse($data->date_payment)->format('d.m.Y'),
                'amount_sum' => number_format($data->amount, 0, ',', ' '),
                'amount_paid' => number_format($data->amount_paid, 0, ',', ' '),
                'residue' => round(($data->amount - $data->amount_paid), 2),
                'status' => Client::STATUS_COLOR[$data->status],
                'contract_note' => $data->note,
                'created_at' => Carbon::parse($data->created_at)->format('d.m.Y'),
                'tax' => $data->tax,
                'expense' => $data->expense,
                'judge_name' => $judge->$type,
                'judge_no' => $data->judge?->work_number,
                'judge_result' => $data->judge?->result,
                'judge_note' => $data->judge?->note,
                'mib_name' => $data->mib?->name,
                'mib_no' => $data->mib?->work_number,
                'mib_result' => $data->mib?->result,
                'mib_note' => $data->mib?->note,
                'curdate' => $curdate,
                'admin_name' => $user->name,
                'admin_phone' => $user->phone,
            ];

            $doc_template = DocTemplate::find($request->template_id);

            $filePath = public_path($doc_template->template);

            $templateProcessor = new TemplateProcessor($filePath);
            foreach ($changeVal as $key => $value) {
                $templateProcessor->setValue($key, $value);
            }

            $file = '/petition/docs/' . '__' . $request->contract_id . '_' . $request->template_id . '.docx';

            $values['file'] = public_path($file);

            $templateProcessor->saveAs($values['file']);

            Petition::updateOrCreate([
                'id' => isset($request->data_id) ? $request->data_id : null
            ], [
                'contract_id' => $values['contract_id'],
                'template_id' => $request->template_id,
                'file' => $file,
                'created_by' => $user->id,
            ]);
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return $e->getMessage();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Успешно сохранено!'
        ]);

    }

    public function show($id)
    {
        $data = Petition::with(['client', 'category'])->find($id);
        return view($this->path . '.show', [
            'data' => $data
        ]);
    }

    public function edit($id)
    {
        $data = Petition::with(['client', 'category'])->find($id);
        return view($this->path . '.create', [
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $data = Petition::find($id);
        \File::delete($data->file);
        $data->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Успешно удалено!'
        ]);
    }
}
