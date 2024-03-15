<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\Contract\Contract;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Excel;

class ContractsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;

    /**
     * It's required to define the fileName within
     * the export class when making use of Responsable.
     */
    private $fileName = 'contracts.xlsx';

    /**
     * Optional Writer Type
     */
    private $writerType = Excel::XLSX;

    /**
     * Optional headers
     */
    private $headers = [
        'Content-Type' => 'text/csv',
    ];
    private $ids;
    public function __construct()
    {
        //
    }

    public function collection()
    {
        $names = [
            "SHARTNOMA ID",
            "SHARTNOMA NOMI",
            "SHARTNOMA RAQAM",
            "SANA",
            "TO'LOV SANASI",
            "SUG'URTA TOVONI SUMMASI",
            "JAMI QARZDORLIK",
            "TO'LANGAN SUMMA",
            "YARATILGAN SANA",
            "YOPILGAN",
            "TO'LIQ ISM",
            "TELEFON",
            "PASPORT",
            "PINFL",
            "STIR",
            "KATEGORIYA NOMI",
            "MIB RAQAMI",
            "SUD RAQAMI",
            "SUD NOMI",
            "QOLDIQ QARZDORLIK",
            "РЕГИОН",
            "ҲОДИМ",
            "ҲОДИМГА ТОПШИРИЛГАН САНА",
            "АВТОСПИСАНИЯ",
        ];

//        $data = json_decode(json_encode(\DB::select(session('contracts_query'))), true);

        $query = session('contracts_query');
        $query = str_replace(" `users`.`name` as `user_name`,", "", $query);
        $query = str_replace(" from `contracts`", ", regions.name as region, users.name as user_name, contracts.attached_at as attached_at from `contracts`", $query);
        $query = str_replace("select ", "select contracts.id, ", $query);
        $query = str_replace("`contracts`.`amount`,", "`contracts`.`amount`,contracts.amount+contracts.tax+contracts.expense as all_amount,", $query);
        $query = str_replace("sum(contract_payments.amount) as payment_total", "contracts.amount+contracts.tax+contracts.expense - contracts.amount_paid as all_amount_left", $query);
        $query = str_replace("`contracts`.`judge_number` as `judge_no`, ", "`contracts`.`judge_number` as `judge_no`, `contracts`.`judge_name`, ", $query);
        $query = str_replace("attached_at from", "attached_at, contracts.auto_pay_activate from", $query);
	\Log::info($query);

        $data = json_decode(json_encode(\DB::select($query)), true);

        return collect($data)->prepend($names);
    }

}
