@extends('layouts.app')


@section('content')

    <div class="container">
        <div class="card">
            <div class="card-header d-flex align-items-end justify-content-between">
                <h2>Users Management</h2>
                <a class="btn btn-success" href="{{ route('users.create') }}"> Create New User </a>
            </div>
            <div class="card-body">

                @if ($message = Session::get('success'))
                    <div class="alert alert-success">
                        <p>{{ $message }}</p>
                    </div>
                @endif


                <table class="table table-sm table-hover table-bordered table-striped table-nowrap align-middle">
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th width="280px">Action</th>
                    </tr>
                    @foreach ($data as $key => $user)
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if(!empty($user->getRoleNames()))
                                    @foreach($user->getRoleNames() as $v)
                                        {{ $v }}
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                <a class="btn btn-info" href="{{ route('users.show',$user->id) }}">Show</a>
                                <a class="btn btn-primary" href="{{ route('users.edit',$user->id) }}">Edit</a>
                                <button type="button" class="btn btn-outline-danger" data-toggle="modal"
                                        data-target="#deleteUser{{$user->id}}">Delete
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="deleteUser{{$user->id}}" tabindex="1" style="z-index: 1050"
                             role="dialog"
                             aria-labelledby="deleteUser{{$user->id}}" aria-hidden="true">
                            <div class="modal-dialog modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteUser{{$user->id}}">Are you sure delete user:
                                            <br><b>{{$user->name }}</b></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                    <div class="modal-footer">
                                        {!! Form::open(['method' => 'DELETE','route' => ['users.destroy', $user->id],'style'=>'display:inline']) !!}
                                        {!! Form::submit('Delete', ['class' => 'btn btn-outline-danger']) !!}
                                        {!! Form::close() !!}
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endforeach
                </table>


                {!! $data->render() !!}
            </div>
        </div>
    </div>

@endsection
