@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="bg-light p-4 rounded">
            <h2>Edit permission</h2>
            <div class="lead">
                Editing permission.
            </div>

            <div class="container mt-4">

                <form method="POST" action="/permissions/{{ $permission->id }}">
                    @method('patch')
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input value="{{ $permission->name }}"
                               type="text"
                               class="form-control"
                               name="name"
                               placeholder="Name" required>

                        @if ($errors->has('name'))
                            <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary">Save permission</button>
                    <a href="/permissions" class="btn btn-default">Back</a>
                </form>
            </div>

        </div>
    </div>

@endsection
