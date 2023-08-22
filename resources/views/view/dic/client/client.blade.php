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
        $(document).ready(function () {

            table = $('#petitions-table').DataTable({
                processing: true,
                scrollY: "55vh",
                serverSide: true,
                lengthMenu: [[25, 50, 100, 250], [25, 50, 100, 250]],
                rowId: 'id',
                select: {style: 'os', selector: 'td:first-child'},
                ajax: '/data/client',

                columns: [
                    {data: 'fullname'},
                    {data: 'passport'},
                    {data: 'pinfl'},
                    {data: 'address'},
                    {data: 'phone'},
                    {data: 'created_at'},
                ]
            });

            $('#petitions-table tbody').on('click', 'td', function () {
                var cell = table.cell(this);
                if (cell.index().column != 1)
                    $(this).closest('tr').toggleClass('selected');
            });

            $('#petitions-table tbody').on('dblclick', 'tr', function () {
                window.location.href = '/dic/client-create/' + $(this).attr('id');
            });
        });

        function updateSelected() {
            var selected = table.rows('.selected').data();
            if (selected.length == 0) {
                $.alert('Мижоз танланмади!');
                return;
            }
            if (selected.length > 1) {
                $.alert('Битта мижозни танланг!');
                return;
            }
            window.location.href = '/dic/client-create/' + selected[0].id;
        }

        function deleteSelected() {
            var selected = table.rows('.selected').data();
            if (selected.length == 0) {
                $.alert('Мижоз танланмади!');
                return;
            }
            var rows = table.rows('.selected').data('id').toArray();

            var ids = rows.map(function (row) {
                return row.id;
            });

            console.log(ids);
            $.confirm({
                icon: 'fa fa-question-circle',
                animation: 'zoom',
                closeAnimation: 'opacity',
                closeIcon: true,
                type: 'red',
                title: '&nbsp;Ишончингиз комилми?',
                content: '<br> Мижоз ўчирилганда унинг барча маълумотлари ҳам ўчирилади!',
                buttons: {
                    ok: {
                        text: 'Удалить',
                        btnClass: 'btn-danger',
                        action: function () {
                            $.ajax({
                                url: '/dic/client-delete',
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    ids: ids
                                },
                                success: function (data) {
                                    if (data.success) {
                                        $.alert('Мижоз ўчирилди!');
                                        table.ajax.reload();
                                    } else {
                                        $.alert('Мижоз ўчирилмади!');
                                    }
                                }
                            });
                        }
                    },
                    cancel: function () {
                        //
                    }
                }
            });
        }

/*
        function deleteSelected() {
            if (!getSelected()) return false;

            var id = table.rows('.selected').data()[0].id;

            $.confirm({
                icon: 'fa fa-question-circle',
                animation: 'zoom',
                closeAnimation: 'opacity',
                closeIcon: true,
                type: 'red',
                title: '&nbsp;Ишончингиз комилми?',
                content: '<br> <b>Ўчирилган маълумотлар қайта тикланмайди!</b>' + id,
                buttons: {
                    ok: {
                        text: 'Удалить',
                        btnClass: 'btn-danger',
                        action: function () {
                            $.ajax({
                                url: '/petitions/' + id,
                                type: 'DELETE',
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                },
                                success: function (data) {
                                    table.ajax.reload();
                                }
                            });
                        }
                    },
                    cancel: {
                        text: 'Отмена',
                        btnClass: 'btn-default',
                    }
                }
            });
        }
*/


    </script>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="user">
                    <div class="card-header d-flex justify-content-between">
                        <div>Мижозлар</div>
                        <div class="d-flex">
                            <a class="btn btn-warning mr-3" onclick="updateSelected()">Ўзгартириш</a>
                            <a class="btn btn-danger" onclick="deleteSelected()">Ўчириш</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="table" id="petitions-table">
                            <thead>
                            <tr>
                                <th>ФИШ</th>
                                <th>Паспорт</th>
                                <th>ПИНФЛ</th>
                                <th>Манзили</th>
                                <th>Телефон рақами</th>
                                <th>Яратилган сана</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
