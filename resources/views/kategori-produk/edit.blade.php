@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title', 'Edit Kategori Produk')

@section('content')
<div class="card">
  <div class="card-header">
    <h2 style="margin:0;">Edit Kategori Produk</h2>
  </div>

  @include('kategori-produk._form', [
      'row' => $row,
      'mode' => 'edit',
  ])
</div>
@endsection
