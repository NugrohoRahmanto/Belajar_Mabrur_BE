<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin BM')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="mb-4 navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Belajar Mabrur</a>
            @auth
                <div class="d-flex">
                    <a href="{{ route('logout') }}" class="btn btn-outline-light btn-sm">Logout</a>
                </div>
            @endauth
        </div>
    </nav>

    <div class="container">
        @yield('content')
    </div>

</body>
</html>
