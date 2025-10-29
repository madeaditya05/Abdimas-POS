<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Cofit EV</title>
    <link rel="stylesheet" href="{{ asset('assets/styles.css') }}">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">
  @csrf
</form>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            @include('layouts.header')

            <!-- Konten Halaman -->
            <main class="dashboard-main">
                @yield('content')
            </main>

            <!-- Footer -->
            @include('layouts.script')
        </div>
    </div>
</body>
</html>
