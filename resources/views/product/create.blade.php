@extends('layoutsbootstrapadmin')

@section('konten')
<div class="body-wrapper">
  <header class="app-header">
    <nav class="navbar navbar-expand-lg navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item d-block d-xl-none">
          <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
            <i class="ti ti-menu-2"></i>
          </a>
        </li>
      </ul>
    </nav>
  </header>

  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Tambah Produk</h5>

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif

        <form action="{{ route('product.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label for="name" class="form-label">Nama Produk</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
          </div>

          <div class="mb-3">
            <label for="sku" class="form-label">SKU</label>
            <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku') }}">
          </div>

          <div class="mb-3">
            <label for="price" class="form-label">Harga (Rp)</label>
            <input type="number" class="form-control" id="price" name="price" min="0" value="{{ old('price') }}" required>
          </div>

          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active',1) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Aktif</label>
          </div>

          <button type="submit" class="btn btn-success">Simpan</button>
          <a href="{{ route('product.index') }}" class="btn btn-dark">Batal</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
