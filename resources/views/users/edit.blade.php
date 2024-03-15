@extends('layouts.app')


@section('content')

    <link href="/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <script src="http://10.49.0.27/assets/libs/select2/js/select2.min.js"></script>

    <div class="container">

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h2>Edit New User</h2>
                <a class="btn btn-primary" href="{{ route('users.index') }}"> Back </a>
            </div>
            <div class="card-body">
                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> Something went wrong.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                {!! Form::model($user, ['method' => 'PATCH','route' => ['users.update', $user->id]]) !!}
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Name:</strong>
                            {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Email:</strong>
                            {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Telefon:</strong>
                            {!! Form::text('phone', null, array('placeholder' => 'Telefon','class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Password:</strong>
                            {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Confirm Password:</strong>
                            {!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Region:</strong>
                            <select class="select2 form-control select2-multiple" multiple="multiple" name="region_id[]" required data-placeholder="Танлаш ...">
                                <option value="">***Танланг***</option>
                                @foreach(\App\Models\Region::all() as $r)
                                    <option value="{{ $r->id }}" @selected(in_array($r->id,explode(',',$user->region_id)))>{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @role('Admin')
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Role:</strong>
                            {!! Form::select('roles[]', $roles,$userRole, array('class' => 'select2 form-control','multiple')) !!}
                        </div>
                    </div>
                    @endrole
                    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection
