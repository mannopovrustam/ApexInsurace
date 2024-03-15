@extends('layouts.app')

@section('script')

<script>

function currentMonth(){

    var currentMonth = $('#currentMonth').val();
    window.location.href = '/dashboard?currentMonth='+currentMonth;

}

</script>

@endsection

@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Dashboard</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Oyni tanlash</a></li>
                            <li class="breadcrumb-item active">


<input type="date" onchange="currentMonth()" id="currentMonth" value="{{isset(request()->currentMonth) ? request()->currentMonth : ''}}">

			    </li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">


            <div class="col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-body d-flex justify-content-between">
                        <div style="width: 40%">
                            <h4 class="mb-1 mt-1"><span data-plugin="counterup">{{ $report1a }}</span></h4>
                            <p class="mb-0">Жами хужжатлар сони</p>
                        </div>
                        <div style="width: 40%;">
                            <h4 class="mb-1 mt-1"><span>{{ number_format($report1b,2,"."," ") }}</span></h4>
                            <p class="mb-0">Жами хужжатлар суммаси</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-body d-flex justify-content-between">
                        <div style="width: 40%">
                            <h4 class="mb-1 mt-1"><span data-plugin="counterup">{{ $report2a }}</span></h4>
                            <p class="mb-0">Жами ундирилган хужжатлар сони</p>
                        </div>
                        <div style="width: 40%;">
                            <h4 class="mb-1 mt-1"><span>{{ number_format($report2b,2,"."," ") }}</span></h4>
                            <p class="mb-0">Жами ундирилган хужжатлар суммаси</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-body d-flex justify-content-between">
                        <div style="width: 40%">
                            <h4 class="mb-1 mt-1"><span data-plugin="counterup">{{ $report3a }}</span></h4>
                            <p class="mb-0">Жами қолдиқ хужжатлар сони</p>
                        </div>
                        <div style="width: 40%;">
                            <h4 class="mb-1 mt-1"><span>{{ number_format($report3b,2,"."," ") }}</span></h4>
                            <p class="mb-0">Жами қолдиқ хужжатлар суммаси</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-body d-flex justify-content-between">
                        <div style="width: 40%">
                            <h4 class="mb-1 mt-1"><span data-plugin="counterup">{{ $report4a }}</span></h4>
                            <p class="mb-0">Жорий ойда ундирилган хужжатлар сони</p>
                        </div>
                        <div style="width: 40%;">
                            <h4 class="mb-1 mt-1"><span>{{ number_format($report4b,2,"."," ") }}</span></h4>
                            <p class="mb-0">Жорий ойда ундирилган хужжатлар суммаси</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-body d-flex justify-content-between">
                        <div style="width: 40%">
                            <h4 class="mb-1 mt-1"><span data-plugin="counterup">{{ $report5a }}</span></h4>
                            <p class="mb-0">Жорий ойда рег қилинган хужжатлар сони</p>
                        </div>
                        <div style="width: 40%;">
                            <h4 class="mb-1 mt-1"><span>{{ number_format($report5b,2,"."," ") }}</span></h4>
                            <p class="mb-0">Жорий ойда рег қилинган хужжатлар суммаси</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card">
                    <div class="card-body d-flex justify-content-between">
                        <div style="width: 40%">
                            <h4 class="mb-1 mt-1"><span data-plugin="counterup">{{ $report6a }}</span></h4>
                            <p class="mb-0">Жорий кунда ундирилган ҳужжатлар сони</p>
                        </div>
                        <div style="width: 40%;">
                            <h4 class="mb-1 mt-1"><span>{{ number_format($report6b,2,"."," ") }}</span></h4>
                            <p class="mb-0">Жорий кунда ундирилган ҳужжатлар суммаси</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Иш туркуми бўйича тушум</h4>
                    </div>
                    <div class="card-body">
                        <div data-simplebar style="max-height: 339px;">
                            <div class="table-responsive">
                                <table class="table table-borderless table-centered table-nowrap">
                                    <thead class="thead-light">
                                    <?php \Carbon\Carbon::setLocale('uz');
					$currentC = \Carbon\Carbon::now();
					if(isset(request()->currentMonth)) $currentC =  \Carbon\Carbon::parse(request()->currentMonth);
 ?>
                                    <tr>
                                        <th>Иш туркуми</th>
                                        <th>Ҳужжат сони ({{ $currentC->translatedFormat('F') }}) <br><span class="text-muted">ёпилган</span></th>
                                        <th>Тушум ({{ $currentC->translatedFormat('F') }})</th>
                                        <th>Ҳужжат сони ({{ $currentC->translatedFormat('Y') }} йил) <br><span class="text-muted">ёпилган</span></th>
                                        <th>Тушум ({{ $currentC->translatedFormat('Y') }} йил)</th>
                                        <th>Ҳужжат сони</th>
                                        <th>Тушум</th>
                                    </tr>
                                    <tbody>
                                    <?php
					$current = date('Y-m-d');
					if(isset(request()->currentMonth)) $current = request()->currentMonth;
                                        $categories = \DB::table('categories as cat')
                                            ->select('cat.permission', 'cat.name',
                                                \DB::raw("sum(if((YEAR(c.closed_at) = YEAR('$current')) && (MONTH(c.closed_at) = MONTH('$current')), 1, 0)) as count_month"),
                                                \DB::raw("sum(if((YEAR(cp.date) = YEAR('$current')) && (MONTH(cp.date) = MONTH('$current')), cp.amount, 0)) as sum_month"),
                                                \DB::raw("sum(if(YEAR(c.closed_at) = YEAR('$current'), 1, 0)) as count_year"),
                                                \DB::raw("sum(if(YEAR(cp.date) = YEAR('$current'), cp.amount, 0)) as sum_year"),
                                                \DB::raw("count(c.id) as count"),
                                                \DB::raw("sum(cp.amount) as sum")
                                            )
                                            ->leftjoin('contracts as c', 'cat.id', '=', 'c.category_id')
                                            ->leftjoin('contract_payments as cp', 'c.id', '=', 'cp.contract_id')
                                            ->where('c.category_id', '!=',null)
                                            ->groupBy('cat.id')
                                            ->get();
                                    ?>
                                    @foreach($categories as $category)
                                        @can($category->permission)
                                            <tr>
                                                <td><h6 class="font-size-15 mb-1 fw-normal">{{ $category->name }}</h6></td>
                                                <td class="text-primary fw-semibold">{{ number_format($category->count_month,0,".",",") }}</td>
                                                <td class="text-primary fw-semibold">{{ number_format($category->sum_month,2,"."," ") }} Сўм</td>
                                                <td class="text-primary fw-semibold">{{ number_format($category->count_year,0,".",",") }}</td>
                                                <td class="text-primary fw-semibold">{{ number_format($category->sum_year,2,"."," ") }} Сўм</td>
                                                <td class="text-primary fw-semibold text-end">{{ number_format($category->count,0,".",",") }}</td>
                                                <td class="text-primary fw-semibold text-end">{{ number_format($category->sum,2,"."," ") }} Сўм</td>
                                            </tr>
                                        @endcan
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Ҳодимларнинг жорий ойда ундирган пуллари</h4>
                    </div>
                    <div class="card-body">
                        <div data-simplebar style="max-height: 339px;">
                            <div class="table-responsive">
                                <table class="table table-borderless table-centered table-nowrap">
                                    <thead class="thead-light">
                                    <?php \Carbon\Carbon::setLocale('uz'); ?>
                                    <tr>
                                        <th>Ҳодим Исми</th>
                                        <th>Ундирилган сумма</th>
                                    </tr>
                                    <tbody>
                                    <?php
					$current = date('Y-m-d');
					if(isset(request()->currentMonth)) $current = request()->currentMonth;

                                        $users = \DB::table('contract_payments as cp')
                                            ->select('u.name', \DB::raw("ifnull(sum(cp.amount), 0) as sum"))
                                            ->join('users as u', function($join) use ($current){
                                                $join->on('cp.user_id', '=', 'u.id');
                                                $join->on(\DB::raw("YEAR(cp.date)"),'=',DB::raw("YEAR('$current')"));
                                                $join->on(\DB::raw("MONTH(cp.date)"),'=',DB::raw("MONTH('$current')"));
                                            })
		                            ->where('cp.type', '=', \DB::raw("2"))
                                            ->groupBy('u.id')->get();

					    $user_amount = 0;

                                    ?>
                                    @foreach($users as $user)
                                            <tr>
                                                <td><h6 class="font-size-15 mb-1 fw-normal">{{ $user->name }}</h6></td>
                                                <td class="text-primary fw-semibold">{{ number_format($user->sum,2,"."," ") }} Сўм  @php $user_amount += $user->sum; @endphp</td>
                                            </tr>
                                    @endforeach
                                    <tr>
                                        <td><h6 class="font-size-15 mb-1 fw-normal"><b>Жами:</b></h6></td>
                                        <td class="text-primary fw-semibold">{{ number_format($user_amount,2,"."," ") }} Сўм</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Ҳодим иш юритувидаги </h4>
                    </div>
                    <div class="card-body">
                        <?php
                            $employees = \DB::table('contracts as c')
                                ->select('u.name', \DB::raw('count(*) as count'), \DB::raw('sum(c.amount + IFNULL(c.tax, 0) + IFNULL(c.expense, 0)) - sum(c.amount_paid) as amount'))
                                ->join('users as u', 'c.user_id', '=', 'u.id')
                                ->where('c.status', '!=', 10)
                                ->where('c.status', '!=', 6)
                                ->groupBy('u.id')
                                ->get();
                        ?>

                        <div class="table-responsive">
                            <table class="table table-borderless table-centered table-nowrap">
                                <thead class="thead-light">
                                <tr>
                                    <th>Ҳодим Исми</th>
                                    <th>Қолдиқ сони</th>
                                    <th>Қолдиқ сумма</th>
                                </tr>
                                </thead>
                                <tbody>
				@php $employee_count = 0; $employee_amount = 0;  @endphp
                                @foreach($employees as $employee)
                                    <tr>
                                        <td><h6 class="font-size-15 mb-1 fw-normal">{{ $employee->name }}</h6></td>
                                        <td class="text-primary fw-semibold">{{ number_format($employee->count,0,".",",") }} @php $employee_count += $employee->count; @endphp</td>
                                        <td class="text-primary fw-semibold">{{ number_format($employee->amount,2,"."," ") }} Сўм  @php $employee_amount += $employee->amount; @endphp</td>
                                    </tr>
                                @endforeach
                                    <tr>
                                        <td><h6 class="font-size-15 mb-1 fw-normal"><b>Жами:</b></h6></td>
                                        <td class="text-primary fw-semibold">{{ number_format($employee_count,0,".",",") }}</td>
                                        <td class="text-primary fw-semibold">{{ number_format($employee_amount,2,"."," ") }} Сўм</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5 col-xl-5">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Тўлов тизимлари бўйича</h4>
                    </div>
                    <div class="card-body">
                        <div data-simplebar style="max-height: 339px;">
                            <div class="table-responsive">
                                <table class="table table-borderless table-centered table-nowrap">
                                    <thead class="thead-light">
                                        <?php \Carbon\Carbon::setLocale('uz'); ?>
                                    <tr>
                                        <th>Тўлов тизими</th>
                                        <th>Жорий ойда</th>
                                        <th>Жорий кунда</th>
                                    </tr>
                                    <tbody>
                                        <?php
                                        $current = date('Y-m-d');
                                        $paymentM = $paymentD = 0;
                                        if(isset(request()->currentMonth)) $current = request()->currentMonth;
                                        $paymentMonth = \DB::table('contract_payments as cp')->select(
                                            \DB::raw('sum(if(auto_pay_trans_id is not null, amount, 0)) as emit'),
                                            \DB::raw('sum(if(uni_trans_id is not null, amount, 0)) as uni')
                                        )->where('cancelled', 0)->where(\DB::raw('month(date)'), date('m', strtotime($current)))
                                            ->where(\DB::raw('year(date)'), date('Y', strtotime($current)))->first();
                                        /* get current date */
                                        $paymentDate = \DB::table('contract_payments as cp')->select(
                                            \DB::raw('sum(if(auto_pay_trans_id is not null, amount, 0)) as emit'),
                                            \DB::raw('sum(if(uni_trans_id is not null, amount, 0)) as uni')
                                        )->where('cancelled', 0)->where('date', $current)->first();
                                        ?>
                                        <tr>
                                            <td>EMIT</td>
                                            <td>{{ number_format($paymentMonth->emit,2,"."," ") }} <?php $paymentM += $paymentMonth->emit; ?></td>
                                            <td>{{ number_format($paymentDate->emit,2,"."," ") }} <?php $paymentD += $paymentDate->emit; ?></td>
                                        </tr>
                                        <tr>
                                            <td>UNIACCESS</td>
                                            <td>{{ number_format($paymentMonth->uni,2,"."," ") }} <?php $paymentM += $paymentMonth->uni; ?></td>
                                            <td>{{ number_format($paymentDate->uni,2,"."," ") }} <?php $paymentD += $paymentDate->uni; ?></td>
                                        </tr>

                                    <tr>
                                        <td><h6 class="font-size-15 mb-1 fw-normal"><b>Жами:</b></h6></td>
                                        <td class="text-primary fw-semibold">{{ number_format($paymentM,2,"."," ") }} Сўм</td>
                                        <td class="text-primary fw-semibold">{{ number_format($paymentD,2,"."," ") }} Сўм</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



        </div>

    </div>
@endsection
