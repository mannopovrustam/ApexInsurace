<?php
namespace App\Imports;

use App\Jobs\importContract;
use App\Models\Client;
use App\Models\Contract\Contract;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ContractsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $count = $rows->where('fullname', '!=', null)->count();

        for ($i = 0; $i < $count; $i++)
        {
            $row = $rows[$i];
            importContract::dispatch($row);
        }
    }
}
