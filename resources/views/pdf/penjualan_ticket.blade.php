<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Tiket Barista</title>
<style>
  @page { margin: 6mm 5mm; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; }
  .c { text-align: center; }
  .r { text-align: right; }
  .hr { border-top: 1px dashed #000; margin: 6px 0; }
  table { width: 100%; border-collapse: collapse; }
  td, th { vertical-align: top; padding: 2px 0; }
  th { border-bottom: 1px solid #000; }
  .item { width: 48%; }
  .qty  { width: 12%; text-align:right; }
  .harga { width: 20%; text-align:right; }
  .sub   { width: 20%; text-align:right; }
  .note { font-size: 10px; color: #333; }
</style>
</head>
<body>
  @php
    $rp = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
  @endphp

  <div class="c"><strong>TIKET BARISTA</strong></div>
  Kode: <strong>{{ $penjualan->kode_penjualan }}</strong><br>
  Tgl : {{ optional($penjualan->tanggal)->format('d/m/Y H:i') }}<br>
  Kasir: {{ optional($penjualan->user)->name ?? '-' }}

  <div class="hr"></div>

  <table>
    <thead>
      <tr>
        <th class="item">Item</th>
        <th class="qty">Qty</th>
        <th class="harga">Harga</th>
        <th class="sub">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($penjualan->details as $d)
        <tr>
          <td class="item">
            <strong>{{ $d->nama_cetak ?? ($d->nama_produk ?? optional($d->produk)->name ?? optional($d->produk)->nama ?? '-') }}</strong>
            @if (!empty($d->catatan))
              <div class="note">• {{ $d->catatan }}</div>
            @endif
          </td>
          <td class="qty">
            {{ rtrim(rtrim(number_format($d->qty ?? 0, 2, '.', ''), '0'), '.') }}
          </td>
          <td class="harga">{{ $rp($d->harga ?? 0) }}</td>
          <td class="sub">{{ $rp($d->subtotal ?? 0) }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="c">Tidak ada item.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="hr"></div>

  <table>
    <tr>
      <td style="width:60%"></td>
      <td class="r" style="width:20%"><strong>Total</strong></td>
      <td class="r" style="width:20%"><strong>{{ $rp($penjualan->total ?? 0) }}</strong></td>
    </tr>
    <tr>
      <td></td>
      <td class="r">Bayar</td>
      <td class="r">{{ $rp($penjualan->bayar ?? 0) }}</td>
    </tr>
    <tr>
      <td></td>
      <td class="r">Kembalian</td>
      <td class="r">{{ $rp($penjualan->kembalian ?? 0) }}</td>
    </tr>
  </table>

</body>
</html>
