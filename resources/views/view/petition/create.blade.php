@extends('layouts.app')

@section('script')
    <script>
    </script>
@endsection
@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="user">
                    <div class="card-header">
                        Регистрация
                    </div>
                    <div class="card-body">
                        <form action="/petitions" method="post">
                            @csrf
                            <input type="hidden" name="data_id" value="{{$data->id}}">
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="number">Иш туркуми</label>
                                        <select class="form-select" name="category_id">
                                            @foreach(\App\Models\Petition::TYPES as $p)
                                                <option value="{{$p}}" @selected($p == $data->category_id)>{{$category->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="brand">Суғурта шартномаси</label>
                                        <input type="text" class="form-control" id="number" value="{{$data->number}}"
                                               name="number" placeholder="Суғурта шартномаси">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="type">Қарздор ФИШ</label>
                                        <input type="text" class="form-control" id="type" value="{{$data->client->fullname}}"
                                               name="fullname" placeholder="ФИШ">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="passport">Паспорт маълумотлари</label>
                                        <input type="text" class="form-control" id="passport"
                                               value="{{$data->client->passport}}"
                                               name="passport" placeholder="ZZ0011223">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="phone">Қарздорнинг телефон рақами</label>
                                        <input type="text" class="form-control" id="phone" value="{{$data->client->phone}}"
                                               name="phone" placeholder="+99800BBBOOII">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="address">Манзили</label>
                                        <input type="text" class="form-control" id="address" value="{{$data->client->address}}"
                                               name="address" placeholder="Манзили">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="dtb">Туғилган санаси</label>
                                        <input type="date" class="form-control" id="dtb" value="{{$data->client->dtb}}"
                                               name="dtb" placeholder="00.00.0000">
                                    </div>
                                </div>

                                {{--
                                    Жисмоний шахс, Юридик шахс
                                --}}

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="type">Қарздор шакли</label>
                                        <select class="form-select" name="type" id="type">
                                            <option value="0" @selected($data->client->type == 0)>Жисмоний шахс</option>
                                            <option value="1" @selected($data->client->type == 1)>Юридик шахс</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="date">Суғурта товони тўланган сана</label>
                                        <input type="date" class="form-control" id="date" value="{{$data->date}}"
                                               name="date" placeholder="Суғурта товони тўланган сана">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="amount">Суғурта товони суммаси</label>
                                        <input type="number" step="0.01" class="form-control" id="amount" value="{{$data->amount}}"
                                               name="amount" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="files">Шартнома учун файллар</label>
                                        <input type="file" name="files" class="form-control" id="files">
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-success">Saqlash</button>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
