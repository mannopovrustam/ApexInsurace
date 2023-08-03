@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Dashboard</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Minible</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
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


        </div>

    </div>
@endsection
