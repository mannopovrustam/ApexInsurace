@extends('layouts.app')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form action="/projects" method="post" enctype="multipart/form-data"
                                      class="row align-items-end">
                                    {{ csrf_field() }}
                                    <div class="col-md-8">
                                        <div class="form-group m-t-40">
                                            <label for="name" class="form-label">Проект:</label>
                                            <input type="text" id="name" class="form-control" name="name"
                                                   value="{{ $project->name }}"/>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <br>
                                        <button type="submit" class="btn btn-primary">Сохранить</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@endsection
