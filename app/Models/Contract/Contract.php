<?php

namespace App\Models\Contract;

use App\Models\Category;
use App\Models\Client;
use App\Models\Judge;
use App\Models\Petition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function files()
    {
        return $this->hasMany(ContractFile::class);
    }

    public function petitions()
    {
        return $this->hasMany(Petition::class);
    }
    public function payments()
    {
        return $this->hasMany(ContractPayment::class);
    }

    public function hybrids()
    {
        return $this->hasMany(ContractHybrid::class);
    }

    public function judge()
    {
        return $this->hasOne(ContractJudge::class);
    }

    public function mib()
    {
        return $this->hasOne(ContractMib::class);
    }
    public function sms()
    {
        return $this->hasMany(ContractSms::class);
    }
}
