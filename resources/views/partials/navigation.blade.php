<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
<div class="container-fluid">
    <span class="navbar-text">{{ env('APP_NAME') }}</span>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <!-- Left Side Of Navbar -->
        <ul class="navbar-nav mr-auto"></ul>
        <!-- Right Side Of Navbar -->
        <ul class="navbar-nav ml-auto">
            <!-- Authentication Links -->
            @include('partials.navigations.'.\App\User::navigation())
        </ul>
    </div>    
</div>
</nav>