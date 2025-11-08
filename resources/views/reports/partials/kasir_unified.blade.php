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
      <tr><td>Tunai</td><td class="kr-right">{{ number_format($tunai->trx ?? 0) }}</td><td class="kr-right kr-money">Rp {{ number_format($tunai->total ?? 0,0,',','.') }}</td></tr>
      <tr><td>Non Tunai</td><td class="kr-right">{{ number_format($nontunai->trx ?? 0) }}</td><td class="kr-right kr-money">Rp {{ number_format($nontunai->total ?? 0,0,',','.') }}</td></tr>
      <tr><th>Total</th><th class="kr-right">{{ number_format(($tunai->trx ?? 0)+($nontunai->trx ?? 0)) }}</th><th class="kr-right kr-money">Rp {{ number_format($sumPay,0,',','.') }}</th></tr>
    </tbody>
  </table>
</div>

