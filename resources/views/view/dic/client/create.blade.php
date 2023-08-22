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

        @if($data->district_id)
        $.ajax({
            url: '{{ asset('data/districts') }}',
            data: {region_id: $('#region_id').val(), selected: '{{ $data->district_id }}'},
            success: function (data) {
                $("#district_id").html(data)
            }
        })
        @endif
        $('#region_id').on('change', function () {
            $.ajax({
                url: '{{ asset('data/districts') }}',
                data: {region_id: $('#region_id').val(), selected: '{{ $data->district_id }}'},
                success: function (data) {
                    $("#district_id").html(data)
                }
            })
        })
    </script>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="user">
                    <div class="card-body">
                        <form action="/dic/client" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{$data->id}}">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="passport">Паспорт маълумотлари</label>
                                        <input type="text" class="form-control" id="passport"
                                               value="{{ isset($data->id) ? $data->passport : old('passport') }}"
                                               name="passport" placeholder="ZZ0011223">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="pinfl">ПИНФЛ</label>
                                        <input type="text" class="form-control" id="pinfl" minlength="14" maxlength="14"
                                               value="{{ isset($data->id) ? $data->pinfl : old('pinfl') }}"
                                               name="pinfl" placeholder="01234567891234">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="dtb">Туғилган санаси</label>
                                        <input type="date" class="form-control" id="dtb"
                                               value="{{ isset($data->id) ? $data->dtb : old('dtb') }}"
                                               name="dtb" placeholder="00.00.0000" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="type">Қарздор ФИШ</label>
                                        <input type="text" class="form-control" id="fullname"
                                               value="{{ isset($data->id) ? $data->fullname : old('fullname') }}"
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
                                                    value="{{ $rgn->id }}" @selected($data->region_id == $rgn->id)>{{ $rgn->name }}</option>
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
                                               value="{{ isset($data->id) ? $data->address : old('address') }}"
                                               name="address" placeholder="Манзили">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="phone">Қарздорнинг телефон рақами </label> <span
                                            class="btn-sm btn-primary" style="padding:1px 6px 3px 5px; cursor: pointer"
                                            id="add-input">+</span>
                                        <div id="input-container">
                                            @forelse(explode(',', $data->phone) as $key => $phone)
                                                <div class="input-group mt-1" id="group_{{ $key+1 }}">
                                                    <div class="input-group-text" style="cursor: pointer"
                                                         onclick="removeInput('{{$key+1}}')"><i class="fa fa-minus"></i>
                                                    </div>
                                                    <input id="phone-{{ $key+1 }}" type="text" name="phone[]"
                                                           class="form-control" value="{{ $phone }}"
                                                           placeholder="XXYYYYYYY" required>
                                                </div>
                                            @empty
                                            @endforelse
                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="type">Қарздор шакли</label>
                                        <select class="form-select" name="type" id="type" required>
                                            <option value="">*** Шахс танланг ***</option>
                                            <option value="0" @selected($data->type == 0)>Жисмоний шахс</option>
                                            <option value="1" @selected($data->type == 1)>Юридик шахс</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12"></div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="inn">ИНН</label>
                                        <input type="text" class="form-control" id="inn" name="inn" value="{{ isset($data->id) ? $data->inn : old('inn') }}" placeholder="ИНН">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="mfo">МФО</label>
                                        <input type="text" class="form-control" id="mfo" name="mfo" value="{{ isset($data->id) ? $data->mfo : old('mfo') }}" placeholder="МФО">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="account_number">Ҳисоб рақам</label>
                                        <input type="text" class="form-control" id="account_number" name="account_number" value="{{ isset($data->id) ? $data->account_number : old('account_number') }}" placeholder="Ҳисоб рақам">
                                    </div>
                                </div>
                                {{-- add dynamical input --}}
                                <div class="form-group mt-3">
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
