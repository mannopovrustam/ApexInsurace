@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="user">
                    <div class="card-header d-flex justify-content-between">
                        <div>
                            Суғурта шартномаси <b>{{ $data->number }}</b>
                        </div>
                        <div class="d-flex">
                            <a href="/contracts/create" class="btn btn-primary mr-3">Янги шартнома</a>
                            <a href="/contracts/{{ $data->id }}/edit" class="btn btn-warning">Ўзгартириш</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="table">
                            <tbody>
                            <tr>
                                <th>Иш туркуми</th>
                                <td>{{ $data->category->name }}</td>
                            </tr>
                            <tr>
                                <th>Суғурта шартномаси</th>
                                <td>{{ $data->number }}</td>
                            </tr>
                            <tr>
                                <th>Қарздор ФИШ</th>
                                <td>{{ $data->client->fullname }}</td>
                            </tr>
                            <tr>
                                <th>Паспорт маълумотлари</th>
                                <td>{{ $data->client->passport }}</td>
                            </tr>
                            <tr>
                                <th>Қарздорнинг телефон рақами</th>
                                <td>{{ $data->client->phone }}</td>
                            </tr>
                            <tr>
                                <th>Манзили</th>
                                <td>{{ $data->client->address }}</td>
                            </tr>
                            <tr>
                                <th>Туғилган санаси</th>
                                <td>{{ \Carbon\Carbon::parse($data->client->dtb)->format('d.m.Y') }}</td>
                            </tr>
                            <tr>
                                <th>Қарздор шакли</th>
                                <td>{{ $data->client->type }}</td>
                            </tr>
                            <tr>
                                <th>Суғурта товони тўланган сана</th>
                                <td>{{ \Carbon\Carbon::parse($data->date)->format('d.m.Y') }}</td>
                            </tr>
                            <tr>
                                <th>Суғурта товони суммаси</th>
                                <td>{{ $data->amount }}</td>
                            </tr>
                            <tr>
                                <th>Ҳолати</th>
                                <td>{{ $data->status }}</td>
                            </tr>
                            <tr>
                                <th>Изоҳ</th>
                                <td>{{ $data->note }}</td>
                            </tr>
                            <tr>
                                <th>Яратилган сана</th>
                                <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d.m.Y') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
