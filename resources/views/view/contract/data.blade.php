<style>
    .viewed {
        background-color: #2f3e66 !important;
        color: #ffffff !important;
    }
</style>
<script>

    var table = null;
    var budget = null;
    var stopLoop = false;


    var start_ins = moment().subtract(7, 'years').startOf('year');
    var end_ins = moment().add(1, 'days');
    var start_created = moment().subtract(7, 'years').startOf('year');
    var end_created = moment().add(1, 'days');

    $(document).ready(function () {

        dataRange('rgsrange', start_ins, end_ins);
        start_ins = $('#rgsrange').data('daterangepicker').startDate.format('YYYY-MM-DD');
        end_ins = $('#rgsrange').data('daterangepicker').endDate.format('YYYY-MM-DD');

        dataRange('rgsrange1', start_created, end_created);
        start_created = $('#rgsrange1').data('daterangepicker').startDate.format('YYYY-MM-DD');
        end_created = $('#rgsrange1').data('daterangepicker').endDate.format('YYYY-MM-DD');


        table = $('#contracts-table').DataTable({
            processing: true,
            scrollY: "55vh",
            serverSide: true,
            lengthMenu: [[10, 25, 50, 100, 250], [10, 25, 50, 100, 250]],
            rowId: 'id',
            columnDefs: [
                {orderable: false, targets: 0}, // Disable ordering for columns 2 and 4
                {searchable: false, targets: 0} // Disable searching for columns 3 and 5
            ],
            order: [[1, 'asc']],
            select: {style: 'os', selector: 'td:first-child'},
            ajax: {
                url: "/data/contracts",
                type: "GET",
                data: function (d) {
                    // Retrieve the updated date range values
                    var start_ins = $('#rgsrange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end_ins = $('#rgsrange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    var start_created = $('#rgsrange1').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end_created = $('#rgsrange1').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    var start_amount = $('#start_amount').val();
                    var end_amount = $('#end_amount').val();

                    // Set the date range values in the AJAX request data
                    d.start_ins = start_ins;
                    d.end_ins = end_ins;
                    d.start_created = start_created;
                    d.end_created = end_created;
                    d.start_amount = start_amount;
                    d.end_amount = end_amount;
                },
            },

            columns: [
                {data: 'check'},
                {data: 'category_name', name: 'categories.name'},
                {data: 'number', name: 'number'},
                {data: 'date_payment', name: 'date_payment'},
                {data: 'fullname', name: 'clients.fullname'},
                {data: 'amount', name: 'amount'},
                {data: 'residue', name: 'residue'},
                {data: 'status', name: 'status'},
                {data: 'created_at', name: 'created_at'},
                {data: 'phone', name: 'clients.phone'},
                {data: 'api', name: 'api'},
                {data: 'passport', name: 'clients.passport', visible: false},
                {data: 'pinfl', name: 'clients.pinfl', visible: false}
            ]
        });

    });

    $('#contracts-table').on('processing.dt', function (e, settings, processing) {
        if (processing)
            $('.ajaxLoading').show();
        else
            $('.ajaxLoading').hide();
    });
    $('#check_all').on('change', function () {
        $('.viewed').removeClass('viewed');
        if ($('#check_all').prop('checked')) $('#contracts-table tbody tr').addClass('selected');
        else $('#contracts-table tbody tr').removeClass('selected');
    });
    $('#contracts-table tbody').on('click', 'td', function () {
        $('.viewed').removeClass('viewed');
        var cell = table.cell(this);
        if (cell.index().column != 1)
            $(this).closest('tr').toggleClass('selected');
    });

    function reloadTable() {

        start_ins = $('#rgsrange').data('daterangepicker').startDate.format('YYYY-MM-DD');
        end_ins = $('#rgsrange').data('daterangepicker').endDate.format('YYYY-MM-DD');

        start_created = $('#rgsrange1').data('daterangepicker').startDate.format('YYYY-MM-DD');
        end_created = $('#rgsrange1').data('daterangepicker').endDate.format('YYYY-MM-DD');

        min_sum = $('#min_sum').val();
        max_sum = $('#max_sum').val();

        table.ajax.reload();
    }

    function getSelected() {
        var total = table.rows('.selected').count();

        if (total == 0) {
            $.alert({
                icon: 'fa fa-info',
                closeIcon: true,
                type: 'red',
                title: '&nbsp;Ҳужжат танланмади!',
                content: '<br>Aввал сичқончанинг чап тугмаси билан чизиқни босиш орқали ҳужжатни танланг!',
                columnClass: 'small',
            });
            return false;
        }
        return true;
    }

    function updateSelected() {
        if (!getSelected()) return false;

        var id = table.rows('.selected').data()[0].id;
        window.location.href = '/contracts/' + id + '/edit';
    }
    @can('СМС юбориш')
    function sendSMS() {
        if (!getSelected()) return false;
        // get selected ids
        var rows = table.rows('.selected').data('id').toArray();
        // get ids from rows
        var ids = rows.map(function (row) {
            return row.id;
        });
        var count = ids.length;

        // send ajax request
        $.confirm({
            title: 'Юбориш',
            content: '<div class="form-group"><label>СМС шаблон</label>' +
                '<select class="form-control" id="sms_template" required>' +
                '<option value="">Шаблон танланг</option>' +
                @foreach($sms_templates as $sms_template)
                    '<option value="{{$sms_template->id}}">{{$sms_template->name}}</option>' +
                @endforeach
                    '</select>' +
                '</div>',
            buttons: {
                confirm: {
                    text: 'Тасдиқлаш',
                    btnClass: 'btn-blue',
                    action: function () {
                        if ($('#sms_template').val() == '') {
                            $.alert({
                                icon: 'fa fa-info',
                                closeIcon: true,
                                type: 'red',
                                title: '&nbsp;Юборилмади!',
                                content: '<br>СМС шаблон танланмади!',
                                columnClass: 'small',
                            });
                            return false;
                        }

                        $.ajax({
                            url: '/contracts/group-sms',
                            type: 'GET',
                            data: {
                                template_id: $('#sms_template').val(),
                                ids: ids,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                $.alert({
                                    icon: 'fa fa-info',
                                    closeIcon: true,
                                    type: 'green',
                                    title: '&nbsp;Юборилди!',
                                    content: '<br>Ҳужжатлар СМС орқали юборилди!',
                                    columnClass: 'small',
                                });
                            },
                            error: function (response) {
                                $.alert({
                                    icon: 'fa fa-info',
                                    closeIcon: true,
                                    type: 'red',
                                    title: '&nbsp;Юборилмади!',
                                    content: '<br>Ҳужжатлар юборилмади!',
                                    columnClass: 'small',
                                });
                            }
                        });
                    }
                },
                cancel: {
                    text: 'Бекор қилиш',
                    btnClass: 'btn-red',
                    action: function () {
                    }
                }
            }
        })

    }
    @endcan
    @can('Почта юбориш')
    function sendEmail() {
        if (!getSelected()) return false;
        // get selected ids
        var rows = table.rows('.selected').data('id').toArray();
        // get ids from rows
        var ids = rows.map(function (row) {
            return row.id;
        });
        var count = ids.length;

        // send ajax request
        $.confirm({
            title: 'Юбориш',
            content: count + ' та ҳужжатни почта орқали юборишни тасдиқлаш.',
            buttons: {
                confirm: {
                    text: 'Тасдиқлаш',
                    btnClass: 'btn-blue',
                    action: function () {
                        $.ajax({
                            url: '/contracts/group-email',
                            type: 'GET',
                            data: {
                                ids: ids,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                $.alert({
                                    icon: 'fa fa-info',
                                    closeIcon: true,
                                    type: 'green',
                                    title: '&nbsp;Юборилди!',
                                    content: '<br>Ҳужжатлар почта орқали юборилди!',
                                    columnClass: 'small',
                                });
                            },
                            error: function (response) {
                                $.alert({
                                    icon: 'fa fa-info',
                                    closeIcon: true,
                                    type: 'red',
                                    title: '&nbsp;Юборилмади!',
                                    content: '<br>Ҳужжатлар юборилмади!',
                                    columnClass: 'small',
                                });
                            }
                        });
                    }
                },
                cancel: {
                    text: 'Бекор қилиш',
                    btnClass: 'btn-red',
                    action: function () {
                    }
                }
            }
        })

    }
    @endcan
    @can('Ҳужжатларни ўчириш')
    function deleteSelected() {
        if (!getSelected()) return false;
        // get selected ids
        var rows = table.rows('.selected').data('id').toArray();
        // get ids from rows
        var ids = rows.map(function (row) {
            return row.id;
        });
        var count = ids.length;

        // send ajax request
        $.confirm({
            title: 'Ўчириш',
            content: count + ' та ҳужжатни ўчиришни тасдиқлаш.',
            buttons: {
                confirm: {
                    text: 'Тасдиқлаш',
                    btnClass: 'btn-blue',
                    action: function () {
                        $.ajax({
                            url: '/contracts/group-delete',
                            type: 'POST',
                            data: {
                                ids: ids,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                $.alert({
                                    icon: 'fa fa-info',
                                    closeIcon: true,
                                    type: 'green',
                                    title: '&nbsp;Ўчирилди!',
                                    content: '<br>Ҳужжатлар ўчирилди!',
                                    columnClass: 'small',
                                });
                                table.ajax.reload();
                            },
                            error: function (response) {
                                $.alert({
                                    icon: 'fa fa-info',
                                    closeIcon: true,
                                    type: 'red',
                                    title: '&nbsp;Ўчирилмади!',
                                    content: '<br>Ҳужжатлар ўчирилмади!',
                                    columnClass: 'small',
                                });
                            }
                        });
                    }
                },
                cancel: {
                    text: 'Бекор қилиш',
                    btnClass: 'btn-red',
                    action: function () {
                    }
                }
            }
        })


    }
    @endcan

    function exportExcel() {
        if (!getSelected()) return false;
        // get selected ids
        var rows = table.rows('.selected').data('id').toArray();
        // get ids from rows
        var ids = rows.map(function (row) {
            return row.id;
        });
        var count = ids.length;

        // send ajax request
        $.confirm({
            title: 'Экспорт',
            content: count + ' та ҳужжатни экспорт қилишни тасдиқлаш.',
            buttons: {
                confirm: {
                    text: 'Тасдиқлаш',
                    btnClass: 'btn-blue',
                    action: function () {
                        // ids to query string
                        var downloadUrl = '/contracts/group-excel?'+$.param({ids: ids});
                        console.log(downloadUrl);
                        var link = document.createElement('a');
                        link.href = downloadUrl;
                        link.target = '_blank';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);


                        $.ajax({
                            url: '/contracts/group-excel',
                            type: 'GET',
                            data: {
                                ids: ids,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                $.alert({
                                    icon: 'fa fa-info',
                                    closeIcon: true,
                                    type: 'green',
                                    title: '&nbsp;Экспорт қилинди!',
                                    content: '<br>Ҳужжатлар экспорт қилинди!',
                                    columnClass: 'small',
                                });
                                table.ajax.reload();
                            },
                            error: function (response) {
                                $.alert({
                                    icon: 'fa fa-info',
                                    closeIcon: true,
                                    type: 'red',
                                    title: '&nbsp;Экспорт қилинмади!',
                                    content: '<br>Ҳужжатлар экспорт қилинмади',
                                    columnClass: 'small',
                                });
                            }
                        });
                    }
                },
                cancel: {
                    text: 'Бекор қилиш',
                    btnClass: 'btn-red',
                    action: function () {
                    }
                }
            }
        })


    }

    @can('Файлни импорт қилиш')
    function importExcel() {
        $.confirm({
            'title': 'Excel файлдан импорт',
            content: '<div class="form-group">' +
                '<label>Файл юкланг</label>' +
                '<a href="/petition/Import-example.xlsx" download="" style="float: right; color: #1a202c">Намуна</a>' +
                '<input type="file" class="form-control" id="file_import"></div>',
            buttons: {
                formSubmit: {
                    text: 'Юклаш',
                    btnClass: 'btn-green',
                    action: function () {
                        var file = this.$content.find('#file_import')[0].files[0];
                        if (!file) {
                            $.alert('Файл танланмади');
                            return false;
                        }
                        var formData = new FormData();
                        formData.append('file', file);
                        formData.append('_token', "{{ csrf_token() }}");
                        $.ajax({
                            url: "{{ asset('contracts/import-data') }}",
                            data: formData,
                            method: "POST",
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                if (data.status == 'error') {
                                    $.alert({
                                        title: 'Хато',
                                        content: data.message,
                                        type: data.color,
                                        buttons: {ok: {text: 'OK', btnClass: 'btn-red'}}
                                    });
                                } else {
                                    $.alert({
                                        title: 'Юборилди',
                                        content: data.message,
                                        type: data.color,
                                        buttons: {ok: {text: 'OK', btnClass: 'btn-green'}}
                                    });
                                    table.ajax.reload();
                                }
                            }
                        })
                    }
                },
                cancel: {
                    text: 'Бекор қилиш'
                }
            }
        })
    }
    @endcan

    $('#contracts-table tbody').on('dblclick', 'tr', function () {
        $('.viewed').removeClass('viewed');
        $(this).toggleClass('viewed');
        $('#contract-index').hide();
        $('#contract-show').show();
        $('#contract-show').load('/contracts/' + this.id);
    });

    // Function to format the number with commas as thousands separators
    function formatNumberWithCommas(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Event handler for input change
    $('.priceInput').on('keyup', function() {
        // Get the input value and remove any existing commas
        let inputVal = $(this).val().replace(/,/g, '');

        console.log(inputVal);
        // Convert the input value to a number
        let number = parseFloat(inputVal);

        // Check if the conversion is successful (not NaN)
        if (!isNaN(number)) {
            // Format the number with commas
            let formattedNumber = formatNumberWithCommas(number);

            // Set the formatted value back to the input field
            $(this).val(formattedNumber);
        }
    });


</script>


<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card" id="user">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        Регистрациялар
                    </div>
                    <div class="col-lg-6">
                        <button type="button" class="btn-sm btn-link waves-effect waves-light ml-2"
                                data-bs-toggle="modal" data-bs-target="#myModal"><i class="fa fa-filter"></i>
                        </button>
                        <!-- sample modal content -->
                        <div id="myModal" class="modal fade" tabindex="-1" role="dialog"
                             aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="myModalLabel">Сана бўйича фильтр</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close">
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="d-flex justify-content-between">
                                            <div class="form-group">
                                                <p>Суғурта товони тўланган сана</p>
                                                <div id="rgsrange" class="form-control"
                                                     style="margin-top: 5px;background: #fff; cursor: pointer; border: 1px solid #55acee;display:inline!important;">
                                                    <i class="fa fa-calendar"></i>&nbsp;<span></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <p>Яратилган сана</p>
                                                <div id="rgsrange1" class="form-control"
                                                     style="margin-top: 5px;background: #fff; cursor: pointer; border: 1px solid #55acee;display:inline!important;">
                                                    <i class="fa fa-calendar"></i>&nbsp;<span></span>
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                        <div class="d-flex justify-content-between">
                                            <div class="form-group">
                                                <label for="min_sum">Минимал сумма</label>
                                                <input type="text" name="start_amount" id="start_amount" class="form-control" value="0">
                                            </div>
                                            <div class="form-group">
                                                <label for="max_sum">Максимал сумма</label>
                                                <input type="text" name="end_amount" id="end_amount" class="form-control"
                                                value="{{ \DB::select("select MAX(amount_paid) as max_amount from contracts")[0]->max_amount + 5 }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" data-bs-dismiss="modal"
                                                class="btn btn-primary waves-effect waves-light"
                                                onclick="reloadTable()">OK
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex">
                        <a class="btn btn-soft-success mr-3" onclick="exportExcel()"><i
                                class="fas fa-file-upload"></i> Експорт </a>
                        @can('Файлни импорт қилиш')
                        <a class="btn btn-soft-success mr-3" onclick="importExcel()"><i
                                class="fas fa-file-download"></i> Импорт </a>
                        @endcan
                        @can('СМС юбориш')
                        <a class="btn btn-info mr-3" onclick="sendSMS()"><i class="fa fa-phone"></i> СМС юбориш</a>
                        @endcan
                        @can('Почта юбориш')
                        <a class="btn btn-success mr-3" onclick="sendEmail()"><i class="fa fa-envelope"></i>
                            Почта
                            юбориш</a>
                        @endcan
                        <a href="/contracts/create" class="btn btn-primary mr-3"><i class="fa fa-file-alt"></i>
                            Янги
                            ҳужжат</a>
                        @can('Ҳужжатларни ўчириш')
                        <a class="btn btn-danger" onclick="deleteSelected()"><i class="fa fa-trash-alt"></i>
                            Ўчириш</a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <table class="table" id="contracts-table">
                        <thead>
                        <tr>
                            <th><input type="checkbox" id="check_all"></th>
                            <th>Иш туркуми</th>
                            <th>Суғурта шартномаси</th>
                            <th>Суғурта товони тўланган сана</th>
                            <th>Қарздор ФИШ</th>
                            <th>Суғурта товони суммаси</th>
                            <th>Суғурта товон қолдиғи</th>
                            <th>Ҳолати</th>
                            <th>Яратилган сана</th>
                            <th>Қарздорнинг телефон рақами</th>
                            <th>API</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
