@extends('layouts.main')

@section('title', 'Rekonsiliasi Kas')

@php
  $rupiah = function ($n) {
    $n = (float) ($n ?? 0);
    return 'Rp ' . number_format($n, 0, ',', '.');
  };
@endphp

@section('content')
  <div class="card" style="max-width:720px; margin:0 auto;">
    <div class="card-header">
      <div class="card-title">
        <h3>Rekonsiliasi Kas sebelum Logout</h3>
        <p>Bandingkan uang fisik di laci dengan total CASH di aplikasi.</p>
      </div>
    </div>

    <div class="card-body">
      @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom:12px;">
          <ul style="margin:0; padding-left:18px;">
            @foreach ($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div style="display:grid; gap:8px; margin-bottom:14px; font-size:14px;">
        <div><b>Periode</b>: {{ $rangeStart->format('d/m/Y H:i') }} s/d {{ $rangeEnd->format('d/m/Y H:i') }}</div>
        <div><b>Transaksi CASH (lunas)</b>: {{ (int) $cashCount }} transaksi</div>
        <div><b>Total CASH di aplikasi</b>: {{ $rupiah($appCashTotal) }}</div>
        <div class="text-muted"><b>Total non-cash</b>: {{ $rupiah($nonCashTotal) }} (info saja)</div>
      </div>

      <form method="POST" action="{{ route('logout.reconcile.store') }}">
        @csrf

        <div style="display:grid; gap:8px; margin-bottom:12px;">
          <label for="physical_cash" style="font-weight:600;">Uang fisik di kas (Rp)</label>
          <input id="physical_cash" name="physical_cash" type="number" min="0" step="0.01"
                 value="{{ old('physical_cash') }}"
                 class="form-input" placeholder="Contoh: 150000">
          <small class="text-muted">Isi jumlah uang yang benar-benar ada di laci kas.</small>
        </div>

        <div style="display:grid; gap:8px; margin-bottom:16px;">
          <label for="notes" style="font-weight:600;">Catatan (opsional)</label>
          <input id="notes" name="notes" type="text" maxlength="255" value="{{ old('notes') }}"
                 class="form-input" placeholder="Misal: ada uang receh, kekurangan, dll">
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end;">
          <a href="{{ route('dashboard') }}" class="btn" style="text-decoration:none;">Batal</a>
          <button type="submit" class="btn btn-primary">Simpan & Logout</button>
        </div>
      </form>
    </div>
  </div>
@endsection

