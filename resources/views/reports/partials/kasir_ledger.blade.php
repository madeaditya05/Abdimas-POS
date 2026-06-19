<div class="kr-section">
  <div class="kr-section-title">Buku Besar</div>
  @forelse($ledger as $acc => $bag)
    @php
      $normalSide = strtoupper($bag['normal'] ?? 'DEBIT'); // 'DEBIT' / 'CREDIT'
    @endphp

    <div class="kr-subcard" style="margin-bottom:12px;">
      <div style="font-weight:700; margin-bottom:6px;">{{ $acc }}</div>
      <table class="kr-table">
        <thead>
          <tr>
            <th rowspan="2">Tanggal</th>
            <th rowspan="2">Keterangan</th>
            <th rowspan="2">Ref</th>
            <th rowspan="2" class="kr-right">Debit</th>
            <th rowspan="2" class="kr-right">Kredit</th>
            <th colspan="2" style="text-align:center;">Saldo</th>
          </tr>
          <tr>
            <th class="kr-right">Debit</th>
            <th class="kr-right">Kredit</th>
          </tr>
        </thead>
        <tbody>
          @foreach($bag['rows'] as $r)
            @php
              $rawSaldo = (float) ($r['saldo'] ?? 0);
              $isDebit = (
                  ($normalSide === 'DEBIT'  && $rawSaldo >= 0) ||
                  ($normalSide === 'CREDIT' && $rawSaldo <  0)
              );
              $nominalSaldo = abs($rawSaldo);
            @endphp
            <tr>
              <td>{{ \Carbon\Carbon::parse($r['date'])->format('d/m/Y') }}</td>
              <td>{{ $r['memo'] ?? '-' }}</td>
              <td>{{ $r['ref'] ?: $r['entry'] }}</td>
              <td class="kr-right kr-money">Rp {{ number_format($r['debit'],0,',','.') }}</td>
              <td class="kr-right kr-money">Rp {{ number_format($r['credit'],0,',','.') }}</td>
              <td class="kr-right kr-money">
                Rp {{ $isDebit ? number_format($nominalSaldo,0,',','.') : '0' }}
              </td>
              <td class="kr-right kr-money">
                Rp {{ !$isDebit ? number_format($nominalSaldo,0,',','.') : '0' }}
              </td>
            </tr>
          @endforeach

          @php
            $rawBalance = (float) ($bag['balance'] ?? 0);
            $isDebitBalance = (
                ($normalSide === 'DEBIT'  && $rawBalance >= 0) ||
                ($normalSide === 'CREDIT' && $rawBalance <  0)
            );
            $nominalBalance = abs($rawBalance);
          @endphp
          <tr>
            <th colspan="3">Total</th>
            <th class="kr-right kr-money">
              Rp {{ number_format($bag['total_debit'],0,',','.') }}
            </th>
            <th class="kr-right kr-money">
              Rp {{ number_format($bag['total_credit'],0,',','.') }}
            </th>
            <th class="kr-right kr-money">
              Rp {{ $isDebitBalance ? number_format($nominalBalance,0,',','.') : '0' }}
            </th>
            <th class="kr-right kr-money">
              Rp {{ !$isDebitBalance ? number_format($nominalBalance,0,',','.') : '0' }}
            </th>
          </tr>
        </tbody>
      </table>
    </div>
  @empty
    <div class="kr-empty">Belum ada pergerakan buku besar di periode ini.</div>
  @endforelse
</div>
