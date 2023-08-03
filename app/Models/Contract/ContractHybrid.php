<?php

namespace App\Models\Contract;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractHybrid extends Model
{
    use HasFactory;

    protected $guarded = [];

    const STATUS = [
        0  => "Доставлен",
        1  => "Адресат умер",
        2  => "Адресат по указанному адресу не проживает",
        3  => "Указан не полный адрес",
        4  => "Адресат от получения отказался",
        5  => "Нет дома",
        6  => "Не явился по извещению",
        7  => "Адресат не определен",
        8  => "Попытка вручения",
        9  => "По указанному адресу организация не найдена",
        -2  => "В процессе обработки",
        -1  => "Создан",
    ];
    const STATUS_COLOR = [
        0  => "success",
        1  => "danger",
        2  => "danger",
        3  => "danger",
        4  => "danger",
        5  => "danger",
        6  => "danger",
        7  => "danger",
        8  => "danger",
        9  => "danger",
        -2  => "warning",
        -1  => "info",
    ];
}
