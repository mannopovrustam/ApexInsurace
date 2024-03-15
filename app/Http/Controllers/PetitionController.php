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

            $curdate = Carbon::now()->translatedFormat('d-F') . ' ' . Carbon::now()->format('Y') . ' yil ';
            $date = Carbon::parse($data->date)->translatedFormat('d-F') . ' ' . Carbon::parse($data->date)->translatedFormat('Y')  . ' yil ';
            $user = auth()->user();

            $changeVal = [
                'contract_id' => $data->id,
                'category_name' => $this->cyrillicToLatin($data->category->name),
                'number' => $this->cyrillicToLatin($data->number),
                'contract_name' => $this->cyrillicToLatin($data->name),
                'contract_date' => $this->cyrillicToLatin($date),
                'inn' => $data->client->inn,
                'mfo' => $data->client->mfo,
                'account_number' => $this->cyrillicToLatin($data->client->account_number),
                'fullname' => $this->cyrillicToLatin($data->client->fullname),
                'passport' => $this->cyrillicToLatin($data->client->passport),
                'pinfl' => $data->client->pinfl,
                'phone' => $data->client->phone,
                'address' => $this->cyrillicToLatin($data->client->address),
                'dtb' => Carbon::parse($data->client->dtb)->format('d.m.Y'),
                'type' => $this->cyrillicToLatin($data->client->type ? 'Юридик шахс' : 'Жисмоний шахс'),
                'date_payment' => Carbon::parse($data->date_payment)->format('d.m.Y'),
                'amount_sum' => number_format($data->amount, 0, ',', ' '),
                'amount_paid' => number_format($data->amount_paid, 0, ',', ' '),
                'residue' => round(($data->amount - $data->amount_paid), 2),
                'status' => Client::STATUS_COLOR[$data->status],
                'contract_note' => $this->cyrillicToLatin($data->note),
                'created_at' => Carbon::parse($data->created_at)->format('d.m.Y'),
                'tax' => $data->tax,
                'expense' => $data->expense,
                'bank_name' => $data->bank_name,
                'judge_name' => $this->cyrillicToLatin($judge->$type),
                'judge_no' => $data->judge?->work_number,
                'judge_result' => $this->cyrillicToLatin($data->judge?->result),
                'judge_note' => $this->cyrillicToLatin($data->judge?->note),
                'mib_name' => $this->cyrillicToLatin($data->mib?->name),
                'mib_no' => $data->mib?->work_number,
                'mib_result' => $this->cyrillicToLatin($data->mib?->result),
                'mib_note' => $this->cyrillicToLatin($data->mib?->note),
                'curdate' => $this->cyrillicToLatin($curdate),
                'admin_name' => $this->cyrillicToLatin($user->name),
                'admin_phone' => $user->phone,
            ];

            $doc_template = DocTemplate::find($request->template_id);

            $filePath = $doc_template->template;

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

    function cyrillicToLatin($text) {
        $cyrillic = array(
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'ў', 'ғ', 'қ', 'ҳ',
            'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'ъ', 'ь', 'э', 'ю', 'я',
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Ў', 'Ғ', 'Қ', 'Ҳ',
            'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Ъ', 'Ь', 'Э', 'Ю', 'Я'
        );

        $latin = array(
            'a', 'b', 'v', 'g', 'd', 'e', 'yo', 'j', 'z', 'i', 'y', 'k', 'l', 'm', 'o‘', 'g‘', 'q', 'h',
            'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'x', 'ts', 'ch', 'sh', '\'', '', 'e', 'yu', 'ya',
            'A', 'B', 'V', 'G', 'D', 'E', 'Yo', 'J', 'Z', 'I', 'Y', 'K', 'L', 'M', 'O‘', 'G‘', 'Q', 'H',
            'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'X', 'Ts', 'Ch', 'Sh', '\'', '', 'E', 'Yu', 'Ya'
        );

        return str_replace($cyrillic, $latin, $text);
    }

}
