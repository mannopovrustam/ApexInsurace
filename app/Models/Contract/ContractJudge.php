<?php

namespace App\Models\Contract;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractJudge extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'contract_judges';

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
