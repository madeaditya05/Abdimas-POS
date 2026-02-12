@extends('layouts.main')
@section('title', 'Laporan Pembelian Bahan')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/bahanbaku.css') }}">

  <style>
    .summary-box {
      margin-top:20px;
      padding:16px;
      border-radius:8px;
      background:#f8f8f8;
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
      gap:12px;
    }

    .summary-item {
      background:white;
      padding:12px;
      border-radius:6px;
      box-shadow:0 1px 3px rgba(0,0,0,0.05);
    }

    .summary-label {
      font-size:13px;
      color:#777;
      margin-bottom:4px;
    }

    .summary-value {
      font-size:16px;
      font-weight:600;
    }

    nav[role="navigation"] svg {
      width:16px !important;
      height:16px !important;
    }
  </style>
@endpush

@section('content')
<div class="card">

  {{-- HEADER --}}
  <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Laporan Pembelian Bahan</h2>
  </div>

  {{-- FILTER --}}
  <form method="GET"
        action="{{ route('pembelian-bahan-detail.index') }}"
        class="filter-bar"
        style="margin:12px 0;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">

    <input type="date"
           name="start_date"
           value="{{ request('start_date') }}"
           required>

    <input type="date"
           name="end_date"
           value="{{ request('end_date') }}"
           required>

    <select name="bahan_id" class="form-select">
      <option value="">Semua Bahan</option>

      @foreach ($bahanList as $bahan)
        <option value="{{ $bahan->id }}"
          {{ request('bahan_id') == $bahan->id ? 'selected' : '' }}>
          {{ $bahan->kode_bahan }} - {{ $bahan->nama_bahan }}
        </option>
      @endforeach
    </select>


    <button class="btn btn--outline-coffee">
      Terapkan
    </button>

    <a href="{{ route('pembelian-bahan-detail.index') }}"
       class="btn btn--outline-coffee">
      Reset
    </a>
  </form>

  {{-- TABLE --}}
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>ID Bahan</th>
          <th>Nama Bahan</th>
          <th class="num">Qty</th>
          <th class="num">Avg Harga</th>
          <th class="num">Total</th>
        </tr>
      </thead>

      <tbody>
        @forelse ($pembelianDetail['rows'] as $row)
          <tr>
            <td>{{ $row->tanggal }}</td>
            <td>{{ $row->bahan_baku_id }}</td>
            <td>{{ $row->nama_bahan }}</td>

            <td class="num">
              {{ number_format((float)$row->qty, 2, ',', '.') }}
            </td>

            <td class="num">
              Rp {{ number_format((float)$row->avg_harga, 0, ',', '.') }}
            </td>

            <td class="num">
              Rp {{ number_format((float)$row->total, 0, ',', '.') }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="text-align:center;">
              Belum ada data pembelian pada periode ini.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- SUMMARY --}}
  <div class="summary-box">

    <div class="summary-item">
      <div class="summary-label">Grand Total Pembelian</div>
      <div class="summary-value">
        Rp {{ number_format($pembelianDetail['stats']['grand_total'], 0, ',', '.') }}
      </div>
    </div>

    <div class="summary-item">
      <div class="summary-label">Harga Terendah</div>
      <div class="summary-value">
        Rp {{ number_format($pembelianDetail['stats']['min'], 0, ',', '.') }}
      </div>
    </div>

    <div class="summary-item">
      <div class="summary-label">Harga Tertinggi</div>
      <div class="summary-value">
        Rp {{ number_format($pembelianDetail['stats']['max'], 0, ',', '.') }}
      </div>
    </div>

    <div class="summary-item">
      <div class="summary-label">Harga Rata-rata</div>
      <div class="summary-value">
        Rp {{ number_format($pembelianDetail['stats']['avg'], 0, ',', '.') }}
      </div>
    </div>

  </div>

</div>
@endsection
