@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/product.css') }}">
@endpush

@extends('layouts.main')
@section('title','Tambah Produk')

@section('content')
<div class="product-admin">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <h2 class="page-title mb-3">Tambah Produk</h2>

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif

        <form action="{{ route('product.store') }}" method="POST" class="mt-2">
          @csrf
          <div class="mb-3">
            <label class="form-label">Nama Produk</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">SKU</label>
            <input type="text" name="sku" class="form-control" value="{{ old('sku') }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Harga (Rp)</label>
            <input type="number" name="price" class="form-control" min="0" value="{{ old('price') }}" required>
          </div>
          

          <div class="form-actions">
            <button class="btn btn-primary" type="submit">Simpan</button>
            <a class="btn btn-light" href="{{ route('product.index') }}">Batal</a>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>
@endsection
