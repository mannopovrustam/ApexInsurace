<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\Contract\Contract;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Excel;

class ContractPaymentsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;

    /**
     * It's required to define the fileName within
     * the export class when making use of Responsable.
     */
    private $fileName = 'contract_payments.xlsx';

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
            "MIQDORI",
            "TO'LANGAN SUMMA",
            "YARATILGAN SANA",
            "YOPILGAN SANA",
            "TO'LIQ ISM",
            "TELEFON",
            "PASPORT",
            "PINFL",
            "STIR",
            "KATEGORIYA NOMI",
            "MIB RAQAMI",
            "SUD RAQAMI",
            "TO'LOV MIQDORI",
            "TO'LOV SANASI",
            "TO'LOV ESLATMASI",
            "TO'LOV TURI",
            "СТАТУСИ",
            "ҚОЛДИҚ ҚАРЗИ",
            "ШАХС ТУРИ",
            "ҲОДИМ",
            "ҲОДИМ ТОПШИРИЛГАН САНА",
            "Тўлов тизими"
        ];

        $query = session('contract_payments_query');
        $query = str_replace(" `users`.`name` as `user_name`,", "", $query);
        $query = str_replace("payment_type from `contracts`", "payment_type, CASE WHEN contracts.status = 1 THEN 'Янги' WHEN contracts.status = 2 THEN 'Талабнома юборилган' WHEN contracts.status = 3 THEN 'Судга юборилган' WHEN contracts.status = 4 THEN 'Қарор чиқарилган' WHEN contracts.status = 5 THEN 'Қаноатлантирилган' WHEN contracts.status = 6 THEN 'Тугатилган' WHEN contracts.status = 8 THEN 'МИБ иш юрутувида' WHEN contracts.status = 10 THEN 'Қарз узилган' ELSE 'Номаълум' END as status_name, (contracts.amount+contracts.tax+contracts.expense) - contracts.amount_paid as residual_amount, CASE WHEN clients.type = 1 THEN 'Юридик шахс' WHEN clients.type = 0 THEN 'Жисмоний шахс' ELSE 'Номаълум' END as client_type, users.name as user_name, contracts.attached_at as attached_at from `contracts`", $query);
	$query = str_replace("select ", "select contracts.id, ", $query);
	$query = str_replace("attached_at from", "attached_at, CASE WHEN contract_payments.auto_pay_trans_id is not null THEN 'EMIT' WHEN contract_payments.uni_trans_id is not null THEN 'UNIACCESS' ELSE 'НАҚТ' END as payment_sys from", $query);

	session()->put('contract_payments_query',$query);

        $data = json_decode(json_encode(\DB::select($query)), true);

        return collect($data)->prepend($names);
    }
}
