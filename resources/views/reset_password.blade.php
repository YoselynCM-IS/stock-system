@extends('layouts.app')

@section('content')
    <div align="center">
        <div class="card col-md-8">
            <div class="card-header">Cambiar contraseña</div>
            <div class="card-body">
                <form method="POST" action="" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="form-group row">
                        <label for="password" class="col-md-4 text-md-right">
                            {{ ("Contraseña") }}
                        </label>
                        <div class="col-md-6">
                            <input 
                                id="password" 
                                name="password" 
                                type="password" 
                                class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" 
                                required 
                            />
                            @if($errors->has('password'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="password-confirm" class="col-md-4 text-md-right">
                            {{ ("Confirma la Contraseña") }}
                        </label>
                        <div class="col-md-6">
                            <input 
                                id="password-confirm" 
                                name="password_confirmation" 
                                type="password" 
                                class="form-control" 
                                required 
                            />
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">
                        {{ ("Cambiar contraseña") }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection