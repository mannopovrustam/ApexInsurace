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

    <script>
        var table = null;

        $(document).ready(function () {
            $('#phy-table').DataTable();
            $('#legal-table').DataTable();

            $('#legal-table tbody').on('dblclick', 'tr', function() {
                updateSelected($(this).data('id'), $(this).data('name'), $(this).data('judge_id'));
            });
        });

        function updateSelected(id, name, judge_id) {
            var content = '', url = '', data = [];
            content =
                '<div class="form-group"><input type="hidden" id="id" class="form-control" value="'+id+'"></div>' +
                '<div class="form-group">' +
                '<select name="judge_id" id="judge" class="form-control">' +
                @foreach($judges as $j)
                    '<option value="{{ $j->id }}" ' + ((judge_id == {{ $j->id }}) ? 'selected':'') +'>{{ $j->name }}</option>' +
                @endforeach
                '</select>'+
                '</div>';

            $.confirm({
                title: name,
                content: content,
                buttons: {
                    confirm: {
                        text: 'Сақлаш',
                        action: function () {
                            var id = $('#id').val();
                            var judge = $('#judge').val();
                            if (judge == '') {
                                $.alert({
                                    icon: 'fa fa-info',
                                    closeIcon: true,
                                    type: 'red',
                                    title: '&nbsp;Ўзгартириш',
                                    content: '<br>Маълумотлар тўлиқ киритилмаган!',
                                    columnClass: 'small',
                                });
                                return false;
                            }

                            $.ajax({
                                url: '/dic/judges-update',
                                type: 'POST',
                                data: {data_id: id, judge_id: judge, type: 'legal_judge', _token: '{{ csrf_token() }}'},
                                success: function (data) {
                                    if (data.success) {
                                        $.alert({
                                            icon: 'fa fa-info',
                                            closeIcon: true,
                                            type: 'green',
                                            title: '&nbsp;Ўзгартириш',
                                            content: '<br>Маълумотлар муваффақиятли ўзгартирилди!',
                                            columnClass: 'small',
                                        });
                                    } else {
                                        $.alert({
                                            icon: 'fa fa-info',
                                            closeIcon: true,
                                            type: 'red',
                                            title: '&nbsp;Ўзгартириш',
                                            content: '<br>Маълумотлар ўзгартирилмади!',
                                            columnClass: 'small',
                                        });
                                    }
                                    return false;
                                }
                            })
                        }
                    },
                    cancel: {
                        text: 'Бекор қилиш',
                        action: function () {
                            // Handle cancel action
                        }
                    }
                }
            });

        }

    </script>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="user">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Судлар</h5>
                    </div>

                    <div class="card-body">
                        <form action="/dic/judges" method="post">
                            @csrf
                            <div class="d-flex justify-content-between align-items-end">
                                <div class="form-group">
                                    <label for="type">Суд тури</label>
                                    <select name="type" id="type" class="form-select">
                                        <option value="phy_judge" selected>Жисмоний шахс</option>
                                        <option value="legal_judge">Юридик шахс</option>
                                    </select>
                                </div>
                                <div class="form-group" style="width: 30rem;">
                                    <label for="district_id">Туман</label> <br>
                                    <select name="district_id[]" id="district_id"
                                            class="select2 form-control select2-multiple" multiple="multiple"
                                            data-placeholder="Choose ..." required autofocus>
                                        @foreach($districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}
                                                ---- {{ $district->region_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="judge_id">Судлар</label>
                                    <select name="judge_id" id="judge_id" class="form-select" required>
                                        <option value=""></option>
                                        @foreach($judges as $judge)
                                            <option
                                                value="{{ $judge->id }}" @selected(session()->get('judge_id') == $judge->id)>{{ $judge->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Қўшиш</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card" id="user">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Жисмоний шахс</h5>
                    </div>

                    <div class="card-body">
                        <table class="table" id="phy-table">
                            <thead>
                            <tr>
                                <th>Вилоят</th>
                                <th>Туман</th>
                                <th>Суд</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($phy_judge as $judge)
                                <tr data-id="{{ $judge->id }}" data-name="{{ $judge->judge_name }}" data-type="0"
                                    data-region="{{ $judge->region_id }}" role="row">
                                    <td>{{ $judge->region_name }}</td>
                                    <td>{{ $judge->district_name }}</td>
                                    <td>{{ $judge->judge_name }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card" id="user">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Юридик шахс</h5>
                    </div>

                    <div class="card-body">
                        <table class="table" id="legal-table">
                            <thead>
                            <tr>
                                <th>Вилоят</th>
                                <th>Туман</th>
                                <th>Суд</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($legal_judge as $judge)
                                <tr data-id="{{ $judge->id }}" data-name="{{ $judge->district_name }}" data-judge_id="{{ $judge->judge_id }}" role="row">
                                    <td>{{ $judge->region_name }}</td>
                                    <td>{{ $judge->district_name }}</td>
                                    <td>{{ $judge->judge_name }}</td>
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
