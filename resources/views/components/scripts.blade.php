<!-- JAVASCRIPT -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{--<script src="/assets/libs/jquery/jquery.min.js"></script>--}}

<script src="/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/assets/libs/metismenu/metisMenu.min.js"></script>
<script src="/assets/libs/simplebar/simplebar.min.js"></script>
<script src="/assets/libs/node-waves/waves.min.js"></script>
<script src="/assets/libs/waypoints/lib/jquery.waypoints.min.js"></script>
<script src="/assets/libs/jquery.counterup/jquery.counterup.min.js"></script>

<!--Moment js-->
<script src="{{asset('assets/libs/moment/min/moment.min.js')}}"></script>

@if(\Request::segment(1) == 'dashboard')
<!-- apexcharts -->
<script src="/assets/libs/apexcharts/apexcharts.min.js"></script>
<script src="/assets/js/pages/dashboard.init.js"></script>
@endif
<!-- App js -->
<script src="/assets/js/app.js"></script>

@stack('modals')

@yield('script')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function () {
        @if(session()->has('message'))
        notify("{{ session()->get('message') }}", "success");
        @endif

        @if(session()->has('errors'))
        @foreach(json_decode(session()->get('errors')) as $key => $value)
        notify("{{ $value[0] }}", "error");
        @endforeach
        @endif
    });

    function notify(message, type) {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            preventDuplicates: true,
            positionClass: "toast-top-right",
            timeOut: 3000, // Set the duration for the toast notification
        };

        if (type == 'success') {
            toastr.success(message, "Success");
        } else if (type == 'error') {
            toastr.error(message, "Error");
            // set time out
            toastr.options.timeOut = 7000;
        } else if (type == 'warning') {
            toastr.warning(message, "Warning");
        } else {
            toastr.info(message, type);
        }
    }

    function dataRange(id, start, end){

        function cb(start, end) {
            $('#'+id+' span').html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
        }

        $('#'+id).daterangepicker({
            'locale': {
                'applyLabel': 'Приминить', 'cancelLabel': 'Отмена', 'fromLabel': 'От', 'toLabel': 'До',
                'customRangeLabel': 'Другой период', 'daysOfWeek': ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                'monthNames': ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сетнятбрь',
                    'Октябрь', 'Ноябрь', 'Декабрь'],
            },
            startDate: start, endDate: end,
            ranges: {
                'Сегодня': [moment(), moment()],
                'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'За последнюю неделю': [moment().subtract(6, 'days'), moment()],
                'Последние 30 дней': [moment().subtract(29, 'days'), moment()],
                'Последние 45 дней': [moment().subtract(44, 'days'), moment()],
                'Текущий месяц': [moment().startOf('month'), moment().endOf('month')],
                'Предыдущий месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Текущий год': [moment().startOf('year'), moment()],
                'Весь период': ['01/01/1970', moment()],
            }
        }, cb);
        cb(start, end);
        $('#'+id).on('apply.daterangepicker', (e, picker) => {
            start = picker.startDate;
            end = picker.endDate;
            cb(start, end);
            table.search('').draw();
        });

    }

</script>
