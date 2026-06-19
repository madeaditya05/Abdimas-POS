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
        <label>Pilih Bulan</label>
        <select name="month" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; background-color: #fff; color: #374151; min-width: 180px; font-weight: 500;">
          @foreach($monthsList as $val => $label)
            <option value="{{ $val }}" {{ $selectedMonth == $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="kr-actions">
        <button class="kr-btn kr-btn-primary" type="submit">Preview</button>
        <a class="kr-btn kr-btn-ghost" target="_blank" href="{{ route('owner.tutupbuku.pdf', ['month' => $selectedMonth]) }}">PDF</a>
        <a class="kr-btn kr-btn-ghost" target="_blank" href="{{ route('owner.tutupbuku.excel', ['month' => $selectedMonth]) }}">Excel</a>
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
          <form method="POST" action="{{ route('owner.tutupbuku.reopen', $already) }}" 
                onsubmit="return confirm('Apakah Anda yakin ingin membuka kembali periode tutup buku ini? Jurnal penyesuaian penutupan buku akan otomatis dihapus.');">
            @csrf
            <input type="hidden" name="month" value="{{ $selectedMonth }}">
            <button class="kr-btn" type="submit" 
                    style="background-color:#dc2626; border-color:#dc2626; color:#fff; cursor:pointer;">
              Buka Kembali Periode
            </button>
          </form>
        @else
          <form method="POST" action="{{ route('owner.tutupbuku.close') }}">
            @csrf
            <input type="hidden" name="month" value="{{ $selectedMonth }}">
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
