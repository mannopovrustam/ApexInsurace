@extends('layouts.app')

@section('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css" rel="stylesheet" type="text/css" />
@endsection

@section('script')

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="{{ asset('/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

    <script>
        var table = null;


        $(document).ready(function() {

            table = $('#petitions-table').DataTable({
                processing: true,
                scrollY:"55vh",
                serverSide: true,
                lengthMenu: [[25, 50, 100, 250], [25, 50, 100, 250]],
                rowId: 'id',
                select: {style:'os',selector: 'td:first-child'},
                ajax: '/data/petitions',

                columns: [
                    { data: 'type', name: 'type' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'fullname', name: 'fullname' },
                    { data: 'phone', name: 'phone' },
                    { data: 'number', name: 'number' },
                    {
                        data: 'file',
                        render: function(data, type, row) {
                            return '<a href="'+data.file+'" target="_blank" download>Download</a>';
                        }
                    },

                ]
            });

            $('#petitions-table tbody').on('click', 'td', function() {
                var cell = table.cell(this);
                if (cell.index().column !=1)
                    $(this).closest('tr').toggleClass('selected');
            });

            $('#petitions-table tbody').on('dblclick', 'tr', function() {
               window.location.href = '/contracts/' + $(this).attr('contract_id') +'#petition-'+this.id;
            });
        });


        function getSelected(){
            var total = table.rows('.selected').count();

            if (total == 0) {
                $.alert({
                    icon: 'fa fa-info',
                    animation: 'rotate',
                    closeAnimation: 'rotate',
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

        function updateSelected() {
            if (!getSelected()) return false;
            var id = table.rows('.selected').data()[0].id;
            var contract_id = table.rows('.selected').data()[0].contract_id;

            window.location.href = '/contracts/' + contract_id+'#petition-'+id;
        }

        function deleteSelected(){
            if (!getSelected()) return false;

            var id = table.rows('.selected').data()[0].id;

            $.confirm({
                icon: 'fa fa-question-circle',
                animation: 'zoom',
                closeAnimation: 'opacity',
                closeIcon: true,
                type: 'red',
                title: '&nbsp;Ишончингиз комилми?',
                content: '<br> <b>Ўчирилган маълумотлар қайта тикланмайди!</b>'+id,
                buttons: {
                    ok: {
                        text: 'Удалить',
                        btnClass: 'btn-danger',
                        action: function() {
                            $.ajax({
                                url: '/petitions/' + id,
                                type: 'DELETE',
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                },
                                success: function(data) {
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

        $('#petitions-table').on('processing.dt', function(e, settings, processing) {
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
            <div class="col-md-12">
                <div class="card" id="user">
                    <div class="card-header d-flex justify-content-between">
                        <div>
                            Регистрациялар
                        </div>
                        <div class="d-flex">
                            <a class="btn btn-warning mr-3" onclick="updateSelected()">Ўзгартириш</a>
                            <a class="btn btn-danger" onclick="deleteSelected()">Ўчириш</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="table" id="petitions-table">
                            <thead>
                                <tr>
                                    <th>Қарздор ФИШ</th>
                                    <th>Паспорт</th>
                                    <th>ПИНФЛ</th>
                                    <th>Манзили</th>
                                    <th>Телефон рақами</th>
                                    <th>Суғурта товони суммаси</th>
                                    <th>Суғурта товони тўланган сана</th>
                                    <th>Ариза</th>
                                    <th>Ариза</th>
                                    <th>Суғурта шартномаси</th>
                                    <th>Яратилган сана</th>
                                    <th>Қарздорнинг телефон рақами</th>
                                    <th>Файл</th>
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
