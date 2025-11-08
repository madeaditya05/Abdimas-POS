<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Rekap Kasir (PDF)</title>
<style>
  /* ====== PDF safe styles ====== */
  @page { margin: 24px 28px; }
  body{ font-family: Helvetica, Arial, sans-serif; font-size:12px; color:#0f172a; }
  h2{ margin:0 0 6px 0; font-size:16px }
  h3{ margin:14px 0 6px 0; font-size:14px }
  .muted{ color:#6b7280; }
  .badge{ display:inline-block; border:1px solid #c7cdd4; border-radius:14px; padding:3px 8px; font-size:11px; background:#f3f4f6; }
  .right{text-align:right}
  .money{ font-variant-numeric: tabular-nums; letter-spacing:.2px; }
  .mb12{ margin-bottom:12px; }
  .page-break{ page-break-before: always; }

  /* ====== Table with full grid lines (dompdf-friendly) ====== */
  table.tbl { width:100%; border-collapse: collapse; background:#fff; }
  table.tbl, .tbl th, .tbl td { border:1px solid #444; }    /* pakai #444 biar jelas di PDF */
  .tbl th, .tbl td { padding:6px 8px; }
  .tbl th { background:#f1f5f9; text-align:left; font-weight:700; color:#334155; }
  .tbl tfoot th, .tbl tfoot td { background:#eef2f7; font-weight:700; }

  /* agar header/foot tetap rapi saat page break */
  thead { display: table-header-group; }
  tfoot { display: table-row-group; }
</style>
</head>
<body>

  <h2>Rekap Kasir</h2>
  <div class="badge">
    Periode:
    {{ \Carbon\Carbon::parse($meta['start'])->format('d/m/Y') }}
    – {{ \Carbon\Carbon::parse($meta['end'])->format('d/m/Y') }}
  </div>

  {{-- Per Produk --}}
  <h3>Rekap Per Produk</h3>
  @if($items->isEmpty())
    <div class="muted mb12">Tidak ada data pada periode ini.</div>
  @else
    @php
      $sumQty = $items->sum('qty');
      $sumTotal = $items->sum('total');
    @endphp
    <table class="tbl mb12">
      <thead>
      <tr>
        <th>Produk</th>
        <th class="right">Qty</th>
        <th class="right">Total</th>
      </tr>
      </thead>
      <tbody>
      @foreach($items as $it)
        <tr>
          <td>{{ $it->name }}</td>
          <td class="right">{{ number_format($it->qty) }}</td>
          <td class="right money">Rp {{ number_format($it->total,0,',','.') }}</td>
        </tr>
      @endforeach
      </tbody>
      <tfoot>
      <tr>
        <th>Total</th>
        <th class="right">{{ number_format($sumQty) }}</th>
        <th class="right money">Rp {{ number_format($sumTotal,0,',','.') }}</th>
      </tr>
      </tfoot>
    </table>
  @endif

  {{-- Per Metode Pembayaran --}}
  <h3>Rekap Per Metode Pembayaran</h3>
  @if($payments->isEmpty())
    <div class="muted mb12">Belum ada pembayaran tersettlement.</div>
  @else
    @php
      $payTrx  = $payments->sum('trx');
      $paySum  = $payments->sum('total');
    @endphp
    <table class="tbl mb12">
      <thead>
      <tr>
        <th>Metode</th>
        <th class="right">Transaksi</th>
        <th class="right">Total</th>
      </tr>
      </thead>
      <tbody>
      @foreach($payments as $p)
        <tr>
          <td>{{ strtoupper($p->pg_payment_type) }}</td>
          <td class="right">{{ number_format($p->trx) }}</td>
          <td class="right money">Rp {{ number_format($p->total,0,',','.') }}</td>
        </tr>
      @endforeach
      </tbody>
      <tfoot>
      <tr>
        <th>Total</th>
        <th class="right">{{ number_format($payTrx) }}</th>
        <th class="right money">Rp {{ number_format($paySum,0,',','.') }}</th>
      </tr>
      </tfoot>
    </table>
  @endif

  {{-- Rekapitulasi Tunai vs Non-Tunai --}}
  <h3>Rekapitulasi Pembayaran (Tunai vs Non-Tunai)</h3>
  @php
    $tunai    = $payUnified->firstWhere('kategori','Tunai');
    $nontunai = $payUnified->firstWhere('kategori','Non Tunai');
    $trxTunai = (int)($tunai->trx ?? 0);
    $trxNon   = (int)($nontunai->trx ?? 0);
    $sumTunai = (int)($tunai->total ?? 0);
    $sumNon   = (int)($nontunai->total ?? 0);
  @endphp
  <table class="tbl mb12">
    <thead>
    <tr>
      <th>Kategori</th>
      <th class="right">Transaksi</th>
      <th class="right">Total</th>
    </tr>
    </thead>
    <tbody>
      <tr><td>Tunai</td><td class="right">{{ number_format($trxTunai) }}</td><td class="right money">Rp {{ number_format($sumTunai,0,',','.') }}</td></tr>
      <tr><td>Non Tunai</td><td class="right">{{ number_format($trxNon) }}</td><td class="right money">Rp {{ number_format($sumNon,0,',','.') }}</td></tr>
    </tbody>
    <tfoot>
    <tr>
      <th>Total</th>
      <th class="right">{{ number_format($trxTunai + $trxNon) }}</th>
      <th class="right money">Rp {{ number_format($sumTunai + $sumNon,0,',','.') }}</th>
    </tr>
    </tfoot>
  </table>

  <div class="page-break"></div>

  {{-- Jurnal Umum --}}
  <h3>Jurnal Umum</h3>
  @if($journal->isEmpty())
    <div class="muted">Tidak ada jurnal pada periode ini.</div>
  @else
    @php
      $tDebit  = $journal->sum('debit');
      $tCredit = $journal->sum('credit');
    @endphp
    <table class="tbl">
      <thead>
      <tr>
        <th>Tanggal</th>
        <th>No. Jurnal</th>
        <th>Ref</th>
        <th>Akun</th>
        <th class="right">Debit</th>
        <th class="right">Credit</th>
      </tr>
      </thead>
      <tbody>
      @foreach($journal as $j)
        <tr>
          <td>{{ \Carbon\Carbon::parse($j->date)->format('d/m/Y') }}</td>
          <td>{{ $j->entry_no }}</td>
          <td>{{ $j->ref_no }}</td>
          <td>{{ $j->code }} — {{ $j->name }}</td>
          <td class="right money">Rp {{ number_format($j->debit,0,',','.') }}</td>
          <td class="right money">Rp {{ number_format($j->credit,0,',','.') }}</td>
        </tr>
      @endforeach
      </tbody>
      <tfoot>
      <tr>
        <th colspan="4">Total</th>
        <th class="right money">Rp {{ number_format($tDebit,0,',','.') }}</th>
        <th class="right money">Rp {{ number_format($tCredit,0,',','.') }}</th>
      </tr>
      </tfoot>
    </table>
  @endif

<div class="page-break"></div>

<h3>Buku Besar</h3>
@php $hasLedger = !empty($ledger) && count($ledger) > 0; @endphp
@if(!$hasLedger)
  <div class="muted">Belum ada pergerakan buku besar di periode ini.</div>
@else
  @foreach($ledger as $acc => $bag)
    <h4 style="margin:10px 0 6px 0; font-size:13px">{{ $acc }}</h4>
    <table class="tbl mb12">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>No.</th>
          <th>Ref</th>
          <th>Keterangan</th>
          <th class="right">Debit</th>
          <th class="right">Credit</th>
          <th class="right">Saldo ({{ $bag['normal']==='DEBIT' ? 'D' : 'C' }})</th>
        </tr>
      </thead>
      <tbody>
        @foreach($bag['rows'] as $r)
          <tr>
            <td>{{ \Carbon\Carbon::parse($r['date'])->format('d/m/Y') }}</td>
            <td>{{ $r['entry'] }}</td>
            <td>{{ $r['ref'] ?? '-' }}</td>
            <td>{{ $r['memo'] ?? '-' }}</td>
            <td class="right money">Rp {{ number_format($r['debit'],0,',','.') }}</td>
            <td class="right money">Rp {{ number_format($r['credit'],0,',','.') }}</td>
            <td class="right money">Rp {{ number_format($r['saldo'],0,',','.') }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th colspan="4">Total</th>
          <th class="right money">Rp {{ number_format($bag['total_debit'],0,',','.') }}</th>
          <th class="right money">Rp {{ number_format($bag['total_credit'],0,',','.') }}</th>
          <th class="right money">Rp {{ number_format($bag['balance'],0,',','.') }}</th>
        </tr>
      </tfoot>
    </table>
  @endforeach
@endif


</body>
</html>
