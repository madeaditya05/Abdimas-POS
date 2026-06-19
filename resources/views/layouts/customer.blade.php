<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Pembayaran') - Pasta Nafisa</title>

  {{-- CSS khusus layar customer, senada dashboard kasir --}}
  <link rel="stylesheet" href="{{ asset('assets/customer.css') }}">
</head>
<body class="cust-body">
  @yield('content')
</body>
</html>
