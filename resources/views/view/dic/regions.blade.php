@extends('layouts.app')


@section('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css" rel="stylesheet"
@endsection

@section('script')

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="{{ asset('/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

    <script>
        var table = null;

        $(document).ready(function() {
            table = $('#region-table').DataTable();

            $('#region-table tbody').on('dblclick', 'tr', function() {
                updateSelected($(this).data('id'), $(this).data('name'), $(this).data('type'), $(this).data('region'));
            });

        });
        $(document).ready(function() {
            table = $('#district-table').DataTable();

            $('#district-table tbody').on('dblclick', 'tr', function() {
                updateSelected($(this).data('id'), $(this).data('name'), $(this).data('type'), $(this).data('region'));
            });

        });


        function getSelected(){
            var total = table.rows('.selected').count();

            if (total == 0) {
                $.alert({
                    icon: 'fa fa-info',
                    closeIcon: true,
                    type: 'red',
                    title: '&nbsp;Не выбран гость!',
                    content: '<br>Сначало выберите гостя щелкнув левой кнопкой мыши по строке!',
                    columnClass: 'small',
                });
                return false;
            }
            return true;
        }

        function updateSelected(id, name, type, region) {
            var content = '', url = '', data = [];
            if(type) {
                content = '<form id="myForm"><div class="form-group"><input type="hidden" id="id" class="form-control" value="'+id+'"></div><div class="form-group"><label for="name">Name:</label><input type="text" id="name" class="form-control" value="'+name+'"></div></form>';
                url = '/dic/region';
            }else{
                content = '<form id="myForm">' +
                    '<div class="form-group"><input type="hidden" id="id" class="form-control" value="'+id+'"></div>' +
                    '<div class="form-group">' +
                    '<select name="region_id" id="region_id" class="form-control">' +
                    @foreach($regions as $region)
                        '<option value="{{ $region->id }}" ' + ((region == {{ $region->id }}) ? 'selected':'') +'>{{ $region->name }}</option>' +
                    @endforeach
                    '</select>'+
                    '</div>' +
                    '<div class="form-group"><label for="name">Name:</label><input type="text" id="name" class="form-control" value="'+name+'"></div>' +
                    '</form>';
                url = '/dic/district';
            }
            $.confirm({
                title: 'Ўзгартириш',
                content: content,
                buttons: {
                    confirm: {
                        text: 'Сақлаш',
                        action: function () {
                            var id = $('#id').val();
                            var name = $('#name').val();
                            var region_id = $('#region_id').val();
                            if (name == '') {
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
                            if(type == 1) {
                                data = {data_id: id,name: name,_token: '{{ csrf_token() }}'};
                            }else{
                                data = {data_id:id, region_id:region_id, name:name, _token:'{{ csrf_token() }}'};
                            }

                            $.ajax({
                                url: url,
                                type: 'POST',
                                data: data,
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
                                        location.reload();
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


        $('#region-table').on('processing.dt', function(e, settings, processing) {
            if (processing)
                $('.ajaxLoading').show();
            else
                $('.ajaxLoading').hide();
        })



    </script>
@endsection


@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card" id="user">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Вилоятлар</h5>
                    </div>

                    <div class="card-body">
                        <table class="table" id="region-table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Номи</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($regions as $region)
                                    <tr data-id="{{ $region->id }}" data-name="{{ $region->name }}" data-type="1" role="row">
                                        <td>{{ $region->id }}</td>
                                        <td>{{ $region->name }}</td>
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
                        <h5>Туманлар</h5>
                    </div>


                    <div class="card-body">
                        <table class="table" id="district-table">
                            <thead>
                            <tr>
                                <th>Вилоят</th>
                                <th>ID</th>
                                <th>Туман</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($districts as $district)
                                <tr data-id="{{ $district->id }}" data-name="{{ $district->name }}" data-type="0" data-region="{{ $district->region_id }}" role="row">
                                    <td>{{ $district->region_name }}</td>
                                    <td>{{ $district->id }}</td>
                                    <td>{{ $district->name }}</td>
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
