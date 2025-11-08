<div class="kr-section">
  <div class="kr-section-title">Rekap Per Produk</div>
  @if($items->isEmpty())
    <div class="kr-empty">Tidak ada data pada periode ini.</div>
  @else
    <table class="kr-table">
      <thead><tr><th>Produk</th><th class="kr-right">Qty</th><th class="kr-right">Total</th></tr></thead>
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
