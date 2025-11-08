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
