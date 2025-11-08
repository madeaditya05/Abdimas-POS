<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Cofit EV</title>
    {{-- CSS global --}}
    <link rel="stylesheet" href="{{ asset('assets/styles.css') }}">


    {{-- Slot untuk CSS halaman (product.css, DataTables, dll) --}}
    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">
  @csrf
</form>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        {{-- BLOK INI KITA MODIFIKASI --}}
        @auth
            @switch(auth()->user()->user_group)
                @case('owner')
                    {{-- Tampilkan sidebar untuk owner --}}
                    @include('layouts.sidebar_owner')
                    @break
                
                @case('kasir')
                    {{-- Tampilkan sidebar untuk kasir --}}
                    @include('layouts.sidebar_kasir')
                    @break

                @default
                    @endswitch
        @endauth
        {{-- AKHIR BLOK MODIFIKASI --}}

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
