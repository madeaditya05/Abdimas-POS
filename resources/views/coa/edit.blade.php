@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/product.css') }}">
@endpush

@extends('layouts.main')
@section('title','Ubah COA')

@section('content')
<div class="product-admin">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <h2 class="page-title mb-3">Ubah Akun COA</h2>

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif

        <form action="{{ route('coa.update', $coa->id) }}" method="POST" class="form-section">
          @csrf @method('PUT')
          <div class="form-grid">
            <div class="col-6">
              <label class="form-label">Kode Akun</label>
              <input type="text" name="code" class="form-control" value="{{ old('code',$coa->code) }}" required>
            </div>
            <div class="col-6">
              <label class="form-label">Nama Akun</label>
              <input type="text" name="name" class="form-control" value="{{ old('name',$coa->name) }}" required>
            </div>
            <div class="col-6">
              <label class="form-label">Tipe</label>
              <input type="text" name="type" class="form-control" value="{{ old('type',$coa->type) }}" required>
            </div>
            <div class="col-6" style="display:flex; align-items:center; gap:10px; margin-top:30px;">
              <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active',$coa->is_active) ? 'checked':'' }}>
              <label for="is_active" class="form-label" style="margin:0;">Aktif</label>
            </div>
            <div class="col-12 form-actions">
              <button class="btn btn-primary" type="submit">Ubah</button>
              <a class="btn btn-light" href="{{ route('coa.index') }}">Batal</a>
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
@endsection
