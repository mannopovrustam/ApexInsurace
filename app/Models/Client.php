<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $guarded = [];

    // new, notified, sent to court, judgment issued (satisfied, terminated, dismissed), MIB pending, fully recovered, debt discharged
    const STATUS = [
        'new' => 1,
        'notified' => 2,
        'sent to court' => 3,
        'judgment issued' => 4,
        'satisfied' => 5,
        'terminated' => 6,
//        'dismissed' => 7,
        'MIB pending' => 8,
//        'fully recovered' => 9,
        'debt discharged' => 10
    ];
    const STATUS_NAME = [
        1 => "Янги",
        2 => "Талабнома юборилган",
        3 => "Судга юборилган",
        4 => "Қарор чиқарилган",
        5 => "Қаноатлантирилган",
        6 => "Тугатилган",
//        7 => "Ishdan bo'shatilgan",
        8 => "МИБ иш юрутувида",
//        9 => "Тўлиқ тикланди",
        10 => "Қарз узилган",
    ];
    const STATUS_COLOR = [
        1 => "bg-info",
        2 => "bg-warning",
        3 => "bg-warning",
        4 => "bg-success",
        5 => "bg-success",
        6 => "bg-success",
//        7 => "bg-green-600",
        8 => "bg-info",
//        9 => "bg-blue-400",
        10 => "bg-success",
    ];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
