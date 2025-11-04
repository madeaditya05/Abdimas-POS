@extends('layouts.main')
@section('title','Rekap Kasir')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/jurnal_kasir.css') }}">
@endpush

@section('content')
<div class="kr-page">
  <div class="kr-card">

    <div class="kr-header">
      <div class="kr-title">Rekap Kasir</div>
      {{-- optional chips ringkas --}}
      <div class="kr-chipbar">
        <div class="kr-chip">Periode:
          {{ \Carbon\Carbon::parse($meta['start'])->format('d/m/Y') }}
          – {{ \Carbon\Carbon::parse($meta['end'])->format('d/m/Y') }}
        </div>
      </div>
    </div>

    <form class="kr-filter" method="GET" action="{{ route('kasir.rekap') }}">
      <div class="kr-field">
        <label>Dari</label>
        <input type="date" name="start_date" value="{{ request('start_date', now()->toDateString()) }}">
      </div>
      <div class="kr-field">
        <label>Sampai</label>
        <input type="date" name="end_date" value="{{ request('end_date', now()->toDateString()) }}">
      </div>
      <div class="kr-actions">
        <button class="kr-btn kr-btn-primary" type="submit">Tampilkan</button>
        <a class="kr-btn kr-btn-ghost" target="_blank"
           href="{{ route('kasir.rekap.pdf', [
              'start_date'=>request('start_date', now()->toDateString()),
              'end_date'=>request('end_date', now()->toDateString())
           ]) }}">
          Download PDF
        </a>
      </div>
    </form>

    <div class="kr-sep"></div>

    {{-- Seksi 1: Per Produk --}}
    <div class="kr-section">
      <div class="kr-section-title">Rekap Per Produk</div>
      @if($items->isEmpty())
        <div class="kr-empty">Tidak ada data pada periode ini.</div>
      @else
        <table class="kr-table">
          <thead>
            <tr>
              <th>Produk</th>
              <th class="kr-right">Qty</th>
              <th class="kr-right">Total</th>
            </tr>
          </thead>
          <tbody>
          @foreach($items as $it)
            <tr>
              <td>{{ $it->name }}</td>
              <td class="kr-right">{{ number_format($it->qty) }}</td>
              <td class="kr-right kr-money">Rp {{ number_format($it->total,0,',','.') }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      @endif
    </div>

    {{-- Seksi 2: Per Metode Pembayaran --}}
    <div class="kr-section">
      <div class="kr-section-title">Rekap Per Metode Pembayaran</div>
      @if($payments->isEmpty())
        <div class="kr-empty">Belum ada pembayaran tersettlement.</div>
      @else
        <table class="kr-table">
          <thead>
            <tr>
              <th>Metode</th>
              <th class="kr-right">Transaksi</th>
              <th class="kr-right">Total</th>
            </tr>
          </thead>
          <tbody>
          @foreach($payments as $p)
            <tr>
              <td>{{ strtoupper($p->pg_payment_type) }}</td>
              <td class="kr-right">{{ number_format($p->trx) }}</td>
              <td class="kr-right kr-money">Rp {{ number_format($p->total,0,',','.') }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <div class="kr-section">
  <div class="kr-section-title">Rekapitulasi Pembayaran (Tunai vs Non-Tunai)</div>
  @php
    $tunai = $payUnified->firstWhere('kategori','Tunai');
    $nontunai = $payUnified->firstWhere('kategori','Non Tunai');
    $sumPay = ($tunai->total ?? 0) + ($nontunai->total ?? 0);
  @endphp
  <table class="kr-table">
    <thead><tr><th>Kategori</th><th class="kr-right">Transaksi</th><th class="kr-right">Total</th></tr></thead>
    <tbody>
      <tr>
        <td>Tunai</td>
        <td class="kr-right">{{ number_format($tunai->trx ?? 0) }}</td>
        <td class="kr-right kr-money">Rp {{ number_format($tunai->total ?? 0,0,',','.') }}</td>
      </tr>
      <tr>
        <td>Non Tunai</td>
        <td class="kr-right">{{ number_format($nontunai->trx ?? 0) }}</td>
        <td class="kr-right kr-money">Rp {{ number_format($nontunai->total ?? 0,0,',','.') }}</td>
      </tr>
      <tr>
        <th>Total</th>
        <th class="kr-right">{{ number_format(($tunai->trx ?? 0) + ($nontunai->trx ?? 0)) }}</th>
        <th class="kr-right kr-money">Rp {{ number_format($sumPay,0,',','.') }}</th>
      </tr>
    </tbody>
  </table>
</div>

{{-- ========== Seksi 3: Jurnal Umum ========== --}}
<div class="kr-section">
  <div class="kr-section-title">Jurnal Umum</div>
  @if($journal->isEmpty())
    <div class="kr-empty">Tidak ada jurnal pada periode ini.</div>
  @else
    @php
      $tDebit  = $journal->sum(fn($r) => (float)$r->debit);
      $tCredit = $journal->sum(fn($r) => (float)$r->credit);
    @endphp
    <table class="kr-table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>No. Jurnal</th>
          <th>Ref</th>
          <th>Akun</th>
          <th class="kr-right">Debit</th>
          <th class="kr-right">Credit</th>
        </tr>
      </thead>
      <tbody>
        @foreach($journal as $j)
          <tr>
            <td>{{ \Carbon\Carbon::parse($j->date)->format('d/m/Y') }}</td>
            <td>{{ $j->entry_no }}</td>
            <td>{{ $j->ref_no }}</td>
            <td>{{ $j->code }} — {{ $j->name }}</td>
            <td class="kr-right kr-money">Rp {{ number_format($j->debit,0,',','.') }}</td>
            <td class="kr-right kr-money">Rp {{ number_format($j->credit,0,',','.') }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th colspan="4">Total</th>
          <th class="kr-right kr-money">Rp {{ number_format($tDebit,0,',','.') }}</th>
          <th class="kr-right kr-money">Rp {{ number_format($tCredit,0,',','.') }}</th>
        </tr>
      </tfoot>
    </table>
  @endif
</div>


{{-- ========== Seksi 4: Buku Besar ========== --}}
<div class="kr-section">
  <div class="kr-section-title">Buku Besar</div>
  @forelse($ledger as $acc => $bag)
    <div class="kr-subcard" style="margin-bottom:12px;">
      <div style="font-weight:700; margin-bottom:6px;">{{ $acc }}</div>
      <table class="kr-table">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>No.</th>
            <th>Keterangan</th>
            <th class="kr-right">Debit</th>
            <th class="kr-right">Credit</th>
            <th class="kr-right">Saldo ({{ $bag['normal']==='DEBIT'?'D':'C' }})</th>
          </tr>
        </thead>
        <tbody>
          @foreach($bag['rows'] as $r)
            <tr>
              <td>{{ \Carbon\Carbon::parse($r['date'])->format('d/m/Y') }}</td>
              <td>{{ $r['entry'] }}</td>
              <td>{{ $r['memo'] ?? '-' }}</td>
              <td class="kr-right kr-money">Rp {{ number_format($r['debit'],0,',','.') }}</td>
              <td class="kr-right kr-money">Rp {{ number_format($r['credit'],0,',','.') }}</td>
              <td class="kr-right kr-money">Rp {{ number_format($r['saldo'],0,',','.') }}</td>
            </tr>
          @endforeach
          <tr>
            <th colspan="3">Total</th>
            <th class="kr-right kr-money">Rp {{ number_format($bag['total_debit'],0,',','.') }}</th>
            <th class="kr-right kr-money">Rp {{ number_format($bag['total_credit'],0,',','.') }}</th>
            <th class="kr-right kr-money">Rp {{ number_format($bag['balance'],0,',','.') }}</th>
          </tr>
        </tbody>
      </table>
    </div>
  @empty
    <div class="kr-empty">Belum ada pergerakan buku besar di periode ini.</div>
  @endforelse
</div>
  </div>
</div>
@endsection
