<?php

namespace App\Models\Contract;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractMib extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'contract_mibs';

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
