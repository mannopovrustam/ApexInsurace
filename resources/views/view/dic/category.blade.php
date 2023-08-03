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
                        <h5>Иш туркуми</h5>
                    </div>

                    <div class="card-body">
                        <form action="/dic/category" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="category_id">Тегишли</label>
                                <select name="category_id" id="category_id" class="form-select">
                                    <option value="">***Тегишли эмас***</option>
                                    @foreach(\App\Models\Category::all() as $c)
                                        <option value="{{$c->id}}">{{$c->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="name">Номи</label>
                                <input type="text" name="name" id="name" class="form-control" required>
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
                        <h5>Иш туркуми</h5>
                    </div>

                    <div class="card-body">
                        <table class="table" id="sms-table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Номи</th>
                                <th>Ҳаракат</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($category as $s)
                                <tr data-id="{{ $s->id }}" data-name="{{ $s->name }}" role="row">
                                    <td>{{ $s->id }}</td>
                                    <td>{{ $s->name }}</td>
                                    <td class="d-flex">
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#edit{{ $s->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <div class="modal fade" id="edit{{ $s->id }}" role="dialog"
                                             aria-labelledby="edit{{ $s->id }}">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="edit{{ $s->id }}">
                                                            {{ $s->name }} ҳақида маълумот ўзгартириш
                                                        </h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close"><span
                                                                aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="/dic/category" method="post">
                                                            <input type="hidden" name="id" value="{{ $s->id }}">
                                                            @csrf
                                                            <div class="form-group">
                                                                <label for="category_id">Тегишли</label>
                                                                <select name="category_id" id="category_id" class="form-select">
                                                                    <option value="" @selected($s->category_id == '')>***Тегишли эмас***</option>
                                                                    @foreach(\App\Models\Category::all() as $c)
                                                                        <option value="{{$c->id}}" @selected($c->id == $s->category_id)>{{$c->name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="name">Номи</label>
                                                                <input type="text" name="name" id="name"
                                                                       class="form-control" value="{{ $s->name }}">
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
                                                data-bs-target="#delete{{ $s->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        {{--delete modal--}}
                                        <div class="modal fade" id="delete{{ $s->id }}" role="dialog"
                                             aria-labelledby="delete{{ $s->id }}">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="edit{{ $s->id }}">
                                                            {{ $s->name }} ҳақида маълумот ўчириш
                                                        </h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close"><span
                                                                aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        {!! Form::open(['method' => 'DELETE','url' => '/dic/sms/'.$s->id,'style'=>'display:inline']) !!}
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
