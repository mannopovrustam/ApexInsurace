@extends('layouts.app')

@section('style')
    <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css" rel="stylesheet"
          type="text/css"/>

    <style>
        .input-group-text:hover {
            color: #fff;
            background: red;
            transition: all 0.3s;
        }
    </style>
@endsection
@section('script')
    <script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

    <script>
        var counter;
        var counterSelect;

        $(document).ready(function () {
            counter = {{ count(explode(',', $data->client->phone)) }};
            $("#add-input").click(function () {
                counter++;
                var inputId = "input_" + counter;
                var inputHtml = '<div class="input-group mt-1" id="group_' + inputId + '"><div class="input-group-text" style="cursor: pointer" onclick="removeInput(\'' + inputId + '\')"><i class="fa fa-minus"></i></div><input id="phone-' + inputId + '" name="phone[]" type="text" class="form-control" placeholder="XXYYYYYYY" required></div>';
                $("#input-container").append(inputHtml);
            });

            counterSelect = {{ count(explode(',', $data->client)) }};
            $("#add-select").click(function () {
                counterSelect++;
                var inputId = "select_" + counterSelect;
                var inputHtml = '<div class="input-group mt-1" id="group_' + inputId + '"><div class="input-group-text" style="cursor: pointer" onclick="removeSelect(\'' + inputId + '\')"><i class="fa fa-minus"></i></div><select name="clients[]" id="clients-' + inputId + '" class="form-select"><option value="">*** Мижозни танланг ***</option>'
                        @foreach($clients as $c)
                    + '<option value="{{ $c->id }}">{{ $c->fullname }}</option>'
                        @endforeach
                    + '</select></div>';
                $("#input-container-select").append(inputHtml);
            });

            var cntr = 0;
            $("#add-file").click(function () {
                cntr++;
                var inputId = "input_" + cntr;
                var inputHtml = '<div class="d-flex mt-1" id="file_' + inputId + '"><select name="file_name[]" id="file_name" class="form-select" required><option value="Суғурта шартномаси">Суғурта шартномаси</option><option value="Кредит шартномаси">Кредит шартномаси</option><option value="Паспорт нусхаси">Паспорт нусхаси</option><option value="Тўлов топширқномаси">Тўлов топширқномаси</option><option value="Бошқа ҳужжатлар">Бошқа ҳужжатлар</option></select><input type="file" name="file[]" id="file" class="form-control ml-2" required><button type="button" class="btn btn-danger ml-2" onclick="removeFile(\'' + inputId + '\')"><i class="fa fa-minus"></i></button></div>';
                $("#input-container-file").append(inputHtml);
            });

            $("#cp_table").load('{{ asset('contracts/payments/'.$data->id) }}')
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

                        $('#payment_amount').val();
                        $('#payment_date').val();
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

            @if($data->client->district_id)
            $.ajax({
                url: '{{ asset('data/districts') }}',
                data: {region_id: $('#region_id').val(), selected: '{{ $data->client->district_id }}'},
                success: function (data) {
                    $("#district_id").html(data)
                }
            })
            @endif
            $('#region_id').on('change', function () {
                $.ajax({
                    url: '{{ asset('data/districts') }}',
                    data: {region_id: $('#region_id').val(), selected: '{{ $data->client->district_id }}'},
                    success: function (data) {
                        $("#district_id").html(data)
                    }
                })
            })


            @if(session()->has('message'))
            notify("{{ session()->get('message') }}", "success");
            @endif

            @if(session()->has('errors'))
            @foreach(json_decode(session()->get('errors')) as $key => $value)
            notify("{{ $value[0] }}", "error");
            @endforeach
            @endif
        });

        $('#passport').keyup(function () {
            var inputValue = $(this).val();
            var latinRegex = /^[a-zA-Z0-9\s]*$/; // Regular expression to allow only Latin characters and spaces

            if (!latinRegex.test(inputValue)) {
                $(this).val(function (_, val) {
                    return val.replace(/[^a-zA-Z0-9\s]/g, ''); // Remove non-Latin characters from the input value
                });
            }
        });

        $('#pinfl').keyup(function () {
            var inputValue = $(this).val();
            var latinRegex = /^[0-9\s]*$/; // Regular expression to allow only Latin characters and spaces

            if (!latinRegex.test(inputValue)) {
                $(this).val(function (_, val) {
                    return val.replace(/[^0-9\s]/g, ''); // Remove non-Latin characters from the input value
                });
            }
        });

        function changePayment(id, amount, date) {
            $('#payment_id').val(id);
            $('#payment_amount').val(amount);
            $('#payment_date').val(date);
        }

        function removeInput(id) {
            $('#group_' + id).remove();
        }

        function removeSelect(id) {
            $('#group_' + id).remove();
        }

        function removeFile(id) {
            $('#file_' + id).remove();
        }

        function findGuest() {
            var passport = $('#passport').val();
            var pinfl = $('#pinfl').val();

            $.ajax({
                url: "/contracts/find-guest",
                data: {
                    passport: passport,
                    pinfl: pinfl,
                },
                success: function (data) {
                    var guestname = ''
                    if (data.status == 'success') {
                        guestname = data.data.fullname;
                        $('#client_id').val(data.data.id);
                        $('#fullname').val(data.data.fullname);
                        $('#address').val(data.data.address);
                        $('#region_id').val(data.data.region_id)
                        $('#type').val(data.data.type)
                        $('#dtb').val(data.data.dtb)
                        $('#passport').val(data.data.passport)
                        $('#pinfl').val(data.data.pinfl)

                        $.ajax({
                            url: '{{ asset('data/districts') }}',
                            data: {region_id: $('#region_id').val(), selected: data.data.district_id},
                            success: function (data) {
                                $("#district_id").html(data)
                            }
                        });

                        var string = data.data.phone;
                        var items = string.split(",");
                        $('.input-group').remove();

                        $.each(items, function (index, item) {
                            counter++;
                            var inputId = "input_" + counter;
                            var inputHtml = '<div class="input-group" id="group_' + inputId + '"><div class="input-group-text" style="cursor: pointer" onclick="removeInput(\'' + inputId + '\')"><i class="fa fa-minus"></i></div><input id="phone-' + inputId + '" name="phone[]" type="text" class="form-control" placeholder="XXYYYYYYY" value="' + item + '" required></div>';
                            $("#input-container").append(inputHtml);
                        });

                        var stringSelect = data.data.clients;
                        var itemsSelect = stringSelect.split(",");
                        $('.input-group').remove();

                        $.each(itemsSelect, function (index, item) {
                            counter++;
                            var inputId = "input_" + counter;
                            var inputHtml = '<div class="input-group" id="group_' + inputId + '"><div class="input-group-text" style="cursor: pointer" onclick="removeInput(\'' + inputId + '\')"><i class="fa fa-minus"></i></div><input id="phone-' + inputId + '" name="phone[]" type="text" class="form-control" placeholder="XXYYYYYYY" value="' + item + '" required></div>';
                            $("#input-container-select").append(inputHtml);
                        });
                    }
                    $.alert({
                        title: data.message,
                        content: guestname,
                        type: data.color,
                        buttons: {ok: {text: 'OK', btnClass: 'btn-' + data.color}}
                    });
                }
            })
        }
    </script>

