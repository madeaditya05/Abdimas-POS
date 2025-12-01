@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title', 'Edit Pembelian Bahan')

@section('content')
  @include('Pembelian-Bahan._form', [
      'mode'         => 'edit',
      'row'          => $row,
      'bahanOptions' => $bahanOptions,
  ])
@endsection
