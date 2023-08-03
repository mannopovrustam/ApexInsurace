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
    private $fileName = 'users.xlsx';

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
    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    public function collection()
    {

        $contracts = Contract::with(['client.region', 'client.district', 'category', 'files', 'petitions', 'hybrids', 'judge', 'mib', 'sms', 'payments'])
            ->whereIn('id', $this->ids)->get();

        $data = [];

        foreach ($contracts as $contract){
            if($contract->client->type == 1) $type = 'Юридик шахс';
            else $type = 'Жисмоний шахс';
            $data[] = [
                $contract->id,
                Client::STATUS_NAME[$contract->status],
                $contract->number,
                $contract->client->fullname,
                $contract->client->dtb,
                $contract->client->passport,
                $contract->client->pinfl,
                $contract->client->region->name,
                $contract->client->district->name,
                $contract->client->address,
                $contract->client->phone,
                $contract->category->name,
                $contract->number,
                $contract->name,
                $contract->created_at,
                $type,
                $contract->date_payment,
                $contract->amount,
                $contract->expense,
                $contract->tax,
                $contract->payments->sum('amount'),
                $contract->payments->implode('date', ', '),
                $contract->amount - $contract->payments->sum('amount'),
                $contract->judge?->name,
                $contract->judge?->work_number,
                $contract->judge?->result,
                $contract->judge?->note,
                $contract->mib?->name,
                $contract->mib?->work_number,
                $contract->mib?->result,
                $contract->mib?->note,
                count($contract->sms) > 0 ? $contract->sms?->first()->created_at->format('d.m.Y') : null,
                count($contract->hybrids) > 0 ? $contract->hybrids?->first()->created_at->format('d.m.Y') : null,
            ];
        }

        return collect($data);
    }
}
