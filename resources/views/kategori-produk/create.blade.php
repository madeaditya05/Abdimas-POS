@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title', 'Create Kategori Produk')

@section('content')
<div class="card">
  <div class="card-header">
    <h2 style="margin:0;">Create Kategori Produk</h2>
  </div>

  @include('kategori-produk._form', [
      'row' => $row,
      'mode' => 'create',
  ])
</div>
@endsection
