@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title', 'Create Produk')

@section('content')
<div class="card">
  <div class="card-header">
    <h2 style="margin:0;">Create Produk</h2>
  </div>

  @include('produk._form', [
      'produk'          => $produk,
      'mode'            => 'create',
      'kategoriOptions' => $kategoriOptions,
  ])
</div>
@endsection
