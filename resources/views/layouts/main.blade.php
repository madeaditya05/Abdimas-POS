<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Pasta Nafisa</title>
    {{-- CSS global --}}
    <link rel="stylesheet" href="{{ asset('assets/styles.css') }}?v={{ filemtime(public_path('assets/styles.css')) }}">


    {{-- Slot untuk CSS halaman (product.css, DataTables, dll) --}}
    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    @php
        $isKasirPanel =
            request()->routeIs('kasir.*')
            || request()->routeIs('kasir.rekap*')
            || request()->is('kasir')
            || request()->is('kasir/*')
            || request()->is('reports/kasir')
            || request()->is('reports/kasir/*');
    @endphp

    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        @auth
            @php
                $panel = session('panel') ?: (auth()->user()->user_group ?? 'owner');
                $panel = in_array($panel, ['owner','kasir'], true) ? $panel : 'owner';
            @endphp

            @if ($isKasirPanel || $panel === 'kasir')
                @include('layouts.sidebar_kasir')
            @else
                @include('layouts.sidebar_owner')
            @endif
        @endauth

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
            @stack('scripts')
        </div>
    </div>
</body>
</html>
