<?php

namespace App\Jobs;

use App\Models\Contract\ContractFile;
use App\Models\User;
use App\Services\FakturaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use PhpOffice\PhpWord\TemplateProcessor;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class sendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $contract, $user;

    public $tries = 3;

    public function __construct($contract, $user)
    {
        $this->contract = $contract;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $forQr = '';
        $forQr .= 'Talabnoma ID: '.$this->contract->id.';';
        $forQr .= ' FISh '.$this->contract->fullname.';';
        $forQr .= ' Summa '.$this->contract->amount;

        $qrName = md5('qrcode_'.$this->contract->id.'_'.time()).'.png';
        $imagePath = public_path('petition/qrcode/'.$qrName);
        $qr = QrCode::format('png')->size(300)->generate($forQr, $imagePath);

        $filePath = public_path('/petition/template/Talabnoma.docx');
        $templateProcessor = new TemplateProcessor($filePath);
        $templateProcessor->setValue('id', $this->contract->id);
        $templateProcessor->setValue('curdate', now()->translatedFormat('d.m.Y'));
        $templateProcessor->setValue('fullname', $this->contract->fullname);
        $templateProcessor->setValue('address', $this->contract->address);
        $templateProcessor->setValue('phone', $this->contract->phone);
        $templateProcessor->setValue('date_payment', $this->contract->amount);
        $templateProcessor->setValue('amount', $this->contract->amount);
        $templateProcessor->setValue('phone', $this->contract->phone);
        $templateProcessor->setValue('phone_user', $this->user->phone);
        $templateProcessor->setValue('name_user', $this->user->name);
        $templateProcessor->setImageValue('qr', $imagePath);

        if ($this->contract->category_id >= 8 && $this->contract->category_id <= 19)
            $templateProcessor->setValue('bank', $this->contract->cat);
        $file = 'petition/Talabnoma_'.$this->contract->id.'.docx';
        $values['file'] = public_path($file);
        $templateProcessor->saveAs($values['file']);

        $contract_files = ContractFile::create([
            'contract_id' => $this->contract->id,
            'file' => '/'.$file,
            'name' => 'Talabnoma',
        ]);

        // User::auditable('contract_files', $contract_files->id, json_encode($contract_files->toArray()), 'C');


        $pdfContent = file_get_contents($file);
        $base64Pdf = base64_encode($pdfContent);

        $url = 'https://api.faktura.uz/Api/HybridDocument/Post?companyInn='.env('COMPANY_INN');
        $postData = [
            "CompanyInn" => env('COMPANY_INN'),
            "Region" => $this->contract->region_id,
            "Area" => $this->contract->district_id,
            "FullName" => $this->contract->fullname,
            "Address" => $this->contract->address,
            "SenderName" => "APEX  INSURANCE AJ",
            "SenderAddress" => "100099, ГОРОД ТАШКЕНТ, ЮНУСАБАДСКИЙ РАЙОН, УЛИЦА А.ТЕМУРА-О.ЗОКИРОВА , 5/1",
            "Base64Content" => $base64Pdf
        ];

        if (env('IS_IDE',0) == 1) (new FakturaService())->getSendRequest($url, 'POST', $postData, $this->contract->id);


//        unlink(public_path($file));
//        unlink($imagePath);

    }
}
