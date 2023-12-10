@extends('layouts.app')

@section('content')
<div class="container" style="margin-top: 150px;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card text-white bg-dark">
                <div class="card-header text-center"><h6>{{ env('APP_NAME') }}</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group row">
                            <label 
                                for="email" 
                                class="col-md-4 col-form-label text-md-right">
                                {{ __('Usuario') }}
                            </label>
                            <div class="col-md-6">
                                <input 
                                    id="user_name" 
                                    type="string" 
                                    class="form-control @error('user_name') is-invalid @enderror" 
                                    name="user_name" 
                                    value="{{ old('user-name') }}" 
                                    required autocomplete="user_name" 
                                    autofocus>
                                @error('user_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label 
                                for="password" 
                                class="col-md-4 col-form-label text-md-right">
                                {{ __('Contraseña') }}
                            </label>
                            <div class="col-md-6">
                                <input 
                                    id="password" 
                                    type="password" 
                                    class="form-control @error('password') is-invalid @enderror" 
                                    name="password" 
                                    required autocomplete="current-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-light">
                                {{ __('Acceder') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection