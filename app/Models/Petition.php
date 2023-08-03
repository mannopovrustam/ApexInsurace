<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Petition extends Model
{
    use HasFactory;

    protected $guarded = [];

    const TYPES = [
        'Даъво ариза',
        'Судга маълумотнома (Қолдиқ қарздорлик тўғрисида)',
        'Судга ариза (ишни даъвогар вакили иштирокисиз кўриш тўғрисида)',
        'Судга ариза (даъво аризани кўрмасдан қолдириш тўғрисида)',
        'МИБга маълумотнома (қарздорлик тўланганлиги тўғрисида)',
        'МИБга ариза (ижро ҳужжатини Қонуннинг 40-моддаси 1-қисмига асосан қайтариш юзасидан)'
    ];

    const TEMPLATES = [
        'Даъво ариза' => '/petition/template/qarz.docx',
        'Судга маълумотнома (Қолдиқ қарздорлик тўғрисида)' => "/petition/template/Sudga ma'lumotnoma.docx",
        'Судга ариза (ишни даъвогар вакили иштирокисиз кўриш тўғрисида)' => "/petition/template/Ishni vakil ishtirokisiz ko‘rish.docx",
        'Судга ариза (даъво аризани кўрмасдан қолдириш тўғрисида)' => "/petition/template/Da'voni ko‘rmasdan qoldirish.docx",
        'МИБга маълумотнома (қарздорлик тўланганлиги тўғрисида)' => '/petition/template/MIBga malumotnoma.docx',
        'МИБга ариза (ижро ҳужжатини Қонуннинг 40-моддаси 1-қисмига асосан қайтариш юзасидан)' => "/petition/template/MIBga 40-modda.docx",
        'Talabnoma' => '/petition/template/Talabnoma.docx',
    ];

    // RESULTS

}
