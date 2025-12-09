@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title', 'Edit Produk')

@section('content')
<div class="card">
  <div class="card-header">
    <h2 style="margin:0;">Edit Produk</h2>
  </div>

  @include('produk._form', [
      'produk'          => $produk,
      'mode'            => 'edit',
      'kategoriOptions' => $kategoriOptions,
  ])
</div>
@endsection
