<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Acceso reestringido</title>
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
                    <h4><i class="fa fa-ban"></i> {{ __(" Acceso reestringido") }}</h4>
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ _("No tienes acceso a este sitio") }}</h5>
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">  
                        {{ __('Salir') }}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>