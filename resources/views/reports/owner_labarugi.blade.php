@extends('layouts.main')
@section('title','Laporan Laba Rugi')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/jurnal.css') }}">
@endpush

@section('content')
<div class="rp-page">
  <div class="rp-card">
    <div class="rp-header">Laporan Laba Rugi</div>

    <form method="GET" action="{{ route('owner.labarugi') }}" class="rp-filter">
      <div class="rp-field">
        <label>Dari</label>
        <input type="date" name="start_date" value="{{ request('start_date', now()->toDateString()) }}">
      </div>
      <div class="rp-field">
        <label>Sampai</label>
        <input type="date" name="end_date" value="{{ request('end_date', now()->toDateString()) }}">
      </div>
      <div class="rp-actions">
        <button type="submit" class="rp-btn rp-btn-primary">Tampilkan</button>
        <a class="rp-btn rp-btn-ghost" target="_blank"
           href="{{ route('owner.labarugi.pdf', [
                'start_date'=>request('start_date', now()->toDateString()),
                'end_date'=>request('end_date', now()->toDateString())
           ]) }}">
          Download PDF
        </a>
      </div>
    </form>

    <div class="rp-sep"></div>

    <ul class="rp-stats">
      <li><span>Pendapatan</span> <span class="rp-money">Rp {{ number_format($lr['revenue'],0,',','.') }}</span></li>
      <li><span><strong>HPP</strong></span> <span class="rp-money">Rp {{ number_format($lr['cogs'],0,',','.') }}</span></li>
      <li class="rp-emph"><span><strong>Laba Kotor</strong></span> <span class="rp-money">Rp {{ number_format($lr['gross'],0,',','.') }}</span></li>
      <li><span>Beban</span> <span class="rp-money">Rp {{ number_format($lr['expense'],0,',','.') }}</span></li>
      <li class="rp-emph"><span><strong>Laba Bersih</strong></span> <span class="rp-money">Rp {{ number_format($lr['net_income'],0,',','.') }}</span></li>
    </ul>
  </div>
</div>
@endsection
