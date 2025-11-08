<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>@yield('title', 'Promo Display')</title>
  <style>html,body{height:100%;margin:0}</style>
  @stack('styles')
</head>
<body>
  @yield('content')
  @stack('scripts')
</body>
</html>
