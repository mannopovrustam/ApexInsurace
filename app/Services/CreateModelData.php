<?php
/**
 * Created by PhpStorm.
 * User: acer
 * Date: 02.06.2022
 * Time: 14:02
 */

namespace App\Services;

use App\Models\Selectable;
use App\Traits\UploadImage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateModelData
{
	use UploadImage;
	protected $directory, $values;

	public function __construct()
	{
		$this->directory = substr(\Request::segment(1), 0, -1);
	}

	public function create($request, $model, $path = null)
	{
		$this->values = Arr::except($request->all(), $this->exceptData());

        if ($request->user_id){
            $this->values['user_id'] = decrypt($request->user_id);
        }

        if ($request->category){
            $this->values['category'] = implode(',', $request->category);
        }

        $this->uploadFile($request, $path);

		$model::updateOrCreate(['id' => $request->data_id], $this->values);
    }

    private function exceptData(){
        return [
            'data_id',
            'photo',
            'photo_second'
        ];
    }
    private function uploadFile($request, $path){
        if ($request->file('photo')) {
            $this->values['photo'] = $this->uploadImage($request->photo, 'img/'.$path);
        }
        if ($request->file('photo_second')) {
            $this->values['photo_second'] = $this->uploadImage($request->photo_second, 'img/'.$path);
        }
    }
}
