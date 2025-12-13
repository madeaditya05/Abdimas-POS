@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title','Daftar Akun (COA)')

@section('content')
  <div class="card">
    <h2 style="margin:0 0 12px 0;">Edit Daftar Akun (COA)</h2>

    {{-- PENTING: path include ikut folder bener --}}
    @include('chart-of-accounts._form', ['mode' => 'edit'])
  </div>
@endsection
