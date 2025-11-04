@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/product.css') }}">
@endpush

@extends('layouts.main')
@section('title','Ubah Produk')

@section('content')
<div class="product-admin">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <h2 class="page-title mb-3">Ubah Produk</h2>

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif

        <form action="{{ route('product.update', $product->id) }}" method="POST" class="mt-2">
          @csrf
          @method('PUT')

          <div class="mb-3">
            <label class="form-label">Nama Produk</label>
            <input type="text" name="name" class="form-control" value="{{ old('name',$product->name) }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">SKU</label>
            <input type="text" name="sku" class="form-control" value="{{ old('sku',$product->sku) }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Harga (Rp)</label>
            <input type="number" name="price" class="form-control" min="0" value="{{ old('price',$product->price) }}" required>
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" {{ old('is_active',$product->is_active) ? 'checked' : '' }}>
            <label for="is_active" class="form-check-label">Aktif</label>
          </div>

          <div class="form-actions">
            <button class="btn btn-primary" type="submit">Ubah</button>
            <a class="btn btn-light" href="{{ route('product.index') }}">Batal</a>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
@endsection
