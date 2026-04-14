@extends('layouts.print')

@section('title', 'Struk ' . ($penjualan->kode_penjualan ?? ''))

@php
  $rupiah = function ($n) {
    $n = (float) ($n ?? 0);
    return 'Rp ' . number_format($n, 0, ',', '.');
  };

  $brand = config('invoice.brand_name') ?: config('app.name');
  $date = $penjualan->tanggal ? $penjualan->tanggal->format('d/m/Y H:i') : now()->format('d/m/Y H:i');
  $kasir = $penjualan->user?->name ?? '-';
  $metode = strtoupper((string) ($penjualan->metode ?? '-'));

  $subtotal = (float) ($penjualan->total ?? 0);
  $bayar = (float) ($penjualan->bayar ?? 0);
  $kembalian = (float) ($penjualan->kembalian ?? 0);

  // Kita paksa printMode false jika hanya ingin melihat preview, 
  // atau biarkan true jika ingin otomatis muncul dialog print saat load
  $printMode = request()->boolean('print', true);
@endphp

@push('styles')
  <style>
    /* Receipt look */
    body { background: #e5e7eb; margin: 0; padding: 20px 0; display: flex; justify-content: center; }
    .page { width: 80mm; min-height: auto; padding: 8mm 6mm; background: #fff; box-shadow: 0 12px 28px rgba(0,0,0,.12); border-radius: 4px; }
    .h { text-align:center; font-weight: 900; letter-spacing:.8px; font-size: 16px; }
    .sub { text-align:center; color:#6b7280; font-size: 12px; margin-top: 2px; }
    .hr { border-top: 1px dashed #111827; margin: 10px 0; }
    .row { display:flex; justify-content:space-between; gap:10px; font-size: 12.5px; margin-top: 4px; }
    .items { margin-top: 6px; }
    .it { margin-top: 8px; }
    .it .name { font-size: 12.5px; font-weight: 700; }
    .it .meta { display:flex; justify-content:space-between; gap:10px; font-size: 12px; color:#111827; margin-top: 2px; }
    .t-right { text-align:right; }
    .tot { font-weight: 900; font-size: 14px; }
    .muted { color:#6b7280; }

    @media print {
      @page { size: 80mm auto; margin: 0; }
      body { background:#fff; padding: 0; }
      .page { width: 100%; padding: 4mm 2mm; box-shadow:none; border-radius: 0; }
      .no-print { display: none !important; }
    }
  </style>
@endpush

@section('content')
<div class="page">
  {{-- Navigasi Tombol --}}
  <div class="no-print" style="display:flex; gap:8px; justify-content:center; margin-bottom:20px; background: #fff; padding: 10px; border-radius: 8px; border: 1px solid #d1d5db;">
    <a href="{{ route('kasir.index') }}" class="btn" style="text-decoration:none; padding:8px 12px; border:1px solid #d1d5db; border-radius:8px; color:#111827; font-size: 13px; font-weight: 600;">
      ⬅️ Kembali
    </a>
    
    {{-- Mengubah link menjadi tombol print agar tidak reload/buka tab baru --}}
    <button type="button" onclick="window.print()" class="btn" style="cursor:pointer; padding:8px 12px; border:1px solid #d1d5db; border-radius:8px; background:#fff; color:#111827; font-size: 13px; font-weight: 600;">
      🖨️ Cetak
    </button>

    <button type="button" id="btnSelesai" class="btn btn-primary" style="cursor:pointer; padding:8px 12px; border-radius:8px; background:#0f766e; color:#fff; border:none; font-size: 13px; font-weight: 600;">
      Selesaikan ✅
    </button>
  </div>

  <div class="h">{{ strtoupper($brand) }}</div>
  <div class="sub">STRUK PEMBAYARAN</div>

  <div class="hr"></div>

  <div class="row"><div class="muted">No</div><div>{{ $penjualan->kode_penjualan }}</div></div>
  <div class="row"><div class="muted">Tgl</div><div>{{ $date }}</div></div>
  <div class="row"><div class="muted">Kasir</div><div>{{ $kasir }}</div></div>
  <div class="row"><div class="muted">Metode</div><div>{{ $metode }}</div></div>

  <div class="hr"></div>

  <div class="items">
    @foreach($penjualan->details as $d)
      @php
        $desc = $d->produk?->nama_barang ?? ('Item #' . $d->produk_id);
        $price = (float) ($d->harga ?? 0);
        $qty = (int) ($d->qty ?? 0);
        $line = $price * $qty;
      @endphp
      <div class="it">
        <div class="name">{{ $desc }}</div>
        <div class="meta">
          <div>{{ $qty }} x {{ $rupiah($price) }}</div>
          <div class="t-right">{{ $rupiah($line) }}</div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="hr"></div>

  <div class="row"><div class="muted">Subtotal</div><div>{{ $rupiah($subtotal) }}</div></div>
  <div class="row tot"><div>Total</div><div>{{ $rupiah($subtotal) }}</div></div>
  <div class="row"><div class="muted">Bayar</div><div>{{ $rupiah($bayar) }}</div></div>
  <div class="row"><div class="muted">Kembalian</div><div>{{ $rupiah($kembalian) }}</div></div>

  <div class="hr"></div>
  <div class="sub">Terima kasih atas kunjungan Anda</div>
</div>

@push('scripts')
  <script>
    (function(){
      const btn = document.getElementById('btnSelesai');
      if (btn) {
        btn.addEventListener('click', function(){
          btn.disabled = true;
          btn.innerText = 'Memproses...';

          // Gunakan route yang benar sesuai diskusi sebelumnya (kasir.selesaiCetak atau kasir.struk.selesai)
          // Jika route kasir.struk.selesai error, ganti ke kasir.selesaiCetak
          fetch("{{ route('kasir.selesaiCetak', ['kode' => $penjualan->kode_penjualan]) }}", {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ dicetak: 1 })
          }).finally(function(){
            // Menghapus sales code dari localStorage agar dashboard tidak polling lagi
            localStorage.removeItem('last_sales_code');
            window.location.href = "{{ route('kasir.index') }}";
          });
        });
      }
    })();
  </script>
@endpush

@if($printMode)
  <script>
    window.addEventListener('load', function () {
      window.print();
    });

    // Setelah dialog print ditutup, otomatis "Selesaikan" agar keranjang reset,
    // lalu kembali ke kasir (tetap di tab yang sama).
    window.addEventListener('afterprint', function () {
      const btn = document.getElementById('btnSelesai');
      if (btn && !btn.disabled) btn.click();
    });
  </script>
@endif
@endsection
