<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>La sesión ha expirado</title>
        <link rel="dns-prefetch" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    </head>
    <body style="background-color: white;">
        <hr>
        <div align="center">
            <div class="card bg-light mb-3" style="max-width: 38rem;" align="center">
                <div class="card-header">
                    {{ __("La sesión ha expirado") }}</h4>
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ _("Lo sentimos la sesión ha expirado, presiona en Iniciar sesión.") }}</h5>
                    <a href="{{ route('login') }}">  
                        {{ __('Iniciar sesión') }}
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>