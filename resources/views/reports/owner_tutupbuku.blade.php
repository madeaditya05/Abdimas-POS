@extends('layouts.main')
@section('title','Tutup Buku')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/owner_report.css') }}">
@endpush

@section('content')
<div class="kr-page">
  <div class="kr-card">

    <div class="kr-header">
      <div class="kr-title">Tutup Buku (Periodik)</div>
      <div class="kr-chipbar">
        <a class="kr-btn kr-btn-ghost"
           href="{{ route('owner.reports.menu', ['start_date'=>$start, 'end_date'=>$end]) }}">
          Kembali
        </a>
      </div>
    </div>

    <form class="kr-filter" method="GET" action="{{ route('owner.tutupbuku') }}">
      <div class="kr-field">
        <label>Dari</label>
        <input type="date" name="start_date" value="{{ $start }}">
      </div>
      <div class="kr-field">
        <label>Sampai</label>
        <input type="date" name="end_date" value="{{ $end }}">
      </div>
      <div class="kr-actions">
        <button class="kr-btn kr-btn-primary" type="submit">Preview</button>
      </div>
    </form>

    <div class="kr-sep"></div>

    @if($already)
      <div class="kr-section">
        <div class="kr-section-title">Status</div>
        <div class="kr-chip">
          Periode ini sudah ditutup ✅ (closed_at: {{ $already->closed_at }})
        </div>
      </div>
      <div class="kr-sep"></div>
    @endif

    <div class="kr-section">
      <div class="kr-section-title">Ringkasan Closing</div>

      <table class="kr-table">
        <tbody>
          <tr>
            <td>Persediaan Awal (BI)</td>
            <td class="kr-right kr-money">Rp {{ number_format((float)$preview['begin_inv'],0,',','.') }}</td>
          </tr>
          <tr>
            <td>Pembelian (5100)</td>
            <td class="kr-right kr-money">Rp {{ number_format((float)$preview['purchases'],0,',','.') }}</td>
          </tr>
          <tr>
            <td>Persediaan Akhir (EI)</td>
            <td class="kr-right kr-money">Rp {{ number_format((float)$preview['end_inv'],0,',','.') }}</td>
          </tr>
          <tr>
            <td><b>HPP (BI + Purchases - EI)</b></td>
            <td class="kr-right kr-money"><b>Rp {{ number_format((float)$preview['cogs'],0,',','.') }}</b></td>
          </tr>
        </tbody>
      </table>

      <div style="margin-top:14px; display:flex; gap:10px;">
        @if($already)
          <button class="kr-btn kr-btn-ghost" type="button" disabled
                  style="opacity:.7; cursor:not-allowed;">
            Periode sudah ditutup ✅
          </button>
        @else
          <form method="POST" action="{{ route('owner.tutupbuku.close') }}">
            @csrf
            <input type="hidden" name="start_date" value="{{ $start }}">
            <input type="hidden" name="end_date" value="{{ $end }}">
            <button class="kr-btn kr-btn-primary" type="submit">
              Tutup Buku Sekarang
            </button>
          </form>
        @endif
      </div>
    </div>

    <div class="kr-sep"></div>

    <div class="kr-section">
      <div class="kr-section-title">Detail Persediaan Akhir (Average Cost)</div>
      <table class="kr-table">
        <thead>
          <tr>
            <th>Bahan ID</th>
            <th class="kr-right">Qty On Hand</th>
            <th class="kr-right">Avg Cost</th>
            <th class="kr-right">Value</th>
          </tr>
        </thead>
        <tbody>
          @forelse(($preview['detail'] ?? []) as $d)
            <tr>
              <td>{{ $d['bahan_baku_id'] }}</td>
              <td class="kr-right">{{ number_format((float)$d['qty_on_hand'],4,',','.') }}</td>
              <td class="kr-right">Rp {{ number_format((float)$d['avg_cost'],2,',','.') }}</td>
              <td class="kr-right">Rp {{ number_format((float)$d['value'],2,',','.') }}</td>
            </tr>
          @empty
            <tr><td colspan="4">Belum ada data persediaan akhir yang bisa dihitung.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection
