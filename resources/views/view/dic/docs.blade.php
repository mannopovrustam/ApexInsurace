@extends('layouts.app')


@section('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css" rel="stylesheet"
          type="text/css"/>
    <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('script')

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="{{ asset('/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

    <script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/ecommerce-add-product.init.js') }}"></script>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="user">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Word шаблон</h5>
                        <button type="button" class="text-muted" data-bs-toggle="modal"
                                data-bs-target="#variables">Ўзгарувчилар
                        </button>
                        <div class="modal fade" id="variables" role="dialog"
                             aria-labelledby="variables">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">
                                            Қуйидаги ўзгарувчиларни ишлатинг:
                                        </h4>
                                        <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close"><span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table table-sm table-hover table-striped">

                                            <tr>
                                                <th>ID</th>
                                                <td>$contract_id</td>
                                            </tr>
                                            <tr>
                                                <th>Иш туркуми(Bank)</th>
                                                <td>$category_name</td>
                                            </tr>
                                            <tr>
                                                <th>Шартнома рақами</th>
                                                <td>$number</td>
                                            </tr>
                                            <tr>
                                                <th>Шартнома номи</th>
                                                <td>$contract_name</td>
                                            </tr>
                                            <tr>
                                                <th>Шартнома санаси</th>
                                                <td>$contract_date</td>
                                            </tr>
                                            <tr>
                                                <th>Қарздор ФИШ</th>
                                                <td>$fullname</td>
                                            </tr>
                                            <tr>
                                                <th>Паспорт маълумотлари</th>
                                                <td>$passport</td>
                                            </tr>
                                            <tr>
                                                <th>ПИНФЛ</th>
                                                <td>$pinfl</td>
                                            </tr>
                                            <tr>
                                                <th>Қарздорнинг телефон рақами</th>
                                                <td>$phone</td>
                                            </tr>
                                            <tr>
                                                <th>Манзили</th>
                                                <td>$address</td>
                                            </tr>
                                            <tr>
                                                <th>Туғилган санаси</th>
                                                <td>$dtb</td>
                                            </tr>
                                            <tr>
                                                <th>Қарздор шакли</th>
                                                <td>$type</td>
                                            </tr>
                                            <tr>
                                                <th>Суғурта товони тўланган сана</th>
                                                <td>$date_payment</td>
                                            </tr>
                                            <tr>
                                                <th>Суғурта товони суммаси</th>
                                                <td>$amount_sum</td>
                                            </tr>
                                            <tr>
                                                <th>Суғурта товони тўланган суммаси</th>
                                                <td>$amount_paid</td>
                                            </tr>
                                            <tr>
                                                <th>Суғурта товон қолдиғи</th>
                                                <td>$residue</td>
                                            </tr>
                                            <tr>
                                                <th>Ҳолати</th>
                                                <td>$status</td>
                                            </tr>
                                            <tr>
                                                <th>Изоҳ</th>
                                                <td>$contract_note</td>
                                            </tr>
                                            <tr>
                                                <th>Яратилган сана</th>
                                                <td>$created_at</td>
                                            </tr>
                                            <tr>
                                                <th>Давлат божи</th>
                                                <td>$tax</td>
                                            </tr>
                                            <tr>
                                                <th>Почта харажати</th>
                                                <td>$expense</td>
                                            </tr>


                                            <tr>
                                                <th>Суд номи</th>
                                                <td>$judge_name</td>
                                            </tr>
                                            <tr>
                                                <th>Суд №</th>
                                                <td>$judge_no</td>
                                            </tr>
                                            <tr>
                                                <th>Суд Натижа</th>
                                                <td>$judge_result</td>
                                            </tr>
                                            <tr>
                                                <th>Суд Изоҳ</th>
                                                <td>$judge_note</td>
                                            </tr>

                                            <tr>
                                                <th>МИБ номи</th>
                                                <td>$mib_name</td>
                                            </tr>
                                            <tr>
                                                <th>МИБ №</th>
                                                <td>$mib_no</td>
                                            </tr>
                                            <tr>
                                                <th>МИБ Натижа</th>
                                                <td>$mib_result</td>
                                            </tr>
                                            <tr>
                                                <th>МИБ Изоҳ</th>
                                                <td>$mib_note</td>
                                            </tr>
                                            <tr>
                                                <th>Ҳозирги вақт</th>
                                                <td>$curdate</td>
                                            </tr>

                                            <tr>
                                                <th>Ҳодим исми</th>
                                                <td>$admin_name</td>
                                            </tr>
                                            <tr>
                                                <th>Ҳодим телефони</th>
                                                <td>$admin_phone</td>
                                            </tr>
                                            <tr>
                                                <th>ИНН</th>
                                                <td>$inn</td>
                                            </tr>
                                            <tr>
                                                <th>МФО</th>
                                                <td>$mfo</td>
                                            </tr>
                                            <tr>
                                                <th>Ҳисоб рақам</th>
                                                <td>$account_number</td>
                                            </tr>
                                            <tr>
                                                <th>Банк номи</th>
                                                <td>$bank_name</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="/dic/docs" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="name">Номи</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="template">Шаблон <span class="text-muted font-size-12"
                                                                  style="font-weight: 100">Lorem ipsum <b>${variable}</b> dolor sit amet!</span></label>
                                <input type="file" name="template" id="template" class="form-control">
                            </div>
                            <br>
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary">Қўшиш</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card" id="user">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Word шаблон</h5>
                    </div>

                    <div class="card-body">
                        <table class="table" id="sms-table">
                            <thead>
                            <tr>
                                <th>Номи</th>
                                <th>Шаблон</th>
                                <th>Ҳаракат</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($docs as $d)
                                <tr data-id="{{ $d->id }}" data-name="{{ $d->name }}" role="row">
                                    <td>{{ $d->name }}</td>
                                    <td><a href="{{ $d->template }}" download=""><i class="fa fa-download"></i></a></td>
                                    <td class="d-flex">
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#edit{{ $d->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <div class="modal fade" id="edit{{ $d->id }}" role="dialog"
                                             aria-labelledby="edit{{ $d->id }}">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="edit{{ $d->id }}">
                                                            {{ $d->name }} ҳақида маълумот ўзгартириш
                                                        </h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close"><span
                                                                aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="/dic/docs" method="post" enctype="multipart/form-data">
                                                            <input type="hidden" name="id" value="{{ $d->id }}">
                                                            @csrf
                                                            <div class="form-group">
                                                                <label for="name">Номи</label>
                                                                <input type="text" name="name" id="name"
                                                                       class="form-control" value="{{ $d->name }}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="content">Шаблон <span
                                                                        class="text-muted font-size-12"
                                                                        style="font-weight: 100">Lorem ipsum ${dolor} sit amet!</span></label>
                                                                <input type="file" name="template" id="template" class="form-control">
                                                            </div>
                                                            <br>
                                                            <div class="form-group text-right">
                                                                <button type="submit" class="btn btn-primary">Сақлаш
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> &nbsp;
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#delete{{ $d->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        {{--delete modal--}}
                                        <div class="modal fade" id="delete{{ $d->id }}" role="dialog"
                                             aria-labelledby="delete{{ $d->id }}">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="edit{{ $d->id }}">
                                                            {{ $d->name }} ҳақида маълумот ўчириш
                                                        </h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close"><span
                                                                aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        {!! Form::open(['method' => 'DELETE','url' => '/dic/docs/'.$d->id,'style'=>'display:inline']) !!}
                                                        {!! Form::submit('Delete', ['class' => 'btn btn-outline-danger']) !!}
                                                        {!! Form::close() !!}
                                                        <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
