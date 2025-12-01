<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Laporan Owner</title>
@php
  // pastikan file ini ada: public/fonts/DejaVuSans.ttf & DejaVuSans-Bold.ttf
  $fontRegular = str_replace('\\','/', public_path('fonts/DejaVuSans.ttf'));
  $fontBold    = str_replace('\\','/', public_path('fonts/DejaVuSans-Bold.ttf'));
@endphp
<style>
  /* Font DejaVuSans dari public/fonts */
  @font-face{
    font-family:'DejaVuSans';
    src: url('{{ $fontRegular }}') format('truetype');
    font-weight: 400; font-style: normal;
  }
  @font-face{
    font-family:'DejaVuSans';
    src: url('{{ $fontBold }}') format('truetype');
    font-weight: 700; font-style: normal;
  }

  @page { margin: 24px 28px; }
  body{ font-family:'DejaVuSans'; font-size:12px; color:#0f172a; line-height:1.35; }

  .title{ font-size:18px; font-weight:700; margin:0 0 2px 0; }
  .subtitle{ font-size:12px; color:#64748b; margin:0 0 10px 0; }
  .muted{ color:#64748b; }
  .right{ text-align:right; }
  .money{ font-variant-numeric: tabular-nums; letter-spacing:.2px; }

  /* Tabel laba rugi */
  table.pl { width:100%; border-collapse:collapse; margin-top:6px; margin-bottom:10px; }
  .pl th, .pl td { padding:6px 8px; }
  .pl .head-row th{ font-size:13px; padding-top:10px; padding-bottom:4px; }
  .pl .sep{ height:1px; background:#e5e7eb; }
  .pl .total-row{ background:#f3f4f6; font-weight:700; }
  .pl .emph{ font-weight:700; }
  .pl .indent{ padding-left:22px; }

  /* Tabel umum (sama seperti kasir pdf) */
  table.tbl { width:100%; border-collapse:collapse; background:#fff; }
  table.tbl, .tbl th, .tbl td { border:1px solid #444; }
  .tbl th, .tbl td { padding:6px 8px; vertical-align:top; }
  .tbl th { background:#f1f5f9; text-align:left; font-weight:700; color:#334155; }
  .tbl tfoot th, .tbl tfoot td { background:#eef2f7; font-weight:700; }

  thead { display: table-header-group; }
  tfoot { display: table-row-group; }
  .tbl tr { page-break-inside: avoid; }
  .tbl td, .tbl th { page-break-inside: avoid; }

  .keep-with-next { page-break-after: avoid; }
</style>
</head>
<body>

@php
  // default kalau tidak dikirim dari controller
  $sections = $sections ?? [
    'labarugi' => true,
    'items'    => true,
    'payments' => true,
    'unified'  => true,
    'journal'  => true,
    'ledger'   => true,
  ];

  $rev   = (float)($lr['revenue'] ?? 0);
  $cogs  = (float)($lr['cogs'] ?? 0);
  $gross = (float)($lr['gross'] ?? ($rev - $cogs));
  $exp   = (float)($lr['expense'] ?? 0);
  $net   = (float)($lr['net_income'] ?? ($gross - $exp));
  $fmt   = fn($n) => 'Rp '.number_format($n,0,',','.');
@endphp

<div class="title">Laporan Owner</div>
<div class="subtitle">
  Periode
  {{ \Carbon\Carbon::parse($meta['start'])->format('d/m/Y') }}
  – {{ \Carbon\Carbon::parse($meta['end'])->format('d/m/Y') }}
</div>

{{-- LABA RUGI --}}
@if($sections['labarugi'])
  <h3 class="keep-with-next">Ringkasan Laba Rugi</h3>
  <table class="pl">
    <tr class="head-row"><th colspan="2">Pendapatan</th></tr>
    <tr>
      <td class="indent muted">Pendapatan Penjualan</td>
      <td class="right money">{{ $fmt($rev) }}</td>
    </tr>
    <tr><td colspan="2"><div class="sep"></div></td></tr>
    <tr class="total-row">
      <td>Total Pendapatan</td>
      <td class="right money">{{ $fmt($rev) }}</td>
    </tr>

    <tr class="head-row"><th colspan="2">Harga Pokok Penjualan</th></tr>
    <tr>
      <td class="indent muted">HPP</td>
      <td class="right money">{{ $fmt($cogs) }}</td>
    </tr>
    <tr><td colspan="2"><div class="sep"></div></td></tr>
    <tr class="total-row">
      <td>Total Harga Pokok Penjualan</td>
      <td class="right money">{{ $fmt($cogs) }}</td>
    </tr>

    <tr><td colspan="2"><div class="sep"></div></td></tr>
    <tr class="total-row">
      <td class="emph">TOTAL LABA KOTOR</td>
      <td class="right money emph">{{ $fmt($gross) }}</td>
    </tr>

    <tr class="head-row"><th colspan="2">Beban Operasional</th></tr>
    <tr>
      <td class="indent muted">Beban Operasional</td>
      <td class="right money">{{ $fmt($exp) }}</td>
    </tr>
    <tr><td colspan="2"><div class="sep"></div></td></tr>
    <tr class="total-row">
      <td>Total Beban Operasional</td>
      <td class="right money">{{ $fmt($exp) }}</td>
    </tr>

    <tr><td colspan="2"><div class="sep"></div></td></tr>
    <tr class="total-row">
      <td class="emph">LABA / (RUGI) BERSIH</td>
      <td class="right money emph">{{ $fmt($net) }}</td>
    </tr>
  </table>
@endif

{{-- REKAP PER PRODUK --}}
@if($sections['items'])
  <h3 class="keep-with-next">Rekap Per Produk</h3>
  @if($items->isEmpty())
    <div class="muted">Tidak ada data pada periode ini.</div>
  @else
    @php
      $sumQty   = $items->sum('qty');
      $sumTotal = $items->sum('total');
    @endphp
    <table class="tbl" style="margin-bottom:10px;">
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
            <td class="right money">{{ $fmt($it->total) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th>Total</th>
          <th class="right">{{ number_format($sumQty) }}</th>
          <th class="right money">{{ $fmt($sumTotal) }}</th>
        </tr>
      </tfoot>
    </table>
  @endif
@endif

{{-- REKAP METODE PEMBAYARAN --}}
@if($sections['payments'])
  <h3 class="keep-with-next">Rekap Per Metode Pembayaran</h3>
  @if($payments->isEmpty())
    <div class="muted">Belum ada pembayaran tersettlement.</div>
  @else
    @php
      $payTrx = $payments->sum('trx');
      $paySum = $payments->sum('total');
    @endphp
    <table class="tbl" style="margin-bottom:10px;">
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
            <td class="right money">{{ $fmt($p->total) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th>Total</th>
          <th class="right">{{ number_format($payTrx) }}</th>
          <th class="right money">{{ $fmt($paySum) }}</th>
        </tr>
      </tfoot>
    </table>
  @endif
@endif

{{-- TUNAI VS NON TUNAI --}}
@if($sections['unified'])
  <h3 class="keep-with-next">Rekapitulasi Pembayaran (Tunai vs Non-Tunai)</h3>
  @php
    $tunai    = $payUnified->firstWhere('kategori','Tunai');
    $nontunai = $payUnified->firstWhere('kategori','Non Tunai');

    $trxTunai = (int)($tunai->trx ?? 0);
    $trxNon   = (int)($nontunai->trx ?? 0);
    $sumTunai = (int)($tunai->total ?? 0);
    $sumNon   = (int)($nontunai->total ?? 0);
  @endphp
  <table class="tbl" style="margin-bottom:10px;">
    <thead>
      <tr>
        <th>Kategori</th>
        <th class="right">Transaksi</th>
        <th class="right">Total</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Tunai</td>
        <td class="right">{{ number_format($trxTunai) }}</td>
        <td class="right money">{{ $fmt($sumTunai) }}</td>
      </tr>
      <tr>
        <td>Non Tunai</td>
        <td class="right">{{ number_format($trxNon) }}</td>
        <td class="right money">{{ $fmt($sumNon) }}</td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <th>Total</th>
        <th class="right">{{ number_format($trxTunai + $trxNon) }}</th>
        <th class="right money">{{ $fmt($sumTunai + $sumNon) }}</th>
      </tr>
    </tfoot>
  </table>
@endif

{{-- JURNAL UMUM --}}
@if($sections['journal'])
  <h3 class="keep-with-next">Jurnal Umum</h3>
  @if($journal->isEmpty())
    <div class="muted">Tidak ada jurnal pada periode ini.</div>
  @else
    @php
      $tDebit  = $journal->sum('debit');
      $tCredit = $journal->sum('credit');
    @endphp
    <table class="tbl" style="margin-bottom:10px;">
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
            <td class="right money">{{ $fmt($j->debit) }}</td>
            <td class="right money">{{ $fmt($j->credit) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th colspan="4">Total</th>
          <th class="right money">{{ $fmt($tDebit) }}</th>
          <th class="right money">{{ $fmt($tCredit) }}</th>
        </tr>
      </tfoot>
    </table>
  @endif
@endif

{{-- BUKU BESAR --}}
@if($sections['ledger'])
  <h3 class="keep-with-next">Buku Besar</h3>
  @php $hasLedger = !empty($ledger) && count($ledger) > 0; @endphp
  @if(!$hasLedger)
    <div class="muted">Belum ada pergerakan buku besar di periode ini.</div>
  @else
    @foreach($ledger as $acc => $bag)
      <h4 class="keep-with-next" style="margin:10px 0 6px 0; font-size:13px">{{ $acc }}</h4>
      <table class="tbl" style="margin-bottom:10px;">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>No.</th>
            <th>Ref</th>
            <th>Keterangan</th>
            <th class="right">Debit</th>
            <th class="right">Credit</th>
            <th class="right">Saldo ({{ $bag['normal']==='DEBIT'?'D':'C' }})</th>
          </tr>
        </thead>
        <tbody>
          @foreach($bag['rows'] as $r)
            <tr>
              <td>{{ \Carbon\Carbon::parse($r['date'])->format('d/m/Y') }}</td>
              <td>{{ $r['entry'] }}</td>
              <td>{{ $r['ref'] ?? '-' }}</td>
              <td>{{ $r['memo'] ?? '-' }}</td>
              <td class="right money">{{ $fmt($r['debit']) }}</td>
              <td class="right money">{{ $fmt($r['credit']) }}</td>
              <td class="right money">{{ $fmt($r['saldo']) }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4">Total</th>
            <th class="right money">{{ $fmt($bag['total_debit']) }}</th>
            <th class="right money">{{ $fmt($bag['total_credit']) }}</th>
            <th class="right money">{{ $fmt($bag['balance']) }}</th>
          </tr>
        </tfoot>
      </table>
    @endforeach
  @endif
@endif

</body>
</html>
