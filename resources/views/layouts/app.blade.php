<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Digital Twin — Sungai Brantas, Kota Batu')</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
  @yield('content')

  @stack('scripts')
</body>
</html>
