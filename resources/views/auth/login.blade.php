@extends('layouts.master')

@section('body')
    <div class="container mt-auto mb-auto">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h1 class="h4 text-center my-3">Login into your account</h1>
                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            @if ($errors->has('email'))
                                <div class="alert alert-danger">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="email" class="col-form-label">Email</label>
                                <input
                                        id="email"
                                        class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                >
                            </div>
                            <div class="form-group">
                                <label for="password" class="col-form-label">Password</label>
                                <input
                                        id="password"
                                        type="password"
                                        class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        name="password"
                                        required
                                >
                            </div>
                                <div class="form-group">
                                    <button class="btn btn-primary btn-block">Log in</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
