<style>
    @keyframes pulse {
        0% {
            background-color: transparent;
        }
        50% {
            background-color: yellow;
        }
        100% {
            background-color: transparent;
        }
    }

    .marked {
        animation-name: pulse;
        animation-duration: 1s;
        animation-iteration-count: infinite;
    }
</style>

<script>
    $(document).ready(function () {
        var hash = window.location.hash;
        if (hash) {
            var targetId = hash.substr(1);
            var $target = $('#' + targetId);
            if ($target.length) {
                $target.addClass('marked');
            }
            setTimeout(function () {
                $target.removeClass('marked');
            }, 3000);
        }
        $('.single-toggle').click(function () {
            $('.single-contract').toggle();
        });
        showFileSaveButton()
        counter = {{ count(explode(',', $data->client->phone)) }};
        $("#add-input").click(function () {
            counter++;
            var inputId = "input_" + counter;
            var inputHtml = '<div class="input-group mt-1" id="group_' + inputId + '"><div class="input-group-text" style="cursor: pointer" onclick="removeInput(\'' + inputId + '\')"><i class="fa fa-minus"></i></div><input id="phone-' + inputId + '" name="phone[]" type="text" class="form-control" placeholder="XXYYYYYYY" required></div>';
            $("#input-container").append(inputHtml);
            showFileSaveButton()
        });
        var cntr = 0;
        $("#add-file").click(function () {
            cntr++;
            var inputId = "input_" + cntr;
            var inputHtml = '<div class="d-flex mt-1" id="file_' + inputId + '"><select name="file_name[]" id="file_name" class="form-select" required><option value="Суғурта шартномаси">Суғурта шартномаси</option><option value="Кредит шартномаси">Кредит шартномаси</option><option value="Паспорт нусхаси">Паспорт нусхаси</option><option value="Тўлов топширқномаси">Тўлов топширқномаси</option><option value="Бошқа ҳужжатлар">Бошқа ҳужжатлар</option></select><input type="file" name="file[]" id="file" class="form-control ml-2" required><button type="button" class="btn btn-danger ml-2" onclick="removeFile(\'' + inputId + '\')"><i class="fa fa-minus"></i></button></div>';
            $("#input-container-file").append(inputHtml);
            showFileSaveButton()
        });

    });

    $("#cp_table").load('{{ asset('contracts/payments/'.$data->id) }}')
    $('#contractPayEmpty').on('click', function () {
        $('#payment_id').val("");
        $('#payment_amount').val("");
        $('#payment_date').val("");
        $('#payment_note').val("");
        $(this).hide();
    });
    $('#contractPay').on('click', function () {
        var formData = $('#contract_payments').serialize();

        $.ajax({
            url: "{{ asset('contracts/payment') }}",
            type: "POST",
            data: formData,
            success: function (data) {
                $.alert({
                    title: data.message,
                    content: data.content,
                    type: 'green',
                    buttons: {ok: {text: 'OK', btnClass: 'btn-green'}}
                });
                $("#cp_table").load('{{ asset('contracts/payments/'.$data->id) }}');

                $('#payment_amount').val("");
                $('#payment_date').val("");
                $('#payment_note').val("");
            },
            error: function (data) {
                console.log(data.responseJSON)
                $.alert({
                    title: data.responseJSON.message,
                    content: JSON.stringify(data.responseJSON.errors),
                    type: 'red',
                    buttons: {ok: {text: 'OK', btnClass: 'btn-red'}}
                });
            }
        })
    });
    $('#taxExpense').on('click', function () {
        var formData = $('#tax_expense').serialize();

        $.ajax({
            url: "{{ asset('contracts/expense') }}",
            type: "POST",
            data: formData,
            success: function (data) {
                console.log(data)
                $.alert({
                    title: data.message,
                    content: data.content,
                    type: 'green',
                    buttons: {ok: {text: 'OK', btnClass: 'btn-green'}}
                });
            },
            error: function (data) {
                console.log(data.responseJSON.errors)
                $.alert({
                    title: data.responseJSON.message,
                    content: JSON.stringify(data.responseJSON.errors),
                    type: 'red',
                    buttons: {ok: {text: 'OK', btnClass: 'btn-red'}}
                });
            }
        })
    });

    // sms-manual form submit
    $('#sms-manual').on('click', function () {
        var formData = $('#sms-manual-form').serialize();
        $.ajax({
            url: "{{ asset('dic/sms-manual') }}",
            type: "POST",
            data: formData,
            success: function (data) {
                $.alert({
                    title: 'Success',
                    content: data.success,
                    type: 'green',
                    buttons: {ok: {text: 'OK', btnClass: 'btn-green'}}
                });
            },
            error: function (data) {
                console.log(data.responseJSON.errors)
                $.alert({
                    title: data.responseJSON.message,
                    content: JSON.stringify(data.responseJSON.errors),
                    type: 'red',
                    buttons: {ok: {text: 'OK', btnClass: 'btn-red'}}
                });
            }
        })
    });

    function savePetition() {
        var formData = $('#petitions-form').serialize();
        $.ajax({
            url: "petitions/add-petition?" + formData,
            type: "GET",
            data: formData,
            processData: false,
            contentType: false,
            success: function (data) {
                $.alert({
                    title: 'Успешно!',
                    content: data.message,
                    type: 'green',
                    buttons: {ok: {text: 'OK', btnClass: 'btn-green'}}
                });
                $('#contract-show').load('{{ asset('contracts/show/'.$data->id) }}')
            },
            error: function (data) {
                $.alert({
                    title: data.responseJSON.message,
                    content: JSON.stringify(data.responseJSON.errors),
                    type: 'red',
                    buttons: {ok: {text: 'OK', btnClass: 'btn-red'}}
                });
            }
        })
        return false;
    }

    function updatePetition(petition) {
        var formData = $('#' + petition).serialize();
        console.log(formData)
        $.ajax({
            url: "petitions/add-petition?" + formData,
            type: "GET",
            processData: false,
            contentType: false,
            success: function (data) {
                $.alert({
                    title: 'Успешно!',
                    content: data.message,
                    type: 'green',
                    buttons: {ok: {text: 'OK', btnClass: 'btn-green'}}
                });
                $('#contract-show').load('{{ asset('contracts/show/'.$data->id) }}')
            },
            error: function (data) {
                $.alert({
                    title: data.responseJSON.message,
                    content: JSON.stringify(data.responseJSON.errors),
                    type: 'red',
                    buttons: {ok: {text: 'OK', btnClass: 'btn-red'}}
                });
            }
        })
        return false;
    }

    function showFileSaveButton() {

        var inputContainer = $("#input-container-file");
        var myButton = $("#contract_files");
        if (inputContainer.find("*").length > 0) {
            myButton.show();
        } else {
            myButton.hide();
        }

    }

    function removeInput(id) {
        $('#group_' + id).remove();
    }

    function removeFile(id) {
        $('#file_' + id).remove();
        showFileSaveButton();
    }

    function deletePetition(id) {
        $.confirm({
            title: 'Тасдиқлаш',
            content: 'Ростдан ҳам ўчирмоқчимисиз?',
            buttons: {
                confirm: {
                    text: 'Ўчириш',
                    btnClass: 'btn-red', // Add custom button class
                    action: function () {
                        $.ajax({
                            url: "{{ asset('petitions') }}/" + id,
                            data: {_token: "{{ csrf_token() }}"},
                            method: "DELETE",
                            success: function (data) {
                                $.alert({
                                    title: data.status,
                                    content: data.message,
                                    type: 'green',
                                    buttons: {ok: {text: 'OK', btnClass: 'btn-green'}}
                                });
                                $('#petition_' + id).hide();
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
    @can('Ҳужжатларни ўчириш')
    function deleteItem() {
        $.confirm({
            title: 'Тасдиқлаш',
            content: 'Ростдан ҳам ўчирмоқчимисиз?',
            buttons: {
                confirm: {
                    text: 'Ўчириш',
                    btnClass: 'btn-red', // Add custom button class
                    action: function () {
                        $.ajax({
                            url: "{{ asset('contracts') }}/{{ $data->id }}",
                            data: {_token: "{{ csrf_token() }}"},
                            method: "DELETE",
                            success: function (data) {
                                $.alert({
                                    title: data.status,
                                    content: data.message,
                                    type: 'green',
                                    buttons: {
                                        ok: {
                                            text: 'OK',
                                            btnClass: 'btn-green',
                                            action: function () {
                                                window.location.href = "{{ asset('contracts') }}";
                                            }
                                        }
                                    }
                                });
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
    function changePayment(id, amount, date, note) {
        $('#payment_id').val(id);
        $('#payment_amount').val(amount);
        $('#payment_date').val(date);
        $('#payment_note').val(note);
        $('#contractPayEmpty').show();
    }
    @can('Почта юбориш')
    function sendFiletoFaktura() {
        $.confirm({
            title: 'Фактура',
            content: '<div class="form-group"><label>Файл юкланг</label><input type="file" class="form-control" id="file_hybrid"></div>',
            buttons: {
                formSubmit: {
                    text: 'Юклаш',
                    btnClass: 'btn-blue',
                    action: function () {
                        var file = this.$content.find('#file_hybrid')[0].files[0];
                        if (!file) {
                            $.alert('Файл танланмади');
                            return false;
                        }
                        var formData = new FormData();
                        formData.append('file', file);
                        formData.append('_token', "{{ csrf_token() }}");
                        $.ajax({
                            url: "{{ asset('contracts/faktura/'.$data->id) }}",
                            data: formData,
                            method: "POST",
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                console.log(data);
                                $.alert({
                                    title: 'Юборилди',
                                    content: data.message,
                                    type: data.color,
                                    buttons: {ok: {text: 'OK', btnClass: 'btn-green'}}
                                });
                                $('#faktura').html(data.html);
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
    function loadDataHybrid() {
        $.ajax({
            url: "{{ asset('contracts/data-hybrid/'.$data->id) }}",
            method: "GET",
            success: function (data) {
                // message content
                $.alert({
                    title: 'Юборилди',
                    content: data.message,
                    type: data.color,
                    buttons: {ok: {text: 'OK', btnClass: 'btn-green'}}
                });
                $('#hybrid').html(data.content);
            }
        })
    }

    function saveJudgeInfo() {
        var form = $('#judge_information').serializeArray();
        // form append _token
        form.push({
            name: '_token',
            value: "{{ csrf_token() }}"
        });
        console.log(form);
        $.ajax({
            url: "{{ asset('contracts/judge-info/'.$data->id) }}",
            method: "POST",
            data: form,
            success: function (data) {
                // message content
                $.alert({
                    title: 'Юборилди',
                    content: data.message,
                    type: data.color,
                    buttons: {ok: {text: 'OK', btnClass: 'btn-green'}}
                });
                $('#judge').html(data.content);
            }
        })
    }

    function saveMibInfo() {
        var form = $('#mib_information').serializeArray();
        form.push({
            name: '_token',
            value: "{{ csrf_token() }}"
        });
        $.ajax({
            url: "{{ asset('contracts/mib-info/'.$data->id) }}",
            method: "POST",
            data: form,
            success: function (data) {
                // message content
                $.alert({
                    title: 'Юборилди',
                    content: data.message,
                    type: data.color,
                    buttons: {ok: {text: 'OK', btnClass: 'btn-green'}}
                });
                $('#mib').html(data.content);
            }
        });
    }
    @can('СМС юбориш')
    function smsSend() {
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
                                ids: ['{{$data->id}}'],
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
    function backIndex() {
        $('#contract-show').hide();
        $('#contract-index').show();
    }


    function printUID(uid, check) {
        var url = "{{ asset('contracts/pdf-check') }}/" + uid + "/" + check;
        var win = window.open(url, '_blank');
        win.print();
    }

    // Function to format the number with commas as thousands separators
    function formatNumberWithCommas(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Event handler for input change
    $('.priceInput').on('keyup', function () {
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

    $('.judge-info').on('click', function (){
        $('#judge-info').toggle();
    })
    $('.mib-info').on('click', function (){
        $('#mib-info').toggle();
    })

</script>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card" id="user">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        Ҳужжат рақами <b>{{ $data->id }}</b>
                    </div>
                    <div class="d-flex">
                        @can('СМС юбориш')
                            <a class="btn btn-info mr-3" onclick="smsSend()">СМС юбориш</a>
                        @endcan
                        @can('Почта юбориш')
                            <a class="btn btn-success mr-3" onclick="sendFiletoFaktura()">Почта</a>
                        @endcan
                        <a href="/contracts/create" class="btn btn-primary mr-3">Янги ҳужжат</a>
                        @can('Ҳужжатларни ўзгартириш (тўлов суммаларидан ташқари)')
                            <a href="/contracts/{{ $data->id }}/edit" class="btn btn-warning mr-3">Ўзгартириш</a>
                        @endcan
                        @can('Ҳужжатларни ўчириш')
                            <button class="btn btn-danger" onclick="deleteItem()">Ўчириш</button>
                        @endcan

                        <div class="btn-group ml-2 mr-3">
                            <button type="button" class="btn btn-secondary dropdown-toggle waves-effect"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Сўров <i
                                    class="mdi mdi-chevron-down"></i></button>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                                <a class="dropdown-item" href="#">Ўлим ҳақида маълумот олиш</a>
                                <a class="dropdown-item" href="#">Ажрашиш ҳақида маълумот олиш</a>
                                <a class="dropdown-item" href="#">Никоҳ ҳақида маълумот олиш</a>
                                <a class="dropdown-item" href="#">Тадбиркорлик субъектининг давлат рўйхатидан
                                    ўтказилганлиги тўғрисидаги гувохнома</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Паспорт маълумотлари (серия + паспорт рақами + Т.
                                    К.)</a>
                                <a class="dropdown-item" href="#">Паспорт маълумотлари (ЖШШИР + серия + паспорт
                                    рақами)</a>
                                <a class="dropdown-item" href="#">Паспорт маълумотлари (ЖШШИР + серия + паспорт
                                    рақами)</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Юридик шахсларнинг маълумотлар базаси</a>
                                <a class="dropdown-item" href="#">Юридик шахсларнинг банк реквизитлари ҳақида
                                    маълумот олиш</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Ҳозирги иш жойи тўғрисида маълумот олиш</a>
                                <a class="dropdown-item" href="#">Иш тарихи тўғрисида маълумот олиш</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Рўйхатдан ўтиш ҳақида маълумот олиш (ЖШШИР)</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Транспорт воситалари эгаларининг фуқаролик
                                    жавобгарлигини мажбурий суғурта қилинганлиги бўйича батафсил маълумот тақдим
                                    этувчи сервис</a>
                                <a class="dropdown-item" href="#">Транспорт воситаси гувоҳномаси (тех паспорт рақами
                                    бўйича, ЖШШИР, СТИР)</a>
                            </div>
                        </div>
                        <a onclick="backIndex()" class="btn btn-primary"><i
                                class="uil-left-arrow-from-left"></i> Ортга</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card" id="user">


                <div class="card-body">
                    <div class="btn single-contract single-toggle" style="display: none">
                        Иш туркуми : {{ $data->category->name }}, Суғурта шартномаси : {{ $data->number }} ... <i
                            class="fa fa-angle-down"></i>
                    </div>
                    <table class="table single-contract">
                        <tbody>
                        <tr>
                            <th>Иш туркуми</th>
                            <td>{{ $data->category->name }}</td>
                        </tr>
                        <tr>
                            <th>Шартнома рақами</th>
                            <td>{{ $data->number }}</td>
                        </tr>
                        <tr>
                            <th>Шартнома номи</th>
                            <td>{{ $data->name }}</td>
                        </tr>
                        <tr>
                            <th>Шартнома санаси</th>
                                <?php \Carbon\Carbon::setLocale('uz'); ?>
                            <td>{{ \Carbon\Carbon::parse($data->date)->translatedFormat('d M Y') }}</td>
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
                            <th>ПИНФЛ</th>
                            <td>{{ $data->client->pinfl }}</td>
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
                            <td>{{ $data->client->type ? 'Юридик шахс':'Жисмоний шахс' }}</td>
                        </tr>
                        <tr>
                            <th>Суғурта товони тўланган сана</th>
                            <td>{{ \Carbon\Carbon::parse($data->date_payment)->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <th>Суғурта товони суммаси</th>
                            <td>{{ number_format($data->amount,2,"."," ") }}</td>
                        </tr>
                        <tr>
                            <th>Суғурта товони тўланган суммаси</th>
                            <td>{{ number_format($data->amount_paid,2,"."," ") }}</td>
                        </tr>
                        <tr>
                            <th class="text-warning">Суғурта товон қолдиғи</th>
                            <td class="text-warning">
                                <b>{{ number_format(($data->amount - $data->amount_paid),2,"."," ") }}</b>
                            </td>
                        </tr>
                        <tr>
                            <th>Ҳолати</th>
                            <td><span
                                    class="badge {{ \App\Models\Client::STATUS_COLOR[$data->status] }}">{{ \App\Models\Client::STATUS_NAME[$data->status] }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Изоҳ</th>
                            <td>{{ $data->note }}</td>
                        </tr>
                        <tr>
                            <th>Шартномага тегишли шахслар</th>
                            <td>
                                @foreach(\App\Models\Client::whereIn('id', explode(',',$data->clients))->with('region', 'district')->get() as $user)
                                    <span class="badge bg-primary" style="cursor: pointer" data-bs-toggle="modal" data-bs-target="#myModal{{$user->id}}">{{ $user->fullname }}</span>

                                    <!-- sample modal content -->
                                    <div id="myModal{{$user->id}}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myModalLabel">{{ $user->fullname }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-sm table-hover table-bordered table-striped table-nowrap align-middle">
                                                        <tr><td>Регион</td><td>{{ $user->region->name }}</td></tr>
                                                        <tr><td>Туман</td><td>{{ $user->district->name }}</td></tr>
                                                        <tr><td>Тўлиқ исм</td><td>{{ $user->fullname }}</td></tr>
                                                        <tr><td>Паспорт</td><td>{{ $user->passport }}</td></tr>
                                                        <tr><td>Пинфл</td><td>{{ $user->pinfl }}</td></tr>
                                                        <tr><td>Телефон</td><td>{{ $user->phone }}</td></tr>
                                                        <tr><td>Манзил</td><td>{{ $user->address }}</td></tr>
                                                        <tr><td>Туғилган куни</td><td>{{ \Carbon\Carbon::parse($user->dtb)->format('d.m.Y') }}</td></tr>
                                                        <tr><td>Тури</td><td>{{ $user->type ? 'Юридик шахс':'Жисмоний шахс' }}</td></tr>
                                                        <tr><td>ИНН</td><td>{{ $user->inn }}</td></tr>
                                                        <tr><td>МФО</td><td>{{ $user->mfo }}</td></tr>
                                                        <tr><td>Ҳисоб рақами</td><td>{{ $user->account_number }}</td></tr>
                                                        <tr><td>Эслатма</td><td>{{ $user->note }}</td></tr>
                                                        <tr><td>Яратилган</td><td>{{ $user->created_at->format('d.m.Y') }}</td></tr>
                                                    </table>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light waves-effect" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>Яратилган сана</th>
                            <td class="d-flex justify-content-between">
                                <p>{{ \Carbon\Carbon::parse($data->created_at)->format('d.m.Y') }}</p>
                                <button type="button" class="btn single-toggle"><i class="fa fa-angle-up"></i>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-body">
                    <div class="row">
                        @foreach($data->petitions as $petition)
                            <div class="col-lg-12">
                                <form id="petition_{{ $petition->id }}">
                                    <input type="hidden" name="contract_id" value="{{ $data->id }}">
                                    <input type="hidden" name="data_id" value="{{ $petition->id }}">
                                    <div class="d-flex align-items-end justify-content-between">
                                        <div class="form-group" style="width: 50%;">
                                            <label for="petition">Ариза</label>
                                            <select class="form-select" name="template_id"
                                                    id="petition-{{$petition->id}}">
                                                @foreach(\App\Models\DocTemplate::all() as $d)
                                                    <option
                                                        value="{{$d->id}}" @selected($d == $petition->type)>{{$d->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <a href="{{ asset($petition->file) }}" download
                                           class="btn btn-success">Download</a>
                                        <button type="button" onclick="updatePetition('petition_{{ $petition->id }}')"
                                                class="btn btn-primary">Сақлаш
                                        </button>
                                        {{-- delete (Ўчириш) with modal--}}
                                        <button type="button" class="btn btn-danger waves-effect waves-light"
                                                onclick="deletePetition('{{$petition->id}}')">Ўчириш
                                        </button>
                                        {{--                                    <button type="button" class="btn btn-danger waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#deletePetition{{$petition->id}}">Ўчириш</button>--}}
                                    </div>
                                </form>
                            </div>

                            <div class="modal fade" id="deletePetition{{$petition->id}}" aria-hidden="true"
                                 aria-labelledby="..." tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fa fa-exclamation-triangle text-danger"></i>
                                                <span class="text-danger">Ўчириш</span>
                                                <br>
                                                {{ $petition->type }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/petitions/{{ $petition->id }}" style='display:inline'
                                                  method="post">
                                                @csrf
                                                {{ method_field('DELETE') }}
                                                <button type="submit" class="btn btn-outline-danger">Ўчириш</button>
                                                <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-dismiss="modal">Бекор қилиш
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <form id="petitions-form" method="post">
                                @csrf
                                <input type="hidden" name="contract_id" value="{{ $data->id }}">
                                <div class="row align-items-end">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="petition">Ариза</label>
                                            <select class="form-select" name="template_id" required>
                                                <option value=""></option>
                                                @foreach(\App\Models\DocTemplate::all() as $d)
                                                    <option value="{{$d->id}}">{{$d->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" onclick="savePetition()" class="btn btn-primary">Сақлаш
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="/contracts/{{$data->id}}" method="post" enctype="multipart/form-data">
                                @csrf
                                {{ method_field('PUT') }}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="files">Ҳужжат учун файллар</label> <span
                                                class="btn-sm btn-primary"
                                                style="padding:1px 6px 3px 5px; cursor: pointer"
                                                id="add-file">+</span>
                                            @if($data->files)
                                                @foreach($data->files as $key => $file)
                                                    <div class="d-flex justify-content-between">
                                                        <b>{{ $file->name }}</b>
                                                        <a href="{{ asset($file->file) }}"
                                                           class="text-success ml-2" download=""><i
                                                                class="fa fa-download"></i> Юклаб олиш</a>
                                                    </div>
                                                @endforeach
                                            @endif
                                            <div id="input-container-file"></div>
                                        </div>
                                    </div>
                                    <div class="form-group mt-3">
                                        <button type="submit" id="contract_files" class="btn btn-success">Saqlash
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">

                @if($data->id)
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                @can('Тўлов суммаларини киритиш')
                                    <form id="contract_payments">
                                        @csrf
                                        <input type="hidden" name="payment_id" id="payment_id">
                                        <input type="hidden" name="contract_id" id="contract_id"
                                               value="{{ $data->id }}">
                                        <input type="hidden" name="client_id" id="client_id"
                                               value="{{ $data->client->id }}">
                                        <div class="row">
                                            <div class="col-md-12 d-flex align-items-end">
                                                <div>
                                                    <div class="d-flex align-items-end">
                                                        <div class="mr-3">
                                                            <label>Тўловлар:</label>
                                                            <input type="text" name="amount" id="payment_amount"
                                                                   class="form-control" placeholder="Summa">
                                                        </div>
                                                        <div>
                                                            <label>Сана:</label>
                                                            <input type="date" name="date" id="payment_date"
                                                                   class="form-control mr-2" placeholder="Sana">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label>Изоҳ:</label>
                                                        <input type="text" name="note" id="payment_note"
                                                               class="form-control mr-2" placeholder="Изоҳ">
                                                    </div>
                                                </div>
                                                <button class="btn btn-danger ml-2 mr-2" type="button"
                                                        style="display: none" id="contractPayEmpty"
                                                        onclick="contractPayEmpty()"><i
                                                        class="fa fa-window-close"></i></button>
                                                <button class="btn btn-primary ml-2" type="button" id="contractPay"><i
                                                        class="fa fa-save"></i></button>
                                            </div>
                                        </div>
                                    </form>
                                @endcan
                                <br>
                                <div id="cp_table"></div>

                            </div>
                            <div class="col-md-6">
                                <form id="tax_expense">
                                    @csrf
                                    <input type="hidden" name="contract_id" id="contract_id"
                                           value="{{ $data->id }}">
                                    <div class="row">
                                        <div class="col-md-6">Давлат божи:</div>
                                        <div class="col-md-6">Почта харажати:</div>
                                        <div class="col-md-6"><input type="text" name="tax" id="tax"
                                                                     class="form-control priceInput"
                                                                     placeholder="DAVLAT BOJ"
                                                                     value="{{ $data->tax }}"></div>
                                        <div class="col-md-6 d-flex">
                                            <input type="text" name="expense" id="expense" class="form-control"
                                                   placeholder="POCHTA XARAJATI" value="{{ $data->expense }}">
                                            <button class="btn btn-primary ml-2" type="button" id="taxExpense"><i
                                                    class="fa fa-save"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h6>Почта</h6>
                            <button type="button" class="btn-sm btn-primary" onclick="loadDataHybrid()"><i
                                    class="uil-refresh"></i></button>
                        </div>
                        <div class="card-body">
                            <table class="table single-contract">
                                <thead>
                                <tr>
                                    <th>Статус</th>
                                    <th>Яратилди</th>
                                    <th>Янгиланди</th>
                                </tr>
                                </thead>
                                <tbody id="hybrid">

                                @forelse($data->hybrids as $hybrid)
                                    <tr>
                                        <td>

                                            <div class="btn-group">
                                                <button type="button"
                                                        class="btn btn-{{ \App\Models\Contract\ContractHybrid::STATUS_COLOR[(int)$hybrid->status] }} waves-effect">{{ \App\Models\Contract\ContractHybrid::STATUS[(int)$hybrid->status] }}</button>
                                                <button type="button"
                                                        class="btn btn-light dropdown-toggle dropdown-toggle-split waves-effect"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                    <i class="mdi mdi-chevron-down"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item cursor-pointer btn btn-primary"
                                                       onclick="printUID('{{ $hybrid->uid }}', true)">Чек</a>
                                                    <a class="dropdown-item cursor-pointer btn btn-primary"
                                                       onclick="printUID('{{ $hybrid->uid }}', false)">Чексиз</a>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $hybrid->created_at }}</td>
                                        <td>{{ $hybrid->updated_at }}</td>
                                    </tr>
                                @empty
                                @endforelse

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6>СМС</h6>
                        </div>
                        <div class="card-body">
                            <table class="table single-contract">
                                <tbody>
                                @foreach($data->sms as $sms)
                                    <tr>
                                        <th>{{ $sms->phone }}</th>
                                        <td>
                                            {{ strlen($sms->message) > 50 ? mb_substr($sms->message, 0, 50) . '...' : $sms->message }}
                                            <button type="button" class="text-muted text-decoration-underline"
                                                    style="float: right" data-bs-toggle="modal"
                                                    data-bs-target="#edit{{ $sms->id }}">Кўпроқ
                                            </button>
                                            <div class="modal fade" id="edit{{ $sms->id }}" role="dialog"
                                                 aria-labelledby="edit{{ $sms->id }}">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title" id="edit{{ $sms->id }}">
                                                                {{ $sms->phone }}
                                                            </h4>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            {!! $sms->message !!}
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

                <div class="col-lg-6">
                    <div class="card" >
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6>Суд маълумотлари</h6>
                            <button type="button" class="btn-sm btn-primary judge-info"><i class="uil-sort-amount-down"></i></button>
                        </div>
                        <div class="card-body" id="judge-info" style="display:none">
                            <form id="judge_information">
                                <table class="table single-contract">
                                    <tbody>
                                    <tr>
                                        <th>Суд</th>
                                        <input type="hidden" class="form-control" name="judge_id"
                                               value="{{ $judge->id }}">
                                        <td>{{ $judge->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Иш №</th>
                                        <td><input type="text" name="work_number" class="form-control"
                                                   value="{{ $data->judge?->work_number }}"></td>
                                    </tr>
                                    <tr>
                                        <th>Натижа</th>
                                        <td><input type="text" name="result" class="form-control"
                                                   value="{{ $data->judge?->result }}"></td>
                                    </tr>
                                    <tr>
                                        <th>Изоҳ</th>
                                        <td><input type="text" name="note" class="form-control"
                                                   value="{{ $data->judge?->note }}"></td>
                                    </tr>
                                    <tr style="text-align: right">
                                        <td></td>
                                        <td>
                                            <button type="button" class="btn btn-primary" onclick="saveJudgeInfo()">
                                                Сақлаш
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card" >
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6>МИБ маълумотлари</h6>
                            <button type="button" class="btn-sm btn-primary mib-info"><i class="uil-sort-amount-down"></i></button>
                        </div>
                        <div class="card-body" id="mib-info" style="display:none">
                            <form id="mib_information">
                                <table class="table single-contract">
                                    <tbody>
                                    <tr>
                                        <th>МИБ</th>
                                        <td><input type="text" name="name" class="form-control"
                                                   value="{{ $data->mib?->name }}"></td>
                                    </tr>
                                    <tr>
                                        <th>Ижро&nbsp;№</th>
                                        <td><input type="text" name="work_number" class="form-control"
                                                   value="{{ $data->mib?->work_number }}"></td>
                                    </tr>
                                    <tr>
                                        <th>Натижа</th>
                                        <td><input type="text" name="result" class="form-control"
                                                   value="{{ $data->mib?->result }}"></td>
                                    </tr>
                                    <tr>
                                        <th>Изоҳ</th>
                                        <td><input type="text" name="note" class="form-control"
                                                   value="{{ $data->mib?->note }}"></td>
                                    </tr>
                                    <tr style="text-align: right">
                                        <td></td>
                                        <td>
                                            <button type="button" class="btn btn-primary" onclick="saveMibInfo()">
                                                Сақлаш
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h6>СМС</h6>
                        </div>
                        <div class="card-body">
                            <form id="sms-manual-form">
                                @csrf
                                <div class="form-group">
                                    <label for="name">Телефон</label>
                                    <input type="text" name="sms_phone" id="sms_phone" class="form-control" required
                                           placeholder="998977820809">
                                </div>
                                <div class="form-group">
                                    <label for="content">Ҳабар</label>
                                    <textarea name="sms_message" id="sms_message" cols="30" class="form-control"
                                              rows="10" required></textarea>
                                </div>
                                <br>
                                <div class="form-group text-right">
                                    <button id="sms-manual" type="button" class="btn btn-primary">Қўшиш</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
