@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@extends('layouts.main')
@section('title', $mode === 'create' ? 'Create Customer' : 'Edit Customer')

@section('content')
@php
  $nama = old('name', $row->name ?? '');
@endphp

{{-- ERROR BOX --}}
@if ($errors->any())
  <div class="form-section" style="border-color:#fecaca;background:#fff1f2;">
    <strong>Periksa kembali:</strong>
    <ul style="margin:6px 0 0 18px;">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST"
      action="{{ $mode === 'create' ? route('customer.store') : route('customer.update', $row) }}"
      class="form">
  @csrf
  @if($mode === 'edit')
    @method('PUT')
  @endif

  {{-- DATA CUSTOMER --}}
  <div class="form-section">
    <div class="form-title">
      {{ $mode === 'create' ? 'Customer Baru' : 'Edit Customer' }}
    </div>

    <div class="form-grid">
      <div class="form-field span-2">
        <label>Nama Customer <span style="color:#ef4444">*</span></label>

        <div class="field-with-icon">
          <input
            class="form-input"
            name="name"
            value="{{ $nama }}"
            required
            placeholder="Masukkan nama customer">
          <span class="field-icon">
            <x-heroicon-o-user class="hi hi-5" />
          </span>
        </div>

        <div class="form-help">
          Nama ini akan muncul di transaksi / order yang terkait.
        </div>
      </div>
    </div>
  </div>

  {{-- AKSI --}}
  <div class="form-section" style="padding-bottom:0;">
    <div class="form-actions">
      <a class="btn btn--danger" href="{{ route('customer.index') }}">
        Batal
      </a>

      <button type="submit" class="btn btn--success">
        Simpan
      </button>
    </div>
  </div>
</form>
@endsection
