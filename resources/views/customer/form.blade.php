@extends('layouts.main')
@section('title', $mode === 'create' ? 'Create Customer' : 'Edit Customer')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">
@endpush

@section('content')
@php
  $nama = old('name', $row->name ?? '');
  $minBeli = old('discount_min_transactions', $row->discount_min_transactions ?? 10);
  $diskonPersen = old('discount_percent', $row->discount_percent ?? 0);
@endphp

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
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" class="hi hi-5"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
          </span>
        </div>

        <div class="form-help">
          Nama ini akan muncul di transaksi / order yang terkait.
        </div>
      </div>

      <div class="form-field">
        <label>Minimal Pembelian Diskon <span style="color:#ef4444">*</span></label>

        <input
          type="number"
          class="form-input"
          name="discount_min_transactions"
          value="{{ $minBeli }}"
          min="1"
          step="1"
          required
          placeholder="Contoh: 10">

        <div class="form-help">
          Customer mulai dapat diskon jika riwayat pembeliannya sudah mencapai angka ini.
        </div>
      </div>

      <div class="form-field">
        <label>Diskon Customer (%) <span style="color:#ef4444">*</span></label>

        <input
          type="number"
          class="form-input"
          name="discount_percent"
          value="{{ $diskonPersen }}"
          min="0"
          max="99.99"
          step="0.01"
          required
          placeholder="Contoh: 5">

        <div class="form-help">
          Isi 0 jika customer ini belum punya diskon.
        </div>
      </div>

      @if($mode === 'edit')
        <div class="form-field span-2">
          <label>Riwayat Pembelian</label>
          <div class="form-input" style="background:#f8fafc;">
            {{ number_format((int) $row->purchase_count, 0, ',', '.') }} kali
          </div>
          <div class="form-help">
            Angka ini dihitung dari transaksi penjualan yang memakai customer ini.
          </div>
        </div>
      @endif
    </div>
  </div>

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
