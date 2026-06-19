<div class="kr-section">
  <div class="kr-section-title">Rekap Per Metode Pencatatan</div>
  @if($payments->isEmpty())
    <div class="kr-empty">Belum ada penjualan tercatat.</div>
  @else
    <table class="kr-table">
      <thead><tr><th>Metode</th><th class="kr-right">Transaksi</th><th class="kr-right">Total</th></tr></thead>
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
