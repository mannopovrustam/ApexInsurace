<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\Contract\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class importContract implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $row;
    public function __construct($row)
    {
        $this->row = $row;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->row['no'] == null || $this->row['no'] == '') return;
        // time to date
        // contract_date, payment_date
        $this->row['birthdate'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($this->row['birthdate'])->format('Y-m-d');
        $this->row['contract_date'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($this->row['contract_date'])->format('Y-m-d');
        $this->row['payment_date'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($this->row['payment_date'])->format('Y-m-d');
        // $this->row['category_id'] = \DB::table('categories')->where('name', $this->row['type'])->first()->id;
        $this->row['category_id'] = $this->row['type'];

        $region_id = null; $district_id = null; $type = null;
        $region_id = \DB::table('regions')->where('id', $this->row['region'])->first()->id;
        $district_id = \DB::table('districts')->where('region_id', $region_id)->where('id', $this->row['districts'])->first()->id;
        $type = $this->row['debtor_type'] == 'Жисмоний шахс' ? 0 : 1;

        if (!($region_id && $district_id && $this->row['debtor_type'])) return;
        $client = Client::updateOrCreate([
            'passport' => $this->row['passport'],
        ],[
            "region_id" => $region_id,
            "district_id" => $district_id,
            "fullname" => $this->row['fullname'],
            "passport" => $this->row['passport'],
            "pinfl" => $this->row['pinfl'],
            "phone" => $this->row['phone'],
            "address" => $this->row['address'],
            "dtb" => $this->row['birthdate'],
            "type" => $type,
        ]);
        Contract::updateOrCreate([
            "number" => $this->row['contract_no']
        ],[
            "client_id" => $client->id,
            "category_id" => $this->row['category_id'],
            "number" => $this->row['contract_no'],
            "name" => $this->row['contract_name'],
            "date_payment" => $this->row['payment_date'],
            "date" => $this->row['contract_date'],
            "amount" => $this->row['amount'],
            "amount_paid" => $this->row['amount_paid']
        ]);
        //  User::auditable('contracts', $contract->id, $contract, 'C');
    }
}
