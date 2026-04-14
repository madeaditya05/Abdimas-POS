<div class="kr-section">
  <div class="kr-section-title">Laporan Penjualan (Ringkas)</div>
  @php
    $sumTrx = (int) ($sales?->sum('trx') ?? 0);
    $sumQty = (int) ($sales?->sum('qty') ?? 0);
    $sumOmz = (float) ($sales?->sum('omzet') ?? 0);
  @endphp

  @if(empty($sales) || $sales->isEmpty())
    <div class="kr-muted">Belum ada penjualan pada periode ini.</div>
  @else
    <table class="kr-table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Kasir</th>
          <th>Metode</th>
          <th>Produk</th>
          <th class="kr-right">Trx</th>
          <th class="kr-right">Qty</th>
          <th class="kr-right">Omzet</th>
        </tr>
      </thead>
      <tbody>
        @foreach($sales as $r)
          <tr>
            <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td>
            <td>{{ $r->kasir }}</td>
            <td>{{ $r->metode }}</td>
            <td>{{ $r->produk }}</td>
            <td class="kr-right">{{ number_format((int)$r->trx) }}</td>
            <td class="kr-right">{{ number_format((int)$r->qty) }}</td>
            <td class="kr-right kr-money">Rp {{ number_format((float)$r->omzet,0,',','.') }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th colspan="4">TOTAL PERIODE</th>
          <th class="kr-right">{{ number_format($sumTrx) }}</th>
          <th class="kr-right">{{ number_format($sumQty) }}</th>
          <th class="kr-right kr-money">Rp {{ number_format($sumOmz,0,',','.') }}</th>
        </tr>
      </tfoot>
    </table>
  @endif
</div>

