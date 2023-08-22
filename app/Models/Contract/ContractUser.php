<?php

namespace App\Models\Contract;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractUser extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'contract_users';
}
