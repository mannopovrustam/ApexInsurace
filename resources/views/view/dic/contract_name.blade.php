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
                        <h5>Шартнома номи</h5>
                    </div>

                    <div class="card-body">
                        <table class="table" id="sms-table">
                            <thead>
                            <tr>
                                <th>Мижоз ID</th>
                                <th>Шартнома ID</th>
                                <th>Шартнома номи</th>
                                <th>Шартнома EmitID</th>
                                <th>Ҳаракат</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($sms as $s)
                                <tr data-id="{{ $s->contractId }}" data-name="{{ $s->contract_name }}" role="row">
                                    <td>{{ $s->client_id }}</td>
                                    <td>{{ $s->contract_id }}</td>
                                    <td>{{ $s->contract_name }}</td>
                                    <td>{{ $s->contractId }}</td>
                                    <td class="d-flex">
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#edit{{ $s->contractId }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <div class="modal fade" id="edit{{ $s->contractId }}" role="dialog"
                                             aria-labelledby="edit{{ $s->contractId }}">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="edit{{ $s->contractId }}">
                                                            {{ $s->contract_name }} ҳақида маълумот ўзгартириш
                                                        </h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close"><span
                                                                aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="/dic/contract-name" method="post">
                                                            @csrf
                                                            <div class="form-group">
                                                                <label for="client_id">Мижоз ID</label>
                                                                <input type="text" name="client_id" id="client_id"
                                                                       class="form-control" value="{{ $s->client_id }}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="contract_id">Шартнома ID</label>
                                                                <input type="text" name="contract_id" id="contract_id"
                                                                       class="form-control" value="{{ $s->contract_id }}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="contract_name">Шартнома номи</label>
                                                                <input type="text" name="contract_name" id="contract_name"
                                                                       class="form-control" value="{{ $s->contract_name }}" readonly>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="contractId">Шартнома EmitID</label>
                                                                <input type="text" name="contractId" id="contractId"
                                                                       class="form-control" value="{{ $s->contractId }}" readonly>
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
