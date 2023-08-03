<?php

namespace App\Services;

use App\Jobs\sendSMS;
use App\Models\Category;
use App\Models\DocTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
class DicService extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:СМС шаблон яратиш', ['only' => ['getSms', 'postSms', 'deleteSms']]);
        $this->middleware('permission:Ҳужжат шаблон яратиш', ['only' => ['getDocs', 'postDocs', 'deleteDocs']]);
        $this->middleware('permission:Судларни қўшиш/тахрирлаш', ['only' => ['getJudges', 'postJudges', 'postJudgesUpdate']]);
    }

    public function getJudges()
    {
        $districts = DB::table('districts')
            ->join('regions', 'districts.region_id', '=', 'regions.id')
            ->select('districts.*', 'regions.id as region_id', 'regions.name as region_name')
            ->get();
        $judges = DB::table('judges')->get();

        $phy_judge = DB::table('phy_judge as pj')
            ->join('judges as j', 'pj.judge_id', '=', 'j.id')
            ->join('districts as d', 'pj.district_id', '=', 'd.id')
            ->join('regions as r', 'd.region_id', '=', 'r.id')
            ->select('pj.*', 'j.name as judge_name', 'd.name as district_name', 'r.name as region_name')
            ->get();

        $legal_judge = DB::table('legal_judge as lj')
            ->join('judges as j', 'lj.judge_id', '=', 'j.id')
            ->join('districts as d', 'lj.district_id', '=', 'd.id')
            ->join('regions as r', 'd.region_id', '=', 'r.id')
            ->select('lj.*', 'j.name as judge_name', 'd.name as district_name', 'r.name as region_name')
            ->get();

        return view('view.dic.judge', ['judges' => $judges, 'districts' => $districts, 'phy_judge' => $phy_judge, 'legal_judge' => $legal_judge]);
    }

    public function postJudges(Request $request)
    {
        foreach ($request->district_id as $key => $value) {
            $d = DB::table('districts')->where('id', $value)->first();
            DB::table($request->type)->insert([
                'region_id' => $d->region_id,
                'district_id' => $value,
                'judge_id' => $request->judge_id
            ]);
        }
        session()->put('judge_id', $request->judge_id+1);

        return back()->with('success', 'Judge saved successfully.');
    }

    public function postJudgesUpdate(Request $request)
    {
        $data = DB::table('legal_judge')->where('id', $request->data_id)->update(['judge_id' => $request->judge_id]);
        return response()->json(['success' => 'Judge saved successfully.']);
    }


    public function getRegions()
    {
        $regions = DB::table('regions')->get();
        $districts = DB::table('districts')
            ->join('regions', 'districts.region_id', '=', 'regions.id')
            ->select('districts.*', 'regions.name as region_name')
            ->get();
        return view('view.dic.regions', ['regions' => $regions, 'districts' => $districts]);
    }

    public function postRegion(Request $request)
    {
        DB::table('regions')->updateOrInsert(['id' => $request->data_id],['name' => $request->name]);
        return response()->json(['success' => 'Region saved successfully.']);
    }

    public function postDistrict(Request $request)
    {
        DB::table('districts')->updateOrInsert(['id' => $request->data_id],['name' => $request->name, 'region_id' => $request->region_id]);
        return response()->json(['success' => 'District saved successfully.']);
    }

    public function getSms()
    {
        $sms = DB::table('sms_templates')->get();
        return view('view.dic.sms', ['sms' => $sms]);
    }

    public function postSms(Request $request)
    {
        DB::table('sms_templates')->updateOrInsert(['id' => $request->id],$request->except('_token'));
        if ($request->id) return back()->with('message', 'SMS муваффакиятли янгиланди.');
        return back()->with('success', 'SMS муваффакиятли сақланди.');
    }
    public function deleteSms($id)
    {
        DB::table('sms_templates')->where('id', $id)->delete();
        return back()->with('message', 'SMS муваффакиятли ўчирилди.');
    }

    public function getDocs()
    {
        $docs = DB::table('doc_templates')->get();
        return view('view.dic.docs', ['docs' => $docs]);
    }

    public function postDocs(Request $request)
    {
        $doc = DocTemplate::updateOrCreate(['id' => $request->id],$request->except(['_token', 'template']));
        if ($request->hasFile('template')) {
            $file = $request->file('template');
            $filename = $file->getClientOriginalName();
            $filepath = $file->move('/petition/template', $filename);
            $doc->template = $filepath;
            $doc->save();
        }

        if ($request->id) return back()->with('message', 'Word муваффакиятли янгиланди.');
        return back()->with('success', 'Word муваффакиятли сақланди.');
    }
    public function deleteDocs($id)
    {
        DB::table('doc_templates')->where('id', $id)->delete();
        return back()->with('message', 'Word муваффакиятли ўчирилди.');
    }

    public function postSmsManual(Request $request)
    {
        sendSMS::dispatch($request->sms_phone, $request->sms_message);
        return response()->json(['success' => 'SMS муваффакиятли юборилди.']);
    }

    public function getCategory()
    {
        $category = DB::table('categories')->get();
        return view('view.dic.category', ['category' => $category]);
    }

    public function postCategory(Request $request)
    {
        $category = Category::updateOrCreate(['id' => $request->id],$request->except(['_token', 'template']));

        if ($request->id) return back()->with('message', 'Иш туркуми муваффакиятли янгиланди.');
        return back()->with('success', 'Иш туркуми муваффакиятли сақланди.');
    }
    public function deleteCategory($id)
    {
        DB::table('categories')->where('id', $id)->delete();
        return back()->with('message', 'Иш туркуми муваффакиятли ўчирилди.');
    }

}
