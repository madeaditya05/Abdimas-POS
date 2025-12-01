@push('styles')
  {{-- sementara pakai CSS bahan baku biar look & feel sama --}}
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title', 'Create Pembelian Bahan')

@section('content')
  @include('Pembelian-Bahan._form', [
      'mode'         => 'create',
      'row'          => $row,
      'bahanOptions' => $bahanOptions,
  ])
@endsection