@endsection
@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-12">


                <div class="card" id="user">
                    <div class="card-header d-flex justify-content-between">
                        <p>Регистрация</p>
                        @if($data->id)
                            <h5>{{ $data->number .'-'. $data->name }}</h5>
                        @endif
                        <a href="{{ asset('contracts') }}" class="btn btn-primary"><i
                                    class="uil-left-arrow-from-left"></i> Ортга</a>
                    </div>
                    <div class="card-body">
                        <form action="/contracts" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="data_id" value="{{$data->id}}">
                            <input type="hidden" name="client_id" id="client_id" value="{{$data->client_id}}">
                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="passport">Паспорт маълумотлари</label>
                                        <input type="text" class="form-control" id="passport"
                                               value="{{ isset($data->id) ? $data->client->passport : old('passport') }}"
                                               name="passport" placeholder="ZZ0011223">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="pinfl">ПИНФЛ</label>
                                        <input type="text" class="form-control" id="pinfl" minlength="14" maxlength="14"
                                               value="{{ isset($data->id) ? $data->client->pinfl : old('pinfl') }}"
                                               name="pinfl" placeholder="01234567891234">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="dtb">Туғилган санаси</label>
                                        <input type="date" class="form-control" id="dtb"
                                               value="{{ isset($data->id) ? $data->client->dtb : old('dtb') }}"
                                               name="dtb" placeholder="00.00.0000">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label><br>
                                        <button type="button" class="btn btn-primary" onclick="findGuest()"><i
                                                    class="fa fa-search"></i> Қидириш
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="number">Иш туркуми</label>
                                        <select class="form-select select2" name="category_id" required>
                                            <option value="">*** Иш туркуми ***</option>
                                            @foreach(\App\Models\Category::with('categories')->where('category_id', null)->get() as $category)
                                                @if(count($category->categories)>0)
                                                    @can($category->permission)
                                                        <optgroup label="{{$category->name}}">
                                                            @foreach($category->categories as $c)
                                                                @can($c->permission)
                                                                    <option
                                                                            value="{{ $c->id }}" @selected($c->id == $data->category_id)
                                                                            @if(!isset($data->category_id)) @if(session()->get('category_id') == $c->id) selected @endif @endif
                                                                    >{{ $c->name }}</option>
                                                                @endcan
                                                            @endforeach
                                                        </optgroup>
                                                    @endcan
                                                @else
                                                    @can($category->permission)
                                                        <option
                                                                value="{{$category->id}}" @selected($category->id == $data->category_id)
                                                                @if(!isset($data->category_id)) @if(session()->get('category_id') == $c->id) selected @endif @endif>
                                                            {{$category->name}}
                                                        </option>
                                                    @endcan
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="brand">Шартнома рақами</label>
                                        <input type="text" class="form-control" id="number"
                                               value="{{ isset($data->id) ? $data->number : session()->get('number') }}"
                                               name="number" placeholder="Шартнома рақами">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="name">Шартнома номи</label>
                                        <input type="text" class="form-control" id="name"
                                               value="{{ isset($data->id) ? $data->name : session()->get('name') }}"
                                               name="name" placeholder="Шартнома номи">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date">Шартнома санаси</label>
                                        <input type="date" class="form-control " id="date"
                                               value="{{ isset($data->id) ? $data->date : session()->get('date') }}"
                                               name="date" placeholder="Шартнома санаси">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="type">Қарздор ФИШ</label>
                                        <input type="text" class="form-control" id="fullname"
                                               value="{{ isset($data->id) ? $data->client->fullname : old('fullname') }}"
                                               name="fullname" placeholder="ФИШ">
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="address">Вилоят</label>
                                        <select class="form-select" name="region_id" id="region_id" required>
                                            <option value="">*** Вилоят танланг ***</option>
                                            @foreach(\DB::table('regions')->get() as $rgn)
                                                <option
                                                        value="{{ $rgn->id }}" @selected($data->client->region_id == $rgn->id)>{{ $rgn->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="address">Туман</label>
                                        <select class="form-select" name="district_id" id="district_id">
                                            <option value="">*** Туман танланг ***</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="address">Манзили</label>
                                        <input type="text" class="form-control" id="address"
                                               value="{{ isset($data->id) ? $data->client->address : old('address') }}"
                                               name="address" placeholder="Манзили">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="phone">Қарздорнинг телефон рақами </label> <span
                                                class="btn-sm btn-primary"
                                                style="padding:1px 6px 3px 5px; cursor: pointer"
                                                id="add-input">+</span>
                                        <div id="input-container">
                                            @foreach(explode(',', $data->client->phone) as $key => $phone)
                                                @if(!empty($phone))
                                                <div class="input-group mt-1" id="group_{{ $key+1 }}">
                                                    <div class="input-group-text" style="cursor: pointer"
                                                         onclick="removeInput('{{$key+1}}')"><i class="fa fa-minus"></i>
                                                    </div>
                                                    <input id="phone-{{ $key+1 }}" type="text" name="phone[]"
                                                           class="form-control" value="{{ $phone }}"
                                                           placeholder="XXYYYYYYY" required>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="type">Қарздор шакли</label>
                                        <select class="form-select" name="type" id="type" required>
                                            <option value="">*** Шахс танланг ***</option>
                                            <option value="0" @if($data->client->type == 0) selected @else @if(session()->get('type') == "0") selected @endif @endif>Жисмоний шахс</option>
                                            <option value="1" @if($data->client->type == 1) selected @else @if(session()->get('type') == "1") selected @endif @endif>Юридик шахс</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_payment">Суғурта товони тўланган сана</label>
                                        <input type="date" class="form-control inputmaskDate" id="date_payment"
                                               value="{{ isset($data->id) ? $data->date_payment : old('date_payment') }}"
                                               name="date_payment" placeholder="Суғурта товони тўланган сана">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="amount">Суғурта товони суммаси</label>
                                        <input type="number" step="0.01" class="form-control" id="amount"
                                               value="{{ isset($data->id) ? $data->amount : old('amount') }}"
                                               name="amount" placeholder="0.00">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="sts">Шартнома ҳолати</label>
                                        <select class="form-select" name="status" id="sts" required>
                                            @foreach(\App\Models\Client::STATUS_NAME as $key => $status)
                                                <option
                                                        value="{{ $key }}" @selected($data->status == $key)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="inn">ИНН</label>
                                        <input type="text" class="form-control" id="inn" name="inn"
                                               value="{{ isset($data->id) ? $data->client->inn : session('inn') }}"
                                               placeholder="ИНН">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="mfo">МФО</label>
                                        <input type="text" class="form-control" id="mfo" name="mfo"
                                               value="{{ isset($data->id) ? $data->client->mfo : session('mfo') }}"
                                               placeholder="МФО">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="account_number">Ҳисоб рақам</label>
                                        <input type="text" class="form-control" id="account_number"
                                               name="account_number"
                                               value="{{ isset($data->id) ? $data->client->account_number : session('account_number') }}"
                                               placeholder="Ҳисоб рақам">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="bank_name">Банк номи</label>
                                        <input type="text" class="form-control" id="bank_name"
                                               name="bank_name"
                                               value="{{ isset($data->id) ? $data->bank_name : '' }}"
                                               placeholder="Банк номи">
                                    </div>
                                </div>


                                {{-- add dynamical input --}}



                                <div class="form-group mt-3 d-flex justify-content-between align-items-end">
{{--
                                    <div class="col-md-6">
                                        <label for="clients">Шартномага тегишли шахслар</label> <span
                                                class="btn-sm btn-primary"
                                                style="padding:1px 6px 3px 5px; cursor: pointer"
                                                id="add-select">+</span>
                                        <div id="input-container-select">
                                            @if($data->clients)
                                                @forelse(explode(',', $data->clients) as $k => $client)
                                                    <div class="input-group mt-1" id="group_select_{{ $k+1 }}">
                                                        <div class="input-group-text" style="cursor: pointer"
                                                             onclick="removeSelect('{{$k+1}}')"><i
                                                                    class="fa fa-minus"></i>
                                                        </div>
                                                        <select name="clients[]" id="clients-{{ $k+1 }}"
                                                                class="form-select">
                                                            <option value="">*** Мижозни танланг ***</option>
                                                            @foreach($clients as $c)
                                                                <option value="{{ $c->id }}" @selected($c->id == $client)>{{ $c->fullname }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @empty
                                                @endforelse
                                            @endif
                                        </div>
                                    </div>
--}}

                                    <button type="submit" class="btn btn-success">Saqlash</button>
                                </div>
                            </div>

                        </form>

                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
